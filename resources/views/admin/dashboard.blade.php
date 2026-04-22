@extends('admin.layouts.app')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dasbor</li>
@endsection

@section('page-title', 'Dasbor Admin')

@section('content')
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h6>Booking Hari Ini</h6>
                <h2>{{ $bookingToday }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h6>Pendapatan Hari Ini</h6>
                <h2>Rp {{ number_format($revenueToday,0,',','.') }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h6>Total Lapangan</h6>
                <h2>{{ $totalCourts }}</h2>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <h6>Pendapatan – 7 Hari Terakhir</h6>
        <canvas id="revenueChart"></canvas>
    </div>
</div>
<script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_keys($chartData)) !!},
            datasets: [{
                label: 'Pendapatan',
                data: {!! json_encode(array_values($chartData)) !!},
                borderColor: '#d4af37',
                backgroundColor: 'rgba(212,175,55,0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
@endsection
