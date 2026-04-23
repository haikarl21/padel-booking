@extends('layouts.app')

@section('content')
<div class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="display-5 fw-bold text-center mb-5" style="color: #ffffff;">Detail Booking</h2>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card-custom">
                    <div class="card-body p-4">
                        @if(session('payment_success'))
                            <div class="alert alert-success d-flex align-items-center">
                                <i class="fas fa-check-circle me-2"></i>
                                {!! session('payment_success') !!}
                                {{-- you could link to a receipt here if you implement one --}}
                            </div>
                        @endif
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <h3 class="card-title fw-bold mb-0" style="color: #ffffff;">
                                <i class="fas fa-info-circle text-warning me-2"></i>Informasi Booking
                            </h3>
                                <span class="badge rounded-pill fs-6 {{ in_array($booking->status,['approved']) ? 'bg-success' : 'bg-warning' }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </div>

                        <!-- BARCODE SECTION - HANYA TAMPIL SETELAH PEMBAYARAN BERHASIL -->
                        @php
                            $payment = $booking->payments->sortBy('created_at')->last();
                            $isPaymentCompleted = $payment && in_array($payment->status, ['success', 'completed', 'settlement', 'capture'], true);
                            $isFullyPaid = $booking->status === 'approved' || (float) $booking->remaining <= 0;
                        @endphp

                        @if($isPaymentCompleted && $isFullyPaid)
                        <div class="mb-4 p-4" style="border: 3px solid #28a745; border-radius: 12px; background-color: #25452f; text-align: center;">
                            <!-- BARCODE TITLE -->
                            <h5 class="fw-bold mb-3" style="color: #90EE90;">
                                <i class="fas fa-barcode me-2"></i>Barcode Pemesanan
                            </h5>
                            
                            <!-- BARCODE IMAGE -->
                            <div class="mb-3 w-100" style="background: white; padding: 15px; border-radius: 8px; display: inline-block; max-width: 100%; overflow: hidden;">
                                <svg id="barcodeDisplay" style="max-width: 100%; height: auto; width: 100%;"></svg>
                            </div>
                            
                            <!-- BOOKING CODE TEXT -->
                            <div class="mb-3">
                                <p style="color: #ffffff; font-size: 0.9rem; margin-bottom: 8px;">Kode:</p>
                                <p class="fw-bold text-success font-monospace" id="bookingCode" style="font-size: 1.3em; letter-spacing: 2px; margin: 0;">{{ $booking->booking_code }}</p>
                            </div>
                            
                            <!-- ACTION BUTTONS -->
                            <div class="d-flex gap-2 justify-content-center">
                                <button class="btn btn-sm btn-success" onclick="copyBookingCode()" title="Copy barcode code">
                                    <i class="fas fa-copy me-1"></i>Copy
                                </button>
                                <button class="btn btn-sm btn-info" onclick="downloadBarcode()" title="Download barcode sebagai PNG">
                                    <i class="fas fa-download me-1"></i>Download
                                </button>
                            </div>
                        </div>
                        @endif

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <div class="card border-0" style="background-color: #1a1a1a;">
                                    <div class="card-body">
                                        <h5 class="card-title fw-bold mb-3" style="color: #ffffff;">
                                            <i class="fas fa-user text-warning me-2"></i>Detail Pelanggan
                                        </h5>
                                        <div class="row g-2">
                                            <div class="col-12 d-flex justify-content-between">
                                                <span style="color: #ffffff;">Nama</span>
                                                <span class="fw-bold" style="color: #ffffff;">{{ $booking->customer_name }}</span>
                                            </div>
                                            <div class="col-12 d-flex justify-content-between">
                                                <span style="color: #ffffff;">Telepon</span>
                                                <span class="fw-bold" style="color: #ffffff;">{{ $booking->phone }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card border-0" style="background-color: #1a1a1a;">
                                    <div class="card-body">
                                        <h5 class="card-title fw-bold mb-3" style="color: #ffffff;">
                                            <i class="fas fa-calendar text-warning me-2"></i>Detail Booking
                                        </h5>
                                        <div class="row g-2">
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
                                                <span class="fw-bold" style="color: #ffffff;">
                                                    @if($booking->time_slot_ids && count($booking->time_slot_ids) > 0)
                                                        @php
                                                            $slots = $booking->bookedTimeSlots();
                                                            $slotTexts = $slots->pluck('display_text')->join(', ');
                                                        @endphp
                                                        {{ $slotTexts ?: 'N/A' }}
                                                    @elseif($booking->start_time)
                                                        {{ $booking->start_time }} - {{ \Carbon\Carbon::parse($booking->start_time)->addHours($booking->duration_hours)->format('H:i') }}
                                                    @else
                                                        {{ $booking->timeSlot->display_text ?? 'N/A' }}
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="col-12 d-flex justify-content-between">
                                                <span style="color: #ffffff;">Durasi</span>
                                                <span class="fw-bold" style="color: #ffffff;">{{ $booking->duration_hours }} Jam</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 bg-warning bg-opacity-10 mb-4">
                            <div class="card-body">
                                <h5 class="card-title fw-bold mb-3" style="color: #ffffff;">
                                    <i class="fas fa-money-bill-wave text-warning me-2"></i>Ringkasan Pembayaran
                                </h5>
                                <div class="row g-2">
                                    <div class="col-12 d-flex justify-content-between">
                                        <span style="color: #ffffff;">Jumlah Total</span>
                                        <span class="fw-bold text-warning">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                                    </div>
                                    @if($payment)
                                    <div class="col-12 d-flex justify-content-between">
                                        <span style="color: #ffffff;">Jumlah Dibayar</span>
                                        <span class="fw-bold text-success">Rp {{ number_format($booking->paid, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="col-12 d-flex justify-content-between border-top pt-2">
                                        <span style="color: #ffffff; font-weight: bold;">Sisa</span>
                                        <span class="fw-bold text-danger">Rp {{ number_format($booking->remaining, 0, ',', '.') }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Bayar Sekarang -->
                        @if($booking->status !== 'approved')
                        <div class="d-grid gap-2 mb-4">
                            <button type="button" class="btn btn-warning btn-lg fw-bold" data-bs-toggle="modal" data-bs-target="#paymentTypeModal">
                                <i class="fas fa-credit-card me-2"></i>Bayar Sekarang
                            </button>
                        </div>

                        <!-- Alert Info Pembayaran -->
                        <div class="alert" style="background-color: #002200; border-color: #004400; margin-bottom: 0;">
                            <div class="d-flex">
                                <i class="fas fa-info-circle me-3 mt-1" style="color: #90EE90;"></i>
                                <div>
                                    <strong style="color: #90EE90;">Metode Pembayaran Tersedia</strong>
                                    <p class="mb-0" style="color: #ffffff; font-size: 0.95rem;">
                                        QRIS, Transfer Bank, E-wallet (GoPay, OVO, Dana), Cicilan Kartu Kredit, dan lainnya.
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($payment)
                        <div class="mt-4 pt-4 border-top">
                            <h4 class="fw-bold mb-4" style="color: #ffffff;">
                                <i class="fas fa-history text-info me-2"></i>Riwayat Pembayaran
                            </h4>
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold">
                                            Metode Pembayaran: {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                        </span>
                                        <span class="badge {{ in_array($payment->status, ['success','completed','settlement','capture'], true) ? 'bg-success' : 'bg-warning' }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted">
                                        <span>Jumlah: Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                                        <span>{{ $payment->created_at->format('d M Y H:i') }}</span>
                                    </div>

                                    <div class="mt-3">
                                        <button class="btn btn-sm btn-info" id="refreshBtnPayment" onclick="refreshPaymentStatus('{{ $payment->order_id }}')">
                                            <i class="fas fa-sync-alt"></i> Refresh Status
                                        </button>
                                        @if($payment->status === 'pending')
                                        <small class="text-muted ms-2" style="display: block; margin-top: 5px;">💡 Klik refresh jika sudah melakukan pembayaran</small>
                                        @endif
                                    </div>

                                    @if($payment->proof_file_path)
                                        <div class="mt-3">
                                            <strong>Bukti Pembayaran:</strong><br>
                                            @if(in_array(pathinfo($payment->proof_file_path, PATHINFO_EXTENSION), ['jpg','jpeg','png']))
                                                <img src="{{ asset('storage/'.$payment->proof_file_path) }}" alt="Proof" style="max-width:200px;">
                                            @else
                                                <a href="{{ asset('storage/'.$payment->proof_file_path) }}" target="_blank">Unduh File</a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================
     MODAL: PILIH TIPE PEMBAYARAN
     ============================================ -->
<div class="modal fade" id="paymentTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-warning">
            <div class="modal-header border-warning">
                <h5 class="modal-title fw-bold" style="color: #FFA500;">
                    <i class="fas fa-credit-card me-2"></i>Pilih Tipe Pembayaran
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <!-- Payment Type Options -->
                <div class="row g-3">
                    <!-- Full Payment Option -->
                    <div class="col-12">
                        <button type="button" class="payment-option p-3 rounded border-2 w-100 text-start" style="border-color: #FFA500; cursor: pointer; background-color: #1a1a1a; color: inherit;" data-payment-type="full">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold mb-1" style="color: #FFA500;">
                                        <i class="fas fa-check-circle"></i> Pembayaran Penuh
                                    </h6>
                                    <small style="color: #999999;">Bayar seluruh jumlah sekarang</small>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-warning fs-5">Rp {{ number_format($booking->remaining > 0 ? $booking->remaining : $booking->total_price, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </button>
                    </div>

                    <!-- Partial Payment Option -->
                    <div class="col-12">
                        <button type="button" class="payment-option p-3 rounded border-2 w-100 text-start" style="border-color: #666666; cursor: pointer; background-color: #1a1a1a; color: inherit;" data-payment-type="partial" @if((float)$booking->paid > 0) disabled @endif>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold mb-1" style="color: #ffffff;">
                                        <i class="fas fa-hand-holding-usd"></i> Pembayaran 50%
                                    </h6>
                                    <small style="color: #999999;">Bayar separuh sekarang, separuh nanti</small>
                                    @if((float)$booking->paid > 0)
                                        <div class="mt-1"><small class="text-warning">Sudah ada pembayaran. Pilih pembayaran penuh untuk melunasi sisa.</small></div>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-warning fs-5">Rp {{ number_format($booking->total_price * 0.5, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="paymentLoading" class="d-none mt-3">
                    <div class="spinner-border text-warning me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span style="color: #ffffff;">Mempersiapkan pembayaran...</span>
                </div>
            </div>

            <div class="modal-footer border-warning">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>


<!-- Load JsBarcode Library for Barcode Generation -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<!-- Load Midtrans Snap JS Library -->
<script
    src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
    data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
    // Snap will read client key from script tag attribute `data-client-key`

    // Move the modal to body to prevent z-index and stacking context issues
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('paymentTypeModal');
        if (modal) {
            document.body.appendChild(modal);
        }
    });

    /**
     * VARIABEL GLOBAL
     * Menyimpan booking ID dan data pembayaran
     */
    const BOOKING_ID = {{ $booking->id }};
    const BOOKING_CODE = '{{ $booking->booking_code }}';
    const SNAP_TOKEN_ROUTE = '{{ route("payment.create-transaction") }}';
    const BOOKING_DETAIL_ROUTE = '{{ route("booking.detail", $booking) }}';

    /**
     * FUNGSI: selectPaymentType
     * Dipanggil saat user memilih tipe pembayaran (full/partial)
     * Langsung fetch Snap token tanpa modal metode pembayaran
     * 
     * @param {string} paymentType - 'full' atau 'partial'
     */
    let selectedPaymentType = null;

    function selectPaymentType(paymentType) {
        // Simpan payment type yang dipilih
        selectedPaymentType = paymentType;
        
        console.log('✓ Selected Payment Type:', paymentType);

        // Hide typeModal
        const typeModal = bootstrap.Modal.getInstance(document.getElementById('paymentTypeModal'));
        if (typeModal) {
            typeModal.hide();
        }

        // Langsung fetch Snap token
        fetchSnapToken(paymentType);
    }

    /**
     * FUNGSI: fetchSnapToken
     * Kirim request ke backend untuk generate snap token
     * 
     * @param {string} paymentType - 'full' atau 'partial'
     * @param {string} paymentMethod - Metode pembayaran (optional)
     */
    function fetchSnapToken(paymentType, paymentMethod = null) {
        console.log('=== FETCH SNAP TOKEN START ===');
        console.log('Payment Type:', paymentType);
        console.log('Payment Method:', paymentMethod);
        console.log('Route:', SNAP_TOKEN_ROUTE);
        console.log('Booking ID:', BOOKING_ID);
        
        // UI: show loading
        document.querySelectorAll('.payment-option').forEach(el => el.style.display = 'none');
        document.getElementById('paymentLoading').classList.remove('d-none');

        // Siapkan data untuk request
        const formData = new FormData();
        formData.append('booking_id', BOOKING_ID);
        formData.append('payment_type', paymentType);
        if (paymentMethod) {
            formData.append('payment_method', paymentMethod);
        }
        formData.append('_token', '{{ csrf_token() }}');

        // Kirim request ke backend
        fetch(SNAP_TOKEN_ROUTE, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData,
        })
        .then(response => {
            console.log('Response Status:', response.status);
            console.log('Response OK:', response.ok);
            
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP ${response.status}: ${text}`);
                });
            }
            
            return response.json();
        })
        .then(data => {
            console.log('✓ Response Data:', data);
            
            // Handle response
            if (data.success && data.snap_token) {
                console.log('✓ Token generated successfully');
                
                // Close typeModal
                const typeModal = bootstrap.Modal.getInstance(document.getElementById('paymentTypeModal'));
                if (typeModal) typeModal.hide();

                // Show Snap payment
                setTimeout(() => {
                    showSnapPayment(data.snap_token);
                }, 300);
            } else {
                const errorMsg = data.message || 'Unknown error occurred';
                console.error('✗ Error from server:', errorMsg);
                showAlert('❌ Error: ' + errorMsg, 'danger');
                resetPaymentModal();
            }
        })
        .catch(error => {
            console.error('✗ Fetch Error:', error);
            console.error('Error Stack:', error.stack);
            showAlert('❌ Error: ' + error.message, 'danger');
            resetPaymentModal();
        });
    }

    /**
     * FUNGSI: showSnapPayment
     * Tampilkan Midtrans Snap popup dengan snap token
     * 
     * @param {string} snapToken - Token dari Midtrans backend
     */
    function showSnapPayment(snapToken) {
        // Log sebelum call snap.pay
        console.log('=== SNAP.PAY CALL ===');
        console.log('Snap Token:', snapToken);
        console.log('Snap object available:', typeof snap !== 'undefined');
        console.log('snap.pay available:', typeof snap?.pay === 'function');
        console.log('Client Key:', window.Midtrans?.clientKey);
        
        // Cek apakah snap library sudah load
        if (typeof snap === 'undefined' || typeof snap.pay !== 'function') {
            console.error('ERROR: Snap library belum ter-load dengan benar');
            showAlert('❌ Error: Snap library tidak ter-load. Silakan refresh halaman.', 'danger');
            return;
        }
        
        if (!snapToken) {
            console.error('ERROR: Snap token tidak valid');
            showAlert('❌ Error: Token pembayaran tidak valid', 'danger');
            return;
        }

        // Panggil snap.pay() dengan snap token dan callback handlers
        snap.pay(snapToken, {
            // ★ CALLBACK: Payment Pending
            // Dipanggil saat pembayaran dalam proses (menunggu konfirmasi bank, dll)
            onPending: function(result) {
                console.log('Pembayaran Pending:', result);
                showAlert(
                    'Pembayaran Anda sedang diproses. Mohon tunggu beberapa saat...',
                    'warning'
                );
                
                // Cek status langsung dari Midtrans API (tidak mengandalkan webhook)
                if (result.order_id) {
                    setTimeout(() => {
                        checkPaymentStatus(result.order_id);
                    }, 2000);
                }
            },

            // ★ CALLBACK: Payment Success
            // Dipanggil saat pembayaran berhasil diproses
            onSuccess: function(result) {
                console.log('Pembayaran Berhasil:', result);
                showAlert(
                    'Pembayaran Anda berhasil! Data booking sedang diperbarui...',
                    'success'
                );
                
                // Langsung cek status dari Midtrans API (tidak mengandalkan webhook)
                // Ini memastikan data tersinkronisasi dengan benar di localhost
                if (result.order_id) {
                    checkPaymentStatus(result.order_id);
                } else {
                    // Fallback jika order_id tidak ada
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                }
            },

            // ★ CALLBACK: Payment Error
            // Dipanggil saat pembayaran gagal
            onError: function(result) {
                console.log('Pembayaran Error:', result);
                showAlert(
                    'Pembayaran gagal. Pesan: ' + (result.status_message || 'Silakan coba lagi'),
                    'danger'
                );
                resetPaymentModal();
            },

            // ★ CALLBACK: Close Popup
            // Dipanggil saat user menutup popup tanpa melakukan pembayaran
            onClose: function() {
                console.log('User menutup Snap popup');
                showAlert(
                    'Anda menutup form pembayaran. Silakan klik "Bayar Sekarang" lagi jika ingin melanjutkan.',
                    'info'
                );
                resetPaymentModal();
            }
        });
    }

    /**
     * FUNGSI: checkPaymentStatus
     * Cek status pembayaran dari Midtrans API
     * Bisa dipanggil dari button click atau dari snap.pay callback
     */
    function checkPaymentStatus(orderId, fromButton = false) {
        let btn = null;
        let originalHTML = '';

        // Jika dipanggil dari button click
        if (fromButton && event && event.target) {
            btn = event.target.closest('button');
            if (btn) {
                originalHTML = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Checking...';
            }
        }

        fetch('/check-status/' + orderId)
            .then(response => response.json())
            .then(data => {
                console.log('Check Status Response:', data);
                
                if (data.success) {
                    // Show success message
                    showAlert('✓ Status: ' + data.message, 'success');
                    
                    // Auto reload untuk menampilkan data terupdate
                    if (['settlement', 'capture', 'success', 'completed'].includes(data.status)) {
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        // Update status badges jika ada
                        const badges = document.querySelectorAll('.badge');
                        badges.forEach(badge => {
                            if (data.status === 'failed') {
                                badge.className = 'badge bg-danger';
                                badge.textContent = 'Failed';
                            } else {
                                badge.className = 'badge bg-warning text-dark';
                                badge.textContent = 'Pending';
                            }
                        });
                    }
                } else {
                    showAlert('✕ ' + (data.message || 'Gagal cek status'), 'danger');
                }
                
                // Restore button jika ada
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                }
            })
            .catch(error => {
                console.error('Error checking status:', error);
                showAlert('Terjadi kesalahan saat mengecek status', 'danger');
                
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                }
            });
    }

    /**
     * FUNGSI: pollPaymentStatus (deprecated)
     */
    function pollPaymentStatus() {
        console.log('Polling status pembayaran...');
    }

    /**
     * FUNGSI: refreshPaymentStatus
     * Refresh payment status dengan button click
     * Auto-reload jika status berubah menjadi success
     */
    function refreshPaymentStatus(orderId) {
        const btn = document.getElementById('refreshBtnPayment');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Refreshing...';
        }

        fetch('/check-status/' + orderId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('✓ Status diperbarui: ' + data.message, 'success');
                    
                    // Auto reload untuk menampilkan barcode jika payment berhasil
                    if (['settlement', 'capture', 'success', 'completed'].includes(data.status)) {
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        // Update badge status jika ada
                        const badges = document.querySelectorAll('.badge');
                        badges.forEach(badge => {
                            if (data.status === 'failed') {
                                badge.className = 'badge bg-danger';
                                badge.textContent = 'Failed';
                            } else if (data.status === 'success' || data.status === 'completed') {
                                badge.className = 'badge bg-success';
                                badge.textContent = 'Success';
                            }
                        });
                        
                        // Restore button
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh Status';
                        }
                    }
                } else {
                    showAlert('Status masih terlihat dari Midtrans', 'info');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh Status';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Gagal refresh status. Coba lagi.', 'danger');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh Status';
                }
            });
    }

    /**
     * FUNGSI: resetPaymentModal
     * Reset modal ke state awal (tampilkan opsi pembayaran kembali)
     */
    function resetPaymentModal() {
        // Tampilkan kembali opsi pembayaran tipe
        document.querySelectorAll('.payment-option').forEach(el => el.style.display = 'block');
        // Sembunyikan loading
        document.getElementById('paymentLoading').classList.add('d-none');
        
        // Reset selected payment type
        selectedPaymentType = null;
    }

    /**
     * FUNGSI: showAlert
     * Helper untuk menampilkan notifikasi (toast/alert)
     * 
     * @param {string} message - Pesan yang akan ditampilkan
     * @param {string} type - Tipe alert: 'success', 'danger', 'warning', 'info'
     */
    function showAlert(message, type = 'info') {
        // Buat elemen alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 99999; min-width: 300px;';
        
        // Tentukan icon berdasarkan tipe alert
        const icons = {
            'success': 'fa-check-circle',
            'danger': 'fa-exclamation-circle',
            'warning': 'fa-exclamation-triangle',
            'info': 'fa-info-circle'
        };
        const icon = icons[type] || 'fa-info-circle';

        // Set HTML content
        alertDiv.innerHTML = `
            <i class="fas ${icon} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // Tambahkan ke body
        document.body.appendChild(alertDiv);

        // Auto remove setelah 5 detik
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    /**
     * EVENT: Payment Type Options Click Handler
     * Memastikan klik pada card memilih tipe pembayaran
     */
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize barcode if payment is completed
        const barcodeElement = document.getElementById('barcodeDisplay');
        if (barcodeElement) {
            initializeBarcode();
        }
        
        // Payment option click + hover effects
        document.querySelectorAll('.payment-option').forEach(option => {
            option.addEventListener('click', function() {
                const type = this.getAttribute('data-payment-type');
                if (this.disabled) return;
                if (!type) return;
                selectPaymentType(type);
            });
            option.addEventListener('mouseenter', function() {
                if (!this.disabled) this.style.opacity = '0.8';
            });
            option.addEventListener('mouseleave', function() {
                this.style.opacity = '1';
            });
        });
    });

    /**
     * COPY BOOKING CODE TO CLIPBOARD
     * Copies the booking code to user's clipboard and shows confirmation
     */
    function copyBookingCode() {
        const bookingCodeElement = document.getElementById('bookingCode');
        if (!bookingCodeElement) {
            showAlert('Kode booking tidak ditemukan', 'danger');
            return;
        }

        const bookingCode = bookingCodeElement.textContent.trim();
        
        // Modern clipboard API
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(bookingCode)
                .then(() => {
                    showAlert('Kode booking berhasil disalin: ' + bookingCode, 'success');
                })
                .catch(err => {
                    console.error('Gagal menyalin:', err);
                    showAlert('Gagal menyalin kode booking', 'danger');
                });
        } else {
            // Fallback untuk browser lama
            const textarea = document.createElement('textarea');
            textarea.value = bookingCode;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            
            try {
                document.execCommand('copy');
                showAlert('Kode booking berhasil disalin: ' + bookingCode, 'success');
            } catch (err) {
                console.error('Gagal menyalin:', err);
                showAlert('Gagal menyalin kode booking', 'danger');
            } finally {
                document.body.removeChild(textarea);
            }
        }
    }

    /**
     * FUNGSI: initializeBarcode
     * Generate barcode SVG dari booking code
     * Dipanggil saat page load
     */
    function initializeBarcode() {
        const bookingCode = '{{ $booking->booking_code }}';
        const barcodeElement = document.getElementById('barcodeDisplay');
        
        if (!barcodeElement || !bookingCode) {
            console.warn('Barcode element atau booking code tidak ditemukan');
            return;
        }
        
        try {
            // Generate barcode using JsBarcode library
            JsBarcode('#barcodeDisplay', bookingCode, {
                format: "CODE128",
                width: 2,
                height: 80,
                displayValue: false,
                margin: 5,
                lineColor: "#000000",
                background: "#ffffff"
            });
            console.log('✓ Barcode generated successfully:', bookingCode);
        } catch (error) {
            console.error('Error generating barcode:', error);
            showAlert('Gagal membuat barcode', 'warning');
        }
    }

    /**
     * FUNGSI: downloadBarcode
     * Download barcode sebagai PNG image
     */
    function downloadBarcode() {
        const bookingCode = '{{ $booking->booking_code }}';
        const barcodeElement = document.getElementById('barcodeDisplay');
        
        if (!barcodeElement) {
            showAlert('Barcode tidak ditemukan', 'danger');
            return;
        }
        
        try {
            // Create canvas from SVG
            const svg = barcodeElement;
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            // Set canvas size
            const svgRect = svg.getBoundingClientRect();
            canvas.width = svgRect.width * 2; // 2x untuk quality lebih baik
            canvas.height = svgRect.height * 2;
            
            // Convert SVG to image
            const svgString = new XMLSerializer().serializeToString(svg);
            const img = new Image();
            img.onload = function() {
                ctx.fillStyle = 'white';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                
                // Download canvas as PNG
                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/png');
                link.download = `Barcode_${bookingCode}_${new Date().getTime()}.png`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                showAlert('✓ Barcode berhasil diunduh', 'success');
            };
            img.onerror = function() {
                // Fallback: use canvas2image or just show warning
                console.error('Error converting SVG to image');
                showAlert('Gagal mengunduh barcode. Coba gunakan screenshot.', 'warning');
            };
            img.src = 'data:image/svg+xml;base64,' + btoa(svgString);
            
        } catch (error) {
            console.error('Error downloading barcode:', error);
            showAlert('Gagal mengunduh barcode', 'danger');
        }
    }

    /**
     * DEBUGGING: Log payment information
     */
    console.log('Snap Payment Integration Ready');
    console.log('- Booking ID: ' + BOOKING_ID);
    console.log('- Client Key: Present');
    console.log('- Snap Library: ' + (typeof snap !== 'undefined' ? 'Loaded' : 'Not Loaded'));
</script>

@endsection
