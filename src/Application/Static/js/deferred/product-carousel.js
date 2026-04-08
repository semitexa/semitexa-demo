/**
 * Product Carousel — clientModule for DeferredProductCarouselSlot.
 * Auto-plays after the block arrives via semitexa:block:rendered.
 */
(function () {
    'use strict';

    function initCarousel(block) {
        if (block.dataset.carouselInitialized === '1') {
            return;
        }

        var items  = block.querySelectorAll('.carousel__item');
        var prev   = block.querySelector('[data-carousel-prev]');
        var next   = block.querySelector('[data-carousel-next]');
        if (!items.length) return;

        var current = 0;

        block.dataset.carouselInitialized = '1';

        function show(index) {
            items.forEach(function (item, i) {
                item.style.display = i === index ? '' : 'none';
            });
            current = index;
        }

        show(0);

        if (prev) prev.addEventListener('click', function () {
            show((current - 1 + items.length) % items.length);
        });
        if (next) next.addEventListener('click', function () {
            show((current + 1) % items.length);
        });

        // Auto-advance every 4 seconds
        window.setInterval(function () {
            show((current + 1) % items.length);
        }, 4000);
    }

    document.addEventListener('semitexa:block:rendered', function (e) {
        var block = e.detail && e.detail.block;
        if (block && block.dataset.block === 'product-carousel') {
            initCarousel(block);
        }
    });
}());
