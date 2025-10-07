<?php

namespace App\Http\Controllers\ajustes;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Role;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    public function index() {
        $roles = Role::all();
        return view('ajustes.roles.index',compact('roles'));
    }
    public function create()
    {
        return view('ajustes.roles.create');
    }
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:roles']);
        Role::create($request->all());

        return redirect()->route('roles.index')->with('success', 'Rol creado correctamente');
    }
    public function edit(Role $role)
    {
        return view('ajustes.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'description' => 'nullable|string'
        ]);
        $role->update($request->all());
        
        return redirect()->route('roles.index')->with('success', 'Rol actualizado correctamente');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Rol eliminado correctamente');
    }

    // 👉 Gestión de permisos
    public function permissions(Role $role)
    {
        $modules = Module::with('views')->get();
        return view('ajustes.roles.permissions', compact('role', 'modules'));
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $request->validate([
            'modules' => 'required|array',
            'views' => 'nullable|array',
        ], [
            'modules.required' => 'Debes seleccionar al menos un módulo.',
        ]);
        
        $role->modules()->sync($request->input('modules', []));
        $role->views()->sync($request->input('views', []));

        return redirect()->route('roles.index')->with('success', 'Permisos actualizados correctamente');
    }
}
