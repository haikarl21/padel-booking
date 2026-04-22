# PADEL BOOKING - PWA + MIDTRANS SETUP GUIDE

> **Tanggal**: April 22, 2026  
> **Status**: ✅ SIAP DIGUNAKAN  
> **Versi**: 1.1.0

---

## 📋 DAFTAR ISI

1. [Quick Start](#quick-start)
2. [Troubleshooting](#troubleshooting)
3. [Testing Checklist](#testing-checklist)
4. [Architecture Overview](#architecture-overview)
5. [Security Notes](#security-notes)
6. [Production Deployment](#production-deployment)

---

## 🚀 QUICK START

### Prerequisites
- PHP 8.0+
- Composer
- MySQL/MariaDB
- Node.js (optional, untuk asset compilation)
- Browser yang support PWA (Chrome, Edge, Brave)

### Step 1: Initial Setup

```bash
# 1. Clone/setup project (sudah ada)
cd padel-booking

# 2. Install PHP dependencies
composer install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Run migrations
php artisan migrate

# 5. Run setup verification
php setup-project.php
```

### Step 2: Konfigurasi .env

Pastikan .env Anda memiliki konfigurasi ini:

```env
# APP Configuration
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=padel
DB_USERNAME=root
DB_PASSWORD=

# Midtrans (Sandbox untuk testing)
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SERVER_KEY=SB-Mid-server-YOUR_SANDBOX_KEY
MIDTRANS_CLIENT_KEY=SB-Mid-client-YOUR_SANDBOX_KEY
MIDTRANS_MERCHANT_ID=YOUR_MERCHANT_ID

# Cache & Session
CACHE_STORE=database
SESSION_DRIVER=database

# PWA Configuration
APP_URL=http://localhost:8000
```

⚠️ **PENTING**: Dapatkan Midtrans keys dari https://dashboard.midtrans.com (Sandbox account)

### Step 3: Jalankan Development Server

```bash
# Terminal 1 - Start Laravel Server
php artisan serve

# Akses di browser: http://localhost:8000
```

### Step 4: Verifikasi PWA & Midtrans

Buka browser (Chrome/Edge) dan lakukan:

```javascript
// Buka F12 → Console, paste ini:

// 1. Check Service Worker
navigator.serviceWorker.getRegistrations().then(regs => {
  console.log('✓ Service Workers:', regs.length > 0 ? 'Registered' : 'Not registered');
});

// 2. Check Manifest
fetch('/manifest.json').then(r => r.json()).then(m => {
  console.log('✓ Manifest:', m.name);
});

// 3. Check Offline page
fetch('/offline.html').then(r => {
  console.log('✓ Offline page:', r.ok ? 'Available' : 'Not found');
});
```

---

## 🔧 TROUBLESHOOTING

### ❌ Error: "Service Worker not registered"

**Penyebab**: Service worker file tidak ada atau tidak ter-load

**Solusi**:
```bash
# 1. Verifikasi file ada
ls -la public/service-worker.js

# 2. Akses langsung di browser
# Buka: http://localhost:8000/service-worker.js
# Harus menampilkan JavaScript code

# 3. Check console
# Buka F12 → Console → cari error messages
# Periksa Storage tab → Service Workers
```

### ❌ Error: "Midtrans API Error 401"

**Penyebab**: API key tidak valid atau configuration salah

**Solusi**:
```bash
# 1. Verifikasi .env
grep MIDTRANS .env

# 2. Pastikan keys benar dari dashboard.midtrans.com
# - Server Key (rahasia, jangan bagikan!)
# - Client Key (boleh public)
# - Merchant ID

# 3. Check is_production setting
# Untuk testing: MIDTRANS_IS_PRODUCTION=false

# 4. Test Midtrans connection
# Buka F12 → Network tab → buat payment
# Cari request ke snap.midtrans.com
```

### ❌ Error: "CORS error dari Midtrans"

**Penyebab**: Browser security policy atau localhost configuration

**Solusi**:
```javascript
// Di app.blade.php service worker registration, pastikan:
navigator.serviceWorker.register('/service-worker.js', {
    scope: '/'  // Penting: scope harus '/'
}).then(registration => {
    console.log('✓ SW registered');
});

// Jika masih error, check:
// 1. F12 → Network → cek request headers
// 2. Response headers harus include CORS headers
// 3. Midtrans routes harus exclude dari CSRF (sudah done)
```

### ❌ Error: "Cannot connect to database"

**Penyebab**: MySQL tidak running atau credentials salah

**Solusi**:
```bash
# 1. Start MySQL server
# Windows: net start MySQL80
# atau buka XAMPP/WampServer

# 2. Verifikasi credentials di .env
# Test manual:
mysql -h 127.0.0.1 -u root -p padel

# 3. Run migrations
php artisan migrate

# 4. Check storage logs
tail -f storage/logs/laravel.log
```

### ❌ Error: "Payment failed - Invalid signature"

**Penyebab**: Signature verification failed (security check)

**Solusi**:
```bash
# 1. Check Server Key di .env
# MIDTRANS_SERVER_KEY harus sama dengan di Midtrans dashboard

# 2. Check Midtrans callback URL
# Di Midtrans Dashboard: Set Notification URL ke:
# http://localhost:8000/midtrans/callback

# 3. Check logs
tail -f storage/logs/laravel.log | grep "Midtrans"

# 4. Pastikan CSRF exception sudah di-add
# Di app/Http/Middleware/VerifyCsrfToken.php
# Sudah di-exclude: /midtrans/callback
```

### ❌ Error: "Offline page not showing"

**Penyebab**: Cache belum ter-register dengan baik

**Solusi**:
```javascript
// Buka F12 → Application → Service Workers
// Click "Unregister" untuk service worker yang ada

// Refresh browser: Ctrl+Shift+R (hard refresh)

// Check cache:
caches.keys().then(names => {
  console.log('Cache names:', names);
  names.forEach(name => {
    caches.open(name).then(cache => {
      cache.keys().then(requests => {
        console.log(`Cache ${name}:`, requests.map(r => r.url));
      });
    });
  });
});

// Clear all caches
caches.keys().then(names => {
  names.forEach(name => caches.delete(name));
});
```

---

## ✅ TESTING CHECKLIST

### Test 1: PWA Installation
- [ ] Buka http://localhost:8000
- [ ] Buka F12 (Browser DevTools)
- [ ] Lihat "Install App" button di navbar
- [ ] Klik tombol → Should show install prompt
- [ ] Install app → Should work offline

### Test 2: Service Worker
- [ ] F12 → Application → Service Workers
- [ ] Harus ada 1 service worker dalam status "activated"
- [ ] Buka offline mode (DevTools → Network → Offline)
- [ ] Refresh page → Harus bisa akses cached pages
- [ ] Buka `/track-booking` → Should show cached version

### Test 3: Midtrans Payment
- [ ] Buka http://localhost:8000
- [ ] Booking lapangan (fill form)
- [ ] Proceed to payment
- [ ] Choose payment method
- [ ] Should redirect ke Midtrans Snap
- [ ] Di Snap popup, pilih payment method
- [ ] Complete payment
- [ ] Check database: Payment status should be "settlement"

### Test 4: Offline Mode
- [ ] F12 → Network → Offline
- [ ] Refresh page → Offline page should show
- [ ] Check message: "Your internet is disconnected"
- [ ] Go Online → Network → Online
- [ ] Refresh → Should back to normal

### Test 5: Cache Management
```bash
# Terminal
php artisan tinker
# Jalankan:
>>> Cache::flush()  # Clear all cache
>>> exit

# Browser
# F12 → Application → Storage → Clear All
# Refresh page
```

### Test 6: Database Connectivity
```bash
# Terminal
php artisan tinker
>>> App\Models\Booking::count()
>>> App\Models\Payment::count()
>>> exit
```

---

## 🏗️ ARCHITECTURE OVERVIEW

### Directory Structure

```
padel-booking/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── PaymentController.php      (Midtrans integration)
│   │   │   ├── MidtransCallbackController.php
│   │   │   └── BookingController.php
│   │   ├── Middleware/
│   │   │   ├── VerifyCsrfToken.php        (Exclude Midtrans routes)
│   │   │   └── LocalhostSecurityMiddleware.php
│   │   └── Kernel.php
│   ├── Models/
│   │   ├── Booking.php
│   │   ├── Payment.php
│   │   └── User.php
│   └── Services/
│       ├── MidtransService.php
│       └── PaymentCustomService.php
│
├── config/
│   ├── midtrans.php                      (Midtrans config)
│   └── app.php
│
├── database/
│   └── migrations/
│       └── 2026_03_28_*.php               (Payment tables)
│
├── public/
│   ├── manifest.json                     (PWA manifest)
│   ├── service-worker.js                 (PWA service worker)
│   ├── offline.html                      (Offline fallback)
│   ├── css/app.css
│   ├── js/app.js
│   └── index.php
│
├── resources/
│   └── views/
│       ├── layouts/app.blade.php         (Main layout with PWA)
│       ├── booking/
│       │   └── *.blade.php
│       ├── payment/
│       │   ├── snap.blade.php            (Midtrans Snap integration)
│       │   └── show.blade.php
│       └── admin/
│
├── routes/
│   └── web.php                           (All routes including Midtrans)
│
├── .env                                   (Environment config)
├── .env.example
├── setup-project.php                     (Setup verification script)
├── composer.json
├── artisan
└── README.md
```

### Request Flow

```
┌─────────────────────────────────────────────────────────────┐
│                    USER BROWSER                              │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  localhost:8000                                      │   │
│  │  - HTML pages cached by Service Worker              │   │
│  │  - PWA manifest loaded                              │   │
│  │  - Midtrans Snap.js loaded                          │   │
│  └──────────────────────────────────────────────────────┘   │
└────────────────────────────────┬────────────────────────────┘
                                 │
                 ┌───────────────┼───────────────┐
                 │               │               │
                 ▼               ▼               ▼
          ┌──────────┐   ┌──────────┐   ┌──────────────┐
          │ Laravel  │   │ Database │   │ Midtrans API │
          │ 8000     │   │ MySQL    │   │ (Sandbox)    │
          │          │   │          │   │              │
          │ Routes:  │   │ Bookings │   │ - getToken   │
          │ /        │   │ Payments │   │ - verify     │
          │ /payment │   │ Users    │   │ - webhook    │
          │ /booking │   └──────────┘   └──────────────┘
          └──────────┘
```

### Payment Flow

```
1. User selects booking
   ↓
2. Click "Bayar Sekarang"
   ↓
3. POST /payment/create-transaction
   ↓
4. PaymentController::createTransaction()
   ├─ MidtransService::getSnapToken()
   ├─ Save to Payment table (status=pending)
   └─ Return snap_token
   ↓
5. Snap.js di frontend
   ├─ Display Snap popup
   └─ User selects payment method
   ↓
6. Payment at Midtrans
   ├─ Bank transfer / E-wallet / Credit Card
   └─ Send webhook when status changes
   ↓
7. MidtransCallbackController::handle()
   ├─ Verify signature
   ├─ Parse transaction status
   ├─ Update Payment & Booking tables
   └─ Return 200 OK
   ↓
8. User sees confirmation
   ├─ Payment status changed
   └─ Booking approved
```

---

## 🔒 SECURITY NOTES

### ✅ What's Protected

1. **Midtrans Callback** (CSRF Exception)
   - Route: `/midtrans/callback`
   - Protection: Signature key verification (SHA512)
   - Why: External service can't include CSRF token

2. **Payment Validation**
   - All amounts validated server-side
   - User cannot manipulate prices
   - Signature verified before processing

3. **Sensitive Data**
   - Server Key: Stored in .env only
   - Client Key: Public (safe in frontend)
   - Signature: Verified with SHA512 hash

4. **Database**
   - All payments logged with timestamps
   - Audit trail: who approved, who rejected
   - Transaction history preserved

### ⚠️ Localhost Notes

- Service Worker works on localhost (special exception)
- HTTPS not required for localhost testing
- But RECOMMENDED for production

### 🚫 What NOT to Do

- ❌ Never commit .env with real keys
- ❌ Never log sensitive data to stdout
- ❌ Never disable signature verification
- ❌ Never bypass CSRF protection
- ❌ Never expose Server Key to frontend

---

## 🚀 PRODUCTION DEPLOYMENT

### Before Going Live

1. **Change Midtrans Keys**
   ```env
   MIDTRANS_IS_PRODUCTION=true
   MIDTRANS_SERVER_KEY=Mid-server-YOUR_PRODUCTION_KEY
   MIDTRANS_CLIENT_KEY=Mid-client-YOUR_PRODUCTION_KEY
   ```

2. **Enable HTTPS**
   - Get SSL certificate (Let's Encrypt)
   - Configure web server (nginx/Apache)
   - Force HTTPS redirects

3. **Set APP_DEBUG to false**
   ```env
   APP_DEBUG=false
   APP_ENV=production
   ```

4. **Configure Monitoring**
   ```bash
   # Setup error logging
   tail -f storage/logs/laravel.log
   
   # Setup uptime monitoring
   # Configure email alerts
   ```

5. **Backup Strategy**
   ```bash
   # Daily database backups
   # Weekly storage backups
   # Version control (git tags)
   ```

6. **Performance Optimization**
   ```bash
   # Cache optimization
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   
   # Database optimization
   php artisan optimize
   ```

### Deployment Checklist

- [ ] SSL certificate installed
- [ ] HTTPS enabled and enforced
- [ ] Midtrans keys set to production
- [ ] APP_DEBUG=false
- [ ] APP_ENV=production
- [ ] Database migrations run
- [ ] Cache cleared and optimized
- [ ] Logs configured
- [ ] Monitoring enabled
- [ ] Email notifications working
- [ ] Testing on production environment
- [ ] Backup strategy in place

---

## 📞 SUPPORT & DEBUGGING

### Enable Debug Mode

```env
# .env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Check Logs

```bash
# Real-time logs
tail -f storage/logs/laravel.log

# Last 50 lines
tail -50 storage/logs/laravel.log

# Search for errors
grep -i error storage/logs/laravel.log

# Search for Midtrans
grep -i midtrans storage/logs/laravel.log
```

### MySQL Debugging

```bash
# Connect to database
mysql -u root -p padel

# Check tables
SHOW TABLES;
SELECT * FROM payments LIMIT 5;
SELECT * FROM bookings LIMIT 5;

# Check payment logs
SELECT * FROM payments ORDER BY created_at DESC;
```

### Browser DevTools

```javascript
// Console - Check for errors
// Application tab - Check Service Worker
// Network tab - Check requests to Midtrans
// Storage tab - Check LocalStorage, Cookies
```

---

## 📝 VERSION HISTORY

- **v1.1.0** (April 22, 2026)
  - ✅ Optimized service worker for localhost
  - ✅ Added CSRF exception for Midtrans callback
  - ✅ Added setup verification script
  - ✅ Comprehensive troubleshooting guide

- **v1.0.0** (March 28, 2026)
  - ✅ Initial PWA setup
  - ✅ Midtrans integration
  - ✅ Payment system

---

## 🎉 READY TO GO!

Selamat! Project Anda sudah siap digunakan dengan PWA dan Midtrans payment.

**Jalankan sekarang:**

```bash
php artisan serve
# Akses: http://localhost:8000
```

**Pertanyaan?** Lihat section Troubleshooting di atas atau check logs.

---

*Last Updated: April 22, 2026*
*Maintained by: Development Team*
