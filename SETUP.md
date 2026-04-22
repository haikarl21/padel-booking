# CourtElite - Badminton Court Booking System

A complete Laravel-based booking system for professional badminton courts with modern styling and a seamless user experience.

## Features

✅ **Landing Page** - Beautiful hero section with court statistics
✅ **Court Listing** - Browse available courts with pricing
✅ **Booking Flow** - Multi-step booking process:
   - Select court
   - Choose date and time slot
   - Enter customer details
   - Payment processing
✅ **Payment Management** - Full/partial payment options
✅ **Booking Confirmation** - View booking details and payment history

## Technology Stack

- **Framework**: Laravel 12
- **Frontend**: Tailwind CSS 4.0
- **Database**: MySQL
- **Build Tool**: Vite
- **PHP**: 8.2+

## Project Structure

```
padel-booking/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── HomeController.php
│   │       ├── BookingController.php
│   │       └── PaymentController.php
│   ├── Models/
│   │   ├── Court.php
│   │   ├── TimeSlot.php
│   │   ├── Booking.php
│   │   └── Payment.php
│   └── Providers/
├── database/
│   ├── migrations/
│   │   ├── create_courts_table.php
│   │   ├── create_time_slots_table.php
│   │   ├── create_bookings_table.php
│   │   └── create_payments_table.php
│   └── seeders/
│       ├── CourtSeeder.php
│       ├── TimeSlotSeeder.php
│       └── DatabaseSeeder.php
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   └── app.blade.php
│   │   ├── welcome.blade.php
│   │   ├── courts/
│   │   │   └── index.blade.php
│   │   ├── booking/
│   │   │   ├── show.blade.php
│   │   │   ├── select-datetime.blade.php
│   │   │   └── detail.blade.php
│   │   └── payment/
│   │       └── show.blade.php
│   ├── css/
│   │   └── app.css
│   └── js/
│       └── app.js
├── routes/
│   └── web.php
└── config/
```

## Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js and npm
- MySQL

### Step 1: Install Dependencies

```bash
cd c:\TA\Padel\padel-booking
composer install
npm install
```

### Step 2: Build Frontend Assets

```bash
npm run build
```

### Step 3: Database Setup

```bash
# Create database (MySQL)
php artisan migrate

# Seed example data
php artisan db:seed
```

### Step 4: Run Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## Database Schema

### Courts Table
- `id` - Primary key
- `name` - Court name
- `slug` - URL-friendly identifier
- `description` - Court details
- `image_path` - Court image
- `price_per_hour` - Hourly rate
- `status` - available/maintenance
- `timestamps`

### Time Slots Table
- `id` - Primary key
- `start_time` - Slot start time
- `end_time` - Slot end time
- `display_text` - Display format (e.g., "09:00 - 10:00")
- `timestamps`

### Bookings Table
- `id` - Primary key
- `booking_code` - Unique booking code
- `court_id` - Foreign key to courts
- `time_slot_id` - Foreign key to time_slots
- `date` - Date of booking
- `customer_name` - Customer name
- `phone` - Customer phone
- `total_price` - Total cost
- `status` - pending/confirmed/completed/cancelled
- `timestamps`

### Payments Table
- `id` - Primary key
- `booking_id` - Foreign key to bookings
- `amount` - Payment amount
- `payment_type` - full/partial
- `payment_method` - bank_transfer/cash
- `proof_file_path` - Receipt file
- `status` - pending/completed/rejected
- `timestamps`

## Routes

| Method | Route | Controller | Name |
|--------|-------|-----------|------|
| GET | / | HomeController@index | home |
| GET | /courts | HomeController@courts | courts |
| GET | /booking/{court} | BookingController@show | booking.show |
| POST | /booking/{court}/select-datetime | BookingController@selectDateTime | booking.select-datetime |
| POST | /booking/{court}/confirm | BookingController@confirm | booking.confirm |
| GET | /booking/{booking}/detail | BookingController@detail | booking.detail |
| GET | /payment/{booking} | PaymentController@show | payment.show |
| POST | /payment/{booking}/process | PaymentController@process | payment.process |

## Booking Workflow

1. **Landing Page** (`/`) - User views CourtElite homepage
2. **Court Selection** (`/courts`) - User browses available courts
3. **Court Details** (`/booking/{court}`) - User selects court and date
4. **Time Selection** (`/booking/{court}/select-datetime`) - User chooses time and enters details
5. **Confirmation** (POST to `/booking/{court}/confirm`) - Booking is created
6. **Payment** (`/payment/{booking}`) - User selects payment method
7. **Completion** (POST to `/payment/{booking}/process`) - Payment processed
8. **Details** (`/booking/{booking}/detail`) - View booking confirmation

## Seeded Data

### Courts
- **Lapangan A** - Rp 100,000/hour
- **Lapangan B** - Rp 120,000/hour  
- **Lapangan C** - Rp 80,000/hour

### Time Slots
- 09:00 - 10:00
- 11:00 - 12:00
- 13:00 - 14:00
- 15:00 - 16:00
- 17:00 - 18:00
- 19:00 - 20:00
- 20:00 - 21:00

## Styling

The project uses **Tailwind CSS 4.0** with a dark theme featuring:
- Dark background (#0a0a0a / #111827)
- Gold/Amber accents (#fbbf24 / #f59e0b)
- Clean, modern design
- Responsive layout (mobile, tablet, desktop)

### Color Scheme
- **Primary**: Amber (#fbbf24)
- **Background**: Dark Gray (#0f172a / #111827)
- **Text**: White (#ffffff / #f3f4f6)
- **Borders**: Gray (#3f4652 / #4b5563)

## Development

### Watch CSS Changes
```bash
npm run dev
```

### Build for Production
```bash
npm run build
```

## File Upload Storage

Payment proofs are stored in:
```
storage/app/public/payments/
```

Make sure the directory exists and is writeable.

## Error Handling

The application includes proper validation:
- Date must be today or later
- Time slot must exist
- Customer details required
- Payment file optional but validated

## Future Enhancements

- User authentication and accounts
- Booking history
- Email notifications
- SMS reminders
- Admin dashboard
- Court availability calendar
- Multi-day bookings
- Booking cancellation
- Refund processing
- Analytics and reports

## Support

For issues and questions, please check the Laravel documentation at https://laravel.com/docs
