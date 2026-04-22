// Service Worker untuk Padel House PWA
// Version: 1.2.0
// Fokus: stabil di localhost, tidak mengganggu Midtrans/payment.

const CACHE_VERSION = 'padel-house-v1.2.0';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const RUNTIME_CACHE = `${CACHE_VERSION}-runtime`;
const OFFLINE_URL = '/offline.html';

// Routes/host yang TIDAK boleh di-cache (API sensitif, pembayaran, dll)
const EXCLUDED_CACHE_ROUTES = [
  '/api/',
  '/payment',
  '/midtrans',
  '/booking/store',
  '/booking/update',
  'snap.midtrans.com',
  'app.midtrans.com',
  'app.sandbox.midtrans.com'
];

// Asset minimum untuk offline experience
const STATIC_ASSETS = [
  OFFLINE_URL,
  '/'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches
      .open(STATIC_CACHE)
      .then((cache) => cache.addAll(STATIC_ASSETS))
      .catch(() => undefined)
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches
      .keys()
      .then((keys) =>
        Promise.all(
          keys.map((key) => {
            if (key.startsWith('padel-house-') && !key.startsWith(CACHE_VERSION)) {
              return caches.delete(key);
            }
          })
        )
      )
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const { request } = event;

  if (request.method !== 'GET') {
    return;
  }

  const url = new URL(request.url);

  if (shouldExcludeFromCache(url)) {
    event.respondWith(fetch(request));
    return;
  }

  // Navigasi halaman: network-first, fallback ke offline
  if (request.mode === 'navigate' || request.destination === 'document') {
    event.respondWith(networkFirstNavigation(request));
    return;
  }

  // Asset statis same-origin: cache-first
  if (isStaticAssetRequest(url, request)) {
    event.respondWith(cacheFirst(request));
    return;
  }

  // Default: stale-while-revalidate untuk request lain (aman, tapi tidak agresif)
  event.respondWith(staleWhileRevalidate(request));
});

function shouldExcludeFromCache(url) {
  const urlString = url.toString();
  return EXCLUDED_CACHE_ROUTES.some((route) => urlString.includes(route));
}

function isStaticAssetRequest(url, request) {
  if (url.origin !== self.location.origin) return false;
  if (['script', 'style', 'image', 'font'].includes(request.destination)) return true;
  return /\.(js|css|png|jpg|jpeg|gif|svg|webp|ico|woff2?|ttf|eot)(\?.*)?$/i.test(url.pathname);
}

async function networkFirstNavigation(request) {
  try {
    return await fetch(request);
  } catch {
    return (
      (await caches.match(OFFLINE_URL)) ||
      new Response('Offline', {
        status: 503,
        headers: { 'Content-Type': 'text/plain' }
      })
    );
  }
}

async function cacheFirst(request) {
  const cached = await caches.match(request);
  if (cached) return cached;

  const response = await fetch(request);
  if (response && response.ok) {
    const cache = await caches.open(RUNTIME_CACHE);
    cache.put(request, response.clone()).catch(() => undefined);
  }
  return response;
}

async function staleWhileRevalidate(request) {
  const cache = await caches.open(RUNTIME_CACHE);
  const cached = await cache.match(request);

  const fetchPromise = fetch(request)
    .then((response) => {
      if (response && response.ok) {
        const url = new URL(request.url);
        if (url.origin === self.location.origin) {
          cache.put(request, response.clone()).catch(() => undefined);
        }
      }
      return response;
    })
    .catch(() => undefined);

  return cached || (await fetchPromise) || new Response('Service unavailable', { status: 503 });
}
