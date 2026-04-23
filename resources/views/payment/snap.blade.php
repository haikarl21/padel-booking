@extends('layouts.app')

@section('content')
<div class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="display-5 fw-bold text-center mb-5">Pembayaran Mudah dengan Midtrans</h2>
            </div>
        </div>

        <div class="row g-4">
            <!-- Ringkasan Booking -->
            <div class="col-lg-6">
                <div class="card-custom">
                    <div class="card-body p-4">
                        <h3 class="card-title fw-bold mb-4">
                            <i class="fas fa-clipboard-list text-warning me-2"></i>Ringkasan Booking
                        </h3>

                        <!-- Booking Code -->
                        <div class="mb-4" style="border: 1px solid #FFA500; border-radius: 8px; background-color: #1a1a1a;">
                            <div class="d-flex justify-content-between align-items-center px-3 py-2">
                                <span style="color: #ffffff;">Referensi Booking</span>
                                <span class="fw-bold text-warning">{{ $booking->booking_code }}</span>
                            </div>
                        </div>

                        <!-- Booking Details -->
                        <div class="row g-3">
                            <div class="col-12 d-flex justify-content-between">
                                <span style="color: #ffffff;">Lapangan</span>
                                <span class="fw-bold" style="color: #ffffff;">{{ $booking->court->name }}</span>
                            </div>
                            <div class="col-12 d-flex justify-content-between">
                                <span style="color: #ffffff;">Tanggal</span>
                                <span class="fw-bold" style="color: #ffffff;">{{ $booking->date->format('l, d F Y') }}</span>
                            </div>
                            <div class="col-12 d-flex justify-content-between">
                                <span style="color: #ffffff;">Waktu</span>
                                <span class="fw-bold" style="color: #ffffff;">{{ $booking->timeSlot->display_text }}</span>
                            </div>
                            <div class="col-12 d-flex justify-content-between">
                                <span style="color: #ffffff;">Durasi</span>
                                <span class="fw-bold" style="color: #ffffff;">1 Jam</span>
                            </div>
                            <hr class="my-3" style="border-color: #333333;">
                            <div class="col-12 d-flex justify-content-between">
                                <span style="color: #ffffff;">Dibayar</span>
                                <span class="fw-bold" style="color: #90EE90;">Rp {{ number_format($booking->paid, 0, ',', '.') }}</span>
                            </div>
                            <div class="col-12 d-flex justify-content-between">
                                <span style="color: #ffffff;">Sisa</span>
                                <span class="fw-bold" style="color: #FF6B6B;">Rp {{ number_format($booking->remaining, 0, ',', '.') }}</span>
                            </div>
                            <div class="col-12 d-flex justify-content-between border-top pt-3" style="border-color: #333333;">
                                <span class="fw-bold" style="color: #ffffff;">Jumlah Pembayaran</span>
                                <span class="text-warning fw-bold fs-5">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Payment Information -->
                        <div class="alert mt-4" style="background-color: #332200; border-color: #664400;">
                            <div class="d-flex">
                                <i class="fas fa-info-circle me-3 mt-1" style="color: #FFA500;"></i>
                                <div>
                                    <strong style="color: #FFA500;">Metode Pembayaran Tersedia</strong>
                                    <p class="mb-0" style="color: #ffffff; font-size: 0.95rem;">QRIS, Transfer Bank, E-wallet (GoPay, OVO, Dana), Cicilan Kartu Kredit, dan lainnya.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Button -->
            <div class="col-lg-6">
                <div class="card-custom">
                    <div class="card-body p-4">
                        <h3 class="card-title fw-bold mb-4">
                            <i class="fas fa-credit-card text-warning me-2"></i>Proses Pembayaran
                        </h3>

                        <div class="alert mb-4" style="background-color: #002200; border-color: #004400;">
                            <div class="d-flex">
                                <i class="fas fa-check-circle me-3 mt-1" style="color: #90EE90;"></i>
                                <div>
                                    <strong style="color: #90EE90;">Pembayaran Aman</strong>
                                    <p class="mb-0" style="color: #ffffff; font-size: 0.95rem;">Sistem pembayaran Anda diproses melalui Midtrans yang terpercaya dan aman.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Snap Payment Button -->
                        <div class="d-grid gap-2 mb-4">
                            <button type="button" id="pay-button" class="btn btn-primary-custom btn-lg">
                                <i class="fas fa-redo me-2"></i>Bayar Sekarang
                            </button>
                        </div>

                        <!-- Instructions -->
                        <div style="background-color: #1a1a1a; padding: 20px; border-radius: 8px;">
                            <h5 class="fw-bold mb-3" style="color: #FFA500;">Langkah-langkah Pembayaran:</h5>
                            <ol style="color: #ffffff;">
                                <li class="mb-2">Klik tombol "Bayar Sekarang" di atas</li>
                                <li class="mb-2">Pilih metode pembayaran yang Anda inginkan</li>
                                <li class="mb-2">Ikuti instruksi pembayaran sesuai metode yang dipilih</li>
                                <li>Pembayaran Anda akan dikonfirmasi secara otomatis</li>
                            </ol>
                        </div>

                        <!-- Order ID (untuk reference) -->
                        <div class="mt-4 pt-4" style="border-top: 1px solid #333333;">
                            <small style="color: #999999;">
                                <i class="fas fa-hashtag me-1"></i>Order ID: <strong>{{ $payment->order_id }}</strong>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 
    ============================================
    MIDTRANS SNAP PAYMENT INTEGRATION
    ============================================
    
    Snap adalah payment gateway dari Midtrans yang menyediakan:
    - Popup payment page
    - Multiple metode pembayaran
    - Real-time payment processing
    - Webhook/callback untuk update status
    
    Documentation: https://snap-docs.midtrans.com
