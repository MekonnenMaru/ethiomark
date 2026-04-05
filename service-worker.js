const CACHE_NAME = 'ethiomark-bingo-v2';
const urlsToCache = [
  '/',
  '/index.html',
  '/cards_data.js',
  '/bootstrap/css/style.css',
  '/bootstrap/css/bingon.css',
  '/bootstrap/css/ball.css',
  '/bootstrap/css/modal.css',
  '/bootstrap/css/gift-unboxing.css',
  '/cashier/right-menu.css',
  '/bootstrap/js/confetti.browser.min.js'
];

self.addEventListener('install', event => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache).catch(() => {}))
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') return;
  event.respondWith(
    caches.match(event.request).then(cached => cached || fetch(event.request))
  );
});
