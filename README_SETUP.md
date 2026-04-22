# 🎾 PADEL BOOKING - PWA + MIDTRANS PAYMENT SYSTEM

> **Status**: ✅ FULLY FUNCTIONAL  
> **Last Updated**: April 22, 2026  
> **Framework**: Laravel 10+  
> **PHP Version**: 8.0+  
> **Database**: MySQL

---

## 📋 OVERVIEW

Ini adalah aplikasi booking lapangan padel profesional dengan:

✅ **PWA (Progressive Web App)**
- Installable ke home screen
- Works offline
- Fast loading dengan intelligent caching

✅ **Midtrans Payment Integration**
- Sandbox untuk testing
- Multiple payment methods (Bank Transfer, E-wallet, Credit Card)
- Real-time payment status
- Automatic payment verification

✅ **Admin Dashboard**
- Manage courts & time slots
- View bookings & payments
- Approve/reject payments

✅ **Security**
- CSRF protection
- Signature key verification
- Database encryption
- Audit logging

---

## 🚀 QUICK START (5 MINUTES)

### Prerequisites Checklist
- [ ] PHP 8.0+
- [ ] Composer installed
- [ ] MySQL running
- [ ] Git (optional)

### Step 1: Install Dependencies
```bash
cd padel-booking
composer install
```

