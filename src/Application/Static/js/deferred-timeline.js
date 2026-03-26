/**
 * Deferred Timeline — visualizes deferred block arrival sequence.
 * Watches for semitexa:block:rendered events and animates a progress bar.
 */
(function () {
    'use strict';

    var TOTAL_BLOCKS = 6;
    var arrivedCount = 0;
    var startTime = null;

    function init() {
        var timeline = document.querySelector('[data-timeline]');
        if (!timeline) return;

        var fill  = timeline.querySelector('.deferred-timeline__fill');
        var label = timeline.querySelector('.deferred-timeline__label');
        var total = parseInt(timeline.getAttribute('data-total-blocks') || '', 10);
        if (Number.isFinite(total) && total > 0) {
            TOTAL_BLOCKS = total;
        }
        startTime = Date.now();

        document.addEventListener('semitexa:block:rendered', function (e) {
            var block = e.detail && e.detail.block;
            if (!block || !timeline.contains(block)) return;

            arrivedCount++;
            var elapsed = Date.now() - startTime;
            var pct = Math.round((arrivedCount / TOTAL_BLOCKS) * 100);

            if (fill)  fill.style.width = pct + '%';
            if (label) label.textContent = arrivedCount + ' / ' + TOTAL_BLOCKS + ' blocks loaded (' + elapsed + 'ms)';

            if (arrivedCount >= TOTAL_BLOCKS && label) {
                label.textContent = 'All ' + TOTAL_BLOCKS + ' blocks loaded in ' + elapsed + 'ms';
                label.classList.add('deferred-timeline__label--done');
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
