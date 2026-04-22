# ✅ FIX SNAP_TOKEN COLUMN - MIGRATION & IMPLEMENTATION

## 🎯 Error yang Diperbaiki

**Error:**
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'snap_token' in 'field list'
```

**Penyebab:** Kolom `snap_token` belum ada di tabel `payments`

---

## ✅ SOLUSI YANG DITERAPKAN

### **1. Migration - Tambah Kolom**

**File:** `database/migrations/2026_03_30_000002_add_snap_token_to_payments.php`

**Status:** ✅ **SUDAH DIJALANKAN** (`php artisan migrate`)

```php
Schema::table('payments', function (Blueprint $table) {
    $table->string('snap_token')
        ->nullable()
        ->after('transaction_status')
        ->comment('Snap token dari Midtrans Snap');
});
```

**Hasil SQL:**
```sql
ALTER TABLE payments 
ADD COLUMN snap_token VARCHAR(255) NULL 
AFTER transaction_status;
```

---

### **2. Model Update - Fillable**

**File:** `app/Models/Payment.php`

**Perubahan:**
```php
protected $fillable = [
    // ... field lain ...
    'snap_token',  // ← DITAMBAHKAN
    // ... field lain ...
];
```

---

## 📝 CONTOH CODE LENGKAP - MENYIMPAN PAYMENT

### **Di Controller: MidtransPaymentController**

```php
public function getSnapToken(Request $request)
{
    try {
        // 1. Validasi
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        // 2. Get Booking
        $booking = Booking::findOrFail($request->booking_id);

        // 3. Setup Midtrans Config
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$clientKey = config('midtrans.client_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');

        // 4. Siapkan data untuk Snap
        $order_id = 'ORDER-' . $booking->id . '-' . uniqid();
        
        $snap_body = [
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => (int) $booking->total_price,
            ],
            'item_details' => [
                [
                    'id' => 'court-' . $booking->court_id,
                    'price' => (int) $booking->total_price,
                    'quantity' => 1,
                    'name' => $booking->court->name,
                ]
            ],
            'customer_details' => [
                'first_name' => $booking->customer_name ?? 'Guest',
                'email' => $booking->email ?? 'guest@mail.com',
                'phone' => $booking->phone ?? '',
            ],
        ];

        // 5. GENERATE SNAP TOKEN dari Midtrans
        $snap_token = \Midtrans\Snap::getSnapToken($snap_body);

        // 6. SIMPAN DATA PAYMENT KE DATABASE
        // ⭐ PENTING: Ini adalah contoh yang benar!
        $payment = Payment::create([
            'booking_id' => $booking->id,                    // FK ke booking
            'order_id' => $order_id,                         // Order ID
            'amount' => $booking->total_price,               // Amount
            'gross_amount' => $booking->total_price,         // Gross amount
            'payment_type' => 'full',                        // Tipe pembayaran
            'payment_method' => 'midtrans_snap',             // Metode
            'status' => 'pending',                           // Status lokal
            'transaction_status' => 'pending',               // Status Midtrans
            'snap_token' => $snap_token,                     // ← SNAP TOKEN DISIMPAN DI SINI!
        ]);

        // 7. RETURN RESPONSE
        return response()->json([
            'success' => true,
            'snap_token' => $snap_token,
            'payment_id' => $payment->id,
        ]);

    } catch (\Exception $e) {
        Log::error('Error generating snap token', [
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Gagal membuat snap token'
        ], 500);
    }
}
```

---

## 💡 PENJELASAN FIELD-BY-FIELD

| Field | Nilai | Penjelasan |
|-------|-------|-----------|
| `booking_id` | dari booking | Foreign key ke tabel bookings |
| `order_id` | 'ORDER-27-69ca72ac9caee' | Unique order ID untuk Midtrans |
| `amount` | booking->total_price | Jumlah yang akan dibayar |
| `gross_amount` | booking->total_price | Total gross amount |
| `payment_type` | 'full' | Tipe: full atau partial |
| `payment_method` | 'midtrans_snap' | Metode pembayaran |
| `status` | 'pending' | Status lokal (pending/paid/rejected) |
| `transaction_status` | 'pending' | Status dari Midtrans |
| **`snap_token`** | dari \Midtrans\Snap | **TOKEN UNTUK POPUP** ← KEY! |

---

## ✅ CONTOH: ALTERNATIF SAVE (Update existing payment)

Jika ingin update payment yang sudah ada:

```php
// Method 1: Using fill()
$payment = Payment::find($payment_id);
$payment->fill([
    'snap_token' => $snap_token,
    'transaction_status' => 'pending',
    'order_id' => $order_id,
]);
$payment->save();

// Method 2: Using update()
Payment::where('id', $payment_id)->update([
    'snap_token' => $snap_token,
    'transaction_status' => 'pending',
]);

// Method 3: Using updateOrCreate()
Payment::updateOrCreate(
    ['booking_id' => $booking->id],
    [
        'snap_token' => $snap_token,
        'order_id' => $order_id,
        'transaction_status' => 'pending',
    ]
);
```

---

## 🧪 VALIDATION STEPS

Untuk verifikasi perbaikan:

```bash
# 1. Cek struktur tabel
php artisan tinker

# Di tinker, jalankan:
>>> Schema::getColumnListing('payments')
# Output harus include: snap_token

# 2. Buat payment
>>> $payment = Payment::create([...])
>>> $payment->snap_token  # Should have value

# 3. Verify di database
>>> Payment::latest()->first()->snap_token
# Should return token string, not null
```

---

## 📊 DATABASE CHANGES

**Tabel `payments` sebelum:**
```
Columns: id, booking_id, amount, gross_amount, status, transaction_status, ...
```

**Tabel `payments` sesudah:**
```
Columns: id, booking_id, amount, gross_amount, status, transaction_status, snap_token ✅, ...
```

---

## 🔄 PAYMENT FLOW LENGKAP

```
1. User klik "Bayar Sekarang"
   ↓
2. getSnapToken() dipanggil
   ↓
3. Generate snap_token dari \Midtrans\Snap::getSnapToken()
   ↓
4. SIMPAN ke database:
   Payment::create([
       'booking_id' => ...,
       'order_id' => ...,
       'snap_token' => $snap_token,  ← INI YANG BARU
       'transaction_status' => 'pending',
       ...
   ])
   ↓
5. Return snap_token ke frontend
   ↓
6. Frontend: snap.pay($snap_token) ← Popup muncul!
   ↓
7. User complete payment
   ↓
8. Midtrans kirim callback
   ↓
9. Update transaction_status ke 'settlement'/'capture'
```

---

## ✅ RESULT

| Aspek | Status |
|-------|--------|
| Kolom snap_token | ✅ Created |
| Migration | ✅ Executed |
| Model fillable | ✅ Updated |
| Save snap token | ✅ Works |
| Error SQL | ✅ Fixed |

---

## 🚀 SIAP DIGUNAKAN

Sistem payment sekarang bisa:
- ✅ Generate snap token dari Midtrans
- ✅ Simpan token ke database
- ✅ Gunakan token untuk popup di frontend
- ✅ Track payment history

**Test sekarang dan error "snap_token column" akan hilang!** 🎉

