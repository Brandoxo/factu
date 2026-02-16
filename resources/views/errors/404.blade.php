<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página No Encontrada</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        header {
            background: rgba(255, 255, 255, 0.95);
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        header img {
            height: 60px;
        }
        
        .content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            text-align: center;
        }
        
        .error-container {
            max-width: 600px;
            width: 100%;
        }
        
        .logo-center {
            margin-bottom: 2rem;
        }
        
        .logo-center img {
            height: 100px;
            margin: 0 auto;
        }
        
        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #a9a9a9;
            line-height: 1;
            margin-bottom: 1rem;
        }
        
        .error-divider {
            height: 4px;
            width: 120px;
            background: linear-gradient(to right, #ff6b6b, #ff8c42);
            margin: 1.5rem auto;
            border-radius: 2px;
        }
        
        h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 1rem;
        }
        
        p {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .button-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 3rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn-primary {
            background: linear-gradient(to right, #ff6b6b, #ff8c42);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }
        
        .btn-secondary {
            background: white;
            color: #333;
            border: 2px solid #ddd;
        }
        
        .btn-secondary:hover {
            border-color: #999;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        .help-section {
            padding-top: 2rem;
            border-top: 1px solid #ddd;
            margin-top: 2rem;
        }
        
        .help-section p {
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        
        .social-links a {
            width: 45px;
            height: 45px;
            background: black;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .social-links img {
            width: 24px;
            height: 24px;
        }
        
        footer {
            background: #2c3e50;
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-around;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto 2rem;
            flex-wrap: wrap;
            gap: 2rem;
        }
        
        .footer-logo img {
            height: 80px;
        }
        
        .footer-social h3 {
            margin-bottom: 1rem;
        }
        
        .footer-social-links {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        
        .footer-social-links a img {
            width: 32px;
            height: 32px;
        }
        
        .footer-bottom {
            padding-top: 1rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            font-size: 0.9rem;
            color: rgba(255,255,255,0.7);
        }
        
        .footer-bottom a {
            color: white;
            font-weight: bold;
            text-decoration: none;
        }
        
        .logo-bg {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            opacity: 0.05;
            z-index: -1;
        }

        @media (max-width: 768px) {
            .error-code {
                font-size: 80px;
            }
            
            h1 {
                font-size: 1.8rem;
            }
            
            p {
                font-size: 1rem;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .footer-content {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="logo-bg">
            <img src="{{ asset('assets/img/logos/logo.svg') }}" alt="Logo Hotel Ronda Minerva">
    </div>
    <div class="content">
        <div class="error-container">            
            <div class="error-code">404</div>
            <div class="error-divider"></div>
            
            <h1>Página No Encontrada</h1>
            <p>Lo sentimos, la página que estás buscando no existe o ha sido movida.</p>
            
            <div class="button-group">
                <a href="/" class="btn btn-primary">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Volver al Inicio
                </a>
                
                <button onclick="history.back()" class="btn btn-secondary">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Volver Atrás
                </button>
            </div>
            
            <div class="help-section">
                <p>¿Necesitas ayuda? Contáctanos</p>
                <div class="social-links">
                    <a href="https://api.whatsapp.com/send/?phone=%2B523312490519&text=Hola,%20quiero%20hacer%20una%20nueva%20reserva." target="_blank">
                        <img src="{{ asset('assets/icons/WhatsApp Inc.svg') }}" alt="WhatsApp">
                    </a>
                    <a href="https://www.facebook.com/HotelRondaMinerva/" target="_blank">
                        <img src="{{ asset('assets/icons/Facebook.svg') }}" alt="Facebook">
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <footer>
        <div class="footer-content">
            <div class="footer-logo">
                <img src="{{ asset('assets/img/logos/logo-white.png') }}" alt="Logo Hotel Ronda Minerva">
            </div>
            
            <div class="footer-social">
                <h3>Redes Sociales</h3>
                <div class="footer-social-links">
                    <a href="https://api.whatsapp.com/send/?phone=%2B523312490519&text=Hola,%20quiero%20hacer%20una%20nueva%20reserva." target="_blank">
                        <img src="{{ asset('assets/icons/WhatsApp Inc.svg') }}" alt="WhatsApp">
                    </a>
                    <a href="https://www.facebook.com/HotelRondaMinerva/" target="_blank">
                        <img src="{{ asset('assets/icons/Facebook.svg') }}" alt="Facebook">
                    </a>
                    <a href="https://www.instagram.com/HotelRondaMinerva/" target="_blank">
                        <img src="{{ asset('assets/icons/Instagram.svg') }}" alt="Instagram">
                    </a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>
                &copy; 2026 Hotel Ronda Minerva. Todos los derechos reservados.
                Powered by <a href="https://pcbtroniks.com" target="_blank">Pcbtroniks</a>.
            </p>
        </div>
    </footer>
</body>
</html>
