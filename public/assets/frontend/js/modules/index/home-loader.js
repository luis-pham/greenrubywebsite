(function () {
    'use strict';

    function loadScript(src) {
        return new Promise(function (resolve, reject) {
            if (!src) {
                resolve();
                return;
            }
            var existing = document.querySelector('script[data-home-deferred="1"]');
            if (existing) {
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

    function loadBelowFold(el) {
        var url = el.getAttribute('data-below-fold-url');
        if (!url) {
            return Promise.resolve();
        }

        return fetch(url, {
            credentials: 'same-origin',
            headers: { Accept: 'text/html' }
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('Below-fold HTTP ' + response.status);
            }
            return response.text();
        }).then(function (html) {
            el.innerHTML = html;
            el.removeAttribute('aria-busy');
        });
    }

    function boot() {
        var el = document.getElementById('home-below-fold');
        var deferredSrc = el ? el.getAttribute('data-deferred-js') : null;

        var chain = el && el.getAttribute('data-below-fold-url')
            ? loadBelowFold(el)
            : Promise.resolve();

        chain
            .catch(function (err) {
                console.error(err);
                if (el) {
                    el.removeAttribute('aria-busy');
                }
            })
            .then(function () {
                return loadScript(deferredSrc);
            })
            .catch(function (err) {
                console.error(err);
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
