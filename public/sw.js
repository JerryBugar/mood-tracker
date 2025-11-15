const CACHE_NAME = 'mood-tracker-v6';
const RUNTIME_CACHE = 'mood-tracker-runtime-v6';

// Halaman-halaman utama yang akan di-cache saat install
// Profile dan Home tidak di-cache karena data bisa berubah dan perlu selalu fresh
const STATIC_ASSETS = [
    '/',
    '/auth/verify',
    '/calendar',
    '/notif',
    '/logo/favicons.png',
    '/logo/google.png',
    '/logo/love.png',
    '/manifest.json',
    '/offline.html'
];

// Daftar halaman yang perlu di-cache (untuk runtime caching)
// Dashboard tidak di-cache karena akan redirect jika user sudah login
// Profile tidak di-cache karena data bisa berubah dan perlu selalu fresh
// Home tidak di-cache karena emoticons bergantung pada jenis kelamin user yang bisa berubah
const PAGES_TO_CACHE = [
    '/auth/verify',
    '/calendar',
    '/notif'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    console.log('[Service Worker] Installing...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[Service Worker] Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .catch((error) => {
                console.error('[Service Worker] Error caching static assets:', error);
            })
    );
    self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[Service Worker] Activating...');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME && cacheName !== RUNTIME_CACHE) {
                        console.log('[Service Worker] Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    return self.clients.claim();
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
    // Skip non-GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    // Skip chrome-extension and other non-http requests
    if (!event.request.url.startsWith('http')) {
        return;
    }

    const url = new URL(event.request.url);
    const isPageRequest = event.request.mode === 'navigate';
    const isPageToCache = PAGES_TO_CACHE.some(page => url.pathname === page);
    
    // Jangan cache dashboard untuk user yang sudah login (selalu fetch dari network)
    // Dashboard akan redirect ke home jika user sudah login
    const isDashboard = url.pathname === '/dashboard';
    
    // Jangan cache profile dengan query parameter atau setelah update (selalu fetch dari network)
    // Ini memastikan data terbaru selalu ditampilkan setelah update
    const isProfile = url.pathname === '/profile';
    
    // Jangan cache home karena emoticons bergantung pada jenis kelamin user yang bisa berubah
    const isHome = url.pathname === '/home';
    
    // Jangan cache semua halaman admin (login, dashboard, dll) - selalu fetch dari network
    // Admin pages tidak boleh di-cache untuk keamanan dan memastikan data selalu fresh
    const isAdmin = url.pathname.startsWith('/admin');

    event.respondWith(
        caches.match(event.request)
            .then((cachedResponse) => {
                // Untuk dashboard, selalu fetch dari network untuk memastikan redirect bekerja
                if (isDashboard && isPageRequest) {
                    return fetch(event.request)
                        .then((response) => {
                            // Jangan cache dashboard response
                            return response;
                        })
                        .catch(() => {
                            // Fallback ke cache jika network error
                            if (cachedResponse) {
                                return cachedResponse;
                            }
                            // Jika tidak ada cache, return offline page
                            return caches.match('/offline.html');
                        });
                }
                
                // Untuk profile, selalu fetch dari network untuk memastikan data terbaru
                // Ini penting karena profile bisa di-update dan kita perlu data terbaru
                if (isProfile && isPageRequest) {
                    return fetch(event.request)
                        .then((response) => {
                            // Jangan cache profile response untuk memastikan data selalu fresh
                            return response;
                        })
                        .catch(() => {
                            // Fallback ke cache jika network error
                            if (cachedResponse) {
                                return cachedResponse;
                            }
                            // Jika tidak ada cache, return offline page
                            return caches.match('/offline.html');
                        });
                }
                
                // Untuk home, selalu fetch dari network untuk memastikan emoticons terbaru
                // Emoticons bergantung pada jenis kelamin user yang bisa berubah
                if (isHome && isPageRequest) {
                    return fetch(event.request)
                        .then((response) => {
                            // Jangan cache home response untuk memastikan emoticons selalu fresh
                            return response;
                        })
                        .catch(() => {
                            // Fallback ke cache jika network error
                            if (cachedResponse) {
                                return cachedResponse;
                            }
                            // Jika tidak ada cache, return offline page
                            return caches.match('/offline.html');
                        });
                }
                
                // Untuk admin pages, selalu fetch dari network untuk keamanan dan data fresh
                // Admin pages tidak boleh di-cache sama sekali
                if (isAdmin && isPageRequest) {
                    return fetch(event.request)
                        .then((response) => {
                            // Jangan cache admin response - selalu fresh dari server
                            return response;
                        })
                        .catch(() => {
                            // Jika network error, jangan fallback ke cache untuk admin
                            // Return error response atau offline page
                            return caches.match('/offline.html');
                        });
                }
                
                // Return cached version if available
                if (cachedResponse) {
                    // Jika ini halaman yang perlu di-cache, update cache di background
                    // Jangan update cache untuk dashboard, profile, home, dan admin
                    if (isPageToCache && !isDashboard && !isProfile && !isHome && !isAdmin) {
                        fetch(event.request)
                            .then((response) => {
                                if (response && response.status === 200 && response.type === 'basic') {
                                    const responseToCache = response.clone();
                                    caches.open(RUNTIME_CACHE)
                                        .then((cache) => {
                                            cache.put(event.request, responseToCache);
                                        });
                                }
                            })
                            .catch(() => {
                                // Ignore fetch errors saat update cache
                            });
                    }
                    return cachedResponse;
                }

                // Otherwise fetch from network
                return fetch(event.request)
                    .then((response) => {
                        // Don't cache non-successful responses
                        if (!response || response.status !== 200 || response.type !== 'basic') {
                            return response;
                        }

                        // Clone the response
                        const responseToCache = response.clone();

                        // Cache halaman utama dan assets penting (kecuali dashboard, profile, home, dan admin)
                        if ((isPageToCache && !isDashboard && !isProfile && !isHome && !isAdmin) || url.pathname.startsWith('/logo/') || url.pathname === '/manifest.json') {
                            caches.open(RUNTIME_CACHE)
                                .then((cache) => {
                                    cache.put(event.request, responseToCache);
                                });
                        }

                        return response;
                    })
                    .catch(() => {
                        // If network fails and it's a navigation request, show offline page
                        if (isPageRequest) {
                            return caches.match('/offline.html').then((response) => {
                                if (response) {
                                    return response;
                                }
                                // Fallback jika offline.html tidak ada di cache
                                return new Response(
                                    '<!DOCTYPE html><html><head><title>Offline</title></head><body><h1>Anda Sedang Offline</h1><p>Mohon periksa koneksi internet Anda.</p></body></html>',
                                    {
                                        headers: { 'Content-Type': 'text/html' }
                                    }
                                );
                            });
                        }
                        // For other requests, return a basic error response
                        return new Response('Offline', {
                            status: 503,
                            statusText: 'Service Unavailable'
                        });
                    });
            })
    );
});

// Background sync (optional - untuk sync data saat online kembali)
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-mood') {
        event.waitUntil(syncMoodData());
    }
});

async function syncMoodData() {
    // Implementasi sync data mood jika diperlukan
    console.log('[Service Worker] Syncing mood data...');
}

// Push notification (optional - untuk notifikasi)
self.addEventListener('push', (event) => {
    const data = event.data ? event.data.json() : {};
    const title = data.title || 'Ceremood';
    const options = {
        body: data.body || 'Anda memiliki notifikasi baru',
        icon: '/logo/favicons.png',
        badge: '/logo/favicons.png',
        vibrate: [200, 100, 200],
        data: data.url || '/'
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Notification click handler
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        clients.openWindow(event.notification.data || '/')
    );
});

