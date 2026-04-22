# 🎯 MIDTRANS SNAP PAYMENT INTEGRATION GUIDE

**Status:** ✅ **IMPLEMENTED & READY**  
**Last Updated:** March 28, 2026  
**Environment:** Sandbox (Testing)

---

## 📋 RINGKASAN IMPLEMENTASI

Sistem pembayaran telah diupdate menggunakan **Midtrans Snap** dengan:

✅ **Snap Popup** - Popup pembayaran muncul saat user klik "Bayar Sekarang"  
✅ **Multi-Method** - QRIS, Virtual Account, E-wallet, Kartu Kredit otomatis tersedia  
✅ **Real-time Verification** - Payment status dicheck otomatis dari Midtrans  
✅ **Webhook Callback** - Auto-update status pembayaran saat transaksi berhasil  
✅ **Sandbox Mode** - Safe untuk testing sebelum production

---

## 🏗️ STRUKTUR IMPLEMENTASI

### **1. Backend Configuration**

**File:** `config/midtrans.php`
```php
return [
    'is_production' => false,  // ← Sandbox mode
    'server_key' => env('MIDTRANS_SERVER_KEY'),    // Keep SECRET
    'client_key' => env('MIDTRANS_CLIENT_KEY'),    // Can be public
    'snap_js_url_sandbox' => 'https://app.sandbox.midtrans.com/snap/snap.js',
    // ... config lainnya
];
```

**File:** `.env` (Sandbox Keys)
```env
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SERVER_KEY=Mid-server-xxxxxxxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=Mid-client-xxxxxxxxxxxxxxxxx
MIDTRANS_MERCHANT_ID=M088508069
```

✅ **Catatan:** Keys ini adalah SANDBOX untuk testing. Untuk production, ganti dengan keys dari Midtrans production dashboard.

### **2. Backend Controller**

**File:** `app/Http/Controllers/MidtransPaymentController.php`

**Key Methods:**
```php
getSnapToken(Request $request)     // Generate token untuk Snap popup
finish(Request $request)            // Callback setelah user close popup
callback(Request $request)          // Webhook dari Midtrans (PUBLIC!)
checkStatus(Payment $payment)       // Check status dari Midtrans API
```

**Flow:**
```
1. User klik "Bayar Sekarang"
        ↓
2. Browser AJAX fetch ke /payment/snap-token (POST)
        ↓
3. Controller validate & setup Midtrans config
        ↓
4. Generate Snap Token menggunakan Midtrans SDK
        ↓
5. Return token ke browser
        ↓
6. Browser call snap.pay(token) untuk popup
```

### **3. Frontend Integration**

**File:** `resources/views/booking/detail.blade.php`

**Loading Snap Library:**
```html
<!-- Load dari Midtrans CDN (Sandbox) -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js"></script>
```

**JavaScript Handlers:**
```javascript
// 1. onSuccess - Pembayaran berhasil
snap.pay(token, {
    onSuccess: function(result) {
        // Update payment status & redirect ke finish page
    }
})

// 2. onPending - Pembayaran menunggu konfirmasi (transfer bank, dll)
onPending: function(result) {
    // Show "waiting for verification" message
    // Polling untuk cek status
}

// 3. onError - Pembayaran gagal/error
onError: function(result) {
    // Show error message
    // Allow user untuk retry
}

// 4. onClose - User close popup tanpa bayar
onClose: function() {
    // Reset form
    // Allow retry
}
```

### **4. Payment Routes**

**File:** `routes/web.php`

```php
// Generate Snap Token (protected, memerlukan login)
Route::post('/payment/snap-token', [MidtransPaymentController::class, 'getSnapToken'])->middleware('auth')->name('payment.snap-token');

// Finish page (redirect setelah close popup)
Route::get('/payment/finish', [MidtransPaymentController::class, 'finish'])->name('payment.finish');

// Webhook callback (PUBLIC - tidak perlu auth!)
Route::post('/payment/callback', [MidtransPaymentController::class, 'callback'])->name('payment.callback');

// Check payment status (protected, untuk polling)
Route::get('/payment/{payment}/check-status', [MidtransPaymentController::class, 'checkStatus'])->middleware('auth')->name('payment.check-status');
```

**⚠️ PENTING:** Route `/payment/callback` HARUS PUBLIC karena Midtrans mengirim webhook dari server mereka (bukan dari user browser).

---

## 🔄 PAYMENT FLOW (STEP-BY-STEP)

### **Phase 1: User Initiates Payment**
```
User di halaman booking detail
    ↓
Klik "Bayar Sekarang"
    ↓
Modal 1 muncul: Pilih tipe pembayaran (Penuh/50%)
    ↓
User pilih "Pembayaran Penuh"
    ↓
Modal 2 muncul: Pilih metode pembayaran
```

