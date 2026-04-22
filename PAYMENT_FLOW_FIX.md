# Fix: Midtrans Snap Payment Flow di Halaman Detail Booking

## 📋 Ringkasan Masalah & Solusi

### Masalah Sebelumnya:
❌ User klik "Lanjut ke Pembayaran" → Hanya redirect ke halaman detail booking  
❌ Tidak ada popup Midtrans Snap yang muncul  
❌ Status transaksi selalu "Pending"  
❌ Flow pembayaran melibatkan multiple page redirects  

### Solusi yang Diimplementasikan:
✅ Tambah tombol "Bayar Sekarang" di halaman detail booking  
✅ Modal dialog untuk memilih tipe pembayaran (full/partial)  
✅ Fetch snap token via AJAX (tanpa redirect)  
✅ Tampilkan Snap popup langsung di halaman yang sama  
✅ Handle callbacks (success, pending, error, close)  

---

## 🔄 Flow Pembayaran (Setelah Fix)

```
USER FLOW:
┌─────────────────────────────────────────────────────────┐
│ 1. User di halaman Detail Booking                       │
│    - Lihat ringkasan booking & pembayaran              │
│    - Klik tombol "Bayar Sekarang"                      │
└────────────────────┬────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────┐
│ 2. Modal muncul: Pilih Tipe Pembayaran                  │
│    - Pembayaran Penuh (100%)                           │
│    - Pembayaran 50%                                     │
│    - User klik salah satu opsi                         │
└────────────────────┬────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────┐
│ 3. AJAX Request ke Backend                              │
│    POST /payment/{booking}/generate-snap-token         │
│    - Kirim payment_type (full/partial)                 │
│    - Backend: Create transaction di Midtrans           │
│    - Backend: Save payment record (status: pending)    │
│    - Return: snap_token + client_key                   │
└────────────────────┬────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────┐
│ 4. Tampilkan Midtrans Snap Popup                        │
│    snap.pay(snapToken, {                               │
│      onSuccess: ...,                                    │
│      onPending: ...,                                    │
│      onError: ...,                                      │
│      onClose: ...                                       │
│    })                                                   │
└────────────────────┬────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────┐
│ 5. User Melakukan Pembayaran                            │
│    - Pilih metode pembayaran (QRIS, Transfer, etc)    │
│    - Input data pembayaran sesuai metode               │
│    - Konfirmasi pembayaran                             │
└────────────────────┬────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────┐
│ 6. Midtrans Memproses Pembayaran                        │
│    - Verifikasi dengan bank/payment provider           │
│    - Tunggu konfirmasi pembayaran                      │
└────────────────────┬────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────┐
│ 7. Callback dari Midtrans (Webhook)                     │
│    POST /midtrans/callback                              │
│    - Midtrans mengirim notifikasi status pembayaran    │
│    - Backend verify signature (keamanan)               │
│    - Update payment status & booking status            │
└────────────────────┬────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────────────┐
│ 8. Frontend Callback Handler                            │
│    onSuccess: Tampilkan notifikasi sukses              │
│    onPending: Tampilkan notifikasi tunggu              │
│    onError: Tampilkan notifikasi error                 │
│    onClose: User bisa retry dengan klik Bayar lagi    │
│                                                         │
│    Setelah sukses: Reload halaman untuk melihat       │
│    booking status terupdate ke "approved"              │
└─────────────────────────────────────────────────────────┘
```

---

## 📁 File-File yang Diubah

### 1. **PaymentController.php** - Tambah method `generateSnapToken()`
- **Path**: `app/Http/Controllers/PaymentController.php`
- **Perubahan**:
  - `generateSnapToken()` - Endpoint untuk generate snap token via AJAX
  - Menerima POST request dengan `payment_type`
  - Return JSON dengan `snap_token`, `order_id`, `client_key`
  - Handle error dan validasi di server-side

### 2. **routes/web.php** - Tambah route untuk snap token
- **Path**: `routes/web.php`
- **Perubahan**:
  ```php
  Route::post('/payment/{booking}/generate-snap-token', [PaymentController::class, 'generateSnapToken'])->name('payment.generate-snap-token');
  ```

### 3. **booking/detail.blade.php** - Tambah UI & JavaScript
- **Path**: `resources/views/booking/detail.blade.php`
- **Perubahan**:
  - Tombol "Bayar Sekarang" (hanya tampil jika booking belum approved)
  - Modal dialog untuk pilih tipe pembayaran
  - JavaScript untuk handle Snap payment
  - Callback handlers (success, pending, error, close)

---

## 🎯 Key Features Implementasi

