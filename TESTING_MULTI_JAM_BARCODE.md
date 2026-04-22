# TESTING GUIDE - Multi-Jam & Barcode Implementation

## 🚀 Langkah Persiapan

### 1. Jalankan Migration
```bash
php artisan migrate
```

Ini akan menambahkan kolom ke tabel bookings:
- `duration_hours`
- `start_time`  
- `email`

### 2. Clear Cache (Optional tapi recommended)
```bash
php artisan cache:clear
php artisan config:clear
```

---

## ✅ Test Cases

### TEST 1: Pemilihan Multi-Jam Dengan Kalkulasi Harga

**Steps:**
1. Navigate ke `/booking/{court_id}` atau pilih lapangan → pilih tanggal
2. Di halaman "Pilih Slot Waktu" observasi:
   - [ ] Label berubah ke "Pilih Waktu Mulai" (bukan "Slot Waktu Tersedia")
   - [ ] Ada dropdown "Durasi Bermain (Jam)" dengan opsi 1-8 jam
   - [ ] Setiap opsi menampilkan: "X Jam - Rp XXXXX.XXX"

**Expected:** Harga per jam × durasi = total yang ditampilkan

| Durasi | Total Harga |
|--------|-------------|
| 1 Jam  | Rp 200.000 (jika price_per_hour = 200.000) |
| 2 Jam  | Rp 400.000  |
| 3 Jam  | Rp 600.000  |

---

### TEST 2: Dynamic Price Calculation

**Steps:**
1. Di halaman select-datetime
2. Pilih time slot (misal: 10:00-11:00)
3. Pilih durasi (misal: 2 jam)
4. Observasi: "Total Harga: Rp 400.000" ditampilkan

**Expected:**
- [ ] Total harga update real-time saat durasi berubah
- [ ] Tombol "Konfirmasi Booking" enabled setelah memilih waktu + durasi
- [ ] Tombol disabled jika belum memilih salah satunya

---

### TEST 3: Form Validasi & Data Penyimpanan

**Steps:**
1. Isi form:
   - Pilih waktu: 10:00-11:00
   - Durasi: 3 jam
   - Nama: "Budi"
   - Telepon: "081234567890"
   - Email: "budi@example.com" (optional)
2. Click "Konfirmasi Booking"
3. Observasi halaman Detail Booking

**Expected:**
- [ ] Tidak ada **Barcode** ditampilkan (belum bayar)
- [ ] "Durasi" menampilkan: "3 Jam"
- [ ] "Waktu" menampilkan: "10:00" atau jam mulai
- [ ] Harga total: 3 × price_per_hour
- [ ] Status: "pending" atau "warning"

---

### TEST 4: Detail Booking (Sebelum Pembayaran)

**Steps:**
1. Dari halaman detail booking (sebelum bayar)
2. Observasi bagian informasi

**Harus:**
- [ ] ❌ **TIDAK ada** "Barcode Pemesanan" di info
- [ ] ✅ Ada "Detail Booking" dengan waktu dan durasi
- [ ] ✅ Ada tombol "Bayar Sekarang"
- [ ] ✅ Status badge "pending" (warning warna)

---

### TEST 5: Detail Booking (Setelah Pembayaran Berhasil)

**Steps:**
1. Complete pembayaran (gunakan custom payment atau Midtrans)
2. Status payment berubah ke "completed"
3. Refresh halaman detail booking atau cek booking detail

