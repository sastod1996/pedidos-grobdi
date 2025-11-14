<?php

namespace App\Application\DTOs\Reports\Ventas;

use App\Application\DTOs\Reports\ReportBaseDto;

enum ReportGeneralType: string
{
    case MONTHLY = 'mensual';
    case DAILY = 'daily';
}

class ReportGeneralDto extends ReportBaseDto
{
    public function __construct(
        private ReportGeneralType $type,
        private string $period,
        private array $generalStatsByYear,
        array $data,
        array $filters = []
    ) {
        parent::__construct($data, $filters);
    }
    protected function getReportData(): array
    {
        return [
            'chart_info' => [
                'type' => $this->type,
                'period' => $this->period,
            ],
            'general_stats' => $this->generalStatsByYear,
            'data' => $this->data
        ];
    }
}
