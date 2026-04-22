@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <!-- Booking Header -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-3">Detail Pembayaran</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Kode Booking:</strong> 
                                <span class="badge bg-primary">{{ $booking->booking_code }}</span>
                            </p>
                            <p class="mb-2">
                                <strong>Nama Pemesan:</strong> {{ $booking->customer_name }}
                            </p>
                            <p class="mb-2">
                                <strong>Tanggal & Jam:</strong> 
                                {{ $booking->date->format('d/m/Y') }}
                            </p>
                            <p class="mb-0">
                                <strong>Metode Pembayaran:</strong> 
                                <span class="badge bg-info">{{ $payment->getMethodDisplayName() }}</span>
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="mb-2">
                                <strong>Status Pembayaran:</strong> 
                                @if($payment->status === 'paid')
                                    <span class="badge bg-success">Terbayar</span>
                                @elseif($payment->status === 'rejected')
                                    <span class="badge bg-danger">Ditolak</span>
                                @elseif($payment->status === 'expired')
                                    <span class="badge bg-warning">Expired</span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                            </p>
                            <p class="mb-2">
                                <strong>Total Harga:</strong> 
                                <span class="text-primary fs-5">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Alert -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Sukses!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Error Alert -->
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Payment Status Section -->
            @if($payment->status === 'paid')
                <!-- PAID STATUS -->
                <div class="card border-0 shadow-sm mb-4 border-start border-5 border-success">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                        </div>
                        <h4 class="text-success mb-2">Pembayaran Berhasil!</h4>
                        <p class="text-muted">Pembayaran Anda telah dikonfirmasi oleh admin.</p>
                        <p class="mb-0">
                            <small class="text-muted">Dikonfirmasi pada: {{ $payment->paid_at->format('d/m/Y H:i') }}</small>
                        </p>
                    </div>
                </div>
            @elseif($payment->status === 'rejected')
                <!-- REJECTED STATUS -->
                <div class="card border-0 shadow-sm mb-4 border-start border-5 border-danger">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2 text-center">
                                <i class="fas fa-times-circle text-danger" style="font-size: 2.5rem;"></i>
                            </div>
                            <div class="col-md-10">
                                <h5 class="text-danger mb-2">Pembayaran Ditolak</h5>
                                <p class="mb-2"><strong>Alasan:</strong></p>
                                <div class="alert alert-light border">
                                    {{ $payment->rejection_reason }}
                                </div>
                                <p class="mb-0 text-muted">
                                    <small>Silakan upload bukti transfer yang benar atau hubungi admin untuk bantuan lebih lanjut.</small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Allow Re-upload -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">📤 Upload Bukti Pembayaran Baru</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('payment.upload-proof', $payment) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="proof_file" class="form-label">Pilih Bukti Transfer (JPG/PNG, Max 5MB)</label>
                                <input type="file" class="form-control @error('proof_file') is-invalid @enderror" 
                                       id="proof_file" name="proof_file" accept="image/jpeg,image/png" required>
                                @error('proof_file')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-cloud-upload-alt"></i> Upload Bukti Transfer
                            </button>
                        </form>
                    </div>
                </div>
            @elseif($payment->status === 'expired')
                <!-- EXPIRED STATUS -->
                <div class="card border-0 shadow-sm mb-4 border-start border-5 border-secondary">
                    <div class="card-body text-center">
                        <i class="fas fa-hourglass-end text-secondary" style="font-size: 2.5rem;"></i>
                        <h4 class="text-secondary mt-3 mb-2">Waktu Pembayaran Habis</h4>
                        <p class="text-muted">Jangka waktu pembayaran 30 menit telah terlewat.</p>
                        <p class="mb-0">
                            <small class="text-muted">Silakan hubungi admin untuk membuat pembayaran baru.</small>
                        </p>
                    </div>
                </div>
            @else
                <!-- PENDING STATUS -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">💳 Informasi Bank Tujuan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-muted mb-1">Nama Bank</p>
                                <p class="h6 mb-3">{{ $displayData['bank']['bank_name'] }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted mb-1">Atas Nama</p>
                                <p class="h6 mb-3">{{ $displayData['bank']['account_holder'] }}</p>
                            </div>
                        </div>
                        <p class="text-muted mb-1">Nomor Rekening</p>
                        <p class="h5 mb-0 font-monospace">
                            <strong>{{ $displayData['bank']['account_number'] }}</strong>
                        </p>
                    </div>
                </div>

                <!-- Unique Payment Amount -->
                <div class="card border-0 shadow-sm mb-4 bg-primary text-white">
                    <div class="card-body text-center">
                        <p class="mb-2 text-white-50">🎯 NOMINAL PEMBAYARAN UNIK</p>
                        <h2 class="mb-1">Rp <strong>{{ $displayData['total_unique'] }}</strong></h2>
                        <p class="mb-3 text-white-50 small">
                            Total Harga: Rp {{ number_format($payment->amount, 0, ',', '.') }} 
                            + Kode Unik: {{ str_pad($displayData['unique_code'], 3, '0', STR_PAD_LEFT) }}
                        </p>
                    </div>
                </div>

                <!-- Countdown Timer -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body text-center">
                        <p class="text-muted mb-2">⏱️ SISA WAKTU PEMBAYARAN</p>
                        <div class="timer mb-2" style="font-size: 2.5rem; font-weight: bold; color: #dc3545;">
                            <span id="countdown">00:00</span>
                        </div>
                        <p class="text-muted small mb-0">
                            Pembayaran harus dilakukan dalam 30 menit dari sekarang
                        </p>
                    </div>
                </div>

                <!-- Confirmation Button - User Clicked "I've Paid" -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">✅ Konfirmasi Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        @if($payment->proof_file)
                            <div class="alert alert-success mb-3">
                                <strong>✓ Pembayaran sudah dikonfirmasi</strong>
                                <p class="mb-0 small">Menunggu verifikasi final dari admin...</p>
                            </div>
                        @else
                            <p class="text-muted mb-3">
                                Setelah Anda melakukan transfer dengan nominal <strong>Rp {{ $displayData['total_unique'] }}</strong> ke rekening yang tertera, 
                                silakan klik tombol di bawah untuk mengonfirmasi bahwa Anda sudah melakukan transfer.
                            </p>
                            <p class="text-danger small mb-3">
                                <i class="fas fa-exclamation-circle"></i>
                                <strong>Perhatian:</strong> Pastikan Anda sudah transfer dengan nominal YANG BENAR (termasuk kode unik) sebelum klik tombol!
                            </p>

                            <form action="{{ route('payment.confirm-transfer', $payment) }}" method="POST" id="confirmForm">
                                @csrf
                                <button type="button" class="btn btn-success btn-lg w-100" 
                                        onclick="if(confirm('Pastikan Anda sudah transfer dengan nominal Rp {{ $displayData['total_unique'] }} ke: {{ $displayData['bank']['account_number'] }}\n\nAda cara untuk membatalkan jika salah. Lanjutkan?')) { document.getElementById(\"confirmForm\").submit(); }">
                                    <i class="fas fa-check-circle"></i> Saya Sudah Transfer - Konfirmasi Sekarang
                                </button>
                            </form>

                            <small class="text-muted d-block mt-2">
                                Anda akan diminta admin untuk verifikasi transfer Anda. Proses verifikasi biasanya selesai dalam 5-10 menit pada jam kerja.
                            </small>
                        @endif
                    </div>
                </div>

                <!-- Instructions -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">📋 Petunjuk Transfer</h5>
                    </div>
                    <div class="card-body">
                        <ol class="mb-0">
                            <li class="mb-2">
                                <strong>Buka aplikasi bank Anda</strong> (BCA Mobile, mBanking, ATM, dll)
                            </li>
                            <li class="mb-2">
                                <strong>Transfer ke {{ $displayData['bank']['account_number'] }}</strong> ({{ $displayData['bank']['account_holder'] }})
                            </li>
                            <li class="mb-2">
                                <strong>Nominal HARUS: Rp {{ $displayData['total_unique'] }}</strong> 
                                <br><small class="text-muted">(Total Rp {{ number_format($payment->amount, 0, ',', '.') }} + Kode Unik {{ str_pad($displayData['unique_code'], 3, '0', STR_PAD_LEFT) }})</small>
                            </li>
                            <li class="mb-2">
                                <strong>Transfer berhasil?</strong> Tunggu uang masuk ke rekening kami (biasanya langsung untuk bank sama)
                            </li>
                            <li>
                                <strong>Klik tombol "Saya Sudah Transfer"</strong> untuk melanjutkan proses
                            </li>
                        </ol>
                    </div>
                </div>
            @endif

            <!-- Back Button -->
            <div class="mt-4">
                <a href="{{ route('booking.detail', $booking) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Detail Booking
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Countdown Timer Script-->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Update timer setiap detik
        function updateTimer() {
            // Get expired_at timestamp dari server
            const expiredAt = new Date('{{ $displayData["expired_at"]->format("Y-m-d H:i:s") }}').getTime();
            const now = new Date().getTime();
            const difference = expiredAt - now;

            if (difference <= 0) {
                // Payment sudah expired
                document.getElementById('countdown').textContent = '00:00';
                document.getElementById('countdown').parentElement.style.color = '#6c757d';
                
                // Auto refresh untuk update status
                setTimeout(() => location.reload(), 1000);
                return;
            }

            // Calculate minutes dan seconds
            const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((difference % (1000 * 60)) / 1000);

            // Format dengan leading zero
            const formattedTime = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
            document.getElementById('countdown').textContent = formattedTime;

            // Change color jika kurang dari 5 menit
            if (minutes < 5) {
                document.getElementById('countdown').parentElement.style.color = '#dc3545';
            }
        }

        // Initial update
        updateTimer();

        // Update setiap 1 detik
        setInterval(updateTimer, 1000);

        // Auto check status setiap 5 detik
        setInterval(function() {
            fetch('{{ route("payment.get-status", $payment) }}')
                .then(response => response.json())
                .then(data => {
                    // Jika status berubah, reload page
                    if (data.status !== 'pending') {
                        location.reload();
                    }
                })
                .catch(error => console.error('Status check error:', error));
        }, 5000);
    });
</script>

<style>
    .timer {
        font-family: 'Courier New', monospace;
        letter-spacing: 2px;
    }

    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
    }
</style>
@endsection
