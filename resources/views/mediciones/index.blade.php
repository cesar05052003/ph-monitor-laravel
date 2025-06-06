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

    <!-- 🔹 Valor en tiempo real desde ThingSpeak -->
    <div class="alert alert-info" id="ph-thingspeak">
        Último valor de pH desde ThingSpeak: <strong><span id="valor-ph">Cargando...</span></strong>
    </div>

    <table class="table table-bordered" id="tabla-mediciones">
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

    <h2 class="mt-5">Gráfica de pH</h2>
    <canvas id="graficaPh" height="100"></canvas>

    <script>
        let labels = @json(collect($mediciones)->pluck('hora')->reverse()->values());
        let data = @json(collect($mediciones)->pluck('valor_ph')->reverse()->values());

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

        async function obtenerPHdeThingSpeak() {
            try {
                const url = 'https://api.thingspeak.com/channels/2983047/feeds.json?api_key=N6CLG1BHFP4YBY1R&results=1';
                const respuesta = await fetch(url);
                const datos = await respuesta.json();

                const ultimoDato = datos.feeds[0];
                const valorPH = parseFloat(ultimoDato.field1).toFixed(2);
                const fecha = new Date(ultimoDato.created_at);

                document.getElementById('valor-ph').textContent = valorPH;

                const hora = fecha.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                const fechaStr = fecha.toISOString().split('T')[0];

                // Verifica si ya existe en tabla
                const existe = labels.includes(hora);
                if (!existe) {
                    // Agregar a la tabla
                    const table = document.querySelector('#tabla-mediciones tbody');
                    const fila = document.createElement('tr');
                    fila.innerHTML = `
                        <td>${table.rows.length + 1}</td>
                        <td>${valorPH}</td>
                        <td>Importado</td>
                        <td>${fechaStr}</td>
                        <td>${hora}</td>
                    `;
                    table.prepend(fila);

                    // Agregar al gráfico
                    labels.unshift(hora);
                    data.unshift(valorPH);
                    if (labels.length > 20) {
                        labels.pop();
                        data.pop();
                    }
                    chart.update();
                }

            } catch (error) {
                console.error('Error al obtener datos de ThingSpeak:', error);
                document.getElementById('valor-ph').textContent = 'Error';
            }
        }

        obtenerPHdeThingSpeak();
        setInterval(obtenerPHdeThingSpeak, 5000); // Actualiza cada 5 seg
    </script>

</body>
</html>
