<?php

namespace App\Application\Services\Reportes;

use App\Models\Pedidos;
use App\Models\Departamento;
use App\Models\Provincia;
use App\Models\Distrito;
use App\Application\DTOs\Reportes\VentasDTO;

class GeoVentasService
{
    /**
     * Devuelve datos de ventas agregados por provincia o departamento
     * Filtros: fecha_inicio_provincia, fecha_fin_provincia, anio_general, mes_general, agrupacion
     */
    public function getGeoVentas(array $filtros = []): array
    {
        $agrupacion = strtolower($filtros['agrupacion'] ?? 'departamento');
        if (!in_array($agrupacion, ['provincia', 'departamento'])) {
            $agrupacion = 'departamento';
        }

        // Obtener datos crudos desde el DTO usando método estático (sin crear instancia)
        $rows = VentasDTO::getDatosCrudosGeoVentas($filtros);

        $mapDepartamentos = $this->cargarMapaDepartamentos();
        $mapProvincias = $this->cargarMapaProvincias();
        $mapProvinciaToDep = $this->cargarMapaProvinciaToDepartamento();
        $mapDistritoToDep = $this->cargarMapaDistritoToDepartamento();

        // Cargar mapas adicionales para jerarquía completa
        $mapDistritoToProvincia = $this->cargarMapaDistritoToProvincia();

        // Preparar mapas para identificación
        $mapas = [
            'departamentos' => $mapDepartamentos,
            'provincias' => $mapProvincias,
            'prov_to_dep' => $mapProvinciaToDep,
            'distrito_to_dep' => $mapDistritoToDep,
            'distrito_to_prov' => $mapDistritoToProvincia
        ];

        $sumas = [];
        foreach ($rows as $row) {
            // Identificar ubicación (retorna null si es Lima/Callao)
            $ubicacion = $this->identificarUbicacion((string) $row->district, $mapas);
            
            if ($ubicacion === null) {
                // Es Lima/Callao - EXCLUIR COMPLETAMENTE
                continue;
            }
            
            // Determinar etiqueta según agrupación
            $label = null;
            if ($ubicacion['departamento']) {
                if ($agrupacion === 'provincia' && $ubicacion['provincia']) {
                    $label = $ubicacion['provincia'];
                } else {
                    $label = $ubicacion['departamento'];
                }
            }

            // Si no se identificó, marcar como "No identificado"
            if (!$label) { 
                $label = 'No identificado'; 
            }

            // Agrupar los datos
            if (!isset($sumas[$label])) { 
                $sumas[$label] = ['ventas'=>0.0,'pedidos'=>0]; 
            }
            $sumas[$label]['ventas'] += (float) $row->ventas;
            $sumas[$label]['pedidos'] += (int) $row->pedidos;
        }

        uasort($sumas, fn($a,$b) => $b['ventas'] <=> $a['ventas']);
        $labels = array_keys($sumas);
        $vals = array_values($sumas);
        $ventas = array_map(fn($v)=>round($v['ventas'],2), $vals);
        $pedidos = array_map(fn($v)=>(int)$v['pedidos'], $vals);
        $total = array_sum($ventas);
        $porcentaje = array_map(fn($v) => $total>0 ? round(($v/$total)*100,1) : 0, $ventas);

        return [
            'agrupacion' => $agrupacion,
            'labels' => $labels,
            'ventas' => $ventas,
            'porcentaje' => $porcentaje,
            'pedidos' => $pedidos,
            'total_ventas' => $total,
            'total_pedidos' => array_sum($pedidos),
            'titulo' => $agrupacion === 'provincia' ? 'Ventas por Provincia' : 'Ventas por Departamento',
        ];
    }

    private function cargarMapaDepartamentos(): array
    {
        $map = [];
        foreach (Departamento::select('name')->get() as $dep) {
            $map[$this->normalizarUbigeo($dep->name)] = $dep->name;
        }
        return $map;
    }

    private function cargarMapaProvincias(): array
    {
        $map = [];
        foreach (Provincia::select('name')->get() as $prov) {
            $map[$this->normalizarUbigeo($prov->name)] = $prov->name;
        }
        return $map;
    }

