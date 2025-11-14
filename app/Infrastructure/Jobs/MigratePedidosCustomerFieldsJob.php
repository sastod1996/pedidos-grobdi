<?php

namespace App\Infrastructure\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class MigratePedidosCustomerFieldsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 3;
    public int $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $startId, public int $endId)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $uniqueCustomers = DB::table('pedidos')
            ->whereBetween('id', [$this->startId, $this->endId])
            ->where('status', true)
            ->whereNotNull('customerName')
            ->whereNotNull('customerNumber')
            ->select(
                'customerName',
                'customerNumber',
            )
            ->distinct()
            ->get();

        if ($uniqueCustomers->isEmpty()) {
            info("No hay clientes únicos en pedidos (IDs {$this->startId}-{$this->endId})");
            return;
        }

        $customerData = [];
        foreach ($uniqueCustomers as $uc) {
            $customerData[] = [
                'document_type_id' => 1,
                'name' => $uc->customerName,
                'phone' => $uc->customerNumber,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('customers')->upsert(
            $customerData,
            ['name', 'phone'],
            ['updated_at']
        );

        $customerRecords = DB::table('customers')
            ->select('id', 'name', 'phone')
            ->whereIn('name', $uniqueCustomers->pluck('customerName'))
            ->whereIn('phone', $uniqueCustomers->pluck('customerNumber'))
            ->get();

        $customerMap = $customerRecords->mapWithKeys(function ($customer) {
            $key = $customer->name . '|' . $customer->phone;
            return [$key => $customer->id];
        })->toArray();

        $updates = [];
        $pedidosToUpdate = DB::table('pedidos')
            ->whereBetween('id', [$this->startId, $this->endId])
            ->where('status', true)
            ->whereNotNull('customerName')
            ->whereNotNull('customerNumber')
            ->select('id', 'customerName', 'customerNumber')
            ->get();

        foreach ($pedidosToUpdate as $pedido) {
            $key = $pedido->customerName . '|' . $pedido->customerNumber;
            if (isset($customerMap[$key])) {
                $updates[] = [
                    'id' => $pedido->id,
                    'customer_id' => $customerMap[$key],
                ];
            }
        }

        if (!empty($updates)) {
            $chunks = array_chunk($updates, 1000);
            foreach ($chunks as $chunk) {
                // Usamos `CASE` para actualizar múltiples filas en una sola query
                $ids = [];
                $cases = [];
                foreach ($chunk as $update) {
                    $ids[] = $update['id'];
                    $cases[] = "WHEN {$update['id']} THEN {$update['customer_id']}";
                }
                $casesSql = implode(' ', $cases);
                DB::statement("
                    UPDATE pedidos 
                    SET customer_id = (CASE id {$casesSql} END)
                    WHERE id IN (" . implode(',', $ids) . ")
                ");
            }
        }

        info("Migrados " . count($updates) . " pedidos con customer_id (IDs {$this->startId}-{$this->endId})");
    }
}
