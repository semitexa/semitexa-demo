/**
 * Search Filter — clientModule for DeferredSearchFilterSlot.
 * Wires up the price range slider to its display label.
 */
(function () {
    'use strict';

    function initFilter(block) {
        var minSlider = block.querySelector('[data-filter-price-min]');
        var display   = block.querySelector('[data-price-display]');
        if (!minSlider || !display) return;

        var max = parseFloat(minSlider.max) || 1000;

        minSlider.addEventListener('input', function () {
            var min = parseFloat(minSlider.value) || 0;
            display.textContent = '$' + min.toFixed(0) + ' – $' + max.toFixed(0);
        });
    }

    document.addEventListener('semitexa:block:rendered', function (e) {
        var block = e.detail && e.detail.block;
        if (block && block.dataset.block === 'search-filter') {
            initFilter(block);
        }
    });
}());
