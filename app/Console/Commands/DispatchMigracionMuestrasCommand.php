<?php

namespace App\Console\Commands;

use App\Jobs\MigrateMuestrasEstadosJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DispatchMigracionMuestrasCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migracion:muestras-estados';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Despacha jobs para migrar estados de muestras';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minId = DB::table('muestras')->min('id');
        $maxId = DB::table('muestras')->max('id');

        if ($minId === null) {
            $this->info('No hay muestras.');
            return;
        }

        $chunkSize = 1000;
        for ($i = $minId; $i <= $maxId; $i += $chunkSize) {
            $start = $i;
            $end = $i + $chunkSize - 1;
            MigrateMuestrasEstadosJob::dispatch($start, $end)->onQueue('migrations');
        }

        $this->info("Jobs despachados para rango ID {$minId}-{$maxId}");
    }
}
