/**
 * reactive/report-chart.js
 * Renders a bar chart from chart data embedded in the reactive report slot.
 * Triggered on `semitexa:block:rendered` for handle `demo_reactive_report`.
 */
(function () {
    'use strict';

    function drawChart(canvas, chartData) {
        const ctx = canvas.getContext('2d');
        if (!ctx || !chartData) return;

        const { labels, values } = chartData;
        const W = canvas.width;
        const H = canvas.height;
        const max = Math.max(...values, 1);
        const pad = 30;
        const barW = Math.floor((W - pad * 2) / labels.length);

        ctx.clearRect(0, 0, W, H);

        labels.forEach(function (label, i) {
            const barH = Math.round(((values[i] || 0) / max) * (H - pad * 2));
            const x = pad + i * barW + 4;
            const y = H - pad - barH;

            ctx.fillStyle = '#4f8ef7';
            ctx.fillRect(x, y, barW - 8, barH);

            ctx.fillStyle = '#555';
            ctx.font = '11px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(label, x + (barW - 8) / 2, H - 8);

            ctx.fillStyle = '#222';
            ctx.fillText(String(values[i] || 0), x + (barW - 8) / 2, y - 4);
        });
    }

    function initChart(root) {
        var canvas = root.querySelector('[data-report-chart], [data-chart-data]');
        if (!canvas) return;

        try {
            var raw = canvas.getAttribute('data-chart-json') || canvas.getAttribute('data-chart-data');
            if (!raw) return;
            var chartData = JSON.parse(raw);
            drawChart(canvas, chartData);
        } catch (e) {
            // malformed data — skip
        }
    }

    document.addEventListener('semitexa:block:rendered', function (e) {
        if (e.detail && e.detail.handle === 'demo_reactive_report') {
            var slot = document.querySelector('[data-slot="reactive_report"]');
            if (slot) initChart(slot);
        }
    });

    // Also run on initial load if already rendered
    document.addEventListener('DOMContentLoaded', function () {
        var slot = document.querySelector('[data-slot="reactive_report"]');
        if (slot) initChart(slot);
    });
}());
