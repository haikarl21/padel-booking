@extends('layouts.app')

@section('content')
<div class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="display-5 fw-bold text-center mb-5">Pilih Slot Waktu</h2>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card-custom">
                    <div class="card-body p-4">
                        <h3 class="card-title fw-bold mb-4">
                            <i class="fas fa-clipboard-list text-warning me-2"></i>Ringkasan Booking
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
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <span style="color: #ffffff;">Lapangan</span>
                                    <span class="fw-bold" style="color: #ffffff;">{{ $court->name }}</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <span style="color: #ffffff;">Tanggal</span>
                                    <span class="fw-bold" style="color: #ffffff;">{{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</span>
                                </div>
                            </div>
                            <hr class="my-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <span style="color: #ffffff;">Harga per jam</span>
                                    <span class="text-warning fw-bold">Rp {{ number_format($court->price_per_hour, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card-custom">
                    <div class="card-body p-4">
                        <h3 class="card-title fw-bold mb-4">
                            <i class="fas fa-clock text-warning me-2"></i>Pilih Slot Waktu
                        </h3>

                        <form action="{{ route('booking.confirm', $court) }}" method="POST" id="bookingForm">
                            @csrf
                            <input type="hidden" name="date" value="{{ $date }}">

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Pilih Slot Waktu (Bisa Lebih dari 1)</label>
                                <div class="row g-3">
                                    @foreach($timeSlots as $slot)
                                    @php
                                        $isBooked = in_array($slot->id, $bookedSlotIds ?? []);
                                    @endphp
                                    <div class="col-6">
                                        <div class="form-check p-3" style="background-color: #1a1a1a; border-radius: 10px; border: 1px solid #333333; cursor: {{ $isBooked ? 'not-allowed' : 'pointer' }};">
                                            <input class="form-check-input time-slot" type="checkbox" name="time_slot_ids[]" id="slot{{ $slot->id }}" value="{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}" data-id="{{ $slot->id }}" {{ $isBooked ? 'disabled' : '' }}>
                                            <label class="form-check-label fw-semibold {{ $isBooked ? 'text-danger' : '' }}" for="slot{{ $slot->id }}" style="cursor: pointer;">
                                                {{ $slot->display_text }}
                                                @if($isBooked)
                                                    <small class="text-danger ms-1">(dipesan)</small>
                                                @endif
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @error('time_slot_ids')
                                    <span class="text-danger mt-2 d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Dynamic Total Price Display -->
                            <div class="mb-4 p-3 rounded" style="background-color: #1a1a1a; border: 1px solid #FFA500;">
                                <div class="d-flex justify-content-between">
                                    <span style="color: #ffffff;">Total Harga:</span>
                                    <span class="fw-bold text-warning" id="totalPrice">Rp 0</span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="customer_name" class="form-label fw-semibold">Nama Lengkap</label>
                                <input type="text" id="customer_name" name="customer_name" required
                                       class="form-control form-control-lg" placeholder="Masukkan nama lengkap Anda"
                                       style="background-color: #1a1a1a; color: #ffffff; border-color: #333333;">
                                @error('customer_name')
                                    <span class="text-danger mt-2 d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="phone" class="form-label fw-semibold">Nomor Telepon</label>
                                <input type="tel" id="phone" name="phone" required
                                       class="form-control form-control-lg" placeholder="Masukkan nomor telepon Anda"
                                       style="background-color: #1a1a1a; color: #ffffff; border-color: #333333;">
                                @error('phone')
                                    <span class="text-danger mt-2 d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold">Email (Opsional)</label>
                                <input type="email" id="email" name="email"
                                       class="form-control form-control-lg" placeholder="Masukkan email Anda"
                                       style="background-color: #1a1a1a; color: #ffffff; border-color: #333333;">
                                @error('email')
                                    <span class="text-danger mt-2 d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary-custom w-100 btn-lg" id="confirmBtn" disabled>
                                <i class="fas fa-check me-2"></i>Konfirmasi Booking
                            </button>
                        </form>

                        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                        <script>
                            const pricePerHour = {{ $court->price_per_hour }};
                            const totalPriceEl = document.getElementById('totalPrice');
                            const confirmBtn = document.getElementById('confirmBtn');
                            const timeSlots = Array.from(document.querySelectorAll('.time-slot'));

                            function getHour(slotValue) {
                                return parseInt(slotValue.split('-')[0].split(':')[0], 10);
                            }

                            function updateTotalPrice() {
                                const checkedSlots = timeSlots.filter(s => s.checked).length;
                                if (checkedSlots > 0) {
                                    const total = pricePerHour * checkedSlots;
                                    totalPriceEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
                                    confirmBtn.disabled = false;
                                } else {
                                    totalPriceEl.textContent = 'Rp 0';
                                    confirmBtn.disabled = true;
                                }
                            }

                            timeSlots.forEach(slot => {
                                slot.addEventListener('change', function(e) {
                                    if (this.checked) {
                                        // Cari checkbox apa saja yang sedang tercentang
                                        const currentlyChecked = timeSlots.filter(s => s.checked);
                                        const hoursChecked = currentlyChecked.map(s => getHour(s.value));
                                        
                                        if (hoursChecked.length > 1) {
                                            const minHour = Math.min(...hoursChecked);
                                            const maxHour = Math.max(...hoursChecked);
                                            
                                            // Cek apakah ada slot di antara min dan max yang disabled (sudah dibooking)
                                            const slotsInRange = timeSlots.filter(s => {
                                                const h = getHour(s.value);
                                                return h >= minHour && h <= maxHour;
                                            });

                                            const hasDisabled = slotsInRange.some(s => s.disabled);

                                            if (hasDisabled) {
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: 'Gagal',
                                                    text: 'Tidak bisa memilih rentang ini karena ada jadwal yang sudah dipesan di antaranya.'
                                                });
                                                this.checked = false;
                                            } else {
                                                // Otomatis centang semua di antara min dan max
                                                slotsInRange.forEach(s => {
                                                    s.checked = true;
                                                });
                                            }
                                        }
                                    } else {
                                        // Jika user uncheck, kita hapus centang dari posisi tersebut hingga ujung yang sesuai
                                        // Atau untuk mudahnya, biarkan user uncheck dan sisanya dievaluasi jika terputus
                                        const stillChecked = timeSlots.filter(s => s.checked);
                                        if (stillChecked.length > 0) {
                                            const hoursChecked = stillChecked.map(s => getHour(s.value));
                                            const minHour = Math.min(...hoursChecked);
                                            const maxHour = Math.max(...hoursChecked);
                                            
                                            // Pastikan tidak ada gap di sisa pilihan (jika uncheck di tengah)
                                            if (maxHour - minHour + 1 !== stillChecked.length) {
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: 'Rentang Terputus',
                                                    text: 'Anda membatalkan slot di tengah. Rentang waktu akan disesuaikan.'
                                                });
                                                // Uncheck semua yang lebih besar dari yang di-uncheck
                                                const uncheckHour = getHour(this.value);
                                                timeSlots.forEach(s => {
                                                    if (s.checked && getHour(s.value) > uncheckHour) {
                                                        s.checked = false;
                                                    }
                                                });
                                            }
                                        }
                                    }
                                    updateTotalPrice();
                                });
                            });

                            // Initialize on page load
                            updateTotalPrice();
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
