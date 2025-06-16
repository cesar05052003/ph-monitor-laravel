<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mediciones de pH</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<style>
    body.dark-mode {
        background-color: #121212;
        color: white;
    }

    body.dark-mode .table {
        color: white;
    }

    body.dark-mode .table-bordered th,
    body.dark-mode .table-bordered td {
        border-color: #444;
    }

    body.dark-mode .btn {
        border-color: white;
    }

    body.dark-mode .alert {
        color: white;
    }

    body.dark-mode .alert-success {
        background-color: #1e4620;
    }

    body.dark-mode .alert-danger {
        background-color: #5a1a1a;
    }

    body.dark-mode .form-control {
        background-color: #222;
        color: white;
        border-color: #555;
    }

    body.dark-mode .form-control::placeholder {
        color: #ccc;
    }
</style>

<body class="container py-4">

    <h1 class="mb-4">Registro de Mediciones de pH</h1>
    <div id="alerta-ph" class="mb-3"></div>
    <button id="toggle-dark" class="btn btn-dark mb-3">🌙 Modo Oscuro</button>
    <a href="{{ route('mediciones.pdf') }}" class="btn btn-primary mb-3" target="_blank">
    Descargar reporte PDF
</a>
 
     <form id="form-toggle-recepcion" method="POST" action="{{ route('mediciones.toggle-recepcion') }}">
    @csrf
    <button type="submit" class="btn btn-warning mb-3">
        {{ $recepcionActiva ? 'Detener recepción de datos' : 'Reanudar recepción de datos' }}
    </button>
</form>

    
     <form method="GET" action="{{ route('mediciones.index') }}" class="mb-4 d-flex align-items-end gap-2">
    <div>
        <label for="fecha" class="form-label">Filtrar por fecha:</label>
        <input type="date" name="fecha" id="fecha" class="form-control" value="{{ request('fecha') }}">
    </div>
    <button type="submit" class="btn btn-secondary">Filtrar</button>
    <a href="{{ route('mediciones.index') }}" class="btn btn-outline-secondary">Limpiar</a>