### 1. **Tombol "Bayar Sekarang"** (Non-Blocking)
```html
<button type="button" class="btn btn-warning btn-lg fw-bold" 
        data-bs-toggle="modal" data-bs-target="#paymentTypeModal">
    <i class="fas fa-credit-card me-2"></i>Bayar Sekarang
</button>
```
❌ **BUKAN**: `<a href="/payment/{booking}">` (redirect)  
✅ **ADALAH**: Button dengan `data-bs-toggle="modal"` (show modal popup)

### 2. **Modal Pilih Tipe Pembayaran**
```
┌─────────────────────────────┐
│ Pilih Tipe Pembayaran       │
├─────────────────────────────┤
│ ✓ Pembayaran Penuh          │
│   Rp 500.000                │
│                             │
│ ✓ Pembayaran 50%            │
│   Rp 250.000                │
├─────────────────────────────┤
│ [Batal]                     │
└─────────────────────────────┘
```

### 3. **Snap Token Generation via AJAX**
```javascript
// Fetch snap token dari backend
fetch('/payment/{booking}/generate-snap-token', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
    },
    body: new FormData(),
})
.then(response => response.json())
.then(data => {
    if (data.status === 'success') {
        snap.pay(data.snap_token, {...callbacks...});
    }
});
```

### 4. **Snap Payment Callback Handlers**
```javascript
snap.pay(snapToken, {
    onPending: (result) => {
        // User belum selesai / tunggu konfirmasi
        showAlert('Pembayaran sedang diproses...', 'warning');
    },
    
    onSuccess: (result) => {
        // Pembayaran berhasil
        showAlert('Pembayaran sukses!', 'success');
        setTimeout(() => window.location.reload(), 3000);
    },
    
    onError: (result) => {
        // Pembayaran gagal
        showAlert('Pembayaran gagal: ' + result.status_message, 'danger');
    },
    
    onClose: () => {
        // User tutup popup
        showAlert('Form pembayaran ditutup. Klik Bayar lagi untuk retry.', 'info');
    }
});
```

---

## 🔍 Debugging: Kesalahan Umum & Cara Memperbaiki

### ❌ Error 1: Snap Popup Tidak Muncul
**Penyebab**:
- Snap token tidak berhasil di-generate
- Client Key tidak benar
- Midtrans library tidak ter-load

**Solusi**:
```javascript
// Cek di browser console (F12)
console.log(snap); // Harus > function, bukan undefined

// Cek network tab:
// POST /payment/{booking}/generate-snap-token → Status 200
// Response harus: {status: 'success', snap_token: '...', ...}

// Cek .env:
MIDTRANS_SERVER_KEY=your_server_key
MIDTRANS_CLIENT_KEY=your_client_key
MIDTRANS_IS_PRODUCTION=false (untuk sandbox testing)
```

### ❌ Error 2: Button Redirect ke Payment Form Page (Bukan Popup)
**Penyebab**:
- Tombol masih menggunakan `<a href>` atau `form` dengan redirect
- Bukan using modal with AJAX

**Solusi**:
```html
<!-- ❌ SALAH - Ini redirect, bukan popup -->
<a href="/payment/{booking}" class="btn btn-warning">Bayar</a>

<!-- ✅ BENAR - Ini membuka modal & trigger Snap -->
<button type="button" data-bs-toggle="modal" data-bs-target="#paymentTypeModal">
    Bayar Sekarang
</button>
```

### ❌ Error 3: CSRF Token Mismatch
**Penyebab**:
- Forgot mengirim CSRF token di AJAX request

**Solusi**:
```javascript
fetch(url, {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}', // ← HARUS ADA
        'Accept': 'application/json',
    },
    body: formData,
});
```

### ❌ Error 4: Payment Status Tidak Terupdate
**Penyebab**:
- Webhook dari Midtrans tidak diterima / diproses
- Payment record tidak ter-update di database

**Solusi**:
```php
// Cek file logs
tail -f storage/logs/laravel.log | grep "Midtrans\|payment"

// Cek di database
SELECT * FROM payments WHERE booking_id = {booking_id};

// Verify Midtrans Dashboard:
// Setting → Notification → Check webhook URL config
```

### ❌ Error 5: onSuccess Callback Execute Tapi Status Tetap Pending
**Penyebab**:
- Webhook belum diproses oleh backend
- Timing issue: page reload sebelum webhook execute

**Solusi**:
```javascript
onSuccess: function(result) {
    showAlert('Pembayaran sukses!', 'success');
    // Tunggu 3 detik agar webhook terproses
    setTimeout(() => {
        window.location.reload();
    }, 3000); // ← Penting untuk wait hook execution
}
```

---

## 🧪 Testing Checklist

### Test di Sandbox (before production)

