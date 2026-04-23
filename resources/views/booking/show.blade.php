@extends('layouts.app')

@section('content')
<div class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="display-5 fw-bold text-center mb-5">Ringkasan Booking</h2>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card-custom">
                    <div class="card-body p-4">
                        <h3 class="card-title fw-bold mb-4">
                            <i class="fas fa-info-circle text-primary me-2"></i>Pilih Detail Booking
                        </h3>

                        @if($court->image_path)
                            <div class="mb-4 overflow-hidden rounded-3" style="height: 200px;">
                                <img src="{{ asset('storage/' . $court->image_path) }}"
                                     alt="{{ $court->name }}"
                                     class="w-100 h-100 object-fit-cover">
                            </div>
                        @else
                            <div class="mb-4 overflow-hidden rounded-3" style="height: 200px;">
                                <img src="{{ asset('images/court.jpg') }}"
                                     alt="{{ $court->name }}"
                                     class="w-100 h-100 object-fit-cover">
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-6">
                                <p style="color: #ffffff;" class="mb-1">Lapangan</p>
                                <p class="fw-bold mb-0" style="color: #ffffff;">{{ $court->name }}</p>
                            </div>
                            <div class="col-6 text-end">
                                <p style="color: #ffffff;" class="mb-1">Harga</p>
                                <p class="text-warning fw-bold mb-0">Rp {{ number_format($court->price_per_hour, 0, ',', '.') }}/jam</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card-custom">
                    <div class="card-body p-4">
                        <h3 class="card-title fw-bold mb-4">
                            <i class="fas fa-calendar-alt" style="color: #ffffff;"></i><span style="color: #ffffff; margin-left: 0.5rem;">Tanggal Booking</span>
                        </h3>

                        <form action="{{ route('booking.select-datetime', $court) }}" method="POST">
                            @csrf

                            <div class="mb-4">
                                <label for="date" class="form-label fw-semibold">Pilih Tanggal Booking</label>
                                <input type="date"
                                       id="date"
                                       name="date"
                                       required
                                       class="form-control form-control-lg"
                                       min="{{ date('Y-m-d') }}"
                                       style="border-radius: 15px; background-color: #1a1a1a; color: #ffffff; border-color: #333333; accent-color: #FFA500;">
                                <div class="form-text mt-2" style="color: #ffffff;">Pilih tanggal yang diinginkan untuk bermain</div>
                            </div>

                            <button type="submit" class="btn btn-primary-custom w-100 btn-lg">
                                <i class="fas fa-arrow-right me-2"></i>Lanjutkan ke Pemilihan Waktu
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
