<?php

namespace App\Http\Controllers\rutas\enrutamiento;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Enrutamiento;
use App\Models\EnrutamientoLista;
use App\Models\EstadoVisita;
use App\Models\Lista;
use App\Models\User;
use App\Models\VisitaDoctor;
use App\Models\Zone;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EnrutamientoController extends Controller
{
    public function index()
    {
        $rutas = Enrutamiento::orderBy('fecha', 'desc')->get();

        return view('rutas.enrutamiento.index', compact('rutas'));
    }

    public function store(Request $request)
    {
        // Validación
        // dd($request->all());
        $request->validate([
            'fecha_mes' => 'required',
        ]);
        // dd($request->fecha_mes.'-00');
        $zonas = Zone::whereNotIn('name', ['Recojo en tienda', 'Otros'])->get();
        $existe_fecha = Enrutamiento::where('fecha', $request->fecha_mes.'-01')->first();
        if (! $existe_fecha) {
            foreach ($zonas as $zona) {
                $enrutamiento = new Enrutamiento;
                $enrutamiento->fecha = $request->fecha_mes.'-01';
                $enrutamiento->zone_id = $zona->id;
                $enrutamiento->save();

            }
        } else {
            return redirect()->route('enrutamiento.index')->with('danger', 'Mes ya existente');
        }
        // Si la validación es correcta, guardar el item
        // Item::create($request->all());

        return redirect()->route('enrutamiento.index')->with('success', 'Mes añadido correctamente');
    }

    public function agregarLista($id)
    {
        $enrutamiento = Enrutamiento::find($id);
        $visitas = VisitaDoctor::whereHas('enrutamientoLista', function ($query) use ($id) {
            $query->where('enrutamiento_id', $id);
        })->where('estado_visita_id', 'like', 6)->get();
        $listas = Lista::where('zone_id', $enrutamiento->zone_id)->get();
        $enrutamiento_lista = EnrutamientoLista::where('enrutamiento_id', $id)->get();
        $fechas_seleccionadas = [];
        $fecha_fin = Carbon::parse($enrutamiento->fecha)->endOfMonth()->toDateString();
        if ($enrutamiento_lista) {
            foreach ($enrutamiento_lista as $ruta_lista) {
                $rangoInicio = Carbon::parse($ruta_lista->fecha_inicio);
                $rangoFin = Carbon::parse($ruta_lista->fecha_fin);
                while ($rangoInicio <= $rangoFin) {
                    // Agregar el día actual al arreglo
                    $fechas_seleccionadas[] = $rangoInicio->toDateString();

                    // Avanzar un día
                    $rangoInicio->addDay();
                }
            }
        }

        return view('rutas.enrutamiento.enrutamientolista', compact('listas', 'enrutamiento', 'fechas_seleccionadas', 'visitas', 'fecha_fin'));
    }

    public function Enrutamientolistastore(Request $request)
    {
        $request->validate([
            'fechas' => 'required',
            'lista_id' => 'required',
        ]);

        $lista = Lista::findOrFail($request->lista_id);
        $fechas = explode(' a ', $request->input('fechas'));
        $startDate = Carbon::parse($fechas[0]);
        $endDate = Carbon::parse($fechas[1]);
        $period = CarbonPeriod::create($startDate, $endDate);

        // Crear registro de EnrutamientoLista
        $enrutamiento_lista = new EnrutamientoLista;
        $enrutamiento_lista->fecha_inicio = $startDate->toDateString();
        $enrutamiento_lista->fecha_fin = $endDate->toDateString();
        $enrutamiento_lista->lista_id = $request->lista_id;
        $enrutamiento_lista->enrutamiento_id = $request->enrutamiento_id;
        $enrutamiento_lista->save();

        // Contador de visitas por fecha (ej: '2025-09-01' => 10)
        $visitasPorDia = [];

        // Obtener todos los doctores asociados a los distritos
        foreach ($lista->distritos as $distrito) {
            if ($lista->recovery == 1) {
                $doctores = Doctor::where('distrito_id', $distrito->id)
                    ->where('recovery', 1)
                    ->where('state', 1)
                    ->get();
            } else {
                $doctores = Doctor::where('distrito_id', $distrito->id)
                    ->where('recovery', 0)
                    ->where('state', 1)
                    ->get();
            }

            if ($doctores->isEmpty()) {
                continue;
            }

            foreach ($doctores as $doctor) {
                $turnos = [];

                // Obtener días y turnos del doctor
                if ($doctor->days) {
                    foreach ($doctor->days as $dia) {
                        $turnos[] = [
                            'dia' => $dia->name,
                            'turno' => $dia->pivot->turno,
                        ];
                    }
                }

                // Crear registro de VisitaDoctor
                $visita_doctor = new VisitaDoctor;
                $visita_doctor->doctor_id = $doctor->id;
                $visita_doctor->created_by = Auth::id();
                $visita_doctor->updated_by = Auth::id();
                $visita_doctor->enrutamientolista_id = $enrutamiento_lista->id;

                $fecha_asignada = false;

                if (! empty($turnos)) {
                    foreach ($period as $date) {
                        $nombre_dia = $date->translatedFormat('l');
                        $fechaStr = $date->toDateString();

                        foreach ($turnos as $turno) {
                            if ($nombre_dia === $turno['dia']) {
                                $cantidad = $visitasPorDia[$fechaStr] ?? 0;

                                if ($cantidad < 15) {
                                    // Asignar fecha y turno
                                    $visita_doctor->fecha = $fechaStr;
                                    $visita_doctor->turno = $turno['turno'];
                                    $visita_doctor->estado_visita_id = 2;

                                    // Actualizar contador
                                    $visitasPorDia[$fechaStr] = $cantidad + 1;

                                    $fecha_asignada = true;
                                    break 2; // salir de los 2 foreach
                                }
                                // si ya hay 15, pasa al siguiente día
                            }
                        }
                    }
                }

                // Si no se encontró ningún día con espacio
                if (! $fecha_asignada) {
                    $visita_doctor->estado_visita_id = 1; // Sin turno disponible
                }

                $visita_doctor->save();
            }
        }

        return redirect()->route('enrutamiento.agregarlista', $request->enrutamiento_id);
    }

    public function DoctoresLista(Request $request, $id)
    {
        $doctores = VisitaDoctor::where('enrutamientolista_id', $id)->whereNot('estado_visita_id', 'like', 6)->get();
        $enruta = VisitaDoctor::where('enrutamientolista_id', $id)->first();
        if (! $enruta) {
            return redirect()->back()->with('danger', 'No hay doctores asignados a esta ruta, por favor asigne una lista');
        }
        $id = $enruta->enrutamientolista->enrutamiento->id;
        $dateRange = [
            'start_date' => $enruta->enrutamientolista->fecha_inicio,
            'end_date' => $enruta->enrutamientolista->fecha_fin,
        ];

        return view('rutas.enrutamiento.doctoreslista', compact('doctores', 'id', 'dateRange'));
    }

    public function DoctoresListaUpdate(Request $request, $id)
    {
        $visita_doctor = VisitaDoctor::find($id);
        $visita_doctor->fecha = $request->fecha;
        $visita_doctor->estado_visita_id = $visita_doctor->estado_visita_id == 3 ? 5 : 2;
        $visita_doctor->save();

        return back()->with('success', 'Doctor Asignado exitosamente');
    }

    public function addSpontaneousVisitaDoctor(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|integer',
            'enrutamientolista_id' => 'required|integer',
            'fecha' => 'required|date',
        ]);

        $date = $request->fecha;
        $dateSelected = Carbon::parse($date);
        $doctorId = $request->doctor_id;
        $enrutamientoListaId = $request->enrutamientolista_id;

        $enrutamientoLista = EnrutamientoLista::where('id', $enrutamientoListaId)->first();

        $allowedStartDate = Carbon::parse($enrutamientoLista->fecha_inicio);
        $allowedEndDate = Carbon::parse($enrutamientoLista->fecha_fin);

        if ($dateSelected->lt($allowedStartDate) || $dateSelected->gt($allowedEndDate)) {
            return response()->json(['success' => false, 'message' => 'La fecha seleccionada esta fuera del rango permitido.']);
        }

        $visitaDoctor = VisitaDoctor::where('doctor_id', $doctorId)
            ->where('enrutamientolista_id', $enrutamientoListaId)->first();

        if (! $visitaDoctor) {
            $visitaDoctor = VisitaDoctor::create([
                'doctor_id' => $doctorId,
                'enrutamientolista_id' => $enrutamientoListaId,
                'fecha' => $date,
                'estado_visita_id' => 4,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
            $message = 'Visita registrada exitosamente.';
        } else {
            if ($visitaDoctor->estado_visita_id === 4) {
                return response()->json(['success' => false, 'message' => 'El doctor ya fue visitado anteriormente.']);
            }
            $visitaDoctor->estado_visita_id = 4;
            $visitaDoctor->fecha = $date;
            $visitaDoctor->save();
            $message = 'Doctor marcado como visitado correctamente.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $visitaDoctor,
        ]);
    }

    public function calendariovisitadora(Request $request)
    {
        // $doctoresSinFecha = VisitaDoctor::with('doctor')->get();
        $visitas = VisitaDoctor::whereHas('enrutamientolista.enrutamiento.zone.users', function ($query) {
            $query->where('users.id', Auth::id());
        })->orderBy('visita_doctor.turno', 'asc')->get();
        $estados = EstadoVisita::all(['id', 'name', 'color']);
        // dd($visitas);
        $eventos = $visitas->map(function ($visita) {
            if ($visita->doctor->categoriadoctor->name == 'AAA') {
                $categoriadoctor = '★★★';
            } elseif ($visita->doctor->categoriadoctor->name == 'AA') {
                $categoriadoctor = '★★';
            } elseif ($visita->doctor->categoriadoctor->name == 'A') {
                $categoriadoctor = '★';
            } else {
                $categoriadoctor = $visita->doctor->categoriadoctor->name;
            }

            return [
                'id' => $visita->id,
                'title' => $categoriadoctor.' - '.$visita->doctor->name.' '.$visita->doctor->first_lastname.' '.$visita->doctor->second_lastname,
                'start' => $visita->fecha,
                'color' => $visita->estado_visita->color ?? '#cccccc',
                'extendedProps' => [
                    'estado' => $visita->estado_visita->name ?? 'Sin estado',
                    'turno' => $visita->turno == 1 ? 'Tarde' : 'Mañana',
                ],
            ];
        });

        $doctoresConVisita = $visitas->pluck('doctor_id');
        // $doctoresSinFecha = Doctor::whereNotIn('id', $doctoresConVisita)->get();
        // $doctoresSinFecha = VisitaDoctor::whereHas('enrutamientolista.enrutamiento.zone.users', function ($query) {
        //     $query->where('users.id', Auth::id());
        // })->whereNotIn('id', $doctoresConVisita)->get();

        return view('rutas.visita.calendario', compact('eventos', 'estados'));
    }

    /**
     * Muestra la vista principal del calendario de visitas, diseñada para el rol de supervisora.
     *
     * Esta función recupera la lista de visitadoras, selecciona una por defecto (o según el parámetro
     * de la URL), y carga las visitas, los estados y las estadísticas iniciales para poblar el calendario.
     *
     * @param  \Illuminate\Http\Request  $request  La petición HTTP, utilizada para obtener el ID de la visitadora seleccionada.
     * @return \Illuminate\View\View Retorna la vista con todos los datos necesarios para renderizar el calendario.
     */
    public function calendarioSupervisora(Request $request)
    {
        $visitadoras = User::visitadoras()->orderBy('name')->get(['id', 'name']);
        $selectedVisitadoraId = $request->query('visitadora_id');

        if (! $selectedVisitadoraId && $visitadoras->isNotEmpty()) {
            $selectedVisitadoraId = (string) $visitadoras->first()->id;
        }

        $visitas = collect();

        if ($selectedVisitadoraId) {
            $visitas = $this->obtenerVisitasPorVisitadora((int) $selectedVisitadoraId);
        }

        $estados = EstadoVisita::all(['id', 'name', 'color']);
        $estadisticasEstados = $estados->map(function ($estado) use ($visitas) {
            return [
                'id' => $estado->id,
                'name' => $estado->name,
                'color' => $estado->color,
                'count' => $visitas->where('estado_visita_id', $estado->id)->count(),
            ];
        })->values();

        $eventos = $this->formatearEventosCalendario($visitas);

        return view('rutas.visita.calendario-supervisora', [
            'visitadoras' => $visitadoras,
            'selectedVisitadoraId' => $selectedVisitadoraId,
            'eventos' => $eventos,
            'estados' => $estados,
            'estadisticasEstados' => $estadisticasEstados,
            'totalVisitas' => $visitas->count(),
            'estadoReprogramadoId' => optional($estados->firstWhere('name', 'Reprogramado'))->id ?? 5,
        ]);
    }

    /**
     * AJAX Endpoint: Retorna los eventos y métricas de las visitas en formato JSON para una visitadora específica.
     *
     * Este método se utiliza típicamente para cargar los datos de forma asíncrona (AJAX) después de que la página
     * inicial ha cargado, o cuando la supervisora cambia la selección de la visitadora en un filtro.
     *
     * @param  \Illuminate\Http\Request  $request  La petición HTTP, debe incluir el parámetro 'visitadora_id'.
     * @return \Illuminate\Http\JsonResponse Retorna una respuesta JSON que contiene la lista de eventos y las estadísticas.
     */
    public function calendarioSupervisoraEventos(Request $request)
    {
        $visitadoraId = $request->query('visitadora_id');

        if (! $visitadoraId) {
            return response()->json([
                'events' => [],
                'metrics' => [
                    'total' => 0,
                    'estados' => [],
                ],
            ]);
        }

        $visitas = $this->obtenerVisitasPorVisitadora((int) $visitadoraId);
        $eventos = $this->formatearEventosCalendario($visitas);

        $estados = EstadoVisita::all(['id', 'name', 'color']);
        $estadisticasEstados = $estados->map(function ($estado) use ($visitas) {
            return [
                'id' => $estado->id,
                'name' => $estado->name,
                'color' => $estado->color,
                'count' => $visitas->where('estado_visita_id', $estado->id)->count(),
            ];
        })->values();

        return response()->json([
            'events' => $eventos,
            'metrics' => [
                'total' => $visitas->count(),
                'estados' => $estadisticasEstados,
            ],
        ]);
    }

    public function DetalleDoctorRutas(Request $request, $id)
    {
        $visita = VisitaDoctor::findOrFail($id);
        $id_doctor = $visita->doctor_id;
        $doctor = Doctor::with(['distrito', 'especialidad', 'centroSalud'])->find($id_doctor);
        $estados = EstadoVisita::whereNotIn('id', [1, 2])->get();
        $fecha_inicio = $visita->enrutamientolista->fecha_inicio;
        $fecha_fin = $visita->enrutamientolista->fecha_fin;

        // $turno = null;

        // if ($visita?->fecha) {
        //     $diaSemana = Carbon::parse($visita->fecha)->dayOfWeek; // 0 (domingo) a 6 (sábado)

        //     $turnoRaw = DB::table('doctor_day')
        //         ->where('doctor_id', $doctor->id)
        //         ->where('day_id', $diaSemana)
        //         ->value('turno'); // trae directamente el campo turno

        $turno = $visita->turno == 1 ? 'Tarde' : 'Mañana';
        // }
        if (! $doctor) {
            return response()->json(['error' => 'Doctor no encontrado'], 404);
        }

        return response()->json([
            'doctor' => $doctor,
            'visita' => $visita,
            'turno' => $turno,
            'estados' => $estados,
            'rango' => [
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
            ],
        ]);
    }

    public function GuardarVisita(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctor,id',
            'estado_visita_id' => 'required|exists:estado_visita,id',
            'observaciones' => 'nullable|string',
        ]);
        $visita = VisitaDoctor::findOrFail($request->visita_id);
        if ($request['estado_visita_id'] == 5) {
            if ($visita->reprogramar == 1) {
                return response()->json(['error' => 'Ya no se puede reprogramar su visita, contactar con su supervisora'], 404);
            } else {
                $visita->reprogramar = 1;
            }
        }
        $visita->estado_visita_id = $request['estado_visita_id'];
        $visita->observaciones_visita = $request['observaciones'];
        if ($request['fecha_visita']) {
            $visita->fecha = $request['fecha_visita'];
        }
        $visita->latitude = $request['latitude'] ?? 19.4326;
        $visita->longitude = $request['longitude'] ?? -99.1332;
        $visita->updated_by = Auth::user()->id;
        $visita->save();

        $doctor = Doctor::find($request['doctor_id']);

        // logger($visita); // Guarda en logs
        return response()->json([
            'success' => true,
            'visita_id' => $visita->id,
            'doctor_id' => $doctor->id,
            'doctor_name' => $doctor->name,
            'fecha_visita' => $visita->fecha,
            'color' => $visita->estado_visita->color ?? '#ccc',
            'extendedProps' => [
                'turno' => $visita->turno == 1 ? 'Tarde' : 'Mañana', // valor textual para el frontend
                'estado' => $visita->estado_visita->name ?? null,
            ],
        ]);
    }

    public function destroyVisitaDoctor($id)
    {
        $visitaDoctor = VisitaDoctor::findOrFail($id);
        $visitaDoctor->delete();

        return back()->with('success', 'Visita eliminada correctamente');
    }

    private function obtenerVisitasPorVisitadora(int $visitadoraId)
    {
        return VisitaDoctor::with([
            'doctor.categoriadoctor',
            'doctor.distrito',
            'doctor.especialidad',
            'doctor.centroSalud',
            'estado_visita',
            'enrutamientolista.enrutamiento.zone',
        ])->whereHas('enrutamientolista.enrutamiento.zone.users', function ($query) use ($visitadoraId) {
            $query->where('users.id', $visitadoraId);
        })->orderBy('visita_doctor.fecha', 'asc')->get();
    }

    private function formatearEventosCalendario($visitas)
    {
        return $visitas->map(function ($visita) {
            $categoria = $visita->doctor?->categoriadoctor?->name;
            if ($categoria === 'AAA') {
                $categoria = '★★★';
            } elseif ($categoria === 'AA') {
                $categoria = '★★';
            } elseif ($categoria === 'A') {
                $categoria = '★';
            }

            $nombreDoctor = trim(collect([
                $visita->doctor?->name,
                $visita->doctor?->first_lastname,
                $visita->doctor?->second_lastname,
            ])->filter()->implode(' '));

            $titulo = $categoria ? $categoria.' - '.$nombreDoctor : $nombreDoctor;

            return [
                'id' => $visita->id,
                'title' => $titulo,
                'start' => $visita->fecha,
                'color' => $visita->estado_visita->color ?? '#cccccc',
                'extendedProps' => [
                    'estado' => $visita->estado_visita->name ?? 'Sin estado',
                    'turno' => $visita->turno == 1 ? 'Tarde' : 'Mañana',
                    'doctor' => [
                        'especialidad' => $visita->doctor?->especialidad?->name,
                        'distrito' => $visita->doctor?->distrito?->name,
                        'centro_salud' => $visita->doctor?->centroSalud?->name,
                    ],
                ],
            ];
        })->values();
    }
}