**1. Test Pembayaran Penuh (Full Payment)**
```
Steps:
1. Go to detail booking page
2. Klik "Bayar Sekarang" → Modal muncul
3. Pilih "Pembayaran Penuh" → Modal tutup, Snap popup muncul
4. Di Snap: Pilih QRIS
5. Gunakan test payment data (dari Midtrans dashboard)
6. Complete payment → modal onSuccess callback execute
7. Page reload → booking status harus "approved"
```

Expected Result:
```
✅ Snap popup muncul
✅ Bisa select payment method
✅ Bisa complete payment dengan test data
✅ onSuccess callback trigger
✅ Booking status update ke "approved"
✅ Payment status update ke "settlement"
```

**2. Test Pembayaran 50% (Partial Payment)**
```
Steps: Same as #1, tapi pilih "Pembayaran 50%"
Expected Amount: 50% dari total price
```

**3. Test Pending Payment**
```
Steps:
1. Di Snap: Pilih metode yang butuh manual konfirmasi (e.g., Bank Transfer)
2. Cancel payment (tap close button, jangan bayar)
3. onClose callback harus trigger
4. Show notifikasi "Pembayaran ditutup, silakan retry"
5. Payment record status harus tetap "pending"
```

**4. Test Error Handling**
```
Steps:
1. Di Snap: Pilih metode pembayaran
2. Close browser atau disconnect internet
3. Snap popup tutup
4. onError atau onClose callback trigger
5. Show notifikasi error
6. User bisa klik "Bayar Sekarang" lagi untuk retry
```

---

## 📊 Database Tables (Payment Flow)

### payments table
```sql
# Status progression:
"pending"    → User mulai process pembayaran (before Snap)
"settlement" → Pembayaran berhasil (from Midtrans webhook)
"failed"     → Pembayaran gagal
"expired"    → Pembayaran expired

# Columns:
- order_id               : Unique order ID dari Midtrans
- transaction_id         : Unique transaction ID dari Midtrans
- gross_amount           : Total jumlah pembayaran (Rp)
- midtrans_response      : JSON response dari Midtrans API
- midtrans_signature_key : SHA512 signature untuk verifikasi webhook
- paid_at                : Timestamp pembayaran sukses
```

### bookings table
```sql
# Status progression:
"pending"   → Booking created, waiting for payment
"approved"  → Payment received (status settlement)
"cancelled" → Admin cancel atau payment failed/expired

# Columns yang update via payment webhook:
- status : Update from "pending" → "approved" saat payment settlement
```

---

## 🔐 Security Checklist

✅ **CSRF Protection**
- All POST requests include `X-CSRF-TOKEN` header

✅ **Webhook Signature Verification**
- MidtransCallbackController verify SHA512 signature
- Only process webhook if signature valid

✅ **Amount Validation**
- Amount calculated server-side (user cannot manipulate)
- Verify payment amount matches booking total

✅ **HTTPS in Production**
- Change `MIDTRANS_IS_PRODUCTION=false` → `true`
- Use production Midtrans keys (not SB-Mid-xxx)
- Setup SSL certificate on domain

---

## 📚 File Locations Summary

```
Database:
├── database/migrations/2026_03_28_000001_update_payments_table_for_midtrans.php
└── app/Models/Payment.php

Backend:
├── app/Http/Controllers/PaymentController.php (NEW: generateSnapToken method)
├── app/Services/MidtransService.php
├── app/Http/Controllers/MidtransCallbackController.php
└── routes/web.php (NEW: payment.generate-snap-token route)

Frontend:
├── resources/views/booking/detail.blade.php (NEW: Bayar button + Modal + JS)
├── resources/views/payment/show.blade.php (form untuk payment.process)
└── resources/views/payment/snap.blade.php (old: redirect flow)

Config:
├── config/midtrans.php
├── .env (MIDTRANS_SERVER_KEY, MIDTRANS_CLIENT_KEY)
└── .env.example
```

---

## 🚀 Next Steps

1. **Test Payment Flow** (Sandbox Mode)
   - Go to `http://localhost:8000/booking/{booking_id}/detail`
   - Click "Bayar Sekarang" button
   - Select payment type (full/partial)
   - Complete test payment

2. **Monitor Logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```
   - Check for "Midtrans callback received"
   - Verify payment status update

3. **Production Setup** (when ready)
   - Get production Midtrans keys
   - Update `.env`: `MIDTRANS_IS_PRODUCTION=true`
   - Setup webhook URL in Midtrans dashboard
   - Test with real payment

---

## 📞 Support & Resources

- **Midtrans Snap Docs**: https://snap-docs.midtrans.com/
- **Midtrans Sandbox**: https://sandbox.midtrans.com/
- **Test Payment Methods**: https://midtrans.com/payments/bank-transfer-guides
