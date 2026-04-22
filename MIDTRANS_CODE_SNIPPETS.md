# 💻 MIDTRANS API REFERENCE & CODE SNIPPETS

**Ready-to-use code examples & API documentation**

---

## 🔧 CONFIGURATION SNIPPETS

### **Test Credentials (Sandbox)**
```php
// .env file
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SERVER_KEY=Mid-server-xxxxxxxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=Mid-client-xxxxxxxxxxxxxxxxx
MIDTRANS_MERCHANT_ID=M088508069

// Production (Replace these for go-live)
MIDTRANS_IS_PRODUCTION=true
MIDTRANS_SERVER_KEY=Mid-server-xxxxxxxxxxxxxxxxx  # Get from Midtrans dashboard
MIDTRANS_CLIENT_KEY=Mid-client-xxxxxxxxxxxxxxxxx   # Get from Midtrans dashboard
MIDTRANS_MERCHANT_ID=xxxxxxxxxx
```

### **Load Configuration (In Controller)**
```php
// Auto-loads from .env & config/midtrans.php
\Midtrans\Config::$serverKey = config('midtrans.server_key');
\Midtrans\Config::$clientKey = config('midtrans.client_key');
\Midtrans\Config::$isProduction = config('midtrans.is_production');
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;
```

---

## 🎟️ SNAP TOKEN GENERATION

### **Basic Snap Token Creation**
```php
use Midtrans\Snap;

$order_id = 'ORDER-' . now()->timestamp . '-' . Auth::id();
$amount = 350000;  // Rp 350,000

$transaction_details = array(
    'order_id' => $order_id,
    'gross_amount' => $amount,
);

$customer_details = array(
    'first_name' => Auth::user()->name,
    'email' => Auth::user()->email,
    'phone' => Auth::user()->phone ?? '',
);

$item_details = array(
    array(
        'id' => 'BOOKING-' . $booking->id,
        'price' => $amount,
        'quantity' => 1,
        'name' => 'Booking: ' . $booking->court->name,
    ),
);

$payload = array(
    'transaction_details' => $transaction_details,
    'customer_details' => $customer_details,
    'item_details' => $item_details,
);

try {
    $snapToken = Snap::getSnapToken($payload);
    return response()->json(['success' => true, 'snap_token' => $snapToken]);
} catch (\Exception $e) {
    return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
}
```

### **Snap Token dengan Custom Fields**
```php
$payload = array(
    'transaction_details' => array(
        'order_id' => $order_id,
        'gross_amount' => $amount,
    ),
    'customer_details' => array(
        'first_name' => $user->name,
        'last_name' => '',
        'email' => $user->email,
        'phone' => $user->phone,
        'billing_address' => array(
            'first_name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address ?? 'N/A',
            'city' => $user->city ?? 'Jakarta',
            'postal_code' => $user->postal_code ?? '10000',
            'country_code' => 'IDN'
        ),
    ),
    'item_details' => array(
        array(
            'id' => 'BOOKING-' . $booking->id,
            'price' => $amount,
            'quantity' => 1,
            'name' => 'Lapangan - ' . $booking->court->name,
            'merchant_name' => 'Padel Booking',
        ),
    ),
    // Optional: Restrict payment methods
    'enabled_payments' => ['qris', 'bank_transfer', 'gopay', 'ovo'],
    // Or disable certain methods
    'disabled_payments' => [], // Leave empty to allow all
    // Optional: Set expiry (in seconds)
    'expiry' => array(
        'start_time' => now()->toIso8601String(),
        'unit' => 'minute',
        'duration' => 30  // 30 minute expiry
    ),
    // Optional: Custom fields
    'custom_field1' => 'booking_' . $booking->id,
    'custom_field2' => 'user_' . $user->id,
    'custom_field3' => $booking->court->name,
);

$snapToken = Snap::getSnapToken($payload);
```

---

## 📲 FRONTEND JAVASCRIPT

