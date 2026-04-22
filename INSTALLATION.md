# Complete Installation Guide

## System Requirements

- **PHP**: 8.2 or higher
- **Composer**: Latest version
- **Node.js**: 18.0 or higher
- **NPM**: 9.0 or higher
- **MySQL**: 5.7 or higher
- **Git**: Required for cloning (optional)

## Step-by-Step Installation

### Step 1: Navigate to Project Directory

```bash
cd c:\TA\Padel\padel-booking
```

### Step 2: Install Composer Dependencies

This will install all PHP packages including Laravel.

```bash
composer install
```

**Expected Output:**
```
Loading composer repositories with package definitions...
Installing dependencies...
[Success] All packages installed successfully!
```

### Step 3: Install NPM Dependencies

This will install JavaScript libraries including Tailwind CSS and Vite.

```bash
npm install
```

**Expected Output:**
```
up to date, audited X packages in Xs
found 0 vulnerabilities
```

### Step 4: Verify .env File

Check that `.env` file exists with database configuration:

```bash
type .env
```

Expected content includes:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=padel
DB_USERNAME=root
DB_PASSWORD=
```

### Step 5: Create Database

Using MySQL, create the database:

```bash
mysql -u root
```

Then in MySQL console:
```sql
CREATE DATABASE padel;
EXIT;
```

### Step 6: Run Migrations

Create all necessary tables in the database:

```bash
php artisan migrate
```

**Expected Output:**
```
Migration Table Created Successfully
Migrated: 2014_10_12_000000_create_users_table
Migrated: 2014_10_12_100000_create_password_resets_table
Migrated: 2014_10_12_200000_create_failed_jobs_table
Migrated: 2026_03_05_000001_create_courts_table
Migrated: 2026_03_05_000002_create_time_slots_table
Migrated: 2026_03_05_000003_create_bookings_table
Migrated: 2026_03_05_000004_create_payments_table
```

### Step 7: Seed Sample Data

Load example courts and time slots:

```bash
php artisan db:seed
```

**Expected Output:**
```
Seeding: Database\Seeders\DatabaseSeeder
Seeded:  Database\Seeders\CourtSeeder
Seeded:  Database\Seeders\TimeSlotSeeder
```

### Step 8: Build Frontend Assets

Compile CSS and JavaScript:

```bash
npm run build
```

**Expected Output:**
```
vite v7.0.7 building for production...
✓ 1 modules transformed.
built in 0.45s
```

### Step 9: Start Development Server

Launch the Laravel development server:

```bash
php artisan serve
```

**Expected Output:**
```
   Local: http://127.0.0.1:8000
   Press Ctrl+C to stop the server
```

### Step 10: Access the Application

Open your browser and visit:

```
http://localhost:8000
```

You should see the CourtElite landing page! 🎉

---

## Database Seeding

After migration, your database will have:

### Courts Seeded
| Name | Price | Status |
|------|-------|--------|
| Lapangan A | Rp 100,000/hour | Available |
| Lapangan B | Rp 120,000/hour | Available |
| Lapangan C | Rp 80,000/hour | Available |

### Time Slots Seeded
- 09:00 - 10:00
- 11:00 - 12:00
- 13:00 - 14:00
- 15:00 - 16:00
- 17:00 - 18:00
- 19:00 - 20:00
- 20:00 - 21:00

---

## Pages & Features

### 1. Landing Page
**URL**: `http://localhost:8000/`
**Features**:
- Hero heading
- Call-to-action buttons
- Statistics display (10+ Courts, 500+ Members, 99% Satisfaction, 24/7 Booking)

### 2. Courts Listing
**URL**: `http://localhost:8000/courts`
**Features**:
- Browse all available courts
- Court images
- Pricing information
- "Book Now" buttons

### 3. Book Court (Date Selection)
**URL**: `http://localhost:8000/booking/{court}`
**Features**:
- Select booking date
- View court details
- Booking summary

### 4. Date & Time Selection
**URL**: `http://localhost:8000/booking/{court}/select-datetime`
**Features**:
- Choose time slot
- Enter customer name
- Enter customer phone
- Confirm booking details

