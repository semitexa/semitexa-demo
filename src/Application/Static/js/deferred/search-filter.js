/**
 * Search Filter — clientModule for DeferredSearchFilterSlot.
 * Wires up the price range slider to its display label.
 */
(function () {
    'use strict';

    function initFilter(block) {
        if (block.dataset.filterInitialized === '1') return;
        block.dataset.filterInitialized = '1';

        var minSlider = block.querySelector('[data-filter-price-min]');
        var display   = block.querySelector('[data-price-display]');
        if (!minSlider || !display) return;

        var max = parseFloat(minSlider.max) || 1000;

        function render() {
            var min = parseFloat(minSlider.value) || 0;
            display.textContent = '$' + min.toFixed(0) + ' – $' + max.toFixed(0);
        }

        minSlider.addEventListener('input', render);
        render();
    }

    document.addEventListener('semitexa:block:rendered', function (e) {
        var block = e.detail && e.detail.block;
        if (block && block.dataset.block === 'search-filter') {
            initFilter(block);
        }
    });
}());
