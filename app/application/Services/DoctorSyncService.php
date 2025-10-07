<?php

namespace App\Application\Services;

use App\Models\Doctor;
use App\Models\Pedidos;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class DoctorSyncService
{
    /**
     * Sincroniza doctores con pedidos basándose en comparación de palabras
     *
     * @return array Resultados de la sincronización
     */
    public function sincronizarDoctoresPedidos(): array
    {
        $resultados = [
            'procesados' => 0,
            'sincronizados' => 0,
            'no_encontrados' => 0,
            'errores' => 0,
            'detalles' => []
        ];

        try {
            // Obtener pedidos sin doctor asignado
            $pedidosSinDoctor = Pedidos::whereNull('id_doctor')
                ->whereNotNull('doctorName')
                ->get();

            $resultados['procesados'] = $pedidosSinDoctor->count();
            
            Log::info("Iniciando sincronización de doctores", [
                'total_pedidos' => $resultados['procesados']
            ]);

            // Obtener todos los doctores activos con name_softlynn
            $doctores = Doctor::where('state', 1)
                ->whereNotNull('name_softlynn')
                ->get();

            foreach ($pedidosSinDoctor as $pedido) {
                try {
                    $doctorEncontrado = $this->buscarDoctorPorNombre(
                        $pedido->doctorName, 
                        $doctores
                    );

                    if ($doctorEncontrado) {
                        $pedido->id_doctor = $doctorEncontrado->id;
                        $pedido->save();

                        $resultados['sincronizados']++;
                        $resultados['detalles'][] = [
                            'pedido_id' => $pedido->id,
                            'order_id' => $pedido->orderId,
                            'doctor_name_pedido' => $pedido->doctorName,
                            'doctor_encontrado' => $doctorEncontrado->name_softlynn,
                            'doctor_id' => $doctorEncontrado->id
                        ];

                        Log::info("Doctor sincronizado", [
                            'pedido_id' => $pedido->id,
                            'order_id' => $pedido->orderId,
                            'doctor_name_pedido' => $pedido->doctorName,
                            'doctor_encontrado' => $doctorEncontrado->name_softlynn
                        ]);
                    } else {
                        $resultados['no_encontrados']++;
                        
                        Log::warning("Doctor no encontrado para pedido", [
                            'pedido_id' => $pedido->id,
                            'order_id' => $pedido->orderId,
                            'doctor_name_pedido' => $pedido->doctorName
                        ]);
                    }
                } catch (\Exception $e) {
                    $resultados['errores']++;
                    
                    Log::error("Error al procesar pedido", [
                        'pedido_id' => $pedido->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info("Sincronización completada", $resultados);
            
        } catch (\Exception $e) {
            $resultados['errores']++;
            
            Log::error("Error general en sincronización", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $resultados;
    }

    /**
     * Busca un doctor comparando palabras entre el nombre del pedido y name_softlynn
     *
     * @param string $nombrePedido Nombre del doctor en el pedido
     * @param Collection $doctores Colección de doctores
     * @return Doctor|null Doctor encontrado o null
     */
    private function buscarDoctorPorNombre(string $nombrePedido, Collection $doctores): ?Doctor
    {
        if (empty(trim($nombrePedido))) {
            return null;
        }

        // Normalizar y obtener palabras del nombre del pedido
        $palabrasPedido = $this->normalizarYObtenerPalabras($nombrePedido);
        
        if ($palabrasPedido->isEmpty()) {
            Log::warning("No se pudieron obtener palabras del nombre del pedido", [
                'nombre_pedido' => $nombrePedido
            ]);
            return null;
        }

        Log::info("Buscando doctor para pedido", [
            'nombre_pedido' => $nombrePedido,
            'palabras_pedido' => $palabrasPedido->toArray(),
            'total_doctores' => $doctores->count()
        ]);

        $mejorCoincidencia = null;
        $mayorPorcentaje = 0;

        foreach ($doctores as $doctor) {
            if (empty($doctor->name_softlynn)) {
                continue;
            }

            // Normalizar y obtener palabras del doctor
            $palabrasDoctor = $this->normalizarYObtenerPalabras($doctor->name_softlynn);
            
            if ($palabrasDoctor->isEmpty()) {
                continue;
            }

            // Calcular porcentaje de coincidencia
            $porcentajeCoincidencia = $this->calcularPorcentajeCoincidencia(
                $palabrasPedido, 
                $palabrasDoctor
            );

            Log::debug("Comparando con doctor", [
                'doctor_id' => $doctor->id,
                'doctor_name' => $doctor->name_softlynn,
                'palabras_doctor' => $palabrasDoctor->toArray(),
                'porcentaje_coincidencia' => $porcentajeCoincidencia
            ]);

            // Ser más flexible: aceptar 60% de coincidencia o más
            if ($porcentajeCoincidencia >= 0.6) {
                if ($porcentajeCoincidencia > $mayorPorcentaje) {
                    $mayorPorcentaje = $porcentajeCoincidencia;
                    $mejorCoincidencia = $doctor;
                    
                    Log::info("Nueva mejor coincidencia encontrada", [
                        'doctor_id' => $doctor->id,
                        'doctor_name' => $doctor->name_softlynn,
                        'porcentaje' => $porcentajeCoincidencia
                    ]);
                }

                // Si es una coincidencia muy alta (90% o más), retornar inmediatamente
                if ($porcentajeCoincidencia >= 0.9) {
                    Log::info("Coincidencia muy alta encontrada, retornando inmediatamente", [
                        'doctor_id' => $doctor->id,
                        'porcentaje' => $porcentajeCoincidencia
                    ]);
                    return $doctor;
                }
            }
        }

        if ($mejorCoincidencia) {
            Log::info("Mejor coincidencia final", [
                'doctor_id' => $mejorCoincidencia->id,
                'doctor_name' => $mejorCoincidencia->name_softlynn,
                'porcentaje' => $mayorPorcentaje
            ]);
        } else {
            Log::warning("No se encontró ninguna coincidencia válida", [
                'nombre_pedido' => $nombrePedido,
                'palabras_pedido' => $palabrasPedido->toArray()
            ]);
        }

        return $mejorCoincidencia;
    }

    /**
     * Normaliza un nombre y obtiene las palabras significativas
     *
     * @param string $nombre Nombre a normalizar
     * @return Collection Palabras normalizadas
     */
    private function normalizarYObtenerPalabras(string $nombre): Collection
    {
        // Primero normalizar caracteres especiales comunes
        $nombre = $this->normalizarCaracteresEspeciales($nombre);
        
        // Verificar encoding UTF-8
        if (!mb_check_encoding($nombre, 'UTF-8')) {
            $nombre = mb_convert_encoding($nombre, 'UTF-8', 'auto');
        }

        // Convertir a minúsculas para comparación uniforme
        $nombre = mb_strtolower($nombre, 'UTF-8');
        
        // Remover títulos médicos comunes y puntuación
        $nombre = preg_replace('/\b(dr|dra|doctor|doctora)\.?\s*/i', '', $nombre);
        
        // Remover puntuación y caracteres que no son letras (mantener espacios)
        $nombre = preg_replace('/[^\p{L}\s]/u', ' ', $nombre);
        
        // Normalizar espacios múltiples
        $nombre = preg_replace('/\s+/', ' ', trim($nombre));
        
        // Dividir en palabras y filtrar palabras muy cortas
        return collect(explode(' ', $nombre))
            ->filter(function ($palabra) {
                return !empty(trim($palabra)) && mb_strlen(trim($palabra)) >= 2;
            })
            ->map(function($palabra) {
                return trim($palabra);
            })
            ->values();
    }

    /**
     * Normaliza caracteres especiales comunes en nombres
     *
     * @param string $nombre Nombre a normalizar
     * @return string Nombre normalizado
     */
    private function normalizarCaracteresEspeciales(string $nombre): string
    {
        // Log para debug
        Log::debug("Normalizando nombre", ['original' => $nombre]);
        
        // Casos específicos para caracteres problemáticos comunes
        $nombre = str_replace('MUAOZ', 'MUÑOZ', $nombre);
        $nombre = str_replace('MUNOZ', 'MUÑOZ', $nombre);
        $nombre = str_replace('PENA', 'PEÑA', $nombre);
        $nombre = str_replace('NUNO', 'NUÑO', $nombre);
        
        // Usar regex para patrones comunes de caracteres malformados
        $nombre = preg_replace('/MU.{1,3}OZ/i', 'MUÑOZ', $nombre);
        $nombre = preg_replace('/PE.{1,2}A$/i', 'PEÑA', $nombre);
        
        Log::debug("Nombre normalizado", ['resultado' => $nombre]);
        
        return $nombre;
    }

    /**
     * Calcula el porcentaje de coincidencia entre dos conjuntos de palabras
     *
     * @param Collection $palabras1 Primer conjunto de palabras
     * @param Collection $palabras2 Segundo conjunto de palabras
     * @return float Porcentaje de coincidencia (0.0 a 1.0)
     */
    private function calcularPorcentajeCoincidencia(Collection $palabras1, Collection $palabras2): float
    {
        if ($palabras1->isEmpty() || $palabras2->isEmpty()) {
            return 0.0;
        }

        // Convertir a arrays para facilitar comparación
        $array1 = $palabras1->toArray();
        $array2 = $palabras2->toArray();

        // Encontrar intersección (palabras comunes)
        $interseccion = array_intersect($array1, $array2);
        
        // Calcular porcentaje basado en el conjunto más pequeño
        $totalPalabras = min(count($array1), count($array2));
        
        if ($totalPalabras === 0) {
            return 0.0;
        }

        return count($interseccion) / $totalPalabras;
    }
}
