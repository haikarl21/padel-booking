@extends('admin.layouts.app')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Beranda</a></li>
    <li class="breadcrumb-item active" aria-current="page">Booking</li>
@endsection

@section('page-title', 'Kelola Booking')

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<h2 class="mb-4">Booking</h2>
<form class="row mb-3" method="GET">
    <div class="col-md-3">
        <input type="text" name="search" class="form-control" placeholder="Cari nama/telepon" value="{{ request('search') }}">
    </div>
    <div class="col-md-2">
        <select name="status" class="form-control">
            <option value="">Semua Status</option>
            <option value="pending" @if(request('status')=='pending') selected @endif>Menunggu</option>
            <option value="partial" @if(request('status')=='partial') selected @endif>Sebagian</option>
            <option value="approved" @if(request('status')=='approved') selected @endif>Disetujui</option>
        </select>
    </div>
    <div class="col-md-2">
        <input type="date" name="date" class="form-control" value="{{ request('date') }}">
    </div>
    <div class="col-md-2">
        <button class="btn btn-primary">Terapkan</button>
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">Setel Ulang</a>
    </div>
</form>
<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th>Pelanggan</th>
            <th>Lapangan</th>
            <th>Tanggal</th>
            <th>Waktu</th>
            <th>Total</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($bookings as $booking)
        <tr>
            <td>{{ $booking->customer_name }}<br><small>{{ $booking->phone }}</small></td>
            <td>{{ $booking->court->name ?? '-' }}</td>
            <td>{{ $booking->date }}</td>
            <td>{{ $booking->timeSlot->display_text ?? '-' }}</td>
            <td>Rp {{ number_format($booking->total_price,0,',','.') }}</td>
            <td><span class="badge bg-{{ $booking->status=='approved' ? 'success' : ($booking->status=='partial' ? 'warning' : 'secondary') }}">{{ ucfirst($booking->status) }}</span></td>
            <td>
                <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-sm btn-info">Detail</a>
                <form action="{{ route('admin.bookings.destroy', $booking) }}" method="POST" style="display:inline-block">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger" onclick="return confirm('Hapus booking ini?')">Hapus</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
