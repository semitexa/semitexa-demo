/**
 * SSE Demo — connects to the Semitexa SSE endpoint and renders incoming events.
 */
(function () {
    'use strict';

    const DEFERRED_KISS_MODE = 'deferred_kiss';

    function init() {
        const rootEl = document.getElementById('sse-demo');
        const streamMode = rootEl ? (rootEl.getAttribute('data-stream-mode') || 'standalone') : 'standalone';
        const SSE_ENDPOINT = rootEl ? (rootEl.getAttribute('data-sse-endpoint') || '/sse') : '/sse';
        const connectBtn = document.getElementById('sse-connect');
        const disconnectBtn = document.getElementById('sse-disconnect');
        const statusEl = document.getElementById('sse-status');
        const logEl = document.getElementById('sse-event-log');
        const logShellEl = logEl ? logEl.closest('.preview-sse-panel__log-shell') : null;
        const minuteSyncEl = document.getElementById('sse-minute-sync');
        const minuteValueEl = document.getElementById('sse-minute-value');
        const minuteSummaryEl = document.getElementById('sse-minute-summary');

        if (!statusEl || !logEl || !minuteSyncEl || !minuteValueEl || !minuteSummaryEl) return;

        const authRequired = rootEl ? rootEl.getAttribute('data-authorization-required') === 'true' : false;
        const isAuthenticated = rootEl ? rootEl.getAttribute('data-is-authenticated') === 'true' : false;
        const authRequiredMessage = rootEl ? (rootEl.getAttribute('data-auth-required-message') || 'Authorization is required.') : 'Authorization is required.';
        const expectedCadenceSeconds = rootEl ? Math.max(1, parseInt(rootEl.getAttribute('data-expected-cadence-seconds') || '60', 10) || 60) : 60;

        let source = null;
        let currentSessionId = '';
        let countdownTimer = null;
        let deferredSummary = 'Shared kiss stream will stay open. The timer tracks the next scheduled heartbeat broadcast from the backend.';

        function setDeferredState(summary) {
            deferredSummary = summary;
            minuteSummaryEl.textContent = summary;
        }

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

            logEl.appendChild(item);

            // Keep at most 20 entries
            while (logEl.children.length > 20) {
                logEl.removeChild(logEl.firstChild);
            }

            if (logShellEl) {
                logShellEl.scrollTop = logShellEl.scrollHeight;
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

        function secondsUntilNextWindow(intervalSeconds) {
            const intervalMs = intervalSeconds * 1000;
            const remainder = Date.now() % intervalMs;

            if (remainder === 0) {
                return 0;
            }

            return Math.ceil((intervalMs - remainder) / 1000);
        }

        function renderCountdown(seconds) {
            minuteValueEl.textContent = String(seconds).padStart(2, '0');

            if (source) {
                minuteSummaryEl.textContent = 'The SSE connection is open. The backend will publish the next scheduled event when this counter reaches zero.';
                return;
            }

            if (authRequired && !isAuthenticated) {
                minuteSummaryEl.textContent = authRequiredMessage;
                return;
            }

            if (streamMode === DEFERRED_KISS_MODE) {
                minuteValueEl.textContent = String(secondsUntilNextWindow(expectedCadenceSeconds)).padStart(2, '0');
                minuteSummaryEl.textContent = deferredSummary;
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
                renderCountdown(secondsUntilNextWindow(expectedCadenceSeconds));
            };

            tick();
            countdownTimer = window.setInterval(tick, 250);
        }

        function openStream() {
            currentSessionId = createSessionId();
            source = new EventSource(
                SSE_ENDPOINT
                + '?session_id=' + encodeURIComponent(currentSessionId)
                + '&demo_stream=' + encodeURIComponent(streamMode)
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

        function initDeferredKissObserver() {
            setStatus('Waiting for deferred stream', 'warning');
            setDeferredState('Waiting for the deferred runtime to open its shared kiss stream and deliver page blocks.');

            document.addEventListener('semitexa:deferred:stream', function (e) {
                const detail = e.detail || {};
                const phase = detail.phase || 'update';

                if (phase === 'connecting') {
                    setStatus('Opening shared kiss stream…', 'warning');
                    setDeferredState('The deferred runtime is opening its shared SSE connection. The countdown tracks the next scheduled heartbeat window.');
                    appendEvent('kiss.connecting', 'Opening __semitexa_kiss for deferred blocks');
                    return;
                }

                if (phase === 'connected') {
                    setStatus('Shared kiss stream connected', 'active');
                    setDeferredState('The deferred runtime is streaming blocks over its shared kiss connection. The countdown tracks the next scheduled heartbeat window.');
                    appendEvent('kiss.connected', 'Shared __semitexa_kiss connection established');
                    return;
                }

                if (phase === 'done') {
                    setStatus(detail.live ? 'Deferred stream live' : 'Deferred stream complete', 'success');
                    setDeferredState(detail.live
                        ? 'Initial deferred delivery finished; the shared kiss connection remains open.'
                        : 'Deferred delivery finished and the shared kiss connection closed.');
                    appendEvent('kiss.done', detail.live ? 'Initial blocks delivered, live updates remain enabled' : 'Initial deferred delivery completed');
                    return;
                }

                if (phase === 'error') {
                    setStatus('Shared kiss stream interrupted', 'warning');
                    setDeferredState('The shared deferred stream dropped; runtime fallback or reconnect logic may take over.');
                    appendEvent('kiss.error', 'The shared __semitexa_kiss connection dropped');
                }
            });

            document.addEventListener('semitexa:deferred:block', function (e) {
                const detail = e.detail || {};
                if (!detail.slotId) return;

                if (detail.mode === 'template') {
                    return;
                }

                if (detail.mode === 'html') {
                    return;
                }

                if (detail.mode === 'fallback') {
                    return;
                }
            });

            document.addEventListener('semitexa:deferred:message', function (e) {
                const detail = e.detail || {};
                const payload = detail.payload || {};
                const eventName = detail.eventName || 'message';

                if (eventName === 'scheduler.tick') {
                    appendEvent(eventName, formatEventData(payload));
                    setStatus('Scheduled heartbeat received', 'success');
                    setDeferredState('A real scheduled backend heartbeat arrived over the shared kiss stream. The countdown restarted for the next scheduler window.');
                    return;
                }
            });

            if (window.SemitexaSSR && window.SemitexaSSR._connected) {
                setStatus('Shared kiss stream connected', 'active');
                setDeferredState('The deferred runtime already has an open shared kiss stream. The countdown tracks the next scheduled heartbeat window.');
            }
        }

        function escHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        if (streamMode === DEFERRED_KISS_MODE) {
            initDeferredKissObserver();
        } else {
            if (!connectBtn || !disconnectBtn) return;

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
        }

        startCountdownLoop();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
