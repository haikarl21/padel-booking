# 🔐 Midtrans Configuration Setup

## Error: "Gagal membuat transaksi"

**Root Cause**: Midtrans keys di `.env` masih placeholder/dummy values

```
Current (.env):
MIDTRANS_SERVER_KEY=SB-Mid-server-ABC123XYZ789  ❌ DUMMY!
MIDTRANS_CLIENT_KEY=SB-Mid-client-ABC123XYZ789  ❌ DUMMY!
```

---

## ✅ Cara Get Real Midtrans Keys

### Step 1: Daftar di Midtrans Sandbox
1. Buka https://sandbox.midtrans.com
2. Klik **"Sign Up"**
3. Isi form registrasi
4. Verify email

### Step 2: Login ke Dashboard
1. Login dengan akun yang sudah dibuat
2. Go to **Settings → Access Keys**

### Step 3: Copy Keys
```
Sandbox Server Key:   SB-Mid-server-xxxxxxxxxxxxxxxx (panjang, jangan sampai terpotong)
Sandbox Client Key:   SB-Mid-client-xxxxxxxxxxxxxxxx (panjang, jangan sampai terpotong)
Merchant ID:          Your merchant ID (optional)
```

### Step 4: Update .env
```env
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SERVER_KEY=SB-Mid-server-[PASTE_YOUR_SERVER_KEY]
MIDTRANS_CLIENT_KEY=SB-Mid-client-[PASTE_YOUR_CLIENT_KEY]
MIDTRANS_MERCHANT_ID=your_merchant_id
```

⚠️ **IMPORTANT**: 
- Jangan share server key ke public/frontend
- Server key hanya dipake di backend
- Client key bisa di frontend

---

## 🧪 Test Midtrans Connection

Jalankan artisan tinker untuk test:

```bash
php artisan tinker
```

Lalu copy-paste ini:

```php
$service = new \App\Services\MidtransService();

$result = $service->createTransaction(
    bookingId: 1,
    amount: 100000,
    paymentType: 'full',
    customerData: [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '081234567890',
    ]
);

dd($result);
```

**Expected Output (Success)**:
```
array [
  "status" => "success"
  "snap_token" => "xxxxxxxxxxxx..."
  "order_id" => "BOOKING-1-1234567890"
  ...
]
```

**Expected Output (Error)**:
```
array [
  "status" => "error"
  "message" => "Gagal membuat transaksi Midtrans: ..."
  "error" => "[Error detail dari Midtrans]"
]
```

---

## 🐛 Common Errors & Solutions

### Error 1: "Invalid server key"
**Cause**: Server key salah atau belum di-update  
**Solution**: 
- Copy lagi dari dashboard Midtrans
- Pastikan formatnya `SB-Mid-server-xxxxx` (Sandbox)
- Clear cache: `php artisan config:clear`

### Error 2: "Unauthorized request"
**Cause**: Server key atau Client key expired  
**Solution**:
- Generate new keys di dashboard
- Update .env
- Clear config cache

### Error 3: "Invalid gross amount"
**Cause**: Amount < Rp 1.000  
**Solution**:
- Check booking total_price
- Minimum amount untuk Midtrans adalah Rp 1.000

### Error 4: "Network timeout"
**Cause**: Server tidak bisa connect ke Midtrans (internet issue)  
**Solution**:
- Check internet connection: `ping api.sandbox.midtrans.com`
- Check firewall/proxy setting
- Try dari public WiFi untuk test

---

## 📋 Verification Checklist

```
□ Sudah register di https://sandbox.midtrans.com
□ Sudah login ke dashboard Midtrans
□ Copy server key dari Access Keys
□ Copy client key dari Access Keys
□ Update .env dengan key yang benar
□ PHP artisan serve berjalan
□ Test payment menggunakan test card:
  - Card: 4811 1111 1111 1114
  - Exp: 12/25
  - CVV: 123
□ Transaksi berhasil di Snap
□ Webhook received di backend
```

---

## 🚀 Production Setup (Nanti)

Ketika mau go live:

```env
MIDTRANS_IS_PRODUCTION=true
MIDTRANS_SERVER_KEY=Mid-server-xxxxxxxxxxxxxxxx  (tanpa "SB-")
MIDTRANS_CLIENT_KEY=Mid-client-xxxxxxxxxxxxxxxx  (tanpa "SB-")
```

**Get production keys**:
1. Login dashboard Midtrans
2. Switch mode dari Sandbox → Production
3. Go to Settings → Access Keys
4. Copy production keys

---

## 📞 Support

- Midtrans Docs: https://docs.midtrans.com
- Sandbox URL: https://sandbox.midtrans.com
- Dashboard Support: support@midtrans.com
