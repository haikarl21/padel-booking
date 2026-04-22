# 🔒 PWA + Midtrans Integration Guide

Panduan memastikan PWA dan Midtrans payment terintegrasi dengan aman.

---

## ⚠️ Important

**Service Worker TIDAK akan cache:**
- ✅ Midtrans API calls
- ✅ Payment creation endpoints
- ✅ AJAX booking requests
- ✅ Sensitive API data

Ini sudah di-konfigurasi otomatis di `service-worker.js`.

---

## 🛡️ Protected Routes

Routes yang automatically excluded dari caching:

```javascript
const EXCLUDED_CACHE_ROUTES = [
  '/api/',                    // All API routes
  '/payment/',                // Payment routes
  '/midtrans/',               // Midtrans routes
  '/booking/store',           // Booking creation
  '/booking/update',          // Booking updates
  '/track-booking',           // Tracking (dynamic)
  'snap.midtrans.com',        // Midtrans Snap
  'app.midtrans.com'          // Midtrans App
];
```

---

## 🔗 Safe AJAX Implementation

### Booking Form Submission

```html
<!-- Booking form -->
<form id="booking-form" method="POST" action="{{ route('booking.store') }}">
    @csrf
    <input type="text" name="player_name" required>
    <select name="court_id" required>
        <option>Pilih Lapangan</option>
    </select>
    <input type="date" name="booking_date" required>
    <input type="time" name="start_time" required>
    <button type="submit">Lanjut ke Pembayaran</button>
</form>

<script>
    // Safe AJAX submission
    document.getElementById('booking-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Check online status
        if (!navigator.onLine) {
            alert('Koneksi internet diperlukan untuk membuat booking');
            return;
        }
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('{{ route("booking.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (response.ok) {
                console.log('✓ Booking created');
                // Redirect to payment
                window.location.href = data.redirect_url;
            } else {
                console.error('✗ Booking failed:', data.message);
                alert('Gagal membuat booking: ' + data.message);
            }
        } catch (error) {
            console.error('✗ Request failed:', error);
            alert('Terjadi kesalahan. Silakan coba lagi.');
        }
    });
</script>
```

### Midtrans Snap Integration

```html
<!-- Midtrans button -->
<button id="pay-button" onclick="processPayment()">
    Bayar dengan Midtrans
</button>

<script src="https://app.midtrans.com/snap/snap.js" 
        data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
    async function processPayment() {
        // Verify online
        if (!navigator.onLine) {
            alert('Koneksi internet diperlukan untuk proses pembayaran');
            return;
        }
        
        try {
            // Get snap token from server (NOT cached)
            const response = await fetch('{{ route("payment.create") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    booking_id: document.getElementById('booking_id').value
                })
            });
            
            if (!response.ok) {
                throw new Error('Failed to get payment token');
            }
            
            const data = await response.json();
            
            // Open Midtrans Snap
            window.snap.pay(data.snap_token, {
                onSuccess: function(result) {
                    console.log('✓ Payment success', result);
                    handlePaymentSuccess(result);
                },
                onPending: function(result) {
                    console.log('⏳ Payment pending', result);
                    handlePaymentPending(result);
                },
                onError: function(result) {
                    console.error('✗ Payment error', result);
                    handlePaymentError(result);
                },
                onClose: function() {
                    console.log('Payment dialog closed');
                }
            });
        } catch (error) {
            console.error('✗ Payment process failed:', error);
            alert('Gagal memproses pembayaran. Silakan coba lagi.');
        }
    }
    
    function handlePaymentSuccess(result) {
        console.log('Saving payment result...');
        // Send confirmation to server
        fetch('{{ route("payment.confirm") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(result)
        }).then(() => {
            alert('Pembayaran berhasil! Terima kasih.');
            window.location.href = '{{ route("booking.success") }}';
        });
    }
    
    function handlePaymentPending(result) {
        alert('Pembayaran dalam proses. Silakan tunggu.');
    }
    
    function handlePaymentError(result) {
        alert('Pembayaran gagal. Silakan coba lagi.');
    }
</script>
```

---

## ✅ Verify Payment Routes Are Not Cached

### Check di Service Worker Console:

```javascript
// Di browser console, setelah SW terdaftar
navigator.serviceWorker.controller.postMessage({
    type: 'CHECK_CACHE',
    url: 'https://yourdomain.com/payment/create'
});

// Harusnya TIDAK di-cache
```

### Test Payment Without Caching:

```bash
# Scenario 1: Device online
1. Open app
2. Create booking
3. Process payment → Snap opens correctly
4. Payment successful

# Scenario 2: Simulate offline AFTER payment started
1. Open app
2. Create booking
3. Payment process starts
4. Disconnect internet
5. Snap might show "Network error" → expected behavior
   (User harus online untuk payment)

# Scenario 3: Back online
1. User connects back
2. Refresh page
3. Payment page available again
```

---

## 🔄 Handle Offline Scenarios

### Best Practices:

```php
<!-- Booking tracking page (SHOULD be cached) -->
@if ($booking->status === 'pending_payment')
    <div class="alert alert-warning">
        Booking Anda menunggu pembayaran.
        @if ($user->isOnline())
            <a href="{{ route('payment.page', $booking) }}" class="btn btn-primary">
                Lanjut Pembayaran
            </a>
        @else
            <!-- Show offline message -->
            <p class="text-muted">
                📡 Sambungkan internet untuk melanjutkan pembayaran.
            </p>
        @endif
    </div>
@endif
```

