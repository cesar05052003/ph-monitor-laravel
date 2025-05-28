<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mediciones de pH</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="container py-4">

    <h1 class="mb-4">Registro de Mediciones de pH</h1>

    <form action="/simular-medicion" method="POST">
        @csrf
        <button type="submit" class="btn btn-success mb-3">Simular Medición</button>
    </form>

    <table class="table table-bordered">
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
                    <td>{{ $m['valor_ph'] }}</td>
                    <td>{{ $m['tipo_superficie'] }}</td>
                    <td>{{ $m['fecha'] }}</td>
                    <td>{{ $m['hora'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2 class="mt-5">Gráfica de pH</h2>
    <canvas id="graficaPh" height="100"></canvas>

    <script>
        const labels = @json(collect($mediciones)->pluck('hora')->reverse()->values());
        const data = @json(collect($mediciones)->pluck('valor_ph')->reverse()->values());

        const ctx = document.getElementById('graficaPh').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'pH',
                    data: data,
                    borderColor: 'blue',
                    backgroundColor: 'rgba(0, 123, 255, 0.2)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        min: 0,
                        max: 14,
                        title: {
                            display: true,
                            text: 'pH'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Hora'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
