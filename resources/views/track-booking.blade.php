@extends('layouts.app')

@section('content')
<div class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card-custom mb-5">
                    <div class="card-body p-5 text-center">
                        <i class="fas fa-search fa-4x mb-4" style="color: #FFA500;"></i>
                        <h2 class="fw-bold mb-3">Scan Barcode Booking</h2>
                        <p style="color: #ffffff; margin-bottom: 2rem;">
                            Pindai atau masukkan barcode booking Anda untuk melacak status pemesanan
                        </p>

                        @if ($errors->any())
                            <div class="alert alert-danger mb-4" style="background-color: #330000; border-color: #660000;">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                {{ $errors->first() }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger mb-4" style="background-color: #330000; border-color: #660000;">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                {{ session('error') }}
                            </div>
                        @endif

                        <!-- CAMERA SCANNER SECTION -->
                        <div class="mb-4" id="cameraSection" style="display: none;">
                            <!-- Camera Stream -->
                            <div class="mb-3" style="border: 3px solid #FFA500; border-radius: 8px; overflow: hidden; background-color: #000000;">
                                <video id="videoStream" 
                                       width="100%" 
                                       height="400" 
                                       style="display: block; background-color: #000000;"
                                       playsinline
                                       autoplay></video>
                            </div>
                            
                            <!-- Detection Canvas (hidden) -->
                            <canvas id="barcode_canvas" style="display: none;"></canvas>
                            
                            <!-- Scanner Status -->
                            <div class="alert alert-info mb-3" id="scannerStatus" style="display: none; background-color: #003366; border-color: #0066cc;">
                                <div class="d-flex align-items-center">
                                    <div class="spinner-border spinner-border-sm me-2 text-info" role="status">
                                        <span class="visually-hidden">Scanning...</span>
                                    </div>
                                    <span id="scannerStatusText" style="color: #ffffff;">Mencari barcode...</span>
                                </div>
                            </div>
                            
                            <!-- Camera Controls -->
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-warning btn-lg" id="stopCameraBtn" onclick="stopCameraScanner()">
                                    <i class="fas fa-stop-circle me-2"></i>Hentikan Scanner
                                </button>
                            </div>
                        </div>

                        <!-- MANUAL INPUT SECTION -->
                        <form action="{{ route('search-booking') }}" method="POST" id="bookingForm">
                            @csrf
                            <div class="mb-4">
                                <label for="booking_code" class="form-label fw-semibold d-block text-start">
                                    Barcode Pemesanan
                                </label>
                                <input type="text"
                                       id="booking_code"
                                       name="booking_code"
                                       class="form-control form-control-lg"
                                       placeholder="Pindai atau masukkan barcode"
                                       style="background-color: #1a1a1a; color: #ffffff; border-color: #333333;"
                                       autofocus>
                            </div>

                            <div class="d-grid gap-2 mb-3">
                                <button type="submit" class="btn btn-primary-custom btn-lg">
                                    <i class="fas fa-barcode me-2"></i>Lacak Booking
                                </button>
                            </div>
                        </form>

                        <!-- Camera Toggle Button -->
                        <button type="button" class="btn btn-warning w-100" id="toggleCameraBtn" onclick="toggleCameraScanner()">
                            <i class="fas fa-camera me-2"></i>Gunakan Kamera
                        </button>

                        <div class="mt-3 text-center">
                            <a href="{{ url('/') }}" class="text-muted" style="text-decoration: none;">
                                &larr; Kembali ke Beranda
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Info Cards -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card-custom h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-camera fa-2x mb-3" style="color: #FFA500;"></i>
                                <h5 class="fw-bold mb-2">Scan dengan Kamera</h5>
                                <p style="color: #ffffff;">Gunakan kamera untuk memindai barcode secara otomatis</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card-custom h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-keyboard fa-2x mb-3" style="color: #FFA500;"></i>
                                <h5 class="fw-bold mb-2">Input Manual</h5>
                                <p style="color: #ffffff;">Atau masukkan barcode secara manual langsung</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load html5-qrcode Library (QR/Barcode Scanner) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.4/html5-qrcode.min.js"></script>

<script>
    // Global variables
    let html5QrcodeScanner = null;
    let cameraActive = false;

    /**
     * FUNGSI: toggleCameraScanner
     * Toggle kamera scanner on/off
     */
    async function toggleCameraScanner() {
        if (cameraActive) {
            stopCameraScanner();
        } else {
            await startCameraScanner();
        }
    }

    /**
     * FUNGSI: startCameraScanner
     * Start barcode scanning dari kamera
     */
    async function startCameraScanner() {
        try {
            const bookingCodeInput = document.getElementById('booking_code');
            const cameraSection = document.getElementById('cameraSection');
            const toggleBtn = document.getElementById('toggleCameraBtn');
            const scannerStatus = document.getElementById('scannerStatus');
            const videoElement = document.getElementById('videoStream');

            // Show camera section
            cameraSection.style.display = 'block';
            bookingCodeInput.disabled = true;
            toggleBtn.disabled = true;
            scannerStatus.style.display = 'block';
            toggleBtn.innerHTML = '<i class="fas fa-stop-circle me-2"></i>Hentikan Scanner';

            cameraActive = true;

            console.log('🎬 Starting html5-qrcode scanner...');

            // Initialize scanner
            html5QrcodeScanner = new Html5QrcodeScanner(
                "videoStream",
                { facingMode: "environment", qrbox: 250, fps: 10 },
                false
            );

            // Handler untuk barcode/QR terdeteksi
            html5QrcodeScanner.render(onScanSuccess, onScanError);

            showAlert('✓ Kamera aktif - arahkan barcode ke kamera', 'info');

        } catch (error) {
            console.error('❌ Camera error:', error);
            
            if (error.name === 'NotAllowedError') {
                showAlert('❌ Izin kamera ditolak. Silakan berikan akses ke kamera.', 'danger');
            } else if (error.name === 'NotFoundError') {
                showAlert('❌ Kamera tidak ditemukan', 'danger');
            } else if (error.name === 'NotSupportedError') {
                showAlert('❌ Browser tidak mendukung akses kamera', 'danger');
            } else {
                showAlert('❌ Error: ' + error.message, 'danger');
            }

            // Reset UI
            stopCameraScanner();
        }
    }

    /**
     * FUNGSI: onScanSuccess
     * Callback when barcode/QR is detected
     */
    function onScanSuccess(decodedText, decodedResult) {
        console.log('✓ Barcode detected:', decodedText);
        
        const barcodeCode = decodedText.trim();
        const bookingCodeInput = document.getElementById('booking_code');
        
        // Update input dan submit
        bookingCodeInput.value = barcodeCode;
        
        // Show success message
        const scannerStatusText = document.getElementById('scannerStatusText');
        scannerStatusText.innerHTML = '<i class="fas fa-check-circle me-2"></i>Barcode ditemukan: ' + barcodeCode;
        
        // Stop scanner
        stopCameraScanner();
        
        // Auto submit form setelah 1 detik
        setTimeout(() => {
            document.getElementById('bookingForm').submit();
        }, 1000);
    }

    /**
     * FUNGSI: onScanError
     * Callback for scan errors
     */
    function onScanError(error) {
        // Ignore "No barcode found" errors
        if (error && error.includes('No code found in image')) {
            return;
        }
        console.warn('⚠️ Scan warning:', error);
    }

    /**
     * FUNGSI: stopCameraScanner
     * Stop camera dan barcode scanning
     */
    function stopCameraScanner() {
        try {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear();
                console.log('🛑 Camera stopped');
            }

            const cameraSection = document.getElementById('cameraSection');
            const bookingCodeInput = document.getElementById('booking_code');
            const toggleBtn = document.getElementById('toggleCameraBtn');
            const scannerStatus = document.getElementById('scannerStatus');

            // Hide camera section, show input
            cameraSection.style.display = 'none';
            bookingCodeInput.disabled = false;
            toggleBtn.disabled = false;
            scannerStatus.style.display = 'none';

            // Reset button text
            toggleBtn.innerHTML = '<i class="fas fa-camera me-2"></i>Gunakan Kamera';

            cameraActive = false;

        } catch (error) {
            console.error('Error stopping camera:', error);
        }
    }

    /**
     * FUNGSI: showAlert
     * Show notification message
     */
    function showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 99999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(alertDiv);

        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    /**
     * EVENT: On page unload, stop camera
     */
    window.addEventListener('beforeunload', function() {
        if (cameraActive) {
            stopCameraScanner();
        }
    });

    console.log('✓ Track Booking Scanner Ready');
</script>

@endsection
