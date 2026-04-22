# 📱 Implementasi PWA - Padel House

Dokumentasi lengkap untuk Progressive Web App (PWA) pada aplikasi Padel House.

**Tanggal**: April 2026  
**Versi**: 1.0.0  
**Status**: ✅ Siap Production (dengan HTTPS)

---

## 📋 Daftar Isi

1. [Implementasi Files](#implementasi-files)
2. [Fitur PWA](#fitur-pwa)
3. [Testing di Localhost](#testing-di-localhost)
4. [Testing di Device](#testing-di-device)
5. [Production Checklist](#production-checklist)
6. [Troubleshooting](#troubleshooting)

---

## 📁 Implementasi Files

### File yang Telah Dibuat:

```
public/
├── manifest.json          # PWA configuration
├── service-worker.js      # Service worker dengan caching strategy
├── offline.html           # Offline fallback page
└── favicon.ico           # (sudah ada)

resources/views/layouts/
└── app.blade.php         # Updated dengan PWA meta tags & SW registration
```

### Perubahan pada app.blade.php:

✅ Ditambahkan di `<head>`:
- `<meta name="theme-color" content="#0d6efd">`
- `<link rel="manifest" href="/manifest.json">`
- `<link rel="apple-touch-icon" href="...">`
- `<meta name="description" content="..."`

✅ Ditambahkan sebelum `</body>`:
- Service Worker registration script
- Install prompt handler
- Online/Offline status listener
- Install button di navbar

---

## 🎯 Fitur PWA

### 1. **Installable**
- ✅ Tombol "Install App" di navbar (muncul di supported browsers)
- ✅ Add to Home Screen di iOS
- ✅ Install via Chrome menu di Android
- ✅ Standalone mode (fullscreen tanpa browser chrome)

### 2. **Offline Capabilities**
- ✅ Service Worker dengan intelligent caching
- ✅ Cache-first untuk CSS, JS, images (static assets)
- ✅ Network-first untuk HTML pages dan API
- ✅ Fallback offline page jika halaman tidak ditemukan

### 3. **Performance**
- ✅ Pre-cache critical assets saat install
- ✅ Lazy loading non-critical resources
- ✅ Automatic cache cleanup untuk old versions

### 4. **Safety for Payments**
- ✅ Midtrans payment routes TIDAK di-cache
- ✅ API requests tidak di-cache (network always used)
- ✅ POST requests tidak di-cache
- ✅ Aman untuk transaksi pembayaran

### 5. **Smart Caching Strategy**

```
┌─ Static Assets (CSS, JS, images)
│  └─ Cache First → Network Fallback
│
├─ HTML Pages
│  └─ Network First → Cache Fallback
│
├─ API Requests
│  └─ Network First → Cache Fallback
│
└─ Payment Routes (/payment, /midtrans)
   └─ ✗ NEVER CACHED
```

---

## 🧪 Testing di Localhost

### Prerequisites:
- Google Chrome / Chromium (recommended)
- Firefox (supported)
- Edge (supported)

### Step 1: Jalankan Laravel Development Server

```bash
php artisan serve
```

Server akan berjalan di `http://localhost:8000`

### Step 2: Buka DevTools (F12)

**Chrome / Edge:**
1. Tekan `F12` atau `Ctrl+Shift+I`
2. Pergi ke tab **Application** (bukan Console)
3. Di sidebar kiri, cari **Service Workers**

**Firefox:**
1. Tekan `F12` atau `Ctrl+Shift+I`
2. Pergi ke tab **Storage**
3. Lihat **Service Workers** di bagian kiri

### Step 3: Check Service Worker Status

Di Chrome DevTools → **Application** tab:

```
✅ Service Workers
   └─ http://localhost:8000/
      Status: activated and running
      Scope: /
      
✅ Manifest
   └─ manifest.json
      Show: manifest.json content
```

### Step 4: Test Caching

1. **Buka halaman utama**: http://localhost:8000
2. Buka DevTools → **Application** → **Caches**
3. Refresh halaman (Ctrl+Shift+R atau Cmd+Shift+R untuk hard refresh)
4. Lihat cache yang terisi:
   - `padel-house-v1-static`
   - `padel-house-v1-dynamic`

### Step 5: Test Offline Mode

**Di Chrome DevTools:**
1. Pergi ke **Application** → **Service Workers**
2. Check kotak **Offline** (☑ Offline)
3. Refresh halaman → harusnya tetap berfungsi (dari cache)
4. Uncheck **Offline** untuk online kembali

**Alternative (Network simulation):**
1. DevTools → **Network** tab
2. Cari dropdown **Throttling** (biasanya "No throttling")
3. Pilih **Offline**
4. Refresh halaman → lihat offline fallback atau cached page

### Step 6: Test Install Button

**Chrome:**
1. Buka halaman di Chrome `http://localhost:8000`
2. Di navbar, seharusnya ada tombol "Install App" (atau di menu hamburger)
3. Klik tombol → muncul dialog install
4. Klik "Install"
5. Aplikasi akan muncul di desktop/taskbar

**Note:** Install button hanya muncul di supported browsers. Di localhost, mungkin diperlukan HTTPS simulation atau Chrome flag tertentu.

### Step 7: Test Manifest

1. DevTools → **Application** → **Manifest**
2. Verifikasi konfigurasi:
   - name: "Padel House - Aplikasi Booking Lapangan Padel"
   - short_name: "Padel"
   - start_url: "/"
   - display: "standalone"
   - theme_color: "#0d6efd"
   - icons: array of icons

### Step 8: Test Install Shortcuts

Jika install berhasil, app akan menampilkan shortcuts:
- "Pesan Lapangan" → langsung ke booking
- "Lacak Booking" → langsung ke tracking page

---

## 📱 Testing di Device

### Android:

#### Chrome Browser:
1. Buka `https://yourdomain.com` (HTTPS required)
2. Tunggu beberapa detik
3. Di bottom, muncul "Install app" prompt
4. Tap **Install**
5. Aplikasi akan muncul di home screen

#### Manual Installation:
1. Buka halaman di Chrome
2. Tap menu (⋮) → **Install app**
3. Confirm dengan **Install**

#### Testing Offline:
1. Buka app dari home screen
2. Disconnect internet
3. Navigate ke halaman sebelumnya → harusnya muncul dari cache
4. Coba buat booking baru → akan error (expected karena no internet)
5. Connect kembali → semua berfungsi normal

### iOS (iPadOS):

#### Add to Home Screen:
1. Buka Safari
2. Navigate ke `https://yourdomain.com`
3. Tap Share button (box dengan panah)
4. Scroll down → tap **Add to Home Screen**
5. Enter app name → tap **Add**
6. Aplikasi akan muncul di home screen

#### Testing:
1. Buka app dari home screen
2. App akan berjalan fullscreen (standalone mode)
3. Offline functionality tersedia (caching akan berfungsi)

**Note:** PWA di iOS memiliki keterbatasan dibanding Android. Service Worker support di iOS lebih limited.

---

## ✅ Production Checklist

### Sebelum Deploy ke Production:

- [ ] **HTTPS Enabled** ✅ (WAJIB untuk PWA)
  ```
  PWA hanya berfungsi di HTTPS!
  Localhost adalah exception untuk testing.
  ```

- [ ] **Manifest.json Updated**
  - [ ] Logo/icons sudah di-update (jangan gunakan placeholder)
  - [ ] URL sesuai dengan domain production
  - [ ] Theme color sesuai brand

- [ ] **Service Worker Configuration**
  - [ ] Cache version updated (`CACHE_VERSION`)
  - [ ] Excluded routes sudah benar (Midtrans, API)
  - [ ] Offline page accessible

- [ ] **Performance Testing**
  - [ ] Lighthouse audit di DevTools (target score > 90)
  - [ ] Page load time di 4G: < 3s
  - [ ] Page load time di LTE: < 5s
  - [ ] Offline functionality working

- [ ] **Security**
  - [ ] No sensitive data di cache
  - [ ] API keys tidak di-cache
  - [ ] Payment routes tidak di-cache

- [ ] **Mobile Testing**
  - [ ] Tested di Chrome Android
  - [ ] Tested di Safari iOS
  - [ ] Install button working
  - [ ] Offline pages accessible

- [ ] **Browser Support**
  - [ ] Chrome/Chromium ✅
  - [ ] Edge ✅
  - [ ] Firefox ✅
  - [ ] Safari ✅ (limited)

### Deploy Commands:

```bash
# 1. Build laravel (if needed)
php artisan optimize

# 2. Clear cache
php artisan cache:clear
php artisan config:clear

# 3. Check HTTPS
# Pastikan site berjalan di HTTPS dengan valid SSL certificate

# 4. Deploy
git push origin main  # atau command deploy Anda
```

### Verify di Production:

```bash
# Check HTTPS
curl -I https://yourdomain.com
# Response harus ada "HTTP/1.1 200 OK" (bukan 301/302 ke https)

# Check manifest
curl https://yourdomain.com/manifest.json
# Response harus JSON valid

# Check service worker
curl https://yourdomain.com/service-worker.js
# Response harus JavaScript valid
```

---

## 🔧 Troubleshooting

### Service Worker Tidak Ter-register

**Gejala:** DevTools → Service Workers kosong, atau error message

**Solusi:**

1. **Check Console untuk error:**
   ```javascript
   // Di browser console
   navigator.serviceWorker.getRegistrations().then(registrations => {
     console.log(registrations);
   });
   ```

2. **Verifikasi file path:**
   - Service Worker harus di `public/service-worker.js`
   - Cek URL di browser: `https://yourdomain.com/service-worker.js`
   - Response harus JavaScript (Content-Type: application/javascript)

3. **Clear cache dan reload:**
   ```javascript
   // Di console
   caches.keys().then(names => {
     names.forEach(name => caches.delete(name));
   });
   ```

4. **Restart browser:**
   - Close semua tab
   - Restart browser
   - Buka site kembali

### Install Button Tidak Muncul

**Gejala:** Tombol "Install App" tidak terlihat di navbar

**Solusi:**

1. **Verifikasi manifest.json:**
   - Pastikan file ada di `public/manifest.json`
   - Pastikan linked di `<head>`: `<link rel="manifest" href="/manifest.json">`

2. **Check manifest requirements:**
   - [ ] `name` field ada
   - [ ] `short_name` field ada
   - [ ] `start_url` field ada
   - [ ] `icons` array ada (minimum 1 icon)
   - [ ] `display` = "standalone"

3. **Browser support:**
   - Install button hanya muncul di Chrome, Edge, dan beberapa browser lain
   - Firefox dan Safari tidak menampilkan native install button

4. **Https required:**
   - Localhost exception untuk testing
   - Production HARUS HTTPS

### Offline Page Tidak Muncul

**Gejala:** Saat offline, malah blank page atau error

**Solusi:**

1. **Check offline.html:**
   - File harus ada di `public/offline.html`
   - Path di service-worker.js: `/offline`

2. **Update Service Worker:**
   ```bash
   # Clear cache
   # Buka DevTools → Application → Service Workers
   # Click "Unregister"
   # Refresh page
   ```

3. **Manual test:**
   - DevTools → Network → set Throttling ke "Offline"
   - Navigate ke halaman baru
   - Seharusnya muncul offline page

### Booking/Payment Functionality Broken Offline

**Gejala:** Form tidak bisa submit saat offline

**Expected:** Ini adalah behavior yang benar. Booking dan pembayaran memerlukan koneksi internet.

**Solusi:** Tampilkan notification ke user:
```javascript
if (!navigator.onLine) {
  alert('Fitur booking memerlukan koneksi internet');
}
```

Sudah di-implement di service-worker.js dengan exclude routes untuk `/payment` dan `/api`.

### Manifest Icons Tidak Loading

**Gejala:** Install button ada, tapi icon tidak muncul di home screen

**Solusi:**

1. **Update manifest.json dengan real icons:**
   ```json
   {
     "icons": [
       {
         "src": "/images/icon-192x192.png",  // ← path real
         "sizes": "192x192",
         "type": "image/png",
         "purpose": "any"
       },
       {
         "src": "/images/icon-512x512.png",
         "sizes": "512x512",
         "type": "image/png",
         "purpose": "any"
       }
     ]
   }
   ```

2. **Generate icons:**
   - Gunakan online tool: https://www.favicon-generator.org/
   - Upload logo Padel House
   - Download PNG 192x192 dan 512x512
   - Taruh di `public/images/`

3. **Update path di manifest.json dan deploy**

### Cache Terlalu Besar / Membengkak

**Gejala:** Storage penuh, browser jadi lambat

**Solusi:**

1. **Manual clear di browser:**
   - DevTools → Application → Storage → Clear site data
   - Check "Unregister service workers"

2. **Programmatic clear (add button di settings):**
   ```javascript
   async function clearPWACache() {
     const cacheNames = await caches.keys();
     await Promise.all(
       cacheNames.map(name => caches.delete(name))
     );
     console.log('Cache cleared');
   }
   ```

3. **Service Worker Update:**
   - Increment `CACHE_VERSION` di service-worker.js
   - Automatic cleanup untuk old caches

---

## 🎯 Performance Tips

### Optimize Manifest Icons

Gunakan tool online untuk generate icons:
- https://www.favicon-generator.org/
- https://www.pwabuilder.com/

### Optimize Caching

1. **Don't cache:**
   - API responses dengan data personal/sensitive
   - Large files (> 5MB)
   - Dynamic content

2. **Do cache:**
   - CSS, JS (static)
   - Fonts
   - Small images/logos
   - Frequently accessed pages

### Monitor Cache Size

```javascript
// Di browser console
caches.keys().then(names => {
  names.forEach(async (name) => {
    const cache = await caches.open(name);
    const keys = await cache.keys();
    console.log(`${name}: ${keys.length} files`);
  });
});
```

---

## 📚 Resources

- [MDN: Service Worker API](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API)
- [MDN: Web App Manifest](https://developer.mozilla.org/en-US/docs/Web/Manifest)
- [PWA Checklist](https://web.dev/pwa-checklist/)
- [Chrome DevTools - Application](https://developer.chrome.com/docs/devtools/progressive-web-apps/)
- [Web.dev PWA Training](https://web.dev/progressive-web-apps/)

---

## 📞 Support

Jika ada issue atau pertanyaan:

1. **Check console error** (F12 → Console tab)
2. **Clear cache dan reload** (Ctrl+Shift+R)
3. **Check manifest.json validity** (validator: https://www.pwabuilder.com/)
4. **Verify HTTPS** (production requirement)
5. **Test di different browser**

---

**Version:** 1.0.0  
**Last Updated:** April 2026  
**Status:** ✅ Production Ready
