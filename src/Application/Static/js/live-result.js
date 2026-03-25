/**
 * LiveResult component — interactive HTTP client panel.
 * Sends a fetch request to the configured endpoint and renders the response inline.
 * Appears at L2 only via disclosure prompt.
 */
(function () {
    'use strict';

    function init() {
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-live-endpoint]');
            if (!btn) return;

            const endpoint = btn.dataset.liveEndpoint;
            const method   = (btn.dataset.liveMethod || 'GET').toUpperCase();
            const targetId = btn.dataset.liveTarget;
            const output   = targetId ? document.getElementById(targetId) : null;
            const statusEl = btn.closest('.live-result')?.querySelector('.live-result__status');

            if (!endpoint || !output) return;

            btn.disabled = true;
            if (statusEl) statusEl.textContent = 'Loading…';

            fetch(endpoint, {
                method: method,
                headers: { 'Accept': 'application/json, text/html' },
            })
            .then(function (res) {
                const contentType = res.headers.get('content-type') || '';
                if (statusEl) {
                    statusEl.innerHTML = '<span class="badge badge--' + (res.ok ? 'active' : 'error') + '">'
                        + res.status + ' ' + res.statusText + '</span>';
                }
                if (contentType.includes('application/json')) {
                    return res.json().then(function (data) {
                        output.innerHTML = '<pre class="code-inline">' + escHtml(JSON.stringify(data, null, 2)) + '</pre>';
                    });
                }
                return res.text().then(function (html) {
                    output.innerHTML = '<div class="live-result__html">' + html + '</div>';
                });
            })
            .catch(function (err) {
                output.innerHTML = '<p class="live-result__error">Request failed: ' + escHtml(err.message) + '</p>';
                if (statusEl) statusEl.innerHTML = '<span class="badge badge--error">Error</span>';
            })
            .finally(function () {
                btn.disabled = false;
            });
        });
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
