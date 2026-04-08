/**
 * Review Feed — clientModule for DeferredReviewFeedSlot.
 * Preserves the initial template render and prepends live backend reactions from the shared kiss stream.
 */
(function () {
    'use strict';

    function renderStars(rating) {
        var safeRating = Math.max(1, Math.min(5, parseInt(rating, 10) || 5));
        var html = '';
        var i;

        for (i = 1; i <= 5; i += 1) {
            html += '<span class="star' + (i <= safeRating ? ' star--filled' : '') + '">★</span>';
        }

        return html;
    }

    function applySchedulerTick(block, payload) {
        var review = payload && payload.review ? payload.review : null;
        var list = block.querySelector('[data-review-feed]');
        var item;

        if (!review || !list) {
            return;
        }

        item = document.createElement('li');
        item.className = 'review-feed__item';
        item.innerHTML =
            '<div class="review-feed__rating">' + renderStars(review.rating || 5) + '</div>' +
            '<p class="review-feed__body">' + escapeHtml(review.body || 'Backend reaction received.') + '</p>';

        list.insertBefore(item, list.firstChild || null);

        while (list.children.length > 4) {
            list.removeChild(list.lastElementChild);
        }
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    document.addEventListener('semitexa:deferred:message', function (e) {
        var detail = e.detail || {};
        var payload = detail.payload || {};
        var eventName = detail.eventName || '';
        var block = document.querySelector('[data-block="review-feed"]');

        if (eventName !== 'scheduler.tick' || !block) {
            return;
        }

        applySchedulerTick(block, payload);
    });
}());
