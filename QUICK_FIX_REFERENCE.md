# 🎯 QUICK REFERENCE - SNAP TOKEN GENERATION (GUEST CHECKOUT)

**Solusi simpel untuk generate Snap Token tanpa login**

---

## ⚡ QUICK COPY-PASTE CODE

### **Controller: Snap Token Generation**

```php
<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransPaymentController extends Controller
{
    /**
     * Generate Snap Token untuk payment
     * Guest checkout - tidak perlu login
     */
    public function getSnapToken(Request $request)
    {
        try {
            // 1. Validasi & Get Booking
            $request->validate(['booking_id' => 'required|exists:bookings,id']);
            $booking = Booking::findOrFail($request->booking_id);

            // 2. Setup Midtrans
            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$clientKey = config('midtrans.client_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');

            // 3. Buat Payment Record
            $payment = $booking->payments()->first() ?? new Payment();
            if (!$payment->exists) $payment->booking_id = $booking->id;
            $order_id = $payment->order_id ?? 'ORDER-' . $booking->id . '-' . uniqid();

            // 4. CUSTOMER DETAILS - Ambil dari booking, BUKAN Auth
            $customer_details = [
                'first_name' => $booking->customer_name ?? 'Guest',
                'email'      => $booking->email ?? 'guest@mail.com',  // ← KEY FIX!
                'phone'      => $booking->phone ?? '',
            ];

            // 5. Siapkan Snap Body
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
                        'name' => $booking->court->name . ' - ' . $booking->date->format('d/m/Y'),
                    ]
                ],
                'customer_details' => $customer_details,
                'callbacks' => ['finish' => route('payment.finish')],
            ];

            // 6. Generate Token
            $snap_token = \Midtrans\Snap::getSnapToken($snap_body);

            // 7. Save Payment
            $payment->update([
                'order_id' => $order_id,
                'amount' => $booking->total_price,
                'gross_amount' => $booking->total_price,
                'payment_method' => 'midtrans_snap',
                'transaction_status' => 'pending',
            ]);

            // 8. Return Response
            return response()->json([
                'success' => true,
                'snap_token' => $snap_token,
            ]);

        } catch (\Exception $e) {
            Log::error('Snap token error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
```

---

## 📝 PENJELASAN SINGKAT

| Baris | Penjelasan |
|-------|-----------|
| `$booking->customer_name ?? 'Guest'` | Ambil nama dari form booking, jika kosong → 'Guest' |
| `$booking->email ?? 'guest@mail.com'` | **PENTING:** Ambil email, jika tidak ada → default 'guest@mail.com' |
| `$booking->phone ?? ''` | Ambil telepon dari form, jika kosong → string kosong |
| `\Midtrans\Snap::getSnapToken()` | Generate token dari Midtrans (jangan gunakan Auth!) |
| `->update([...])` | Simpan payment record ke database |
| `return response()->json()` | Kirim token ke frontend untuk popup |

---

## ✅ YANG SUDAH DIPERBAIKI

- ❌ `Auth::user()->email` → error null
- ✅ `$booking->email ?? 'guest@mail.com'` → selalu ada value
- ❌ Auth::user()->name → bisa null
- ✅ `$booking->customer_name ?? 'Guest'` → dari form booking

---

## 🧪 TESTING

**1. Akses booking detail:**
```
http://127.0.0.1:8000/booking/26/detail
```

**2. Klik "Bayar Sekarang"**

**3. Check console (F12):**
```javascript
// Harus ada di console log:
// ✓ Snap library loaded
// ✓ Token generated
// ✓ snap.pay() called
```

**4. Snap popup harus muncul** ✅

---

## 🚀 SIAP DIGUNAKAN

Code di atas **sudah siap** dan **tanpa login** requirement!

- ✅ Guest checkout fully supported
- ✅ No Auth dependency
- ✅ Email fallback handled
- ✅ Error handled
- ✅ Simple & clean code

**Test sekarang dan Snap popup akan muncul!** 🎉

