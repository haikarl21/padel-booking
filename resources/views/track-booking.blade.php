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
                            <!-- Reader (html5-qrcode renders video here) -->
                            <div class="mb-3" style="border: 3px solid #FFA500; border-radius: 8px; overflow: hidden; background-color: #000000;">
                                <div id="qrReader" style="width: 100%;"></div>
                            </div>

                            <!-- Scanner Status -->
                            <div class="alert alert-info mb-3" id="scannerStatus" style="display: none; background-color: #003366; border-color: #0066cc;">
                                <div class="d-flex align-items-center">
                                    <div class="spinner-border spinner-border-sm me-2 text-info" role="status">
                                        <span class="visually-hidden">Scanning...</span>
                                    </div>
                                    <span id="scannerStatusText" style="color: #ffffff;">Mencari QR...</span>
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

                        <div class="d-grid gap-2">
                            <!-- Camera Toggle Button -->
                            <button type="button" class="btn btn-warning w-100" id="toggleCameraBtn" onclick="toggleCameraScanner()">
                                <i class="fas fa-camera me-2"></i>Gunakan Kamera
                            </button>

                            <!-- Image Upload Scan Button -->
                            <button type="button" class="btn btn-outline-warning w-100" id="scanImageBtn" onclick="triggerImagePick()">
                                <i class="fas fa-plus me-2"></i>Pilih Gambar QR
                            </button>

                            <input type="file" id="qrImageInput" accept="image/*" class="d-none" />
                        </div>

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
    let html5QrCode = null;
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
            const scannerStatusText = document.getElementById('scannerStatusText');

            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode('qrReader');
            }

            // Show camera section
            cameraSection.style.display = 'block';
            bookingCodeInput.disabled = true;
            scannerStatus.style.display = 'block';
            toggleBtn.innerHTML = '<i class="fas fa-stop-circle me-2"></i>Hentikan Scanner';

            scannerStatusText.textContent = 'Meminta akses kamera...';

            cameraActive = true;

            console.log('🎬 Starting html5-qrcode...');

            scannerStatusText.textContent = 'Mencari QR...';
            
            // Gunakan facingMode environment agar lebih stabil di semua device 
            // daripada manual mencari camera ID yang sering menyebabkan black screen
            const config = { 
                fps: 10, 
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };
            
            try {
                await html5QrCode.start(
                    { facingMode: "environment" },
                    config,
                    (decodedText) => onScanSuccess(decodedText),
                    (errorMessage) => { /* ignore */ }
                );
            } catch (cameraErr) {
                console.warn('⚠️ Environment camera not found, trying user camera...', cameraErr);
                // Fallback untuk device tanpa kamera belakang (PC/Laptop)
                await html5QrCode.start(
                    { facingMode: "user" },
                    config,
                    (decodedText) => onScanSuccess(decodedText),
                    (errorMessage) => { /* ignore */ }
                );
            }

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
            await stopCameraScanner();
        }
    }

    /**
     * FUNGSI: onScanSuccess
     * Callback when barcode/QR is detected
     */
    function onScanSuccess(decodedText) {
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
        // keep silent to avoid noisy UI
        // console.warn('⚠️ Scan warning:', error);
    }

    /**
     * FUNGSI: stopCameraScanner
     * Stop camera dan barcode scanning
     */
    async function stopCameraScanner() {
        try {
            if (html5QrCode && cameraActive) {
                await html5QrCode.stop();
                await html5QrCode.clear();
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
     * Image scan flow
     */
    function triggerImagePick() {
        const input = document.getElementById('qrImageInput');
        input.value = '';
        input.click();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('qrImageInput');
        input.addEventListener('change', async function () {
            const file = input.files && input.files[0];
            if (!file) return;

            try {
                // Stop camera if active
                if (cameraActive) {
                    await stopCameraScanner();
                }

                if (!html5QrCode) {
                    html5QrCode = new Html5Qrcode('qrReader');
                }

                // Show section to reuse same status UI
                const cameraSection = document.getElementById('cameraSection');
                const scannerStatus = document.getElementById('scannerStatus');
                const scannerStatusText = document.getElementById('scannerStatusText');
                cameraSection.style.display = 'block';
                scannerStatus.style.display = 'block';
                scannerStatusText.textContent = 'Memindai gambar...';

                const decodedText = await html5QrCode.scanFile(file, true);
                onScanSuccess(decodedText);

            } catch (err) {
                console.error('❌ scanFile error:', err);
                showAlert('❌ Gagal membaca QR dari gambar. Coba gambar yang lebih jelas.', 'danger');
                document.getElementById('scannerStatus').style.display = 'none';
                document.getElementById('cameraSection').style.display = 'none';
            }
        });
    });

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