### **Phase 2: Generate Snap Token**
```
User pilih metode (QRIS/Bank Transfer/E-wallet/Kartu Kredit)
    ↓
Browser AJAX POST ke /payment/snap-token
    ├─ booking_id
    ├─ payment_type
    └─ payment_method
    ↓
PaymentController:
    1. Validate booking & user
    2. Setup Midtrans config
    3. Create payment record
    4. Generate Snap token
    5. Save payment to database
    ↓
Return JSON: { success: true, snap_token: "..." }
    ↓
Browser receive token
```

### **Phase 3: Snap Popup Shown**
```
JavaScript call: snap.pay(token, { callbacks... })
    ↓
Midtrans Snap popup muncul
    ↓
User lihat berbagai metode pembayaran:
    ├─ QRIS
    ├─ Bank Transfer (VA BCA, BRI, Mandiri, Permata, Cimb)
    ├─ E-wallet (GoPay, OVO, Shopeepay, Dana, Linkaja)
    ├─ Kartu Kredit
    └─ Cicilan
    ↓
User pilih metode & complete payment
```

### **Phase 4: Midtrans Processing**
```
Midtrans server process pembayaran
    ↓
2 pathway:
    
    A. Instant (QRIS, E-wallet):
        1. Status: capture
        2. Midtrans call webhook: /payment/callback
        3. Controller update payment status → paid
        4. snap.pay() callback: onSuccess
        
    B. Bank Transfer (Virtual Account):
        1. Status: pending (menunggu uang masuk)
        2. Midtrans generate VA number
        3. User transfer ke VA
        4. Saat uang masuk → Midtrans call webhook
        5. snap.pay() callback: onPending atau onSuccess
        
    C. Kartu Kredit:
        1. User enter card details di popup
        2. Midtrans process
        3. Status: capture (jika 3DS approve)
        4. snap.pay() callback: onSuccess
        
    D. User close popup:
        1. snap.pay() callback: onClose
        2. User bisa retry dengan bayar sekarang lagi
```

### **Phase 5: Callback Handling**

**Webhook dari Midtrans ke /payment/callback (POST):**
```
Midtrans server kirim:
{
    "order_id": "ORDER-23-...",
    "gross_amount": 350000,
    "transaction_status": "settlement",  // capture/settlement/pending/deny/expire
    "fraud_status": "accept",
    "signature_key": "...hashing...",
    "... other fields ..."
}

Controller:
    1. Verify signature (prevent spoofing)
    2. Find payment by order_id
    3. Update payment status
    4. Update booking status → approved
    5. Return 200 OK

Frontend (auto polling):
    Jika pending → polling setiap 2 detik
    Jika settlement/capture → redirect ke receipt
    Jika denied/cancelled → show error & allow retry
```

### **Phase 6: Completion**

```
Payment approved:
    ↓
Booking status: pending → approved
    ↓
User redirect ke receipt page
    ↓
Email confirmation sent (bisa ditambah)
    ↓
Booking locked (tidak bisa change/cancel)
```

---

## 🧪 TESTING DENGAN SANDBOX

### **1. Test Credentials**
```
Server Key: Mid-server-xxxxxxxxxxxxxxxxx
Client Key: Mid-client-xxxxxxxxxxxxxxxxx
Snap URL: https://app.sandbox.midtrans.com/snap/snap.js
```

### **2. Test Payment Methods**

**QRIS (Instant):**
- Scan QR dengan bank app
- Popup: Select bank & verify
- Status: Langsung settlement

**Virtual Account BCA:**
- No: 1116666999936  (format: [Bank Code][Customer Code][Random])
- Amount: Harus EXACT dengan order amount
- Status: settlement saat uang masuk

**GoPay:**
- Click in popup → redirect ke GoPay app/web
- Verify dengan OTP
- Status: settlement setelah verify

**Kartu Kredit (Dengan 3DS):**
- Card: tester cards available di Midtrans docs
- Exp: any future date
- CVC: any 3 digits
- Status: settlement with 3DS approval

### **3. How to Test**

1. **Create booking** → Lihat di booking detail
2. **Click "Bayar Sekarang"** → Pilih "Pembayaran Penuh"
3. **Select method** → E.g., "QRIS"
4. **Snap popup muncul** → BERHASIL! ✅
5. **Click payment method** → Ikuti instruksi
6. **Complete payment** → System auto-update status
7. **Check receipt** → Booking now approved

---

## 🔐 SECURITY IMPLEMENTATION

### **1. Server Key Protection**
```php
// Server key TIDAK pernah dikirim ke frontend
// Hanya disimpan di .env dan config
MIDTRANS_SERVER_KEY=... (KEEP SECRET)
```

### **2. Client Key Configuration**
```javascript
// Client key set dari backend (tidak hardcoded di template)
window.Midtrans.clientKey = '{{ config("midtrans.client_key") }}';
```

### **3. Signature Verification**
```php
// Webhook signature di-verify sebelum update database
$my_signature = hash('sha512', $order_id . $status_code . $gross_amount . $server_key);
if ($server_signature !== $my_signature) {
    // Reject as spoofed webhook
}
```

