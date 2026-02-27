/**
 * TradeGriffus — Service Worker
 * Cache de assets + suporte offline + sync de formulários
 */

const CACHE_VERSION = 'griffus-v4';
const STATIC_CACHE = CACHE_VERSION + '-static';
const PAGES_CACHE = CACHE_VERSION + '-pages';

// Assets estáticos para pré-cache
const PRECACHE_ASSETS = [
    '/offline.html',
    '/offline-form.html',
    '/assets/css/variables.css',
    '/assets/css/reset.css',
    '/assets/css/typography.css',
    '/assets/css/components.css',
    '/assets/css/layout.css',
    '/assets/img/logo_tradegriffus.png',
    '/assets/img/icone_tradegriffus.png',
    '/assets/img/icon-192.png',
    '/assets/img/icon-512.png',
    '/assets/js/app.js',
    '/assets/js/sidebar.js',
    '/assets/js/img-process.js',
    '/assets/js/offline-db.js',
    '/assets/js/offline-sync.js',
    '/assets/js/offline-ui.js',
    '/assets/js/pwa-install.js',
    '/assets/js/pagination.js',
];

// Páginas do promotor para cache network-first
const PROMOTOR_PAGES = [
    '/promotor/dashboard',
    '/promotor/rotas',
    '/promotor/mapa',
    '/promotor/ponto',
    '/promotor/perfil',
    '/promotor/pesquisas',
];

// ============================================
// INSTALL — Pré-cacheia assets estáticos
// ============================================
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS).catch(err => {
                console.warn('[SW] Falha no pré-cache de alguns assets:', err);
            });
        })
    );
    self.skipWaiting();
});

// ============================================
// ACTIVATE — Limpa caches antigos
// ============================================
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys
                    .filter(key => key !== STATIC_CACHE && key !== PAGES_CACHE)
                    .map(key => caches.delete(key))
            );
        })
    );
    self.clients.claim();
});

// ============================================
// FETCH — Estratégias de cache
// ============================================
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Ignora requests não-HTTP (chrome-extension, etc)
    if (!url.protocol.startsWith('http')) return;

    // POSTs — não cacheia, deixa o offline-sync.js cuidar
    if (request.method !== 'GET') return;

    // Assets estáticos (CSS, JS, imagens) → cache-first
    if (isStaticAsset(url)) {
        event.respondWith(cacheFirst(request, STATIC_CACHE));
        return;
    }

    // CDN (fonts, font-awesome) → cache-first
    if (isCDN(url)) {
        event.respondWith(cacheFirst(request, STATIC_CACHE));
        return;
    }

    // Páginas de navegação → network-first com fallback offline
    if (request.mode === 'navigate' || request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(networkFirstPage(request));
        return;
    }

    // Tudo mais → network-first genérico
    event.respondWith(networkFirst(request, STATIC_CACHE));
});

// ============================================
// Estratégias
// ============================================

/**
 * Cache-first: tenta o cache, depois rede
 */
async function cacheFirst(request, cacheName) {
    const cached = await caches.match(request);
    if (cached) return cached;

    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(cacheName);
            cache.put(request, response.clone());
        }
        return response;
    } catch (err) {
        return new Response('Offline', { status: 503, statusText: 'Offline' });
    }
}

/**
 * Network-first para páginas HTML: tenta rede, fallback cache, depois offline form
 */
async function networkFirstPage(request) {
    const url = new URL(request.url);

    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(PAGES_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    } catch (err) {
        // Sem rede: tenta cache da página
        const cached = await caches.match(request);
        if (cached) return cached;

        // Páginas do promotor → redireciona para offline-form.html
        if (isPromotorPage(url.pathname)) {
            const offlineForm = await caches.match('/offline-form.html');
            if (offlineForm) {
                // Determina a ação baseada na URL
                const action = getOfflineAction(url.pathname);
                if (action) {
                    // Retorna a página offline-form com redirect via meta refresh
                    const body = await offlineForm.text();
                    return new Response(body, {
                        headers: { 'Content-Type': 'text/html' },
                    });
                }
                return offlineForm;
            }
        }

        // Fallback final: página offline genérica
        return caches.match('/offline.html');
    }
}

/**
 * Verifica se a URL é uma página do promotor
 */