### **Snap Library Loading (Guaranteed)**
```javascript
// This waits for Snap.js to fully load before attempting to call snap.pay()
function initMidtrans(callback) {
    if (typeof snap !== 'undefined') {
        // Already loaded
        if (callback) callback();
        return;
    }
    
    // Wait for it to load
    var checkCount = 0;
    var maxChecks = 50; // 5 seconds max wait
    var checkInterval = setInterval(function() {
        checkCount++;
        if (typeof snap !== 'undefined') {
            clearInterval(checkInterval);
            if (callback) callback();
        } else if (checkCount >= maxChecks) {
            clearInterval(checkInterval);
            alert('Gagal memuat Midtrans. Silakan refresh halaman.');
        }
    }, 100);
}
```

### **Snap Payment Call**
```javascript
function showSnapPayment(snapToken) {
    initMidtrans(function() {
        snap.pay(snapToken, {
            onSuccess: function(result) {
                console.log('Payment success:', result);
                // Redirect to finish page
                window.location.href = '/payment/finish?order_id=' + result.order_id;
            },
            onPending: function(result) {
                console.log('Payment pending:', result);
                // Show pending message & start polling
                showAlert('Pembayaran sedang diproses. Mohon tunggu...', 'info');
                window.location.href = '/payment/finish?order_id=' + result.order_id;
            },
            onError: function(result) {
                console.log('Payment error:', result);
                showAlert('Pembayaran gagal: ' + (result.status_message || 'Error'), 'danger');
                // Allow retry
            },
            onClose: function() {
                console.log('Snap popup closed by user');
                showAlert('Form pembayaran ditutup. Silakan coba lagi.', 'warning');
            }
        });
    });
}
```

### **Fetch Snap Token via AJAX**
```javascript
function fetchSnapToken(bookingId, paymentType, paymentMethod) {
    return fetch('/payment/snap-token', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            booking_id: bookingId,
            payment_type: paymentType,  // 'full' atau 'partial'
            payment_method: paymentMethod, // 'qris', 'bank_transfer', etc
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            return data.snap_token;
        } else {
            throw new Error(data.error || 'Failed to generate token');
        }
    })
    .catch(error => {
        console.error('Error fetching token:', error);
        showAlert('Gagal generate payment token: ' + error.message, 'danger');
        throw error;
    });
}
```

### **Complete Flow**
```javascript
async function processPayment() {
    try {
        // Show loading
        document.getElementById('payBtn').disabled = true;
        document.getElementById('payBtn').textContent = 'Loading...';
        
        // Get snap token
        const snapToken = await fetchSnapToken(
            bookingId,
            'full',  // Payment type
            'qris'   // Payment method
        );
        
        // Show payment popup
        showSnapPayment(snapToken);
        
    } catch (error) {
        console.error('Payment process error:', error);
    } finally {
        document.getElementById('payBtn').disabled = false;
        document.getElementById('payBtn').textContent = 'Bayar Sekarang';
    }
}

// Call when user clicks pay button
document.getElementById('payBtn').addEventListener('click', processPayment);
```

---

## 🔐 WEBHOOK CALLBACK HANDLING

### **Verify Signature (Essential for Security)**
```php
// In MidtransPaymentController::callback()

private function verifySignature($request, $serverKey)
{
    $order_id = $request->input('order_id');
    $status_code = $request->input('status_code');
    $gross_amount = $request->input('gross_amount');
    $signature_key = $request->input('signature_key');
    
    // Calculate expected signature
    $my_signature = hash('sha512', $order_id . $status_code . $gross_amount . $serverKey);
    
    // Compare with incoming signature
    if ($signature_key !== $my_signature) {
        Log::warning('Signature mismatch for order: ' . $order_id);
        return false;
    }
    
    return true;
}

// Usage in callback method
public function callback(Request $request)
{
    $serverKey = config('midtrans.server_key');
    
    if (!$this->verifySignature($request, $serverKey)) {
        abort(403, 'Invalid signature');
    }
    
    // Continue processing...
}
```

