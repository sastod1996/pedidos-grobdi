<?php

namespace App\Console\Commands;

use App\Infrastructure\Jobs\MigratePedidosCustomerFieldsJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DispatchMigracionPedidosCustomerFieldsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migracion:pedidos-customer-fields-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Despacha jobs para migrar los campos de los clientes de Pedidos a la tabla Customers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minId = DB::table('pedidos')->min('id');
        $maxId = DB::table('pedidos')->max('id');

        if ($minId === null) {
            $this->info('No hay pedidos.');
            return;
        }

        $chunkSize = 1000;
        for ($i = $minId; $i <= $maxId; $i += $chunkSize) {
            $start = $i;
            $end = $i + $chunkSize - 1;
            MigratePedidosCustomerFieldsJob::dispatch($start, $end)->onQueue('migrations');
        }

        $this->info("Jobs despachados para rango ID {$minId}-{$maxId}");
    }
}
