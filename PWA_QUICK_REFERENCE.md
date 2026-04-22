# 🚀 PWA Quick Start & Troubleshooting

Cheat sheet untuk implementasi PWA Padel House.

---

## ⚡ Quick Start (5 Minutes)

### ✅ Sudah Implemented:

```
✓ public/manifest.json
✓ public/service-worker.js
✓ public/offline.html
✓ resources/views/layouts/app.blade.php (updated)
  - PWA meta tags added
  - Service worker registration script added
  - Install button added to navbar
```

### 📋 What to Do Next:

1. **Update Icons** (optional tapi recommended)
   ```bash
   # Generate 192x192 dan 512x512 icons
   # Save ke public/images/
   # Update manifest.json dengan path
   ```

2. **Test Locally**
   ```bash
   php artisan serve
   # Open http://localhost:8000
   # Press F12 → Application tab
   # Check Service Workers
   ```

3. **Deploy to Production**
   - Ensure HTTPS is enabled
   - Push code to server
   - Verify at https://yourdomain.com

---

## 🧪 Quick Testing

### Test di Chrome DevTools:

**Service Worker Status:**
```
F12 → Application → Service Workers
Expected: "activated and running"
```

**Offline Mode:**
```
F12 → Application → Service Workers
Check: ☑ Offline
Action: Refresh page
Result: Should load from cache or show offline page
```

**Manifest:**
```
F12 → Application → Manifest
Check: name, icons, display, theme_color
```

**Install Button:**
```
F12 → Console
Run: (Firefox only needed)
  navigator.serviceWorker.ready.then(() => alert('SW ready'))
Result: Button should appear in navbar
```

### Test Offline:

```
F12 → Network tab
Throttling: Change to "Offline"
Navigate: Go to new page
Result: Should see cached page or offline.html
```

---

## ❌ Troubleshooting

### Problem: Service Worker Not Registered

**Check:**
1. File exists: `public/service-worker.js`
2. Accessible: `http://localhost:8000/service-worker.js` (returns JS code)
3. Script tag in `app.blade.php` before `</body>`
4. No CORS errors in console

**Fix:**
```javascript
// Run in browser console
navigator.serviceWorker.getRegistrations().then(regs => {
  if (regs.length === 0) {
    console.log('No service workers registered');
  } else {
    console.log('Registered SWs:', regs);
  }
});
```

### Problem: Manifest Not Loading

**Check:**
1. File exists: `public/manifest.json`
2. Link tag: `<link rel="manifest" href="/manifest.json">` in `<head>`
3. Valid JSON: No syntax errors
4. Content-Type: `application/json`

**Fix:**
```bash
# Check manifest validity
curl http://localhost:8000/manifest.json
# Should return valid JSON
```

### Problem: Offline Page Not Showing

**Check:**
1. File exists: `public/offline.html`
2. Accessible: `http://localhost:8000/offline.html`
3. Service worker is registered

**Fix:**
```javascript
// In browser console
caches.match('/offline').then(response => {
  if (response) {
    console.log('✓ Offline page in cache');
  } else {
    console.log('✗ Offline page NOT in cache');
  }
});
```

### Problem: Install Button Not Showing

**Check:**
1. Browser support (Chrome, Edge, Brave required)
2. HTTPS enabled (localhost exception)
3. Manifest has `icons` array with at least 1 icon
4. Icon files exist and are accessible
5. Element `#pwa-install-button` exists in navbar

**Fix:**
```javascript
// Check beforeinstallprompt event
window.addEventListener('beforeinstallprompt', (e) => {
  console.log('✓ Install prompt available');
});

// If no event, browser doesn't support PWA install
```

### Problem: Cache Growing Too Large

**Check:**
1. How many files in cache?
2. Are API responses being cached?
3. Are large files being cached?

**Fix:**
```javascript
// Clear all cache
caches.keys().then(names => {
  names.forEach(name => caches.delete(name));
  console.log('Cache cleared');
});

// Restart service worker
navigator.serviceWorker.getRegistrations().then(regs => {
  regs.forEach(reg => reg.unregister());
});
```

---

## 📱 Testing on Mobile

### Android Chrome:

1. Open `https://yourdomain.com`
2. Menu (⋮) → "Install app"
3. Confirm
4. App on home screen ✓

**Offline Test:**
- Open app from home screen
- Airplane mode ON
- Navigate previously visited pages → should work
- Try new page → offline fallback

### iOS Safari:

1. Open `https://yourdomain.com`
2. Share → "Add to Home Screen"
3. Enter name → "Add"
4. App on home screen ✓

**Note:** iOS PWA support more limited than Android

---

## 🔐 Production Pre-Check

