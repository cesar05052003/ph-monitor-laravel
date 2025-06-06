<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Web\MedicionWebController;
use Illuminate\Http\Request;

// Página principal redirige a /mediciones
Route::get('/', function () {
    return redirect('/mediciones');
});

Route::get('/mediciones', [MedicionWebController::class, 'index']);

// Ruta API para insertar medición desde JavaScript
Route::post('/api/registrar-medicion', function (Request $request) {
    $ph = $request->input('valor_ph');
    $fecha = $request->input('fecha');
    $hora = $request->input('hora');

    // Verificar si ya existe esa entrada
    $existe = DB::table('mediciones')
        ->where('fecha', $fecha)
        ->where('hora', $hora)
        ->exists();

    if (!$existe) {
        DB::table('mediciones')->insert([
            'valor_ph' => $ph,
            'tipo_superficie' => 'Importado',
            'fecha' => $fecha,
            'hora' => $hora
        ]);
    }

    return response()->json(['success' => true]);
});

