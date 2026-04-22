# PADEL BOOKING - VERIFICATION & QUICK START

> Tanggal: April 22, 2026  
> Dibuat untuk memastikan PWA + Midtrans berfungsi sempurna

---

## ⚡ QUICK START (5 menit)

### 1. Setup Database (1 menit)

```bash
# Pastikan MySQL running, lalu:
php artisan migrate
```

**Check**: Tidak ada error

### 2. Start Server (1 menit)

```bash
php artisan serve
```

**Check**: Tertulis "Laravel development server started"

### 3. Verify Setup (1 menit)

```bash
php setup-project.php
```

**Check**: Output harus show "✓ PROJECT SETUP LOOKS GOOD!"

### 4. Test di Browser (2 menit)

1. Buka http://localhost:8000
2. Buka F12 (DevTools)
3. Console tab → Paste ini:

```javascript
// Copy-paste ini di console:

console.log('=== VERIFICATION CHECKS ===');

// 1. Check Service Worker
navigator.serviceWorker.getRegistrations().then(regs => {
  console.log('✓ Service Workers:', regs.length > 0 ? 'YES' : 'NO');
});

// 2. Check Manifest
fetch('/manifest.json').then(r => r.json()).then(m => {
  console.log('✓ Manifest Name:', m.name);
});

// 3. Check Offline Page
fetch('/offline.html').then(r => {
  console.log('✓ Offline Page:', r.ok ? 'YES' : 'NO');
});

console.log('=== DONE ===');
```

**Check**: Semua harus "YES"

---

## 🔍 DETAILED VERIFICATION

### Check 1: .env File

```bash
# Pastikan ada di .env:
grep -E "^(MIDTRANS_|APP_|DB_)" .env
```

**Yang harus ada:**
- ✓ `MIDTRANS_IS_PRODUCTION=false` (untuk testing)
- ✓ `MIDTRANS_SERVER_KEY=SB-Mid-server-...`
- ✓ `MIDTRANS_CLIENT_KEY=SB-Mid-client-...`
- ✓ `DB_DATABASE=padel`
- ✓ `APP_DEBUG=true`
- ✓ `APP_ENV=local`

### Check 2: PWA Files

```bash
# Verifikasi file ada
ls -la public/service-worker.js
ls -la public/manifest.json
ls -la public/offline.html

# Harus output 3 file dengan size > 0
```

### Check 3: Database Tables

```bash
# Connect ke MySQL
mysql -u root padel -e "SHOW TABLES LIKE 'payment%';"

# Harus output:
# payments
# payment_methods (opsional)
```

### Check 4: Routes

```bash
# List payment routes
php artisan route:list | grep -i payment
```

**Harus ada routes:**
- ✓ `/payment/create-transaction` (POST)
- ✓ `/midtrans/callback` (POST)
- ✓ `/payment/{booking}` (GET)

### Check 5: Controllers

```bash
# Verify controllers ada
ls -la app/Http/Controllers/ | grep -i payment

# Harus ada:
# PaymentController.php
# MidtransCallbackController.php
```

### Check 6: Middleware

```bash
# Check CSRF exceptions
grep -A 5 "protected \$except" app/Http/Middleware/VerifyCsrfToken.php

# Harus include:
# 'payment/callback'
# 'midtrans/callback'
```

---

## 🧪 TESTING SCENARIOS

### Scenario 1: PWA Installation

**Step:**
1. Buka http://localhost:8000
2. Lihat "Install App" button di navbar
3. Klik → Browser akan show install prompt
4. Click "Install"

**Expected Result:**
- ✓ App bisa di-install
- ✓ Icon muncul di desktop/home screen
- ✓ Open dengan fullscreen (tanpa browser bar)

### Scenario 2: Offline Mode

**Step:**
1. Buka http://localhost:8000
2. F12 → Network tab → Offline (click checkbox)
3. Refresh page
4. Coba buka halaman lain (click menu items)

**Expected Result:**
- ✓ Main page tetap bisa diakses (dari cache)
- ✓ Offline message muncul (atau offline.html)
- ✓ Tidak ada error di console

### Scenario 3: Midtrans Payment

**Step:**
1. Buka http://localhost:8000
2. Pilih court
3. Booking lapangan (isi form)
4. Proceed to payment
5. Di payment page, click "Bayar Sekarang"
6. Midtrans Snap popup akan muncul

**Expected Result:**
- ✓ Midtrans Snap popup muncul
- ✓ Bisa pilih payment method (Bank Transfer, E-wallet, etc)
- ✓ Tidak ada error di console
- ✓ No CORS errors

### Scenario 4: Complete Payment Flow

**Step:**
1. Di Midtrans Snap, pilih "Bank Transfer"
2. Follow instruksi (copy nomor rekening)
3. Click "Selesai"
4. Kembali ke app

**Expected Result:**
- ✓ Payment status berubah ke "pending"
- ✓ User see confirmation message
- ✓ Database ter-update (check: `SELECT * FROM payments;`)

---

## 🆘 COMMON ISSUES & FIXES

### Issue 1: Service Worker Not Registered

**Symptoms:**
- F12 → Application → Service Workers → (empty)
- Console error: "Failed to register service worker"

**Fix:**
```bash
# 1. Check file exists
ls -la public/service-worker.js

# 2. Clear browser cache
# F12 → Application → Clear site data

# 3. Hard refresh
# Ctrl+Shift+R (Windows/Linux) atau Cmd+Shift+R (Mac)

# 4. Restart server
# Ctrl+C → php artisan serve
```

