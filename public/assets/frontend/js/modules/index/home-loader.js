(function () {
    'use strict';

    function whenIdle(callback) {
        if (typeof window.requestIdleCallback === 'function') {
            window.requestIdleCallback(callback, { timeout: 1200 });
            return;
        }
        setTimeout(callback, 50);
    }

    function loadDeferredHomepage() {
        var home = document.getElementById('home');
        var src = home ? home.getAttribute('data-deferred-js') : null;
        if (!src || document.querySelector('script[data-home-deferred="1"]')) {
            return;
        }

        var script = document.createElement('script');
        script.src = src;
        script.async = true;
        script.setAttribute('data-home-deferred', '1');
        script.onload = function () {
            // Bootstrap-dependent global tooltips were skipped before this chunk
            // arrived. Re-run their debounced resize initialization now.
            window.dispatchEvent(new Event('resize'));
        };
        script.onerror = function () {
            console.error('Failed to load deferred homepage scripts: ' + src);
        };
        document.body.appendChild(script);
    }

    function schedule() {
        whenIdle(function () {
            loadDeferredHomepage();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', schedule, { once: true });
    } else {
        schedule();
    }
})();
