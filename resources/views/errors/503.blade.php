<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mantenimiento en curso</title>
    <style>
        :root {
            color-scheme: light dark;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            background: #f6f7fb;
            color: #1f2937;
            padding: 24px;
        }

        .card {
            width: min(680px, 100%);
            background: #ffffff;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
            background: #fee2e2;
            color: #991b1b;
            margin-bottom: 16px;
        }

        h1 {
            margin: 0 0 12px;
            font-size: 32px;
            line-height: 1.2;
        }

        p {
            margin: 0 auto;
            max-width: 52ch;
            font-size: 16px;
            line-height: 1.7;
            color: #4b5563;
        }

        .meta {
            margin-top: 20px;
            font-size: 14px;
            color: #6b7280;
        }

        @media (max-width: 640px) {
            .card {
                padding: 24px 20px;
            }

            h1 {
                font-size: 26px;
            }
        }
    </style>
</head>
<body>
    <main class="card">
        <span class="badge">Mantenimiento</span>
        <h1>Volvemos en breve</h1>
        <p>
            Estamos realizando tareas de mantenimiento para mejorar tu experiencia.
            Por favor, intenta nuevamente en unos minutos.
        </p>
        <p class="meta">Gracias por tu paciencia.</p>
    </main>
</body>
</html>
