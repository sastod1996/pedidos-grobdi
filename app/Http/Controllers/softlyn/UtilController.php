<?php

namespace App\Http\Controllers\softlyn;
use App\Http\Controllers\Controller;

use App\Models\Util;
use App\Models\Articulo;
use Illuminate\Http\Request;

class UtilController extends Controller
{
    public function index()
    {
        $estado = request()->estado;

        $utiles = Util::with('articulo.ultimaCompra')
            ->whereHas('articulo', function ($query) use ($estado) {
                if ($estado === 'inactivo') {
                    $query->where('estado', 'inactivo');
                } else {
                    $query->where('estado', 'activo');
                }
            })
            ->orderBy('id', 'desc')
            ->get();

        return view('cotizador.util.index', compact('utiles'));
    }

    public function create()
    {
        $utiles = Util::all();

        return view("cotizador.util.create", compact("utiles"));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255|unique:articulos,nombre',
            'precio' => 'required|numeric|min:0',
        ], [
            'nombre.unique' => 'Ya existe un artículo con ese nombre.',
        ]);

        $articulo = Articulo::create([
            'nombre' => $data['nombre'],
            'tipo' => 'util',
            'stock' => 0,
            'estado' => 'activo',
        ]);

        Util::create([
            'articulo_id' => $articulo->id,
            'precio' => $data['precio'],
        ]);

        return redirect()->route('util.index')->with('success', 'Útil registrado correctamente.');
    }

    public function edit($id)
    {
        $util = Util::findOrFail($id);
        return view('cotizador.util.edit', compact('util'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255|unique:articulos,nombre,' . $id,
            'precio' => 'required|numeric|min:0',
            'estado' => 'nullable|in:activo,inactivo',
        ], [
            'nombre.unique' => 'Ya existe un artículo con ese nombre.',
        ]);

        $articulo = Articulo::findOrFail($id);
        $articulo->update([
            'nombre' => $data['nombre'],
            'estado' => $data['estado'] ?? 'activo',
        ]);

        $util = Util::where('articulo_id', $id)->first();
        if ($util) {
            $util->update([
                'precio' => $data['precio'],
            ]);
        }

        return redirect()->route('util.index')->with('success', 'Útil actualizado correctamente.');
    }

    public function destroy($id)
    {
        $articulo = Articulo::findOrFail($id);

        if ($articulo->estado === 'inactivo') {
            return redirect()->back()->with('error', 'Este útil ya está inactivo. Puedes activarlo desde la pantalla de edición.');
        }

        $articulo->update([
            'estado' => 'inactivo',
        ]);

        return redirect()->route('util.index', [ 'estado' => 'inactivo'])->with('error', 'Útil marcado como inactivo.');
    }
}

