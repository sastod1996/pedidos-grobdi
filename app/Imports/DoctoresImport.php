<?php

namespace App\Imports;

use App\Imports\BaseImport;
use App\Models\Doctor;
use App\Services\Import\DoctorImportService;

class DoctoresImport extends BaseImport
{
    protected DoctorImportService $doctorService;

    /**
     * Constructor de la clase DoctoresImport
     *
     * Inicializa la instancia del servicio DoctorImportService
     * que se utilizará para manejar la lógica de negocio de los doctores.
     */
    public function __construct()
    {
        $this->doctorService = new DoctorImportService();
    }

    /**
     * Obtiene el mapeo de columnas por defecto para importación de doctores
     *
     * Este método define el mapeo estándar de columnas para archivos Excel
     * que contienen información de doctores, incluyendo campos como nombre,
     * CMP, teléfonos, especialidad, centro de salud, distrito, etc.
     *
     * @return array Mapeo de columnas con índices y nombres de campos
     */
    protected function getDefaultColumnMapping(): array
    {
        return [
            'estado' => 0,
            'name_prefix' => 1,
            'name' => 2,
            'CMP' => 3,
            'phone' => 4,
            'telefono2' => 5,
            'telefono3' => 6,
            'name_secretariat' => 7,
            'observations' => 8,
            'especialidad' => 9,
            'asignado_visitadora' => 10,
            'distrito_direccion' => 11,
            'centrosalud' => 12,
            'numero_consultorio' => 13,
            'horario_atencion' => 14,
            'categoria_medico' => 15,
            'tipo_medico' => 16,
            'precio_consulta' => 17,
            'campo18' => 18,
            'campo19' => 19,
            'campo20' => 20,
            'dia_lunes' => 21,
            'dia_martes' => 22,
            'dia_miercoles' => 23,
            'dia_jueves' => 24,
            'dia_viernes' => 25,
        ];
    }

