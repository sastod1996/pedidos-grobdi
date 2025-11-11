<?php

namespace App\Console\Commands;

use App\Infrastructure\Jobs\MigrateMuestrasEstadosJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestMigrateMuestrasJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:migrate-muestras-job {--chunk=1000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba el Job de migración en modo síncrono';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $chunkSize = (int) $this->option('chunk');
        $minId = DB::table('muestras')->min('id');
        $maxId = DB::table('muestras')->max('id');

        if (!$minId) {
            $this->error('No hay muestras.');
            return;
        }

        $this->info("Rango: {$minId} - {$maxId} (chunk: {$chunkSize})");

        $start = microtime(true);

        for ($i = $minId; $i <= $maxId; $i += $chunkSize) {
            $startId = $i;
            $endId = $i + $chunkSize - 1;

            $this->info("Ejecutando Job para IDs {$startId}-{$endId}...");

            // Ejecutar Job de forma síncrona (como si fuera en cola)
            (new MigrateMuestrasEstadosJob($startId, $endId))->handle();
        }

        $elapsed = round(microtime(true) - $start, 2);
        $totalEstados = DB::table('muestras_estados')->count();

        $this->info("Finalizado en {$elapsed} segundos.");
        $this->info("Total de estados creados: {$totalEstados}");
    }
}
