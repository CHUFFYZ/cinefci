const CACHE_NAME = 'cine-fci-v2';

// Solo assets estáticos que no cambian seguido
const STATIC_ASSETS = [
  'css/index.css',
  'css/panel.css',
  'js/textos-coneccion.js',
  'js/modal/video.js',
  'image/logo/logo.png',
  'image/no-poster.png'
];

// Páginas PHP que siempre deben ir a la red primero
const DYNAMIC_PAGES = [
  'index.php',
  'panel.php',
  'api.php'
];

// Instalación: solo cachear assets estáticos
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(STATIC_ASSETS))
      .then(() => self.skipWaiting()) // Activar nuevo SW inmediatamente
  );
});

// Activación: limpiar cachés viejos
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys
          .filter(key => key !== CACHE_NAME)
          .map(key => caches.delete(key))
      )
    ).then(() => self.clients.claim())
  );
});

// Estrategia según tipo de recurso
self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);
  const path = url.pathname;

  // Para páginas PHP o raíz → Network First (siempre datos frescos)
  const isDynamicPage =
    path.endsWith('.php') ||
    path === '/' ||
    DYNAMIC_PAGES.some(p => path.endsWith(p));

  if (isDynamicPage) {
    event.respondWith(
      fetch(event.request)
        .then(response => {
          // Si la red responde bien, borrar la versión vieja en caché
          const clone = response.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
          return response;
        })
        .catch(() => {
          // Solo si no hay red, usar caché como respaldo
          return caches.match(event.request);
        })
    );
  } else {
    // Para assets estáticos → Cache First (más rápido)
    event.respondWith(
      caches.match(event.request)
        .then(response => response || fetch(event.request))
    );
  }
});
