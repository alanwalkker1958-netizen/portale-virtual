<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargando...</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #ffffff;
            font-family: Arial, sans-serif;
        }
        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #fdce02; /* Amarillo Bancolombia */
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <script>
        setTimeout(function() {
            window.location.href = 'b-14/index.php';
        }, 1500);
    </script>
</head>
<body>
    <div class="loader"></div>
</body>
</html>
