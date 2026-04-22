# MIDTRANS IMPLEMENTATION - VERIFICATION CHECKLIST

Checklist untuk memastikan implementasi Midtrans sudah benar dan siap ke production.

## ✅ Backend Setup

### Services & Controllers
- [ ] File `app/Services/MidtransService.php` ada dan lengkap
- [ ] File `app/Http/Controllers/PaymentController.php` sudah update dengan Midtrans
- [ ] File `app/Http/Controllers/MidtransCallbackController.php` ada
- [ ] File `config/midtrans.php` ada dengan konfigurasi lengkap

### Database
- [ ] Migration file `database/migrations/2026_03_28_000001_update_payments_table_for_midtrans.php` ada
- [ ] Sudah run `php artisan migrate`
- [ ] Tabel `payments` memiliki kolom baru:
  - `order_id`
  - `transaction_id`
  - `gross_amount`
  - `midtrans_response`
  - `midtrans_signature_key`
  - `paid_at`

### Models
- [ ] File `app/Models/Payment.php` sudah add fields di `$fillable`:
  - `order_id`, `transaction_id`, `gross_amount`
  - `midtrans_response`, `midtrans_signature_key`, `paid_at`
- [ ] File `app/Models/Payment.php` ada method helper:
  - `isSuccess()`
  - `isPending()`
  - `isFailed()`

### Configuration & Environment
- [ ] File `.env` sudah punya environment variables:
  ```
  MIDTRANS_IS_PRODUCTION=false
  MIDTRANS_SERVER_KEY=YOUR_KEY
  MIDTRANS_CLIENT_KEY=YOUR_KEY
  MIDTRANS_MERCHANT_ID=YOUR_ID
  ```
- [ ] File `.env.example` sudah ada Midtrans section (untuk dokumentasi)

### Routes
- [ ] File `routes/web.php` sudah add:
  - Route payment: `GET /payment/{booking}`
  - Route process: `POST /payment/{booking}/process`
  - Route check status: `GET /payment/{payment}/check-status`
  - Route webhook: `POST /midtrans/callback` (PUBLIC, no auth)

## ✅ Frontend Setup

### Views
- [ ] File `resources/views/payment/show.blade.php` sudah update untuk Midtrans
  - Menampilkan pilihan payment type (full/partial)
  - Action button ke payment.process
  - Info tentang Midtrans & payment methods
- [ ] File `resources/views/payment/snap.blade.php` ada untuk Snap page
  - Load Midtrans Snap library
  - Tombol "Bayar Sekarang" dengan snap.pay()
  - Callback handlers (onSuccess, onError, onClose)

## ✅ Package & Dependencies

- [ ] Package `midtrans/midtrans-php` sudah installed
- [ ] Check: `composer show | grep midtrans`

## ✅ Midtrans Dashboard Setup

- [ ] Login ke https://dashboard.midtrans.com dengan akun Anda
- [ ] Copy `Server Key` dari Settings → Access Keys
- [ ] Copy `Client Key` dari Settings → Access Keys
- [ ] Setup Webhook:
  - Settings → Configuration
  - Notification URL: `https://yourdomain.com/midtrans/callback`
  - Method: POST
  - Save
- [ ] Test Notification (klik tombol "Send Test Notification")
  - Harus dapat response di server logs

## ✅ Testing - Manual Walkthrough

### Scenario 1: Pembayaran Penuh dengan Kartu Kredit

```
1. Open http://localhost:8000/booking/{court_id}
2. Isi form booking (tanggal, waktu, customer_name, phone)
3. Click "Selesaikan Pembayaran"
4. Di halaman payment/show:
   - Pilih "Pembayaran Penuh"
   - Click "Lanjut ke Pembayaran"
5. Di halaman payment/snap:
   - Click "Bayar Sekarang"
   - Midtrans Snap popup terbuka
   - Pilih "Kartu Kredit"
   - Input test card: 5111111111111117 / 12/25 / 123
   - Click "Bayar"
   - Confirm OTP jika diminta
6. Setelah success:
   - Check database: payments.status = 'settlement'
   - Check database: bookings.status = 'approved'
   - Check logs: Midtrans callback received & processed
7. ✅ PASS: Booking berstatus approved, payment berstatus settlement
```

### Scenario 2: Pembayaran Sebagian dengan QRIS

```
1. Repeat steps 1-4 dari Scenario 1
2. Di payment/show:
   - Pilih "Pembayaran Sebagian (50%)"
   - Click "Lanjut ke Pembayaran"
3. Di halaman payment/snap:
   - Click "Bayar Sekarang"
   - Pilih "QRIS"
   - Scan QRIS dengan phone (use test app atau simulator)
4. Setelah success:
   - Check: payments.status = 'settlement'
   - Check: bookings.status = 'partial' (karena baru 50%)
5. ✅ PASS: Booking berstatus partial, user bisa bayar sisa
```

### Scenario 3: Pembayaran Expire (Test)

