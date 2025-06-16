<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Mediciones de pH</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: center; }
        th { background-color: #f2f2f2; }
        h2 { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <h2>Reporte de Mediciones de pH</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>pH</th>
                <th>Superficie</th>
                <th>Fecha</th>
                <th>Hora</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mediciones as $i => $m)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $m->valor_ph }}</td>
                    <td>{{ $m->tipo_superficie }}</td>
                    <td>{{ $m->fecha }}</td>
                    <td>{{ $m->hora }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
