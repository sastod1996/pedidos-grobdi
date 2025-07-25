<?php

namespace App\Http\Controllers\pedidos\contabilidad;

use App\Exports\pedidos\PedidoscontabilidadExport;
use App\Http\Controllers\Controller;
use App\Models\Pedidos;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Intervention\Image\Colors\Rgb\Channels\Red;
use Maatwebsite\Excel\Facades\Excel;

class PedidosContaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if($request->query("fecha_inicio")){
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            ]);
            $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
            $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();
        
            // Realizar la búsqueda en la base de datos
            $pedidos = Pedidos::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->orderBy('created_at','asc')->latest()->get();

        }else{
            $pedidos = Pedidos::orderBy('created_at','asc')
            ->where('created_at',date('Y-m-d'))
            ->latest()->get();

        }
        return view('pedidos.contabilidad.index', compact('pedidos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }
    public function update(Request $request, $id)
    {
        $pedidos = Pedidos::find($id);
        $pedidos->accountingStatus = $request->accountingStatus;
        $pedidos->bancoDestino = $request->bancoDestino;
        $pedidos->save();
        return response()->json([
            'id' => $pedidos->id,
            'orderId' => $pedidos->orderId,
            'customerName' => $pedidos->customerName,
            'created_at' => $pedidos->created_at->format('d-m-Y'),
            'paymentStatus' => $pedidos->paymentStatus,
            'accountingStatus' => $pedidos->accountingStatus,
            'voucher' => $pedidos->voucher,
            'accountingStatusLabel' => $pedidos->accountingStatus == 1 
                ? '<i class="fa fa-check" aria-hidden="true"></i> Revisado'
                : '<i class="fa fa-times" aria-hidden="true"></i> Sin revisar',
            'voucherLabel' => $pedidos->voucher == 0 
                ? '<span class="badge rounded-pill bg-danger">Sin imagen</span>' 
                : '<span class="badge rounded-pill bg-success">Imagen</span>'
        ]);
    }
    public function downloadExcel($fecha_inicio,$fecha_fin){
        $dia = date('d-m-Y');
        return Excel::download(new PedidoscontabilidadExport($fecha_inicio,$fecha_fin), 'reporte-'.$dia.'.xlsx');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
