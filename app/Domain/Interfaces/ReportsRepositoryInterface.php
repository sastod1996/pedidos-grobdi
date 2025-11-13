<?php

namespace App\Domain\Interfaces;

use Carbon\Carbon;
use Illuminate\Support\Collection;

interface ReportsRepositoryInterface
{
    public function getVentasGeneralReport(int $month, Carbon $startYearDate, Carbon $endYearDate): Collection;
    public function getVentasVisitadorasReport(string $startDate, string $endDate): Collection;
    public function getVentasProductosReport(string $startDate, string $endDate): Collection;
    public function getRutasZonesReport(int $month, int $year, array $distritos): Collection;
    public function getAmountSpentAnuallyByDoctor(Carbon $startDate, Carbon $endDate, int $doctorId): array;
    public function getMostConsumedProductsMonthlyByDoctor(string $startDate, string $endDate, int $doctorId): Collection;
    public function getAmountSpentMonthlyGroupedByTipo(string $startDate, string $endDate, int $doctorId): Collection;
    public function getTopDoctorByAmountInfo(string $startDate, string $endDate): mixed;
    public function getDoctorInfo(int $doctorId): mixed;
    public function getRawDataGeoVentas(string $startDate, string $endDate): Collection;
    public function getRawDataGeoVentasDetails(string $startDate, string $endDate): Collection;
    public function getDepartamentosForMap(): Collection;
    public function getProvinciasForMap(): Collection;
    public function getProvinciasWithDepartamentoForMap(): Collection;
    public function getDistritosWithProvinciaAndDepartamentoForMap(): Collection;
    public function getDistritosWithProvinciaForMap(): Collection;
    public function getRawMuestrasData(string $startDate, string $endDate): Collection;
}
