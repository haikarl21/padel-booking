
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MidtransPaymentController;
use App\Http\Controllers\Auth\LoginController;

// ================= ADMIN PANEL =================
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Courts
    Route::resource('courts', \App\Http\Controllers\Admin\CourtController::class);
    Route::post('courts/{court}/change-status', [\App\Http\Controllers\Admin\CourtController::class, 'changeStatus'])->name('courts.change-status');

    // Time Slots
    Route::resource('timeslots', \App\Http\Controllers\Admin\TimeSlotController::class);

    // Bookings
    Route::resource('bookings', \App\Http\Controllers\Admin\BookingController::class)->only(['index', 'show', 'destroy']);
    Route::post('bookings/{booking}/payments/{payment}/approve', [\App\Http\Controllers\Admin\BookingController::class, 'approvePayment'])->name('bookings.payments.approve');

    // Payments Management (Custom Payment System)
    Route::get('/payments', [PaymentController::class, 'listPayments'])->name('payments');
    Route::get('/payment/{payment}/detail', [PaymentController::class, 'viewPaymentDetail'])->name('payment-detail');

    // Users
    Route::get('users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    // Profile (current admin)
    Route::get('profile', [\App\Http\Controllers\Admin\UserController::class, 'profile'])->name('profile');
    Route::post('profile', [\App\Http\Controllers\Admin\UserController::class, 'updateProfile'])->name('profile.update');
});

// Auth routes
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/courts', [HomeController::class, 'courts'])->name('courts');
Route::get('/track-booking', [HomeController::class, 'trackBooking'])->name('track-booking');
Route::post('/search-booking', [HomeController::class, 'searchBooking'])->name('search-booking');
Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

Route::get('/booking/{court}', [BookingController::class, 'show'])->name('booking.show');
Route::post('/booking/{court}/select-datetime', [BookingController::class, 'selectDateTime'])->name('booking.select-datetime');
Route::post('/booking/{court}/confirm', [BookingController::class, 'confirm'])->name('booking.confirm');
Route::get('/booking/{booking}/detail', [BookingController::class, 'detail'])->name('booking.detail');

// receipt for payments
Route::get('/booking/{booking}/receipt', [BookingController::class, 'receipt'])->name('booking.receipt');

// ================= MIDTRANS PAYMENT ROUTES (SNAP) =================
Route::post('/payment/create-transaction', [PaymentController::class, 'createTransaction'])->name('payment.create-transaction');
Route::post('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
Route::get('/check-status/{order_id}', [PaymentController::class, 'checkStatusFromMidtrans'])->name('check-status');

// Legacy route used by older payment UI/tests
Route::post('/payment/{booking}/process', [PaymentController::class, 'process'])->name('payment.process');

// OLD CUSTOM PAYMENT (KEEP FOR LEGACY)
Route::get('/payment/{booking}/select-method', [PaymentController::class, 'selectMethod'])->name('payment.select-method');
Route::post('/payment/{booking}/select-method', [PaymentController::class, 'storeMethod'])->name('payment.store-method');
Route::get('/payment/{booking}', [PaymentController::class, 'show'])->name('payment.show');
Route::get('/payment/{payment}/status', [PaymentController::class, 'getStatus'])->name('payment.get-status');

// Admin Payment Actions
Route::post('/payment/{payment}/approve', [PaymentController::class, 'approve'])->middleware(['auth'])->name('payment.approve');
Route::post('/payment/{payment}/reject', [PaymentController::class, 'reject'])->middleware(['auth'])->name('payment.reject');
Route::get('/admin/payments', [PaymentController::class, 'listPayments'])->middleware(['auth'])->name('admin.payments');
// Validasi security dilakukan via signature key verification di controller
Route::post('/midtrans/callback', [\App\Http\Controllers\MidtransCallbackController::class, 'handle'])->name('midtrans.callback');

// ROUTE SEMENTARA UNTUK RESET DATABASE DI RAILWAY
Route::get('/reset-database-sekarang', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
        return '<h1>BERHASIL!✅</h1><p>Database Railway Berhasil Direset. Jam sudah urut. Silakan kembali ke website Anda.</p>';
    } catch (\Exception $e) {
        return '<h1>GAGAL!❌</h1><p>Error: ' . $e->getMessage() . '</p>';
    }
});

