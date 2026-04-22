# 🎾 CourtElite - Badminton Court Booking System

Welcome! Your complete badminton court booking system has been created.

## 📋 What's Been Built?

A fully functional, modern web application for booking professional badminton courts with:

✅ Beautiful landing page with hero section
✅ Court listing with images and pricing
✅ Multi-step booking workflow
✅ Customer information capture
✅ Payment processing system
✅ Booking confirmation and details
✅ Dark theme with amber accents
✅ Fully responsive design
✅ Database with 3 sample courts
✅ 7 predefined time slots

---

## 🚀 Quick Start (5 Minutes)

### Copy-Paste These Commands:

```bash
cd c:\TA\Padel\padel-booking
composer install
npm install
php artisan migrate
php artisan db:seed
npm run build
php artisan serve
```

Then open: **http://localhost:8000** ⭕

That's it! Your app is running.

---

## 📁 Documentation Files Created

| File | Purpose |
|------|---------|
| **INSTALLATION.md** | 📝 Step-by-step setup guide with troubleshooting |

Start with **INSTALLATION.md** for detailed setup.

---

## 📂 Project Structure

```
padel-booking/
├── 🎨 resources/
│   └── views/
│       ├── welcome.blade.php           (Landing page)
│       ├── courts/index.blade.php      (Court listing)
│       ├── booking/
│       │   ├── show.blade.php          (Select date)
│       │   ├── select-datetime.blade.php (Select time)
│       │   └── detail.blade.php        (Booking confirmation)
│       ├── payment/show.blade.php      (Payment form)
│       └── layouts/app.blade.php       (Main layout)
│
├── 🔧 app/
│   ├── Http/Controllers/
│   │   ├── HomeController.php
│   │   ├── BookingController.php
│   │   └── PaymentController.php
│   └── Models/
│       ├── Court.php
│       ├── TimeSlot.php
│       ├── Booking.php
│       └── Payment.php
│
├── 💾 database/
│   ├── migrations/    (4 table schemas)
│   └── seeders/       (Sample data)
│
└── 📖 routes/web.php  (All routes)
```

---

## 🎯 Pages & Routes

| Page | URL | Purpose |
|------|-----|---------|
| 🏠 Landing | `/` | Hero section |
| 🏸 Courts | `/courts` | Browse courts |
| 📅 Booking | `/booking/{court}` | Select date |
| ⏰ DateTime | `/booking/{court}/select-datetime` | Select time |
| 💳 Payment | `/payment/{booking}` | Pay |
| ✅ Details | `/booking/{booking}/detail` | Confirmation |

---

## 🗄️ Database

Four tables automatically created:

1. **courts** - Stores court information
2. **time_slots** - Available booking times
3. **bookings** - Customer bookings
4. **payments** - Payment records

### Sample Data
- 3 courts (Lapangan A, B, C)
- 7 time slots (09:00 - 21:00)
- Automatically seeded with `php artisan db:seed`

---

## 🎨 Design

**Dark Theme** with **Amber Accents**
- Background: Very Dark Blue (#0f172a)
- Primary Color: Amber (#fbbf24)
- Text: White (#ffffff)
- Fully Responsive

Built with **Tailwind CSS 4.0** - no custom CSS needed!

---

## 🛠️ Technology Stack

| Component | Technology |
|-----------|-----------|
| Backend | Laravel 12 |
| Database | MySQL |
| Frontend | Tailwind CSS 4.0 |
| Build Tool | Vite |
| PHP Version | 8.2+ |
| Templating | Blade |

---

## ⚙️ Installation Summary

The system is set up to:
1. ✅ Run migrations automatically
2. ✅ Seed sample data automatically
3. ✅ Compile CSS/JS with Vite
4. ✅ Serve on http://localhost:8000

No additional configuration needed!

---

## 📝 Workflow

```
User Landing Page
        ↓
    Browse Courts
        ↓
    Select Court & Date
        ↓
    Choose Time Slot
        ↓
    Enter Customer Info
        ↓
    Review & Confirm
        ↓
    Select Payment Method
        ↓
    Upload Payment Proof (Optional)
        ↓
    Payment Processed
        ↓
    View Booking Details
```

---

## 🔑 Key Features

✨ **Multi-Step Booking**
- Guided workflow from court selection to confirmation
- Form validation at each step
- Clear confirmation codes

💳 **Payment Options**
- Full or Partial payment
- Bank transfer (generic, BCA, BRI), QRIS, or cash
- QRIS displays code and auto‑completes without upload
- Bank transfers show account numbers and require proof (
  JPG/PNG/PDF only, max 5 MB)
- Receipt link is provided immediately upon submission
- Payment status tracking via history

📱 **Responsive Design**
- Mobile-friendly interfaces
- Works on phones, tablets, desktops
- Touch-optimized buttons

🎨 **Modern UI**
- Dark theme reduces eye strain
- Clear hierarchy and spacing
- Smooth transitions
- Good visual feedback

---

## 📚 Files You Should Know About

**To Run:**
- `artisan` - Main command file
- `routes/web.php` - All URL routes

**To Customize Views:**
- `resources/views/layouts/app.blade.php` - Main layout
- `resources/css/app.css` - CSS configuration

**Controllers (Business Logic):**
- `app/Http/Controllers/HomeController.php`
- `app/Http/Controllers/BookingController.php`
- `app/Http/Controllers/PaymentController.php`

**Database:**
- `database/migrations/` - Table schemas
- `database/seeders/` - Sample data

---

## 🎓 Learning Path

1. **First Time?** → Read INSTALLATION.md
2. **Build Features** → Explore `routes/web.php` and controllers
3. **Customize UI** → Edit files in `resources/views/` and `resources/css/`

---

## ❓ Common Commands

```bash
# Start server
php artisan serve

# Rebuild database with fresh data
php artisan migrate:fresh --seed

# Watch CSS changes (development)
npm run dev

# Build for production
npm run build

# Run all migrations
php artisan migrate

# Seed sample data
php artisan db:seed
```

---

## 🐛 Issues?

**Server won't start?**
→ Make sure port 8000 is not in use
→ Try: `php artisan serve --port=8001`

**Database errors?**
→ Make sure MySQL is running
→ Check `.env` file has correct credentials

**Assets not showing?**
→ Run: `npm run build`
→ Then restart server

**More help?** → Check INSTALLATION.md troubleshooting section

---

## ✅ Verification

After installation, you should see:
1. ✅ No errors in terminal
2. ✅ Server running on localhost:8000
3. ✅ CourtElite landing page loads
4. ✅ Can click "Dashboard" to see courts
5. ✅ Can select a court and see booking form

---

## 🚀 Next Steps

```
1. Installation
   ↓
2. Browse http://localhost:8000
   ↓
3. Complete a test booking
   ↓
4. Explore the code
   ↓
5. Customize as needed
   ↓
6. Deploy when ready
```

---

## 📞 Support Documents

- **INSTALLATION.md** - Detailed setup with all steps

---

## 🎉 Ready?

Run these commands now:

```bash
cd c:\TA\Padel\padel-booking
composer install && npm install && php artisan migrate && php artisan db:seed && npm run build && php artisan serve
```

Then visit: **http://localhost:8000** 🎾

---

**Status**: ✅ Complete & Ready to Use
**Created**: March 5, 2026
**Framework**: Laravel 12 + Tailwind CSS 4.0
**License**: MIT

Enjoy your badminton court booking system! 🏸
