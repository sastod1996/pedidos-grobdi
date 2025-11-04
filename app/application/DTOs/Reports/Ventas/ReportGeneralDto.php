<?php

namespace App\Application\DTOs\Reports\Ventas;

use App\Application\DTOs\Reports\ReportBaseDto;
use Brick\Money\Money;

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
        private Money $totalAmount,
        private int $totalPedidos,
        private Money $averageAmount,
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
            'general_stats' => [
                'total_amount' => $this->totalAmount->getAmount()->__toString(),
                'total_pedidos' => $this->totalPedidos,
                'average_amount' => $this->averageAmount->getAmount()->__toString(),
            ],
        ];
    }
}
