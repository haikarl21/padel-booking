@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-warning bg-opacity-10 border-warning">
                    <h4 class="mb-0"><i class="fas fa-ticket-alt"></i> Detail Booking</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Kode Booking</h6>
                            <h5 class="fw-bold text-warning">{{ $booking->booking_code }}</h5>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Status</h6>
                            <span class="badge {{ $booking->status === 'approved' ? 'bg-success' : 'bg-warning' }} fs-6">
                                {{ strtoupper($booking->status) }}
                            </span>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Lapangan</h6>
                            <h5 class="fw-bold">{{ $booking->court->name }}</h5>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Tanggal</h6>
                            <h5 class="fw-bold">{{ $booking->date->format('d M Y') }}</h5>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Waktu</h6>
                            <h5 class="fw-bold">{{ $booking->timeSlot->start_time}} - {{ $booking->timeSlot->end_time }}</h5>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Customer</h6>
                            <h5 class="fw-bold">{{ $booking->customer_name }}</h5>
                        </div>
                    </div>

                    <hr>

                    <div class="bg-light p-3 rounded mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 text-muted">Total Harga</h6>
                            <h4 class="mb-0 fw-bold text-warning">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</h4>
                        </div>
                    </div>

                    @if($booking->status !== 'approved')
                    <button type="button" class="btn btn-warning btn-lg w-100 fw-bold" onclick="bayarSekarang()">
                        <i class="fas fa-credit-card me-2"></i> Bayar Sekarang
                    </button>
                    @else
                    <div class="alert alert-success"><i class="fas fa-check"></i> Pembayaran sudah lunas</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Midtrans Snap JS -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
    function bayarSekarang() {
        const bookingId = {{ $booking->id }};

        // Show loading
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
        btn.disabled = true;

        // Request snap token dari backend
        fetch('{{ route("payment.create-transaction") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                booking_id: bookingId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.snap_token) {
                // Show Midtrans Snap popup
                snap.pay(data.snap_token, {
                    onSuccess: function(result) {
                        alert('Pembayaran berhasil! Terima kasih.');
                        location.reload();
                    },
                    onPending: function(result) {
                        alert('Pembayaran menunggu konfirmasi.');
                    },
                    onError: function(result) {
                        alert('Pembayaran gagal. Silakan coba lagi.');
                    },
                    onClose: function() {
                        alert('Popup ditutup tanpa menyelesaikan pembayaran.');
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                });
            } else {
                alert('Gagal membuat transaksi: ' + (data.message || 'Unknown error'));
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
</script>
@endsection
