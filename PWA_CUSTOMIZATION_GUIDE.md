# 🎨 PWA Customization Guide - Padel House

Advanced customization options untuk PWA Anda.

---

## 🖼️ Generate & Customize Icons

### Opsi 1: Online Generator (Recommended for Quick Start)

1. Go to: https://www.favicon-generator.org/
2. Upload logo Padel House Anda
3. Download PNG versions:
   - 192x192 px
   - 512x512 px
   - Other sizes (optional)
4. Save to `public/images/`
5. Update manifest.json dengan path yang benar

### Opsi 2: Manual Design dengan Design Tools

**Menggunakan Figma:**
1. Create 192x192 px canvas
2. Design icon untuk Padel House
3. Export as PNG
4. Duplicate canvas, resize to 512x512
5. Export again

**Menggunakan Adobe XD:**
1. New artboard 192x192
2. Design logo
3. File → Export → PNG (2x resolution)

### Opsi 3: Programmatic Generation (Advanced)

Gunakan ImageMagick atau similar:

```bash
# Resize existing image ke 192x192
convert logo.png -resize 192x192 icon-192x192.png

# Resize ke 512x512
convert logo.png -resize 512x512 icon-512x512.png

# Generate dengan background color
convert logo.png -background "#0a0a0a" -gravity center -extent 192x192 icon-192x192.png
```

---

## 🎯 Update manifest.json dengan Icons

Setelah icons ready, update `public/manifest.json`:

```json
{
  "name": "Padel House - Aplikasi Booking Lapangan Padel",
  "short_name": "Padel",
  "icons": [
    {
      "src": "/images/icon-192x192.png",
      "sizes": "192x192",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/images/icon-512x512.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/images/icon-512x512-maskable.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "maskable"
    }
  ]
}
```

**Icon Purpose Explained:**
- `"any"`: Standard icon (normal)
- `"maskable"`: Icon dengan safe zone untuk custom shapes pada OS (optional)

---

## 🎨 Customize Theme Colors

### Theme Colors:

Current configuration di `app.blade.php`:
```html
<meta name="theme-color" content="#0d6efd">
```

Change ke brand color Anda:
- `#0d6efd` = Bootstrap blue
- `#FFA500` = Padel House orange (recommended)
- `#0a0a0a` = Dark background

Update di 3 places:

**1. app.blade.php (head):**
```html
<meta name="theme-color" content="#FFA500">
```

**2. manifest.json:**
```json
{
  "theme_color": "#FFA500",
  "background_color": "#0a0a0a"
}
```

**3. offline.html (optional):**
```html
<meta name="theme-color" content="#FFA500">
```

---

## 🔗 Customize Shortcuts

Edit shortcuts di `public/manifest.json`:

```json
{
  "shortcuts": [
    {
      "name": "Pesan Lapangan",
      "short_name": "Pesan",
      "description": "Langsung ke halaman pemesanan lapangan padel",
      "url": "/?shortcut=booking",
      "icons": [
        {
          "src": "/images/shortcut-booking.png",
          "sizes": "192x192",
          "type": "image/png"
        }
      ]
    },
    {
      "name": "Lacak Booking",
      "short_name": "Lacak",
      "description": "Cek status booking yang sudah dibuat",
      "url": "/track-booking",
      "icons": [
        {
          "src": "/images/shortcut-track.png",
          "sizes": "192x192",
          "type": "image/png"
        }
      ]
    },
    {
      "name": "Kontak Support",
      "short_name": "Support",
      "description": "Hubungi customer support kami",
      "url": "/support",
      "icons": [
        {
          "src": "/images/shortcut-support.png",
          "sizes": "192x192",
          "type": "image/png"
        }
      ]
    }
  ]
}
```

Handle shortcuts di controller (optional):

```php
// routes/web.php
Route::get('/', function () {
    $shortcut = request('shortcut');
    if ($shortcut === 'booking') {
        // Redirect to booking section
    }
    return view('home');
});
```

---

## 📱 Custom Install Button

### Styling Install Button

Di `app.blade.php`, customize button styling:

```html
<li class="nav-item d-none" id="pwa-install-button" style="margin-left: 10px;">
    <button class="btn btn-install-custom" title="Install aplikasi">
        <i class="fas fa-download"></i>
        <span class="d-none d-md-inline">Install App</span>
    </button>
</li>
```

Add custom CSS di `<style>` section:

```css
.btn-install-custom {
    background: linear-gradient(135deg, #FFA500 0%, #ffb81a 100%);
    border: none;
    color: #000000;
    border-radius: 50px;
    padding: 10px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(255, 165, 0, 0.3);
}

.btn-install-custom:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(255, 165, 0, 0.5);
}

.btn-install-custom i {
    margin-right: 8px;
}

@media (max-width: 768px) {
    .btn-install-custom {
        padding: 8px 15px;
        font-size: 14px;
    }
}
```

### Enhance Install Experience

Modifikasi script sebelum `</body>`:

```javascript
<script>
    let deferredPrompt;
    let installPromptShown = false;
    
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        
        const installBtn = document.getElementById('pwa-install-button');
        if (installBtn && !installPromptShown) {
            // Show with animation
            installBtn.style.display = 'flex';
            installBtn.style.animation = 'slideIn 0.3s ease';
            installPromptShown = true;
        }
    });
    
    const installBtn = document.getElementById('pwa-install-button');
    if (installBtn) {
        installBtn.addEventListener('click', async () => {
            if (deferredPrompt) {
                // Show native install prompt
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                
                if (outcome === 'accepted') {
                    console.log('✓ User accepted install');
                    // Show success message
                    showNotification('Aplikasi berhasil di-install!', 'success');
                } else {
                    console.log('✗ User dismissed install');
                }
                
                deferredPrompt = null;
                installBtn.style.display = 'none';
            }
        });
    }
    
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: ${type === 'success' ? '#28a745' : '#17a2b8'};
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            font-weight: 600;
            z-index: 9999;
            animation: slideUp 0.3s ease;
        `;
        document.body.appendChild(notification);
        
        setTimeout(() => notification.remove(), 3000);
    }
    
    // CSS animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    `;
    document.head.appendChild(style);
