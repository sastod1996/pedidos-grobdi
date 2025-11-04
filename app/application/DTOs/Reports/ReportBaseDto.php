<?php

namespace App\Application\DTOs\Reports;

use Brick\Money\Money;

/**
 * Parent class for Reports DTOs
 **/
abstract class ReportBaseDto
{
    public function __construct(
        public array $data = [],
        public array $filters = [],
    ) {
    }

    protected function serialize(mixed $value): mixed
    {
        if ($value instanceof Money) {
            return $value->getAmount()->toFloat();
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->serialize($item);
            }
            return $value;
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            return $this->serialize($value->toArray());
        }

        return $value;
    }

    public function toArray(): array
    {
        return array_merge(
            $this->getReportData(),
            [
                'data' => $this->data,
                'filters' => $this->filters,
            ]
        );
    }
    abstract protected function getReportData(): array;
}
