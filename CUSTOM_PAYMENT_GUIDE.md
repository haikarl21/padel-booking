# 💳 Panduan Sistem Pembayaran Custom

Dokumentasi lengkap untuk implementasi **Custom Payment System** (tanpa payment gateway) pada aplikasi Padel Booking.

---

## 📋 Daftar Isi

1. [Overview Sistem](#overview-sistem)
2. [Flow Pembayaran](#flow-pembayaran)
3. [Fitur Utama](#fitur-utama)
4. [Struktur Database](#struktur-database)
5. [Struktur File](#struktur-file)
6. [Implementasi Detail](#implementasi-detail)
7. [Testing & Troubleshooting](#testing--troubleshooting)
8. [Keamanan & Best Practices](#keamanan--best-practices)

---

## 🎯 Overview Sistem

Sistem pembayaran custom yang menggantikan payment gateway (Midtrans). Konsep utama:

- **Kode Unik 3 Digit**: Setiap transaksi mendapat kode unik (100-999) untuk verifikasi
- **Nominal Unik**: Total pembayaran = Harga Booking + Kode Unik (misal: 120327)
- **Manual Verification**: Admin menge-cek bukti transfer dan memvalidasi nominal yang diterima
- **Expired Tracking**: Pembayaran otomatis expired setelah 30 menit
- **File Upload**: User upload bukti transfer (JPG/PNG, max 5MB)

**Keuntungan**:
✅ Tidak perlu payment gateway (no fee)  
✅ Full kontrol terhadap proses pembayaran  
✅ Verifikasi manual lebih aman dari nominal unik  
✅ Tidak bergantung API pihak ketiga  

---

## 🔄 Flow Pembayaran

### User Side

```
1. User melakukan booking → data tersimpan dengan status "pending"
2. User klik "Lanjut ke Pembayaran"
3. Sistem generate:
   - Kode unik 3 digit (misal: 327)
   - Total pembayaran = Harga + Kode (misal: 120000 + 327 = 120327)
   - Expired time: 30 menit dari sekarang
4. User lihat:
   - Nomor rekening tujuan
   - Nominal pembayaran unik
   - Countdown timer (realtime)
5. User transfer ke nomor rekening dengan nominal unik
6. User upload bukti (screenshot transfer)
7. Sistem show: "Menunggu verifikasi admin"
```

### Admin Side

```
1. Admin buka halaman "Manajemen Pembayaran"
2. Lihat daftar pembayaran pending + waktu expired
3. Klik "Detail" pada pembayaran
4. Admin cek:
   - Gambar bukti transfer
   - Nominal yang diterima (harus = nominal unik)
   - Tanggal/waktu transfer
5. Admin approve → Payment status = "paid", Booking status = "approved"
   ATAU
   Admin reject dengan alasan penolakan
6. Jika reject, user bisa upload bukti baru
7. Jika expired (lampau 30 menit), sistem automatis ubah status → "expired"
```

---

## ✨ Fitur Utama

### 1. Generate Kode Unik
```php
// PaymentCustomService::generateUniqueCode()
// Random 3 digit: 100 - 999
$uniqueCode = rand(100, 999);
```

### 2. Hitung Total Pembayaran
```php
$amount = 120000;           // Harga booking
$uniqueCode = 327;          // Kode unik
$totalUnique = $amount + $uniqueCode; // 120327
```

### 3. Set Expiration Time
```php
$expiredAt = now()->addMinutes(30); // 30 menit dari sekarang
```

### 4. Check Expiration Status
```php
if ($payment->expired_at && now()->isAfter($payment->expired_at)) {
    $payment->status = 'expired';
    $payment->save();
}
```

### 5. Upload & Validate File
```php
// Validasi:
// - File type: JPG, PNG only
// - File size: max 5MB
// - Prevent double upload
// - Safe filename: payment_{id}_{timestamp}_{hash}.{ext}
```

### 6. Admin Approval/Rejection
```php
// Approve:
$payment->status = 'paid';
$payment->approved_by = $adminId;
$payment->paid_at = now();
$payment->booking->status = 'approved';

// Reject:
$payment->status = 'rejected';
$payment->rejection_reason = 'Nominal tidak sesuai';
$payment->approved_by = $adminId;
$payment->booking->status = 'pending'; // Reset untuk re-payment
```

---

## 🗄️ Struktur Database

### Payments Table (Updated)

```sql
-- Original Columns
id                   INT PRIMARY KEY
booking_id           FOREIGN KEY → bookings.id
amount               DECIMAL(10,2)
payment_type         VARCHAR (full/partial)
payment_method       VARCHAR (bank_transfer)
status               VARCHAR (pending/paid/rejected/expired)
created_at           TIMESTAMP
updated_at           TIMESTAMP

-- Custom Payment Columns (New)
unique_code          VARCHAR(3)           -- Kode unik 3 digit
total_unique         DECIMAL(10,2)        -- amount + unique_code
expired_at           TIMESTAMP            -- Waktu expired
proof_file           VARCHAR              -- Nama file bukti (hashed)
approved_by          FOREIGN KEY → users.id (NULLABLE)
rejection_reason     TEXT (NULLABLE)      -- Alasan reject

-- Deprecated Columns (dari Midtrans)
order_id             VARCHAR (NULLABLE)
transaction_id       VARCHAR (NULLABLE)
gross_amount         DECIMAL (NULLABLE)
midtrans_response    JSON (NULLABLE)
midtrans_signature   VARCHAR (NULLABLE)
paid_at              TIMESTAMP (NULLABLE)
```

### Users Table (Updated)
```sql
-- New Column
is_admin             BOOLEAN DEFAULT false  -- Admin akses kontrol
```

---

## 📁 Struktur File

### Backend (PHP/Laravel)

```
app/
├── Services/
│   └── PaymentCustomService.php          # Business logic payment custom
├── Http/Controllers/
│   └── PaymentController.php              # Handle payment actions (show, upload, approve, reject)
├── Models/
│   ├── Payment.php                        # Payment model (methods isExpired, isApproved)
│   ├── Booking.php                        # (relations updated)
│   └── User.php                           # (is_admin fillable)
│
database/
├── migrations/
│   ├── 2026_03_28_000003_update_payments_table_custom_payment.php
│   └── 2026_03_28_000004_add_is_admin_to_users_table.php
│
routes/
└── web.php                                # Payment routes (show, upload-proof, approve, reject)

resources/views/
├── booking/
│   └── payment-detail.blade.php           # User payment page (countdown timer, upload form)
└── admin/payments/
    ├── index.blade.php                    # Admin payment list (pending + history)
    └── detail.blade.php                   # Admin payment detail (approve/reject form)

storage/app/
└── payments/                              # Direktori untuk upload bukti (JPG/PNG)
```

### Key Classes & Methods

#### PaymentCustomService

```php
class PaymentCustomService {
    // Generators & Utilities
    generatePayment(Booking $booking, string $paymentType): Payment
    generateUniqueCode(): int
    getPaymentDisplay(Payment $payment): array
    getBankAccount(): array
    
    // Validation & Status
    checkExpiration(Payment $payment): bool
    getTimeRemaining(Payment $payment): int
    uploadProof(Payment $payment, $file): string|bool
    
    // Admin Actions
    approvePayment(Payment $payment, int $adminId): bool
    rejectPayment(Payment $payment, string $reason, int $adminId): bool
    
    // Admin Dashboard
    getPendingPayments(): Collection
}
```

#### PaymentController

```php
class PaymentController {
    // User Actions
    show(Booking $booking): View
    getStatus(Payment $payment): JsonResponse
    uploadProof(Request $request, Payment $payment): RedirectResponse
    downloadProof(Payment $payment): StreamedResponse
    
    // Admin Actions
    approve(Request $request, Payment $payment): RedirectResponse
    reject(Request $request, Payment $payment): RedirectResponse
    viewPaymentDetail(Payment $payment): View
    
    // Admin Dashboard
    listPayments(): View
}
```

#### Payment Model

```php
class Payment extends Model {
    // Helper Methods
    isExpired(): bool              // Cek apakah sudah expired
    isApproved(): bool             // Cek apakah sudah di-approve
    isPending(): bool              // Status pending
    isFailed(): bool               // Status failed/expired
    
    // Relations
    booking(): BelongsTo
    approver(): BelongsTo          // Admin yang approve (relasi ke User)
}
```

---

## 🔧 Implementasi Detail

### 1. Mengakses Payment Detail User

**Route**: `GET /payment/{booking}`  
**Controller**: `PaymentController@show`

```php
// Cek apakah sudah ada payment
$payment = $booking->payments()->latest()->first();

// Jika belum, generate baru
if (!$payment) {
    $payment = $this->customPaymentService->generatePayment($booking, 'full');
}

// Check expired status
$this->customPaymentService->checkExpiration($payment);

// Get display data
$displayData = $this->customPaymentService->getPaymentDisplay($payment);

// Return view dengan:
// - Bank account info
// - Nominal pembayaran unik
// - Countdown timer (30 menit)
// - Form upload bukti
```

**View Display**:
- Bank Name: "Bank Central Asia"
- Account: "1234567890"
- Nominal: "Rp 120327" (amount + unique code)
- Expired: "00:29:45" (countdown realtime)
- Upload button: JPG/PNG, max 5MB

### 2. Upload Bukti Transfer

**Route**: `POST /payment/{payment}/upload-proof`  
**Controller**: `PaymentController@uploadProof`

```php
// Validasi
$request->validate([
    'proof_file' => 'required|file|mimes:jpg,jpeg,png|max:5120',
]);

// Check payment status (harus pending)
if ($payment->status !== 'pending') {
    return error('Pembayaran tidak dapat diubah lagi');
}

// Check expiration
if ($this->customPaymentService->checkExpiration($payment)) {
    return error('Waktu pembayaran sudah habis');
}

// Upload ke storage/app/payments/
$filename = $this->customPaymentService->uploadProof($payment, $request->file('proof_file'));

// Format filename: payment_123_1711612800_a1b2c3d4e5f6g7h8.jpg
// (safe against path traversal attacks)
```

**Security**:
- ✅ File type validation (JPG/PNG only)
- ✅ File size check (max 5MB)
- ✅ Secure filename (hash + timestamp)
- ✅ Prevent double upload
- ✅ Check status & expiration sebelum terima

### 3. Admin Approve Payment

**Route**: `POST /payment/{payment}/approve`  
**Middleware**: `auth`, `admin`  
**Controller**: `PaymentController@approve`

```php
// Service method
$result = $this->customPaymentService->approvePayment($payment, auth()->id());

// Update payment
$payment->status = 'paid';
$payment->approved_by = $adminId;
$payment->paid_at = now();

// Update booking
$payment->booking->status = 'approved';

// Logging
Log::info('Payment approved', [
    'payment_id' => $payment->id,
    'admin_id' => auth()->id()
]);
```

**Validation Checklist** (di interface admin):
- ✓ Apakah nominal yang diterima = Nominal unik?
- ✓ Apakah tanggal transfer sesuai?
- ✓ Apakah bukti transfer jelas & terverifikasi?

### 4. Admin Reject Payment

**Route**: `POST /payment/{payment}/reject`  
**Middleware**: `auth`, `admin`  
**Controller**: `PaymentController@reject`

```php
// Validasi reason
$request->validate([
    'reason' => 'required|string|min:10|max:500',
]);

// Service method
$result = $this->customPaymentService->rejectPayment(
    $payment,
    $request->input('reason'),
    auth()->id()
);

// Update payment
$payment->status = 'rejected';
$payment->rejection_reason = 'Nominal tidak sesuai...';
$payment->approved_by = $adminId;

// Reset booking status
$payment->booking->status = 'pending';

// User bisa upload bukti baru atau re-do pembayaran
```

**Alasan Reject Umum**:
- Nominal tidak sesuai
- Tanggal/waktu transfer tidak sesuai
- Bukti tidak jelas atau terlihat palsu
- Rekening tujuan berbeda

### 5. Auto Check Expired (30 Menit)

**On Page Load**:
```javascript
// Di payment-detail.blade.php
// JavaScript countdown timer
setInterval(() => {
    const remaining = expiredAt - now();
    if (remaining <= 0) {
        // Auto refresh halaman
        location.reload();
    }
}, 1000); // Update setiap 1 detik

// Auto check status setiap 5 detik
setInterval(() => {
    fetch('/payment/{payment}/status')
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'pending') {
                location.reload(); // Reload jika status berubah
            }
        });
}, 5000);
```

**On Admin Access** (Payment Detail):
```php
// PaymentController::viewPaymentDetail()
$this->customPaymentService->checkExpiration($payment);
$payment->refresh(); // Load latest from DB
```

**Via Cron/Job** (Optional - untuk production):
```php
// app/Console/Commands/CheckPaymentExpiration.php
protected function handle() {
    $expiredPayments = Payment::where('status', 'pending')
        ->where('expired_at', '<', now())
        ->get();
    
    foreach ($expiredPayments as $payment) {
        $payment->update(['status' => 'expired']);
    }
}
```

---

## 🧪 Testing & Troubleshooting

### Test Scenario 1: Normal Flow (Approve)

```
1. User booking → status "pending"
2. Route: GET /payment/{booking}
   - Lihat nominal pembayaran unik (misal: 120327)
   - Lihat countdown timer (30:00 → 0:00)
3. User transfer Rp 120327 ke nomor rekening
4. User upload bukti (screenshot transfer)
5. Route: POST /payment/{payment}/upload-proof
   - File tersimpan di storage/app/payments/
   - status masih "pending"
6. Admin Route: GET /admin/payments
   - Lihat pembayaran pending di list
7. Admin Route: GET /admin/payment/{payment}/detail
   - Lihat bukti transfer (image preview)
   - Klik tombol "Approve Pembayaran"
8. Route: POST /payment/{payment}/approve
   - payment.status → "paid"
   - booking.status → "approved"
   - User terima notifikasi pembayaran approved ✓
```

### Test Scenario 2: Reject Flow

```
1. User upload bukti (nominal salah: 120000 bukan 120327)
2. Admin lihat bukti di detail page
3. Admin klik "Reject Pembayaran"
4. Admin input alasan: "Nominal tidak sesuai. Seharusnya Rp 120327"
5. Route: POST /payment/{payment}/reject
   - payment.status → "rejected"
   - payment.rejection_reason → "Nominal tidak sesuai..."
   - booking.status → "pending" (reset)
6. User buka halaman payment lagi
   - Lihat alert "Pembayaran Ditolak"
   - Lihat alasan reject
   - Bisa upload bukti baru ✓
```

### Test Scenario 3: Expired Flow

```
1. User booking (payment dibuat, expired_at = now + 30 menit)
2. User tidak upload bukti sampai melebihi 30 menit
3. JavaScript countdown timer update otomatis
4. Ketika waktu habis: "00:00"
5. Auto-refresh page
6. Route: GET /payment/{booking}
   - Service check: checkExpiration() = true
   - payment.status → "expired"
   - View show: "Waktu Pembayaran Habis"
   - Disable upload button
   - Show alert: "Silakan hubungi admin untuk buat pembayaran baru"
```

### Common Issues & Solutions

#### Issue 1: Countdown Timer Tidak Update

**Penyebab**: JavaScript error atau file cache  
**Solusi**:
```bash
php artisan view:clear
php artisan config:clear
# Check browser console untuk JS error
```

#### Issue 2: File Upload Gagal (Max File Size)

**Penyebab**: File terlalu besar atau config PHP terlalu kecil  
**Solusi**:
```php
// php.ini atau .env
upload_max_filesize = 10M
post_max_size = 10M

// atau di .env
UPLOAD_MAX_SIZE=10M
```

#### Issue 3: Admin Tidak Bisa Approve/Reject

**Penyebab**: User tidak punya `is_admin = 1`  
**Solusi**:
```bash
# Database update
UPDATE users SET is_admin = 1 WHERE id = 1;

# Or via PHP
$user->update(['is_admin' => true]);
$user->save();
```

#### Issue 4: File Bukti Tidak Bisa Di-download

**Penyebab**: Storage folder permission atau config issue  
**Solusi**:
```bash
# Set permission
chmod -R 755 storage/app/payments/

# Create symlink
php artisan make:storage-link
```

---

## 🔒 Keamanan & Best Practices

### 1. Input Validation & Sanitization

```php
// Validate file upload
$request->validate([
    'proof_file' => 'required|file|mimes:jpg,jpeg,png|max:5120',
]);

// Sanitize text input
$reason = strip_tags($input);          // Hapus HTML tags
$reason = htmlspecialchars($reason);   // Escape special chars
$reason = trim($reason);               // Hapus whitespace
```

### 2. Authorization & Authentication

```php
// Check admin akses
if (!auth()->user() || !auth()->user()->is_admin) {
    abort(403, 'Unauthorized');
}

// Use middleware di routes
Route::post('/payment/{payment}/approve', [...])
    ->middleware(['auth', 'admin']);
```

### 3. Secure File Handling

```php
// Safe filename (prevent path traversal)
$filename = "payment_{$payment->id}_{time()}_{md5hash}.jpg";

// Store di proper directory
Storage::disk('local')->put('payments/' . $filename, $content);

// Download với proper headers
return Storage::disk('local')->download('payments/' . $filename);
```

### 4. Prevent Double Payment

```php
// Check existing pending payment
$existingPayment = $booking->payments()
    ->where('status', 'pending')
    ->first();

if ($existingPayment) {
    return error('Pembayaran sudah ada, gunakan yang lama');
}
```

### 5. Prevent Expired Payment Modification

```php
// Check status & expiration sebelum upload
if ($payment->status !== 'pending') {
    return error('Pembayaran tidak dapat diubah');
}

if ($this->checkExpiration($payment)) {
    return error('Pembayaran sudah expired');
}
```

### 6. Logging & Audit Trail

```php
// Log semua aksi penting
Log::info('Payment uploaded', [
    'payment_id' => $payment->id,
    'user_id' => auth()->id(),
    'file' => $filename,
]);

Log::info('Payment approved', [
    'payment_id' => $payment->id,
    'admin_id' => auth()->id(),
    'booking_id' => $payment->booking_id,
]);
```

### 7. Database Constraints

```sql
-- Foreign key constraints
ALTER TABLE payments 
ADD CONSTRAINT fk_payments_bookings 
FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE;

-- Unique constraints (prevent duplicate active payments)
ALTER TABLE payments 
ADD CONSTRAINT unique_active_payment_per_booking 
UNIQUE (booking_id, status) 
WHERE status = 'pending';

-- Check constraints
ALTER TABLE payments 
ADD CONSTRAINT check_valid_status 
CHECK (status IN ('pending', 'paid', 'rejected', 'expired'));
```

### 8. Password & API Key Security

```php
// Bank account info di PaymentCustomService const
const BANK_ACCOUNT = [
    'bank_name' => 'Bank Central Asia',
    'account_number' => '1234567890',  // CATAT: Ganti dengan nomor real
];

// JANGAN simpan di .env (terlalu public)
// Lebih baik simpan di database encrypted atau config protected
```

---

## 📊 Monitoring & Analytics

### Payment Status Distribution

```sql
SELECT 
    status,
    COUNT(*) as total,
    ROUND(AVG(amount), 2) as avg_amount
FROM payments
GROUP BY status;

-- Expected output:
-- pending   | 5  | 125000.00
-- paid      | 45 | 130000.00
-- rejected  | 3  | 120000.00
-- expired   | 2  | $125000.00
```

### Payment Processing Time

```sql
SELECT 
    payment_id,
    booking_code,
    customer_name,
    TIMEDIFF(paid_at, created_at) as processing_time
FROM payments
WHERE status = 'paid'
ORDER BY processing_time DESC
LIMIT 10;
```

### Admin Approval Rate

```sql
SELECT 
    approver_id,
    approver_name,
    COUNT(*) as total_approved,
    AVG(TIMEDIFF(paid_at, created_at)) as avg_approval_time
FROM payments
WHERE status = 'paid'
GROUP BY approver_id;
```

---

## 🎓 Integration Checklist

Untuk production deployment:

- [ ] Schema sudah di-migrate (is_admin, payment columns)
- [ ] PaymentCustomService sudah di-register di service provider
- [ ] Routes sudah di-setup (payment.show, upload-proof, approve, reject)
- [ ] Views sudah di-buat (payment-detail, admin list, admin detail)
- [ ] Storage/payments folder sudah di-create
- [ ] Admin user sudah punya is_admin = 1
- [ ] File upload permission sudah di-setup (chmod 755)
- [ ] Countdown timer JS sudah berfungsi
- [ ] Database backup sebelum migrate
- [ ] Test semua 3 scenario (Approve, Reject, Expired)
- [ ] Setup error logging (Laravel logs)
- [ ] Setup email notification (optional, untuk admin)

---

## 📞 Support & Documentation

Untuk bantuan lebih lanjut:

1. **Cek Laravel Documentation**: https://laravel.com/docs
2. **Cek Service File**: `app/Services/PaymentCustomService.php`
3. **Cek Controller**: `app/Http/Controllers/PaymentController.php`
4. **Cek View Source**: `resources/views/booking/payment-detail.blade.php`

---

**Terakhir Updated**: March 28, 2026  
**Laravel Version**: 10+  
**PHP Version**: 8.2+

