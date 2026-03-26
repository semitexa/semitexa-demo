/**
 * resolver-chain-viz.js
 * Animates the resolver chain visualization: highlights strategies one by one
 * until the winning resolver is found.
 */
(function () {
    'use strict';

    function animateChain(root) {
        var nodes = Array.from(root.querySelectorAll('[data-strategy]'));
        var winner = root.querySelector('.resolver-node--winner');

        nodes.forEach(function (node) {
            node.classList.remove('resolver-node--active', 'resolver-node--skipped');
        });

        var delay = 0;
        nodes.forEach(function (node) {
            setTimeout(function () {
                if (node === winner) {
                    node.classList.add('resolver-node--active');
                } else if (winner && nodes.indexOf(node) < nodes.indexOf(winner)) {
                    node.classList.add('resolver-node--skipped');
                }
            }, delay);
            delay += 220;
        });
    }

    function init() {
        var chains = document.querySelectorAll('[data-resolver-chain]');
        chains.forEach(function (chain) {
            // Auto-animate on load
            setTimeout(function () { animateChain(chain); }, 400);

            // Re-animate on button click if present
            var replayBtn = chain.querySelector('[data-replay-chain]');
            if (replayBtn) {
                replayBtn.addEventListener('click', function () { animateChain(chain); });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', init);
}());
