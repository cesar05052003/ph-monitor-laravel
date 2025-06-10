<!-- resources/views/inicio.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Monitor de pH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light text-center py-5">

    <div class="container">
        <h1 class="mb-4">Bienvenido al Monitor de pH</h1>
        <p class="mb-4">Sistema de monitoreo de mediciones de pH.</p>
        <a href="{{ url('/mediciones') }}" class="btn btn-primary btn-lg">Ver Mediciones</a>
    </div>

</body>
</html>
