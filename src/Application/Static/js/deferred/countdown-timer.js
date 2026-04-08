/**
 * Countdown Timer — clientModule for DeferredCountdownSlot.
 * Starts a countdown when the block arrives. Scoped by data-instance.
 */
(function () {
    'use strict';

    var timers = {};

    function bindRestart(block) {
        var restart = block.querySelector('[data-countdown-restart]');
        if (!restart || restart.dataset.bound === '1') {
            return;
        }

        restart.dataset.bound = '1';
        restart.addEventListener('click', function () {
            startCountdown(block);
            block.classList.remove('deferred-block--restarted');
            void block.offsetWidth;
            block.classList.add('deferred-block--restarted');
        });
    }

    function startCountdown(block) {
        var el       = block.querySelector('[data-countdown]');
        if (!el) return;

        var instanceId = el.dataset.instance || block.dataset.instance || Math.random().toString(36).slice(2);
        var durationRaw = parseInt(el.dataset.duration, 10);
        var duration = Number.isFinite(durationRaw) && durationRaw > 0 ? durationRaw : 60;
        var display  = el.querySelector('[data-countdown-display]');
        var progress = el.querySelector('[data-countdown-progress]');
        var summary = block.querySelector('[data-countdown-summary]');
        var remaining = duration;

        if (timers[instanceId]) {
            clearInterval(timers[instanceId]);
            delete timers[instanceId];
        }

        if (display)  display.textContent = duration + 's';
        if (progress) progress.style.width = '100%';
        if (summary) summary.textContent = 'The timer is counting down to the next expected backend heartbeat.';

        timers[instanceId] = setInterval(function () {
            remaining = Math.max(0, remaining - 1);
            var pct = Math.round((remaining / duration) * 100);

            if (display)  display.textContent = remaining + 's';
            if (progress) progress.style.width = pct + '%';

            if (remaining <= 0) {
                clearInterval(timers[instanceId]);
                delete timers[instanceId];
                if (display) display.textContent = 'Tick…';
                if (summary) summary.textContent = 'Waiting for the backend heartbeat to re-synchronize this timer.';
            }
        }, 1000);
    }

    function initBlock(block) {
        if (!block || block.dataset.block !== 'countdown-timer') {
            return;
        }

        bindRestart(block);

        if (block.dataset.countdownInitialized === '1') {
            return;
        }

        block.dataset.countdownInitialized = '1';
        startCountdown(block);
    }

    document.addEventListener('semitexa:block:rendered', function (e) {
        var block = e.detail && e.detail.block;
        initBlock(block);
    });

    document.addEventListener('semitexa:deferred:message', function (e) {
        var detail = e.detail || {};
        var payload = detail.payload || {};
        var countdown = payload && payload.countdown ? payload.countdown : null;
        var eventName = detail.eventName || '';
        var block = document.querySelector('[data-block="countdown-timer"]');
        var summary;
        var countdownEl;

        if (eventName !== 'scheduler.tick' || !block) {
            return;
        }

        countdownEl = block.querySelector('[data-countdown]');
        summary = block.querySelector('[data-countdown-summary]');

        if (countdown && countdown.duration_seconds && countdownEl) {
            countdownEl.dataset.duration = String(countdown.duration_seconds);
        }

        startCountdown(block);
        block.classList.remove('deferred-block--restarted');
        void block.offsetWidth;
        block.classList.add('deferred-block--restarted');

        if (summary) {
            summary.textContent = countdown && countdown.summary
                ? countdown.summary
                : 'Timer re-synced from the latest backend heartbeat.';
        }
    });

    function initExisting() {
        document.querySelectorAll('[data-block="countdown-timer"]').forEach(initBlock);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initExisting);
    } else {
        initExisting();
    }
}());
