# Code Examples: Midtrans Snap Integration

> File ini berisi contoh code untuk referensi dan copy-paste

---

## 1️⃣ PaymentController - generateSnapToken() Method

**File**: `app/Http/Controllers/PaymentController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $midtrans;

    public function __construct(MidtransService $midtrans)
    {
        $this->midtrans = $midtrans;
    }

    /**
     *📌 BARU METHOD: Generate Snap Token via AJAX
     * 
     * Route: POST /payment/{booking}/generate-snap-token
     * Called from: booking/detail.blade.php (JavaScript)
     * Returns: JSON {status, snap_token, order_id, client_key}
     */
    public function generateSnapToken(Request $request, Booking $booking)
    {
        try {
            // 1️⃣ Validasi input dari user (payment type)
            $validated = $request->validate([
                'payment_type' => 'required|in:full,partial',
            ]);

            // 2️⃣ Hitung amount (server-side security)
            $amount = $validated['payment_type'] === 'full' 
                ? $booking->total_price 
                : $booking->total_price * 0.5;

            // 3️⃣ Siapkan data customer untuk Midtrans
            $customerData = [
                'name'  => $booking->customer_name,
                'email' => $booking->user->email ?? 'biller@example.com',
                'phone' => $booking->phone,
            ];

            // 4️⃣ Generate transaksi & snap token
            $midtransResult = $this->midtrans->createTransaction(
                $booking->id,
                $amount,
                $validated['payment_type'],
                $customerData
            );

            // 5️⃣ Handle error dari Midtrans
            if ($midtransResult['status'] !== 'success') {
                return response()->json([
                    'status' => 'error',
                    'message' => $midtransResult['message'] ?? 'Gagal membuat transaksi',
                ], 400);
            }

            // 6️⃣ Simpan payment record ke database (status: pending)
            $payment = Payment::create([
                'booking_id'      => $booking->id,
                'order_id'        => $midtransResult['order_id'],
                'amount'          => $amount,
                'gross_amount'    => $amount,
                'payment_type'    => $validated['payment_type'],
                'payment_method'  => 'midtrans',
                'status'          => 'pending',
                'midtrans_response' => json_encode($midtransResult['response']),
            ]);

            // 7️⃣ Return snap token ke frontend
            return response()->json([
                'status'     => 'success',
                'snap_token' => $midtransResult['snap_token'],
                'order_id'   => $midtransResult['order_id'],
                'client_key' => $this->midtrans->getClientKey(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
```

---

## 2️⃣ Routes Configuration

**File**: `routes/web.php`

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

// Payment routes
Route::get('/payment/{booking}', [PaymentController::class, 'show'])->name('payment.show');
Route::post('/payment/{booking}/process', [PaymentController::class, 'process'])->name('payment.process');

// 📌 BARU ROUTE: Generate Snap Token via AJAX
Route::post('/payment/{booking}/generate-snap-token', [PaymentController::class, 'generateSnapToken'])
    ->name('payment.generate-snap-token');

Route::get('/payment/{payment}/check-status', [PaymentController::class, 'checkStatus'])->name('payment.check-status');

// Midtrans webhook (PUBLIC - no auth)
Route::post('/midtrans/callback', [\App\Http\Controllers\MidtransCallbackController::class, 'handle'])
    ->name('midtrans.callback');
```

---

## 3️⃣ Blade Template - Detail Booking

**File**: `resources/views/booking/detail.blade.php`

### A. Payment Summary Section
```blade
<!-- Ringkasan Pembayaran -->
<div class="card border-0 bg-warning bg-opacity-10 mb-4">
    <div class="card-body">
        <h5 class="card-title fw-bold mb-3">
            <i class="fas fa-money-bill-wave text-warning me-2"></i>Ringkasan Pembayaran
        </h5>
        <div class="row g-2">
            <div class="col-12 d-flex justify-content-between">
                <span style="color: #ffffff;">Jumlah Total</span>
                <span class="fw-bold text-warning">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
            </div>
            @if($payment)
            <div class="col-12 d-flex justify-content-between">
                <span style="color: #ffffff;">Jumlah Dibayar</span>
                <span class="fw-bold text-success">Rp {{ number_format($booking->paid, 0, ',', '.') }}</span>
            </div>
            <div class="col-12 d-flex justify-content-between border-top pt-2">
                <span style="color: #ffffff; font-weight: bold;">Sisa</span>
                <span class="fw-bold text-danger">Rp {{ number_format($booking->remaining, 0, ',', '.') }}</span>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- 📌 TOMBOL BAYAR SEKARANG - JANGAN REDIRECT! -->
@if($booking->status !== 'approved')
<div class="d-grid gap-2 mb-4">
    <button type="button" class="btn btn-warning btn-lg fw-bold" 
            data-bs-toggle="modal" 
            data-bs-target="#paymentTypeModal">
        <i class="fas fa-credit-card me-2"></i>Bayar Sekarang
    </button>
