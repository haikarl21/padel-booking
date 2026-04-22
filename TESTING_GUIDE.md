# 🧪 MIDTRANS PAYMENT TESTING GUIDE

**Quick Start untuk Test Payment Flow** 

---

## ⚡ QUICK START (5 MINUTES)

### **Step 1: Start Server**
```bash
cd c:\TA\Padel\padel-booking
php artisan serve
# Output: Server running on [http://127.0.0.1:8000]
```

### **Step 2: Test Payment**
1. Open browser: http://127.0.0.1:8000
2. Login dengan akun user manapun
3. Buat booking baru OR pilih existing booking
4. Click "Bayar Sekarang" di booking detail
5. Select "Pembayaran Penuh"
6. Select metode: **QRIS** (paling cepat)
7. **Snap popup harus muncul** ✅

### **Step 3: Verify Popup Behavior**

**Jika popup MUNCUL ✅:**
```
User melihat Midtrans Snap popup dengan:
├─ Daftar payment methods
├─ QRIS QR code area
├─ Virtual Account nomer
├─ E-wallet links
└─ Kartu Kredit area
```

**Jika popup TIDAK muncul ❌:**
```
Buka browser developer console (F12)
├─ Check for JavaScript errors
├─ Verify snap.js loaded:
│  console.log(window.snap)  // should show function
├─ Verify client key:
│  console.log(window.Midtrans.clientKey)  // should show key
└─ Check network tab untuk snap.js request
```

---

## 🧪 TESTING DIFFERENT PAYMENT METHODS

### **Method 1: QRIS (Recommended for Quick Test)**

**Flow:**
```
1. Select QRIS
2. Click "Bayar dengan QRIS"
3. QR Code tampil di popup
4. (Don't scan, just close popup)
5. System will auto-check status
```

**Expected Result:**
- Snap popup terbuka dengan QR code
- Payment status: `pending` → `settlement` (otomatis dari Midtrans)
- Browser redirect ke `/payment/finish`
- Status polling menunjukkan saat settlement tercapai

### **Method 2: Virtual Account Bank Transfer**

**Flow:**
```
1. Select "Transfer Bank"
2. Choose bank (BCA, BRI, Mandiri, Permata)
3. Virtual Account number ditampilkan
4. User bisa transfer dari bank app/ATM
5. Status: pending sampai uang masuk
```

**For Testing (Simulate Payment):**
- Di Midtrans Dashboard → Transactions
- Cari order ID Anda
- Click "Mark as Paid" (for testing only)
- Watch /payment/finish polling auto-update

### **Method 3: E-Wallet (GoPay, OVO, etc)**

**Flow:**
```
1. Select E-wallet (GoPay/OVO/Shopeepay/Dana/LinkAja)
2. Click method
3. Redirected to app/web (sandbox environment)
4. Approve payment
5. Back to Snap popup → Success
```

**For Testing:**
- Don't have app? Just close popup
- System still recognizes as "interactive" payment
- You can retry immediately after

### **Method 4: Kartu Kredit**

**Flow:**
```
1. Select "Kartu Kredit"
2. Enter test card details:
   Card: 4111111111111111 (visa test card)
   Exp: 12/25 (any future date)
   CVC: 123 (any 3 digits)
3. Click Pay
4. 3DS verification (if enabled)
5. Success → Redirect
```

---

## ✅ EXPECTED TEST RESULTS

### **Success Scenario**

```
1. Click "Bayar Sekarang" ✅
2. Select payment type → modal closes ✅
3. Select payment method → modal closes ✅
4. Snap popup APPEARS ✅
5. Payment method visible in popup ✅
6. (Optional) Complete payment flow ✅
7. Auto-redirect to /payment/finish ✅
8. Status auto-polling starts ✅
```

### **Failure Scenario (For Debugging)**

