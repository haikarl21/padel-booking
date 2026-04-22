@extends('layouts.app')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Receipt Header -->
            <div class="card border-0 shadow-lg mb-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h4 class="mb-0">
                        <i class="fas fa-receipt"></i> Bukti Pembayaran
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Status Badge -->
                    <div class="text-center mb-4">
                        @if($payment->status === 'paid')
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle" style="font-size: 3rem;"></i>
                                <h3 class="mt-2">Pembayaran Berhasil</h3>
                                <p class="mb-0">Terima kasih telah melakukan pembayaran</p>
                            </div>
                        @elseif($payment->status === 'rejected')
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-times-circle" style="font-size: 3rem;"></i>
                                <h3 class="mt-2">Pembayaran Ditolak</h3>
                                <p class="mb-0">Pembayaran Anda tidak berhasil</p>
                            </div>
                        @elseif($payment->status === 'expired')
                            <div class="alert alert-warning" role="alert">
                                <i class="fas fa-hourglass-end" style="font-size: 3rem;"></i>
                                <h3 class="mt-2">Pembayaran Kadaluarsa</h3>
                                <p class="mb-0">Waktu pembayaran telah berakhir</p>
                            </div>
                        @else
                            <div class="alert alert-info" role="alert">
                                <i class="fas fa-hourglass-half fa-spin" style="font-size: 2rem;"></i>
                                <h3 class="mt-2">Pembayaran Pending</h3>
                                <p class="mb-0">Menunggu konfirmasi pembayaran</p>
                            </div>
                        @endif
                    </div>

                    <!-- Receipt Details -->
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td><strong>Kode Booking</strong></td>
                                    <td class="text-end">
                                        <span class="badge bg-primary">{{ $booking->booking_code }}</span>
                                    </td>
                                </tr>
                                <tr class="table-light">
                                    <td><strong>Nomor Order</strong></td>
                                    <td class="text-end"><code>{{ $payment->order_id }}</code></td>
                                </tr>
                                <tr>
                                    <td><strong>ID Pembayaran</strong></td>
                                    <td class="text-end"><small>{{ $payment->id }}</small></td>
                                </tr>
                                <tr class="table-light">
                                    <td><strong>Metode Pembayaran</strong></td>
                                    <td class="text-end">
                                        <strong>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <hr class="my-4">

                    <!-- Booking Details -->
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-calendar"></i> Informasi Lapangan
                            </h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($booking->date)->format('d M Y') }}
                                </li>
                                <li class="mb-2">
                                    <strong>Waktu:</strong> {{ $booking->timeSlot->start_time }} - {{ $booking->timeSlot->end_time }}
                                </li>
                                <li>
                                    <strong>Lapangan:</strong> {{ $booking->court->name }}
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-user"></i> Informasi Pemesan
                            </h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <strong>Nama:</strong> {{ $booking->customer_name }}
                                </li>
                                <li class="mb-2">
                                    <strong>No. HP:</strong> {{ $booking->phone }}
                                </li>
                                <li>
                                    <strong>Email:</strong> {{ $booking->email ?? 'N/A' }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Payment Summary -->
                    <div class="bg-light p-3 rounded">
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr>
                                    <td><strong>Jumlah Pembayaran</strong></td>
                                    <td class="text-end">
                                        <strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong>
                                    </td>
                                </tr>
                                @if($payment->amount != $booking->total_price)
                                <tr class="table-warning">
                                    <td><strong>Total Booking</strong></td>
                                    <td class="text-end">
                                        Rp {{ number_format($booking->total_price, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endif
                                <tr class="table-success">
                                    <td><strong>Status Pembayaran</strong></td>
                                    <td class="text-end">
                                        @if($payment->status === 'paid')
                                            <span class="badge bg-success">DIBAYAR</span>
                                        @elseif($payment->status === 'rejected')
                                            <span class="badge bg-danger">DITOLAK</span>
                                        @elseif($payment->status === 'expired')
                                            <span class="badge bg-warning text-dark">KADALUARSA</span>
                                        @else
                                            <span class="badge bg-info">PENDING</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status Transaksi</strong></td>
                                    <td class="text-end">
                                        <code>{{ $payment->transaction_status ?? 'N/A' }}</code>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-grid gap-2 mb-4">
                @if($payment->status !== 'paid')
                    <button type="button" class="btn btn-success btn-lg" onclick="markAsPaidAction({{ $payment->id }})">
                        <i class="fas fa-check"></i> Mark as Paid (Testing Only)
                    </button>
                @endif
                <a href="{{ route('booking.detail', $booking) }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-arrow-left"></i> Kembali ke Detail Booking
                </a>
                <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-home"></i> Kembali ke Beranda
                </a>
            </div>

            <!-- Testing Info -->
            <div class="alert alert-warning" role="alert">
                <h6 class="alert-heading">
                    <i class="fas fa-info-circle"></i> Informasi Testing
                </h6>
                <ul class="mb-0">
                    <li>Halaman ini adalah untuk testing tanpa pembayaran actual</li>
                    <li>Gunakan tombol "Mark as Paid" untuk mensimulasikan pembayaran sukses</li>
                    <li>Dalam production, hanya pembayaran via Midtrans yang diterima</li>
                    <li>Status transaksi: <code>{{ $payment->transaction_status ?? 'pending' }}</code></li>
                    <li>Order ID: <code>{{ $payment->order_id }}</code></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    function markAsPaidAction(paymentId) {
        if (confirm('Tandai pembayaran ini sebagai DIBAYAR? (Hanya untuk testing)')) {
            fetch("/payment/" + paymentId + "/mark-as-paid", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✓ Pembayaran berhasil ditandai sebagai DIBAYAR');
                    location.reload();
                } else {
                    alert('✗ Gagal: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('✗ Error: ' + error.message);
            });
        }
    }
</script>

@endsection