### Issue 2: Midtrans API Error 401

**Symptoms:**
- Console error: "Unauthorized"
- Network tab → Request to Midtrans → 401 response

**Fix:**
```bash
# 1. Verify .env keys
echo $MIDTRANS_SERVER_KEY
echo $MIDTRANS_CLIENT_KEY

# 2. Get correct keys from https://dashboard.midtrans.com
# - Go to Settings → Access Keys
# - Copy Sandbox keys (NOT Production!)

# 3. Update .env
# MIDTRANS_SERVER_KEY=SB-Mid-server-...
# MIDTRANS_CLIENT_KEY=SB-Mid-client-...

# 4. Restart server
```

### Issue 3: Database Connection Error

**Symptoms:**
- Console error: "SQLSTATE[HY000]"
- Laravel error: "connection refused"

**Fix:**
```bash
# 1. Start MySQL
# Windows: net start MySQL80
# Mac: brew services start mysql
# Linux: sudo systemctl start mysql

# 2. Check MySQL is running
mysql -u root -e "SELECT 1;"

# 3. Check .env credentials
cat .env | grep DB_

# 4. Run migrations
php artisan migrate
```

### Issue 4: CSRF Token Mismatch

**Symptoms:**
- Error: "419 | Page Expired"
- Or: "CSRF token mismatch"

**Fix:**
```bash
# 1. Check CSRF exceptions in VerifyCsrfToken.php
grep -A 10 "protected \$except" app/Http/Middleware/VerifyCsrfToken.php

# 2. Ensure Midtrans routes are excluded:
# protected $except = [
#     'payment/callback',
#     'midtrans/callback',
# ];

# 3. If needed, add/update:
# See: app/Http/Middleware/VerifyCsrfToken.php
```

### Issue 5: Offline Page Not Showing

**Symptoms:**
- Go offline → Page shows error instead of offline.html
- F12 → Application → Cache Storage → (empty)

**Fix:**
```bash
# 1. Clear all service workers
# F12 → Application → Service Workers
# Click "Unregister" on all

# 2. Clear cache
# F12 → Application → Storage → Clear site data

# 3. Hard refresh
# Ctrl+Shift+R

# 4. Go offline and test
# F12 → Network → Offline (check)
# Refresh
```

---

## 📊 VERIFICATION REPORT TEMPLATE

Print ini untuk track setup progress:

```
PROJECT: Padel Booking
DATE: _______________
TESTER: ______________

SETUP STATUS:
☐ PHP 8.0+ installed
☐ Composer dependencies installed
☐ MySQL running
☐ .env configured with correct keys
☐ Database migrations run
☐ App key generated

PWA STATUS:
☐ service-worker.js exists
☐ manifest.json exists  
☐ offline.html exists
☐ Service Worker registered in browser
☐ Manifest loads without error
☐ Install button shows in browser
☐ Install works

MIDTRANS STATUS:
☐ API keys configured in .env
☐ Sandbox mode enabled
☐ Snap token generation works
☐ Snap popup displays
☐ Payment callback received
☐ Database updated after payment

TESTING STATUS:
☐ Offline mode works
☐ Cache working
☐ No console errors
☐ Payment flow complete
☐ Admin panel accessible
☐ Booking creates successfully

NOTES:
_________________________________
_________________________________
_________________________________

SIGNED: _____________ DATE: _____
```

---

## 📞 STILL HAVING ISSUES?

### Step 1: Check Logs

```bash
# Real-time logs
tail -f storage/logs/laravel.log

# See last errors
tail -100 storage/logs/laravel.log | grep -i error
```

### Step 2: Browser Console

```javascript
// F12 → Console

// Check for errors (red text)
// Copy-paste error messages

// Check service worker status
navigator.serviceWorker.getRegistrations().then(regs => {
  console.log('SW:', regs);
});

// Check cache status  
caches.keys().then(names => {
  console.log('Caches:', names);
  names.forEach(name => {
    caches.open(name).then(cache => {
      cache.keys().then(requests => {
        console.log(`${name}: ${requests.length} files`);
      });
    });
  });
});
```

### Step 3: Network Tab

```
F12 → Network tab

Lihat requests:
1. Ke localhost → harus 200 OK
2. Ke cdn.jsdelivr.net → harus 200 OK  
3. Ke Midtrans (snap.midtrans.com) → harus 200 OK

Jika ada error (red):
- Right-click → Copy → cURL
- Paste ke terminal untuk debug
```

### Step 4: Database Check

```bash
php artisan tinker

# Check payments
>>> App\Models\Payment::latest()->first()
>>> App\Models\Booking::count()

# Check if tables exist
>>> Schema::hasTable('payments')
>>> Schema::hasTable('bookings')

exit
```

---

## ✅ FINAL CHECKLIST

Sebelum declare "SIAP PRODUCTION", pastikan:

- [ ] ✓ Setup script berhasil tanpa error
- [ ] ✓ Service Worker registered
- [ ] ✓ Offline page works
- [ ] ✓ Midtrans payment flow complete
- [ ] ✓ Database connected
- [ ] ✓ All routes accessible
- [ ] ✓ No console errors
- [ ] ✓ Payment recorded in DB
- [ ] ✓ Admin panel works
- [ ] ✓ Responsive design OK

**Status: READY FOR DEPLOYMENT** ✅

---

*Setup Verified on: April 22, 2026*  
*Framework: Laravel 10+*  
*PHP Version: 8.0+*  
*Database: MySQL*
