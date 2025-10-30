<?php

namespace App\Http\Controllers\ajustes;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\View;
use Illuminate\Http\Request;

class ViewController extends Controller
{
    public function index(Request $request)
    {
        $modules = Module::orderBy('name')->get();

        $views = View::with('module')
            ->orderBy('id')
            ->when($request->filled('module_id'), function ($query) use ($request) {
                $query->where('module_id', $request->module_id);
            })
            ->paginate(25)
            ->withQueryString();

        return view('ajustes.views.index', [
            'views' => $views,
            'modules' => $modules,
            'selectedModule' => $request->module_id,
        ]);
    }

    public function create()
    {
        $modules = Module::all();
        return view('ajustes.views.create', compact('modules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
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
            'name' => 'required',
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