### 5. Payment
**URL**: `http://localhost:8000/payment/{booking}`
**Features**:
- View booking reference code
- Select payment type (Full/Partial)
- Choose payment method (Bank Transfer, QRIS, BCA, BRI or Cash)
- For QRIS a QR image is displayed; payment is auto‑completed with no upload required
- For bank transfers the BCA/BRI account numbers (configured via `.env`) are shown and proof upload is required
- Upload payment proof (JPG/PNG/PDF only, max 5 MB)
- Copy booking reference


**Environment variables**:
```
BANK_BCA=1234567890   # your BCA account
BANK_BRI=0987654321   # your BRI account
QRIS_IMAGE=images/qris.png  # path under public/ for the QRIS code image
```

### 6. Booking Details
**URL**: `http://localhost:8000/booking/{booking}/detail`
**Features**:
- View complete booking information
- Check payment status
- See payment history
- Confirm booking reference

---

## Development Workflow

### During Development (Watch for Changes)
```bash
npm run dev
```

This will watch for CSS and JS changes and rebuild automatically.

### For Production Build
```bash
npm run build
```

This creates optimized, minified assets.

---

## Database Reset (If Needed)

⚠️ **WARNING**: This will delete all data!

```bash
php artisan migrate:fresh --seed
```

This will:
1. Drop all tables
2. Re-run all migrations
3. Re-seed the database

---

## Common Issues & Solutions

### 1. "SQLSTATE[HY000] [2002] No such file or directory"
**Problem**: MySQL service not running
**Solution**: 
- Windows: Start MySQL from Services or XAMPP Control Panel
- macOS: `brew services start mysql`
- Linux: `sudo systemctl start mysql`

### 2. "Class 'PDO' not found"
**Problem**: PHP PDO extension not enabled
**Solution**: 
- Check `php.ini` and uncomment `;extension=pdo_mysql`
- Restart your web server

### 3. Port 8000 Already in Use
**Problem**: Another app using port 8000
**Solution**:
```bash
php artisan serve --port=8001
```
Then visit `http://localhost:8001`

### 4. "npm: command not found"
**Problem**: Node.js/NPM not installed
**Solution**:
- Download from https://nodejs.org
- Install Node.js (includes NPM)

### 5. CSRF Token Mismatch
**Problem**: Session issues
**Solution**:
```bash
php artisan migrate:fresh --seed
```

### 6. Assets Not Loading
**Problem**: CSS/JS not compiled
**Solution**:
```bash
npm run build
php artisan serve
```

---

## File Permissions

If you get permission errors on Linux/macOS:

```bash
chmod -R 775 storage bootstrap/cache
chmod -R 775 storage/app/public
```

---

## Viewing Database

### Using MySQL CLI
```bash
mysql -u root padel
```

Then you can run queries:
```sql
SHOW TABLES;
SELECT * FROM courts;
SELECT * FROM time_slots;
```

### Using phpMyAdmin
If you have phpMyAdmin installed, visit:
```
http://localhost/phpmyadmin
```

---

## Complete Quick Setup (Copy-Paste)

If you want to run all commands at once:

```bash
cd c:\TA\Padel\padel-booking && ^
composer install && ^
npm install && ^
php artisan migrate && ^
php artisan db:seed && ^
npm run build && ^
php artisan serve
```

Then open http://localhost:8000 in your browser.

---

## Verification Checklist

After installation, verify:

- [ ] No errors during `composer install`
- [ ] No errors during `npm install`
- [ ] No errors during `php artisan migrate`
- [ ] No errors during `php artisan db:seed`
- [ ] No errors during `npm run build`
- [ ] Server starts with `php artisan serve`
- [ ] Landing page loads at http://localhost:8000
- [ ] Can navigate to /courts
- [ ] Can select a court and see booking form
- [ ] Database has 3 courts
- [ ] Database has 7 time slots

---

## Next Steps

1. ✅ Installation complete
2. 🧭 Explore routes in `routes/web.php`
3. 🎨 Customize colors in CSS if needed
4. 🚀 Deploy to production when ready

---

## Support Resources

- **Laravel Docs**: https://laravel.com/docs
- **Tailwind CSS**: https://tailwindcss.com/docs
- **Laravel Blade**: https://laravel.com/docs/12.x/blade
- **MySQL Docs**: https://dev.mysql.com/doc

---

## Success! 🎉

Your CourtElite booking system is ready to use!

Start by visiting: **http://localhost:8000**

Enjoy booking badminton courts! 🎾
