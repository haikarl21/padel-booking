@extends('layouts.app')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="text-muted mb-1">Kode Booking</h5>
                            <h4 class="mb-3">{{ $booking->booking_code }}</h4>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">Lapangan</small>
                                    <p class="mb-0"><strong>{{ $booking->court->name }}</strong></p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Tanggal & Jam</small>
                                    <p class="mb-0"><strong>{{ $booking->timeSlot->date_formatted }} {{ $booking->timeSlot->time_start }}-{{ $booking->timeSlot->time_end }}</strong></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-end">
                                <small class="text-muted">Total Pembayaran</small>
                                <h4 class="text-primary mb-2">Rp {{ number_format($payment->amount, 0, ',', '.') }}</h4>
                                <span class="badge bg-info">{{ $payment->getMethodDisplayName() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Status: PAID ✅ -->
            @if($payment->status === 'paid')
                <div class="card border-0 shadow-sm border-start border-5 border-success mb-4">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem; margin-bottom: 1rem;"></i>
                        <h3 class="text-success mb-2">Pembayaran Berhasil!</h3>
                        <p class="text-muted mb-3">Booking Anda telah dikonfirmasi dan siap digunakan</p>
                        <small class="text-muted">Konfirmasi: {{ $payment->paid_at?->format('d M Y H:i') }}</small>
                    </div>
                </div>

                <div class="text-center">
                    <a href="{{ route('booking.receipt', $booking) }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-file-invoice"></i> Lihat Receipt
                    </a>
                </div>

            <!-- Status: REJECTED ❌ -->
            @elseif($payment->status === 'rejected')
                <div class="card border-0 shadow-sm border-start border-5 border-danger mb-4">
                    <div class="card-body">
                        <h5 class="text-danger mb-3">
                            <i class="fas fa-times-circle"></i> Pembayaran Ditolak
                        </h5>
                        <div class="alert alert-light border-danger border">
                            <p class="mb-0"><strong>Alasan:</strong> {{ $payment->rejection_reason }}</p>
                        </div>
                        <p class="text-muted">Silakan hubungi admin untuk membuat pembayaran baru.</p>
                    </div>
                </div>

            <!-- Status: EXPIRED ⏱️ -->
            @elseif($payment->status === 'expired')
                <div class="card border-0 shadow-sm border-start border-5 border-secondary mb-4">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-hourglass-end text-secondary" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h4 class="text-secondary mb-2">Waktu Pembayaran Habis</h4>
                        <p class="text-muted">Jangka waktu 30 menit telah terlewat.</p>
                        <p class="mb-0 text-muted">Silakan hubungi admin untuk membuat pembayaran baru.</p>
                    </div>
                </div>

            <!-- Status: PENDING (BANK TRANSFER) 💳 -->
            @elseif($payment->payment_method === 'bank_transfer' && in_array($payment->status, ['pending', 'paid_pending_verification']))
                <!-- Bank Details Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-piggy-bank"></i> Informasi Rekening Tujuan</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <small class="text-muted">Nama Bank</small>
                                <p class="h6 mb-3">{{ $displayData['bank']['bank_name'] }}</p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Atas Nama Rekening</small>
                                <p class="h6 mb-3">{{ $displayData['bank']['account_holder'] }}</p>
                            </div>
                        </div>
                        <div>
                            <small class="text-muted">Nomor Rekening</small>
                            <p class="font-monospace h5 mb-0">
                                <strong>{{ $displayData['bank']['account_number'] }}</strong>
                                <button type="button" class="btn btn-sm btn-outline-secondary ms-2" 
                                        onclick="navigator.clipboard.writeText('{{ $displayData['bank']['account_number'] }}')">
                                    <i class="fas fa-copy"></i> Salin
                                </button>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Unique Amount Card -->
                <div class="card border-0 shadow-sm bg-gradient-primary text-white mb-4">
                    <div class="card-body text-center">
                        <p class="mb-2 text-white-50">🎯 NOMINAL PEMBAYARAN UNIK</p>
                        <h2 class="mb-3"><strong>Rp {{ $displayData['total_unique'] }}</strong></h2>
                        <p class="text-white-50 small mb-0">
                            <small>
                                Total: Rp{{ number_format($payment->amount, 0, ',', '.') }} + 
                                Kode Unik: {{ str_pad($displayData['unique_code'], 3, '0', STR_PAD_LEFT) }}
                            </small>
                        </p>
                    </div>
                </div>

                <!-- Countdown Timer -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body text-center">
                        <p class="text-muted mb-2">⏱️ SISA WAKTU PEMBAYARAN</p>
                        <div class="display-4 mb-2" id="countdown">
                            <strong style="color: #dc3545;">00:00</strong>
                        </div>
                        <p class="text-muted small mb-0">Pembayaran harus selesai dalam 30 menit</p>
                    </div>
                </div>

                <!-- Instructions Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-list-check"></i> Langkah-Langkah Transfer</h6>
                    </div>
                    <div class="card-body">
                        <ol class="ps-0 mb-0 list-no-style">
                            <li class="mb-3">
                                <span class="badge bg-primary me-2">1</span>
                                <strong>Buka aplikasi bank Anda</strong>
                                <p class="text-muted small ms-4 mb-0">Gunakan mobile banking, ATM, atau aplikasi dompet digital</p>
                            </li>
                            <li class="mb-3">
                                <span class="badge bg-primary me-2">2</span>
                                <strong>Transfer ke nomor rekening</strong>
                                <p class="text-muted small ms-4 mb-0">{{ $displayData['bank']['account_number'] }} ({{ $displayData['bank']['account_holder'] }})</p>
                            </li>
                            <li class="mb-3">
                                <span class="badge bg-primary me-2">3</span>
                                <strong class="text-danger">Nominal HARUS: Rp {{ $displayData['total_unique'] }}</strong>
                                <p class="text-muted small ms-4 mb-0">Pastikan termasuk kode unik di akhir nominal</p>
                            </li>
                            <li class="mb-3">
                                <span class="badge bg-primary me-2">4</span>
                                <strong>Tunggu perubahan status di aplikasi</strong>
                                <p class="text-muted small ms-4 mb-0">Biasanya langsung masuk jika se-bank</p>
                            </li>
                            <li>
                                <span class="badge bg-primary me-2">5</span>
                                <strong>Klik tombol "Konfirmasi Transfer" di bawah</strong>
                                <p class="text-muted small ms-4 mb-0">Admin akan verifikasi dalam 5-10 menit</p>
                            </li>
                        </ol>
                    </div>
                </div>

                <!-- Confirmation Action -->
                @if($payment->status === 'pending')
                    <form action="{{ route('payment.confirm-transfer', $payment) }}" method="POST" id="confirmForm">
                        @csrf
                        <button type="button" class="btn btn-success btn-lg w-100 mb-2" 
                                onclick="confirmTransfer('bank transfer', 'Rp {{ $displayData['total_unique'] }}', '{{ $displayData['bank']['account_number'] }}')">
                            <i class="fas fa-check-circle"></i> Konfirmasi Transfer Sudah Dikirim
                        </button>
                    </form>
                    <small class="text-muted d-block text-center">
                        Admin akan verifikasi pembayaran Anda dalam beberapa menit pada jam kerja
                    </small>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Transfer Dikonfirmasi!</strong> Menunggu verifikasi final dari admin...
                    </div>
                @endif

            <!-- Status: PENDING (QRIS) ⚡ -->
            @elseif($payment->payment_method === 'qrcode_dynamic' && in_array($payment->status, ['pending', 'paid_pending_verification']))
                <!-- QRIS Instructions Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="fas fa-qrcode"></i> Pembayaran QRIS</h6>
                    </div>
                    <div class="card-body text-center">
                        <p class="text-muted mb-3">Pembayaran instan menggunakan QRIS</p>
                        
                        <!-- QRIS Code Display -->
                        <div class="bg-light p-4 rounded mb-3">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=00020126360014ID.CO.TELKOM01051234501520210131ABC31350014ID3020368601051234501520210131ABC3635040512500513100113688001140114ABC000370671005802ID5912PT%20PADEL%20IND6013JAKARTA%20PUSAT" 
                                 alt="QRIS Code" 
                                 class="img-fluid mb-3"
                                 style="max-width: 250px;">
                            <p class="text-muted small mb-0">Scan QR dengan aplikasi bank Anda</p>
                        </div>

                        <!-- Payment Amount -->
                        <div class="alert alert-primary mb-3">
                            <p class="mb-1 text-muted">Nominal Pembayaran</p>
                            <h4 class="mb-0">Rp {{ number_format($payment->amount, 0, ',', '.') }}</h4>
                        </div>

                        <!-- Timer -->
                        <div class="mb-3">
                            <p class="text-muted small mb-1">⏱️ Sisa Waktu</p>
                            <div class="h5" id="countdown-qris">
                                <strong style="color: #dc3545;">00:00</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- QRIS Instructions -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Langkah-Langkah Pembayaran QRIS</h6>
                    </div>
                    <div class="card-body">
                        <ol class="ps-0 mb-0 list-no-style">
                            <li class="mb-3">
                                <span class="badge bg-warning me-2">1</span>
                                <strong>Buka aplikasi bank Anda</strong>
                            </li>
                            <li class="mb-3">
                                <span class="badge bg-warning me-2">2</span>
                                <strong>Pilih menu "Scan QRIS" atau "Transfer via QRIS"</strong>
                            </li>
                            <li class="mb-3">
                                <span class="badge bg-warning me-2">3</span>
                                <strong>Arahkan kamera ke QR Code di atas</strong>
                            </li>
                            <li class="mb-3">
                                <span class="badge bg-warning me-2">4</span>
                                <strong>Verifikasi nominal: Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong>
                            </li>
                            <li>
                                <span class="badge bg-warning me-2">5</span>
                                <strong>Masukkan PIN & selesaikan pembayaran</strong>
                            </li>
                        </ol>
                    </div>
                </div>

                <!-- Confirmation Action -->
                @if($payment->status === 'pending')
                    <form action="{{ route('payment.confirm-transfer', $payment) }}" method="POST" id="confirmForm">
                        @csrf
                        <button type="button" class="btn btn-warning btn-lg w-100 mb-2" 
                                onclick="confirmTransfer('QRIS', 'Rp {{ number_format($payment->amount, 0, ',', '.') }}', 'pembayaran QRIS')">
                            <i class="fas fa-check-circle"></i> Pembayaran Selesai - Konfirmasi Sekarang
                        </button>
                    </form>
                    <small class="text-muted d-block text-center">
                        Admin akan verifikasi pembayaran Anda dalam beberapa menit
                    </small>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>QRIS Dikonfirmasi!</strong> Menunggu verifikasi dari admin...
                    </div>
                @endif

            @endif

            <!-- Back Button -->
            <div class="mt-4 text-center">
                <a href="{{ route('booking.detail', $booking) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Detail Booking
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Style untuk countdown -->
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0854ca 100%);
    }

    .list-no-style {
        list-style: none;
    }

    .display-4 {
        font-size: 3.5rem;
    }

    .text-white-50 {
        color: rgba(255, 255, 255, 0.5);
    }
