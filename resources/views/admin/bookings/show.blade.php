@extends('admin.layouts.app')
@section('content')
<h2 class="mb-4">Detail Booking</h2>
<div class="row">
    <div class="col-md-7">
        <div class="card mb-3">
            <div class="card-body">
                <h5>Informasi Booking</h5>
                <div class="mb-2"><b>Kode Booking:</b> {{ $booking->booking_code }}</div>
                <div class="mb-2"><b>Pelanggan:</b> {{ $booking->customer_name }}</div>
                <div class="mb-2"><b>Telepon:</b> {{ $booking->phone }}</div>
                <div class="mb-2"><b>Lapangan:</b> {{ $booking->court->name ?? '-' }}</div>
                <div class="mb-2"><b>Tanggal:</b> {{ $booking->date }}</div>
                <div class="mb-2"><b>Slot Waktu:</b> {{ $booking->timeSlot->display_text ?? '-' }}</div>
                <div class="mb-2"><b>Durasi Total:</b> 1 Jam</div>
                <div class="mb-2"><b>Total:</b> Rp {{ number_format($booking->total_price,0,',','.') }}</div>
                <div class="mb-2"><b>Dibayar:</b> <span class="text-success">Rp {{ number_format($booking->paid,0,',','.') }}</span></div>
                <div class="mb-2"><b>Sisa:</b> <span class="text-danger">Rp {{ number_format($booking->remaining,0,',','.') }}</span></div>
                <div class="mb-2"><b>Status:</b> <span class="badge bg-{{ $booking->status=='approved' ? 'success' : ($booking->status=='partial' ? 'warning' : 'secondary') }}">{{ ucfirst($booking->status) }}</span></div>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card mb-3">
            <div class="card-body">
                <h5>Pembayaran</h5>
                @foreach($booking->payments as $payment)
                <div class="mb-2">
                    <b>{{ strtoupper(str_replace('_',' ', $payment->payment_method)) }}</b><br>
                    Rp {{ number_format($payment->amount,0,',','.') }}<br>
                    <span class="badge bg-{{ $payment->status=='completed' ? 'success' : 'secondary' }}">{{ ucfirst($payment->status) }}</span>
                    <br><small>{{ $payment->created_at }}</small>
                    @if($payment->status === 'pending')
                        <form method="POST" action="{{ route('admin.bookings.payments.approve', [$booking, $payment]) }}" style="display:inline">
                            @csrf
                            <button class="btn btn-sm btn-success mt-1">Setujui</button>
                        </form>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
<a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">Kembali</a>
@endsection
