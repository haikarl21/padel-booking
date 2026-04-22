@extends('layouts.app')

@section('content')
<div class="hero-section">
    <div class="hero-content">
        <div class="container">
            <div class="row align-items-center justify-content-center" style="min-height: calc(100vh - 80px);">
                <div class="col-lg-8 text-white text-center">
                    <div class="hero-text-wrapper">
                        <h1 class="display-4 fw-bold mb-4" style="font-size: 3.5rem; line-height: 1.2; animation: slideInDown 0.8s ease-out; color: #ffffff;">
                            Tingkatkan Permainan Anda di
                        </h1>
                        <h2 class="display-3 fw-bold mb-4" style="font-size: 4rem; line-height: 1.1; animation: slideInUp 0.8s ease-out 0.2s both; color: #FFB81A;">
                            <i class="" style="color: #FFA500;"></i>Padel House
                        </h2>
                        <p class="lead mb-4" style="color: #ffffff; font-size: 1.25rem; line-height: 1.6; animation: fadeIn 1s ease-out 0.4s both;">
                            Lapangan Padel Profesional untuk Pengalaman Bermain Terbaik
                        </p>
                        <p class="mb-5" style="color: #e8e8e8; font-size: 1.1rem; line-height: 1.6; animation: fadeIn 1s ease-out 0.6s both;">
                            Ketersediaan real-time, penjadwalan yang mudah, dan pengalaman padel premium untuk para pemain.
                        </p>
                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center" style="animation: fadeIn 1s ease-out 0.8s both;">
                            <a href="{{ route('courts') }}" class="btn btn-primary-custom btn-lg px-5 py-3" style="box-shadow: 0 4px 15px rgba(255, 165, 0, 0.4); transition: all 0.3s ease;">
                                <i class="fas fa-calendar-check me-2"></i>Pesan Sekarang
                            </a>
                            <button class="btn btn-outline-light btn-lg px-5 py-3" style="border-color: #FFA500; color: #FFA500; transition: all 0.3s ease;" data-bs-toggle="modal" data-bs-target="#learnMoreModal">
                                <i class="fas fa-info-circle me-2"></i>Pelajari Selengkapnya
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Learn More Modal -->
<div class="modal fade" id="learnMoreModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background-color: #1a1a1a; border-color: #333333;">
            <div class="modal-header border-bottom" style="border-color: #333333 !important;">
                <h5 class="modal-title" style="color: #FFA500;">
                    <i class="fas fa-info-circle me-2"></i>About Padel House
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: brightness(0) invert(1);"></button>
            </div>
            <div class="modal-body" style="color: #ffffff;">
                <h6 class="mb-3" style="color: #FFA500;">Tentang Padel House</h6>
                <p style="color: #ffffff; line-height: 1.8;">
                    Padel House adalah fasilitas olahraga premium yang didedikasikan untuk memberikan pengalaman terbaik bagi para pemain padel di seluruh wilayah. Kami berkomitmen untuk menyediakan lapangan berkualitas tinggi dengan standar internasional.
                </p>

                <h6 class="mb-3 mt-4" style="color: #FFA500;">Visi Kami</h6>
                <p style="color: #ffffff; line-height: 1.8;">
                    Menjadi pusat olahraga padel terdepan yang menghadirkan inovasi dalam fasilitas, layanan, dan pengalaman pemain. Kami percaya bahwa padel bukan hanya olahraga, tetapi juga gaya hidup yang menghubungkan komunitas.
                </p>

                <h6 class="mb-3 mt-4" style="color: #FFA500;">Fasilitas Kami</h6>
                <ul style="color: #ffffff; line-height: 2;">
                    <li><i class="fas fa-check me-2" style="color: #FFA500;"></i>10+ Lapangan Padel Professional</li>
                    <li><i class="fas fa-check me-2" style="color: #FFA500;"></i>Lighting System dengan Standar Internasional</li>
                    <li><i class="fas fa-check me-2" style="color: #FFA500;"></i>Ruang Locker Room Modern dan Nyaman</li>
                    <li><i class="fas fa-check me-2" style="color: #FFA500;"></i>Cafe & Lounge Area</li>
                    <li><i class="fas fa-check me-2" style="color: #FFA500;"></i>Professional Coaching Services</li>
                    <li><i class="fas fa-check me-2" style="color: #FFA500;"></i>Rental Equipment Lengkap</li>
                </ul>

                <h6 class="mb-3 mt-4" style="color: #FFA500;">Mengapa Memilih Padel House?</h6>
                <p style="color: #ffffff; line-height: 1.8;">
                    Dengan pengalaman lebih dari 10 tahun dalam industri olahraga, Padel House telah melayani ribuan pemain dari berbagai level, mulai dari pemula hingga profesional. Tim kami yang berpengalaman siap membantu Anda mencapai potensi terbaik dalam bermain padel.
                </p>

                <p style="color: #ffffff; line-height: 1.8;">
                    Kami menggunakan teknologi booking online terkini untuk memudahkan Anda dalam memesan lapangan kapan saja, di mana saja. Sistem pembayaran kami aman dan mendukung berbagai metode pembayaran.
                </p>
            </div>
            <div class="modal-footer border-top" style="border-color: #333333 !important;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background-color: #333333; border-color: #333333;">
                    Tutup
                </button>
                <a href="{{ route('courts') }}" class="btn btn-primary-custom">
                    Mulai Booking
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
