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
      const parsed = JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '{}');
      return parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? parsed : {};
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

    const isExpanded = target.getAttribute('data-expanded') === 'true';
    const shouldOpen = forceOpen !== undefined ? forceOpen : !isExpanded;

    target.setAttribute('data-expanded', shouldOpen ? 'true' : 'false');
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

    // Restore focus to the trigger that opened this drawer
    const trigger = document.querySelector('[data-disclosure-trigger="' + targetId + '"]');
    if (trigger) {
      trigger.focus();
    } else if (document.activeElement instanceof HTMLElement && target.contains(document.activeElement)) {
      document.body.focus();
    }

    target.setAttribute('data-expanded', 'false');
    syncTriggerState(targetId, false);

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
          target.setAttribute('data-expanded', 'true');
          syncTriggerState(targetId, true);
        }
      }
    }
  }

  function syncTooltipState(trigger, expanded) {
    const tooltip = trigger.nextElementSibling;
    if (!(tooltip instanceof HTMLElement) || !tooltip.classList.contains('explanation-tooltip__content')) {
      return;
    }

    tooltip.setAttribute('aria-hidden', expanded ? 'false' : 'true');
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
      toggleSection(targetId);
      trigger.removeAttribute('data-first-view');
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

  document.addEventListener('mouseover', function (e) {
    const trigger = e.target.closest('.explanation-tooltip__trigger');
    if (trigger instanceof HTMLElement) {
      syncTooltipState(trigger, true);
    }
  });

  document.addEventListener('mouseout', function (e) {
    const trigger = e.target.closest('.explanation-tooltip__trigger');
    if (trigger instanceof HTMLElement) {
      syncTooltipState(trigger, false);
    }
  });

  document.addEventListener('focusin', function (e) {
    const trigger = e.target.closest('.explanation-tooltip__trigger');
    if (trigger instanceof HTMLElement) {
      syncTooltipState(trigger, true);
    }
  });

  document.addEventListener('focusout', function (e) {
    const trigger = e.target.closest('.explanation-tooltip__trigger');
    if (trigger instanceof HTMLElement) {
      syncTooltipState(trigger, false);
    }
  });

  // Close drawer on Escape key
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      var openDrawer = document.activeElement instanceof Element
        ? document.activeElement.closest('[data-drawer][data-expanded="true"]')
        : null;
      if (!openDrawer) {
        openDrawer = document.querySelector('[data-drawer][data-expanded="true"]');
      }
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
