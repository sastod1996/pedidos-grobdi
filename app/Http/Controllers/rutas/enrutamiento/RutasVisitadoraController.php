<?php

namespace App\Http\Controllers\rutas\enrutamiento;

use App\Http\Controllers\Controller;
use App\Models\Day;
use App\Models\Distrito;
use App\Models\Doctor;
use App\Models\Enrutamiento;
use App\Models\EnrutamientoLista;
use App\Models\Especialidad;
use App\Models\VisitaDoctor;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RutasVisitadoraController extends Controller
{
    public function ListarMisRutas()
    {
        $primerDiaMes = Carbon::now()->startOfMonth()->toDateString();
        $rutames = Enrutamiento::where('fecha', $primerDiaMes)->whereIn('zone_id', Auth::user()->zones->pluck('id'))->get();
        foreach ($rutames as $ruta) {
            $listas = EnrutamientoLista::where('enrutamiento_id', $ruta->id)->get();
        }
        return view('rutas.visita.misrutas', compact('listas'));
    }
    public function listadoctores($id)
    {
        $rutames = EnrutamientoLista::findOrFail($id);
        $fecha_inicio = $rutames->fecha_inicio;
        $fecha_fin = $rutames->fecha_fin;
        $semana_ruta = $rutames;
        $dias = Day::all();
        $especialidades = Especialidad::all();
        $visitadoctores = VisitaDoctor::where('enrutamientolista_id', $id)->get();
        $distritos = Distrito::select('id', 'name')->where('provincia_id', 128)->orWhere('provincia_id', 67)->orderBy('name')->get();
        return view('rutas.visita.doctoresrutas', compact('visitadoctores', 'fecha_inicio', 'fecha_fin', 'semana_ruta', 'especialidades', 'distritos', 'dias'));
    }
    public function asignar(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:visita_doctor,id',
            'fecha' => 'required|date',
            'turno' => 'required',
        ]);
        $visita = VisitaDoctor::find($request->id);
        $visita->fecha = $request->fecha;
        $visita->turno = $request->turno;
        $visita->estado_visita_id = 2;
        $visita->save();
    }
}
