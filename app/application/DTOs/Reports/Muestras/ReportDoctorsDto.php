<?php

namespace App\Application\DTOs\Reports\Muestras;

use App\Application\DTOs\Reports\ReportBaseDto;

class ReportDoctorsDto extends ReportBaseDto
{
    public function __construct(
        private bool $isTopDoctor,
        private string $doctor,
        private string $tipoDoctor,
        array $data,
        array $filters = []
    ) {
        parent::__construct($data, $filters);
    }
    protected function getReportData(): array
    {
        return [
            'doctor_info' => [
                'is_top_doctor' => $this->isTopDoctor,
                'doctor' => $this->doctor,
                'tipo_doctor' => $this->tipoDoctor,
            ],
        ];
    }
}
