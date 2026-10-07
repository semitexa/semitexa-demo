(function () {
    'use strict';

    function init() {
        var consoles = document.querySelectorAll('[data-component-bridge-console]');
        if (!consoles.length) {
            return;
        }

        // The component's click goes through the one UI event runtime; its
        // lifecycle events carry HUG's answer.
        document.addEventListener('semitexa:ui-event:dispatched', function (event) {
            if (isDisclosure(event.detail)) render(consoles, event.detail.response, 'accepted');
        });

        document.addEventListener('semitexa:ui-event:failed', function (event) {
            if (isDisclosure(event.detail)) render(consoles, event.detail.response || event.detail, 'failed');
        });
    }

    function isDisclosure(detail) {
        return !!(detail && detail.captured && detail.captured.component === 'demo-disclosure-prompt');
    }

    function render(nodes, detail, status) {
        nodes.forEach(function (node) {
            var body = node.querySelector('.component-bridge-preview__console-body');
            if (!body) {
                return;
            }

            node.setAttribute('data-status', status);
            body.textContent = JSON.stringify(detail, null, 2);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
