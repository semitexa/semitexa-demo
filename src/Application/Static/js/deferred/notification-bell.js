/**
 * Notification Bell — clientModule for DeferredNotificationSlot.
 * Renders initial template output, then appends live scheduler messages from the shared kiss stream.
 */
(function () {
    'use strict';

    function ensureBadge(block, count) {
        var bell = block.querySelector('[data-notification-bell]');
        if (!bell) {
            bell = block.querySelector('[data-notification-bell]') || block.querySelector('.notification-bell');
        }
        if (!bell) return null;

        var badge = block.querySelector('[data-notification-count]');
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'notification-bell__badge';
            badge.setAttribute('data-notification-count', '');
            bell.appendChild(badge);
        }

        badge.textContent = String(count);

        return badge;
    }

    function pulseBadge(badge) {
        if (!badge) return;

        badge.classList.remove('notification-bell__badge--pulse');
        void badge.offsetWidth;
        badge.classList.add('notification-bell__badge--pulse');
        window.setTimeout(function () {
            badge.classList.remove('notification-bell__badge--pulse');
        }, 600);
    }

    function initBlock(block) {
        if (!block || block.dataset.block !== 'notification-bell' || block.dataset.notificationInitialized === '1') {
            return;
        }

        var badge = block.querySelector('[data-notification-count]');
        var count = badge ? (parseInt(badge.textContent, 10) || 0) : 0;
        block.dataset.notificationInitialized = '1';
        block.dataset.notificationCount = String(count);
    }

    function applySchedulerTick(block, payload) {
        var notification = payload && payload.notification ? payload.notification : {};
        var list = block.querySelector('[data-notification-list]');
        var summary = block.querySelector('[data-notification-summary]');
        var currentCount = parseInt(block.dataset.notificationCount || '0', 10) || 0;
        var nextCount = currentCount + Math.max(1, parseInt(notification.count_delta || '1', 10) || 1);
        var badge = ensureBadge(block, nextCount);

        block.dataset.notificationCount = String(nextCount);
        pulseBadge(badge);

        if (summary) {
            summary.textContent = notification.message || 'Backend heartbeat delivered through the shared kiss stream.';
        }

        if (!list) {
            return;
        }

        var item = document.createElement('li');
        item.className = 'notification-list__item notification-list__item--' + (notification.level || 'info');
        item.textContent = notification.message || 'Backend heartbeat delivered through the shared kiss stream.';
        list.insertBefore(item, list.firstChild || null);

        while (list.children.length > 5) {
            list.removeChild(list.lastElementChild);
        }
    }

    document.addEventListener('semitexa:block:rendered', function (e) {
        var block = e.detail && e.detail.block;
        initBlock(block);
    });

    document.addEventListener('semitexa:deferred:message', function (e) {
        var detail = e.detail || {};
        var payload = detail.payload || {};
        var eventName = detail.eventName || '';
        var block = document.querySelector('[data-block="notification-bell"]');

        if (eventName !== 'scheduler.tick' || !block) {
            return;
        }

        initBlock(block);
        applySchedulerTick(block, payload);
    });
}());