### **Process Callback Data**
```php
public function callback(Request $request)
{
    $order_id = $request->input('order_id');
    $transaction_status = $request->input('transaction_status');
    $fraud_status = $request->input('fraud_status');
    $gross_amount = $request->input('gross_amount');
    
    // Verify signature
    if (!$this->verifySignature($request, config('midtrans.server_key'))) {
        abort(403, 'Invalid signature');
    }
    
    // Find payment
    $payment = Payment::where('order_id', $order_id)->first();
    if (!$payment) {
        Log::error('Payment not found for order: ' . $order_id);
        return response()->json(['status' => 'not_found'], 404);
    }
    
    // Update payment status based on transaction_status
    $payment_status = 'pending';
    if ($transaction_status == 'capture') {
        if ($fraud_status == 'challenge') {
            $payment_status = 'pending';  // Challenge by bank
        } else {
            $payment_status = 'paid';     // Auto-approved
        }
    } else if ($transaction_status == 'settlement') {
        $payment_status = 'paid';         // Confirmed
    } else if ($transaction_status == 'pending') {
        $payment_status = 'pending';      // Waiting (e.g., bank transfer)
    } else if (in_array($transaction_status, ['deny', 'cancel', 'expire'])) {
        $payment_status = 'rejected';
    }
    
    // Update payment
    $payment->update([
        'transaction_status' => $transaction_status,
        'fraud_status' => $fraud_status,
        'status' => $payment_status,
        'paid_at' => in_array($payment_status, ['paid']) ? now() : null,
    ]);
    
    // Update booking status if payment successful
    if ($payment_status == 'paid') {
        $payment->booking->update(['status' => 'approved']);
        
        // Send confirmation email
        // Mail::send(new PaymentConfirmed($payment));
    }
    
    Log::info('Callback processed for order: ' . $order_id . ' | Status: ' . $payment_status);
    
    // Respond to Midtrans (must return 200 OK)
    return response()->json(['status' => 'success']);
}
```

---

## 🔍 STATUS CHECKING

### **Check Status from Midtrans API**
```php
use Midtrans\Transaction;

public function checkStatus(Payment $payment)
{
    try {
        // Configure Midtrans first
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$clientKey = config('midtrans.client_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        
        // Get status from Midtrans
        $status = Transaction::status($payment->order_id);
        
        // Update our record
        $payment->update([
            'transaction_status' => $status->transaction_status,
            'fraud_status' => $status->fraud_status ?? null,
        ]);
        
        // Determine payment status
        if ($status->transaction_status == 'settlement' || $status->transaction_status == 'capture') {
            $payment->update(['status' => 'paid']);
        } else if (in_array($status->transaction_status, ['deny', 'cancel', 'expire'])) {
            $payment->update(['status' => 'rejected']);
        }
        
        return response()->json([
            'status' => $payment->status,
            'transaction_status' => $status->transaction_status,
            'fraud_status' => $status->fraud_status ?? null,
        ]);
    } catch (\Exception $e) {
        Log::error('Error checking status: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
```

---

## 📊 PAYMENT STATUS CONSTANTS

### **Payment Status Values**
```php
// In Payment Model or Constants
const STATUS_PENDING = 'pending';           // Awaiting payment
const STATUS_PAID = 'paid';                 // Payment confirmed
const STATUS_REJECTED = 'rejected';         // Payment failed/denied
const STATUS_EXPIRED = 'expired';           // Payment window closed

// Transaction status from Midtrans
const TRANSACTION_PENDING = 'pending';
const TRANSACTION_CAPTURE = 'capture';      // Auto-debit (e.g., e-wallet)
const TRANSACTION_SETTLEMENT = 'settlement'; // Confirmed
const TRANSACTION_DENY = 'deny';
const TRANSACTION_CANCEL = 'cancel';
const TRANSACTION_EXPIRE = 'expire';

// Fraud status from Midtrans (for credit card)
const FRAUD_ACCEPT = 'accept';
const FRAUD_CHALLENGE = 'challenge';        // Need manual review
const FRAUD_DENY = 'deny';
```

---

## 🎯 PAYMENT METHODS

