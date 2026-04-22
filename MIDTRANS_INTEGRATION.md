# MIDTRANS PAYMENT GATEWAY INTEGRATION GUIDE

## 📋 Daftar Isi
1. [Persiapan & Setup](#persiapan--setup)
2. [Struktur Implementasi](#struktur-implementasi)
3. [Flow Pembayaran](#flow-pembayaran)
4. [Konfigurasi Midtrans](#konfigurasi-midtrans)
5. [Testing](#testing)
6. [Troubleshooting](#troubleshooting)
7. [Security Best Practices](#security-best-practices)

---

## Persiapan & Setup

### 1. Daftar ke Midtrans
- Kunjungi https://midtrans.com
- Register akun (sandbox untuk testing terlebih dahulu)
- Dapatkan credentials:
  - **Server Key** (rahasia, hanya di backend)
  - **Client Key** (boleh public, untuk frontend)
  - **Merchant ID** (optional)

### 2. Setup Environment Variables
Edit file `.env` Anda dan tambahkan:

```env
# Midtrans Configuration
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SERVER_KEY=SB-Mid-server-YOUR_SERVER_KEY_HERE
MIDTRANS_CLIENT_KEY=SB-Mid-client-YOUR_CLIENT_KEY_HERE
MIDTRANS_MERCHANT_ID=your_merchant_id
```

> **Catatan**: Gunakan `SB-Mid-server-xxx` untuk Sandbox (testing)  
> Gunakan `Mid-server-xxx` untuk Production (real payment)

### 3. Jalankan Migration
```bash
php artisan migrate
```

Ini akan membuat kolom-kolom baru di tabel `payments`:
- `order_id` - Unique ID untuk setiap transaksi
- `transaction_id` - ID dari Midtrans
- `gross_amount` - Jumlah yang dikirim ke Midtrans
- `midtrans_response` - Full response dari Midtrans (JSON)
- `midtrans_signature_key` - Untuk signature verification
- `paid_at` - Timestamp pembayaran berhasil

---

## Struktur Implementasi

### File yang Ditambah/Dimodifikasi:

```
app/
├── Services/
│   └── MidtransService.php          (NEW) Service untuk Midtrans API
├── Http/Controllers/
│   ├── PaymentController.php        (UPDATED) Tambah Midtrans logic
│   └── MidtransCallbackController.php (NEW) Handle webhook dari Midtrans
└── Models/
    └── Payment.php                  (UPDATED) Tambah fields & methods

config/
└── midtrans.php                     (NEW) Konfigurasi Midtrans

database/
└── migrations/
    └── 2026_03_28_000001_update_payments_table_for_midtrans.php (NEW)

resources/views/payment/
├── show.blade.php                   (UPDATED) Payment method selection
└── snap.blade.php                   (NEW) Midtrans Snap Payment Page

routes/
└── web.php                          (UPDATED) Tambah webhook route
```

### Class & Method Reference:

#### MidtransService (app/Services/MidtransService.php)
- `createTransaction()` - Buat transaksi dan generate snap token
- `getTransactionStatus()` - Cek status transaksi di Midtrans
- `verifySignature()` - Verifikasi webhook signature untuk security
- `parseTransactionStatus()` - Parse status dari Midtrans response
- `getClientKey()` - Dapatkan client key untuk frontend

#### PaymentController (app/Http/Controllers/PaymentController.php)
- `show()` - Tampilkan halaman pilihan metode pembayaran
- `process()` - Buat transaksi dan generate snap token
- `checkStatus()` - AJAX endpoint untuk cek status payment

#### MidtransCallbackController (app/Http/Controllers/MidtransCallbackController.php)
- `handle()` - Main webhook handler (dipanggil oleh Midtrans)
- `handleSuccessfulPayment()` - Update booking saat payment success
- `handleExpiredPayment()` - Handle payment expired
- `handleFailedPayment()` - Handle payment failed

---

## Flow Pembayaran

### User Journey:

```
┌─────────────────────────────────────────────────────────────┐
│ 1. User membuat booking (Booking page)                       │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. User pilih jenis pembayaran (payment/show.blade.php)     │
│    → Pembayaran Penuh atau Sebagian (50%)                   │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. Backend: PaymentController@process                        │
│    → Call MidtransService::createTransaction()              │
│    → Generate snap_token dari Midtrans                      │
│    → Simpan payment record dengan status 'pending'          │
│    → Return payment/snap.blade.php dengan snap_token        │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 4. Frontend: Snap Payment Page (payment/snap.blade.php)     │
│    → Load Midtrans Snap library                             │
│    → Klik tombol "Bayar Sekarang"                           │
│    → Snap.pay() membuka popup pembayaran                    │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 5. User Pilih Metode & Bayar                                │
│    Pilihan:                                                  │
│    • QRIS (scan & bayar)                                     │
│    • Transfer Bank (manual transfer)                         │
│    • E-Wallet (GoPay, OVO, Dana, dll)                        │
│    • Kartu Kredit (termasuk cicilan)                         │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼ (SETELAH USER BAYAR)
┌─────────────────────────────────────────────────────────────┐
│ 6. Midtrans Kirim Webhook ke Backend                        │
│    POST http://yourapp.com/midtrans/callback                │
│    Data: order_id, status_code, gross_amount, signature_key │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 7. Backend: MidtransCallbackController@handle               │
│    → Verifikasi signature (security check)                  │
│    → Parse status pembayaran                                │
│    → Update payment record & booking status                 │
│    → Return 200 OK to Midtrans                              │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 8. Frontend: Poll atau Webhook Update                       │
│    → Status booking otomatis update ke 'approved'           │
│    → User lihat booking detail dengan status 'approved'     │
└─────────────────────────────────────────────────────────────┘
```

---

## Konfigurasi Midtrans

### Setup Webhook di Dashboard Midtrans:

1. Login ke https://dashboard.midtrans.com
2. Pilih "Settings" → "Configuration"
3. Di bagian "Notification URL", isi:
   - **Notification URL**: `https://yourdomain.com/midtrans/callback`
   - Pilih metode: "POST"
4. Click "Save"

### Status Pembayaran:

Midtrans mengirimkan status dalam field `transaction_status`:

| Status | Arti | Action |
|--------|------|--------|
| `settlement` | Pembayaran berhasil | ✅ Update booking ke 'approved' |
| `pending` | Menunggu pembayaran | ⏳ Keep status 'pending' |
| `expire` | Waktu pembayaran habis | ❌ Set status 'expired' |
| `failed` | Pembayaran gagal | ❌ Set status 'failed' |
| `cancel` | Pembayaran dibatalkan | ❌ Set status 'failed' |
| `deny` | Pembayaran ditolak | ❌ Set status 'failed' |

---

## Testing

### Sandbox Testing:

Gunakan kartu2 test di Sandbox Midtrans untuk testing:

**Kartu Email/Phone Success:**
```
Email: success@midtrans.com
Kode OTP: 123456
```

**Kartu Kredit Test:**
- Nomor: 5111111111111117
- Expired: 12/25
- CVV: 123

> Full test credentials: https://docs.midtrans.com/en/technical-reference/sandbox-test-data

### Manual Testing Flow:

1. **Buka browser** dan akses: `http://localhost:8000/booking/{courtId}`
2. **Buat booking** dengan pilih court, waktu, dll
3. **Klik "Selesaikan Pembayaran"**
4. **Pilih jenis pembayaran** (full atau partial)
5. **Klik "Lanjut ke Pembayaran"** → Akan redirect ke payment/snap
6. **Klik "Bayar Sekarang"** → Snap popup akan terbuka
7. **Pilih metode** (QRIS atau kartu kredit)
8. **Lakukan pembayaran** (gunakan test data)
9. **Ceck database** - `payments` table harus update status ke 'settlement'
10. **Cek booking** - `bookings` table harus update status ke 'approved'

### Cek Webhook (Laravel Logs):

```bash
# Terminal
tail -f storage/logs/laravel.log
```

Anda akan melihat log:
```
[2026-03-28 10:30:45] local.INFO: Midtrans Callback Received {
  "order_id": "BOOKING-123-1711610445",
  "status_code": "200",
  "gross_amount": "500000"
}
```

---

## Troubleshooting

### ❌ Error: "Gagal membuat transaksi pembayaran"

**Penyebab**: Server Key atau Client Key tidak valid

**Solusi**:
```bash
# Check .env
cat .env | grep MIDTRANS

# Pastikan format:
# MIDTRANS_SERVER_KEY=SB-Mid-server-xxx
# MIDTRANS_CLIENT_KEY=SB-Mid-client-xxx
```

### ❌ Error: "Invalid signature"

**Penyebab**: Webhook signature verification gagal

**Solusi**:
- Pastikan `MIDTRANS_SERVER_KEY` sama antara `.env` dan dashboard Midtrans
- Check logs: `storage/logs/laravel.log`
- Verifikasi di Midtrans Dashboard → Transaction → click transaksi → see webhook response

### ❌ Webhook tidak diterima (status payment tidak update)

**Penyebab**: Notification URL tidak tersave di Midtrans atau firewall issue

**Solusi**:
1. Login Midtrans Dashboard
2. Settings → Configuration
3. Ensure Notification URL is set: `https://yourdomain.com/midtrans/callback`
4. Trigger manual resend di dashboard (klik transaction → kebab menu → resend notification)

### ❌ Snap Payment Page blank/tidak load

**Penyebab**: Client Key tidak valid atau Snap library gagal load

**Solusi**:
```blade
<!-- Di payment/snap.blade.php, check -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js"></script>
<!-- atau Production: https://app.midtrans.com/snap/snap.js -->

<!-- Check di browser console (F12 → Console) -->
<!-- Harus ada: snap object yang available -->
```

### ✅ Debug: Enable Midtrans Debug Logs

Buka `app/Services/MidtransService.php`:

```php
// Tambahkan ini di __construct():
Config::$logger->setLoggerName('Midtrans');
Config::$logger->setDebug(true);
```

---

## Security Best Practices

### 🔒 Backend Security:

1. **Server Key Harus Rahasia**
   - Jangan commit ke git
   - Jangan pernah expose di frontend
   - Simpan hanya di `.env` backend

2. **Signature Verification**
   - Selalu verify signature webhook dengan `verifySignature()`
   - Jangan skip verification meskipun "trusted"
   - Signature dibuat dengan: `sha512(orderId + statusCode + grossAmount + serverKey)`

3. **Validate Amount**
   - Jangan percaya amount dari user input
   - Server-side compute amount dari `$booking->total_price`
   - Bandingkan dengan `gross_amount` dari webhook

### 🔒 Frontend Security:

1. **Client Key Boleh Public**
   - Safe untuk dirender di HTML
   - Client key hanya bisa untuk membaca transaksi
   - Tidak bisa membuat/mengubah transaksi

2. **HTTPS Only**
   - Production harus gunakan HTTPS
   - Sandbox bisa HTTP untuk development

3. **CSRF Protection**
   - Laravel sudah automatic
   - @csrf di setiap form

### 🔒 Database:

1. **Jangan Store Kartu Kredit**
   - Midtrans handle segala card data
   - Kami hanya store order_id dan status
   - Tidak perlu PCI compliance khusus

2. **Log Sensitif Data**
   - Jangan log signature_key or bank details
   - Hanya log order_id, status, amount

---

## API Endpoints Reference

### Customer Endpoints:

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/payment/{booking}` | Tampilkan payment method selection |
| POST | `/payment/{booking}/process` | Create transaksi & generate snap token |
| GET | `/payment/{payment}/check-status` | Check status pembayaran (AJAX) |

### Webhook Endpoint (dari Midtrans):

| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/midtrans/callback` | Receive payment notifications |

---

## Example Controller Usage

### Simple Payment Flow:

```php
// Di PaymentController.php

public function process(Request $request, Booking $booking)
{
    // 1. Validate
    $validated = $request->validate([
        'payment_type' => 'required|in:full,partial',
    ]);

    // 2. Hitung amount (server-side)
    $amount = $validated['payment_type'] === 'full' 
        ? $booking->total_price 
        : $booking->total_price * 0.5;

    // 3. Call Midtrans Service
    $result = $this->midtrans->createTransaction(
        $booking->id,
        $amount,
        $validated['payment_type'],
        [
            'name'  => $booking->customer_name,
            'email' => 'customer@example.com',
            'phone' => $booking->phone,
        ]
    );

    // 4. Create payment record
    $payment = Payment::create([
        'booking_id'      => $booking->id,
        'order_id'        => $result['order_id'],
        'amount'          => $amount,
        'gross_amount'    => $amount,
        'payment_type'    => $validated['payment_type'],
        'payment_method'  => 'midtrans',
        'status'          => 'pending',
        'midtrans_response' => json_encode($result['response']),
    ]);

    // 5. Return snap payment page
    return view('payment.snap', [
        'booking'   => $booking,
        'payment'   => $payment,
        'snapToken' => $result['snap_token'],
        'clientKey' => $this->midtrans->getClientKey(),
    ]);
}
```

---

## Contact & Support

- **Midtrans Documentation**: https://docs.midtrans.com
- **Midtrans Support**: https://support.midtrans.com
- **Dashboard**: https://dashboard.midtrans.com
- **Status Page**: https://status.midtrans.com

---

## Changelog

### v1.0.0 (2026-03-28)
- ✅ Initial Midtrans integration
- ✅ Snap Payment Page support
- ✅ Webhook/callback handler
- ✅ Multiple payment methods support
- ✅ Auto status update
