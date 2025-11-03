<?php

namespace App\Application\Services\Reports\VentasReport;

use App\Application\DTOs\Reports\Ventas\ReportGeneralDto;
use App\Application\DTOs\Reports\Ventas\ReportGeneralType;
use App\Application\DTOs\Reports\Ventas\ReportProductosDto;
use App\Application\DTOs\Reports\Ventas\ReportProvinciasDto;
use App\Application\DTOs\Reports\Ventas\ReportVisitadorasDto;
use App\Application\Services\Reports\ReportBaseService;
use App\Domain\Interfaces\ReportsRepositoryInterface;
use App\Shared\Helpers\GetPercentageHelper;
use Carbon\Carbon;

class VentasReportService extends ReportBaseService
{
    protected string $cachePrefix = 'ventas_report_';
    public function __construct(protected ReportsRepositoryInterface $repo)
    {
    }
    public function createInitialReport(): array
    {
        return [
            'generalReport' => $this->getGeneralReport()->toArray(),
            'visitadorasReport' => $this->getVisitadorasReport()->toArray(),
            'productosReport' => $this->getProductosReport()->toArray(),
            'provinciasReport' => $this->getProvinciasReport()->toArray(),
        ];
    }
    public function getGeneralReport(array $filters = []): ReportGeneralDto
    {
        $month = $filters['month'] ?? 0;
        $year = $filters['year'] ?? now()->year;
        $isDaily = $month > 0;
        $rawData = $this->repo->getVentasGeneralReport($month, $year);
        $dataMap = $rawData->keyBy('period')->mapWithKeys(function ($item) {
            return [
                $item->period => [
                    'total_amount' => (float) $item->total_amount,
                    'total_pedidos' => (int) $item->total_pedidos,
                ]
            ];
        });

        if ($isDaily) {
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $range = range(1, $daysInMonth);
            $labels = array_map(fn($d) => str_pad($d, 2, '0', STR_PAD_LEFT), $range);
        } else {
            $range = range(1, 12);
            $labels = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        }

        $totalAmount = 0;
        $totalPedidos = 0;
        foreach ($range as $index => $period) {
            $exists = $dataMap->has($period);
            $amount = $exists ? $dataMap[$period]['total_amount'] : 0;
            $pedidos = $exists ? $dataMap[$period]['total_pedidos'] : 0;
            $completeData[] = [
                'label' => $labels[$index],
                'total_amount' => $amount,
                'total_pedidos' => $pedidos,
            ];
            $totalAmount += $amount;
            $totalPedidos += $pedidos;
        }

        $periodLabel = $isDaily ? sprintf('%02d-%d', $month, $year) : (string) $year;

        $average = count($completeData) > 0 ? $totalAmount / count($completeData) : 0;

        return new ReportGeneralDto(
            $isDaily ? ReportGeneralType::DAILY : ReportGeneralType::MONTHLY,
            $periodLabel,
            $totalAmount,
            $totalPedidos,
            $average,
            $completeData,
            compact('month', 'year')
        );
    }
    public function getVisitadorasReport(array $filters = []): ReportVisitadorasDto
    {
        $start_date = Carbon::parse($filters['start_date'] ?? now()->startOfMonth())->startOfDay();
        $end_date = Carbon::parse($filters['end_date'] ?? now())->endOfDay();

        $data = $this->repo->getVentasVisitadorasReport($start_date, $end_date);

        $totalAmount = $data->sum('total_amount') ?? 0;
        $totalPedidos = $data->sum('total_pedidos') ?? 0;
        $topVisitadora = $data->sortByDesc('total_amount')->first()->visitadora ?? 'No disponible';

        $data = $data->map(function ($item) use ($totalAmount, $totalPedidos) {
            return [
                'visitadora' => $item->visitadora,
                'total_amount' => $item->total_amount,
                'total_pedidos' => $item->total_pedidos,
                'pedidos_percentage' => GetPercentageHelper::calculate($item->total_pedidos, $totalPedidos),
                'amount_percentage' => GetPercentageHelper::calculate($item->total_amount, $totalAmount),
            ];
        })->toArray();

        return new ReportVisitadorasDto(
            $totalAmount,
            $totalPedidos,
            $topVisitadora,
            $data,
            compact('start_date', 'end_date')
        );
    }
    public function getProductosReport(array $filters = []): ReportProductosDto
    {
        $start_date = Carbon::parse($filters['start_date'] ?? now()->startOfMonth())->startOfDay();
        $end_date = Carbon::parse($filters['end_date'] ?? now())->endOfDay();
        $numberOfDays = $start_date->diffInDays($end_date) + 1;

        $data = $this->repo->getVentasProductosReport($start_date, $end_date);

        $totalGroupedProducts = $data->count();
        $totalProducts = $data->sum('total_products');
        $totalAmount = $data->sum('total_amount');
        $averageProductsPerDay = round($totalProducts / $numberOfDays, 2);

        $data = $data->map(function ($item) use ($totalAmount) {
            return [
                'product' => $item->product,
                'total_amount' => $item->total_amount,
                'total_products' => $item->total_products,
                'average_price_per_unit' => $item->total_products > 0 ? round($item->total_amount / $item->total_products, 2) : 0,
                'percentage_amount' => GetPercentageHelper::calculate($item->total_amount, $totalAmount)
            ];
        })->toArray();

        return new ReportProductosDto(
            $totalGroupedProducts,
            $totalProducts,
            $totalAmount,
            $averageProductsPerDay,
            $data,
            compact('start_date', 'end_date')
        );
    }

