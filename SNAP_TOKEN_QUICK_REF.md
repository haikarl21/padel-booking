# 🎯 QUICK REFERENCE - SNAP_TOKEN COLUMN

**Simple reference untuk menyimpan snap token**

---

## ⚡ SUDAH DILAKUKAN

### ✅ Step 1: Migration Created & Executed
```bash
File: 2026_03_30_000002_add_snap_token_to_payments.php
Status: DONE (php artisan migrate)
Kolom: snap_token (VARCHAR, nullable)
```

### ✅ Step 2: Model Updated
```php
// app/Models/Payment.php
protected $fillable = [
    // ...
    'snap_token',  // ← ADDED
    // ...
];
```

### ✅ Step 3: Controller Ready
```php
// app/Http/Controllers/MidtransPaymentController.php
$snap_token = \Midtrans\Snap::getSnapToken($snap_body);
$payment->snap_token = $snap_token;  // ← SAVED
$payment->save();
```

---

## 📝 CARA MENYIMPAN PAYMENT (3 METHOD)

### **METHOD 1: CREATE (Buat baru)**
```php
$payment = Payment::create([
    'booking_id' => $booking->id,
    'order_id' => $order_id,
    'amount' => $booking->total_price,
    'gross_amount' => $booking->total_price,
    'payment_type' => 'full',
    'payment_method' => 'midtrans_snap',
    'status' => 'pending',
    'transaction_status' => 'pending',
    'snap_token' => $snap_token,  // ← TOKEN DISIMPAN
]);

// Access token
echo $payment->snap_token;  // Output: token value
```

### **METHOD 2: FILL & SAVE (Update existing)**
```php
$payment = Payment::firstOrCreate(['booking_id' => $booking->id]);
$payment->fill([
    'order_id' => $order_id,
    'snap_token' => $snap_token,
    'transaction_status' => 'pending',
]);
$payment->save();
```

### **METHOD 3: UPDATE (Direct update)**
```php
Payment::where('booking_id', $booking->id)->update([
    'snap_token' => $snap_token,
    'transaction_status' => 'pending',
]);
```

---

## ✅ VALIDATION

```bash
php artisan tinker

# Check token sudah tersimpan:
>>> $payment = Payment::latest()->first()
>>> $payment->snap_token
# Output: "xxx..." (harus ada nilai, tidak null)
```

---

## 🚀 RESULT

| Item | Status |
|------|--------|
| Kolom | ✅ Created |
| Model | ✅ Updated |
| Error | ✅ Fixed |
| Save | ✅ Works |

**Error "Unknown column 'snap_token'" sudah hilang!** ✅

