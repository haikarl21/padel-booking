# UPDATE: Multiple Time Slot Selection

## Perubahan dari Implementasi Sebelumnya

### Sebelumnya:
- Pilih 1 waktu mulai (radio button)
- Pilih durasi bermain 1-8 jam (dropdown)
- Total harga = price_per_hour × durasi

### Sekarang (Updated):
- Pilih MULTIPLE time slots (checkbox) ✅
- HAPUS dropdown durasi ✅
- Total harga = price_per_hour × jumlah slots yang dipilih ✅

---

## Detail Perubahan

### 1. View: select-datetime.blade.php

**Perubahan:**
- ✅ Radio button → Checkbox (type="checkbox")
- ✅ Input name: `start_time_slot_id` → `time_slot_ids[]` (array)
- ✅ Label: "Pilih Waktu Mulai" → "Pilih Slot Waktu (Bisa Lebih dari 1)"
- ✅ HAPUS: Dropdown durasi bermain
- ✅ JavaScript: Hitung harga berdasarkan jumlah checkbox yang di-check

**JavaScript Logic:**
```javascript
const checkedSlots = document.querySelectorAll('.time-slot:checked').length;
const total = pricePerHour * checkedSlots;
// Update totalPrice
```

### 2. Controller: BookingController.php

**Metode `confirm()`:**
- ✅ Validasi berubah:
  - `time_slot_ids` → array (min:1)
  - `time_slot_ids.*` → exists:time_slots,id
- ✅ Ambil semua selected slots: `TimeSlot::whereIn('id', $validated['time_slot_ids'])`
- ✅ Duration = jumlah slots: `$durationHours = $selectedSlots->count()`
- ✅ Total price = `price_per_hour × count($selectedSlots)`
- ✅ Start time = slot pertama (earliest)

### 3. View: detail.blade.php

**Perubahan:**
- ✅ Waktu tampil: `start_time - (start_time + duration_hours)`
- ✅ Contoh: 09:00 - 12:00 (jika dipilih 3 slot)

---

## Contoh Alur

### User memilih:
- Lapangan Court 2
- Tanggal: 2026-04-20
- Slot waktu: 
  - ☑ 09:00-10:00
  - ☑ 10:00-11:00
  - ☑ 11:00-12:00

### Hasil:
- Duration: 3 jam (3 slots)
- Total harga: 3 × Rp 200.000 = Rp 600.000
- Waktu display: 09:00 - 12:00

---

## Database Schema (Unchanged)

```sql
bookings:
- duration_hours (int) ← 3 (jumlah slots)
- start_time (time) ← 09:00 (slot pertama)
- total_price (decimal) ← 600000 (price × duration)
```

---

## Validasi

### ✅ Form Validation
```php
'time_slot_ids' => 'required|array|min:1'
'time_slot_ids.*' => 'exists:time_slots,id'
```

### ✅ Button Enable/Disable
- Disabled: Tidak ada slot dipilih
- Enabled: Minimal 1 slot dipilih

### ✅ Price Calculation
- Real-time update saat checkbox di-check/uncheck
- Format: Rp X.XXX.XXX (Indonesia)

---

## Files Modified

1. ✅ `resources/views/booking/select-datetime.blade.php`
   - Checkbox (bukan radio)
   - Hapus dropdown durasi
   - Update JavaScript

2. ✅ `app/Http/Controllers/BookingController.php`
   - Update validasi
   - Handle array time_slot_ids
   - Kalkulasi duration dari count slots

3. ✅ `resources/views/booking/detail.blade.php`
   - Tampil waktu range: start_time - end_time

---

## Testing

Test cases:
- [ ] Pilih 1 slot → Harga = price_per_hour
- [ ] Pilih 2 slot → Harga = price_per_hour × 2
- [ ] Pilih 3 slot → Harga = price_per_hour × 3
- [ ] Harga update real-time saat checkbox di-check
- [ ] Booking detail tampil waktu range benar
- [ ] Duration tampil benar (jumlah slot)

---

**Status**: ✅ UPDATED & READY FOR TESTING
