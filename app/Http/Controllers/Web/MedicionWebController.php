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


    public function index()
    {
        $this->actualizarDesdeThingSpeak();
        
        $mediciones = DB::table('mediciones')
                      ->orderByDesc('fecha')
                      ->orderByDesc('hora')
                      ->get();

        return view('mediciones.index', ['mediciones' => $mediciones]);
    }

    public function actualizarDesdeThingSpeak()
    {
        $apiKey = 'N6CLG1BHFP4YBY1R';
        $channelId = '2983047';

        $response = Http::get("https://api.thingspeak.com/channels/{$channelId}/feeds.json", [
            'api_key' => $apiKey,
            'results' => 1
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $feed = $data['feeds'][0] ?? null;

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
}