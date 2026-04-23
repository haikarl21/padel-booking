<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Aplikasi booking lapangan padel online dengan sistem pembayaran Midtrans">
    <meta name="theme-color" content="#0d6efd">
    <meta name="msapplication-TileColor" content="#0d6efd">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">
    <title>Padel House - Lapangan Padel Profesional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-dark: #0a0a0a;
            --secondary-dark: #1a1a1a;
            --accent-orange: #FFA500;
            --text-white: #ffffff;
            --text-grey: #999999;
        }
        
        body {
            background-color: var(--primary-dark);
            background-image: linear-gradient(135deg, rgba(10, 10, 10, 0.45) 0%, rgba(20, 20, 20, 0.35) 100%), url('{{ asset('images/court.jpg') }}');
            background-size: 100% auto;
            background-position: center top;
            background-attachment: fixed;
            background-repeat: repeat-y;
            color: var(--text-white);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        main {
            width: 100%;
            min-height: 100vh;
            display: block;
            position: relative;
        }
        
        .navbar-custom {
            background-color: var(--primary-dark);
            border-bottom: 1px solid var(--secondary-dark);
            padding: 1.5rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            position: relative;
            z-index: 1000;
        }
        
        .navbar-brand {
            color: var(--accent-orange) !important;
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        .nav-link {
            color: var(--text-white) !important;
            margin-left: 2rem;
            transition: color 0.3s ease;
            font-weight: 500;
        }
        
        .nav-link:hover {
            color: var(--accent-orange) !important;
        }
        
        .card-custom {
            background-color: var(--secondary-dark);
            border: 1px solid #333333;
            border-radius: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .card-custom:hover {
            border-color: var(--accent-orange);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        
        .btn-primary-custom {
            background-color: var(--accent-orange);
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            color: #000000;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(255, 165, 0, 0.3);
        }
        
        .btn-primary-custom:hover {
            background-color: #ffb81a;
            transform: scale(1.02);
            color: #000000;
            box-shadow: 0 4px 15px rgba(255, 165, 0, 0.4);
        }
        
        /* ensure booking buttons are equal height and cards stretch */
        .card-custom {
            display: flex;
            flex-direction: column;
        }
        .card-custom .btn {
            min-height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .hero-section {
            background-color: var(--primary-dark);
            background-image: linear-gradient(135deg, rgba(10, 10, 10, 0.75) 0%, rgba(20, 20, 20, 0.6) 100%), url('{{ asset('images/court.jpg') }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(10, 10, 10, 0.75) 0%, rgba(20, 20, 20, 0.6) 100%);
            z-index: 1;
        }
        
        .hero-section > * {
            position: relative;
            z-index: 2;
        }
        
        .hero-content {
            width: 100%;
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #ffffff;
        }
        
        .hero-content h1,
        .hero-content h2,
        .hero-content p {
            color: #ffffff !important;
            opacity: 1 !important;
            visibility: visible !important;
            display: block !important;
        }
        
        .hero-text-wrapper {
            width: 100%;
        }
        
        .stats-hero-section {
            background-color: var(--primary-dark);
            background-image: linear-gradient(135deg, rgba(10, 10, 10, 0.45) 0%, rgba(20, 20, 20, 0.35) 100%), url('{{ asset('images/court.jpg') }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            padding: 50px 0;
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .stats-hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(10, 10, 10, 0.45) 0%, rgba(20, 20, 20, 0.35) 100%);
            z-index: 1;
        }
        
        .stats-card {
            background-color: rgba(20, 20, 20, 0.7);
            border: 2px solid rgba(255, 165, 0, 0.5);
            border-radius: 20px;
            padding: 30px 20px;
            text-align: center;
            color: var(--text-white);
            backdrop-filter: blur(8px);
            transition: all 0.3s ease;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 2;
        }
        
        .stats-card:hover {
            border-color: #FFA500;
            background-color: rgba(26, 26, 26, 0.95);
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(255, 165, 0, 0.25);
        }
        
        .stats-card i {
            font-size: 3rem;
            background: linear-gradient(135deg, #FFA500 0%, #FFB81A 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }
        
        h1, h2, h3, h4, h5, h6 {
            color: var(--text-white);
        }
        
        .text-warning {
            color: var(--accent-orange) !important;
        }
        
        footer {
            background-color: var(--secondary-dark) !important;
            border-top: 1px solid #333333;
            background-image: linear-gradient(135deg, rgba(10, 10, 10, 0.95) 0%, rgba(20, 20, 20, 0.92) 100%), url('{{ asset('images/court.jpg') }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            position: relative;
        }
        
        footer > * {
            position: relative;
            z-index: 2;
        }
        
        footer .container {
            position: relative;
            z-index: 2;
        }
        
        footer a,
        footer p {
            color: #ffffff;
        }
        
        footer a:hover {
            color: #FFA500;
        }
        
        .form-control, .form-select {
            background-color: var(--secondary-dark) !important;
            color: var(--text-white) !important;
            border-color: #333333 !important;
        }
        
        .form-control::placeholder {
            color: #666666 !important;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-orange) !important;
            box-shadow: 0 0 0 0.2rem rgba(255, 165, 0, 0.25) !important;
            background-color: var(--secondary-dark) !important;
            color: var(--text-white) !important;
        }
        
        /* Date Input Styling */
        input[type="date"],
        input[type="time"],
        input[type="datetime-local"],
        input[type="month"],
        input[type="week"] {
            accent-color: var(--accent-orange);
        }
        
        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="time"]::-webkit-calendar-picker-indicator,
        input[type="datetime-local"]::-webkit-calendar-picker-indicator,
        input[type="month"]::-webkit-calendar-picker-indicator,
        input[type="week"]::-webkit-calendar-picker-indicator {
            filter: invert(1) brightness(1.2);
            cursor: pointer;
        }
        
        .form-label {
            color: var(--text-white) !important;
        }
        
        .form-text {
            color: #ffffff !important;
        }
        
        /* Hero Section Animations */
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        .btn-primary-custom:hover {
            background-color: #ffb81a;
            transform: scale(1.05);
            color: #000000;
            box-shadow: 0 6px 20px rgba(255, 165, 0, 0.5);
        }
        
        .btn-outline-light:hover {
            background-color: rgba(255, 165, 0, 0.1);
            color: #FFA500;
            border-color: #FFA500;
        }
        
        /* PWA Install Button Styling */
        #pwa-install-button .btn {
            background: linear-gradient(135deg, #FFA500 0%, #ffb81a 100%);
            border: none;
            color: #000 !important;
            font-weight: 600;
            padding: 10px 16px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(255, 165, 0, 0.4);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        #pwa-install-button .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(255, 165, 0, 0.6);
            background: linear-gradient(135deg, #ffb81a 0%, #FFA500 100%);
        }
        
        #pwa-install-button .btn i {
            font-size: 1.1rem;
        }

        /* Ensure modals are always on top and clickable */
        .modal {
            z-index: 2000;
        }

        .modal-backdrop {
            z-index: 1990;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('home') }}">
                <i class="fas fa-tennis-ball me-2"></i>Padel House
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('track-booking') }}">Lacak Booking</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary-custom" href="{{ route('home') }}">Beranda</a>
                    </li>
                    <li class="nav-item d-none" id="pwa-install-button" style="margin-left: 10px;">
                    
                    
                    
                    
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer class="py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0" style="color: #ffffff;">© 2026 Padel House - Lapangan Padel Profesional</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- PWA Service Worker Registration & Install Prompt -->
    <script>
        // ========== Service Worker Registration ==========
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('{{ asset("service-worker.js") }}?v=2.0.0', {
                    scope: '/'
                }).then(registration => {
                    console.log('✓ Service Worker registered successfully (v2.0.0)');
                    
                    // Check untuk updates setiap jam
                    setInterval(() => {
                        registration.update();
                    }, 60 * 60 * 1000);
                    
                    // Notify user jika ada update
                    registration.addEventListener('updatefound', () => {
                        const newWorker = registration.installing;
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                console.log('✓ New version available, refresh to update');
                                // Opsional: tampilkan notifikasi ke user
                            }
                        });
                    });
                }).catch(error => {
                    console.error('✗ Service Worker registration failed:', error);
                });
            });
        }
        
        // ========== PWA Install Prompt ==========
        let deferredPrompt;
        
        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent the mini-infobar dari tampil
            e.preventDefault();
            // Simpan event untuk dipanggil nanti
            deferredPrompt = e;
            
            // Tampilkan install button jika ada
            const installBtn = document.getElementById('pwa-install-button');
            if (installBtn) {
                // Remove d-none class (lebih baik dari style.display)
                installBtn.classList.remove('d-none');
                console.log('✓ Install button revealed');
            }
        });
        
        // Handle install button click
        const installBtn = document.getElementById('pwa-install-button');
        if (installBtn) {
            installBtn.addEventListener('click', async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    console.log(`✓ User response: ${outcome}`);
                    deferredPrompt = null;
                    installBtn.classList.add('d-none');
                } else {
                    alert('Install not available in this browser');
                }
            });
        } else {
            console.warn('⚠ Install button element not found');
        }
        
        // Handle successful installation
        window.addEventListener('appinstalled', () => {
            console.log('✓ PWA installed successfully');
            deferredPrompt = null;
            if (installBtn) {
                installBtn.style.display = 'none';
            }
        });
        
        // ========== Handle online/offline status ==========
        window.addEventListener('online', () => {
            console.log('✓ Connection restored');
        });
        
        window.addEventListener('offline', () => {
            console.log('✗ Connection lost');
        });
    </script>
    
    <script>
        // Parallax Effect untuk Hero Sections
        window.addEventListener('scroll', function() {
            const heroSections = document.querySelectorAll('.hero-section, .stats-hero-section');
            
            heroSections.forEach(section => {
                const scrollPosition = window.pageYOffset;
                const elementOffset = section.getBoundingClientRect().top + window.pageYOffset;
                const distance = elementOffset - scrollPosition;
                
                if (distance < window.innerHeight) {
                    const yPos = (scrollPosition - elementOffset) * 0.5;
                    section.style.backgroundPosition = `center calc(center + ${yPos}px)`;
                }
            });
        });
        
        // Smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>