    public function getProvinciasReport(array $filters = []): ReportProvinciasDto
    {
        $start_date = Carbon::parse($filters['start_date'] ?? now()->startOfMonth())->startOfDay();
        $end_date = Carbon::parse($filters['end_date'] ?? now())->endOfDay();

        $maps = $this->getMaps();

        $rows = $this->repo->getRawDataGeoVentas($start_date, $end_date);

        $provinciasData = $rows->reduce(function ($carry, $row) use ($maps) {
            $distrito = $this->identifiedDistrito($row->district, $maps);

            if (!$distrito) {
                return $carry;
            }

            $label = $this->resolveLabel($distrito);

            //Si no tiene asignado, se coloca los valores por defecto 0.0 y 0
            $carry[$label] ??= ['total_amount' => 0.0, 'total_pedidos' => 0];
            $carry[$label]['total_amount'] += (float) $row->total_amount;
            $carry[$label]['total_pedidos'] += (float) $row->total_pedidos;
            return $carry;
        }, []);

        $totalAmount = array_sum(array_column($provinciasData, 'total_amount'));
        $totalPedidos = array_sum(array_column($provinciasData, 'total_pedidos'));

        $data = collect($provinciasData)->map(function ($item, $provincia) use ($totalAmount, $totalPedidos) {
            return [
                'provincia' => $provincia,
                'total_amount' => $item['total_amount'],
                'total_pedidos' => $item['total_pedidos'],
                'percentage_amount' => GetPercentageHelper::calculate($item['total_amount'], $totalAmount),
                'percentage_pedidos' => GetPercentageHelper::calculate($item['total_pedidos'], $totalPedidos),
            ];
        })->values()->toArray();

        return new ReportProvinciasDto(
            $totalAmount,
            $totalPedidos,
            $data,
            compact('start_date', 'end_date')
        );
    }

