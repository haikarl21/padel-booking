<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $booking->booking_code }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h2 class="mb-4">Bukti Pembayaran</h2>
    <p>Kode booking: <strong>{{ $booking->booking_code }}</strong></p>
    <p>Pelanggan: {{ $booking->customer_name }}</p>
    <p>Lapangan: {{ $booking->court->name }} pada {{ $booking->date->format('d M Y') }} jam {{ $booking->timeSlot->display_text }}</p>
    <hr>
    <p>Total: Rp {{ number_format($booking->total_price,0,',','.') }}</p>
    <p>Dibayar: Rp {{ number_format($booking->paid,0,',','.') }}</p>
    <p>Sisa: Rp {{ number_format($booking->remaining,0,',','.') }}</p>
    <hr>
    @php
        $latest = $booking->payments->sortBy('created_at')->last();
    @endphp
    @if($latest)
        <p>Metode pembayaran terakhir: {{ ucfirst(str_replace('_',' ',$latest->payment_method)) }}</p>
        <p>Status: {{ ucfirst($latest->status) }}</p>
        <p>Jumlah: Rp {{ number_format($latest->amount,0,',','.') }}</p>
    @endif

    <footer class="mt-5 text-muted"><small>Generated at {{ now() }}</small></footer>
</body>
</html>