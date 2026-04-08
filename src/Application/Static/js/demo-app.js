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
  var THEME_STORAGE_KEY = 'semitexa_demo_theme';
  var themeMediaQuery = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

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
      var section = toggle.closest('.feature-tree__section');
      if (!section) return;

      section.classList.toggle('feature-tree__section--open', toggle.getAttribute('aria-expanded') === 'true');

      toggle.addEventListener('click', function () {
        var expanded = toggle.getAttribute('aria-expanded') === 'true';
        var nextExpanded = expanded ? 'false' : 'true';
        toggle.setAttribute('aria-expanded', nextExpanded);
        section.classList.toggle('feature-tree__section--open', nextExpanded === 'true');
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

  function initMobileNav() {
    var body = document.body;
    var root = document.documentElement;
    var toggle = document.querySelector('[data-demo-nav-toggle]');
    var close = document.querySelector('[data-demo-nav-close]');
    var overlay = document.querySelector('[data-demo-nav-overlay]');
    var sidebar = document.querySelector('[data-demo-sidebar]');

    if (!body || !toggle || !close || !overlay || !sidebar) return;

    function setOpen(nextOpen) {
      body.classList.toggle('demo-layout--nav-open', nextOpen);
      root.classList.toggle('demo-layout--nav-open', nextOpen);
      toggle.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
      overlay.hidden = !nextOpen;
    }

    toggle.addEventListener('click', function () {
      setOpen(toggle.getAttribute('aria-expanded') !== 'true');
    });

    close.addEventListener('click', function () {
      setOpen(false);
    });

    overlay.addEventListener('click', function () {
      setOpen(false);
    });

    sidebar.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        if (window.innerWidth <= 920) {
          setOpen(false);
        }
      });
    });

    window.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        setOpen(false);
      }
    });

    window.addEventListener('resize', function () {
      if (window.innerWidth > 920) {
        setOpen(false);
      }
    });
  }

  function getPreferredTheme() {
    var storedTheme = null;

    try {
      storedTheme = window.localStorage.getItem(THEME_STORAGE_KEY);
    } catch (e) {
      storedTheme = null;
    }

    if (storedTheme === 'light' || storedTheme === 'dark') {
      return storedTheme;
    }

    if (themeMediaQuery && themeMediaQuery.matches) {
      return 'dark';
    }

    return 'light';
  }

  function applyTheme(theme) {
    var root = document.documentElement;
    var resolvedTheme = theme === 'dark' ? 'dark' : 'light';
    var isDark = resolvedTheme === 'dark';
    var nextActionLabel = isDark ? 'Light mode' : 'Dark mode';

    root.setAttribute('data-demo-theme', resolvedTheme);

    document.querySelectorAll('[data-demo-theme-toggle]').forEach(function (toggle) {
      toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
      toggle.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
      toggle.setAttribute('title', isDark ? 'Switch to light mode' : 'Switch to dark mode');
    });

    document.querySelectorAll('[data-demo-theme-text]').forEach(function (label) {
      label.textContent = nextActionLabel;
    });
  }

  function padCountdownUnit(value) {
    return String(value).padStart(2, '0');
  }

  function initCountdowns() {
    document.querySelectorAll('[data-countdown]').forEach(function (countdown) {
      var target = countdown.getAttribute('data-countdown-target');
      var targetTime = target ? Date.parse(target) : NaN;

      if (Number.isNaN(targetTime)) {
        return;
      }

      var days = countdown.querySelector('[data-countdown-days]');
      var hours = countdown.querySelector('[data-countdown-hours]');
      var minutes = countdown.querySelector('[data-countdown-minutes]');
      var seconds = countdown.querySelector('[data-countdown-seconds]');
      var state = countdown.querySelector('[data-countdown-state]');

      function render() {
        var diff = targetTime - Date.now();

        if (diff <= 0) {
          if (days) days.textContent = '00';
          if (hours) hours.textContent = '00';
          if (minutes) minutes.textContent = '00';
          if (seconds) seconds.textContent = '00';
          if (state) state.textContent = 'Released';
          return;
        }

        var totalSeconds = Math.floor(diff / 1000);
        var dayValue = Math.floor(totalSeconds / 86400);
        var hourValue = Math.floor((totalSeconds % 86400) / 3600);
        var minuteValue = Math.floor((totalSeconds % 3600) / 60);
        var secondValue = totalSeconds % 60;

        if (days) days.textContent = padCountdownUnit(dayValue);
        if (hours) hours.textContent = padCountdownUnit(hourValue);
        if (minutes) minutes.textContent = padCountdownUnit(minuteValue);
        if (seconds) seconds.textContent = padCountdownUnit(secondValue);
        if (state) state.textContent = 'Until first release';
      }

      render();
      window.setInterval(render, 1000);
    });
  }

  function initResponsiveTables() {
    var tables = document.querySelectorAll('table.preview-table, .preview-table > table, table.data-table');

    tables.forEach(function (table) {
      if (table.getAttribute('data-demo-table-ready') === 'true') {
        return;
      }

      var headers = Array.from(table.querySelectorAll('thead th')).map(function (header) {
        return (header.textContent || '').trim();
      });

      if (!headers.length) {
        return;
      }

      table.classList.add('demo-table-mobile');
      table.setAttribute('data-demo-table-ready', 'true');

      table.querySelectorAll('tbody tr').forEach(function (row) {
        var cells = row.querySelectorAll('td');

        cells.forEach(function (cell, index) {
          if (cell.hasAttribute('data-label')) {
            return;
          }

          var label = headers[index] || '';
          cell.setAttribute('data-label', label);
        });
      });
    });
  }

  function persistTheme(theme) {
    try {
      window.localStorage.setItem(THEME_STORAGE_KEY, theme);
    } catch (e) {
      // Ignore storage failures; theme still applies for current session.
    }
  }

  function initThemeToggle() {
    var toggles = document.querySelectorAll('[data-demo-theme-toggle]');
    if (!toggles.length) return;

    applyTheme(getPreferredTheme());

    toggles.forEach(function (toggle) {
      toggle.addEventListener('click', function () {
        var currentTheme = document.documentElement.getAttribute('data-demo-theme') === 'dark' ? 'dark' : 'light';
        var nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
        persistTheme(nextTheme);
        applyTheme(nextTheme);
      });
    });

    if (!themeMediaQuery) return;

    themeMediaQuery.addEventListener('change', function (event) {
      var storedTheme = null;

      try {
        storedTheme = window.localStorage.getItem(THEME_STORAGE_KEY);
      } catch (e) {
        storedTheme = null;
      }

      if (storedTheme === 'light' || storedTheme === 'dark') {
        return;
      }

      applyTheme(event.matches ? 'dark' : 'light');
    });
  }

  // --- Init ---

  function init() {
    initThemeToggle();
    trackCurrentFeature();
    updateReadyPrompt();
    initFeatureTree();
    updateVisitedIndicators();
    initNegotiationPreview();
    initMobileNav();
    initCountdowns();
    initResponsiveTables();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
