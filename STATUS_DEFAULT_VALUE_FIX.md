# ✅ FIX STATUS DEFAULT VALUE - PAYMENT INSERTION ERROR

## 🎯 Error yang Diperbaiki

**Error:**
```
Field 'status' doesn't have a default value

INSERT INTO `payments` 
(`booking_id`, `order_id`, `amount`, `gross_amount`, `payment_type`, `payment_method`, 
`transaction_status`, `snap_token`, `updated_at`, `created_at`) 
VALUES (28, ORDER-28-69ca740820468, 80000.00, 80000.00, full, midtrans_snap, pending, ...)
```

**Root Cause:** Field `status` tidak diberi nilai saat INSERT dan juga tidak punya DEFAULT value di database

---

## ✅ SOLUSI YANG DITERAPKAN

### **1. Migration - Fix Status Default Value**

**File:** `database/migrations/2026_03_30_000003_fix_status_default_value.php`

**Status:** ✅ **SUDAH DIJALANKAN** (`php artisan migrate`)

```php
Schema::table('payments', function (Blueprint $table) {
    $table->string('status')
        ->default('pending')
        ->change();
});
```

**Hasil SQL:**
```sql
ALTER TABLE payments MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'pending';
```

---

### **2. Controller - Always Include Status**

**File:** `app/Http/Controllers/MidtransPaymentController.php` (Line 111)

**SEBELUM (ERROR):**
```php
$payment->payment_method = 'midtrans_snap';
$payment->transaction_status = 'pending';  // ← ada
// status tidak ada! ← PROBLEM!
$payment->snap_token = $snap_token;
$payment->save();
```

**SESUDAH (FIXED):**
```php
$payment->payment_method = 'midtrans_snap';
$payment->status = 'pending';              // ← DITAMBAHKAN!
$payment->transaction_status = 'pending';
$payment->snap_token = $snap_token;
$payment->save();
```

---

## 📝 PENJELASAN: STATUS vs TRANSACTION_STATUS

| Field | Sumber | Value | Tujuan |
|-------|--------|-------|--------|
| `status` | Local (Laravel) | pending, paid, rejected, expired | Track status pembayaran lokal |
| `transaction_status` | Midtrans | pending, capture, settlement, deny, cancel | Track status dari Midtrans |

**Contoh flow:**
```
1. Create payment → status='pending', transaction_status='pending'
2. User bayar di Midtrans
3. Midtrans callback → transaction_status berubah ke 'settlement'/'capture'
4. Controller update → status berubah ke 'paid'
```

---

## 💡 CONTOH CODE - MEMBUAT PAYMENT

### **Cara 1: Assign satu-satu (Seperti di controller sekarang)**

```php
$payment = new Payment();
$payment->booking_id = $booking->id;
$payment->order_id = $order_id;
$payment->amount = 80000;
$payment->gross_amount = 80000;
$payment->payment_type = 'full';
$payment->payment_method = 'midtrans_snap';
$payment->status = 'pending';              // ← PENTING!
$payment->transaction_status = 'pending';
$payment->snap_token = $snap_token;
$payment->save();
```

### **Cara 2: Gunakan create() method**

```php
$payment = Payment::create([
    'booking_id' => $booking->id,
    'order_id' => $order_id,
    'amount' => 80000,
    'gross_amount' => 80000,
    'payment_type' => 'full',
    'payment_method' => 'midtrans_snap',
    'status' => 'pending',                 // ← PENTING!
    'transaction_status' => 'pending',
    'snap_token' => $snap_token,
]);
```

### **Cara 3: Update existing payment**

```php
$payment = Payment::find($id);
$payment->update([
    'status' => 'pending',                 // ← PENTING!
    'transaction_status' => 'pending',
    'snap_token' => $snap_token,
]);
```

---

## 🔄 PAYMENT CREATION FLOW (COMPLETE)

```
1. getSnapToken(Request) dipanggil
   ↓
2. Validasi booking
   ↓
3. Setup Midtrans config
   ↓
4. Generate snap token dari Midtrans
   ↓
5. CREATE payment dengan:
   - status = 'pending'                    ← WAJIB!
   - transaction_status = 'pending'
   - snap_token = dari Midtrans
   - order_id, amount, dll
   ↓
6. Save ke database ✅ (tidak error lagi!)
   ↓
7. Return snap_token ke frontend
   ↓
8. Frontend tampilkan Snap popup
```

---

## 🧪 VERIFICATION

```bash
# Test di tinker
php artisan tinker

>>> $payment = Payment::latest()->first()
>>> $payment->status           # Should: 'pending'
>>> $payment->transaction_status  # Should: 'pending'
>>> $payment->snap_token       # Should: token value
>>> $payment->booking_id       # Should: booking ID
```

---

## ✅ RESULT

| Aspek | Status |
|-------|--------|
| Migration | ✅ Executed |
| Default value | ✅ Set to 'pending' |
| Controller | ✅ Updated |
| Status field | ✅ Always included |
| Error SQL | ✅ Fixed |
| No errors | ✅ YES |

---

## 🔍 TROUBLESHOOTING

**Jika masih error:**

1. Verify migration berjalan:
```bash
php artisan migrate:status
# Ceklis 2026_03_30_000003_fix_status_default_value harus ✅
```

2. Check kolom status di database:
```bash
php artisan tinker
>>> Schema::getColumnListing('payments')
# 'status' harus ada dengan default 'pending'
```

3. Clear config cache:
```bash
php artisan config:clear
php artisan cache:clear
```

---

## 🚀 SIAP DIGUNAKAN

Payment system sekarang bisa:
- ✅ Create payment dengan status field
- ✅ Database punya default value sebagai safety net
- ✅ No "doesn't have a default value" error
- ✅ Payment insertion berjalan lancar

**Test sekarang dengan klik "Bayar Sekarang" - error akan hilang!** 🎉

