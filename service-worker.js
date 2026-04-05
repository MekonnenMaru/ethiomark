const CACHE_NAME = 'ethiomark-cache-v1';
const urlsToCache = [
  '/index.php',         // Add the index.php to the cache
  '/manifest.json',
  '/assets/icon/512X512.png'
];

// Install the service worker and cache resources
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
});

// Fetch cached resources when offline
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        return response || fetch(event.request);
      })
  );
});
