<?php

namespace App\Http\Controllers\rutas\mantenimiento;

use App\Http\Controllers\Controller;
use App\Http\Requests\rutas\DoctorStoreRequest;
use App\Imports\DoctoresImport;
use App\Models\CategoriaDoctor;
use App\Models\Day;
use App\Models\Distrito;
use App\Models\Doctor;
use App\Models\Especialidad;
use App\Models\VisitaDoctor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource with advanced filtering.
     *
     * This method handles the index view for doctors, applying filters based on user input.
     * Filters include: search by name, date range for creation date, type of medical professional,
     * and district. Results are paginated and can be sorted.
     *
     * @param Request $request The HTTP request object containing filter parameters.
     * @return \Illuminate\View\View The view with filtered doctors and filter options.
     */
    public function index(Request $request)
    {
        $ordenarPor = $request->get('sort_by', 'name'); // campo por defecto
        $direccion = $request->get('direction', 'asc'); // dirección por defecto

        // Obtener parámetros de filtros
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $tipoMedico = $request->input('tipo_medico');
        $distritoId = $request->input('distrito_id');

        $query = Doctor::query();

        // Aplicar filtros
        if ($search) {
            $doctores = Doctor::where('name', 'like', '%' . $search . '%')->orWhere('cmp', 'like', '%' . $search . '%')->orderBy($ordenarPor, $direccion)->paginate(20);  // Paginación, 20 por página
        } else {
            $doctores = Doctor::orderBy($ordenarPor, $direccion)->paginate(20);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if ($tipoMedico) {
            $query->where('tipo_medico', $tipoMedico);
        }

        if ($distritoId) {
            $query->where('distrito_id', $distritoId);
        }

        // Aplicar ordenamiento y paginación
        $doctores = $query->orderBy($ordenarPor, $direccion)->paginate(20);

        // Obtener datos para los selects
        $distritos = Distrito::select('id', 'name')->where('provincia_id', 128)->orWhere('provincia_id', 67)->get();
        $tiposMedico = Doctor::select('tipo_medico')->distinct()->pluck('tipo_medico');

        return view('rutas.mantenimiento.doctor.index', compact('doctores', 'distritos', 'tiposMedico', 'ordenarPor', 'direccion', 'search', 'startDate', 'endDate', 'tipoMedico', 'distritoId'));
    }
    public function buscarCMP($cmp)
    {
        // Validar que el CMP no esté vacío y sea numérico
        if (empty($cmp) || !ctype_digit($cmp)) {
            return response()->json([
                'success' => false,
                'message' => 'El CMP ingresado no es válido'
            ], 400);
        }
        $doctor = Doctor::where('cmp', $cmp)->first();
        if ($doctor) {
            return response()->json([
                'success' => false,
                'message' => 'El doctor ya existe'
            ], 404);
        }
        // Realizar una solicitud POST al formulario del CMP
        $datos = Doctor::ScrappingDoctor($cmp);
        if (isset($datos[1])) {
            $datos = $datos[1];
            $especialidad = isset($datos['tabla_interna'][7]) ? $datos['tabla_interna'][7][0] : '';
            array_push($datos['cols'], $especialidad);
            $datos = $datos['cols'];
            return response()->json([
                'success' => true,
                'data' => $datos
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró ningún doctor con ese CMP'
            ], 404);
        }
        // Logger($datos);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $distritos = Distrito::select('id', 'name')->where('provincia_id', 128)->orWhere('provincia_id', 67)->get();
        $especialidades = Especialidad::all();
        $categorias = CategoriaDoctor::all();
        $dias = Day::all();
        return view('rutas.mantenimiento.doctor.create',compact('distritos','especialidades','dias','categorias'));
    }
    public function guardarDoctorVisitador(Request $request)
    {
        $request->validate([
            'CMP' => 'required|numeric|unique:doctor,CMP',
            'first_lastname' => 'required|string|max:100',
            'second_lastname' => 'required|string|max:100',
            'name' => 'required|string|max:150',
            'phone' => 'required|numeric',
            'categoria_medico' => 'required',
            'distrito_id' => 'required',
            'especialidad_id' => 'required',
            'centrosalud_id' => 'required',
            'dias' => 'array',
            'fecha_visita' => 'required',
        ], [
            'first_lastname.required' => 'El apellido paterno es obligatorio',
            'second_lastname.required' => 'El apellido materno es obligatorio',
            'name.required' => 'El nombre es obligatorio',
            'categoria_medico.required' => 'La categoria del medico es obligatorio',
            'fecha_visita.required' => 'La fecha de visita es obligatorio',
        ]);


        $doctor = new Doctor();
        $doctor->cmp = $request->CMP;
        $doctor->first_lastname = $request->first_lastname;
        $doctor->second_lastname = $request->second_lastname;
        $doctor->name = $request->name;
        $doctor->phone = $request->phone;
        $doctor->birthdate = $request->birthdate;
        $doctor->distrito_id = $request->distrito_id;
        $doctor->especialidad_id = $request->especialidad_id;
        $doctor->centrosalud_id = $request->centrosalud_id;
        $doctor->categoriadoctor_id = 6;
        $doctor->tipo_medico = "En Proceso";
        $doctor->asignado_consultorio = 0;
        $doctor->categoria_medico = $request->categoria_medico;
        $doctor->state = 0;
        $doctor->user_id = Auth::user()->id;

        $visitadoctor = new VisitaDoctor();
        $visitadoctor->enrutamientolista_id = $request->id_enrutamientolista;
        $visitadoctor->fecha = $request->fecha_visita;
        $visitadoctor->estado_visita_id = 6;
        $visitadoctor->observaciones_visita = $request->observaciones_visita;
        $visitadoctor->created_by = Auth::user()->id;
        $visitadoctor->updated_by = Auth::user()->id;

        $diasSeleccionados = $request->input('dias');
        DB::transaction(function () use ($doctor, $visitadoctor, $diasSeleccionados) {
            $doctor->save();
            // Crear un arreglo con los turnos seleccionados para cada día
            $doctorday = [];
            if ($diasSeleccionados) {
                foreach ($diasSeleccionados as $dia) {
                    array_push($doctorday, ['doctor_id' => $doctor->id, 'day_id' => $dia, 'turno' => 0]);
                    // dd($doctor_day);
                }
                $doctor->days()->attach($doctorday);
            }
            $visitadoctor->doctor_id = $doctor->id;
            $visitadoctor->save();
        });
        return response()->json(['success' => true, 'message' => 'Doctor y visita guardado correctamente']);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(DoctorStoreRequest $request)
    {
        // Obtener los días seleccionados
        $diasSeleccionados = $request->input('dias');


        $doctor = new Doctor();
        $doctor->name = $request->name;
        $doctor->phone = $request->phone;
        $doctor->CMP = $request->cmp;
        $doctor->distrito_id = $request->distrito_id;
        $doctor->centrosalud_id = $request->centrosalud_id;
        $doctor->especialidad_id = $request->especialidad_id;
        $doctor->centrosalud_id = $request->centrosalud_id;
        $doctor->birthdate =  date('Y-m-d', strtotime($request->birthdate));
        $doctor->categoria_medico = $request->categoria_medico;
        $doctor->tipo_medico = $request->tipo_medico;
        $doctor->asignado_consultorio = $request->asignado_consultorio;
        $doctor->categoriadoctor_id = $request->categoria_id;
        $doctor->songs = $request->songs ?? '';
        $doctor->recovery = $request->recovery ?? 0;
        $doctor->name_secretariat = $request->name_secretariat;
        $doctor->phone_secretariat = $request->phone_secretariat;
        $doctor->observations = $request->observations;
        $doctor->user_id = Auth::user()->id;
        // dd($doctor);
        $doctor->save();
        // Crear un arreglo con los turnos seleccionados para cada día
        $doctorday = [];
        if ($diasSeleccionados) {
            foreach ($diasSeleccionados as $dia) {
                array_push($doctorday, ['doctor_id' => $doctor->id, 'day_id' => $dia, 'turno' => $request->input("turno_$dia")]);
                // dd($doctor_day);
            }
            $doctor->days()->attach($doctorday);
        }
        return redirect()->route('doctor.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $doctor = Doctor::find($id);
        $array_diasselect = [];
        foreach ($doctor->days as $diasselec) {
            array_push($array_diasselect, $diasselec->id);
        }
        $distritos = Distrito::select('id', 'name')->where('provincia_id', 128)->orWhere('provincia_id', 67)->get();
        $especialidades = Especialidad::all();
        $categorias = CategoriaDoctor::all();
        $dias = Day::all();
        return view("rutas.mantenimiento.doctor.edit",compact('distritos','especialidades','dias', 'doctor','array_diasselect','categorias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $doctor = Doctor::find($id);
        $doctor->name = $request->name;
        $doctor->phone = $request->phone;
        $doctor->CMP = $request->cmp;
        $doctor->distrito_id = $request->distrito_id;
        if ($request->centrosalud_id) {
            $doctor->centrosalud_id = $request->centrosalud_id;
        }
        $doctor->especialidad_id = $request->especialidad_id;
        $doctor->birthdate =  date('Y-m-d', strtotime($request->birthdate));
        $doctor->categoria_medico = $request->categoria_medico;
        $doctor->tipo_medico = $request->tipo_medico;
        $doctor->asignado_consultorio = $request->asignado_consultorio;
        $doctor->songs = $request->songs;
        $doctor->name_secretariat = $request->name_secretariat;
        $doctor->phone_secretariat = $request->phone_secretariat;
        $doctor->observations = $request->observations;
        $doctor->recovery = $request->recovery;
        $doctor->categoriadoctor_id = $request->categoria_id;
        $doctor->user_id = Auth::user()->id;
        $doctor->save();
        $doctor->days()->detach();
        if ($request->input('dias')) {
            // Crear un arreglo con los turnos seleccionados para cada día
            $diasSeleccionados = $request->input('dias');
            $doctorday = [];
            foreach ($diasSeleccionados as $dia) {
                array_push($doctorday, ['doctor_id' => $doctor->id, 'day_id' => $dia, 'turno' => $request->input("turno_$dia")]);
                // dd($doctor_day);
            }
            $doctor->days()->attach($doctorday);
        }
        // dd($request->input('previous_url'));
        return redirect($request->input('previous_url'))->with('success', 'doctor actualizado correctamente');;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $doctor = Doctor::find($id);
        if ($doctor->state == 1) {
            $doctor->state = 0;
            $msj = "inhabilitado";
        } else {
            $doctor->state = 1;
            $msj = "habilitado";
        }
        $doctor->save();
        return redirect()->route('doctor.index')->with('success', 'doctor ' . $msj . ' correctamente');
    }
    public function cargadata(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'archivo' => 'required|mimes:xls,xlsx',
        ]);
        // Get the uploaded file
        $file = $request->file('archivo');

        // Process the Excel file
        $doctoresImport = new DoctoresImport;

        $excel = Excel::import($doctoresImport, $file);
        return redirect()->back()->with($doctoresImport->key, $doctoresImport->data);
    }

    public function showByNameLike(Request $request)
    {
        $query = $request->get('q');

        $doctors = Doctor::where('name', 'LIKE', '%' . $query . '%')->orWhere('first_lastname', 'LIKE', '%' . $query . '%')->orWhere('second_lastname', 'LIKE', '%' . $query . '%')
            ->limit(10)
            ->get(['id', 'name','first_lastname','second_lastname']);

        return response()->json($doctors);
    }
}