    public function getDetailsPedidosByDepartamento(array $filters = []): ReportProvinciasDto
    {
        if (!isset($filters['departamento'])) {
            throw new \InvalidArgumentException("El parámetro 'departamento' es obligatorio para obtener pedidos detallados.");
        }

        $start_date = Carbon::parse($filters['start_date'] ?? now()->startOfMonth())->startOfDay();
        $end_date = Carbon::parse($filters['end_date'] ?? now())->endOfDay();
        $departamento = $filters['departamento'];

        $maps = $this->getMaps();

        $pedidos = $this->repo->getRawDataGeoVentasDetails($start_date, $end_date);

        $data = $pedidos->map(function ($item) use ($maps, $departamento) {
            $distrito = $this->identifiedDistrito($item->district, $maps);
            if (!$distrito) {
                return null;
            }

            $label = $this->resolveLabel($distrito);

            if ($departamento !== $label) {
                return null;
            }

            return [
                'id' => $item->id,
                'total_amount' => $item->total_amount,
                'distrito' => $item->district,
                'departamento' => $label,
                'created_by' => $item->created_by,
                'created_at' => $item->created_at,
            ];
        })->filter()->values()->toArray();

        return new ReportProvinciasDto(
            array_sum(array_column($data, 'total_amount')),
            count($data),
            $data,
            compact('start_date', 'end_date')
        );
    }
    private function buildNormalizeMap(array $items, callable $keyGenerator, callable $valueGenerator): array
    {
        $map = [];
        foreach ($items as $item) {
            $key = $keyGenerator($item);
            $value = $valueGenerator($item);
            if ($value !== null) {
                $map[$key] = $value;
            }
        }
        return $map;
    }
    private function normalizeUbicationText(string $text): string
    {
        $outputText = strtolower(trim($text));
        $outputText = str_replace(['\\', '/', '-', ',', ';', '|', ':'], ' ', $outputText);
        $outputText = str_replace(['provincia', 'distrito', 'departamento', 'depto', 'dpto', 'dept.', 'prov.', 'dpto.', ' region '], ' ', $outputText);

        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT', $outputText);
        $outputText = ($converted !== false) ? $converted : $outputText;

        $outputText = preg_replace(['/[^a-z0-9 ]+/i', '/\s+/'], ' ', $outputText);
        return trim($outputText);
    }
    private function getMaps(): array
    {
        $departamentosMap = $this->buildNormalizeMap(
            $this->repo->getDepartamentosForMap()->toArray(),
            fn($item) => $this->normalizeUbicationText($item['name']),
            fn($item) => $item['name']
        );
        $provinciasMap = $this->buildNormalizeMap(
            $this->repo->getProvinciasForMap()->toArray(),
            fn($item) => $this->normalizeUbicationText($item['name']),
            fn($item) => $item['name']
        );
        $provinciaToDepartamentoMap = $this->buildNormalizeMap(
            $this->repo->getProvinciasWithDepartamentoForMap()->toArray(),
            fn($item) => $this->normalizeUbicationText($item['name']),
            fn($item) => $item['departamento']['name'] ?? null
        );
        $distritoToDepartamentoMap = $this->buildNormalizeMap(
            $this->repo->getDistritosWithProvinciaAndDepartamentoForMap()->toArray(),
            fn($item) => $this->normalizeUbicationText($item['name']),
            fn($item) => $item['provincia']['departamento']['name'] ?? null
        );
        $distritoToProvinciaMap = $this->buildNormalizeMap(
            $this->repo->getDistritosWithProvinciaForMap()->toArray(),
            fn($item) => $this->normalizeUbicationText($item['name']),
            fn($item) => $item['provincia']['name'] ?? null
        );

        return [
            'departamentos' => $departamentosMap,
            'provincias' => $provinciasMap,
            'provincia_to_departamento' => $provinciaToDepartamentoMap,
            'distrito_to_departamento' => $distritoToDepartamentoMap,
            'distrito_to_provincia' => $distritoToProvinciaMap
        ];
    }
    private function isLimaOrCallao(string $departamento)
    {
        $normalizedDepartamento = $this->normalizeUbicationText($departamento);

        // Distritos de Lima y Callao
        $excludedDistricts = [
            // Distritos de Lima
            'ancon',
            'ate',
            'barranco',
            'brena',
            'carabayllo',
            'chaclacayo',
            'chorrillos',
            'cieneguilla',
            'comas',
            'el agustino',
            'independencia',
            'jesus maria',
            'la molina',
            'la victoria',
            'lima',
            'lince',
            'los olivos',
            'lurigancho',
            'lurin',
            'magdalena del mar',
            'miraflores',
            'pachacamac',
            'pucusana',
            'pueblo libre',
            'puente piedra',
            'punta hermosa',
            'punta negra',
            'rimac',
            'san bartolo',
            'san borja',
            'san isidro',
            'san juan de lurigancho',
            'san juan de miraflores',
            'san luis',
            'san martin de porres',
            'san miguel',
            'santa anita',
            'santa maria del mar',
            'santa rosa',
            'santiago de surco',
            'surquillo',
            'villa el salvador',
            'villa maria del triunfo',
            // Distritos del Callao
            'bellavista',
            'callao',
            'carmen de la legua reynoso',
            'la perla',
            'la punta',
            'ventanilla',
            // Provincias de Lima
            'lima metropolitana',
            'barranca',
            'cajatambo',
            'canta',
            'canete',
            'huaral',
            'huarochiri',
            'huaura',
            'oyon',
            'yauyos',
            // Departamentos
            'lima',
            'callao'
        ];
        // Verificación directa
        if (in_array($normalizedDepartamento, $excludedDistricts, true)) {
            return true;
        }
        // Verificación de contención para casos como "lima san martin de porres"
        foreach ($excludedDistricts as $distrito) {
            if (strpos($normalizedDepartamento, $distrito) !== false || strpos($distrito, $normalizedDepartamento) !== false) {
                return true;
            }
        }
        return false;
    }
    private function findCoincidenceInArray(string $unnormalizedKey, array $array, bool $allowPartialCoincidence = false): ?string
    {
        $normalizedKey = $this->normalizeUbicationText($unnormalizedKey);
        // Coincidencia exacta
        if (isset($array[$normalizedKey])) {
            return $array[$normalizedKey];
        }
        // Para casos como: "lima metropolitana san isidro"
        if ($allowPartialCoincidence) {
            foreach ($array as $key => $value) {
                if (strpos($normalizedKey, $key) !== false || strpos($key, $normalizedKey) !== false) {
                    return $value;
                }
            }
        }
        return null;
    }
    private function findCoincidenceWithFallBack(string $unnormalizedKey, array $array)
    {
        return $this->findCoincidenceInArray($unnormalizedKey, $array, false)
            ?? $this->findCoincidenceInArray($unnormalizedKey, $array, true);
    }
    private function identifiedDistrito(string $text, array $maps): ?array
    {
        $normalizedText = $this->normalizeUbicationText($text);

        if ($this->isLimaOrCallao($normalizedText)) {
            return null;
        }

        $departamento = null;
        $provincia = null;

        $distrito = $this->findCoincidenceWithFallBack($normalizedText, array_keys($maps['distrito_to_departamento']));

        if ($distrito) {
            $departamento = $maps['distrito_to_departamento'][$distrito] ?? null;
            $provincia = $maps['distrito_to_provincia'][$distrito] ?? null;
        }

        if (!$departamento) {
            $provinciaNormalized = $this->findCoincidenceWithFallBack($normalizedText, array_keys($maps['provincia_to_departamento']));

            if ($provinciaNormalized) {
                $departamento = $maps['provincia_to_departamento'][$provinciaNormalized] ?? null;
                $provincia = $maps['provincias'][$provinciaNormalized] ?? $provinciaNormalized;
            } else {
                $departamento = $this->findCoincidenceWithFallBack($normalizedText, $maps['departamentos']);
            }
        }

        if ($departamento && $this->isLimaOrCallao($departamento)) {
            return null;
        }

        return compact('departamento', 'provincia');
    }
    private function resolveLabel(array $ubicacion): string
    {
        return $ubicacion['departamento'] ?? 'No identificado';
    }
}