</form>


    <table class="table table-bordered" id="tabla-mediciones">
        <thead>
            <tr>
                <th>#</th>
                <th>pH</th>
                <th>Superficie</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mediciones as $i => $m)
                <tr data-id="{{ $m->id }}" @if($m->valor_ph < 6.5 || $m->valor_ph > 8.5) class="table-danger" @endif>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $m->valor_ph }}</td>
                    <td>{{ $m->tipo_superficie }}</td>
                    <td>{{ $m->fecha }}</td>
                    <td>{{ $m->hora }}</td>
                    <td>
                        <button class="btn btn-danger btn-sm eliminar-medicion" data-id="{{ $m->id }}">
                            Eliminar
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2 class="mt-5">Gráfica de pH</h2>
    <canvas id="graficaPh" height="100"></canvas>

    <script>
        // Objeto para mapear IDs de mediciones con índices del gráfico
        const medicionIndices = {};
        @foreach($mediciones as $i => $m)
            medicionIndices[{{ $m->id }}] = {{ count($mediciones) - $i - 1 }};
        @endforeach

        let labels = @json(collect($mediciones)->pluck('hora')->reverse()->values());
        let data = @json(collect($mediciones)->pluck('valor_ph')->reverse()->values());
        let medicionesIds = @json(collect($mediciones)->pluck('id')->reverse()->values());

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


        // Función para actualizar la interfaz con los últimos datos
    async function actualizarInterfaz() {
        try {
            const response = await fetch('/mediciones/ultima');
            const nuevaMedicion = await response.json();
            
            if (!nuevaMedicion || !nuevaMedicion.id) return;
            
            // Verificar si ya existe en la tabla
            const existeEnTabla = document.querySelector(`tr[data-id="${nuevaMedicion.id}"]`);
            if (existeEnTabla) return;
            
            // Agregar a la tabla
            const table = document.querySelector('#tabla-mediciones tbody');
            const fila = document.createElement('tr');
fila.setAttribute('data-id', nuevaMedicion.id);

const valorPh = parseFloat(nuevaMedicion.valor_ph);
if (valorPh < 6.5 || valorPh > 8.5) {
    fila.classList.add('table-danger'); // resaltar fila en rojo
}

 fila.innerHTML = `
    <td>${table.rows.length + 1}</td>
    <td>${nuevaMedicion.valor_ph}</td>
    <td>${nuevaMedicion.tipo_superficie}</td>
    <td>${nuevaMedicion.fecha}</td>
    <td>${nuevaMedicion.hora}</td>
    <td>
        <button class="btn btn-danger btn-sm eliminar-medicion" data-id="${nuevaMedicion.id}">
            Eliminar
        </button>
    </td>
`;

            table.prepend(fila);
            
            // Agregar al gráfico
            labels.unshift(nuevaMedicion.hora);
            data.unshift(nuevaMedicion.valor_ph);
            medicionesIds.unshift(nuevaMedicion.id);
            medicionIndices[nuevaMedicion.id] = 0;
            
            // Actualizar índices
            for (let key in medicionIndices) {
                if (key != nuevaMedicion.id) {
                    medicionIndices[key]++;
                }
            }
            
            // Limitar a 20 elementos
            if (labels.length > 20) {
                labels.pop();
                data.pop();
                const idEliminar = medicionesIds.pop();
                delete medicionIndices[idEliminar];
            }
            
            chart.update();
            
            // Actualizar el valor en tiempo real
            document.getElementById('valor-ph').textContent = nuevaMedicion.valor_ph;
            const alertaDiv = document.getElementById('alerta-ph');
const ph = parseFloat(nuevaMedicion.valor_ph);

if (ph < 6.5) {
    alertaDiv.innerHTML = `
        <div class="alert alert-danger">
            ⚠️ Alerta: El pH está en un nivel ácido (${ph}) — fuera del rango saludable (6.5 – 8.5).
        </div>`;
} else if (ph > 8.5) {
    alertaDiv.innerHTML = `
        <div class="alert alert-danger">
            ⚠️ Alerta: El pH está en un nivel básico (${ph}) — fuera del rango saludable (6.5 – 8.5).
        </div>`;
} else {
    alertaDiv.innerHTML = `
        <div class="alert alert-success">
            ✅ El pH actual (${ph}) está dentro del rango saludable (6.5 – 8.5).
        </div>`;
}

        } catch (error) {
            console.error('Error al actualizar:', error);
        }
    }

    // Configurar actualizaciones periódicas
    setInterval(actualizarInterfaz, 5000); // Actualiza cada 5 segundos
    actualizarInterfaz(); // Ejecutar inmediatamente al cargar

        // Función para eliminar una medición
        async function eliminarMedicion(id) {
            if (!confirm('¿Estás seguro de eliminar esta medición?')) return;

            try {
                const response = await fetch(`/mediciones/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                });

                if (response.ok) {
                    // Eliminar de la tabla
                    document.querySelector(`tr[data-id="${id}"]`).remove();
                    
                    // Eliminar del gráfico
                    const index = medicionIndices[id];
                    if (index !== undefined) {
                        labels.splice(index, 1);
                        data.splice(index, 1);
                        medicionesIds.splice(index, 1);
                        
                        // Actualizar índices en el objeto medicionIndices
                        delete medicionIndices[id];
                        for (let key in medicionIndices) {
                            if (medicionIndices[key] > index) {
                                medicionIndices[key]--;
                            }
                        }
                        
                        chart.update();
                    }
                    
                    // Renumerar las filas de la tabla
                    const filas = document.querySelectorAll('#tabla-mediciones tbody tr');
                    filas.forEach((fila, index) => {
                        fila.cells[0].textContent = index + 1;
                    });
                    
                    alert('Medición eliminada correctamente');
                } else {
                    throw new Error('Error al eliminar');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al eliminar la medición');
            }
        }

        // Event listeners para botones de eliminar
        document.querySelectorAll('.eliminar-medicion').forEach(btn => {
            btn.addEventListener('click', function() {
                eliminarMedicion(this.getAttribute('data-id'));
            });
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
                    // Aquí deberías hacer una petición para guardar en tu base de datos primero
                    // Y luego actualizar la tabla y gráfico con el ID que devuelva el servidor
                    // Por simplicidad, lo omito en este ejemplo
                }

            } catch (error) {
                console.error('Error al obtener datos de ThingSpeak:', error);
                document.getElementById('valor-ph').textContent = 'Error';
            }
        }

        obtenerPHdeThingSpeak();
        setInterval(obtenerPHdeThingSpeak, 5000);
    </script>

<script>
    const toggleBtn = document.getElementById('toggle-dark');

    // Guardar preferencia en localStorage
    if (localStorage.getItem('modoOscuro') === 'true') {
        document.body.classList.add('dark-mode');
        toggleBtn.textContent = '☀️ Modo Claro';
    }

    toggleBtn.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        const activado = document.body.classList.contains('dark-mode');
        toggleBtn.textContent = activado ? '☀️ Modo Claro' : '🌙 Modo Oscuro';
        localStorage.setItem('modoOscuro', activado);
    });
</script>


</body>
</html>