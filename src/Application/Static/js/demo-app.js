/**
 * Semitexa Demo — Interaction Orchestrator
 *
 * Feature card navigation, visited tracking (cookie),
 * "Ready for more?" prompt visibility, feature tree toggle.
 */
(function () {
  'use strict';

  var VISITED_COOKIE = 'semitexa_demo_visited';
  var READY_THRESHOLD = 3;

  // --- Cookie helpers ---

  function getVisited() {
    var match = document.cookie.match(new RegExp('(?:^|; )' + VISITED_COOKIE + '=([^;]*)'));
    if (!match) return [];
    try {
      return JSON.parse(decodeURIComponent(match[1]));
    } catch (e) {
      return [];
    }
  }

  function setVisited(list) {
    var unique = Array.from(new Set(list));
    var value = encodeURIComponent(JSON.stringify(unique));
    document.cookie = VISITED_COOKIE + '=' + value + '; path=/demo; max-age=31536000; SameSite=Lax';
    return unique;
  }

  function markVisited(slug) {
    var visited = getVisited();
    if (visited.indexOf(slug) === -1) {
      visited.push(slug);
      setVisited(visited);
    }
    return visited;
  }

  // --- Track current feature visit ---

  function trackCurrentFeature() {
    var detail = document.querySelector('.feature-detail');
    if (!detail) return;

    var section = detail.getAttribute('data-section');
    var slug = detail.getAttribute('data-slug');
    if (section && slug) {
      return markVisited(section + '/' + slug);
    }
    return getVisited();
  }

  // --- "Ready for more?" prompt ---

  function updateReadyPrompt() {
    var prompt = document.querySelector('[data-ready-prompt]');
    if (!prompt) return;

    var visited = getVisited();
    if (visited.length >= READY_THRESHOLD) {
      prompt.style.display = '';
    }
  }

  // --- Feature tree toggle ---

  function initFeatureTree() {
    document.querySelectorAll('.feature-tree__toggle').forEach(function (toggle) {
      toggle.addEventListener('click', function () {
        var expanded = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
      });
    });
  }

  // --- Visited indicators in feature tree ---

  function updateVisitedIndicators() {
    var visited = getVisited();
    document.querySelectorAll('[data-feature-slug]').forEach(function (link) {
      var slug = link.getAttribute('data-feature-slug');
      var section = link.closest('.feature-tree__section');
      if (!section) return;

      var sectionKey = section.getAttribute('data-section-key') || '';
      if (!sectionKey) return;

      // Check if this feature has been visited
      var expectedKey = sectionKey + '/' + slug;
      for (var i = 0; i < visited.length; i++) {
        if (visited[i] === expectedKey) {
          link.classList.add('feature-tree__link--visited');
          break;
        }
      }
    });
  }

  function initNegotiationPreview() {
    document.querySelectorAll('[data-negotiation-preview]').forEach(function (preview) {
      var tabs = preview.querySelectorAll('[data-negotiation-tab]');
      var panels = preview.querySelectorAll('[data-negotiation-panel]');

      tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
          var target = tab.getAttribute('data-negotiation-tab');

          tabs.forEach(function (item) {
            var active = item === tab;
            item.classList.toggle('preview-negotiation__tab--active', active);
            item.setAttribute('aria-selected', active ? 'true' : 'false');
          });

          panels.forEach(function (panel) {
            var active = panel.getAttribute('data-negotiation-panel') === target;
            panel.classList.toggle('preview-negotiation__panel--active', active);
            panel.hidden = !active;
          });
        });
      });
    });
  }

  // --- Init ---

  function init() {
    trackCurrentFeature();
    updateReadyPrompt();
    initFeatureTree();
    updateVisitedIndicators();
    initNegotiationPreview();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
