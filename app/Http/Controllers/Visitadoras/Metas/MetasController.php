<?php

namespace App\Http\Controllers\Visitadoras\Metas;

use App\Application\Services\Visitadoras\Metas\MetasService;
use App\Http\Controllers\Controller;
use App\Http\Requests\visitadoras\metas\StoreOrUpdateMetasRequest;
use App\Models\Doctor;
use App\Models\User;
use App\Models\VisitorGoal;
use Illuminate\Validation\ValidationException;

class MetasController extends Controller
{

    public function __construct(private readonly MetasService $service)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request)
    {
        // Collect allowed filters from query string
        $filters = [
            'month' => $request->query('month'),
            'tipo_medico' => $request->query('tipo_medico')
        ];

    $listOfMetas = $this->service->getListOfMetas($filters);

    // Also fetch visitadoras so the create modal can render the list
    $visitadoras = User::visitadoras()->get();
    // Fetch doctors grouped by tipo_medico to allow frontend to show doctors by type when creating a month
    $doctors = \App\Models\Doctor::select('id', 'name', 'first_lastname', 'second_lastname', 'tipo_medico')
        ->get()
        ->groupBy('tipo_medico')
        ->map(function ($group) {
            return $group->map(function ($d) {
                $parts = array_filter([$d->name ?? '', $d->first_lastname ?? '', $d->second_lastname ?? ''], fn($p) => !empty(trim($p)));
                return [
                    'id' => $d->id,
                    'name' => implode(' ', $parts),
                ];
            })->values();
        })->toArray();

    // Mostrar la lista de metas en la vista del módulo de bonificaciones
    return view('bonificaciones.index', compact('listOfMetas', 'visitadoras', 'doctors'));
    }

    /**
     * Show the form for creating a new Meta (Month, Tipo_Medico, GeneralMeta).
     */
    // public function form()
    // {
    //     $visitadoras = User::visitadoras()->get();
    //     $tipoMedicoList = Doctor::distinct()->pluck('tipo_medico')->filter()->values()->all();

    //     return view('visitadoras.metas.form', compact('visitadoras', 'tipoMedicoList'));
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrUpdateMetasRequest $request)
    {
        $validated = $request->validated();
        try {
            $this->service->create($validated);

            // If the request expects JSON (AJAX), return JSON as before.
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Metas creadas exitosamente.']);
            }

            // For normal form submissions, redirect to the bonificaciones index
            return redirect()->route('bonificaciones.index')->with('success', 'Metas creadas exitosamente.');
        } catch (ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors(),
                    'message' => 'Error de validación'
                ], 422);
            }

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $payload = $this->service->getListOfVisitorGoalByMetaId($id);

        $meta = $payload['meta'] ?? [];
        $visitorGoals = $payload['visitor_goals'] ?? [];

        $periodLabel = $meta['period_label'] ?? null;
        $tipoMedicoLabel = $meta['tipo_medico_label'] ?? ($meta['tipo_medico'] ?? null);
        $tipoMedicoSlug = $meta['tipo_medico_slug'] ?? ($meta['tipo_medico'] ?? null);
        // Renderiza las bonificaciones. vista con todos los objetivos de las visitadoras
        return view('bonificaciones.view', compact('meta', 'visitorGoals', 'periodLabel', 'tipoMedicoLabel', 'tipoMedicoSlug'));
    }

    public function getDataForChartByVisitorGoal(int $visitorGoalId)
    {
        try {
            $visitorGoal = VisitorGoal::with([
                'visitadora:id,name',
                'monthlyVisitorGoal:id,start_date,end_date,tipo_medico'
            ])
                ->select('id', 'user_id', 'goal_amount', 'debited_amount', 'monthly_visitor_goal_id')
                ->findOrFail($visitorGoalId);

            $chartData = $this->service->getDataForChart($visitorGoal);
            $visitadoraId = $visitorGoal->visitadora->id ?? $visitorGoal->user_id;
            $doctorsData = $this->service->getPedidosDoctorStatsByMonthlyVisitorGoal(
                $visitorGoal->monthlyVisitorGoal->id,
                $visitadoraId
            );
            $metaSummary = $this->service->mapMonthlyGoalToSummary($visitorGoal->monthlyVisitorGoal);

            // Ensure doctors data is returned as array (json serializable)
            $doctorsData = is_array($doctorsData) ? $doctorsData : $doctorsData->values()->all();

            return response()->json([
                'success' => true,
                'message' => 'Datos para chart obtenidos.',
                'chart-data' => $chartData,
                'doctors-data' => $doctorsData,
                'meta' => $metaSummary,
            ]);
        } catch (\Throwable $th) {
            // Manejo de error apropiado (log, respuesta de error, etc.)
            return response()->json(['success' => false, 'message' => $th->getMessage()], 500);
        }
    }
}
