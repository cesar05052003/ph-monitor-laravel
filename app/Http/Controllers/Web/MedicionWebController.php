<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class MedicionWebController extends Controller
{
    public function index()
    {
        // 🔹 Obtener desde ThingSpeak
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

                // Verificar si ya está registrado
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
            }
        }

        // 🔹 Obtener todas las mediciones para la vista
        $mediciones = DB::table('mediciones')->orderByDesc('fecha')->orderByDesc('hora')->get();

        return view('mediciones.index', ['mediciones' => $mediciones]);
    }
}
