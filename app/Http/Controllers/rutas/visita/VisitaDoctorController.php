<?php

namespace App\Http\Controllers\rutas\visita;

use App\Http\Controllers\Controller;
use App\Models\Day;
use App\Models\Doctor;
use App\Models\VisitaDoctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VisitaDoctorController extends Controller
{
    public function aprobar($id)
    {
        $visita = VisitaDoctor::findOrFail($id);
        // Tu lógica para aprobar (ejemplo: cambiar un estado)
        $visita->estado_visita_id = 4;
        $visita->save();

        $doctor = Doctor::where('id', $visita->doctor_id)->first();
        $doctor->state = 1;
        $doctor->save();

        return response()->json(['success' => true]);
    }

    public function rechazar($id)
    {
        $visita = VisitaDoctor::findOrFail($id);
        // Tu lógica para rechazar
        $visita->estado_visita_id = 3;
        $visita->save();


        return response()->json(['success' => true]);
    }

    public function mapa()
    {
        $columns = [
            'visita_doctor.id',
            'centrosalud.name as centrosalud_name',
            'centrosalud.id as centrosalud_id',
            'centrosalud.latitude as centrosalud_lat',
            'centrosalud.longitude as centrosalud_lng',
            'categoria_doctor.name as categoria_doctor',
            'doctor.id as doctor_id',
            'doctor.name as doctor_name',
            'doctor.first_lastname as doctor_first_lastname',
            'doctor.second_lastname as doctor_second_lastname',
            'visita_doctor.fecha',
            'estado_visita.color as estado_color',
            'estado_visita.name as estado',
        ];

        $data = DB::table('visita_doctor')
            ->leftJoin('enrutamiento_lista', 'visita_doctor.enrutamientolista_id', '=', 'enrutamiento_lista.id')
            ->leftJoin('enrutamiento', 'enrutamiento_lista.enrutamiento_id', '=', 'enrutamiento.id')
            ->leftJoin('zones', 'enrutamiento.zone_id', '=', 'zones.id')
            ->leftJoin('user_zones', 'zones.id', '=', 'user_zones.zone_id')
            ->leftJoin('users', 'user_zones.user_id', '=', 'users.id')
            ->leftJoin('estado_visita', 'visita_doctor.estado_visita_id', '=', 'estado_visita.id')
            ->leftJoin('doctor', 'visita_doctor.doctor_id', '=', 'doctor.id')
            ->leftJoin('centrosalud', 'doctor.centrosalud_id', '=', 'centrosalud.id')
            ->leftJoin('categoria_doctor', 'doctor.categoriadoctor_id', '=', 'categoria_doctor.id')
            ->select($columns)
            ->orderBy('visita_doctor.turno', 'asc')
            ->whereIn('estado_visita.id', [2, 5])
            ->where('users.id', '=', Auth::id())
            ->whereDate('visita_doctor.fecha', '=', now()->toDateString())
            ->get();

        $days = Day::select('id', 'name')->orderBy('id')->get();

        return view('rutas.mapa', compact('data', 'days'));
    }

    public function FindDetalleVisitaByID($id)
    {
        $columns = [
            'visita_doctor.id',
            'centrosalud.latitude as centrosalud_lat',
            'centrosalud.longitude as centrosalud_lng',
            'doctor.id as doctor_id',
            'doctor.name as doctor_name',
            'doctor.cmp as doctor_cmp',
            'doctor.first_lastname as doctor_first_lastname',
            'doctor.second_lastname as doctor_second_lastname',
            'doctor.phone as doctor_phone',
            'distritos.name as doctor_distrito',
            'especialidad.name as doctor_especialidad',
            'visita_doctor.fecha',
            'visita_doctor.turno',
            'centrosalud.name as doctor_centro_salud',
            'estado_visita.color as estado_color',
            'estado_visita.name as estado',
            'enrutamiento_lista.fecha_inicio',
            'enrutamiento_lista.fecha_fin',
        ];

        $data = DB::table('visita_doctor')
            ->leftJoin('doctor', 'visita_doctor.doctor_id', '=', 'doctor.id')
            ->leftJoin('centrosalud', 'doctor.centrosalud_id', '=', 'centrosalud.id')
            ->leftJoin('distritos', 'doctor.distrito_id', '=', 'distritos.id')
            ->leftJoin('especialidad', 'doctor.especialidad_id', '=', 'especialidad.id')
            ->leftJoin('estado_visita', 'visita_doctor.estado_visita_id', '=', 'estado_visita.id')
            ->leftJoin('enrutamiento_lista', 'visita_doctor.enrutamientolista_id', '=', 'enrutamiento_lista.id')
            ->select($columns)
            ->where('visita_doctor.id', '=', $id)
            ->first();

        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Detalle de visita no encontrado'], 404);
        }

        $doctorDays = DB::table('doctor_day')
            ->join('day', 'doctor_day.day_id', '=', 'day.id')
            ->select([
                'day.id as id',
                'day.name as name',
                'doctor_day.turno as turno',
            ])
            ->where('doctor_day.doctor_id', $data->doctor_id)
            ->get()
            ->map(function ($day) {
                $day->turno = (int) $day->turno;
                $day->id = (int) $day->id;
                return $day;
            });

        $data->doctor_days = $doctorDays;

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function updateVisitaDoctor(Request $request, $id)
    {
        $visita = VisitaDoctor::findOrFail($id);

        $request->validate([
            'estado_visita' => 'required|exists:estado_visita,id',
            'observaciones' => 'nullable|string',
            'fecha_visita_reprogramada' => 'nullable|date',
            'dias' => 'nullable|array',
            'dias.*' => 'integer|exists:day,id',
            'update_days' => 'nullable|boolean',
        ]);

        if ($visita->fecha != now()->toDateString()) {
            return response()->json(['success' => false, 'message' => 'La visita no se puede actualizar porque no corresponde al día de hoy'], 401);
        }

        $reqEstado = $request['estado_visita'];

        if ($reqEstado == 5) {
            if ($visita->reprogramar == 1) {
                return response()->json(['success' => false, 'message' => 'La visita no se puede reprogramar más de una vez. Ponerse en contacto con su supervisora'], 400);
            }
            $visita->reprogramar = 1;

            if (!$request['fecha_visita_reprogramada']) {
                return response()->json(['success' => false, 'message' => 'La visita no se puede reprogramar sin asignarle una nueva fecha'], 400);
            }
            $nuevaFecha = $request['fecha_visita_reprogramada'];

            if ($nuevaFecha <= now()->toDateString()) {
                return response()->json(['success' => false, 'message' => 'La fecha de la visita debe ser un día posterior a la establecida'], 400);
            }
            $visita->fecha = $nuevaFecha;
        }

        if ($reqEstado == 4) {
            if (!($request['update_longitude'] && $request['update_latitude'])) {
                return response()->json(['success' => false, 'message' => 'Debe activar su ubicación para marcar como VISITADO.'], 400);
            }
            $visita->latitude = $request['update_latitude'];
            $visita->longitude = $request['update_longitude'];
        }

        if ($visita->estado_visita_id === 3 || $visita->estado_visita_id == 4) {
            return response()->json(['success' => false, 'message' => 'No se puede actualizar el estado una vez marcado como VISITADO o NO VISITADO.'], 400);
        }

        if ($request->boolean('update_days')) {
            $doctor = $visita->doctor;

            if ($doctor) {
                $selectedDays = $request->input('dias', []);
                $syncPayload = [];

                foreach ($selectedDays as $dayId) {
                    $turnoKey = "turno_{$dayId}";
                    $turnoValue = $request->input($turnoKey);

                    if ($turnoValue === null || ($turnoValue !== '0' && $turnoValue !== '1' && $turnoValue !== 0 && $turnoValue !== 1)) {
                        return response()->json(['success' => false, 'message' => 'Debe seleccionar un turno válido para cada día habilitado.'], 422);
                    }

                    $syncPayload[$dayId] = ['turno' => (int) $turnoValue];
                }

                $doctor->days()->sync($syncPayload);
            }
        }

        $visita->estado_visita_id = $request['estado_visita'];
        $visita->observaciones_visita = $request['observaciones'];

        $visita->updated_by = Auth::user()->id;
        $visita->save();

        return response()->json(['success' => true, 'message' => "La visita con ID: $id fue actualizada correctamente"]);
    }
}
