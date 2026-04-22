# 🔧 DEBUGGING: Mengapa Payment Gagal

Gunakan guide ini untuk menemukan root cause masalah pembayaran.

---

## 📋 Step 1: Check Browser Console (F12)

**Buka halaman booking detail:**
```
http://127.0.0.1:8000/booking/10/detail
```

**Buka Developer Tools (F12)** dan go to **Console tab**

**Klik tombol "Bayar Sekarang"**

Catat semua error message yang muncul di console. Anda harus melihat logs seperti:

```
=== FETCH SNAP TOKEN START ===
Payment Type: full
Route: http://127.0.0.1:8000/payment/10/generate-snap-token
Booking ID: 10
Response Status: 200
Response OK: true
Response Data: {status: 'success', snap_token: '...', ...}
✓ Token generated successfully
```

---

## 🔍 Step 2: Check Network Tab (F12)

**Tetap di Developer Tools → Network tab**

**Klik tombol "Bayar Sekarang" lagi**

**Cari request ke `/payment/{booking_id}/generate-snap-token`**

- **Status Code**: Harus `200` (success), BUKAN `404`, `422`, atau `500`
- **Response Type**: Harus `json`
- **Response Body**: Harus ada kunci `status: 'success'` dan `snap_token`

Jika response adalah error, lihat response body dan baca pesan error-nya.

---

## 📊 Step 3: Check Server Logs

**Buka terminal baru dan cek logs:**

```powershell
cd c:\TA\Padel\padel-booking
tail -f storage/logs/laravel.log
```

**Klik "Bayar Sekarang" di browser**, lalu lihat log messages yang muncul di terminal.

Cari logs berisi:
- `Payment token generation started`
- `Midtrans response received`
- `Payment record created successfully`

Atau jika error, cari:
- `Midtrans transaction failed`
- `Payment validation failed`
- `Payment token generation failed`

---

## 🐛 Common Issues & Solutions

### ❌ Issue 1: 404 Error di Network Tab

**Penyebab**: Route tidak terdaftar atau typo di URL  
**Solusi**:
```bash
# Verify route terdaftar
php artisan route:list | findstr generate-snap-token
# Harus muncul: POST /payment/{booking}/generate-snap-token
```

### ❌ Issue 2: 422 Validation Error

**Penyebab**: Payment type tidak valid atau CSRF token missing  
**Solusi**:
- Check di console: `selectPaymentType('full')` apakah benar-benar dipanggil
- Verify CSRF token ada: `console.log('{{ csrf_token() }}')`

### ❌ Issue 3: 500 Server Error

**Penyebab**: Exception di controller (kemungkinan Midtrans service error)  
**Solusi**:
- Check logs di terminal untuk detail error message
- Verify `.env` punya `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY`

### ❌ Issue 4: Response Status 200 tapi data.status = 'error'

**Penyebab**: Midtrans API call gagal  
**Lihat console atau network tab untuk `data.message`**

Contoh error messages:
- "Minimum transaction Rp1.000" → Amount terlalu kecil
- "Client key not found" → MIDTRANS_CLIENT_KEY tidak valid
- "Transaction not found" → Booking ID tidak valid

---

## 📝 Debug Script (Copy-Paste di Console)

Paste script ini di browser console (F12 → Console) untuk test secara manual:

```javascript
// ===== DEBUG SCRIPT =====
console.log('=== PAYMENT DEBUG START ===');

// Check 1: Snap library
console.log('✓ Snap loaded:', typeof snap !== 'undefined');

// Check 2: CSRF Token
const csrfToken = '{{ csrf_token() }}';
console.log('✓ CSRF token length:', csrfToken.length);

// Check 3: Global variables
console.log('✓ BOOKING_ID:', BOOKING_ID);
console.log('✓ PAYMENT_ROUTE:', PAYMENT_ROUTE);

// Check 4: Test fetch request
async function testPaymentFetch() {
    console.log('\n=== TESTING FETCH REQUEST ===');
    
    const response = await fetch(PAYMENT_ROUTE, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: new FormData(Object.entries({payment_type: 'full'}).reduce((f, [k, v]) => {f.append(k, v); return f;}, new FormData())),
    });
    
    console.log('Response Status:', response.status);
    const data = await response.json();
    console.log('Response Data:', data);
    
    return data;
}

// Run test
testPaymentFetch().then(result => {
    if (result.status === 'success') {
        console.log('✓ Token generated:', result.snap_token.substring(0, 20) + '...');
    } else {
        console.error('✗ Error:', result.message);
    }
});
```

---

## 🎯 Debugging Checklist

Sebelum report error, pastikan sudah check:

```
□ Browser console (F12) untuk error messages
□ Network tab (F12) untuk HTTP status code
□ Server logs (tail -f storage/logs/laravel.log)
□ .env file punya MIDTRANS_SERVER_KEY & MIDTRANS_CLIENT_KEY
□ Booking ID valid & booking ada di database
□ BookingDetail page bisa load dengan benar
□ "Bayar Sekarang" button ada dan bisa diklik

Jika semua OK tapi masih error:
□ Clear browser cache (Ctrl+Shift+Delete)
□ Restart Laravel server (kill & restart)
□ Check apakah ada PHP error (tail -f laravel.log)
```

---

## 📥 Report Format

Jika masih error setelah debugging, silakan report dengan format:

```
Browser Console Error:
[copy exact error message]

Network Status:
[HTTP status code]

Server Log Error:
[copy log message dari laravel.log]

.env Configuration:
MIDTRANS_CLIENT_KEY=[first 10 chars]...
MIDTRANS_SERVER_KEY=[first 10 chars]...
MIDTRANS_IS_PRODUCTION=false

Booking ID: [number]
Payment Amount: Rp [amount]
```

---

## 🚀 Quick Test (tanpa debugging)

Jika sudah confident, test flow lengkap:

```
1. Go to http://127.0.0.1:8000/booking/10/detail
2. Klik "Bayar Sekarang"
3. Pilih "Pembayaran Penuh"
4. Modal harus tutup & Snap popup muncul
5. Di Snap: Pilih QRIS
6. Complete dengan test data (card: 4811111111111114)
7. Klik "Confirm"
8. Status harus update ke "approved"
9. Done! 🎉
```

---

**Good luck debugging!** 🔧  
Jika ada yang unclear, tanya saja! 😊
