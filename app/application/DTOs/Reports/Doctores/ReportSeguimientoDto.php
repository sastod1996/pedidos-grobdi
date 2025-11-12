<?php

namespace App\Application\DTOs\Reports\Doctores;

use App\Application\DTOs\Reports\ReportBaseDto;
use Brick\Money\Money;

class ReportSeguimientoDto extends ReportBaseDto
{
    public function __construct(
        private array $amountIncrease,
        private array $amountDecrease,
        private array $quantityIncrease,
        private array $quantityDecrease,
        private Money $firstAverageAmount,
        private Money $secondAverageAmount,
        private float $firstAverageQuantity,
        private float $secondAverageQuantity,
        array $data,
        array $filters = []
    ) {
        parent::__construct($data, $filters);
    }
    protected function getReportData(): array
    {
        return [
            'general_Stats' => [
                'total_doctores' => count($this->data),
                'averages' => [
                    'first' => [
                        'amount' => $this->firstAverageAmount->getAmount()->__toString(),
                        'quantity' => $this->firstAverageQuantity,
                    ],
                    'second' => [
                        'amount' => $this->secondAverageAmount->getAmount()->__toString(),
                        'quantity' => $this->secondAverageQuantity,
                    ],
                ]
            ],
            'top_stats' => [
                'amount_increase' => $this->amountIncrease,
                'amount_decrease' => $this->amountDecrease,
                'quantity_increase' => $this->quantityIncrease,
                'quantity_decrease' => $this->quantityDecrease,
            ]
        ];
    }
}