-->

<!-- Load Midtrans Snap Library -->
<script
    src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
    data-client-key="{{ $clientKey }}"></script>
<script>
    // SNAP TOKEN yang digenerate di backend
    var snapToken = '{{ $snapToken }}';

    /**
     * Event handler untuk tombol pembayaran
     * Ketika user klik tombol "Bayar Sekarang", Midtrans Snap akan membuka
     */
    document.getElementById('pay-button').addEventListener('click', function () {
        // Tampilkan Snap Checkout untuk pembayaran
        // callback result_type:'error' akan dipanggil jika ada error
        // callback result_type:'success' akan dipanggil jika pembayaran berhasil
        snap.pay(snapToken, {
            // Callback ketika transaksi pending
            onPending: function(result){
                console.log('Pending payment', result);
                // Tampilkan pesan pending ke user
                showNotification('Pembayaran Anda sedang diproses. Mohon tunggu...', 'warning');
            },
            
            // Callback ketika pembayaran berhasil
            onSuccess: function(result){
                console.log('Payment success', result);
                // Redirect ke halaman receipt atau success
                // Status pembayaran akan diupdate via webhook dari Midtrans
                setTimeout(function() {
                    // Tunggu 2 detik agar webhook dari Midtrans terproses di backend
                    window.location.href = '{{ route("booking.detail", $booking) }}';
                }, 2000);
            },
            
            // Callback ketika pembayaran gagal atau user membatalkan
            onError: function(result){
                console.log('Payment error', result);
                showNotification('Pembayaran gagal. Silakan coba lagi.', 'danger');
            },
            
            // Callback ketika user menutup Snap tanpa melakukan pembayaran
            onClose: function(){
                console.log('Customer closed the popup without finishing the payment');
                showNotification('Anda menutup form pembayaran. Silakan coba lagi jika diperlukan.', 'info');
            }
        });
    });

    /**
     * Helper function untuk menampilkan notification
     * @param {string} message - Pesan yang akan ditampilkan
     * @param {string} type - Tipe alert: success, danger, warning, info
     */
    function showNotification(message, type = 'info') {
        // Buat elemen alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Tambahkan ke body
        document.body.appendChild(alertDiv);
        
        // Auto remove setelah 5 detik
        setTimeout(() => alertDiv.remove(), 5000);
    }

    /**
     * Polling untuk cek status pembayaran (optional, jika ingin real-time update)
     * Catatan: Webhook dari Midtrans lebih reliable daripada polling
     */
    // Uncomment jika ingin menambahkan polling untuk check status
    /*
    function checkPaymentStatus() {
        fetch('{{ route("payment.check-status", $payment) }}')
            .then(response => response.json())
            .then(data => {
                if (data.payment_status === 'settlement') {
                    window.location.href = '{{ route("booking.detail", $booking) }}';
                }
            })
            .catch(error => console.error('Error:', error));
    }
    
    // Check status setiap 5 detik
    setInterval(checkPaymentStatus, 5000);
    */
</script>

<!-- Styling untuk payment button -->
<style>
    .btn-primary-custom {
        background-color: #FFA500;
        color: #000000;
        border: none;
        font-weight: bold;
        transition: all 0.3s ease;
    }
    
    .btn-primary-custom:hover {
        background-color: #FF8C00;
        color: #000000;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(255, 165, 0, 0.3);
    }
    
    .card-custom {
        background-color: #0a0a0a;
        border: 1px solid #333333;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
    }
</style>
@endsection