### **4. CSRF Protection**
```html
<!-- All forms include CSRF token -->
<input type="hidden" name="_csrf_token" value="{{ csrf_token() }}">
```

### **5. Authentication Check**
```php
// Only authenticated users bisa request token
Route::post('/payment/snap-token', ...)->middleware('auth');

// Verify booking ownership
if ($booking->user_id !== Auth::id()) {
    return 403 Unauthorized;
}
```

---

## 🚀 PRODUCTION DEPLOYMENT CHECKLIST

### **Before Going LIVE:**

- [ ] Change `MIDTRANS_IS_PRODUCTION` = true
- [ ] Update Server Key to production key
- [ ] Update Client Key to production key
- [ ] Update Snap JS URL to production
- [ ] Set `APP_ENV=production`
- [ ] Update `.env` configuration
- [ ] Test IN PRODUCTION SANDBOX first
- [ ] Set webhook URL di Midtrans dashboard
- [ ] Enable email notifications
- [ ] Setup payment receipt PDF generation
- [ ] Monitor first week of transactions
- [ ] Setup notification email for failed payments

### **Webhook Configuration (Midtrans Dashboard):**

1. Login ke https://dashboard.midtrans.com
2. Go to Settings → Configuration
3. Set HTTP Notification URL: `https://your-domain.com/payment/callback`
4. Enable notification untuk semua payment status
5. Test webhook dari dashboard

---

## 📊 PAYMENT STATUS MAPPING

| Midtrans Status | Booking Status | Action |
|---|---|---|
| `settlement` / `capture` | approved | ✅ Booking confirmed |
| `pending` | pending | ⏳ Waiting for payment |
| `denied` | pending | ❌ Payment failed, allow retry |
| `cancel` | pending | ❌ Payment cancelled, allow retry |
| `expire` | expired | ❌ Payment window closed (30 min) |

---

## 🆘 TROUBLESHOOTING

### **Snap popup tidak muncul**

**Cause:** Snap.js library tidak ter-load  
**Solution:**
```javascript
// Check di console
console.log(typeof snap);  // Should be 'function'
console.log(window.Midtrans.clientKey);  // Should show key
```

### **Payment token generate error**

**Cause:** Midtrans config salah  
**Solution:**
```bash
# Check .env keys
grep MIDTRANS .env

# Clear config cache
php artisan config:clear
php artisan config:cache

# Test dengan direct API call
curl -X POST https://app.sandbox.midtrans.com/snap/v1/transactions \
  -H "Authorization: Basic $(echo -n YOUR_SERVER_KEY: | base64)"
```

### **Webhook tidak ter-hit**

**Cause:** Webhook URL tidak accessible dari Midtrans  
**Solution:**
```bash
# Make sure route is PUBLIC (no auth middleware)
# Test dengan Midtrans dashboard "Send Test Webhook"
# Monitor Laravel logs: tail -f storage/logs/laravel.log
```

### **Signature verification failed**

**Cause:** Server key salah atau tampered request  
**Solution:**
```php
// Debug di callback method
Log::info('Signature Debug', [
    'received' => $server_signature,
    'calculated' => $my_signature,
    'order_id' => $order_id,
]);
```

---

## 📞 SUPPORT RESOURCES

- **Midtrans Docs:** https://docs.midtrans.com
- **Snap Documentation:** https://docs.midtrans.com/en/snap/overview
- **API Reference:** https://docs.midtrans.com/en/api-reference
- **Sandbox Credentials:** https://dashboard.sandbox.midtrans.com
- **Test Payment Methods:** https://docs.midtrans.com/en/technical-reference/sandbox-test-payment

---

## ✅ CURRENT IMPLEMENTATION STATUS

| Component | Status | Location |
|---|---|---|
| Config | ✅ Done | `config/midtrans.php` |
| Controller | ✅ Done | `app/Http/Controllers/MidtransPaymentController.php` |
| Routes | ✅ Done | `routes/web.php` |
| Frontend | ✅ Done | `resources/views/booking/detail.blade.php` |
| Snap Library | ✅ Done | CDN loaded |
| Callback Handling | ✅ Done | Webhook signature verified |
| Finish Page | ✅ Done | Auto-polling status |
| Error Handling | ✅ Done | Try-catch + user alerts |

---

## 🎯 NEXT STEPS (Optional Enhancements)

1. **Email Notifications**
   - Send confirmation email after successful payment
   - Send reminder for pending bank transfer
   - Send receipt PDF

2. **Payment Analytics**
   - Dashboard untuk payment statistics
   - Success/failure rate tracking
   - Revenue reporting

3. **Advanced Features**
   - Installment plans (if Midtrans supports)
   - Multi-currency support
   - Recurring payments (subscription)
   - Payment dispute handling

---

**✨ Midtrans Snap Integration Complete! Ready for Testing & Production Deployment ✨**