</div>

<!-- Info -->
<div class="alert" style="background-color: #002200; border-color: #004400; margin-bottom: 0;">
    <div class="d-flex">
        <i class="fas fa-info-circle me-3 mt-1" style="color: #90EE90;"></i>
        <div>
            <strong style="color: #90EE90;">Metode Pembayaran Tersedia</strong>
            <p class="mb-0" style="color: #ffffff; font-size: 0.95rem;">
                QRIS, Transfer Bank, E-wallet (GoPay, OVO, Dana), Cicilan Kartu Kredit, dan lainnya.
            </p>
        </div>
    </div>
</div>
@endif
```

### B. Modal Dialog - Payment Type Selection
```blade
<!-- 📌 MODAL: PILIH TIPE PEMBAYARAN -->
<div class="modal fade" id="paymentTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-warning">
            <div class="modal-header border-warning">
                <h5 class="modal-title fw-bold" style="color: #FFA500;">
                    <i class="fas fa-credit-card me-2"></i>Pilih Tipe Pembayaran
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <!-- Option 1: Full Payment -->
                <div class="row g-3">
                    <div class="col-12">
                        <div class="payment-option p-3 rounded border-2" 
                             style="border-color: #FFA500; cursor: pointer; background-color: #1a1a1a;" 
                             onclick="selectPaymentType('full')">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold mb-1" style="color: #FFA500;">
                                        <i class="fas fa-check-circle"></i> Pembayaran Penuh
                                    </h6>
                                    <small style="color: #999999;">Bayar seluruh jumlah sekarang</small>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-warning fs-5">
                                        Rp {{ number_format($booking->total_price, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Option 2: Partial Payment (50%) -->
                    <div class="col-12">
                        <div class="payment-option p-3 rounded border-2" 
                             style="border-color: #666666; cursor: pointer; background-color: #1a1a1a;" 
                             onclick="selectPaymentType('partial')">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold mb-1" style="color: #ffffff;">
                                        <i class="fas fa-hand-holding-usd"></i> Pembayaran 50%
                                    </h6>
                                    <small style="color: #999999;">Bayar separuh sekarang, separuh nanti</small>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-warning fs-5">
                                        Rp {{ number_format($booking->total_price * 0.5, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading state -->
                <div id="paymentLoading" class="d-none mt-3">
                    <div class="spinner-border text-warning me-2" role="status"></div>
                    <span style="color: #ffffff;">Mempersiapkan pembayaran...</span>
                </div>
            </div>

            <div class="modal-footer border-warning">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>
```

---

## 4️⃣ JavaScript - Payment Logic

**File**: `resources/views/booking/detail.blade.php` (di dalam `<script>` tag)

```javascript
<!-- ============================================
     VARIABEL & KONFIGURASI
     ============================================ -->

const BOOKING_ID = {{ $booking->id }};
const BOOKING_CODE = '{{ $booking->booking_code }}';
const PAYMENT_ROUTE = '{{ route("payment.generate-snap-token", $booking) }}';
const BOOKING_DETAIL_ROUTE = '{{ route("booking.detail", $booking) }}';

<!-- ============================================
     MIDTRANS SNAP LIBRARY
     ============================================ -->

<script src="https://app.sandbox.midtrans.com/snap/snap.js"></script>
<!-- Note: Change ke production URL saat go live:
     https://app.midtrans.com/snap/snap.js
-->


<!-- ============================================
     FUNCTION: SELECT PAYMENT TYPE
     ============================================ -->

/**
 * Dipanggil saat user klik salah satu opsi payment
 * Kirim AJAX request ke backend untuk generate snap token
 */
function selectPaymentType(paymentType) {
    console.log('User selected payment type:', paymentType);
    
    // Sembunyikan opsi, tampilkan loading
    document.querySelectorAll('.payment-option').forEach(el => el.style.display = 'none');
    document.getElementById('paymentLoading').classList.remove('d-none');

    // Fetch snap token dari backend
    fetchSnapToken(paymentType);
}


<!-- ============================================
     FUNCTION: FETCH SNAP TOKEN
     ============================================ -->

/**
 * Kirim AJAX request ke backend untuk generate snap token
 * 
 * Request:
 *   POST /payment/{booking}/generate-snap-token
 *   Data: {payment_type: 'full' atau 'partial', _token: csrf}
 * 
 * Response:
 *   {
 *     status: 'success',
 *     snap_token: '...',
 *     order_id: '...',
 *     client_key: '...'
 *   }
 */
function fetchSnapToken(paymentType) {
    const formData = new FormData();
    formData.append('payment_type', paymentType);
    formData.append('_token', '{{ csrf_token() }}');

    fetch(PAYMENT_ROUTE, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Tutup modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('paymentTypeModal'));
            modal.hide();

            // Tunggu modal animation selesai, lalu tampilkan Snap
            setTimeout(() => {
                showSnapPayment(data.snap_token);
            }, 300);
        } else {
            showAlert('Error: ' + data.message, 'danger');
            resetPaymentModal();
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        showAlert('Error: Gagal membuat transaksi. ' + error.message, 'danger');
        resetPaymentModal();
    });
}


<!-- ============================================
     FUNCTION: SHOW SNAP PAYMENT
     ============================================ -->

/**
 * Tampilkan Midtrans Snap popup dengan callback handlers
 */
function showSnapPayment(snapToken) {
    snap.pay(snapToken, {
        // 1️⃣ Callback: Pembayaran Pending
        onPending: function(result) {
            console.log('Payment pending:', result);
            showAlert(
                'Pembayaran Anda sedang diproses. Mohon tunggu beberapa saat...',
                'warning'
            );
        },

        // 2️⃣ Callback: Pembayaran Sukses
        onSuccess: function(result) {
            console.log('Payment success:', result);
            showAlert(
                'Pembayaran Anda berhasil! Booking akan diperbarui dalam beberapa detik...',
                'success'
            );
            
            // Tunggu webhook terproses, lalu reload
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        },

        // 3️⃣ Callback: Pembayaran Error
        onError: function(result) {
            console.log('Payment error:', result);
            showAlert(
                'Pembayaran gagal. Pesan: ' + (result.status_message || 'Silakan coba lagi'),
                'danger'
            );
            resetPaymentModal();
        },

        // 4️⃣ Callback: User Tutup Popup
        onClose: function() {
            console.log('User closed Snap popup');
            showAlert(
                'Anda menutup form pembayaran. Silakan klik "Bayar Sekarang" lagi jika ingin melanjutkan.',
                'info'
            );
            resetPaymentModal();
        }
    });
}


<!-- ============================================
     FUNCTION: RESET & HELPER FUNCTIONS
     ============================================ -->

/**
 * Reset modal ke state awal
 */
function resetPaymentModal() {
    document.querySelectorAll('.payment-option').forEach(el => el.style.display = 'block');
    document.getElementById('paymentLoading').classList.add('d-none');
}

/**
 * Show notification/alert
 */
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 99999; min-width: 300px;';
    
    const icons = {
        'success': 'fa-check-circle',
        'danger': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    };
    const icon = icons[type] || 'fa-info-circle';

    alertDiv.innerHTML = `
        <i class="fas ${icon} me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(alertDiv);

    // Auto remove after 5 seconds
    setTimeout(() => alertDiv.remove(), 5000);
}

/**
 * Ready event
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Snap payment initialized for booking ID:', BOOKING_ID);
});
```

---

## 5️⃣ Comparison: Before vs After

### ❌ BEFORE (Old Flow - Redirect Based)

```
User di detail booking page
        ↓
Klik "Lanjut ke Pembayaran"
        ↓
Redirect ke /payment/{booking}
        ↓
Submit form dengan payment type
        ↓
Redirect ke /payment/snap
        ↓
Halaman snap.blade.php tampil
        ↓
Klik "Bayar Sekarang" di halaman snap
        ↓
Snap.pay() trigger (di halaman lain)
```

**Problems:**
- Banyak page redirect → UX jelek
- Payment form terpisah dari detail booking
- Kompleks & sulit di-maintain

### ✅ AFTER (New Flow - Modal + AJAX)

```
User di detail booking page
        ↓
Klik "Bayar Sekarang"
        ↓
Modal popup pilih payment type
        ↓
AJAX fetch snap token (tanpa redirect)
        ↓
Modal tutup, Snap popup muncul langsung
        ↓
Klik payment method & complete pembayaran
        ↓
Callback onSuccess trigger
        ↓
Reload halaman untuk lihat status update
```

**Benefits:**
- ✅ No redirect - better UX
- ✅ Modal dialog - intuitive
- ✅ AJAX - seamless integration
- ✅ Simple & maintainable code
- ✅ Better error handling

---

## 6️⃣ Testing Script (Manual Testing)

```javascript
// Buka console di browser (F12 → Console tab)

// Test 1: Check Snap Library
console.log(snap);
// Output: ƒ (b){...} - Snap library loaded ✅

// Test 2: Check CSRF Token
console.log('{{ csrf_token() }}');
// Output: your_csrf_token_here ✅

// Test 3: Check Booking ID
console.log(BOOKING_ID);
// Output: 1 (or booking ID) ✅

// Test 4: Check Route
console.log(PAYMENT_ROUTE);
// Output: http://localhost:8000/payment/1/generate-snap-token ✅

// Test 5: Test fetch manually
fetch(PAYMENT_ROUTE, {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json',
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: 'payment_type=full&_token={{ csrf_token() }}',
})
.then(r => r.json())
.then(d => console.log('Response:', d))
.catch(e => console.error('Error:', e));
// Output: {status: 'success', snap_token: '...', ...} ✅
```
