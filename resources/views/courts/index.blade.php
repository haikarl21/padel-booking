@extends('layouts.app')

@section('content')
<div class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-4 fw-bold mb-3">
                    <span class="text-warning">Lapangan Padel</span> Tersedia
                </h2>
                <p class="lead" style="color: #ffffff;">Pilih lapangan pilihan Anda dan mulai bermain di Padel House</p>
            </div>
        </div>

        <div class="row g-4">
            @foreach($courts as $court)
            <div class="col-lg-6 col-xl-4">
                <div class="card-custom h-100 d-flex flex-column">
                    @php
                        $defaultImage = asset('images/court.jpg');
                        $imgUrl = $court->image_path ? asset('storage/' . $court->image_path) : $defaultImage;
                    @endphp
                    @if($imgUrl)
                        <div class="position-relative overflow-hidden" style="height: 250px;">
                            <img src="{{ $imgUrl }}"
                                 alt="{{ $court->name }}"
                                 class="card-img-top object-fit-cover w-100 h-100">
                        </div>
                    @else
                        <div class="bg-secondary d-flex align-items-center justify-content-center"
                             style="height: 250px;">
                            <span class="text-white display-4 fw-bold">{{ $court->name }}</span>
                        </div>
                    @endif

                    <div class="card-body d-flex flex-column p-4" style="min-height: 200px;">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title fw-bold mb-0" style="color: #ffffff;">{{ $court->name }}</h5>
                            <span class="badge bg-success fs-6 rounded-pill">Tersedia</span>
                        </div>

                        <h6 class="text-warning fw-bold mb-3">
                            Rp {{ number_format($court->price_per_hour, 0, ',', '.') }} <small style="color: #ffffff;">/jam</small>
                        </h6>

                        @if($court->description)
                            <p class="card-text mb-4" style="color: #ffffff;">{{ $court->description }}</p>
                        @else
                            <p class="card-text mb-4" style="color: #ffffff;">Lapangan padel profesional dengan fasilitas premium dan kondisi bermain yang sangat baik.</p>
                        @endif

                        <div class="mt-auto pt-3">
                            <a href="{{ route('booking.show', $court) }}"
                               class="btn btn-primary-custom w-100">
                                <i class="fas fa-calendar-check me-2"></i>Pesan Sekarang
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @if($courts->isEmpty())
        <div class="row">
            <div class="col-12 text-center py-5">
                <div class="card-custom">
                    <div class="card-body py-5">
                        <i class="fas fa-tennis-ball fa-4x mb-4" style="color: #666666;"></i>
                        <h4 style="color: #ffffff;">Tidak Ada Lapangan Tersedia</h4>
                        <p style="color: #666666;">Silakan periksa kembali nanti atau hubungi kami untuk informasi lebih lanjut.</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