**Snap popup doesn't appear:**
```
🔍 Debug Steps:
1. Open DevTools (F12)
2. Go to Console tab
3. Type: console.log(window.snap)
   │ Should show: ƒ () function
   │ If undefined: Snap.js didn't load
   
4. Type: console.log(window.Midtrans.clientKey)
   │ Should show: Mid-client-xxxxxxxxxxxxxxxxx...
   │ If undefined: Client key not set
   
5. Go to Network tab
6. Reload page & filter by "snap.js"
7. Check if request 200 OK (not 404)

🔧 Common Fixes:
- Clear browser cache (Ctrl+Shift+Delete)
- Hard reload (Ctrl+Shift+R)
- Try different browser
- Check .env for correct keys
- Run: php artisan config:clear && php artisan config:cache
```

**Payment token not generating:**
```
🔍 Debug Steps:
1. Open browser DevTools → Network tab
2. Click "Bayar Sekarang"
3. Look for POST request to /payment/snap-token
4. Check response:
   ✅ 200 OK {success: true, snap_token: "..."}
   ❌ 500 Error → Check Laravel logs
   ❌ 403 Forbidden → Auth issue
   
📋 Check Laravel logs:
tail -f storage/logs/laravel.log

🔧 Common Causes:
- Booking sudah ada payment lain
- User tidak punya akses ke booking
- Midtrans SDK not installed
- Config keys wrong
```

---

## 📊 MONITORING PAYMENT STATUS

### **During Payment Flow**

**Browser Console (Real-time):**
```javascript
// Type di console untuk monitor:
console.log(window.snap)       // Check Snap available
console.log(window.snapToken)  // Current token
// Open Network tab untuk lihat:
// POST /payment/snap-token
// GET /payment/{id}/check-status (polling)
// POST /payment/callback (webhook dari Midtrans)
```

**Laravel Logs:**
```bash
# Terminal 1: Watch logs
tail -f storage/logs/laravel.log

# Payment flow akan log:
# [getSnapToken] Generating snap token
# [finish] User arrived at finish page  
# [checkStatus] Polling for status
# [callback] Webhook received from Midtrans
# [Payment updated] Status: settlement
```

### **After Payment Completion**

**Database Check:**
```bash
# Terminal, masuk Laravel tinker
php artisan tinker

# Check payment record:
>>> $payment = Payment::latest()->first()
>>> $payment->status              # Should be "paid" or "settlement"
>>> $payment->transaction_status  # "settlement" atau "capture"
>>> $payment->paid_at             # Should have timestamp

# Check booking status:
>>> $payment->booking->status     # Should be "approved"
```

---

## 🔍 DETAILED TESTING CHECKLIST

### **Frontend Checks**

- [ ] "Bayar Sekarang" button visible di booking detail
- [ ] Button click opens payment type modal
- [ ] Payment type selection closes modal
- [ ] Payment method modal appears
- [ ] Method selection visible (QRIS, Bank, E-wallet, CC)
- [ ] Snap popup appears after method selection
- [ ] Snap popup shows payment methods
- [ ] Can close popup without error
- [ ] Page doesn't crash on close

### **Backend Checks**

- [ ] POST /payment/snap-token returns 200 OK
- [ ] Response includes snap_token
- [ ] Payment record created in database
- [ ] Server logs show token generation
- [ ] GET /payment/finish loads successfully
- [ ] Status polling requests show 200 OK
- [ ] POST /payment/callback accessible (no auth required)

### **Integration Checks**

- [ ] Midtrans Snap.js library loads from CDN
- [ ] Client key properly configured
- [ ] Server key kept secret (not in frontend)
- [ ] CSRF tokens included in requests
- [ ] User auth validated for payment endpoints
- [ ] Booking ownership verified

### **Status Flow Checks**

- [ ] Payment status starts as "pending"
- [ ] After (simulated) payment: status updates to "paid"/"settlement"
- [ ] Booking status updates to "approved"
- [ ] Finish page redirects automatically
- [ ] Email notification sent (if configured)

---

## 🐛 COMMON ISSUES & FIXES

### **Issue: "snap is not defined"**
```
❌ Error: Uncaught ReferenceError: snap is not defined
✅ Fix: Snap.js library tidak fully loaded sebelum call snap.pay()

Solution:
// Check initMidtrans() fungsi jalan
// Verify Snap.js URL: https://app.sandbox.midtrans.com/snap/snap.js
// Check network tab untuk library load
```

