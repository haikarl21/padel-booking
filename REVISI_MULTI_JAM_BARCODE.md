# Implementasi Revisi Pemesanan Padel - April 17, 2026

## Ringkasan Perubahan Utama

Implementasi revisi untuk mendukung:
1. ✅ **Pemilihan multi-jam** - Pengguna dapat memilih 1-8 jam bermain
2. ✅ **Barcode Pemesanan** - Mengganti "Referensi Booking" menjadi "Barcode"
3. ✅ **Scan Barcode** - Fitur track booking menggunakan barcode
4. ✅ **Barcode setelah pembayaran** - Barcode hanya ditampilkan setelah pembayaran berhasil

---

## Detail Perubahan

### 1. Database Schema (Migration)
**File**: `database/migrations/2026_04_17_000001_update_bookings_for_multi_hour.php`

Menambahkan kolom ke tabel `bookings`:
- `duration_hours` (integer, default 1) - Durasi bermain dalam jam
- `start_time` (time, nullable) - Jam mulai bermain
- `email` (string, nullable) - Email pelanggan

### 2. Model Booking
**File**: `app/Models/Booking.php`

Update `$fillable` array untuk menambahkan:
- `duration_hours`
- `start_time`

### 3. View: Pilih Waktu & Durasi
**File**: `resources/views/booking/select-datetime.blade.php`

**Perubahan:**
- ✅ Label berubah dari "Slot Waktu Tersedia" → "Pilih Waktu Mulai"
- ✅ Input field untuk durasi (1-8 jam)
- ✅ Kalkulasi harga dinamis berdasarkan durasi
- ✅ Tombol confirm disabled sampai waktu dan durasi dipilih
- ✅ Tambah field email (optional)
- ✅ JavaScript untuk auto-calculate total price

**Form fields baru:**
```blade
<select name="duration_hours">
  <option>1 Jam</option>
  <option>2 Jam</option>
  ... hingga 8 Jam
</select>
```

### 4. Controller: BookingController
**File**: `app/Http/Controllers/BookingController.php`

**Metode `confirm()`:**
- Validasi perubahan:
  - `start_time_slot_id` (bukan `time_slot_id`)
  - `duration_hours` (1-8)
- Kalkulasi: `total_price = price_per_hour × duration_hours`
- Simpan: `start_time` dari slot yang dipilih

### 5. View: Detail Booking
**File**: `resources/views/booking/detail.blade.php`

**Perubahan:**
- ✅ Barcode HANYA ditampilkan jika pembayaran sudah completed
- ✅ Border barcode gemerlap hijau (bukan orange) saat pembayaran berhasil
- ✅ Durasi menampilkan `{{ $booking->duration_hours }} Jam` (bukan hardcoded "1 Jam")
- ✅ Waktu menampilkan dari `start_time` field
- ✅ Logic: Check jika payment status = 'completed' baru tampil barcode

**Conditional Display:**
```blade
@if($isPaymentCompleted)
  <!-- Tampilkan Barcode dengan styling hijau -->
@endif
```

### 6. View: Track Booking
**File**: `resources/views/track-booking.blade.php`

**Perubahan:**
- ✅ Title: "Lacak Booking Anda" → "Scan Barcode Booking"
- ✅ Label: "Nomor Referensi Booking" → "Barcode Pemesanan"
- ✅ Placeholder: "BKG-XXXXXX" → "Pindai atau masukkan barcode"
- ✅ Button: "Lihat Booking" → "Lacak Booking"
- ✅ Info card icon berubah (hashtag → barcode)
- ✅ Input autofocus untuk memudahkan scanning barcode

---

## Alur Pemesanan (Updated)

### Sebelumnya:
```
1. Pilih lapangan
2. Pilih tanggal
3. Pilih 1 slot waktu (1 jam fixed)
4. Isi data diri
5. Lihat detail booking dengan booking code/referensi
6. Bayar
```

### Sekarang:
```
1. Pilih lapangan
2. Pilih tanggal
3. Pilih WAKTU MULAI
4. Pilih DURASI (1-8 jam)
5. Lihat harga dinamis
6. Isi data diri (nama, telepon, email)
7. Lihat detail booking (TANPA BARCODE)
8. Pilih metode pembayaran
9. Bayar
10. ✅ BARCODE ditampilkan setelah pembayaran berhasil
```

---

## Database Migrations

### Eksekusi:
```bash
php artisan migrate
```

### Setelah Migrate:
Kolom baru akan tersedia untuk bookings:
- `duration_hours` - Menyimpan jumlah jam yang dipesan
- `start_time` - Menyimpan jam mulai secara langsung
- `email` - Email pelanggan (untuk komunikasi)

---

## Testing Checklist

### ✅ Multi-jam Selection:
- [ ] Buka pages pemesanan
- [ ] Pilih tanggal
- [ ] Lihat dropdown durasi 1-8 jam
- [ ] Harga berubah saat durasi berubah
- [ ] Total = price_per_hour × duration

### ✅ Barcode Display:
- [ ] Sebelum bayar: TIDAK ada barcode di detail booking
- [ ] Setelah bayar (status completed): Barcode tampil dengan styling hijau
- [ ] Copy button berfungsi

### ✅ Track Booking (Barcode Scan):
- [ ] Kunjungi `/track-booking`
- [ ] Input barcode manual atau scan
- [ ] Redirect ke detail booking

### ✅ Data Penyimpanan:
- [ ] Booking menyimpan `duration_hours`
- [ ] Booking menyimpan `start_time`
- [ ] Harga dihitung benar: `price × hours`

---

## Files Modified

1. ✅ `database/migrations/2026_04_17_000001_update_bookings_for_multi_hour.php` - BARU
2. ✅ `app/Models/Booking.php` - Updated fillable
3. ✅ `app/Http/Controllers/BookingController.php` - Updated confirm() method
4. ✅ `resources/views/booking/select-datetime.blade.php` - Major update
5. ✅ `resources/views/booking/detail.blade.php` - Updated to hide barcode
6. ✅ `resources/views/track-booking.blade.php` - Updated UI for barcode

---

## Next Steps

1. **Run Migration** (ketika DB sudah connected):
   ```bash
   php artisan migrate
   ```

2. **Test Bookings** - Pastikan semua flow berfungsi:
   - Multi-hour selection works
   - Harga dihitung benar
   - Barcode hanya tampil setelah bayar
   - Track booking dengan barcode berfungsi

3. **QR Code (Optional)** - Bisa ditambahkan nanti:
   - Generate QR dari booking_code
   - Display di detail booking setelah pembayaran

---

## Notes

- ⚠️ `time_slot_id` tetap ada di schema untuk backward compatibility
- ⚠️ `start_time_slot_id` di form sebagai variable saja (tidak tersimpan sebagai kolom)
- ✅ `start_time` yang disimpan adalah waktu dari TimeSlot yang dipilih
- ✅ Email sekarang optional, tidak required
- ✅ Barcode = booking_code (sama, hanya rename UI)
