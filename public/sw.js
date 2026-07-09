// Tradexy Service Worker — Network-first with offline fallback
// Version-stamped for cache busting on deploy
const CACHE_VERSION = 'tradexy-v1';
const OFFLINE_URL = '/offline';

// Static assets to precache on install
const PRECACHE_ASSETS = [
    '/images/logo.png',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
];

// Install: precache essential assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_VERSION).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS);
        })
    );
    // Activate immediately without waiting for existing clients to close
    self.skipWaiting();
});

// Activate: clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name.startsWith('tradexy-') && name !== CACHE_VERSION)
                    .map((name) => caches.delete(name))
            );
        })
    );
    // Take control of all clients immediately
    self.clients.claim();
});

// Fetch: network-first strategy for navigation, cache-first for static assets
self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Skip non-GET requests (POST, PUT, DELETE — never cache mutations)
    if (request.method !== 'GET') {
        return;
    }

    // Skip cross-origin requests (analytics, fonts CDN, ads, etc.)
    if (!request.url.startsWith(self.location.origin)) {
        return;
    }

    // Skip API routes and auth-related paths
    const url = new URL(request.url);
    if (url.pathname.startsWith('/api/') ||
        url.pathname.startsWith('/login') ||
        url.pathname.startsWith('/register') ||
        url.pathname.startsWith('/logout') ||
        url.pathname.startsWith('/auth/') ||
        url.pathname.startsWith('/broadcasting/')) {
        return;
    }

    // Navigation requests: network-first, fallback to offline page
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    return response;
                })
                .catch(() => {
                    return caches.match(request).then((cached) => {
                        return cached || new Response(
                            '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Offline — Tradexy</title><style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Inter,system-ui,sans-serif;background:#1d232a;color:#a6adbb;display:flex;align-items:center;justify-content:center;min-height:100vh;text-align:center;padding:2rem}.container{max-width:400px}.icon{font-size:4rem;margin-bottom:1.5rem}h1{font-size:1.75rem;font-weight:900;color:#f5f5f5;margin-bottom:.75rem;text-transform:uppercase;letter-spacing:.05em}p{font-size:.95rem;line-height:1.6;margin-bottom:2rem;opacity:.7}button{background:#6366f1;color:#fff;border:none;padding:.75rem 2rem;border-radius:.75rem;font-weight:700;font-size:.9rem;cursor:pointer;text-transform:uppercase;letter-spacing:.05em;transition:opacity .2s}button:hover{opacity:.85}</style></head><body><div class="container"><div class="icon">📡</div><h1>You\'re Offline</h1><p>Check your internet connection and try again. Your trading data is safe on the server.</p><button onclick="window.location.reload()">Try Again</button></div></body></html>',
                            { headers: { 'Content-Type': 'text/html' } }
                        );
                    });
                })
        );
        return;
    }

    // Static assets: cache-first strategy
    if (request.destination === 'image' ||
        request.destination === 'style' ||
        request.destination === 'script' ||
        request.destination === 'font') {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) {
                    // Revalidate in background
                    fetch(request).then((response) => {
                        if (response && response.status === 200) {
                            caches.open(CACHE_VERSION).then((cache) => {
                                cache.put(request, response);
                            });
                        }
                    }).catch(() => {});
                    return cached;
                }
                return fetch(request).then((response) => {
                    if (response && response.status === 200) {
                        const responseClone = response.clone();
                        caches.open(CACHE_VERSION).then((cache) => {
                            cache.put(request, responseClone);
                        });
                    }
                    return response;
                }).catch(() => caches.match(request));
            })
        );
        return;
    }
});
