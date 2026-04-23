// Service Worker untuk Padel House PWA
// Version: 2.0.0 - FIXED untuk Payment Flow & Camera
// Fokus: Network-first untuk dynamic content, cache static assets saja

const CACHE_VERSION = 'padel-house-v2.0.0';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const RUNTIME_CACHE = `${CACHE_VERSION}-runtime`;
const OFFLINE_URL = '/offline.html';

// ================================================================================
// CRITICAL: Routes yang TIDAK boleh di-cache sama sekali
// - Jangan ubah array ini tanpa test menyeluruh!
// ================================================================================
const NO_CACHE_ROUTES = [
  // Booking Flow
  '/courts',
  '/booking/',
  '/booking/create',
  '/booking/store',
  '/booking/update',
  '/booking/show',
  '/select-datetime',
  '/select-payment-method',
  
  // Payment & Midtrans
  '/payment',
  '/midtrans/',
  '/midtrans-callback',
  'snap.midtrans.com',
  'app.midtrans.com',
  'app.sandbox.midtrans.com',
  
  // Tracking & Camera
  '/track-booking',
  '/search-booking',
  
  // API Calls
  '/api/',
  
  // Library dinamis yang perlu selalu fresh
  'html5-qrcode'
];

// Asset statis yang AMAN untuk di-cache (tidak berubah sering)
const STATIC_ASSETS = [
  OFFLINE_URL,
  '/',
  '/manifest.json'
];

// ================================================================================
// INSTALL EVENT - Cache hanya file statis minimal
// ================================================================================
self.addEventListener('install', (event) => {
  console.log('[SW] Installing Service Worker v2.0.0');
  event.waitUntil(
    caches
      .open(STATIC_CACHE)
      .then((cache) => {
        console.log('[SW] Caching static assets...');
        return cache.addAll(STATIC_ASSETS).catch(() => {
          console.warn('[SW] Some assets failed to cache (offline.html may not exist)');
        });
      })
      .then(() => {
        console.log('[SW] Calling skipWaiting()');
        return self.skipWaiting();
      })
  );
});

// ================================================================================
// ACTIVATE EVENT - Cleanup cache lama
// ================================================================================
self.addEventListener('activate', (event) => {
  console.log('[SW] Activating Service Worker');
  event.waitUntil(
    caches.keys().then((keys) => {
      console.log('[SW] Existing caches:', keys);
      return Promise.all(
        keys.map((key) => {
          // Delete old cache versions
          if (key.startsWith('padel-house-') && key !== STATIC_CACHE && key !== RUNTIME_CACHE) {
            console.log('[SW] Deleting old cache:', key);
            return caches.delete(key);
          }
        })
      );
    }).then(() => {
      console.log('[SW] Calling clients.claim()');
      return self.clients.claim();
    })
  );
});

// ================================================================================
// FETCH EVENT - Network-first untuk payment/booking, cache-first untuk static
// ================================================================================
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // 1. Non-GET requests: selalu network
  if (request.method !== 'GET') {
    return;
  }

  // 2. Routes yang critical: JANGAN di-cache
  if (shouldNotCache(url)) {
    console.log('[SW] Network-only:', url.pathname);
    event.respondWith(
      fetch(request).catch(() => {
        if (request.mode === 'navigate') {
          return caches.match(OFFLINE_URL) || new Response('Offline', { status: 503 });
        }
        throw new Error('Network request failed');
      })
    );
    return;
  }

  // 3. Static navigation (document): network-first dengan fallback cache
  if (request.mode === 'navigate' || request.destination === 'document') {
    console.log('[SW] Navigation (network-first):', url.pathname);
    event.respondWith(networkFirstNavigation(request));
    return;
  }

  // 4. Static assets (CSS, JS, images, fonts): cache-first
  if (isStaticAsset(url, request)) {
    console.log('[SW] Static asset (cache-first):', url.pathname);
    event.respondWith(cacheFirstWithNetworkFallback(request));
    return;
  }

  // 5. Default: network-first untuk XHR/fetch calls (API, page fragments)
  console.log('[SW] Other request (network-first):', url.pathname);
  event.respondWith(networkFirstWithCacheFallback(request));
});

// ================================================================================
// HELPERS
// ================================================================================

/**
 * Check jika URL harus TIDAK di-cache (network-only)
 */
function shouldNotCache(url) {
  const urlString = url.toString();
  return NO_CACHE_ROUTES.some((route) => urlString.includes(route));
}

/**
 * Check jika request adalah static asset (aman di-cache)
 */
function isStaticAsset(url, request) {
  if (url.origin !== self.location.origin) return false;
  
  const destination = request.destination;
  if (['script', 'style', 'image', 'font'].includes(destination)) {
    return true;
  }
  
  const pathname = url.pathname;
  return /\.(js|css|png|jpg|jpeg|gif|svg|webp|ico|woff2?|ttf|eot)(\?.*)?$/i.test(pathname);
}

/**
 * Network-first untuk navigasi (halaman)
 * Coba network dulu, fallback ke cache lama atau offline page
 */
async function networkFirstNavigation(request) {
  try {
    const response = await fetch(request);
    if (response && response.ok) {
      // Cache the successful response
      const cache = await caches.open(RUNTIME_CACHE);
      cache.put(request, response.clone()).catch(() => {});
    }
    return response;
  } catch (error) {
    console.error('[SW] Navigation fetch failed:', error);
    // Fallback ke cache atau offline page
    const cached = await caches.match(request);
    if (cached) return cached;
    
    return caches.match(OFFLINE_URL) || new Response('Offline', {
      status: 503,
      headers: { 'Content-Type': 'text/plain' }
    });
  }
}

/**
 * Cache-first untuk static assets
 * Gunakan cache jika ada, network jika tidak
 */
async function cacheFirstWithNetworkFallback(request) {
  const cached = await caches.match(request);
  if (cached) {
    console.log('[SW] Served from cache:', request.url);
    return cached;
  }

  try {
    const response = await fetch(request);
    if (response && response.ok) {
      const cache = await caches.open(RUNTIME_CACHE);
      cache.put(request, response.clone()).catch(() => {});
    }
    return response;
  } catch (error) {
    console.error('[SW] Asset fetch failed:', error);
    throw new Error('Asset not available');
  }
}

/**
 * Network-first untuk XHR/API calls
 * Coba network dulu, fallback ke cache jika offline
 */
async function networkFirstWithCacheFallback(request) {
  const cache = await caches.open(RUNTIME_CACHE);

  try {
    const response = await fetch(request);
    if (response && response.ok) {
      cache.put(request, response.clone()).catch(() => {});
    }
    return response;
  } catch (error) {
    console.error('[SW] Network request failed:', error);
    // Fallback ke cache jika ada
    const cached = await cache.match(request);
    if (cached) {
      console.log('[SW] Served from cache (offline):', request.url);
      return cached;
    }
    
    throw new Error('Offline and no cached response');
  }
}
