/**
 * Code Tab Switching
 *
 * Instant tab switching for code blocks. No page reload.
 * Also handles copy-to-clipboard functionality.
 */
(function () {
  'use strict';

  function mount(block) {
    if (!(block instanceof Element)) {
      return;
    }

    if (block.hasAttribute('data-code-block-mounted')) {
      return;
    }

    block.setAttribute('data-code-block-mounted', 'true');

    block.addEventListener('click', function (e) {
      var tab = e.target.closest('[data-code-tab]');
      if (tab && block.contains(tab)) {
        activateTab(block, tab);
        return;
      }

      var copyBtn = e.target.closest('[data-copy-source]');
      if (copyBtn && block.contains(copyBtn)) {
        copySource(copyBtn);
      }
    });
  }

  function activateTab(block, tab) {
    if (!block || !tab) return;

    block.querySelectorAll('.code-block__tab').forEach(function (t) {
      t.classList.remove('code-block__tab--active');
      t.setAttribute('aria-selected', 'false');
    });
    block.querySelectorAll('.code-block__panel').forEach(function (p) {
      p.classList.remove('code-block__panel--active');
      p.setAttribute('hidden', '');
    });

    // Activate selected tab and panel
    tab.classList.add('code-block__tab--active');
    tab.setAttribute('aria-selected', 'true');

    var panelId = tab.getAttribute('aria-controls');
    var panel = document.getElementById(panelId);
    if (panel) {
      panel.classList.add('code-block__panel--active');
      panel.removeAttribute('hidden');
    }
  }

  function copySource(copyBtn) {
    var rawSourceId = copyBtn.getAttribute('data-copy-raw-source');
    var sourceId = rawSourceId || copyBtn.getAttribute('data-copy-source');
    var sourceEl = document.getElementById(sourceId);
    if (!sourceEl) return;

    var text = ('value' in sourceEl ? sourceEl.value : sourceEl.textContent) || '';

    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(function () {
        showCopyFeedback(copyBtn, 'Copied!');
      }).catch(function () {
        showCopyFeedback(copyBtn, 'Failed');
      });
    } else {
      // Fallback for older browsers
      var textarea = document.createElement('textarea');
      textarea.value = text;
      textarea.style.position = 'fixed';
      textarea.style.opacity = '0';
      document.body.appendChild(textarea);
      textarea.select();
      try {
        document.execCommand('copy');
        showCopyFeedback(copyBtn, 'Copied!');
      } catch (err) {
        showCopyFeedback(copyBtn, 'Failed');
      }
      document.body.removeChild(textarea);
    }
  }

  function showCopyFeedback(btn, message) {
    var original = btn.textContent;
    btn.textContent = message;
    setTimeout(function () {
      btn.textContent = original;
    }, 1500);
  }

  function mountAll() {
    document.querySelectorAll('[data-code-block]').forEach(mount);
  }

  if (window.SemitexaComponent && typeof window.SemitexaComponent.register === 'function') {
    window.SemitexaComponent.register('demo-code-block', mount);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mountAll, { once: true });
  } else {
    mountAll();
  }
})();
