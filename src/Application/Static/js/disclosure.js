/**
 * Progressive Disclosure Controller
 *
 * Listens for disclosure:expand events and manages expandable sections,
 * deep dive drawers, and state persistence via sessionStorage.
 */
(function () {
  'use strict';

  const STORAGE_KEY = 'semitexa-demo-disclosure';

  function getState() {
    try {
      return JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '{}');
    } catch {
      return {};
    }
  }

  function saveState(state) {
    try {
      sessionStorage.setItem(STORAGE_KEY, JSON.stringify(state));
    } catch {
      // sessionStorage may be unavailable
    }
  }

  function syncTriggerState(targetId, expanded) {
    document.querySelectorAll('[data-disclosure-trigger="' + targetId + '"]').forEach(function (trigger) {
      trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    });
  }

  function toggleSection(targetId, forceOpen) {
    const target = document.getElementById(targetId);
    if (!target) return;

    const isExpanded = target.getAttribute('aria-expanded') === 'true';
    const shouldOpen = forceOpen !== undefined ? forceOpen : !isExpanded;

    target.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
    syncTriggerState(targetId, shouldOpen);

    // Persist state
    const state = getState();
    state[targetId] = shouldOpen;
    saveState(state);

    // Scroll into view on expand
    if (shouldOpen) {
      requestAnimationFrame(function () {
        const rect = target.getBoundingClientRect();
        if (rect.top < 0 || rect.top > window.innerHeight * 0.7) {
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    }
  }

  function closeDrawer(targetId) {
    const target = document.getElementById(targetId);
    if (!target) return;

    target.setAttribute('aria-expanded', 'false');
    syncTriggerState(targetId, false);

    // Restore focus to the trigger that opened this drawer
    const trigger = document.querySelector('[data-disclosure-trigger="' + targetId + '"]');
    if (trigger) {
      trigger.focus();
    }

    const state = getState();
    state[targetId] = false;
    saveState(state);
  }

  // Restore persisted state on load
  function restoreState() {
    const state = getState();
    for (const [targetId, isOpen] of Object.entries(state)) {
      if (isOpen) {
        const target = document.getElementById(targetId);
        if (target) {
          target.setAttribute('aria-expanded', 'true');
        }
      }
    }
  }

  // Mark first-view prompts
  function markFirstViewPrompts() {
    const state = getState();
    document.querySelectorAll('[data-disclosure-trigger]').forEach(function (trigger) {
      const targetId = trigger.getAttribute('data-disclosure-trigger');
      if (!state[targetId]) {
        trigger.setAttribute('data-first-view', 'true');
      }
    });
  }

  // Event delegation for disclosure triggers
  document.addEventListener('click', function (e) {
    // Disclosure prompt buttons
    var trigger = e.target.closest('[data-disclosure-trigger]');
    if (trigger) {
      var targetId = trigger.getAttribute('data-disclosure-trigger');
      toggleSection(targetId, true);
      trigger.removeAttribute('data-first-view');

      // Dispatch custom event
      document.dispatchEvent(new CustomEvent('disclosure:expand', {
        detail: { targetId: targetId },
      }));
      return;
    }

    // Drawer close buttons and overlays
    var closeBtn = e.target.closest('[data-drawer-close]');
    if (closeBtn) {
      var drawer = closeBtn.closest('[data-drawer]');
      if (drawer) {
        closeDrawer(drawer.id);
      }
    }
  });

  // Close drawer on Escape key
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      var openDrawer = document.querySelector('[data-drawer][aria-expanded="true"]');
      if (openDrawer) {
        closeDrawer(openDrawer.id);
      }
    }
  });

  // Listen for programmatic disclosure:expand events
  document.addEventListener('disclosure:expand', function (e) {
    if (e.detail && e.detail.targetId) {
      toggleSection(e.detail.targetId, true);
    }
  });

  // Initialize on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      restoreState();
      markFirstViewPrompts();
    });
  } else {
    restoreState();
    markFirstViewPrompts();
  }
})();