    private function cargarMapaProvinciaToDepartamento(): array
    {
        $map = [];
        $provs = Provincia::select('id','name','departamento_id')->with(['departamento:id,name'])->get();
        foreach ($provs as $p) {
            $key = $this->normalizarUbigeo($p->name);
            if ($p->departamento) { $map[$key] = $p->departamento->name; }
        }
        return $map;
    }

    private function cargarMapaDistritoToDepartamento(): array
    {
        $map = [];
        $dists = Distrito::select('id','name','provincia_id')->with(['provincia:id,name,departamento_id','provincia.departamento:id,name'])->get();
        foreach ($dists as $d) {
            $key = $this->normalizarUbigeo($d->name);
            $dep = $d->provincia && $d->provincia->departamento ? $d->provincia->departamento->name : null;
            if ($dep) { $map[$key] = $dep; }
        }
        return $map;
    }

    private function cargarMapaDistritoToProvincia(): array
    {
        $map = [];
        $dists = Distrito::select('id','name','provincia_id')->with(['provincia:id,name'])->get();
        foreach ($dists as $d) {
            $key = $this->normalizarUbigeo($d->name);
            $prov = $d->provincia ? $d->provincia->name : null;
            if ($prov) { $map[$key] = $prov; }
        }
        return $map;
    }

