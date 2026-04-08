/**
 * Chart Widget — clientModule for DeferredChartWidgetSlot.
 * Draws a simple bar chart on canvas when the block arrives.
 */
(function () {
    'use strict';

    var themeObserverInitialized = false;

    function getCssVar(name, fallback) {
        var value = window.getComputedStyle(document.documentElement).getPropertyValue(name);
        value = value ? value.trim() : '';
        return value || fallback;
    }

    function getChartPalette() {
        var isDark = document.documentElement.getAttribute('data-demo-theme') === 'dark';

        return {
            background: getCssVar('--demo-elevated-bg', isDark ? '#101821' : '#fff9f1'),
            bar: getCssVar('--demo-accent', isDark ? '#ef8c68' : '#b63a2f'),
            label: getCssVar('--demo-text-muted', isDark ? '#c2b3a7' : '#6b625c'),
            value: getCssVar('--demo-text', isDark ? '#f2e9e0' : '#1f1b18')
        };
    }

    function drawBarChart(canvas, data) {
        var ctx = canvas.getContext('2d');
        if (!ctx) return;

        var palette = getChartPalette();
        var labels = data.labels || [];
        var values = data.values || [];
        var max    = Math.max.apply(null, values) || 1;
        var w = canvas.width;
        var h = canvas.height;
        var barCount = labels.length;
        var barW = barCount > 0 ? Math.floor((w - 40) / barCount) - 4 : 0;

        ctx.clearRect(0, 0, w, h);
        ctx.fillStyle = palette.background;
        ctx.fillRect(0, 0, w, h);

        values.forEach(function (val, i) {
            var barH = Math.round(((val / max) * (h - 40)));
            var x = 20 + i * (barW + 4);
            var y = h - 20 - barH;

            ctx.fillStyle = palette.bar;
            ctx.fillRect(x, y, barW, barH);

            ctx.fillStyle = palette.label;
            ctx.font = '11px sans-serif';
            ctx.textAlign = 'center';
            if (labels[i]) ctx.fillText(String(labels[i]).slice(0, 8), x + barW / 2, h - 4);

            ctx.fillStyle = palette.value;
            ctx.fillText(String(val), x + barW / 2, Math.max(14, y - 6));
        });
    }

    function renderCanvas(canvas) {
        if (!canvas) return;

        var raw = canvas.dataset.chartData;
        try {
            var data = raw ? JSON.parse(raw) : {};
            drawBarChart(canvas, data);
        } catch (_) {}
    }

    function initThemeObserver() {
        if (themeObserverInitialized || typeof MutationObserver === 'undefined') return;

        themeObserverInitialized = true;

        new MutationObserver(function (mutations) {
            for (var i = 0; i < mutations.length; i++) {
                if (mutations[i].attributeName === 'data-demo-theme') {
                    document.querySelectorAll('.chart-canvas[data-chart-data]').forEach(renderCanvas);
                    break;
        }
    }

    function initBlock(block) {
        if (!block || block.dataset.block !== 'chart-widget') {
            return;
        }

        var canvas = block.querySelector('.chart-canvas');
        if (!canvas) {
            return;
        }

        renderCanvas(canvas);
    }

    function applySchedulerTick(block, payload) {
        var chart = payload && payload.chart ? payload.chart : null;
        var canvas = block.querySelector('.chart-canvas');
        var summary = block.querySelector('[data-chart-summary]');

        if (!chart || !canvas) {
            return;
        }

        canvas.dataset.chartData = JSON.stringify({
            labels: Array.isArray(chart.labels) ? chart.labels : [],
            values: Array.isArray(chart.values) ? chart.values : []
        });

        renderCanvas(canvas);

        if (summary) {
            summary.textContent = chart.summary || 'Backend metrics snapshot refreshed from the shared kiss stream.';
        }
    }
        }).observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-demo-theme']
        });
    }

    document.addEventListener('semitexa:block:rendered', function (e) {
        var block = e.detail && e.detail.block;
        initBlock(block);
    });

    document.addEventListener('semitexa:deferred:message', function (e) {
        var detail = e.detail || {};
        var payload = detail.payload || {};
        var eventName = detail.eventName || '';
        var block = document.querySelector('[data-block="chart-widget"]');

        if (eventName !== 'scheduler.tick' || !block) {
            return;
        }

        applySchedulerTick(block, payload);
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initThemeObserver);
    } else {
        initThemeObserver();
    }
}());
