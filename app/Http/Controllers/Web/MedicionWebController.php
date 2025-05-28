<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MedicionWebController extends Controller
{
    public function index()
    {
        return view('mediciones.index', [
            'mediciones' => session('mediciones', [])
        ]);
    }

    public function simular(Request $request)
    {
        $simuladas = session('mediciones', []);

        $simuladas[] = [
            'valor_ph' => $request->input('valor_ph', round(rand(0, 140) / 10, 2)),
            'tipo_superficie' => $request->input('tipo_superficie', 'líquido'),
            'fecha' => now()->toDateString(),
            'hora' => now()->toTimeString()
        ];

        session(['mediciones' => $simuladas]);

        return redirect('/mediciones');
    }
}
