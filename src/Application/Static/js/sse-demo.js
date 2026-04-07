/**
 * SSE Demo — connects to the Semitexa SSE endpoint and renders incoming events.
 */
(function () {
    'use strict';

    // Connect to the public SSE route handled by the SSR package.
    const SSE_ENDPOINT = '/sse';
    const DEMO_STREAM = 'showcase';

    function init() {
        const rootEl = document.getElementById('sse-demo');
        const connectBtn = document.getElementById('sse-connect');
        const disconnectBtn = document.getElementById('sse-disconnect');
        const statusEl = document.getElementById('sse-status');
        const logEl = document.getElementById('sse-event-log');
        const minuteSyncEl = document.getElementById('sse-minute-sync');
        const minuteValueEl = document.getElementById('sse-minute-value');
        const minuteSummaryEl = document.getElementById('sse-minute-summary');

        if (!connectBtn || !disconnectBtn || !statusEl || !logEl || !minuteSyncEl || !minuteValueEl || !minuteSummaryEl) return;

        const authRequired = rootEl ? rootEl.getAttribute('data-authorization-required') === 'true' : false;
        const isAuthenticated = rootEl ? rootEl.getAttribute('data-is-authenticated') === 'true' : false;
        const authRequiredMessage = rootEl ? (rootEl.getAttribute('data-auth-required-message') || 'Authorization is required.') : 'Authorization is required.';

        let source = null;
        let currentSessionId = '';
        let countdownTimer = null;

        function setStatus(text, variant) {
            statusEl.innerHTML = '<span class="preview-pill preview-pill--' + variant + '">' + text + '</span>';
        }

        function appendEvent(name, data) {
            const placeholder = logEl.querySelector('.preview-sse-panel__placeholder');
            if (placeholder) placeholder.remove();

            const item = document.createElement('li');
            item.className = 'preview-sse-panel__event';
            item.innerHTML =
                '<span class="preview-sse-panel__event-name">' + escHtml(name) + '</span> ' +
                '<span class="preview-sse-panel__event-data">' + escHtml(typeof data === 'string' ? data : JSON.stringify(data)) + '</span>' +
                '<span class="preview-sse-panel__event-time">' + new Date().toLocaleTimeString() + '</span>';

            logEl.insertBefore(item, logEl.firstChild);

            // Keep at most 20 entries
            while (logEl.children.length > 20) {
                logEl.removeChild(logEl.lastChild);
            }
        }

        function parseEventData(raw) {
            if (typeof raw !== 'string') {
                return raw;
            }

            try {
                return JSON.parse(raw);
            } catch (_error) {
                return raw;
            }
        }

        function formatEventData(data) {
            if (!data || typeof data !== 'object') {
                return typeof data === 'string' ? data : JSON.stringify(data);
            }

            const prefix = [];
            if (data.source) prefix.push(data.source);
            if (data.level) prefix.push(data.level);

            const title = data.title ? data.title + ': ' : '';
            const message = data.message || JSON.stringify(data);

            return (prefix.length ? '[' + prefix.join(' / ') + '] ' : '') + title + message;
        }

        function createSessionId() {
            return 'demo_sse_' + Math.random().toString(36).slice(2, 10) + '_' + Date.now().toString(36);
        }

        function secondsUntilNextMinute() {
            const now = new Date();
            const seconds = now.getSeconds();
            const millis = now.getMilliseconds();
            return Math.max(0, 60 - seconds - (millis > 0 ? 1 : 0));
        }

        function renderCountdown(seconds) {
            minuteValueEl.textContent = String(seconds).padStart(2, '0');

            if (source) {
                minuteSummaryEl.textContent = 'The SSE connection is open. The backend will publish the next minute tick when this counter reaches zero.';
                return;
            }

            if (authRequired && !isAuthenticated) {
                minuteSummaryEl.textContent = authRequiredMessage;
                return;
            }

            minuteSummaryEl.textContent = 'Connect once. The backend will emit a fresh SSE message on every new minute.';
        }

        function clearCountdown() {
            if (countdownTimer) {
                window.clearInterval(countdownTimer);
                countdownTimer = null;
            }
        }

        function startCountdownLoop() {
            clearCountdown();

            const tick = function () {
                renderCountdown(secondsUntilNextMinute());
            };

            tick();
            countdownTimer = window.setInterval(tick, 250);
        }

        function openStream() {
            currentSessionId = createSessionId();
            source = new EventSource(
                SSE_ENDPOINT
                + '?session_id=' + encodeURIComponent(currentSessionId)
                + '&demo_stream=' + encodeURIComponent(DEMO_STREAM)
            );
            setStatus('Connecting…', 'warning');
            connectBtn.style.display = 'none';
            disconnectBtn.style.display = '';

            source.addEventListener('open', function () {
                setStatus('Connected to backend stream', 'active');
                minuteSummaryEl.textContent = 'Live backend SSE connection is open. A new backend message will arrive on every next minute boundary.';
            });

            source.addEventListener('message', function (e) {
                const payload = parseEventData(e.data);
                appendEvent('message', formatEventData(payload));
            });

            ['connected', 'resource.updated', 'notification', 'scheduler.tick', 'ping'].forEach(function (eventName) {
                source.addEventListener(eventName, function (e) {
                    const payload = parseEventData(e.data);
                    appendEvent(eventName, formatEventData(payload));
                    if (eventName === 'scheduler.tick') {
                        setStatus('Minute tick received', 'success');
                        minuteSummaryEl.textContent = 'Fresh backend minute tick received. Countdown restarted for the next minute.';
                    }
                });
            });

            source.addEventListener('error', function () {
                setStatus('Reconnecting…', 'warning');
                minuteSummaryEl.textContent = 'The connection dropped. EventSource is retrying automatically.';
            });
        }

        function escHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        connectBtn.addEventListener('click', function () {
            if (source || connectBtn.disabled) return;

            openStream();
        });

        disconnectBtn.addEventListener('click', function () {
            if (source) {
                source.close();
                source = null;
            }
            currentSessionId = '';
            setStatus('Disconnected', 'warning');
            minuteSummaryEl.textContent = 'Connect once. The backend will emit a fresh SSE message on every new minute.';
            connectBtn.style.display = '';
            disconnectBtn.style.display = 'none';
        });

        startCountdownLoop();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