    /**
     * Procesa una fila individual de datos de doctor
     *
     * Este método procesa cada fila del archivo Excel, valida los datos esenciales,
     * busca o crea las entidades relacionadas (centro de salud, especialidad, distrito),
     * crea el registro del doctor y opcionalmente asocia los días de atención.
     *
     * @param array $row La fila de datos a procesar
     * @param int $index El índice de la fila en el archivo
     * @param array $colMap El mapeo de columnas detectado
     * @return void
     */
    protected function processRow(array $row, int $index, array $colMap): void
    {
        // Siempre saltar las primeras dos filas (índices 0 y 1) que son cabeceras
        if ($index === 0 || $index === 1) {
            $this->incrementStat('skipped');
            return;
        }

        /* Verificar si la fila debe ser omitida (filas vacías, etc.)
        if ($this->shouldSkipRow($row, $colMap)) {
            $this->incrementStat('skipped');
            return;
        }
        */

        // Validar solo los campos absolutamente requeridos
        $cmp = trim($row[$colMap['CMP']] ?? '');

        // Salta si no tiene CMP
        if (empty($cmp)) {
            $this->incrementStat('errors');
            return;
        }

        // Verifica si el doctor ya existe por CMP
        if (Doctor::where('CMP', $cmp)->exists()) {
            $this->incrementStat('skipped');
            return;
        }

        try {
            // Verifica si tiene centro de salud, sino usa un valor por defecto
            $centroSaludName = trim($row[$colMap['centrosalud']] ?? 'Sin Centro de Salud');
            $centroSalud = $this->doctorService->findOrCreateCentroSalud($centroSaludName);

            $especialidad = $this->doctorService->findOrCreateEspecialidad(
                trim($row[$colMap['especialidad']] ?? 'General')
            );

            // Extract distrito from distrito_direccion field
            $distrito = null;
            $distritoField = $row[$colMap['distrito_direccion']] ?? '';
            if (!empty($distritoField)) {
                $distritoParts = explode('-', $distritoField);
                $distritoName = trim($distritoParts[0]);
                $distrito = $this->doctorService->findDistrito($distritoName);
            }

            // Prepare doctor data con manejo seguro de campos vacíos
            $doctorData = [
                'name' => trim($row[$colMap['name']] ?? '') ?: 'Doctor Sin Nombre',
                'name_softlynn' =>trim($row[$colMap['name']] ?? '') ?: 'Doctor Sin Nombre',
                'CMP' => $cmp,
                'phone' => !empty(trim($row[$colMap['phone']] ?? '')) ? trim($row[$colMap['phone']]) : null,
                'name_secretariat' => !empty(trim($row[$colMap['name_secretariat']] ?? '')) ? trim($row[$colMap['name_secretariat']]) : null,
                'observations' => !empty(trim($row[$colMap['observations']] ?? '')) ? trim($row[$colMap['observations']]) : null,
                'categoria_medico' => !empty(trim($row[$colMap['categoria_medico']] ?? '')) ? trim($row[$colMap['categoria_medico']]) : 'Visitador',
                'tipo_medico' => !empty(trim($row[$colMap['tipo_medico']] ?? '')) ? trim($row[$colMap['tipo_medico']]) : 'En Proceso',
                'centrosalud_id' => $centroSalud->id,
                'especialidad_id' => $especialidad->id,
                'distrito_id' => $distrito?->id,
            ];

            // Create doctor
            $doctor = $this->doctorService->createDoctor($doctorData);

            // Attach days if provided
            $days = [];
            $dayColumns = ['dia_lunes', 'dia_martes', 'dia_miercoles', 'dia_jueves', 'dia_viernes'];

            foreach ($dayColumns as $dayIndex => $dayColumn) {
                // Verificar que la columna existe en el mapeo antes de acceder
                if (isset($colMap[$dayColumn])) {
                    $dayValue = $row[$colMap[$dayColumn]] ?? '';
                    if (!empty(trim($dayValue))) {
                        $days[21 + $dayIndex] = trim($dayValue);
                    }
                }
            }

            if (!empty($days)) {
                $this->doctorService->attachDaysToDoctor($doctor, $days);
            }

            $this->incrementStat('created');

        } catch (\Exception $e) {
            $this->incrementStat('errors');
            // Log del error específico para debug
            logger()->error('Error procesando fila ' . $index . ' en DoctoresImport: ' . $e->getMessage(), [
                'cmp' => $cmp,
                'row_data' => $row
            ]);
        }
    }

    /**
     * Obtiene las palabras clave para identificar encabezados específicos de doctores
     *
     * Este método sobrescribe el método padre para incluir palabras clave
     * específicas relacionadas con doctores además de las generales.
     *
     * @return array Array de palabras clave de encabezado
     */
    protected function getHeaderKeywords(): array
    {
        // Palabras clave generales del padre
        $parentKeywords = parent::getHeaderKeywords();

        // Palabras clave específicas de doctores
        $doctorKeywords = [
            'estado',
            'prefijo', 'name_prefix', 'prefijo_nombre',
            'nombre', 'name', 'doctor', 'medico', 'médico',
            'cmp', 'colegio', 'colegio_medico', 'colegio_médico',
            'telefono', 'teléfono', 'phone', 'celular',
            'secretaria', 'name_secretariat', 'secretaria',
            'observaciones', 'observations', 'notas',
            'especialidad', 'specialty', 'especialidad',
            'visitadora', 'asignado_visitadora', 'visitador',
            'distrito', 'distrito_direccion', 'direccion', 'dirección',
            'centro', 'centrosalud', 'centro_salud', 'hospital', 'clinica', 'clínica',
            'consultorio', 'numero_consultorio', 'consultorio',
            'horario', 'horario_atencion', 'atencion', 'atención',
            'categoria', 'categoria_medico', 'categoría', 'categoría_médico',
            'tipo', 'tipo_medico', 'tipo_médico',
            'precio', 'precio_consulta', 'consulta',
            'lunes', 'dia_lunes',
            'martes', 'dia_martes',
            'miercoles', 'dia_miercoles', 'miércoles',
            'jueves', 'dia_jueves',
            'viernes', 'dia_viernes'
        ];

        return array_merge($parentKeywords, $doctorKeywords);
    }
}
