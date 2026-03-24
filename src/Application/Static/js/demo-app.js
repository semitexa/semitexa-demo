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

      var sectionToggle = section.querySelector('.feature-tree__toggle');
      var sectionKey = sectionToggle ? sectionToggle.textContent.trim().toLowerCase().replace(/\s+/g, '-') : '';

      // Check if this feature has been visited
      for (var i = 0; i < visited.length; i++) {
        if (visited[i].indexOf('/' + slug) !== -1) {
          link.classList.add('feature-tree__link--visited');
          break;
        }
      }
    });
  }

  // --- Init ---

  function init() {
    trackCurrentFeature();
    updateReadyPrompt();
    initFeatureTree();
    updateVisitedIndicators();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
