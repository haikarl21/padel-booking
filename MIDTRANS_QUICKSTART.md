# 🚀 MIDTRANS SETUP - QUICK START (5 MENIT)

## Step 1: Setup Environment Variables (1 menit)

Edit `.env`:
```env
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SERVER_KEY=SB-Mid-server-YOUR_KEY
MIDTRANS_CLIENT_KEY=SB-Mid-client-YOUR_KEY
MIDTRANS_MERCHANT_ID=your_merchant_id
```

Get your keys dari: https://dashboard.midtrans.com → Settings → Access Keys

## Step 2: Run Migration (1 menit)

```bash
php artisan migrate
```

## Step 3: Setup Webhook di Midtrans Dashboard (1 menit)

1. Login: https://dashboard.midtrans.com
2. Settings → Configuration
3. Notification URL: `https://yourapp.com/midtrans/callback`
4. Save

## Step 4: Test Payment Flow (2 menit)

1. Go to: `http://localhost:8000/booking/{courtId}`
2. Create booking & pilih "Selesaikan Pembayaran"
3. Select "Pembayaran Penuh"
4. Click "Lanjut ke Pembayaran"
5. Click "Bayar Sekarang" → Snap popup terbuka
6. Test dengan kartu: `5111111111111117` / 12/25 / 123
7. Check database - payment status should be `settlement`

## ✅ Done! 

Sistem pembayaran Anda sudah siap. Untuk production, ubah:
- `MIDTRANS_IS_PRODUCTION=true`
- Gunakan Production keys (bukan Sandbox)

---

## Arti Status Pembayaran:

| Status | Artinya |
|--------|---------|
| `pending` | Belum dibayar |
| `settlement` | ✅ Pembayaran berhasil, booking approved |
| `expired` | Waktu pembayaran habis |
| `failed` | ❌ Pembayaran gagal |

---

## Troubleshooting Cepat:

| Error | Solusi |
|-------|--------|
| Snap blank | Check Client Key di `.env` |
| Webhook tidak diterima | Pastikan Notification URL tersave di dashboard Midtrans |
| Invalid signature | Server Key harus sama di `.env` dan Midtrans dashboard |

---

Untuk detail lengkap, baca: `MIDTRANS_INTEGRATION.md`
