/**
 * Countdown Timer — clientModule for DeferredCountdownSlot.
 * Starts a countdown when the block arrives. Scoped by data-instance.
 */
(function () {
    'use strict';

    var timers = {};

    function startCountdown(block) {
        var el       = block.querySelector('[data-countdown]');
        if (!el) return;

        var instanceId = el.dataset.instance || block.dataset.instance || Math.random().toString(36).slice(2);
        if (timers[instanceId]) return; // Already running

        var duration = parseInt(el.dataset.duration, 10) || 60;
        var display  = el.querySelector('[data-countdown-display]');
        var progress = el.querySelector('[data-countdown-progress]');
        var remaining = duration;

        timers[instanceId] = setInterval(function () {
            remaining = Math.max(0, remaining - 1);
            var pct = Math.round((remaining / duration) * 100);

            if (display)  display.textContent = remaining + 's';
            if (progress) progress.style.width = pct + '%';

            if (remaining <= 0) {
                clearInterval(timers[instanceId]);
                delete timers[instanceId];
                if (display) display.textContent = 'Done!';
            }
        }, 1000);
    }

    document.addEventListener('semitexa:block:rendered', function (e) {
        var block = e.detail && e.detail.block;
        if (block && block.dataset.block === 'countdown-timer') {
            startCountdown(block);
        }
    });
}());
