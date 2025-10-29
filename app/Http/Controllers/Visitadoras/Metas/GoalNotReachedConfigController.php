<?php

namespace App\Http\Controllers\Visitadoras\Metas;

use App\Application\Services\Visitadoras\Metas\GoalNotReachedConfigService;
use App\Http\Controllers\Controller;
use App\Http\Requests\visitadoras\metas\StoreGoalNotReachedConfigRequest;
use App\Http\Resources\GoalNotReachedConfigResource;
use App\Models\GoalNotReachedConfig;
use Illuminate\Validation\ValidationException;

class GoalNotReachedConfigController extends Controller
{

    public function __construct(protected readonly GoalNotReachedConfigService $service)
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGoalNotReachedConfigRequest $request)
    {
        $validated = $request->validated();

        try {
            $this->service->create($validated);
            return response()->json(['success' => true, 'message' => 'Configuración para metas creada correctamente.']);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Error de validación'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(GoalNotReachedConfig $goalNotReachedConfig)
    {
        $goalNotReachedConfig->load('details');
        return new GoalNotReachedConfigResource($goalNotReachedConfig);
    }

    public function showActive()
    {
        $goalNotReachedConfig = GoalNotReachedConfig::where('state', true)->first()->load('details');
        return new GoalNotReachedConfigResource($goalNotReachedConfig);
    }
}
