<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Medicion;

class MedicionWebController extends Controller
{
    public function descargarPDF()
    {
        $mediciones = Medicion::orderBy('fecha', 'desc')->orderBy('hora', 'desc')->get();

        $pdf = Pdf::loadView('mediciones.reporte', compact('mediciones'));

        return $pdf->download('reporte_mediciones_ph.pdf');
    }

    public function index(Request $request)
    {
        $this->actualizarDesdeThingSpeak();

        $query = DB::table('mediciones');

        if ($request->filled('fecha')) {
            $query->whereDate('fecha', $request->input('fecha'));
        }

        $mediciones = $query->orderByDesc('fecha')
                            ->orderByDesc('hora')
                            ->get();

        $recepcionActiva = DB::table('configuraciones')
                            ->where('clave', 'recepcion_activa')
                            ->value('valor');

        return view('mediciones.index', [
            'mediciones' => $mediciones,
            'recepcionActiva' => $recepcionActiva
        ]);
    }

    public function actualizarDesdeThingSpeak()
    {
        $activo = DB::table('configuraciones')
                  ->where('clave', 'recepcion_activa')
                  ->value('valor');

        if (!$activo) {
            return false;
        }

        $apiKey = 'N6CLG1BHFP4YBY1R';
        $channelId = '2983047';

        $response = Http::get("https://api.thingspeak.com/channels/{$channelId}/feeds/last.json", [
    'api_key' => $apiKey
       ]);


        if ($response->successful()) {
            $data = $response->json();
            $feed = $data ?? null;

            if ($feed && !empty($feed['field1'])) {
                $ph = floatval($feed['field1']);
                $fecha = date('Y-m-d', strtotime($feed['created_at']));
                $hora = date('H:i:s', strtotime($feed['created_at']));

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

                    return true; // Se insertó nueva medición
                }
            }
        }

        return false; // No hubo cambios
    }

    public function getLatestMeasurement()
    {
        $this->actualizarDesdeThingSpeak();

        $latest = DB::table('mediciones')
                   ->orderByDesc('fecha')
                   ->orderByDesc('hora')
                   ->first();

        return response()->json($latest);
    }

    public function destroy($id)
    {
        try {
            $medicion = DB::table('mediciones')->where('id', $id)->first();

            if (!$medicion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Medición no encontrada'
                ], 404);
            }

            DB::table('mediciones')->where('id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Medición eliminada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la medición',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleRecepcion()
    {
        $estadoActual = DB::table('configuraciones')
                        ->where('clave', 'recepcion_activa')
                        ->value('valor');

        DB::table('configuraciones')
            ->where('clave', 'recepcion_activa')
            ->update(['valor' => !$estadoActual]);

        return redirect()->route('mediciones.index');
    }
}
