/**
 * Execution Arena demo.
 */
(function () {
    'use strict';

    function init() {
        const root = document.getElementById('execution-arena');
        if (!root) {
            return;
        }

        const launchEndpoint = root.getAttribute('data-launch-endpoint') || '/demo/events/arena/launch';
        const sseEndpoint = root.getAttribute('data-sse-endpoint') || '/sse';
        const streamStatus = document.getElementById('execution-arena-stream-status');
        const streamSummary = document.getElementById('execution-arena-stream-summary');
        const sessionPill = document.getElementById('execution-arena-session-pill');
        const logEl = document.getElementById('execution-arena-log');
        const lanes = {};
        let source = null;

        root.querySelectorAll('.execution-lane').forEach(function (laneEl) {
            const mode = laneEl.getAttribute('data-mode');
            if (!mode) {
                return;
            }

            lanes[mode] = {
                state: laneEl.querySelector('[data-role="lane-state"]'),
                responseSummary: laneEl.querySelector('[data-role="response-summary"]'),
                responseMs: laneEl.querySelector('[data-role="response-ms"]'),
                dispatchMs: laneEl.querySelector('[data-role="dispatch-ms"]'),
                runId: laneEl.querySelector('[data-role="run-id"]'),
                progressBar: laneEl.querySelector('[data-role="progress-bar"]'),
                progressLabel: laneEl.querySelector('[data-role="progress-label"]'),
                workerLabel: laneEl.querySelector('[data-role="worker-label"]'),
                message: laneEl.querySelector('[data-role="lane-message"]'),
                timeline: laneEl.querySelector('[data-role="timeline"]'),
                button: laneEl.querySelector('[data-action="launch"]'),
            };
        });

        const sessionId = createSessionId();
        if (sessionPill) {
            sessionPill.textContent = 'session ' + shortId(sessionId);
        }

        openStream(sessionId);

        Object.keys(lanes).forEach(function (mode) {
            const lane = lanes[mode];
            if (!lane.button) {
                return;
            }

            lane.button.addEventListener('click', function () {
                launch(mode, sessionId);
            });
        });

        window.addEventListener('beforeunload', function () {
            if (source) {
                source.close();
            }
        });

        function openStream(sessionId) {
            const streamUrl = new URL(sseEndpoint, window.location.origin);
            streamUrl.searchParams.set('session_id', sessionId);
            source = new EventSource(streamUrl.toString());

            source.addEventListener('open', function () {
                setStreamStatus('SSE proof stream live', 'active');
                if (streamSummary) {
                    streamSummary.textContent = 'The browser is now listening to backend evidence on a dedicated SSE session.';
                }
                appendLog('stream.open', 'SSE proof stream connected for ' + shortId(sessionId));
            });

            ['demo.execution.accepted', 'demo.execution.progress', 'demo.execution.completed', 'connected', 'ping'].forEach(function (eventName) {
                source.addEventListener(eventName, function (event) {
                    const payload = parsePayload(event.data);
                    if (eventName.indexOf('demo.execution.') === 0) {
                        applyExecutionEvent(eventName, payload);
                    } else if (eventName === 'connected') {
                        appendLog('connected', stringifyPayload(payload));
                    }
                });
            });

            source.addEventListener('error', function () {
                setStreamStatus('Reconnecting…', 'warning');
                if (streamSummary) {
                    streamSummary.textContent = 'The SSE connection dropped. EventSource will retry automatically.';
                }
            });
        }

        function launch(mode, sessionId) {
            const lane = lanes[mode];
            if (!lane) {
                return;
            }

            const startedAt = performance.now();
            resetLaneTimeline(lane);
            setLaneState(lane, 'Launching…', 'warning');
            updateLaneMessage(lane, 'Sending launch request to the backend…');
            appendLaneTimeline(lane, 'Launch request sent from the browser.');

            fetch(launchEndpoint, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
                body: new URLSearchParams({
                    mode: mode,
                    sessionId: sessionId,
                }),
            })
                .then(function (response) {
                    return response.json().then(function (payload) {
                        return { ok: response.ok, payload: unwrapResourceData(payload) };
                    });
                })
                .then(function (result) {
                    const roundtripMs = Math.round(performance.now() - startedAt);
                    if (!result.ok || !result.payload || result.payload.ok !== true) {
                        throw new Error(result.payload && result.payload.error ? result.payload.error : 'Launch failed');
                    }

                    lane.responseMs.textContent = roundtripMs + ' ms';
                    lane.dispatchMs.textContent = result.payload.dispatchMs + ' ms';
                    lane.runId.textContent = shortId(result.payload.runId);
                    lane.workerLabel.textContent = result.payload.executionLabel || 'accepted';
                    updateResponseSummary(lane, result.payload.responseSummary || result.payload.message || 'Launch accepted.');
                    updateLaneMessage(lane, result.payload.message || 'Launch accepted.');
                    appendLaneTimeline(lane, 'HTTP response returned in ' + roundtripMs + ' ms.');
                    appendLog(mode + '.launch', 'run ' + shortId(result.payload.runId) + ' accepted: ' + (result.payload.message || ''));

                    if (mode === 'sync') {
                        setLaneState(lane, 'Response carried completion proof', 'success');
                    } else {
                        setLaneState(lane, 'Response returned early', 'active');
                    }
                })
                .catch(function (error) {
                    setLaneState(lane, 'Launch failed', 'error');
                    updateLaneMessage(lane, error.message || 'Launch failed.');
                    appendLaneTimeline(lane, 'Launch failed: ' + (error.message || 'Unknown error'));
                    appendLog(mode + '.error', error.message || 'Launch failed');
                });
        }

        function applyExecutionEvent(eventName, payload) {
            if (!payload || typeof payload !== 'object') {
                return;
            }

            const lane = lanes[payload.mode];
            if (!lane) {
                return;
            }

            if (payload.run_id) {
                lane.runId.textContent = shortId(payload.run_id);
            }

            if (typeof payload.progress === 'number') {
                lane.progressBar.style.width = payload.progress + '%';
                lane.progressLabel.textContent = payload.progress + '%';
            }

            if (payload.worker_model) {
                lane.workerLabel.textContent = payload.worker_model;
            }

            if (payload.message) {
                updateLaneMessage(lane, payload.message);
            }

            if (eventName === 'demo.execution.accepted') {
                setLaneState(lane, 'Backend accepted ticket', 'active');
                appendLaneTimeline(lane, 'Backend accepted ticket for ' + shortId(payload.run_id) + '.');
            }

            if (eventName === 'demo.execution.progress') {
                setLaneState(lane, 'Running on backend', 'active');
                appendLaneTimeline(lane, (payload.title || 'Progress update') + '.');
            }

            if (eventName === 'demo.execution.completed') {
                setLaneState(lane, 'Completed', 'success');
                if (payload.duration_ms) {
                    lane.dispatchMs.textContent = payload.duration_ms + ' ms work';
                }
                appendLaneTimeline(lane, 'Completed at backend in ' + (payload.duration_ms || '?') + ' ms.');
            }

            appendLog(eventName, '[' + (payload.mode_label || payload.mode || 'unknown') + '] ' + (payload.message || stringifyPayload(payload)));
        }

        function resetLaneTimeline(lane) {
            if (lane.timeline) {
                lane.timeline.innerHTML = '';
            }
        }

        function appendLaneTimeline(lane, text) {
            if (!lane.timeline) {
                return;
            }

            const item = document.createElement('li');
            item.textContent = text;
            lane.timeline.appendChild(item);

            while (lane.timeline.children.length > 5) {
                lane.timeline.removeChild(lane.timeline.firstChild);
            }
        }

        function updateLaneMessage(lane, text) {
            if (lane.message) {
                lane.message.textContent = text;
            }
        }

        function updateResponseSummary(lane, text) {
            if (lane.responseSummary) {
                lane.responseSummary.textContent = text;
            }
        }

        function setLaneState(lane, text, variant) {
            if (!lane.state) {
                return;
            }
            lane.state.className = 'preview-pill preview-pill--' + variant;
            lane.state.textContent = text;
        }

        function setStreamStatus(text, variant) {
            if (!streamStatus) {
                return;
            }
            streamStatus.className = 'preview-pill preview-pill--' + variant;
            streamStatus.textContent = text;
        }

        function appendLog(name, text) {
            if (!logEl) {
                return;
            }

            const placeholder = logEl.querySelector('.execution-arena__log-placeholder');
            if (placeholder) {
                placeholder.remove();
            }

            const item = document.createElement('li');
            item.innerHTML =
                '<span>' + escapeHtml(name) + '</span>' +
                '<strong>' + escapeHtml(text) + '</strong>' +
                '<time>' + new Date().toLocaleTimeString() + '</time>';
            logEl.appendChild(item);

            while (logEl.children.length > 16) {
                logEl.removeChild(logEl.firstChild);
            }
        }

        function parsePayload(raw) {
            try {
                return JSON.parse(raw);
            } catch (_error) {
                return raw;
            }
        }

        function unwrapResourceData(payload) {
            if (
                payload &&
                typeof payload === 'object' &&
                payload.content &&
                typeof payload.content === 'object' &&
                payload.content.data &&
                typeof payload.content.data === 'object'
            ) {
                return payload.content.data;
            }

            return payload;
        }

        function stringifyPayload(payload) {
            if (typeof payload === 'string') {
                return payload;
            }

            try {
                return JSON.stringify(payload);
            } catch (_error) {
                return '[unserializable payload]';
            }
        }

        function createSessionId() {
            if (globalThis.crypto && typeof globalThis.crypto.randomUUID === 'function') {
                return 'arena_' + globalThis.crypto.randomUUID();
            }

            if (globalThis.crypto && typeof globalThis.crypto.getRandomValues === 'function') {
                const bytes = new Uint8Array(8);
                globalThis.crypto.getRandomValues(bytes);

                return 'arena_' + Array.from(bytes, function (value) {
                    return value.toString(16).padStart(2, '0');
                }).join('') + '_' + Date.now().toString(36);
            }

            return 'arena_' + Math.random().toString(36).slice(2, 10) + '_' + Date.now().toString(36);
        }

        function shortId(value) {
            if (!value) {
                return '—';
            }
            return String(value).slice(0, 8) + '…';
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
