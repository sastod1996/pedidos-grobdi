<?php

namespace App\Application\Services\Muestras;

use App\Models\Muestras;
use App\Models\MuestrasEstado;
use App\Models\TipoMuestra;
use App\Models\User;
use App\Models\Enums\MuestraEstadoType;
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
                $query->withEvent(MuestraEstadoType::APROVE_COORDINADOR);
            } elseif ($user->hasRole('laboratorio')) {
                $query->withEvent(MuestraEstadoType::APROVE_JEFE_OPERACIONES);
            } elseif ($user->hasRole('jefe-operaciones')) {
                $restrictedRange = $this->getLimitMuestrasShowed();

                if ($restrictedRange) {
                    [$start, $end] = $restrictedRange;
                    $query->where(function ($q) use ($start, $end) {
                        $q->where('created_at', '<', $start)
                            ->orWhere('created_at', '>=', $end);
                    });
                }

                $query->withEvent(MuestraEstadoType::APROVE_JEFE_COMERCIAL);
            } else {
                $query->withEvent(MuestraEstadoType::APROVE_JEFE_COMERCIAL);
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
            if ($filters['lab_state']) {
                $query->withEvent(MuestraEstadoType::PRODUCED);
            } else {
                $query->withoutEvent(MuestraEstadoType::PRODUCED);
            }
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
        $this->verifyIsActive($muestra);

        if ($muestra->isAprovedByCoordinadora())
            throw new \LogicException("No se puede editar una muestra ya aprobada.");

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
        if ($muestra->isAprovedByJefeOperaciones()) {
            throw new \LogicException("No se puede deshabilitar una muestra aprobada por Jefe de Operaciones.");
        }

        $muestra->update([
            'state' => false,
            'delete_reason' => $reason,
            'updated_by' => $userId,
        ]);

        return $muestra;
    }

    public function updatePrice(Muestras $muestra, float $price, User $user)
    {
        $this->verifyIsActive($muestra);

        $this->assertValidTransition($muestra, MuestraEstadoType::SET_PRICE);

        if ($muestra->precio === $price)
            throw new \LogicException("El precio es el mismo al ya asignado.");

        DB::transaction(function () use ($muestra, $price, $user) {
            $muestra->precio = $price;
            $muestra->save();

            MuestrasEstado::create([
                'muestras_id' => $muestra->id,
                'user_id' => $user->id,
                'type' => MuestraEstadoType::SET_PRICE,
                'comment' => "Precio actualizado {Precio: $price} por $user->name"
            ]);
        });

        return $muestra;
    }

    public function updateTipoMuestra(Muestras $muestra, int $tipoMuestraId)
    {
        $this->verifyIsActive($muestra);

        if ($muestra->isAprovedByCoordinadora())
            throw new \LogicException("No se puede cambiar el tipo de muestra una vez aprobada por la Supervisora.");

        $muestra->id_tipo_muestra = $tipoMuestraId;
        $muestra->save();
        return $muestra;
    }

    public function updateDateTimeScheduled(Muestras $muestra, string $datetime)
    {
        $this->verifyIsActive($muestra);

        if ($muestra->isAprovedByCoordinadora())
            throw new \LogicException("No se puede cambiar fecha tras aprobación.");

        $muestra->datetime_scheduled = $datetime;
        $muestra->save();
        return $muestra;
    }

    public function updateComentarioLab(Muestras $muestra, string $comentario)
    {
        $this->verifyIsActive($muestra);

        $muestra->comentarios = $comentario;
        $muestra->save();
        return $muestra;
    }

    public function markAsElaborated(Muestras $muestra, User $user)
    {
        $this->verifyIsActive($muestra);

        $this->assertValidTransition($muestra, MuestraEstadoType::PRODUCED);

        MuestrasEstado::create([
            'muestras_id' => $muestra->id,
            'user_id' => $user->id,
            'type' => MuestraEstadoType::PRODUCED,
            'comment' => "Marcada como producida por $user->name"
        ]);
        return $muestra->currentStatus;
    }

    public function aproveByCoordinadora(Muestras $muestra, User $user)
    {
        $this->verifyIsActive($muestra);

        if (!$muestra->id_tipo_muestra || $muestra->id_tipo_muestra < 1)
            throw new \LogicException("Se requiere un tipo de muestra para aprobarse.");
        if (!$muestra->datetime_scheduled)
            throw new \LogicException("Se requiere una fecha y hora de entrega para aprobarse.");

        $this->assertValidTransition($muestra, MuestraEstadoType::APROVE_COORDINADOR);

        MuestrasEstado::create([
            'muestras_id' => $muestra->id,
            'user_id' => $user->id,
            'type' => MuestraEstadoType::APROVE_COORDINADOR,
            'comment' => "Aprobado por Coordinador@: $user->name"
        ]);
        return $muestra->currentStatus;
    }

    public function aproveByJefeComercial(Muestras $muestra, User $user)
    {
        $this->verifyIsActive($muestra);

        $this->assertValidTransition($muestra, MuestraEstadoType::APROVE_JEFE_COMERCIAL);

        MuestrasEstado::create([
            'muestras_id' => $muestra->id,
            'user_id' => $user->id,
            'type' => MuestraEstadoType::APROVE_JEFE_COMERCIAL,
            'comment' => "Aprobado por Jefe Comercial: $user->name"
        ]);
        return $muestra->currentStatus;
    }

    public function aproveByJefeOperaciones(Muestras $muestra, User $user)
    {
        $this->verifyIsActive($muestra);

        $this->assertValidTransition($muestra, MuestraEstadoType::APROVE_JEFE_OPERACIONES);

        if (is_null($muestra->precio) || $muestra->precio <= 0)
            throw new \LogicException("Contabilidad debe asignar precio a la muestra.");

        MuestrasEstado::create([
            'muestras_id' => $muestra->id,
            'user_id' => $user->id,
            'type' => MuestraEstadoType::APROVE_JEFE_OPERACIONES,
            'comment' => "Aprobado por Jefe de Operaciones: $user->name"
        ]);
        return $muestra->currentStatus;
    }

    public function getStatusByMuestra($muestra)
    {
        if (!$muestra instanceof Muestras) {
            $muestra = Muestras::findOrFail((int) $muestra);
        }

        return $muestra->status()
            ->with('user:id,name,role_id')
            ->with('user.role:id,name')
            ->get()
            ->map(function ($status) {
                return [
                    'id' => $status->id,
                    'muestras_id' => $status->muestras_id,
                    'user_id' => $status->user_id,
                    'user_name' => $status->user->name,
                    'user_role' => $status->user->role->name,
                    'type' => $status->type,
                    'comment' => $status->comment,
                    'created_at' => $status->created_at,
                ];
            });
    }

    /* ------------- Estados para Muestras ------------- */

    /* Status Flow:
            Coordinador@ de lineas → Jef@ Comercial → Contador@ → Jef@ de Operaciones → Laboratorio lo produce
    */
    private const TRANSITIONS = [
        null => [MuestraEstadoType::APROVE_COORDINADOR],
        MuestraEstadoType::APROVE_COORDINADOR->value => [MuestraEstadoType::APROVE_JEFE_COMERCIAL],
        MuestraEstadoType::APROVE_JEFE_COMERCIAL->value => [MuestraEstadoType::SET_PRICE],
        MuestraEstadoType::SET_PRICE->value => [MuestraEstadoType::SET_PRICE, MuestraEstadoType::APROVE_JEFE_OPERACIONES],
        MuestraEstadoType::APROVE_JEFE_OPERACIONES->value => [MuestraEstadoType::PRODUCED],
        MuestraEstadoType::PRODUCED->value => [],
    ];

    private const TRANSITION_ERROR_MESSAGES = [
        null => [
            [
                'message' => 'Se requiere aprobación de la Coordinadora de Líneas.',
                'targets' => [
                    MuestraEstadoType::APROVE_JEFE_COMERCIAL,
                    MuestraEstadoType::SET_PRICE,
                    MuestraEstadoType::APROVE_JEFE_OPERACIONES,
                    MuestraEstadoType::PRODUCED,
                ],
            ]
        ],
        MuestraEstadoType::APROVE_COORDINADOR->value => [
            [
                'message' => 'La muestra ya fue aprobada por la Coordinadora de Líneas.',
                'targets' => [MuestraEstadoType::APROVE_COORDINADOR],
            ],
            [
                'message' => 'Se requiere aprobación del Jefe Comercial.',
                'targets' => [
                    MuestraEstadoType::SET_PRICE,
                    MuestraEstadoType::APROVE_JEFE_OPERACIONES,
                    MuestraEstadoType::PRODUCED,
                ],
            ],
        ],
        MuestraEstadoType::APROVE_JEFE_COMERCIAL->value => [
            [
                'message' => 'La muestra ya fue aprobada por el Jefe Comercial.',
                'targets' => [MuestraEstadoType::APROVE_COORDINADOR, MuestraEstadoType::APROVE_JEFE_COMERCIAL],
            ],
            [
                'message' => 'La muestra requiere que se le asigne un precio.',
                'targets' => [
                    MuestraEstadoType::APROVE_JEFE_OPERACIONES,
                    MuestraEstadoType::PRODUCED,
                ],
            ],
        ],
        MuestraEstadoType::SET_PRICE->value => [
            [
                'message' => 'La muestra ya fue aprobada por el Jefe Comercial.',
                'targets' => [
                    MuestraEstadoType::APROVE_COORDINADOR,
                    MuestraEstadoType::APROVE_JEFE_COMERCIAL,
                ],
            ],
            [
                'message' => 'Se requiere de aprobación del Jefe de Operaciones.',
                'targets' => [
                    MuestraEstadoType::PRODUCED,
                ],
            ],
        ],
        MuestraEstadoType::APROVE_JEFE_OPERACIONES->value => [
            [
                'message' => 'La muestra ya fue aprobada por el Jefe de Operaciones.',
                'targets' => [
                    MuestraEstadoType::APROVE_COORDINADOR,
                    MuestraEstadoType::APROVE_JEFE_COMERCIAL,
                    MuestraEstadoType::SET_PRICE,
                    MuestraEstadoType::APROVE_JEFE_OPERACIONES
                ],
            ],
        ],
        MuestraEstadoType::PRODUCED->value => [
            [
                'message' => 'La muestra ya fue producida.',
                'targets' => [
                    MuestraEstadoType::APROVE_COORDINADOR,
                    MuestraEstadoType::APROVE_JEFE_COMERCIAL,
                    MuestraEstadoType::SET_PRICE,
                    MuestraEstadoType::APROVE_JEFE_OPERACIONES,
                    MuestraEstadoType::PRODUCED
                ],
            ]
        ],
    ];

    private function assertValidTransition(Muestras $muestra, MuestraEstadoType $nextState)
    {
        $currentStateVal = $muestra->currentStatus?->type?->value;

        $allowedTransitions = self::TRANSITIONS[$currentStateVal] ?? [];

        if (in_array($nextState, $allowedTransitions, true))
            return;

        $errorMessage = null;

        if (isset(self::TRANSITION_ERROR_MESSAGES[$currentStateVal])) {
            foreach (self::TRANSITION_ERROR_MESSAGES[$currentStateVal] as $group) {
                if (in_array($nextState, $group['targets'], true)) {
                    $errorMessage = $group['message'];
                    break;
                }
            }
        }

        if (!$errorMessage) {
            $currentLabel = $currentStateVal ?? 'ninguno';
            $errorMessage = "Transición inválida: no se puede pasar de '$currentLabel' a '{$nextState->value}'.";
        }

        throw new \LogicException($errorMessage);
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

    private function verifyIsActive(Muestras $muestra, ?string $message = null)
    {
        if (!$muestra->isActive())
            throw new \LogicException($message ?? "No se puede realizar esta operación. La muestra con ID: {$muestra->id} esta inhabilitada.");
    }

}