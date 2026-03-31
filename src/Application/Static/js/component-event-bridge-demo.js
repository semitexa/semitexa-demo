(function () {
    'use strict';

    function init() {
        var consoles = document.querySelectorAll('[data-component-bridge-console]');
        if (!consoles.length) {
            return;
        }

        document.addEventListener('semitexa:component-event:accepted', function (event) {
            render(consoles, event.detail, 'accepted');
        });

        document.addEventListener('semitexa:component-event:failed', function (event) {
            render(consoles, event.detail, 'failed');
        });
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
