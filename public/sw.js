// Minimal Service Worker - required for PWA installability
// No caching strategy - just enables "Add to Home Screen"
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', () => self.clients.claim());
