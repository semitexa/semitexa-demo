(function () {
    'use strict';

    function init() {
        if (!document.querySelector('[data-component-bridge-console]')) {
            return;
        }

        // The component's click goes through the one UI event runtime; its
        // lifecycle events carry HUG's answer.
        document.addEventListener('semitexa:ui-event:dispatched', function (event) {
            if (isDisclosure(event.detail)) render(consoleFor(event.detail), event.detail.response, 'accepted');
        });

        document.addEventListener('semitexa:ui-event:failed', function (event) {
            if (isDisclosure(event.detail)) render(consoleFor(event.detail), event.detail.response || event.detail, 'failed');
        });
    }

    function isDisclosure(detail) {
        return !!(detail && detail.captured && detail.captured.component === 'demo-disclosure-prompt');
    }

    // The console of the preview the clicked component lives in, never another preview's on the same page.
    // Uses the documented capture contract only: the native event's target, or
    // (for a programmatic dispatch, which has no DOM event) the instance root.
    function consoleFor(detail) {
        var captured = detail.captured;
        var el = captured.originalEvent && captured.originalEvent.target;
        if (!el && typeof captured.instanceId === 'string' && /^[A-Za-z0-9_-]+$/.test(captured.instanceId)) {
            el = document.querySelector('[data-ui-component-instance-id="' + captured.instanceId + '"]');
        }
        var preview = el && typeof el.closest === 'function' ? el.closest('.component-bridge-preview') : null;
        return preview ? preview.querySelector('[data-component-bridge-console]') : null;
    }

    function render(node, detail, status) {
        var body = node ? node.querySelector('.component-bridge-preview__console-body') : null;
        if (!body) {
            return;
        }

        node.setAttribute('data-status', status);
        body.textContent = JSON.stringify(detail, null, 2);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
