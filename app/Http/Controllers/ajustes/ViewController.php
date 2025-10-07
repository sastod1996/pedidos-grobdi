<?php

namespace App\Http\Controllers\ajustes;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\View;
use Illuminate\Http\Request;

class ViewController extends Controller
{
    public function index()
    {
        $views = View::with('module')->get();
        return view('ajustes.views.index', compact('views'));
    }

    public function create()
    {
        $modules = Module::all();
        return view('ajustes.views.create', compact('modules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required',
            'url' => 'required|unique:views',
            'module_id' => 'required|exists:modules,id',
            'is_menu' => 'nullable|boolean',
        ]);
        View::create($request->all());

        return redirect()->route('views.index')->with('success', 'Vista creada correctamente');
    }

    public function edit(View $view)
    {
        $modules = Module::all();
        return view('ajustes.views.edit', compact('view', 'modules'));
    }

    public function update(Request $request, View $view)
    {
        $request->validate([
            'description' => 'required',
            'url' => 'required|unique:views,url,' . $view->id,
            'module_id' => 'required|exists:modules,id',
            'is_menu' => 'nullable|boolean',
        ]);
        $view->update($request->all());
        return redirect()->route('views.index')->with('success', 'Vista actualizada correctamente');
    }

    public function destroy(View $view)
    {
        $view->delete();
        return redirect()->route('views.index')->with('success', 'Vista eliminada correctamente');
    }
}
