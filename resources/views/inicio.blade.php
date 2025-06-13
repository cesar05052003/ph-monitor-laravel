<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Monitor pH Check-Pro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --bg-color: #f0f8ff;
            --text-color: #212529;
            --card-bg: #ffffff;
            --btn-bg: #0d6efd;
            --btn-text: #ffffff;
        }

        [data-theme="dark"] {
            --bg-color: #1e1e1e;
            --text-color: #f1f1f1;
            --card-bg: #2c2c2c;
            --btn-bg: #0d6efd;
            --btn-text: #ffffff;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
            transition: background-color 0.3s, color 0.3s;
        }

        .card-welcome {
            background-color: var(--card-bg);
            padding: 3rem;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
            animation: fadeIn 0.7s ease;
        }

        .card-welcome img {
            max-height: 80px;
            margin-bottom: 1rem;
        }

        .btn-primary {
            background-color: var(--btn-bg);
            border-color: var(--btn-bg);
            color: var(--btn-text);
        }

        .toggle-theme {
            position: absolute;
            top: 1rem;
            right: 1rem;
            border: none;
            background: transparent;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-color);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body data-theme="light">

    <button class="toggle-theme" id="toggleTheme" title="Cambiar modo 🌙/☀️">🌓</button>

    <div class="card-welcome">
        <img src="https://cdn-icons-png.flaticon.com/512/2942/2942199.png" alt="Logo Check-Pro">

        <h1 class="mb-3">Bienvenido a <span class="text-primary">Check-Pro</span></h1>
        <p class="mb-4">Monitoreo inteligente de niveles de pH para limpieza profesional.</p>
        <a href="{{ url('/mediciones') }}" class="btn btn-primary btn-lg">📈 Ver Mediciones</a>
    </div>

    <script>
        const toggleBtn = document.getElementById('toggleTheme');
        const body = document.body;

        function setTheme(theme) {
            body.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
        }

        toggleBtn.addEventListener('click', () => {
            const current = body.getAttribute('data-theme');
            setTheme(current === 'dark' ? 'light' : 'dark');
        });

        // Cargar tema guardado o usar preferencia del sistema
        const savedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (savedTheme) {
            setTheme(savedTheme);
        } else if (prefersDark) {
            setTheme('dark');
        }
    </script>

</body>
</html>
