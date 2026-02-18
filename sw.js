const CACHE_NAME = 'cine-fci-v1';
const assets = [
  '/',
  'index.php',
  'panel.php',
  'css/index.css',
  'css/panel.css',
  'js/textos-coneccion.js',
  'js/modal/video.js',
  'image/logo/logo.png',
  'image/no-poster.png'
];

// Instalación y almacenamiento en caché
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(assets);
      })
  );
});

// Estrategia: Buscar en caché, si no hay, ir a la red
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        return response || fetch(event.request);
      })
  );
});