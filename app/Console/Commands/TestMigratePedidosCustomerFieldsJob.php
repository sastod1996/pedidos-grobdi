<?php

namespace App\Console\Commands;

use App\Infrastructure\Jobs\MigratePedidosCustomerFieldsJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestMigratePedidosCustomerFieldsJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:migrate-pedidos-customer-fields-job {--chunk=1000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba el Job de migración de columnas relacionadas a Customer en Pedidos en modo síncrono';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $chunkSize = (int) $this->option('chunk');
        $minId = DB::table('pedidos')->min('id');
        $maxId = DB::table('pedidos')->max('id');

        if ($minId === null) {
            $this->error('No hay pedidos para migrar.');
            return;
        }

        $this->info("Rango de IDs: {$minId} - {$maxId} | Chunk: {$chunkSize}");

        $start = microtime(true);

        for ($i = $minId; $i <= $maxId; $i += $chunkSize) {
            $startId = $i;
            $endId = min($i + $chunkSize - 1, $maxId); // evita pasarte del maxId

            $this->info("Procesando rango {$startId}-{$endId}...");

            // Ejecutar el job de forma síncrona (sin cola)
            (new MigratePedidosCustomerFieldsJob($startId, $endId))->handle();
        }

        $elapsed = round(microtime(true) - $start, 2);
        $totalCustomers = DB::table('customers')->count();
        $pedidosConCustomerId = DB::table('pedidos')->whereNotNull('customer_id')->count();

        $this->info("Migración completada en {$elapsed} segundos.");
        $this->info("Total de clientes creados: {$totalCustomers}");
        $this->info("Pedidos asociados a customer_id: {$pedidosConCustomerId}");
    }
}
