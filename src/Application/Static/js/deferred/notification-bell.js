/**
 * Notification Bell — clientModule for DeferredNotificationSlot.
 * Animates the badge when the count changes on refresh.
 */
(function () {
    'use strict';

    var lastCount = null;

    document.addEventListener('semitexa:block:rendered', function (e) {
        var block = e.detail && e.detail.block;
        if (!block || block.dataset.block !== 'notification-bell') return;

        var badge = block.querySelector('[data-notification-count]');
        if (!badge) return;

        var count = parseInt(badge.textContent, 10) || 0;

        if (lastCount !== null && count !== lastCount) {
            badge.classList.add('notification-bell__badge--pulse');
            setTimeout(function () {
                badge.classList.remove('notification-bell__badge--pulse');
            }, 600);
        }

        lastCount = count;
    });
}());
