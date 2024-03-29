var cacheName = 'fw';
var subFolder = '/fieldview/';
var filesToCache = [ //make more beautiful
  subFolder.concat(''),
  subFolder.concat('manifest.json'),
  subFolder.concat('favicon.ico'),
  subFolder.concat('assets/images/damm.svg'),
  subFolder.concat('assets/images/Logo_rwdraw.svg'),
  subFolder.concat('assets/js/node_modules/jquery/dist/jquery.min.js'),
  subFolder.concat('assets/js/node_modules/idb/build/iife/index-min.js'),
  subFolder.concat('assets/css/style.css')
  
];

/* Start the service worker and cache all of the app's content */
self.addEventListener('install', function(e) {
  e.waitUntil(
    caches.open(cacheName).then(function(cache) {
      return cache.addAll(filesToCache);
    })
  );
  //self.skipWaiting();
});

self.addEventListener('activate', function(e) {
});

/* Serve cached content when offline */
self.addEventListener('fetch', event => {
  //console.log('Fetch event for ', event.request.url);
  event.respondWith(
    caches.open(cacheName).then(function(cache) {
      return caches.match(event.request)
      .then(response => {
        if (response) {
          //console.log('Found ', event.request.url, ' in cache');
          return response;
        }
        console.log('Network request for ', event.request.url);
        return fetch(event.request)

        // TODO 4 - Add fetched files to the cache

      }).catch(error => {

        // TODO 6 - Respond with custom offline page

      })
    })
  );
});
