/**
 * reactive/import-counters.js
 * Animates row counters in the reactive import slot.
 * Triggered on `semitexa:block:rendered` for handle `demo_reactive_import`.
 */
(function () {
    'use strict';

    function animateCounter(el) {
        var target = parseInt(el.getAttribute('data-target') || '0', 10);
        var currentText = (el.textContent || '0').replace(/[^\d-]/g, '');
        var current = parseInt(currentText || '0', 10);
        if (isNaN(target) || current === target) return;

        var steps = 20;
        var step = Math.ceil(Math.abs(target - current) / steps);
        var direction = target > current ? 1 : -1;

        var interval = setInterval(function () {
            current += direction * step;
            if ((direction > 0 && current >= target) || (direction < 0 && current <= target)) {
                current = target;
                clearInterval(interval);
            }
            el.textContent = current.toLocaleString();
        }, 30);
    }

    function initCounters(root) {
        var counters = root.querySelectorAll('[data-counter]');
        counters.forEach(animateCounter);

        var progressBar = root.querySelector('[data-import-progress-bar], [data-import-progress]');
        if (progressBar) {
            var pct = progressBar.getAttribute('data-percent') || progressBar.textContent || '0';
            progressBar.style.width = String(parseInt(pct, 10) || 0) + '%';
        }
    }

    document.addEventListener('semitexa:block:rendered', function (e) {
        if (e.detail && e.detail.handle === 'demo_reactive_import') {
            var slot = document.querySelector('[data-slot="reactive_import"]');
            if (slot) initCounters(slot);
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        var slot = document.querySelector('[data-slot="reactive_import"]');
        if (slot) initCounters(slot);
    });
}());
