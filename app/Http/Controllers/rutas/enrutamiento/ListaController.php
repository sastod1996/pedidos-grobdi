<?php

namespace App\Http\Controllers\rutas\enrutamiento;

use App\Http\Controllers\Controller;
use App\Models\Distrito;
use App\Models\Lista;
use App\Models\Zone;
use Illuminate\Http\Request;

class ListaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $listas = Lista::with('zone')->get()->sortBy('zone.name');
        return view('rutas.lista.index',compact('listas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $zonas = Zone::whereNotIn('id',[1,5])->get();
        $distritos = Distrito::select('id','name')
                        ->where('provincia_id',128)
                        ->orWhere('provincia_id',67)
                        ->orderBy('name')->get();
                        
        return view('rutas.lista.create',compact('zonas','distritos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'zone_id' => 'required',
        ]);
        $lista = new Lista();
        $lista->name = $request->name;
        $lista->recovery = $request->recovery;
        $lista->zone_id = $request->zone_id;
        $lista->save();

        $lista->distritos()->sync($request->distritos);
        return redirect()->route('lista.index')->with('success','La lista fue creada correctamente');
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
        $lista = Lista::find($id);
        $zonas = Zone::whereNotIn('id',[1,5])->get();
        $distritos = Distrito::select('id','name')->where('provincia_id',128)->orWhere('provincia_id',67)->orderBy('name')->get();

        return view('rutas.lista.edit',compact('lista','distritos','zonas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        
        $lista =  Lista::find($id);
        $request->validate([
            'name' => 'required',
            'zone_id' => 'required',
            'recovery' => 'required',
        ]);
        $lista->update([
            'name' => $request->name,
            'zone_id' => $request->zone_id,
            'recovery' => $request->recovery
        ]);
        $lista->distritos()->sync($request->distritos);
        return redirect()->route('lista.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
