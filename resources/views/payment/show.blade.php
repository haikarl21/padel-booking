@extends('layouts.app')

@section('content')
<div class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="display-5 fw-bold text-center mb-5">Selesaikan Pembayaran Anda</h2>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card-custom">
                    <div class="card-body p-4">
                        <h3 class="card-title fw-bold mb-4">
                            <i class="fas fa-clipboard-list text-warning me-2"></i>Ringkasan Booking
                        </h3>

                        <div class="mb-4" style="border: 1px solid #FFA500; border-radius: 8px; background-color: #1a1a1a;">
                            <div class="d-flex justify-content-between align-items-center px-3 py-2">
                                <span style="color: #ffffff;">Referensi Booking</span>
                                <span class="fw-bold text-warning">{{ $booking->booking_code }}</span>
                            </div>
                        </div>

                        <div class="alert mb-4" style="background-color: #332200; border-color: #664400;">
                            <div class="d-flex">
                                <i class="fas fa-exclamation-triangle me-3 mt-1" style="color: #FFA500;"></i>
                                <div>
                                    <strong style="color: #FFA500;">Simpan Kode Pelacakan Anda</strong>
                                    <p class="mb-0" style="color: #ffffff;">Anda akan memerlukan kode ini untuk melacak status booking Anda.</p>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 mb-4" style="background-color: #1a1a1a;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-warning fw-bold fs-5">{{ $booking->booking_code }}</span>
                                    <button onclick="navigator.clipboard.writeText('{{ $booking->booking_code }}')"
                                            class="btn btn-primary-custom btn-sm">
                                        <i class="fas fa-copy me-1"></i>Salin
                                    </button>
                                </div>
                            </div>
                        </div>

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
                            <hr class="my-3">
                            <div class="col-12 d-flex justify-content-between">
                                <span style="color: #ffffff;">Dibayar</span>
                                <span class="fw-bold" style="color: #90EE90;">Rp {{ number_format($booking->paid, 0, ',', '.') }}</span>
                            </div>
                            <div class="col-12 d-flex justify-content-between">
                                <span style="color: #ffffff;">Sisa</span>
                                <span class="fw-bold" style="color: #FF6B6B;">Rp {{ number_format($booking->remaining, 0, ',', '.') }}</span>
                            </div>
                            <div class="col-12 d-flex justify-content-between border-top pt-3" style="border-color: #333333;">
                                <span class="fw-bold" style="color: #ffffff;">Pembayaran Penuh</span>
                                <span class="text-warning fw-bold fs-5">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card-custom">
                    <div class="card-body p-4">
                        <h3 class="card-title fw-bold mb-4">
                            <i class="fas fa-credit-card text-warning me-2"></i>Jenis Pembayaran
                        </h3>

                        <!-- Info Midtrans Payment -->
                        <div class="alert mb-4" style="background-color: #002200; border-color: #004400;">
                            <div class="d-flex">
                                <i class="fas fa-check-circle me-3 mt-1" style="color: #90EE90;"></i>
                                <div>
                                    <strong style="color: #90EE90;">Pembayaran Aman & Mudah</strong>
                                    <p class="mb-0" style="color: #ffffff; font-size: 0.95rem;">
                                        Sistem pembayaran kami menggunakan <strong>Midtrans</strong> - platform pembayaran terpercaya dengan berbagai pilihan metode.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method Selection -->
                        <form action="{{ route('payment.process', $booking) }}" method="POST">
                            @csrf

                            <div class="mb-4">
                                <label for="payment_type" class="form-label fw-semibold">Jenis Pembayaran</label>
                                <select name="payment_type" id="payment_type" required class="form-select form-select-lg"
                                        style="background-color: #1a1a1a; color: #ffffff; border-color: #333333;">
                                    <option value="full" selected>
                                        Pembayaran Penuh - Rp {{ number_format($booking->total_price, 0, ',', '.') }}
                                    </option>
                                    <option value="partial">
                                        Pembayaran Sebagian (50%) - Rp {{ number_format($booking->total_price * 0.5, 0, ',', '.') }}
                                    </option>
                                </select>
                                <small class="form-text" style="color: #999999;">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Pilih jumlah pembayaran yang ingin Anda lakukan
                                </small>
                            </div>

                            <!-- Payment Methods Info -->
                            <div class="mb-4" style="background-color: #1a1a1a; padding: 15px; border-radius: 8px;">
                                <h5 class="fw-bold mb-3" style="color: #FFA500;">Metode Pembayaran Tersedia:</h5>
                                <ul style="color: #ffffff; margin-bottom: 0; padding-left: 20px;">
                                    <li class="mb-2"><strong>QRIS</strong> - Scan & bayar langsung</li>
                                    <li class="mb-2"><strong>Transfer Bank</strong> - BCA, BRI, Mandiri, dan bank lainnya</li>
                                    <li class="mb-2"><strong>E-Wallet</strong> - GoPay, OVO, Dana, LinkAja</li>
                                    <li><strong>Kartu Kredit</strong> - Cicilan tanpa bunga tersedia</li>
                                </ul>
                            </div>

                            <button type="submit" class="btn btn-primary-custom w-100 btn-lg">
                                <i class="fas fa-arrow-right me-2"></i>Lanjut ke Pembayaran
                            </button>
                        </form>

                        <!-- Additional Info -->
                        <div class="mt-4 pt-4" style="border-top: 1px solid #333333;">
                            <small style="color: #999999;">
                                <i class="fas fa-shield-alt me-1"></i>
                                Transaksi Anda dilindungi oleh enkripsi SSL dan proses verifikasi keamanan berlapis.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styling -->
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
