<?php

namespace App\Jobs;

use App\Models\MuestrasEstado;
use App\Models\User;
use App\MuestraEstadoType;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class MigrateMuestrasEstadosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $startId, public int $endId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $userId = User::where('role_id', 1)->first()?->id;

        if (!$userId) {
            throw new Exception('No se encontró ningún usuario para asignar como responsable de la migración.');
        }

        $muestras = DB::table('muestras')
            ->whereBetween('id', [$this->startId, $this->endId])
            ->select(
                'id',
                'aprobado_coordinadora',
                'aprobado_jefe_comercial',
                'aprobado_jefe_operaciones',
                'precio',
                'lab_state',
                'updated_at',
            )->get();

        $batch = [];

        foreach ($muestras as $muestra) {
            $events = [];

            if ($muestra->aprobado_coordinadora) {
                $events[] = $this->buildEvent($muestra, $userId, MuestraEstadoType::APROVE_COORDINADOR, -15);
            }

            if ($muestra->aprobado_jefe_comercial) {
                $events[] = $this->buildEvent($muestra, $userId, MuestraEstadoType::APROVE_JEFE_COMERCIAL, -12);
            }

            if ($muestra->precio !== null) {
                $events[] = $this->buildEvent($muestra, $userId, MuestraEstadoType::SET_PRICE, -8);
            }

            if ($muestra->aprobado_jefe_operaciones) {
                $events[] = $this->buildEvent($muestra, $userId, MuestraEstadoType::APROVE_JEFE_OPERACIONES, -4);
            }

            if ($muestra->lab_state) {
                $events[] = $this->buildEvent($muestra, $userId, MuestraEstadoType::PRODUCED);
            }

            usort($events, fn($a, $b) => $a['created_at'] <=> $b['created_at']);
            $batch = array_merge($batch, $events);
        }

        $batchSize = 1000;
        foreach (array_chunk($batch, $batchSize) as $chunk) {
            $chunk = array_map(function ($item) {
                $item['type'] = $item['type']->value;
                return $item;
            }, $chunk);

            MuestrasEstado::insert($chunk);
        }

        info("Migrados {$muestras->count()} muestras (IDs {$this->startId}-{$this->endId})");
    }

    private function buildEvent($muestra, int $userId, MuestraEstadoType $type, ?int $minutesOffset = null): array
    {
        $createdAt = $minutesOffset === null
            ? $muestra->updated_at
            : $muestra->updated_at->copy()->addMinutes($minutesOffset);

        return [
            'muestra_id' => $muestra->id,
            'user_id' => $userId,
            'type' => $type,
            'comment' => 'Migración inicial 04-11-2025.',
            'created_at' => $createdAt,
        ];
    }
}