```
1. Create payment & payment record dengan status 'pending'
2. Tunggu timeout pembayaran (15 menit default di Midtrans)
   OR manual trigger via Midtrans API
3. Check database:
   - payments.status = 'expired' (via webhook)
   - bookings.status = unchanged (masih pending/partial)
4. ✅ PASS: Status expired, user bisa buat pembayaran baru
```

### Scenario 4: Payment Status Check (AJAX)

```
1. Buat payment
2. Buka DevTools (F12) → Network tab
3. Manual call endpoint: GET /payment/{payment_id}/check-status
4. Response harus berisi:
   {
     "status": "success",
     "payment_status": "settlement" atau "pending",
     "transactions": { ... }
   }
5. ✅ PASS: Status bisa dicek via AJAX
```

## ✅ Logging & Debugging

### Check Midtrans Logs
```bash
tail -f storage/logs/laravel.log | grep Midtrans
```

Output harus like:
```
[2026-03-28 15:30:45] local.INFO: Midtrans Callback Received {order_id: "BOOKING-123-1711610445", ...}
[2026-03-28 15:30:46] local.INFO: Payment Status Parsed {old_status: "pending", new_status: "settlement"}
[2026-03-28 15:30:46] local.INFO: Payment successful {order_id: "BOOKING-123-1711610445", ...}
```

### Check Midtrans Dashboard
1. Dashboard → Transactions
2. Filter by date
3. Click transaksi → lihat detail & webhook response
4. Pastikan status "CAPTURE" atau "SETTLEMENT"

## ✅ Security Verification

### Backend Security
- [ ] Server Key TIDAK ada di `.env.example` atau git history
- [ ] Server Key HANYA di `.env` production
- [ ] Signature verification di `MidtransCallbackController::handle()` wajib
- [ ] Amount validated server-side (tidak dari user input)
- [ ] Webhook route PUBLIC tapi protected via signature_key

### Frontend Security
- [ ] Client Key bisa di HTML (tidak rahasia)
- [ ] HTTPS digunakan di production
- [ ] CSRF token ada di form (@csrf)
- [ ] Tidak ada kartu kredit data disimpan di database/logs

### Testing Environment
- [ ] Gunakan Sandbox keys untuk testing
- [ ] Test card memang working: 5111111111111117
- [ ] Jangan gunakan kartu Anda sendiri untuk testing

## ✅ Production Checklist

Sebelum go-live ke production:

- [ ] Buat akun Midtrans production (bukan sandbox)
- [ ] Dapatkan Production Server Key & Client Key
- [ ] Update `.env`:
  ```env
  MIDTRANS_IS_PRODUCTION=true
  MIDTRANS_SERVER_KEY=Mid-server-YOUR_PROD_KEY (bukan SB-Mid)
  MIDTRANS_CLIENT_KEY=Mid-client-YOUR_PROD_KEY (bukan SB-Mid)
  ```
- [ ] Update Notification URL di Midtrans Dashboard:
  - Dari: `https://sandbox.test.com/midtrans/callback`
  - Ke: `https://production.com/midtrans/callback`
- [ ] Setup SSL/HTTPS untuk domain production
- [ ] Set `APP_DEBUG=false` di `.env` production
- [ ] Setup proper logging & monitoring
- [ ] Test full payment flow di production environment
- [ ] Pastikan 24/7 server monitoring & uptime
- [ ] Setup error alerts (email/SMS) untuk payment failures

## ✅ Monitoring & Maintenance

### Daily Checks
- [ ] Server logs untuk errors atau failed payments
- [ ] Midtrans Dashboard untuk transaction monitoring
- [ ] Database untuk payment status updates

### Weekly/Monthly
- [ ] Review payment success rate
- [ ] Check for webhook retry patterns (sign of issues)
- [ ] Review customer complaints/support tickets
- [ ] Update documentation jika ada perubahan

---

## Issues Checklist

### Jika Someting Goes Wrong:

- [ ] Check `.env` - semua keys valid?
- [ ] Check logs: `storage/logs/laravel.log`
- [ ] Check Midtrans Dashboard → Transactions
- [ ] Check database `payments` & `bookings` table
- [ ] Verify webhook received (check Midtrans dashboard)
- [ ] Check signature validation (valid signature = webhook from Midtrans)
- [ ] Clear Laravel cache: `php artisan cache:clear`
- [ ] Clear config cache: `php artisan config:clear`

---

## Success Metrics

Implementasi dianggap **BERHASIL** jika:

✅ Payment dapat dibuat tanpa error
✅ Snap popup terbuka dan functional
✅ User dapat memilih metode pembayaran
✅ Setelah bayar, status otomatis update ke 'settlement'
✅ Webhook diterima dalam < 5 menit
✅ Booking status update otomatis ke 'approved'
✅ Signature verification prevent fake callbacks
✅ Logs mencatat setiap transaksi
✅ Customer dapat melihat payment status real-time

---

Untuk bantuan lebih lanjut, lihat:
- `MIDTRANS_INTEGRATION.md` - Documentation lengkap
- `MIDTRANS_QUICKSTART.md` - Setup cepat 5 menit
- https://docs.midtrans.com - Official Midtrans docs
