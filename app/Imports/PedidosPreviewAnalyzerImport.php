<?php

namespace App\Imports;

use Closure;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Row;

class PedidosPreviewAnalyzerImport implements OnEachRow, WithChunkReading
{
    private Closure $rowHandler;

    private int $chunkSize;

    public function __construct(callable $rowHandler, int $chunkSize = 1000)
    {
        if ($rowHandler instanceof Closure) {
            $this->rowHandler = $rowHandler;
        } else {
            $this->rowHandler = function (...$arguments) use ($rowHandler) {
                return $rowHandler(...$arguments);
            };
        }

        $this->chunkSize = max(100, $chunkSize);
    }

    public function onRow(Row $row)
    {
        ($this->rowHandler)($row->toArray(), $row->getIndex());
    }

    public function chunkSize(): int
    {
        return $this->chunkSize;
    }
}