### **Enable/Disable Specific Methods**
```php
// In Snap token generation

$payload = array(
    // ... other fields ...
    
    // Allow only QRIS and Bank Transfer
    'enabled_payments' => ['qris', 'bank_transfer'],
    
    // Or disable specific methods
    'disabled_payments' => ['credit_card', 'bca_klikbca'],
);

// Available payment methods:
/*
- qris : QRIS
- bank_transfer : Semua Virtual Account (BCA, BRI, Mandiri, Permata, CIMB)
- bca_klikbca : BCA Klik BCA
- bca_klikpay : BCA Klik Pay
- bri_epay : BRI e-Pay
- echannel : Mandiri & CIMB
- permata : Permata
- cimb : CIMB
- gopay : GoPay
- ovo : OVO
- shopeepay : Shopeepay
- dana : Dana
- linkaja : Link Aja
- credit_card : Kartu Kredit
- akulaku : Akulaku
- twentyfour : 24 Installment
*/
```

### **Check Enabled Methods in Response**
```php
// After generating snap token, response includes available methods
{
    "token": "xxx",
    "redirect_url": "...",
    "available_methods": ["qris", "bank_transfer", "gopay", "ovo"]
}
```

---

## 🆘 ERROR HANDLING

### **Try-Catch with Proper Error Logging**
```php
try {
    // Normalize token generation
    \Midtrans\Config::$serverKey = config('midtrans.server_key');
    \Midtrans\Config::$clientKey = config('midtrans.client_key');
    \Midtrans\Config::$isProduction = config('midtrans.is_production');
    \Midtrans\Config::$isSanitized = true;
    \Midtrans\Config::$is3ds = true;
    
    // Generate token
    $snapToken = \Midtrans\Snap::getSnapToken($payload);
    
    return response()->json([
        'success' => true,
        'snap_token' => $snapToken,
    ]);
    
} catch (\Midtrans\Exception $e) {
    Log::error('Midtrans Exception', [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'trace' => $e->getTraceAsString(),
    ]);
    
    return response()->json([
        'success' => false,
        'error' => 'Failed to generate payment token: ' . $e->getMessage(),
    ], 500);
    
} catch (\Exception $e) {
    Log::error('Unexpected Exception', [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'trace' => $e->getTraceAsString(),
    ]);
    
    return response()->json([
        'success' => false,
        'error' => 'An unexpected error occurred',
    ], 500);
}
```

---

## 📧 EMAIL NOTIFICATIONS

### **Auto-Send Confirmation Email**
```php
// In callback() when payment successful

use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentConfirmed;

if ($payment_status == 'paid') {
    // Send email
    Mail::to($payment->booking->user->email)->send(new PaymentConfirmed($payment));
    
    Log::info('Confirmation email sent to ' . $payment->booking->user->email);
}

// In app/Mail/PaymentConfirmed.php:
class PaymentConfirmed extends Mailable {
    public function build() {
        return $this->view('emails.payment-confirmed')
            ->subject('Pembayaran Berhasil - Lapangan Padel Booking')
            ->with([
                'payment' => $this->payment,
                'booking' => $this->payment->booking,
                'receipt_url' => route('payment.receipt', $this->payment->id),
            ]);
    }
}
```

---

## 🔄 RESTART & CLEANUP

### **If Something Goes Wrong**
```bash
# Clear config cache
php artisan config:clear
php artisan config:cache

# Clear view cache
php artisan view:clear

# Restart tinker session
# Just quit and start new

# Reset database (⚠️ WARNING: Deletes all data!)
php artisan migrate:reset
php artisan migrate:fresh --seed

# Kill running server & restart
# Kill terminal, start new: php artisan serve
```

---

## 📚 ADDITIONAL RESOURCES

### **Official Documentation**
- https://docs.midtrans.com/en/snap/overview
- https://docs.midtrans.com/en/api-reference
- https://docs.midtrans.com/en/snap/integration-guide

### **SDK Documentation**
- https://github.com/Midtrans/midtrans-php

### **Test Cards & Methods**
- https://docs.midtrans.com/en/technical-reference/sandbox-test-payment

### **Sandbox Dashboard**
- https://dashboard.sandbox.midtrans.com

---

**💡 Tip: Copy-paste snippets as needed & test in isolation before integrating into main code**