### Step 2: Setup Environment
```bash
# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Create database (manual atau via MySQL CLI):
# mysql> CREATE DATABASE padel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 3: Configure Midtrans
```bash
# Edit .env dan set:
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SERVER_KEY=SB-Mid-server-YOUR_KEY  # Get from dashboard.midtrans.com
MIDTRANS_CLIENT_KEY=SB-Mid-client-YOUR_KEY
MIDTRANS_MERCHANT_ID=YOUR_MERCHANT_ID
```

📌 **Get Midtrans Keys:**
1. Go to https://dashboard.midtrans.com
2. Sign up (free Sandbox account)
3. Go to Settings → Access Keys
4. Copy Sandbox keys (NOT Production!)

### Step 4: Run Migrations
```bash
php artisan migrate
```

### Step 5: Start Development Server
```bash
php artisan serve
```

**Output akan show:**
```
   INFO  Server running on [http://127.0.0.1:8000].
```

### Step 6: Verify Everything Works
1. Open browser: http://localhost:8000
2. Press F12 (DevTools)
3. Console tab, paste:
```javascript
navigator.serviceWorker.getRegistrations().then(r => console.log('SW:', r.length > 0 ? 'OK' : 'FAIL'));
fetch('/manifest.json').then(r => r.json()).then(m => console.log('Manifest:', m.name));
fetch('/offline.html').then(r => console.log('Offline:', r.ok ? 'OK' : 'FAIL'));
```

✅ **Semua harus OK?** → Project siap digunakan!

---

## 📁 PROJECT STRUCTURE

```
padel-booking/
├── app/
│   ├── Http/Controllers/
│   │   ├── PaymentController.php       ← Midtrans integration
│   │   ├── MidtransCallbackController.php ← Payment webhook
│   │   ├── BookingController.php
│   │   └── HomeController.php
│   ├── Models/
│   │   ├── Booking.php
│   │   ├── Payment.php
│   │   └── User.php
│   └── Services/
│       └── MidtransService.php         ← Midtrans API wrapper
│
├── config/
│   ├── midtrans.php                   ← Midtrans config
│   └── app.php
│
├── database/
│   └── migrations/                    ← Database schema
│
├── public/
│   ├── service-worker.js              ← PWA service worker
│   ├── manifest.json                  ← PWA manifest
│   ├── offline.html                   ← Offline page
│   └── css/, js/, images/
│
├── resources/views/
│   ├── layouts/app.blade.php          ← Main layout with PWA
│   ├── booking/                       ← Booking pages
│   ├── payment/                       ← Payment pages
│   └── admin/                         ← Admin dashboard
│
├── routes/
│   └── web.php                        ← All routes
│
├── storage/
│   └── logs/                          ← Application logs
│
├── .env                               ← Configuration
├── .env.example
├── setup-project.php                  ← Verification script
├── composer.json
├── artisan
└── README.md
```

---

## 🎯 FEATURES

### 1. PWA (Progressive Web App)

**Installation:**
- Click "Install App" button di navbar (Chrome/Edge/Brave)
- Atau: Menu → "Install app"
- Aplikasi akan tersimpan di home screen

**Offline Support:**
- Halaman yang sudah diakses bisa diakses offline
- Automatic caching strategy
- Fallback offline page

**Performance:**
- Cache-first untuk static assets
- Network-first untuk dynamic content
- Intelligent cache invalidation

### 2. Midtrans Payment Integration

**Payment Methods:**
- ✅ Bank Transfer (Manual & E-banking)
- ✅ E-wallet (GCash, OVO, Dana, LinkAja)
- ✅ Credit Card
- ✅ Installment (cicilan)

**Payment Flow:**
```
1. User books court
2. Click "Bayar Sekarang"
3. Midtrans Snap popup opens
4. User selects payment method
5. Payment processed
6. Webhook callback received
7. Booking status updated
```

**Security:**
- Server-side amount validation
- Signature key verification
- CSRF protection on callback
- Automatic logging

### 3. Admin Dashboard

**Features:**
- Manage courts (add/edit/delete)
- Manage time slots
- View all bookings
- View all payments
- Approve/reject payments

**Access:**
```
URL: http://localhost:8000/admin/dashboard
Username: admin@example.com
Password: password
```

---

## 📝 API ENDPOINTS

### Public Routes
```
GET  /                      → Home page
GET  /courts                → List all courts
GET  /track-booking         → Track booking by ID
POST /search-booking        → Search booking
GET  /booking/{court}       → Booking page
```

### Payment Routes
```
POST /payment/create-transaction    → Create Midtrans transaction
GET  /payment/{booking}             → Show payment page
POST /midtrans/callback             → Midtrans webhook (webhook validation)
GET  /check-status/{order_id}       → Check payment status
```

### Admin Routes (Requires Auth)
```
GET  /admin/dashboard               → Admin dashboard
GET  /admin/courts                  → Manage courts
GET  /admin/bookings                → Manage bookings
GET  /admin/payments                → Manage payments
```

---

## 🔧 CONFIGURATION

### .env Configuration

```env
# Application
APP_NAME=Padel Booking
APP_ENV=local               # 'local' = development, 'production' = live
APP_DEBUG=true              # 'true' = show errors, 'false' = hide errors
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=padel
DB_USERNAME=root
DB_PASSWORD=

# Midtrans (Sandbox for testing)
MIDTRANS_IS_PRODUCTION=false        # 'false' = Sandbox, 'true' = Production
MIDTRANS_SERVER_KEY=SB-Mid-server-XXX
MIDTRANS_CLIENT_KEY=SB-Mid-client-XXX
MIDTRANS_MERCHANT_ID=YOUR_MERCHANT_ID

# Cache & Session
CACHE_STORE=database        # 'database', 'redis', 'file'
SESSION_DRIVER=database     # 'database', 'cookie', 'redis'
```

### File yang Penting

**Service Worker:**
- `public/service-worker.js` - PWA caching logic
- Exclude payment routes dari cache
- Handle offline scenarios

**Middleware:**
- `app/Http/Middleware/VerifyCsrfToken.php`
- Exclude Midtrans callbacks dari CSRF check
- Why: External webhook can't include CSRF token

**Config:**
- `config/midtrans.php` - Midtrans configuration
- `config/app.php` - Laravel app config

---

## 🧪 TESTING

### Manual Testing

```bash
# 1. Create test booking
php artisan tinker
>>> $booking = App\Models\Booking::create([
...   'customer_name' => 'Test User',
...   'email' => 'test@example.com',
...   'phone' => '08123456789',
...   'booking_date' => '2026-05-01',
...   'start_time' => '08:00',
...   'end_time' => '09:00',
...   'total_price' => 100000
... ]);
>>> exit

# 2. Open payment page
# http://localhost:8000/payment/{booking_id}

# 3. Test payment through Midtrans Snap
# Complete payment flow in sandbox
```

### Automated Testing

```bash
# Run tests
php artisan test

# Run specific test
php artisan test --filter TestPaymentFlow

# Generate coverage report
php artisan test --coverage
```

---

## 🐛 TROUBLESHOOTING

### Issue: Service Worker Not Registered
**Solution:**
```bash
# 1. Check file exists
ls -la public/service-worker.js

# 2. Clear browser cache
# F12 → Application → Clear Site Data

# 3. Hard refresh
# Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
```

### Issue: Midtrans API 401 Error
**Solution:**
```bash
# 1. Verify .env keys
cat .env | grep MIDTRANS

# 2. Get correct keys from https://dashboard.midtrans.com
# Make sure you're using SANDBOX keys (not Production)

# 3. Restart server
php artisan serve
```

### Issue: Database Connection Error
**Solution:**
```bash
# 1. Start MySQL
# Mac: brew services start mysql
# Windows: net start MySQL80

# 2. Create database
mysql -u root -e "CREATE DATABASE padel;"

# 3. Run migrations
php artisan migrate
```

### Issue: Payment Status Not Updating
**Solution:**
```bash
# 1. Check logs
tail -f storage/logs/laravel.log | grep -i midtrans

# 2. Verify callback URL in Midtrans Dashboard
# Settings → Notification URL
# Should be: http://YOUR_DOMAIN/midtrans/callback

# 3. Test webhook manually (if needed)
curl -X POST http://localhost:8000/midtrans/callback \
  -H "Content-Type: application/json" \
  -d '{"order_id":"TEST-123","gross_amount":100000}'
```

**More help:** See `PWA_MIDTRANS_SETUP_GUIDE.md` for detailed troubleshooting

---

## 📊 DATABASE SCHEMA

### Bookings Table
```sql
id              | int (primary key)
customer_name   | string
email           | string
phone           | string
booking_date    | date
start_time      | time
end_time        | time
court_id        | int (foreign key)
total_price     | decimal
status          | enum (pending, approved, cancelled)
paid            | decimal (amount paid)
remaining       | decimal (amount remaining)
created_at      | timestamp
updated_at      | timestamp
```

### Payments Table
```sql
id                    | int (primary key)
booking_id            | int (foreign key)
order_id              | string (unique)
amount                | decimal
status                | enum (pending, settlement, failed, expired)
payment_type          | string (full, partial)
snap_token            | string
transaction_id        | string
gross_amount          | decimal
midtrans_response     | json
paid_at               | timestamp
created_at            | timestamp
updated_at            | timestamp
```

---

## 🔒 SECURITY NOTES

### ✅ What's Protected
- All payments validated server-side
- Midtrans signature verified
- CSRF tokens required (except callbacks)
- Sensitive data encrypted
- Audit logging enabled

### ❌ What NOT to Do
- Don't commit .env with real keys
- Don't expose Server Key to frontend
- Don't disable signature verification
- Don't bypass CSRF protection

---

## 📦 DEPENDENCIES

**Backend:**
- Laravel 10+
- PHP 8.0+
- MySQL 5.7+
- Midtrans SDK

**Frontend:**
- Bootstrap 5
- Font Awesome 6
- Native JavaScript (no jQuery)

**PWA:**
- Service Worker API
- Cache API
- Web Manifest

---

## 🚀 DEPLOYMENT

### Before Going Live
1. Change `MIDTRANS_IS_PRODUCTION=true`
2. Update Midtrans keys to production
3. Set `APP_DEBUG=false`
4. Set `APP_ENV=production`
5. Enable HTTPS
6. Update APP_URL to domain

### Deployment Platforms
- AWS (EC2 + RDS)
- DigitalOcean (App Platform)
- Heroku
- Shared hosting (cPanel)
- VPS

### Production Checklist
- [ ] SSL certificate installed
- [ ] HTTPS enforced
- [ ] Database backed up
- [ ] Logs configured
- [ ] Email notifications working
- [ ] Monitoring enabled
- [ ] CDN configured (optional)

---

## 📞 SUPPORT

### Documentation
- `PWA_MIDTRANS_SETUP_GUIDE.md` - Detailed setup & troubleshooting
- `VERIFICATION_QUICK_START.md` - Quick verification checklist
- `setup-project.php` - Automated verification

### Debug Mode
```bash
# Enable debug
APP_DEBUG=true
LOG_LEVEL=debug

# View logs
tail -f storage/logs/laravel.log
```

### Useful Commands
```bash
# Database
php artisan migrate              # Run migrations
php artisan migrate:rollback     # Rollback migrations
php artisan tinker               # Interactive shell

# Cache
php artisan cache:clear          # Clear cache
php artisan config:cache         # Cache config
php artisan view:cache           # Cache views

# Optimization
php artisan optimize             # Optimize app
php artisan route:cache          # Cache routes
```

---

## 📝 VERSION HISTORY

**v1.1.0** (April 22, 2026)
- ✅ Optimized service worker
- ✅ Added setup verification
- ✅ Comprehensive documentation
- ✅ CSRF exceptions for Midtrans

**v1.0.0** (March 28, 2026)
- ✅ Initial release
- ✅ PWA implementation
- ✅ Midtrans integration

---

## 📜 LICENSE

Proprietary - Padel Booking System

---

## 🎉 YOU'RE ALL SET!

**Next steps:**
1. Run: `php artisan serve`
2. Open: http://localhost:8000
3. Test booking flow
4. Test payment with Midtrans
5. Check admin dashboard

**Need help?**
- Check `PWA_MIDTRANS_SETUP_GUIDE.md`
- Run `php setup-project.php` to verify setup
- Check `storage/logs/laravel.log` for errors
- Press F12 in browser to check console

---

**Happy coding! 🚀**

*Last Updated: April 22, 2026*  
*Maintained with ❤️ by Development Team*
