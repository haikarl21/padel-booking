// Service Worker untuk Padel House PWA
// Version: 1.0.0
// Cache strategy yang aman untuk AJAX dan Midtrans payment

const CACHE_VERSION = 'padel-house-v1';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const DYNAMIC_CACHE = `${CACHE_VERSION}-dynamic`;
const API_CACHE = `${CACHE_VERSION}-api`;

// Assets yang harus di-cache di awal (critical resources)
const CRITICAL_ASSETS = [
  '/',
  '/index.php',
  '/css/app.css',
  '/js/app.js',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'
];

// Daftar halaman penting untuk pre-cache
const IMPORTANT_PAGES = [
  '/',
  '/track-booking',
  '/offline'
];

// Routes yang TIDAK boleh di-cache (API sensitif, pembayaran, etc)
const EXCLUDED_CACHE_ROUTES = [
  '/api/',
  '/payment/',
  '/midtrans/',
  '/booking/store',
  '/booking/update',
  '/track-booking',
  'snap.midtrans.com',
  'app.midtrans.com'
];

/**
 * Install Event - Pre-cache critical assets
 */
self.addEventListener('install', event => {
  console.log('Service Worker installing...');
  
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then(cache => {
        console.log('Caching critical assets');
        return cache.addAll(CRITICAL_ASSETS).catch(err => {
          console.warn('Some critical assets failed to cache:', err);
          // Lanjutkan meski beberapa asset gagal
        });
      })
      .then(() => self.skipWaiting())
  );
});

/**
 * Activate Event - Clean up old caches
 */
self.addEventListener('activate', event => {
  console.log('Service Worker activating...');
  
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          // Hapus cache lama
          if (cacheName.startsWith('padel-house-') && cacheName !== STATIC_CACHE && 
              cacheName !== DYNAMIC_CACHE && cacheName !== API_CACHE) {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
      .then(() => self.clients.claim())
  );
});

/**
 * Fetch Event - Intelligent caching strategy
 */
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Jangan cache request yang tidak penting
  if (shouldExcludeFromCache(url)) {
    return event.respondWith(fetch(request));
  }
  
  // Handle GET requests saja (POST tidak di-cache)
  if (request.method !== 'GET') {
    return event.respondWith(fetch(request));
  }
  
  // Strategi berbeda untuk tipe resource yang berbeda
  if (isApiRequest(url)) {
    // API: Network first, cache fallback
    event.respondWith(networkFirstStrategy(request));
  } else if (isAssetRequest(url)) {
    // Assets (CSS, JS, images): Cache first, network fallback
    event.respondWith(cacheFirstStrategy(request));
  } else if (isHtmlRequest(request)) {
    // HTML pages: Network first, cache fallback
    event.respondWith(networkFirstStrategy(request));
  } else {
    // Default: Cache first
    event.respondWith(cacheFirstStrategy(request));
  }
});

/**
 * Cache First Strategy - Cocok untuk static assets
 */
function cacheFirstStrategy(request) {
  return caches.match(request)
    .then(response => {
      if (response) {
        return response;
      }
      
      return fetch(request)
        .then(response => {
          // Jangan cache response yang tidak berhasil
          if (!response || response.status !== 200 || response.type === 'error') {
            return response;
          }
          
          // Clone response untuk di-cache
          const responseToCache = response.clone();
          caches.open(DYNAMIC_CACHE)
            .then(cache => {
              cache.put(request, responseToCache);
            });
          
          return response;
        });
    })
    .catch(error => {
      console.log('Fetch failed:', error);
      // Return offline page jika tersedia
      return caches.match('/offline')
        .then(response => response || new Response('Halaman tidak ditemukan', { status: 404 }));
    });
}

/**
 * Network First Strategy - Cocok untuk API dan HTML
 */
function networkFirstStrategy(request) {
  return fetch(request)
    .then(response => {
      // Jangan cache response yang tidak berhasil
      if (!response || response.status !== 200) {
        return response;
      }
      
      // Clone response untuk di-cache
      const responseToCache = response.clone();
      caches.open(isApiRequest(new URL(request.url)) ? API_CACHE : DYNAMIC_CACHE)
        .then(cache => {
          cache.put(request, responseToCache);
        });
      
      return response;
    })
    .catch(error => {
      console.log('Network request failed:', error);
      
      // Coba return dari cache
      return caches.match(request)
        .then(response => {
          if (response) {
            return response;
          }
          
          // Jika HTML request, return offline page
          if (isHtmlRequest(request)) {
            return caches.match('/offline')
              .then(offlineResponse => offlineResponse || new Response(getOfflinePage(), {
                headers: { 'Content-Type': 'text/html' }
              }));
          }
          
          // Untuk API/resource lain, return error response
          return new Response('Network error', { status: 503 });
        });
    });
}