    /**
     * Verificar si una ubicación pertenece a Lima o Callao
     * Incluye distritos, provincias y departamentos de Lima/Callao
     */
    private function esLimaOCallao(string $texto): bool
    {
        $norm = $this->normalizarUbigeo($texto);
        
        // Lista completa de distritos de Lima y Callao que deben ser excluidos
        $ubicacionesLimaCallao = [
            // Distritos de Lima
            'ancon', 'ate', 'barranco', 'brena', 'carabayllo', 'chaclacayo', 'chorrillos', 'cieneguilla', 
            'comas', 'el agustino', 'independencia', 'jesus maria', 'la molina', 'la victoria', 'lima', 
            'lince', 'los olivos', 'lurigancho', 'lurin', 'magdalena del mar', 'miraflores', 'pachacamac', 
            'pucusana', 'pueblo libre', 'puente piedra', 'punta hermosa', 'punta negra', 'rimac', 
            'san bartolo', 'san borja', 'san isidro', 'san juan de lurigancho', 'san juan de miraflores', 
            'san luis', 'san martin de porres', 'san miguel', 'santa anita', 'santa maria del mar', 
            'santa rosa', 'santiago de surco', 'surquillo', 'villa el salvador', 'villa maria del triunfo',
            
            // Distritos del Callao
            'bellavista', 'callao', 'carmen de la legua reynoso', 'la perla', 'la punta', 'ventanilla',
            
            // Provincias de Lima
            'lima metropolitana', 'barranca', 'cajatambo', 'canta', 'canete', 'huaral', 'huarochiri', 
            'huaura', 'oyon', 'yauyos',
            
            // Departamentos
            'lima', 'callao'
        ];
        
        // Verificación directa
        if (in_array($norm, $ubicacionesLimaCallao, true)) {
            return true;
        }
        
        // Verificación de contención para casos como "lima san martin de porres"
        foreach ($ubicacionesLimaCallao as $ubicacion) {
            if (strpos($norm, $ubicacion) !== false || strpos($ubicacion, $norm) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Identifica la ubicación geográfica de un texto, excluyendo Lima y Callao
     * Retorna array con departamento y provincia identificados, o null si es Lima/Callao
     */
    private function identificarUbicacion(string $texto, array $mapas): ?array
    {
        $norm = $this->normalizarUbigeo($texto);
        
        // Excluir completamente si es de Lima o Callao
        if ($this->esLimaOCallao($norm)) {
            return null;
        }
        
        $departamento = null;
        $provincia = null;
        
        // 1. Intentar identificar como distrito
        $distrito = $this->encontrarCoincidencia($norm, array_keys($mapas['distrito_to_dep']), false);
        if (!$distrito) {
            $distrito = $this->encontrarCoincidencia($norm, array_keys($mapas['distrito_to_dep']), true);
        }
        
        if ($distrito) {
            $departamento = $mapas['distrito_to_dep'][$distrito] ?? null;
            $provincia = $mapas['distrito_to_prov'][$distrito] ?? null;
            
            // Verificar que el departamento no sea Lima/Callao
            if ($departamento && $this->esLimaOCallao($departamento)) {
                return null;
            }
        } else {
            // 2. Intentar identificar como provincia
            $provNorm = $this->encontrarCoincidencia($norm, array_keys($mapas['prov_to_dep']), false);
            if (!$provNorm) {
                $provNorm = $this->encontrarCoincidencia($norm, array_keys($mapas['prov_to_dep']), true);
            }
            
            if ($provNorm) {
                $departamento = $mapas['prov_to_dep'][$provNorm] ?? null;
                $provincia = $this->encontrarCoincidencia($provNorm, $mapas['provincias'], false);
                
                // Verificar que el departamento no sea Lima/Callao
                if ($departamento && $this->esLimaOCallao($departamento)) {
                    return null;
                }
            } else {
                // 3. Intentar identificar como departamento
                $departamento = $this->encontrarCoincidencia($norm, $mapas['departamentos'], false);
                if (!$departamento) {
                    $departamento = $this->encontrarCoincidencia($norm, $mapas['departamentos'], true);
                }
                
                // Verificar que el departamento no sea Lima/Callao
                if ($departamento && $this->esLimaOCallao($departamento)) {
                    return null;
                }
            }
        }
        
        return [
            'departamento' => $departamento,
            'provincia' => $provincia
        ];
    }

    private function normalizarUbigeo(string $texto): string
    {
        $tLower = strtolower(trim($texto));
        $t = $tLower;
        $t = str_replace(['\\', '/', '-', ',', ';', '|', ':'], ' ', $t);
        $t = str_replace(['provincia', 'distrito', 'departamento', 'depto', 'dpto', 'dept.', 'prov.', 'dpto.', ' region '], ' ', $t);
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT', $t);
        if ($converted !== false) { $t = $converted; } else { $t = $tLower; }
        $t = preg_replace('/[^a-z0-9 ]+/i', ' ', $t);
        $t = preg_replace('/\s+/', ' ', $t);
        return trim($t);
    }

    private function tokenizarUbigeo(string $normalizado): array
    {
        $parts = preg_split('/\s+/', $normalizado);
        return array_values(array_filter(array_unique($parts), fn($p) => strlen($p) > 1));
    }

    private function tokenizarConBigramas(string $normalizado): array
    {
        $tokens = $this->tokenizarUbigeo($normalizado);
        $out = $tokens;
        for ($i = 0; $i < count($tokens) - 1; $i++) {
            $out[] = $tokens[$i] . ' ' . $tokens[$i+1];
        }
        $out = array_values(array_filter(array_unique($out), fn($p) => strlen($p) > 1));
        usort($out, function($a,$b){ return strlen($b) <=> strlen($a); });
        return $out;
    }

    private function encontrarCoincidencia(string $needle, array $map, bool $permitirParcial = false): ?string
    {
        $n = $this->normalizarUbigeo($needle);
        
        // 1. Coincidencia exacta completa
        if (isset($map[$n])) { 
            return $map[$n]; 
        }
        
        // 2. Si se permite parcial, buscar dentro del texto (para casos como "lima metropolitana san isidro")
        if ($permitirParcial) {
            foreach ($map as $k => $label) {
                // Buscar si el nombre está contenido en el texto o viceversa
                if (strpos($n, $k) !== false || strpos($k, $n) !== false) {
                    return $label;
                }
            }
        }
        
        return null;
    }

    private function encontrarClaveCoincidencia(string $needle, array $keys, bool $permitirParcial = false): ?string
    {
        $n = $this->normalizarUbigeo($needle);
        
        // 1. Coincidencia exacta completa
        if (in_array($n, $keys, true)) { 
            return $n; 
        }
        
        // 2. Si se permite parcial, buscar dentro del texto
        if ($permitirParcial) {
            foreach ($keys as $k) {
                // Buscar si el nombre está contenido en el texto o viceversa
                if (strpos($n, $k) !== false || strpos($k, $n) !== false) {
                    return $k;
                }
            }
        }
        
        return null;
    }

    private function generarTablaUbigeoHtml(array $labels, array $ventas, array $porcentaje, array $pedidos): string
    {
        if (empty($labels)) {
            return '<tr><td colspan="5" class="text-center py-4 text-muted">Sin datos para el rango seleccionado</td></tr>';
        }
        $html = '';
        for ($i = 0; $i < count($labels); $i++) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($labels[$i]) . '</td>';
            $html .= '<td class="text-end">S/ ' . number_format($ventas[$i] ?? 0, 2) . '</td>';
            $html .= '<td class="text-center">' . number_format($porcentaje[$i] ?? 0, 1) . '%</td>';
            $html .= '<td class="text-center">' . number_format($pedidos[$i] ?? 0) . '</td>';
            $html .= '<td class="text-center">';
            $html .= '<button class="btn btn-sm btn-outline-primary ver-pedidos-departamento" ';
            $html .= 'data-departamento="' . htmlspecialchars($labels[$i]) . '" ';
            $html .= 'title="Ver pedidos detallados de ' . htmlspecialchars($labels[$i]) . '">';
            $html .= '<i class="fas fa-eye"></i>';
            $html .= '</button>';
            $html .= '</td>';
            $html .= '</tr>';
        }
        return $html;
    }

    /**
     * Obtiene pedidos detallados por departamento
     * 
     * @param string $departamento Nombre del departamento o 'No identificado'
     * @param array $filtros Filtros aplicados
     * @return array Pedidos detallados con información completa
     */
    public function getPedidosDetallados(string $departamento, array $filtros = []): array
    {
        // Obtener datos crudos desde el DTO usando método estático (sin crear instancia)
        $pedidos = VentasDTO::getDatosCrudosPedidosDetallados($filtros);

        // Cargar mapas para identificación
        $mapDepartamentos = $this->cargarMapaDepartamentos();
        $mapProvincias = $this->cargarMapaProvincias();
        $mapProvinciaToDep = $this->cargarMapaProvinciaToDepartamento();
        $mapDistritoToDep = $this->cargarMapaDistritoToDepartamento();
        $mapDistritoToProvincia = $this->cargarMapaDistritoToProvincia();

        // Preparar mapas para identificación
        $mapas = [
            'departamentos' => $mapDepartamentos,
            'provincias' => $mapProvincias,
            'prov_to_dep' => $mapProvinciaToDep,
            'distrito_to_dep' => $mapDistritoToDep,
            'distrito_to_prov' => $mapDistritoToProvincia
        ];

        $pedidosFiltrados = [];
        $agrupacion = strtolower($filtros['agrupacion'] ?? 'departamento');

        foreach ($pedidos as $pedido) {
            // Identificar ubicación (retorna null si es Lima/Callao)
            $ubicacion = $this->identificarUbicacion((string) $pedido->distrito_original, $mapas);
            
            if ($ubicacion === null) {
                // Es Lima/Callao - EXCLUIR COMPLETAMENTE
                continue;
            }
            
            // Determinar etiqueta según agrupación
            $labelIdentificado = null;
            if ($ubicacion['departamento']) {
                if ($agrupacion === 'provincia' && $ubicacion['provincia']) {
                    $labelIdentificado = $ubicacion['provincia'];
                } else {
                    $labelIdentificado = $ubicacion['departamento'];
                }
            }

            // Si no se identificó, marcar como "No identificado"
            if (!$labelIdentificado) { 
                $labelIdentificado = 'No identificado'; 
            }

            // Filtrar por departamento solicitado
            if ($departamento === $labelIdentificado) {
                $pedidosFiltrados[] = [
                    'id' => $pedido->id,
                    'fecha_pedido' => $pedido->fecha_pedido,
                    'total' => (float) $pedido->total,
                    'distrito_original' => $pedido->distrito_original,
                    'departamento_identificado' => $labelIdentificado,
                    'visitadora' => $pedido->visitadora ?: 'Sin asignar',
                    'email_visitadora' => $pedido->email_visitadora ?: ''
                ];
            }
        }

        return [
            'departamento' => $departamento,
            'total_pedidos' => count($pedidosFiltrados),
            'total_ventas' => array_sum(array_column($pedidosFiltrados, 'total')),
            'pedidos' => $pedidosFiltrados
        ];
    }
}
