(function () {
    'use strict';

    function loadScript(src) {
        return new Promise(function (resolve, reject) {
            if (!src) {
                resolve();
                return;
            }
            if (document.querySelector('script[data-home-deferred="1"]')) {
                resolve();
                return;
            }
            var script = document.createElement('script');
            script.src = src;
            script.async = true;
            script.setAttribute('data-home-deferred', '1');
            script.onload = function () { resolve(); };
            script.onerror = function () { reject(new Error('Failed to load ' + src)); };
            document.body.appendChild(script);
        });
    }

    function fetchBelowFold(url) {
        return fetch(url, {
            credentials: 'same-origin',
            headers: { Accept: 'text/html' }
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('Below-fold HTTP ' + response.status);
            }
            return response.text();
        });
    }

    function injectBelowFold(el, html) {
        if (!el || html == null) {
            return;
        }
        el.innerHTML = html;
        el.removeAttribute('aria-busy');
    }

    function afterWindowLoad(callback) {
        if (document.readyState === 'complete') {
            callback();
            return;
        }
        window.addEventListener('load', callback, { once: true });
    }

    function whenIdle(callback) {
        if (typeof window.requestIdleCallback === 'function') {
            window.requestIdleCallback(callback, { timeout: 3000 });
            return;
        }
        setTimeout(callback, 300);
    }

    function boot() {
        var el = document.getElementById('home-below-fold');
        if (!el) {
            return;
        }

        var deferredSrc = el.getAttribute('data-deferred-js');
        var url = el.getAttribute('data-below-fold-url');

        // Do not contend with LCP: wait until window load + idle, then fetch/inject.
        afterWindowLoad(function () {
            whenIdle(function () {
                var chain = url ? fetchBelowFold(url) : Promise.resolve(null);

                chain
                    .then(function (html) {
                        if (url) {
                            injectBelowFold(el, html);
                        }
                    })
                    .catch(function (err) {
                        console.error(err);
                        el.removeAttribute('aria-busy');
                    })
                    .then(function () {
                        return loadScript(deferredSrc);
                    })
                    .catch(function (err) {
                        console.error(err);
                    });
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
