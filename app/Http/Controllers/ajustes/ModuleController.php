<?php

namespace App\Http\Controllers\ajustes;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index()
    {
        $modules = Module::all();
        return view('ajustes.modules.index', compact('modules'));
    }
    public function create()
    {
        return view('ajustes.modules.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:modules','description'=>'nullable']);
        Module::create($request->all());

        return redirect()->route('modules.index')->with('success', 'Módulo creado correctamente');
    }

    public function edit(Module $module)
    {
        return view('ajustes.modules.edit', compact('module'));
    }

    public function update(Request $request, Module $module)
    {
        $request->validate([
            'name' => 'required|unique:modules,name,' . $module->id,
            'description' => 'nullable|string'
        ]);
        $module->update($request->all());

        return redirect()->route('modules.index')->with('success', 'Módulo actualizado correctamente');
    }

    public function destroy(Module $module)
    {
        $module->delete();
        return redirect()->route('modules.index')->with('success', 'Módulo eliminado correctamente');
    }
}
