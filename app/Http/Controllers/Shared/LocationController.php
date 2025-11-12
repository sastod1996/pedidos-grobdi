<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\Provincia;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function departamentos(): JsonResponse
    {
        $departamentos = Departamento::select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $departamentos,
        ]);
    }

    public function provincias(Departamento $departamento): JsonResponse
    {
        $provincias = $departamento->provincias()
            ->select('id', 'name', 'departamento_id')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $provincias,
        ]);
    }

    public function distritos(Provincia $provincia): JsonResponse
    {
        $distritos = $provincia->distritos()
            ->select('id', 'name', 'provincia_id')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $distritos,
        ]);
    }

    public function distritoChain(Distrito $distrito): JsonResponse
    {
        $distrito->loadMissing('provincia.departamento');

        $provincia = $distrito->provincia;
        $departamento = $provincia?->departamento;

        $provincias = $departamento
            ? Provincia::select('id', 'name', 'departamento_id')
                ->where('departamento_id', $departamento->id)
                ->orderBy('name')
                ->get()
            : collect();

        $distritos = $provincia
            ? Distrito::select('id', 'name', 'provincia_id')
                ->where('provincia_id', $provincia->id)
                ->orderBy('name')
                ->get()
            : collect();

        return response()->json([
            'departamento' => $departamento
                ? [
                    'id' => $departamento->id,
                    'name' => $departamento->name,
                ]
                : null,
            'provincia' => $provincia
                ? [
                    'id' => $provincia->id,
                    'name' => $provincia->name,
                    'departamento_id' => $provincia->departamento_id,
                ]
                : null,
            'distrito' => [
                'id' => $distrito->id,
                'name' => $distrito->name,
                'provincia_id' => $distrito->provincia_id,
            ],
            'provincias' => $provincias,
            'distritos' => $distritos,
        ]);
    }
}