```bash
# 1. HTTPS validation
curl -I https://yourdomain.com
# Should redirect /service-worker.js to HTTPS without error

# 2. Manifest check
curl https://yourdomain.com/manifest.json | jq '.' 
# Should return valid JSON

# 3. Service Worker check
curl https://yourdomain.com/service-worker.js | head -5
# Should show JavaScript code starting with comments

# 4. Offline page check
curl https://yourdomain.com/offline.html | head -5
# Should show HTML code

# 5. Meta tags check
curl https://yourdomain.com | grep -E 'manifest|theme-color'
# Should show manifest link and theme-color meta tag
```

---

## 🎯 Performance Checklist

- [ ] Lighthouse Score > 90
- [ ] FCP < 2 seconds
- [ ] LCP < 2.5 seconds
- [ ] CLS < 0.1
- [ ] No 404 errors in console
- [ ] No CORS errors
- [ ] Cache size < 50MB
- [ ] Install button shows
- [ ] Offline page loads
- [ ] Midtrans payment not cached

---

## 📊 Monitoring

### Check Cache Usage:

```javascript
async function getStorageInfo() {
  const storage = await navigator.storage.estimate?.();
  if (storage) {
    const percentUsed = (storage.usage / storage.quota) * 100;
    console.log(`Storage: ${percentUsed.toFixed(2)}% used`);
    console.log(`Used: ${(storage.usage / 1024 / 1024).toFixed(2)} MB`);
    console.log(`Quota: ${(storage.quota / 1024 / 1024).toFixed(2)} MB`);
  }
}

getStorageInfo();
```

### Monitor Service Worker:

```javascript
navigator.serviceWorker.ready.then(reg => {
  console.log('✓ SW Ready');
  console.log('Active:', reg.active?.scriptURL);
  console.log('State:', reg.active?.state);
});
```

### Check Network Status:

```javascript
console.log('Online:', navigator.onLine);

window.addEventListener('online', () => {
  console.log('✓ Back online');
});

window.addEventListener('offline', () => {
  console.log('✗ Went offline');
});
```

---

## 🆘 Get Help

### Debug Checklist:

1. **Open DevTools** (F12)
2. **Clear Everything**
   ```javascript
   // In console
   caches.keys().then(n => n.forEach(name => caches.delete(name)));
   navigator.serviceWorker.getRegistrations().then(r => r.forEach(reg => reg.unregister()));
   localStorage.clear();
   ```

3. **Reload Hard** (Ctrl+Shift+R or Cmd+Shift+R)

4. **Check Console** for errors

5. **Look at Network** tab:
   - Service Worker marked as "SW"
   - Other requests cached with "disk" label

6. **Check Application** tab:
   - Service Workers section
   - Caches section
   - Manifest section

### Common Error Messages:

| Error | Cause | Solution |
|-------|-------|----------|
| `ServiceWorkerGlobalScope is not defined` | Syntax error in SW | Check service-worker.js for typos |
| `Manifest could not be parsed` | Invalid JSON | Validate manifest.json |
| `MIME type 'text/html' is not executable` | Wrong Content-Type | Check server headers |
| `Cannot add cache entry` | SW file too large | Split or optimize SW |
| `App not installable` | Missing requirements | Check manifest completeness |

---

## 🔄 Deployment Script

**Untuk quick deployment:**

```bash
#!/bin/bash
# deploy-pwa.sh

echo "🔄 Deploying PWA..."

# 1. Clear Laravel cache
php artisan cache:clear
php artisan config:clear

# 2. Verify HTTPS
echo "Checking HTTPS..."
curl -sI https://yourdomain.com | head -1

# 3. Verify manifest
echo "Verifying manifest.json..."
curl -s https://yourdomain.com/manifest.json | jq '.' > /dev/null && echo "✓ Manifest OK" || echo "✗ Manifest Error"

# 4. Verify SW
echo "Verifying service-worker.js..."
curl -s https://yourdomain.com/service-worker.js | grep -q "Service Worker" && echo "✓ Service Worker OK" || echo "✗ Service Worker Error"

echo "✓ Deployment complete!"
```

---

## 📚 Quick Reference

**Key Files:**
- `public/manifest.json` - PWA configuration
- `public/service-worker.js` - Caching logic
- `public/offline.html` - Offline fallback
- `resources/views/layouts/app.blade.php` - PWA integration

**Key Commands:**
```bash
# Start development
php artisan serve

# Build for production
php artisan optimize

# Clear caches
php artisan cache:clear

# Check HTTPS
curl -I https://yourdomain.com
```

**Important URLs:**
- `http://localhost:8000/manifest.json`
- `http://localhost:8000/service-worker.js`
- `http://localhost:8000/offline.html`
- DevTools: `F12` → Application tab

---

**Last Updated:** April 2026  
**Version:** 1.0.0
