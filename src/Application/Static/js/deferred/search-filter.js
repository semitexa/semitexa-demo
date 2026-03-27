/**
 * Search Filter — clientModule for DeferredSearchFilterSlot.
 * Wires up the price range slider to its display label.
 */
(function () {
    'use strict';

    function initFilter(block) {
        if (block.dataset.filterInitialized === '1') return;
        block.dataset.filterInitialized = '1';

        var form      = block.querySelector('[data-search-filter]');
        var minSlider = block.querySelector('[data-filter-price-min]');
        var category  = block.querySelector('[data-filter-category]');
        var display   = block.querySelector('[data-price-display]');
        var summary   = block.querySelector('[data-filter-summary]');
        if (!form || !minSlider || !display || !category) return;

        var minBound = parseFloat(minSlider.min) || 0;
        var max = parseFloat(minSlider.max) || 1000;
        var params = new URLSearchParams(window.location.search);

        if (params.has('category')) {
            category.value = params.get('category') || '';
        }
        if (params.has('price_min')) {
            minSlider.value = params.get('price_min') || String(minBound);
        }

        function render() {
            var min = parseFloat(minSlider.value) || 0;
            display.textContent = '$' + min.toFixed(0) + ' – $' + max.toFixed(0);

            if (!summary) return;

            var categoryLabel = category.options[category.selectedIndex]
                ? category.options[category.selectedIndex].textContent
                : 'All';

            if ((category.value || '') === '' && min <= minBound) {
                summary.textContent = 'Currently showing the default deferred query state.';
                return;
            }

            summary.textContent = 'Scoped to ' + categoryLabel + ' with a minimum price of $' + min.toFixed(0) + '.';
        }

        minSlider.addEventListener('input', render);
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            var next = new URLSearchParams(window.location.search);
            var min = parseFloat(minSlider.value) || 0;

            if ((category.value || '') !== '') {
                next.set('category', category.value);
            } else {
                next.delete('category');
            }

            if (min > minBound) {
                next.set('price_min', String(Math.round(min)));
            } else {
                next.delete('price_min');
            }

            var nextUrl = window.location.pathname + (next.toString() ? '?' + next.toString() : '');
            window.history.replaceState({}, '', nextUrl);
            render();
        });

        render();
    }

    document.addEventListener('semitexa:block:rendered', function (e) {
        var block = e.detail && e.detail.block;
        if (block && block.dataset.block === 'search-filter') {
            initFilter(block);
        }
    });
}());
