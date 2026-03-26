/**
 * Chart Widget — clientModule for DeferredChartWidgetSlot.
 * Draws a simple bar chart on canvas when the block arrives.
 */
(function () {
    'use strict';

    function drawBarChart(canvas, data) {
        var ctx = canvas.getContext('2d');
        if (!ctx) return;

        var labels = data.labels || [];
        var values = data.values || [];
        var max    = Math.max.apply(null, values) || 1;
        var w = canvas.width;
        var h = canvas.height;
        var barCount = labels.length;
        var barW = barCount > 0 ? Math.floor((w - 40) / barCount) - 4 : 0;

        ctx.clearRect(0, 0, w, h);
        ctx.fillStyle = '#f8fafc';
        ctx.fillRect(0, 0, w, h);

        values.forEach(function (val, i) {
            var barH = Math.round(((val / max) * (h - 40)));
            var x = 20 + i * (barW + 4);
            var y = h - 20 - barH;

            ctx.fillStyle = '#3b82f6';
            ctx.fillRect(x, y, barW, barH);

            ctx.fillStyle = '#64748b';
            ctx.font = '11px sans-serif';
            ctx.textAlign = 'center';
            if (labels[i]) ctx.fillText(String(labels[i]).slice(0, 8), x + barW / 2, h - 4);
        });
    }

    document.addEventListener('semitexa:block:rendered', function (e) {
        var block = e.detail && e.detail.block;
        if (!block || block.dataset.block !== 'chart-widget') return;

        var canvas = block.querySelector('.chart-canvas');
        if (!canvas) return;

        var raw = canvas.dataset.chartData;
        try {
            var data = raw ? JSON.parse(raw) : {};
            drawBarChart(canvas, data);
        } catch (_) {}
    });
}());