**Expected:**
- [ ] ✅ Now "Barcode Pemesanan" VISIBLE dengan border hijau
- [ ] ✅ Border + text berwarna hijau (#28a745, #90EE90)
- [ ] ✅ Barcode = booking_code (misal: "BKG-ABC12XYZ")
- [ ] ✅ Ada tombol copy (  ) berfungsi
- [ ] ✅ Status badge "approved" (success warna)

---

### TEST 6: Barcode Visibility Logic

**Verify kondisi:**

```
SEBELUM BAYAR:
├─ Payment null → Barcode HIDDEN ✅
├─ Payment status = 'pending' → Barcode HIDDEN ✅
├─ Payment status = 'partial' → Barcode HIDDEN ✅

SETELAH BAYAR:
└─ Payment status = 'completed' → Barcode SHOWN ✅
```

---

### TEST 7: Track Booking dengan Barcode

**Steps:**
1. Navigate ke `/track-booking`
2. Observasi form

**Expected:**
- [ ] ✅ Label: "Barcode Pemesanan" (bukan "Nomor Referensi")
- [ ] ✅ Placeholder: "Pindai atau masukkan barcode"
- [ ] ✅ Button: "Lacak Booking" (bukan "Lihat Booking")
- [ ] ✅ Input autofocus (siap untuk pindai barcode)

3. Input barcode atau pindai barcode dari booking yang sebelumnya dibuat
4. Click "Lacak Booking"

**Expected:**
- [ ] Redirect ke halaman detail booking yang benar
- [ ] Barcode ditampilkan jika pembayaran sudah completed

---

### TEST 8: Barcode Scanner Simulation

**Steps (untuk test barcode scanning):**
1. Buka `/track-booking`
2. Gunakan barcode scanner atau input manual
3. Input: "BKG-ABC12XYZ" (dari booking code)

**Expected:**
- [ ] ✅ Sistem menemukan booking
- [ ] ✅ Redirect ke detail booking
- [ ] ✅ Menampilkan barcode jika sudah bayar

---

## 🔍 Database Verification

### Check Migration Hasil

```sql
DESCRIBE bookings;

-- Harus ada kolom:
-- - duration_hours (int, default 1)
-- - start_time (time)
-- - email (varchar, nullable)
```

### Check Data Tersimpan

```sql
SELECT 
    id, 
    booking_code, 
    duration_hours, 
    start_time, 
    email, 
    total_price, 
    status 
FROM bookings 
ORDER BY created_at DESC 
LIMIT 5;

-- Pastikan:
-- - duration_hours bukan NULL
-- - total_price = price_per_hour × duration_hours
-- - start_time terisi dengan jam dari time_slot
```

---

## 🐛 Troubleshooting

### Issue 1: Durasi tidak tersimpan
**Check:**
- [ ] Migration sudah dijalankan?
- [ ] Kolom `duration_hours` tersedia di database?
- [ ] Controller validasi menerima `duration_hours`?

### Issue 2: Barcode tidak hilang setelah bayar
**Check:**
- [ ] Payment status di database = 'completed'?
- [ ] Kondisi logic: `$payment && $payment->status === 'completed'`?
- [ ] Reload halaman (cache)?

### Issue 3: Harga tidak dihitung benar
**Check:**
- [ ] `price_per_hour` field ada di Court?
- [ ] Formula: `$booking->total_price = $court->price_per_hour * $duration_hours`
- [ ] JavaScript kalkulasi: `pricePerHour * duration`

### Issue 4: Waktu menampilkan NULL
**Check:**
- [ ] TimeSlot table punya `start_time` field?
- [ ] Controller populate `start_time`: `$booking->start_time = $startTimeSlot->start_time`
- [ ] View access: `{{ $booking->start_time }}`

---

## 📝 Validation Rules

```php
// Validasi di BookingController->confirm()
'date' => 'required|date',
'start_time_slot_id' => 'required|exists:time_slots,id',
'duration_hours' => 'required|integer|min:1|max:8',
'customer_name' => 'required|string|max:255',
'phone' => 'required|string|max:20',
'email' => 'nullable|email',
```

---

## 🎯 Success Criteria

✅ Implementation berhasil jika:

1. **Multi-jam selection** berfungsi (1-8 jam)
2. **Harga dinamis** dihitung dengan benar
3. **Barcode HIDDEN** sebelum pembayaran
4. **Barcode VISIBLE** setelah pembayaran completed
5. **Track booking** berfungsi dengan barcode
6. **Database** menyimpan duration dan start_time

---

## 📞 Questions/Issues?

Jika ada issues, check:
1. Database migrations status: `php artisan migrate:status`
2. Error logs: `storage/logs/laravel.log`
3. Browser console (F12) untuk JS errors
4. Database values dengan `php artisan tinker`

---

**Last Updated**: April 17, 2026  
**Version**: 1.0  
**Status**: Ready for Testing