</style>

<!-- Countdown Timer Script -->
<script>
    function startCountdown(elementId) {
        const expiredAt = new Date('{{ $displayData["expired_at"]->format("Y-m-d H:i:s") }}').getTime();
        
        function updateTimer() {
            const now = new Date().getTime();
            const difference = expiredAt - now;

            const element = document.getElementById(elementId);
            if (!element) return;

            if (difference <= 0) {
                element.innerHTML = '<strong style="color: #6c757d;">00:00</strong>';
                setTimeout(() => location.reload(), 2000);
                return;
            }

            const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((difference % (1000 * 60)) / 1000);
            const color = minutes < 5 ? '#dc3545' : '#0d6efd';
            
            const formatted = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
            element.innerHTML = `<strong style="color: ${color};">${formatted}</strong>`;
        }

        updateTimer();
        setInterval(updateTimer, 1000);
    }

    function confirmTransfer(method, amount, detail) {
        const msg = `Konfirmasi Pembayaran ${method}?\n\nNominal: ${amount}\nDetail: ${detail}\n\nPastikan pembayaran sudah dikirim.`;
        if (confirm(msg)) {
            document.getElementById('confirmForm').submit();
        }
    }

    // Start countdown untuk semua method
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('countdown')) {
            startCountdown('countdown');
        }
        if (document.getElementById('countdown-qris')) {
            startCountdown('countdown-qris');
        }
    });
</script>
@endsection
