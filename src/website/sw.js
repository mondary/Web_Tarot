const CACHE = 'tarot-v2026.10.12';
const SHELL = ['./', 'manifest.json', 'icon-192.png', 'icon-512.png'];

self.addEventListener('install', event => {
  event.waitUntil(caches.open(CACHE).then(cache => cache.addAll(SHELL)));
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => Promise.all(
      keys.filter(key => key.startsWith('tarot-v') && key !== CACHE).map(key => caches.delete(key))
    )).then(() => self.clients.claim())
  );
});

const unavailableImage = () => new Response(
  '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 360"><rect width="240" height="360" fill="#17130e"/><rect x="12" y="12" width="216" height="336" rx="12" fill="none" stroke="#c9a227" stroke-opacity=".5"/><text x="120" y="165" text-anchor="middle" font-family="sans-serif" font-size="18" fill="#f1ede4">Image indisponible</text><text x="120" y="195" text-anchor="middle" font-family="sans-serif" font-size="14" fill="#c9a227">Reconnectez-vous</text></svg>',
  {headers: {'Content-Type': 'image/svg+xml; charset=utf-8', 'Cache-Control': 'no-store'}}
);

self.addEventListener('fetch', event => {
  const request = event.request;
  if (request.method !== 'GET' || new URL(request.url).origin !== self.location.origin) return;

  const url = new URL(request.url);
  const isImage = url.searchParams.has('img') || url.searchParams.has('deckimg') || url.pathname.includes('/img/');
  const isAppCode = request.mode === 'navigate' || url.searchParams.has('js');
  const save = async response => {
    if (response.ok) {
      try {
        const cache = await caches.open(CACHE);
        await cache.put(request, response.clone());
      } catch (_) { /* A full cache must not turn a successful network response into a failure. */ }
    }
    return response;
  };

  event.respondWith((async () => {
    const cached = await caches.match(request);
    if (!isAppCode && cached) return cached;
    try {
      return await save(await fetch(request));
    } catch (_) {
      if (cached) return cached;
      if (isImage) return unavailableImage();
      return new Response('Connexion au serveur local indisponible.', {
        status: 503, headers: {'Content-Type': 'text/plain; charset=utf-8', 'Cache-Control': 'no-store'}
      });
    }
  })());
});
