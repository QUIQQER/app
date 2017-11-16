'use strict';

importScripts('/packages/bin/sw-toolbox/sw-toolbox.js');

self.toolbox.options.cache = {
    name: 'quiqqer-sw-cache'
};

// dynamically cache any other local assets
self.toolbox.router.any('/*', self.toolbox.cacheFirst);

// for any other requests go to the network, cache,
// and then only use that cached resource if your user goes offline
self.toolbox.router.default = self.toolbox.networkFirst;