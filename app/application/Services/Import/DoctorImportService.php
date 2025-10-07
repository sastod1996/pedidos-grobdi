<?php

namespace App\Application\Services\Import;

use App\Models\CentroSalud;
use App\Models\Distrito;
use App\Models\Doctor;
use App\Models\Especialidad;
use Illuminate\Support\Facades\Auth;

class DoctorImportService
{
    /**
     * Busca o crea un centro de salud
     * 
     * Este método busca un centro de salud existente por nombre. Si no lo encuentra,
     * crea automáticamente un nuevo registro. Esto asegura que no haya duplicados
     * de centros de salud en la base de datos.
     * 
     * @param string $name El nombre del centro de salud
     * @return CentroSalud El centro de salud encontrado o creado
     */
    public function findOrCreateCentroSalud(string $name): CentroSalud
    {
        $centroSalud = CentroSalud::where('name', $name)->first();
        
        if (!$centroSalud) {
            $centroSalud = CentroSalud::create(['name' => $name]);
        }
        
        return $centroSalud;
    }
    
    /**
     * Busca o crea una especialidad médica
     * 
     * Este método busca una especialidad médica existente por nombre. Si no la encuentra,
     * crea automáticamente un nuevo registro. Esto mantiene la integridad de las
     * especialidades médicas en el sistema.
     * 
     * @param string $name El nombre de la especialidad médica
     * @return Especialidad La especialidad encontrada o creada
     */
    public function findOrCreateEspecialidad(string $name): Especialidad
    {
        $especialidad = Especialidad::where('name', $name)->first();
        
        if (!$especialidad) {
            $especialidad = Especialidad::create(['name' => $name]);
        }
        
        return $especialidad;
    }
    
    /**
     * Normaliza el nombre de un distrito
     * 
     * Este método aplica normalizaciones específicas a nombres de distritos
     * para corregir variaciones comunes y asegurar consistencia en los datos.
     * Por ejemplo, "CERCADO DE LIMA" se normaliza a "LIMA", "SURCO" a "SANTIAGO DE SURCO", etc.
     * 
     * @param string $districtName El nombre original del distrito
     * @return string El nombre del distrito normalizado
     */
    public function normalizeDistrictName(string $districtName): string
    {
        $normalizations = [
            "CERCADO DE LIMA" => "LIMA",
            "SURCO" => "SANTIAGO DE SURCO", 
            "ATE " => "ATE",
            "MAGDALENA" => "MAGDALENA DEL MAR",
            "BREÃ'A" => "BREÑA",
            "BREÃ'A " => "BREÑA",
            "ZARATE" => "SAN JUAN DE LURIGANCHO",
        ];
        
        return $normalizations[$districtName] ?? $districtName;
    }
    
    /**
     * Busca un distrito por nombre
     * 
     * Este método busca un distrito en la base de datos aplicando primero
     * la normalización del nombre y filtrando por las provincias permitidas
     * (Lima y Callao). Retorna null si no encuentra el distrito.
     * 
     * @param string $districtName El nombre del distrito a buscar
     * @return Distrito|null El distrito encontrado o null si no existe
     */
    public function findDistrito(string $districtName): ?Distrito
    {
        $normalizedName = $this->normalizeDistrictName($districtName);
        
        return Distrito::whereIn('provincia_id', [128, 67])
            ->where('name', $normalizedName)
            ->first();
    }
    
    /**
     * Crea un doctor con los datos proporcionados
     * 
     * Este método crea un nuevo registro de doctor con toda la información
     * necesaria, incluyendo relaciones con centro de salud, especialidad y distrito.
     * Establece valores por defecto para campos como categoria_medico, tipo_medico,
     * asignado_consultorio, user_id y categoriadoctor_id.
     * 
     * @param array $data Array con los datos del doctor ['name', 'CMP', 'phone', 'name_secretariat', 'observations', 'centrosalud_id', 'especialidad_id', 'distrito_id']
     * @return Doctor El doctor creado
     */
    public function createDoctor(array $data): Doctor
    {
        $doctor = new Doctor();
        
        // Set basic information
        $doctor->name = $data['name'];
        $doctor->CMP = $data['CMP'];
        $doctor->phone = $data['phone'] ?? null;
        $doctor->name_secretariat = $data['name_secretariat'] ?? null;
        $doctor->observations = $data['observations'] ?? null;
        
        // Set relationships
        $doctor->centrosalud_id = $data['centrosalud_id'];
        $doctor->especialidad_id = $data['especialidad_id'];
        $doctor->distrito_id = $data['distrito_id'] ?? null;
        
        // Set default values
        $doctor->categoria_medico = $data['categoria_medico'] ?? 'Visitador';
        $doctor->tipo_medico = $data['tipo_medico'] ?? 'En Proceso';
        $doctor->asignado_consultorio = 0;
        $doctor->user_id = Auth::id();
        $doctor->categoriadoctor_id = 5;
        
        $doctor->save();
        
        return $doctor;
    }
    
    /**
     * Asocia días al doctor
     * 
     * Este método asocia los días de atención al doctor basado en los datos
     * proporcionados. Utiliza un mapeo específico de índices de columna a IDs
     * de día (21=Lunes, 22=Martes, etc.) y determina el turno (mañana/tarde)
     * basado en el valor ('M' para mañana, otro valor para tarde).
     * 
     * @param Doctor $doctor El doctor al que se asociarán los días
     * @param array $days Array con los valores de días por índice de columna
     * @return void
     */
    public function attachDaysToDoctor(Doctor $doctor, array $days): void
    {
        $dayMapping = [
            21 => 1, // Lunes
            22 => 2, // Martes
            23 => 3, // Miércoles
            24 => 4, // Jueves
            25 => 5, // Viernes
        ];
        
        foreach ($days as $columnIndex => $dayValue) {
            if (isset($dayMapping[$columnIndex]) && !empty($dayValue)) {
                $dayId = $dayMapping[$columnIndex];
                $turno = strtolower($dayValue) === 'm' ? 0 : 1;
                
                $doctor->days()->attach($dayId, ['turno' => $turno]);
            }
        }
    }
}
