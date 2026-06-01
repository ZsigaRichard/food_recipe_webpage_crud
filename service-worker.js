const CACHE_NAME = 'my-pwa-cache-v1';
const urlsToCache = [
  '/',
  '/recept_index.php',
  '/footer.html',
'/js_functions.js',
  '/favicon.jfif',
  '/favicon.jfif'
];

// Install esemény - fájlok cache-elése
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(urlsToCache);
    })
  );
});

// Fetch esemény - cache vagy hálózat válasz
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request).then(response => {
      return response || fetch(event.request);
    })
  );
});