function isPromotorPage(pathname) {
    return pathname.startsWith('/promotor/');
}

/**
 * Determina a ação offline baseada na URL
 */
function getOfflineAction(pathname) {
    if (pathname.match(/^\/promotor\/checkin\/\d+/)) return 'checkin';
    if (pathname.match(/^\/promotor\/visita\/\d+/)) return 'checkout';
    if (pathname === '/promotor/ponto') return 'ponto';
    if (pathname === '/promotor/dashboard') return 'dashboard';
    return null;
}

/**
 * Network-first genérico: tenta rede, fallback cache
 */
async function networkFirst(request, cacheName) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(cacheName);
            cache.put(request, response.clone());
        }
        return response;
    } catch (err) {
        const cached = await caches.match(request);
        return cached || new Response('Offline', { status: 503 });
    }
}

// ============================================
// Helpers
// ============================================

function isStaticAsset(url) {
    return url.pathname.startsWith('/assets/') ||
        url.pathname.startsWith('/uploads/') ||
        /\.(css|js|png|jpg|jpeg|gif|webp|svg|ico|woff|woff2|ttf)$/i.test(url.pathname);
}

function isCDN(url) {
    return url.hostname.includes('cdnjs.cloudflare.com') ||
        url.hostname.includes('fonts.googleapis.com') ||
        url.hostname.includes('fonts.gstatic.com') ||
        url.hostname.includes('cdn.jsdelivr.net') ||
        url.hostname.includes('unpkg.com');
}

// ============================================
// Background Sync — processa fila offline
// ============================================
self.addEventListener('sync', (event) => {
    if (event.tag === 'griffus-offline-sync') {
        event.waitUntil(processOfflineQueue());
    }
});

/**
 * Processa a fila de ações offline via IndexedDB
 */
async function processOfflineQueue() {
    const db = await openDB();
    const actions = await getAllActions(db);

    for (const action of actions) {
        try {
            const formData = rebuildFormData(action);
            const response = await fetch(action.url, {
                method: action.method || 'POST',
                body: formData,
                credentials: 'same-origin',
            });

            if (response.ok || response.status === 302) {
                await removeAction(db, action.id);
                // Notifica clients
                const clients = await self.clients.matchAll();
                clients.forEach(client => {
                    client.postMessage({
                        type: 'SYNC_COMPLETE',
                        action: action.tipo,
                        id: action.id,
                    });
                });
            }
        } catch (err) {
            console.warn('[SW] Sync falhou para ação:', action.id, err);
            // Mantém na fila para retry
        }
    }

    // Notifica todas as tabs
    const clients = await self.clients.matchAll();
    clients.forEach(client => {
        client.postMessage({ type: 'SYNC_DONE' });
    });
}

/**
 * Reconstrói FormData a partir dos dados salvos
 */
function rebuildFormData(action) {
    const fd = new FormData();

    // Adiciona campos regulares
    if (action.fields) {
        for (const [key, value] of Object.entries(action.fields)) {
            fd.append(key, value);
        }
    }

    // Adiciona arquivos (blobs salvos)
    if (action.files) {
        for (const fileData of action.files) {
            const blob = new Blob([fileData.buffer], { type: fileData.type });
            fd.append(fileData.fieldName, blob, fileData.fileName);
        }
    }

    return fd;
}

// ============================================
// IndexedDB helpers (no Service Worker)
// ============================================

function openDB() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open('griffus_offline', 1);
        req.onupgradeneeded = (e) => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains('sync_queue')) {
                db.createObjectStore('sync_queue', { keyPath: 'id', autoIncrement: true });
            }
        };
        req.onsuccess = (e) => resolve(e.target.result);
        req.onerror = (e) => reject(e.target.error);
    });
}

function getAllActions(db) {
    return new Promise((resolve, reject) => {
        const tx = db.transaction('sync_queue', 'readonly');
        const store = tx.objectStore('sync_queue');
        const req = store.getAll();
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}

function removeAction(db, id) {
    return new Promise((resolve, reject) => {
        const tx = db.transaction('sync_queue', 'readwrite');
        const store = tx.objectStore('sync_queue');
        const req = store.delete(id);
        req.onsuccess = () => resolve();
        req.onerror = () => reject(req.error);
    });
}
