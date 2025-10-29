<?php

namespace App\Application\DTOs\Reports\Muestras;

use App\Application\DTOs\Reports\ReportBaseDto;

class ReportGeneralDto extends ReportBaseDto
{
    public function __construct(
        private int $totalMuestras,
        private int $totalQuantity,
        private float $totalAmount,
        private array $groupByTipoFrasco,
        private array $groupByTipoMuestra,
        array $data,
        array $filters = []
    ) {
        parent::__construct($data, $filters);
    }
    protected function getReportData(): array
    {
        return [
            'general_stats' => [
                'total_muestras' => $this->totalMuestras,
                'total_quantity' => $this->totalQuantity,
                'total_amount' => $this->totalAmount,
                'by_tipo_frasco' => $this->groupByTipoFrasco,
                'by_tipo_muestra' => $this->groupByTipoMuestra,
            ],
        ];
    }
}
