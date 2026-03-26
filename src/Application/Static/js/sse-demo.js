/**
 * SSE Demo — connects to the Semitexa SSE endpoint and renders incoming events.
 */
(function () {
    'use strict';

    const SSE_ENDPOINT = '/demo/events/sse';

    function init() {
        const connectBtn = document.getElementById('sse-connect');
        const disconnectBtn = document.getElementById('sse-disconnect');
        const statusEl = document.getElementById('sse-status');
        const logEl = document.getElementById('sse-event-log');

        if (!connectBtn || !disconnectBtn || !statusEl || !logEl) return;

        let source = null;

        function setStatus(text, variant) {
            statusEl.innerHTML = '<span class="badge badge--' + variant + '">' + text + '</span>';
        }

        function appendEvent(name, data) {
            const placeholder = logEl.querySelector('.sse-placeholder');
            if (placeholder) placeholder.remove();

            const item = document.createElement('li');
            item.className = 'sse-event-item';
            item.innerHTML =
                '<span class="sse-event-name">' + escHtml(name) + '</span> ' +
                '<span class="sse-event-data">' + escHtml(typeof data === 'string' ? data : JSON.stringify(data)) + '</span>' +
                '<span class="sse-event-time">' + new Date().toLocaleTimeString() + '</span>';

            logEl.insertBefore(item, logEl.firstChild);

            // Keep at most 20 entries
            while (logEl.children.length > 20) {
                logEl.removeChild(logEl.lastChild);
            }
        }

        function escHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        connectBtn.addEventListener('click', function () {
            if (source) return;

            source = new EventSource(SSE_ENDPOINT);
            setStatus('Connecting…', 'warning');
            connectBtn.style.display = 'none';
            disconnectBtn.style.display = '';

            source.addEventListener('open', function () {
                setStatus('Connected', 'active');
            });

            source.addEventListener('message', function (e) {
                appendEvent('message', e.data);
            });

            // Listen for named events the framework emits
            ['resource.updated', 'notification', 'ping'].forEach(function (eventName) {
                source.addEventListener(eventName, function (e) {
                    appendEvent(eventName, e.data);
                });
            });

            source.addEventListener('error', function () {
                setStatus('Disconnected', 'error');
                source.close();
                source = null;
                connectBtn.style.display = '';
                disconnectBtn.style.display = 'none';
            });
        });

        disconnectBtn.addEventListener('click', function () {
            if (source) {
                source.close();
                source = null;
            }
            setStatus('Disconnected', 'warning');
            connectBtn.style.display = '';
            disconnectBtn.style.display = 'none';
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
