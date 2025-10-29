<?php

namespace App\Application\Services\Muestras;

use App\Models\Muestras;
use App\Models\TipoMuestra;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MuestrasService
{

    public function getFilteredMuestras(array $filters, User $user): array
    {
        $query = Muestras::with(['clasificacion.unidadMedida', 'tipoMuestra', 'doctor', 'clasificacionPresentacion'])
            ->where('state', true);

        $tiposMuestra = null;
        // === Filtros por rol del usuario ===
        if (in_array($user->role->name, ['admin', 'coordinador-lineas', 'supervisor'])) {
            $tiposMuestra = TipoMuestra::get();
        } else {
            if ($user->hasRole('visitador')) {
                $query->where('created_by', $user->id);
            } elseif ($user->hasRole('jefe-comercial')) {
                $query->where('aprobado_coordinadora', true);
            } elseif ($user->hasRole('laboratorio')) {
                $query->where([
                    'aprobado_coordinadora' => true,
                    'aprobado_jefe_comercial' => true,
                    'aprobado_jefe_operaciones' => true
                ]);
            } elseif ($user->hasRole('jefe-operaciones')) {
                $restrictedRange = $this->getLimitMuestrasShowed();

                if ($restrictedRange) {
                    [$start, $end] = $restrictedRange;
                    $query->where(function ($q) use ($start, $end) {
                        $q->where('created_at', '<', $start)
                            ->orWhere('created_at', '>=', $end);
                    });
                }

                $query->where([
                    'aprobado_coordinadora' => true,
                    'aprobado_jefe_comercial' => true
                ]);
            } else {
                $query->where([
                    'aprobado_coordinadora' => true,
                    'aprobado_jefe_comercial' => true
                ]);
            }
        }

        // === Filtros de búsqueda ===
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nombre_muestra', 'like', "%{$search}%")
                    ->orWhereHas('doctor', function ($q2) use ($search) {
                        $q2->where(DB::raw("CONCAT_WS(' ', name, first_lastname, second_lastname)"), 'like', "%{$search}%");
                    });
            });
        }

        // === Filtro por rango de fechas ===
        $query->whereBetween(
            $filters['filter_by_date_field'],
            [$filters['date_since'], $filters['date_to']]
        );

        // === Filtro por estado de laboratorio ===
        if ($filters['lab_state'] !== null) {
            $query->where('lab_state', $filters['lab_state']);
        }

        // === Ordenamiento ===
        if ($filters['order_by'] === 'datetime_scheduled') {
            $query->orderByRaw('CASE WHEN datetime_scheduled IS NULL THEN 0 ELSE 1 END ASC')
                ->orderBy('datetime_scheduled', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // === Paginación ===
        $muestras = $query->paginate(10);

        // === Preparar respuesta ===
        $data = compact('muestras');

        if ($tiposMuestra) {
            $data['tiposMuestra'] = $tiposMuestra;
        }

        return $data;
    }

    private function getLimitMuestrasShowed()
    {
        $now = Carbon::now();

        $startRestriction = $now->copy()->startOfWeek()->addDays(2)->setTime(14, 0, 0);
        $endRestriction = $now->copy()->startOfWeek()->addDays(4)->setTime(12, 0, 0);

        if ($now->between($startRestriction, $endRestriction)) {
            return [$startRestriction, $endRestriction];
        }

        return null;
    }

    public function create(array $data, $userId, $foto = null)
    {
        // Manejo de imagen
        $fotoPath = $this->handleImageUpload($foto, $data['nombre_muestra'] ?? 'muestra');

        $muestra = Muestras::create([
            'nombre_muestra' => $data['nombre_muestra'],
            'clasificacion_id' => $data['clasificacion_id'],
            'cantidad_de_muestra' => $data['cantidad_de_muestra'],
            'observacion' => $data['observacion'] ?? null,
            'tipo_frasco' => $data['tipo_frasco'],
            'id_doctor' => $data['id_doctor'],
            'clasificacion_presentacion_id' => $data['clasificacion_presentacion_id'] ?? null,
            'foto' => $fotoPath,
            'created_by' => $userId,
        ]);

        return $muestra;
    }

    public function update(Muestras $muestra, array $data)
    {
        if (!$muestra->state) {
            throw new \LogicException("No se puede editar una muestra inhabilitada.");
        }
        if ($muestra->aprobado_coordinadora) {
            throw new \LogicException("No se puede editar una muestra ya aprobada.");
        }

        if ($data['tipo_frasco'] === 'frasco muestra') {
            $data['clasificacion_presentacion_id'] = null;
        }

        if (isset($data['foto']) && $data['foto'] instanceof \Illuminate\Http\UploadedFile) {
            $this->deleteOldImage($muestra->foto);
            $data['foto'] = $this->handleImageUpload($data['foto'], $data['nombre_muestra']);
        } else {
            unset($data['foto']); // no actualizar si no hay nueva imagen
        }

        $muestra->update($data);
        return $muestra;
    }

    public function disable(Muestras $muestra, string $reason, int $userId)
    {
        if ($muestra->aprobado_jefe_operaciones) {
            throw new \LogicException("No se puede deshabilitar una muestra aprobada por Jefe de Operaciones.");
        }

        $muestra->update([
            'state' => false,
            'delete_reason' => $reason,
            'updated_by' => $userId,
        ]);

        return $muestra;
    }

    public function updatePrice(Muestras $muestra, float $price)
    {
        if (!$muestra->state)
            throw new \LogicException("No se puede realizar esta operación. La muestra esta inhabilitada.");
        if (!$muestra->aprobado_jefe_comercial)
            throw new \LogicException("El Jefe Comercial debe aprobar la muestra primero.");
        if ($muestra->aprobado_jefe_operaciones)
            throw new \LogicException("No se puede cambiar el precio una vez aprobado por el Jefe de Operaciones.");
        if ($muestra->precio === $price)
            throw new \LogicException("El precio es el mismo al ya asignado.");

        $muestra->precio = $price;
        $muestra->save();

        return $muestra;
    }

    public function updateTipoMuestra(Muestras $muestra, int $tipoMuestraId)
    {
        if (!$muestra->state)
            throw new \LogicException("No se puede realizar esta operación. La muestra esta inhabilitada.");
        if ($muestra->aprobado_coordinadora) {
            throw new \LogicException("No se puede cambiar el tipo de muestra una vez aprobada por la Supervisora.");
        }

        $muestra->id_tipo_muestra = $tipoMuestraId;
        $muestra->save();
        return $muestra;
    }

    public function updateDateTimeScheduled(Muestras $muestra, string $datetime)
    {
        if (!$muestra->state)
            throw new \LogicException("No se puede realizar esta operación. La muestra esta inhabilitada.");
        if ($muestra->aprobado_coordinadora)
            throw new \LogicException("No se puede cambiar fecha tras aprobación.");

        $muestra->datetime_scheduled = $datetime;
        $muestra->save();
        return $muestra;
    }

    public function updateComentarioLab(Muestras $muestra, string $comentario)
    {
        if (!$muestra->state)
            throw new \LogicException("No se puede realizar esta operación. La muestra esta inhabilitada.");

        $muestra->comentarios = $comentario;
        $muestra->save();
        return $muestra;
    }

    public function markAsElaborated(Muestras $muestra)
    {
        if (!$muestra->state)
            throw new \LogicException("No se puede realizar esta operación. La muestra esta inhabilitada.");
        if (!$muestra->aprobado_jefe_operaciones)
            throw new \LogicException("La muestra debe estar aprobada por el Jefe de Operaciones.");
        if ($muestra->lab_state)
            throw new \LogicException("La muestra ya está marcada como elaborada.");

        $muestra->lab_state = true;
        $muestra->datetime_delivered = Carbon::now();
        $muestra->save();
        return $muestra;
    }

    public function aproveByCoordinadora(Muestras $muestra)
    {
        if (!$muestra->state)
            throw new \LogicException("No se puede realizar esta operación. La muestra esta inhabilitada.");
        if (!$muestra->id_tipo_muestra || $muestra->id_tipo_muestra < 1)
            throw new \LogicException("Se requiere un tipo de muestra para aprobarse.");
        if (!$muestra->datetime_scheduled)
            throw new \LogicException("Se requiere una fecha y hora de entrega para aprobarse.");
        if ($muestra->aprobado_coordinadora)
            throw new \LogicException("La muestra ya ha sido aprobada.");

        $muestra->aprobado_coordinadora = true;
        $muestra->saveWithoutTimestamps();
        return $muestra;
    }

    public function aproveByJefeComercial(Muestras $muestra)
    {
        if (!$muestra->state)
            throw new \LogicException("No se puede realizar esta operación. La muestra esta inhabilitada.");
        if (!$muestra->aprobado_coordinadora)
            throw new \LogicException("La Supervisora debe aprobar la muestra primero.");
        if ($muestra->aprobado_jefe_comercial)
            throw new \LogicException("La muestra ya ha sido aprobada.");

        $muestra->aprobado_jefe_comercial = true;
        $muestra->saveWithoutTimestamps();
        return $muestra;
    }

    public function aproveByJefeOperaciones(Muestras $muestra)
    {
        if (!$muestra->state)
            throw new \LogicException("No se puede realizar esta operación. La muestra esta inhabilitada.");
        if (!$muestra->aprobado_jefe_comercial)
            throw new \LogicException("El Jefe Comercial debe aprobar la muestra primero.");
        if (is_null($muestra->precio) || $muestra->precio <= 0)
            throw new \LogicException("Contabilidad debe asignar precio a la muestra.");
        if ($muestra->aprobado_jefe_operaciones)
            throw new \LogicException("La muestra ya ha sido aprobada.");

        $muestra->aprobado_jefe_operaciones = true;
        $muestra->saveWithoutTimestamps();
        return $muestra;
    }

    // --- Métodos privados auxiliares ---

    private function handleImageUpload($file, string $nombreMuestra): ?string
    {
        if (!$file)
            return null;

        $timestamp = Carbon::now()->format('m-d_H-i');
        $filename = Str::slug($nombreMuestra) . "_$timestamp." . $file->getClientOriginalExtension();
        $relativePath = 'images/muestras_fotos';
        $fullPath = public_path($relativePath);

        if (!File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }

        $file->move($fullPath, $filename);
        return "$relativePath/$filename";
    }

    private function deleteOldImage(?string $oldPath): void
    {
        if ($oldPath && File::exists(public_path($oldPath))) {
            File::delete(public_path($oldPath));
        }
    }

}