```javascript
// JavaScript - Check online status before payment
function startPayment() {
    if (!navigator.onLine) {
        showOfflineModal();
        return;
    }
    
    // Proceed dengan payment
    processPayment();
}

function showOfflineModal() {
    const modal = `
        <div class="modal" style="display: block;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Koneksi Diperlukan</h5>
                    </div>
                    <div class="modal-body">
                        <p>📡 Pembayaran memerlukan koneksi internet.</p>
                        <p>Pastikan Anda terhubung ke internet sebelum melanjutkan.</p>
                    </div>
                    <div class="modal-footer">
                        <button onclick="this.parentElement.parentElement.parentElement.remove()">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modal);
}
```

---

## 📊 Monitoring Payment Requests

### Service Worker Logs:

```javascript
// Add logging ke service-worker.js untuk debug

self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);
    
    // Log payment-related requests
    if (url.pathname.includes('/payment') || url.hostname.includes('midtrans')) {
        console.log('🔒 Payment request (NOT cached):', event.request.url);
    }
    
    // Standard handling tetap berjalan
    // ...
});
```

### Check Server Logs:

```bash
# Verify payment routes hit server (not served from cache)
tail -f storage/logs/laravel.log | grep -i payment

# Should see requests hitting your endpoints
# Not served from cache
```

---

## 🧪 Integration Test

### Test Scenario: Complete Booking + Payment Flow

**Step 1: Online Booking**
```
1. Open https://yourdomain.com
2. Fill booking form
3. Submit → Request goes to /booking/store
4. Response with snap_token
5. Midtrans Snap opens
```

**Step 2: Verify Payment Route Not Cached**
```
F12 → Application → Caches
Search: snap.midtrans.com
Expected: ✗ NOT IN CACHE
```

**Step 3: Complete Payment**
```
6. Complete payment in Snap
7. Success callback triggered
8. Booking status updated to "confirmed"
```

**Step 4: Offline Tracking**
```
9. Turn off internet
10. Refresh page (track-booking loaded from cache)
11. Status: "Confirmed" ✓
12. Online again
13. Everything synced correctly
```

---

## 🔍 Debug Payment Issues

### Problem: Payment fails when online

**Check:**
1. Midtrans credentials correct (`config/midtrans.php`)
2. Snap token generated successfully
3. CORS not blocking requests

**Debug:**
```javascript
// Check snap token
console.log('Snap Token:', snapToken);
console.log('Midtrans Client Key:', clientKey);

// Verify snap.js loaded
console.log('window.snap available:', !!window.snap);
```

### Problem: Payment cached (should not happen)

**Verify:**
```javascript
// In console
caches.open('padel-house-v1-api').then(cache => {
  cache.keys().then(requests => {
    requests.forEach(req => {
      if (req.url.includes('midtrans') || req.url.includes('payment')) {
        console.error('✗ Payment URL cached:', req.url);
      }
    });
  });
});
```

**Fix:**
- Update `EXCLUDED_CACHE_ROUTES` di service-worker.js
- Clear cache: `caches.keys().then(n => n.forEach(name => caches.delete(name)))`
- Reload

### Problem: CORS errors with Midtrans

**Check:**
1. Request headers are correct
2. Credentials include: true if needed
3. Midtrans server responds to CORS

**Debug:**
```javascript
// Check response headers
fetch('https://app.midtrans.com/api/...').then(r => {
  console.log('Headers:', r.headers);
  console.log('CORS-Allow-Origin:', r.headers.get('access-control-allow-origin'));
});
```

---

## 📋 Production Deployment Checklist

- [ ] Midtrans credentials updated for production
- [ ] Payment routes in EXCLUDED_CACHE_ROUTES
- [ ] HTTPS enabled
- [ ] Service Worker deployed
- [ ] Test payment flow end-to-end
- [ ] Offline fallback working
- [ ] Online restoration working
- [ ] No payment data in cache storage
- [ ] Error handling for network failures
- [ ] User notifications for offline state

---

## 🎯 Best Practices

### Do:
- ✅ Always check `navigator.onLine` before payment
- ✅ Use `try/catch` for payment requests
- ✅ Show user-friendly error messages
- ✅ Allow retry functionality
- ✅ Log payment events for debugging

### Don't:
- ❌ Cache payment responses
- ❌ Cache API keys or tokens
- ❌ Ignore CORS errors
- ❌ Store snap_token longer than needed
- ❌ Auto-retry payment immediately

---

## 📞 Support Resources

- [Midtrans Snap Documentation](https://docs.midtrans.com/en/snap/overview)
- [Midtrans CORS Setup](https://docs.midtrans.com/en/technical-reference/api-cors)
- [Service Worker Fetch API](https://developer.mozilla.org/en-US/docs/Web/API/FetchEvent)
- [Padel House Backend API](./API_DOCUMENTATION.md) (if exists)

---

**Last Updated:** April 2026  
**Version:** 1.0.0  
**Status:** Production Ready with Midtrans Integration