/**
 * Check apakah request harus di-exclude dari cache
 */
function shouldExcludeFromCache(url) {
  const urlString = url.toString();
  
  return EXCLUDED_CACHE_ROUTES.some(route => {
    if (route.includes('midtrans') || route.includes('snap')) {
      return urlString.includes(route);
    }
    return urlString.includes(route);
  });
}

/**
 * Check apakah URL adalah API request
 */
function isApiRequest(url) {
  return url.pathname.startsWith('/api/') || 
         url.hostname.includes('midtrans') ||
         url.hostname.includes('snap');
}

/**
 * Check apakah URL adalah asset (CSS, JS, images, fonts)
 */
function isAssetRequest(url) {
  return /\.(js|css|png|jpg|jpeg|gif|svg|woff|woff2|ttf|eot)(\?.*)?$/i.test(url.pathname) ||
         url.hostname.includes('cdn.jsdelivr.net') ||
         url.hostname.includes('cdnjs.cloudflare.com');
}

/**
 * Check apakah request adalah HTML
 */
function isHtmlRequest(request) {
  return request.headers.get('accept')?.includes('text/html');
}

/**
 * Generate simple offline page
 */
function getOfflinePage() {
  return `
    <!DOCTYPE html>
    <html lang="id">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Offline - Padel House</title>
      <style>
        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
        }
        
        body {
          background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
          color: #ffffff;
          font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
          display: flex;
          align-items: center;
          justify-content: center;
          min-height: 100vh;
          padding: 20px;
        }
        
        .offline-container {
          text-align: center;
          max-width: 500px;
        }
        
        .offline-icon {
          font-size: 80px;
          margin-bottom: 20px;
        }
        
        h1 {
          font-size: 32px;
          margin-bottom: 10px;
          color: #FFA500;
        }
        
        p {
          font-size: 16px;
          color: #999999;
          margin-bottom: 30px;
          line-height: 1.6;
        }
        
        .button-group {
          display: flex;
          gap: 10px;
          flex-wrap: wrap;
          justify-content: center;
        }
        
        button {
          padding: 12px 30px;
          border: none;
          border-radius: 50px;
          font-size: 16px;
          font-weight: 600;
          cursor: pointer;
          transition: all 0.3s ease;
        }
        
        .btn-primary {
          background-color: #FFA500;
          color: #000000;
        }
        
        .btn-primary:hover {
          background-color: #ffb81a;
          transform: scale(1.02);
        }
        
        .btn-secondary {
          background-color: transparent;
          color: #FFA500;
          border: 2px solid #FFA500;
        }
        
        .btn-secondary:hover {
          background-color: rgba(255, 165, 0, 0.1);
        }
        
        .tips {
          margin-top: 40px;
          padding: 20px;
          background: rgba(26, 26, 26, 0.5);
          border-radius: 10px;
          border-left: 4px solid #FFA500;
          text-align: left;
        }
        
        .tips h3 {
          color: #FFA500;
          margin-bottom: 10px;
          font-size: 16px;
        }
        
        .tips ul {
          list-style: none;
          font-size: 14px;
          color: #cccccc;
        }
        
        .tips li {
          margin-bottom: 8px;
          padding-left: 20px;
          position: relative;
        }
        
        .tips li:before {
          content: "✓";
          position: absolute;
          left: 0;
          color: #FFA500;
        }
      </style>
    </head>
    <body>
      <div class="offline-container">
        <div class="offline-icon">📡</div>
        <h1>Koneksi Terputus</h1>
        <p>Saatnya Anda terhubung kembali ke internet untuk mengakses Padel House.</p>
        
        <div class="button-group">
          <button class="btn-primary" onclick="location.reload()">Muat Ulang</button>
          <button class="btn-secondary" onclick="history.back()">Kembali</button>
        </div>
        
        <div class="tips">
          <h3>💡 Tips:</h3>
          <ul>
            <li>Periksa koneksi internet Anda</li>
            <li>Coba aktifkan WiFi atau data seluler</li>
            <li>Buka halaman ini lagi saat koneksi sudah tersambung</li>
          </ul>
        </div>
      </div>
      
      <script>
        // Update status koneksi secara real-time
        window.addEventListener('online', () => {
          setTimeout(() => location.reload(), 500);
        });
      </script>
    </body>
    </html>
  `;
}

/**
 * Message handling untuk komunikasi dengan main thread
 */
self.addEventListener('message', event => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  
  if (event.data && event.data.type === 'CLEAR_CACHE') {
    caches.delete(DYNAMIC_CACHE);
    caches.delete(API_CACHE);
  }
});
