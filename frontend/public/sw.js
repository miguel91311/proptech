const CACHE_NAME = "proptech-v1";
const STATIC_ASSETS = [
  "/",
  "/index.html",
  "/manifest.webmanifest",
  "/icon-192x192.svg",
  "/icon-512x512.svg",
];

const API_CACHE_NAME = "proptech-api-v1";
const API_ROUTES = ["/properties", "/properties/geo-search"];

// Instalação: cache de assets estáticos
self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS))
  );
  self.skipWaiting();
});

// Ativação: limpa caches antigos
self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((names) =>
      Promise.all(
        names
          .filter((n) => n !== CACHE_NAME && n !== API_CACHE_NAME)
          .map((n) => caches.delete(n))
      )
    )
  );
  self.clients.claim();
});

// Fetch: cache-first para assets, stale-while-revalidate para API
self.addEventListener("fetch", (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // API routes — stale-while-revalidate
  if (API_ROUTES.some((route) => url.pathname.includes(route))) {
    event.respondWith(
      caches.open(API_CACHE_NAME).then((cache) =>
        cache.match(request).then((cached) => {
          const fetchPromise = fetch(request)
            .then((networkResponse) => {
              if (networkResponse.ok) {
                cache.put(request, networkResponse.clone());
              }
              return networkResponse;
            })
            .catch(() => cached);
          return cached || fetchPromise;
        })
      )
    );
    return;
  }

  // Static assets — cache-first
  if (request.mode === "navigate" || STATIC_ASSETS.includes(url.pathname)) {
    event.respondWith(
      caches.match(request).then((cached) => cached || fetch(request))
    );
    return;
  }

  // Default: network-first com fallback
  event.respondWith(
    fetch(request).catch(() => caches.match(request))
  );
});
