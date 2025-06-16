<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Medicion;

class MedicionController extends Controller
{
    public function store(Request $request)
    {
        // Validar los datos
        $request->validate([
            'valor_ph' => 'required|numeric',
            'tipo_superficie' => 'required|string',
        ]);

        // Crear la medición en base de datos
        $medicion = Medicion::create([
            'valor_ph' => $request->valor_ph,
            'tipo_superficie' => $request->tipo_superficie,
            'fecha' => now()->toDateString(),
            'hora' => now()->toTimeString(),
        ]);

        // Respuesta en formato JSON
        return response()->json([
            'status' => 'ok',
            'medicion' => $medicion
        ]);
    }
}
