/**
 * reactive/analytics-panels.js
 * Handles panel reveal animations for the reactive analytics slot.
 * Each metric panel animates in independently as snapshot data arrives.
 * Triggered on `semitexa:block:rendered` for handle `demo_reactive_analytics`.
 */
(function () {
    'use strict';

    function formatValue(type, value) {
        if (value === null || value === undefined) return '—';
        var num = parseFloat(value);
        switch (type) {
            case 'pageviews':
                return Number.isFinite(num) ? Math.round(num).toLocaleString() : '—';
            case 'conversions':
                return Number.isFinite(num) ? (num * 100).toFixed(2) + '%' : '—';
            case 'top_products':
                return Number.isFinite(num) ? Math.round(num).toString() : '—';
            default:
                return String(value);
        }
    }

    function animatePanel(panel) {
        panel.classList.remove('analytics-panel--hidden');
        panel.classList.add('analytics-panel--revealed');

        var valueEl = panel.querySelector('[data-panel-value]');
        if (!valueEl) return;

        var type = panel.getAttribute('data-metric-type') || valueEl.getAttribute('data-metric-type') || '';
        var raw = valueEl.getAttribute('data-raw-value');
        valueEl.textContent = formatValue(type, raw);
    }

    function initPanels(root) {
        var panels = root.querySelectorAll('[data-analytics-panel], .analytics-panel');
        panels.forEach(function (panel, i) {
            setTimeout(function () { animatePanel(panel); }, i * 120);
        });
    }

    document.addEventListener('semitexa:block:rendered', function (e) {
        if (e.detail && e.detail.handle === 'demo_reactive_analytics') {
            var slot = document.querySelector('[data-slot="reactive_analytics"]');
            if (slot) initPanels(slot);
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        var slot = document.querySelector('[data-slot="reactive_analytics"]');
        if (slot) initPanels(slot);
    });
}());