</script>
```

---

## 🔄 Add Update Notification

Notify users ketika ada update baru untuk app:

```javascript
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('{{ asset("service-worker.js") }}').then(reg => {
            // Check for updates setiap 30 menit
            setInterval(() => reg.update(), 30 * 60 * 1000);
            
            // Handle updates
            reg.addEventListener('updatefound', () => {
                const newWorker = reg.installing;
                newWorker.addEventListener('statechange', () => {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        // Ada update baru!
                        showUpdatePrompt();
                    }
                });
            });
        });
    }
    
    function showUpdatePrompt() {
        if (confirm('Versi baru aplikasi tersedia. Refresh sekarang?')) {
            window.location.reload();
        }
    }
</script>
```

---

## 📊 Add Analytics Tracking

Opsional: Track PWA install dan usage:

```javascript
<script>
    // Track install
    window.addEventListener('appinstalled', () => {
        gtag('event', 'app_installed', {
            event_category: 'pwa',
            event_label: 'Padel House PWA'
        });
        console.log('✓ PWA installed');
    });
    
    // Track install prompt shown
    window.addEventListener('beforeinstallprompt', () => {
        gtag('event', 'install_prompt_shown', {
            event_category: 'pwa'
        });
    });
    
    // Track offline usage
    window.addEventListener('offline', () => {
        gtag('event', 'went_offline', {
            event_category: 'connectivity'
        });
    });
    
    window.addEventListener('online', () => {
        gtag('event', 'went_online', {
            event_category: 'connectivity'
        });
    });
</script>
```

---

## 🛡️ Security Considerations

### Protect API Keys

✅ **DO:**
- Cache public CSS/JS
- Cache static images
- Cache HTML pages

❌ **DON'T:**
- Cache API responses dengan sensitive data
- Cache payment-related requests
- Cache authentication tokens

Current configuration sudah aman (API routes excluded).

### Content Security Policy

Add CSP header di `config/headers.php` atau via web server:

```php
// .htaccess (Apache)
Header set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;"
```

```nginx
# nginx.conf
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;" always;
```

---

## 🚀 Advanced: Sync Data in Background

Implement background sync untuk offline actions:

```javascript
// Register sync event
async function registerBackgroundSync() {
    if ('serviceWorkerContainer' in navigator && 'SyncManager' in window) {
        try {
            const registration = await navigator.serviceWorker.ready;
            await registration.sync.register('sync-bookings');
            console.log('✓ Background sync registered');
        } catch (err) {
            console.error('✗ Background sync registration failed:', err);
        }
    }
}

// Call saat user submit booking offline
document.getElementById('booking-form').addEventListener('submit', async (e) => {
    if (!navigator.onLine) {
        e.preventDefault();
        // Save to IndexedDB
        await saveBookingOffline(formData);
        // Register background sync
        await registerBackgroundSync();
        alert('Booking akan dikirim saat koneksi tersambung');
    }
});
```

Di `service-worker.js`, handle sync:

```javascript
self.addEventListener('sync', event => {
    if (event.tag === 'sync-bookings') {
        event.waitUntil(syncOfflineBookings());
    }
});

async function syncOfflineBookings() {
    const db = await openIndexedDB();
    const bookings = await db.getAll('pending-bookings');
    
    for (const booking of bookings) {
        try {
            const response = await fetch('/api/bookings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(booking)
            });
            
            if (response.ok) {
                await db.delete('pending-bookings', booking.id);
            }
        } catch (err) {
            console.error('Sync failed:', err);
        }
    }
}
```

---

## 📈 Performance Monitoring

### Lighthouse Audit

Di Chrome DevTools:
1. Go to **Lighthouse** tab
2. Click **Generate report**
3. Target scores:
   - Performance: > 90
   - Accessibility: > 90
   - Best Practices: > 90
   - SEO: > 90
   - PWA: > 90

### Web Vitals

Monitor Core Web Vitals:

```javascript
<script>
    import {getCLS, getFID, getFCP, getLCP, getTTFB} from 'https://cdn.jsdelivr.net/npm/web-vitals@3/dist/web-vitals.min.js';
    
    getCLS(console.log);
    getFID(console.log);
    getFCP(console.log);
    getLCP(console.log);
    getTTFB(console.log);
</script>
```

---

## 🔍 Testing Checklist

- [ ] Manifest valid (use PWA Builder validator)
- [ ] Icons loading correctly
- [ ] Install button showing on supported browsers
- [ ] App installs and launches fullscreen
- [ ] Offline pages accessible
- [ ] Payment flow working online
- [ ] No cache for API endpoints
- [ ] Service Worker registered
- [ ] Theme colors applied
- [ ] Performance score > 90 (Lighthouse)

---

## 📞 Useful Tools

- [PWA Builder](https://www.pwabuilder.com/) - Validate & test PWA
- [Favicon Generator](https://www.favicon-generator.org/) - Generate icons
- [Web Manifest Validator](https://manifest-validator.appspot.com/) - Validate manifest.json
- [Chrome DevTools](https://developer.chrome.com/docs/devtools/) - Debug
- [Lighthouse](https://developers.google.com/web/tools/lighthouse) - Performance audit

---

**Last Updated:** April 2026