### **Issue: "clientKey not set"**
```
❌ Error: clientKey is not set or invalid
✅ Fix: Client key tidak ter-set sebelum snap.pay()

Solution:
// Di booking/detail.blade.php, verify:
window.Midtrans.clientKey = '{{ config("midtrans.client_key") }}';
// Run: php artisan config:clear
```

### **Issue: CORS Error**
```
❌ Error: Cross-Origin Request Blocked
✅ Fix: Browser blocking request ke Midtrans dari localhost

Solution:
// This is expected for QRIS/E-wallet (they require redirect)
// Just continue - Midtrans popup will still work
// Production: Won't have CORS issue
```

### **Issue: "Payment already exists"**
```
❌ Error: Payment sudah ada untuk booking ini
✅ Fix: User try bayar 2x untuk booking yang sama

Solution:
// This is expected - prevent duplicate payment
// User harus gunakan existing payment atau create new booking
// Or change payment method & retry (delete old payment record untuk testing)
```

### **Issue: Webhook callback not received**
```
❌ Status tidak auto-update setelah payment
✅ Fix: Midtrans webhook tidak hit /payment/callback

Solution:
1. Check route PUBLIC (no auth required) ✓
2. Check .env MIDTRANS keys correct
3. Log POST request:
   - Add to routes/web.php logging
   - Monitor Laravel logs
4. Test webhook dari Midtrans Dashboard:
   - Settings → Webhook
   - Send Test Event
   - Watch logs untuk POST callback request
```

---

## 📱 TESTING WITH MULTIPLE DEVICES

### **Same Network (Localhost)**
```
Device 1 (Dev Machine):
http://127.0.0.1:8000 ✓ Works

Device 2 (Phone/Laptop):
http://127.0.0.1:8000 ✗ Won't work (localhost only on that machine)

Solution: Get your IP
$ ipconfig getifaddr en0  // Mac
$ ipconfig              // Windows (look for IPv4 Address)

Then access from Device 2:
http://192.168.x.x:8000 ✓ Works
```

### **Different Networks (Testing Before Production)**
```
For actual Midtrans webhook testing:
You need public URL (not localhost)

Options:
1. Deploy to staging server
2. Use ngrok: ngrok http 8000
   - Get public URL: https://xxx.ngrok.io
   - Update Midtrans webhook URL
   - Test payment
```

---

## 🎓 LEARNING PATH

**Do this in order:**

1. **[5 min] Basic Test**
   - Start server
   - Click "Bayar Sekarang"
   - Verify popup appears
   
2. **[10 min] Debug Popup**
   - If popup doesn't appear
   - Use DevTools to debug
   - Check console for errors
   
3. **[15 min] Simulate Payment**
   - Try each payment method in Snap popup
   - Don't complete (just test UI)
   - Close popup & check redirect
   
4. **[20 min] Status Polling**
   - Watch finish page polling
   - Check status updates in real-time
   - Monitor browser & server logs
   
5. **[30 min] End-to-End Flow**
   - Complete mock payment (use Midtrans test method)
   - Verify webhook callback
   - Check database updates
   - Verify email notification

---

## 📞 QUICK REFERENCE

**Server Management:**
```bash
# Start
php artisan serve

# Logs
tail -f storage/logs/laravel.log

# Database
php artisan tinker

# Config
php artisan config:clear
php artisan config:cache
```

**Key Files to Monitor:**
```
Server Config:    config/midtrans.php
Controller:       app/Http/Controllers/MidtransPaymentController.php
Routes:           routes/web.php
Frontend:         resources/views/booking/detail.blade.php
Finish Page:      resources/views/payment/finish.blade.php
Logs:             storage/logs/laravel.log
Database:         orders → payments → transactions tables
```

**Debugging Priority:**
```
1. Browser DevTools (F12) → Console
2. Laravel logs (storage/logs/laravel.log)
3. Network tab (see request/response)
4. Database (verify record updates)
5. Midtrans Dashboard (check transaction status)
```

---

**✅ Ready to Test! Click "Bayar Sekarang" & verify Snap popup appears** 🚀

