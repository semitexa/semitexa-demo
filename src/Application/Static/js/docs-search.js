/**
 * Keyboard-first documentation search over the server-rendered catalog index.
 *
 * Mounts per component root, like every other widget here: the trigger and the
 * dialog that belong together live inside one `[data-docs-search]` element, so
 * a second instance on a page cannot steal the first one's opener.
 */
(function () {
  'use strict';

  var roots = [];

  function mount(root) {
    if (!(root instanceof Element) || root.hasAttribute('data-docs-search-mounted')) {
      return;
    }

    var dialog = root.querySelector('[data-docs-search-dialog]');
    var trigger = root.querySelector('[data-docs-search-open]');
    var input = root.querySelector('[data-docs-search-input]');
    var results = root.querySelector('[data-docs-search-results]');
    var status = root.querySelector('[data-docs-search-status]');
    var empty = root.querySelector('[data-docs-search-empty]');
    var close = root.querySelector('[data-docs-search-close]');

    // All or nothing: a half-wired dialog is worse than an inert trigger.
    if (!dialog || !trigger || !input || !results || !status || !empty || !close) return;

    root.setAttribute('data-docs-search-mounted', 'true');

    var items = Array.from(root.querySelectorAll('[data-docs-search-item]'));
    var opener = null;
    // One flag for all three paths. A browser that has showModal but not
    // close (or the reverse) must not take the modal path half-way.
    var supportsModal = typeof dialog.showModal === 'function' && typeof dialog.close === 'function';

    function normalize(value) {
      return (value || '').toLocaleLowerCase().normalize('NFKD').replace(/[̀-ͯ]/g, '').trim();
    }

    function visibleLinks() {
      return items.filter(function (item) { return !item.hidden; }).map(function (item) {
        return item.querySelector('a');
      }).filter(Boolean);
    }

    function filter() {
      var query = normalize(input.value);
      var words = query.split(/\s+/).filter(Boolean);
      var matches = [];

      items.forEach(function (item, position) {
        var haystack = normalize(item.getAttribute('data-search-text'));
        var title = normalize(item.getAttribute('data-search-title'));
        var matchesAll = words.length > 0 && words.every(function (word) { return haystack.indexOf(word) !== -1; });
        var score = title === query ? 1000 : (title.indexOf(query) === 0 ? 500 : (title.indexOf(query) !== -1 ? 250 : 0));

        item.hidden = true;
        if (matchesAll) matches.push({ item: item, score: score, position: position });
      });

      matches.sort(function (left, right) {
        return right.score - left.score || left.position - right.position;
      });
      matches.slice(0, 12).forEach(function (match) {
        match.item.hidden = false;
        results.appendChild(match.item);
      });

      empty.hidden = query === '' || matches.length > 0;
      status.textContent = query === ''
        ? 'Type to search every documentation page.'
        : matches.length + (matches.length === 1 ? ' result' : ' results') + (matches.length > 12 ? '; showing the first 12.' : '.');
    }

    function openSearch(from) {
      // Opened by shortcut, the active element is often <body>, which cannot
      // take focus back on close. Fall back to the trigger so Escape always
      // returns the keyboard somewhere it can carry on from.
      opener = from instanceof HTMLElement && from !== document.body ? from : trigger;
      if (supportsModal) dialog.showModal();
      else dialog.setAttribute('open', '');
      input.value = '';
      filter();
      window.requestAnimationFrame(function () { input.focus(); });
    }

    function restoreFocus() {
      if (opener && typeof opener.focus === 'function') opener.focus();
    }

    function closeSearch() {
      if (supportsModal) {
        dialog.close();
        return;
      }

      // No showModal/close support: removing the attribute fires no 'close'
      // event, so focus would stay inside a hidden dialog unless we move it.
      dialog.removeAttribute('open');
      restoreFocus();
    }

    trigger.addEventListener('click', function () { openSearch(trigger); });
    close.addEventListener('click', closeSearch);
    input.addEventListener('input', filter);

    input.addEventListener('keydown', function (event) {
      var links = visibleLinks();
      if ((event.key === 'ArrowDown' || event.key === 'ArrowUp') && links.length > 0) {
        event.preventDefault();
        (event.key === 'ArrowDown' ? links[0] : links[links.length - 1]).focus();
      } else if (event.key === 'Enter' && links.length > 0) {
        event.preventDefault();
        links[0].click();
      }
    });

    results.addEventListener('keydown', function (event) {
      if (event.key !== 'ArrowDown' && event.key !== 'ArrowUp') return;
      var links = visibleLinks();
      var current = links.indexOf(document.activeElement);
      if (current === -1) return;
      event.preventDefault();
      var next = event.key === 'ArrowDown' ? Math.min(current + 1, links.length - 1) : current - 1;
      if (next < 0) input.focus();
      else links[next].focus();
    });

    dialog.addEventListener('click', function (event) {
      if (event.target === dialog) closeSearch();
    });
    dialog.addEventListener('close', restoreFocus);

    // Without showModal the dialog is not a real modal, so the browser does
    // not dismiss it on Escape. Bound on the document because in that mode
    // focus is not confined to the dialog either.
    if (!supportsModal) {
      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && dialog.hasAttribute('open')) {
          event.preventDefault();
          closeSearch();
        }
      });
    }

    roots.push({ open: openSearch });
  }

  document.querySelectorAll('[data-docs-search]').forEach(mount);

  if (roots.length === 0) return;

  // The page-level shortcut belongs to one instance; the first mounted wins.
  document.addEventListener('keydown', function (event) {
    var target = event.target;
    var isTyping = target instanceof HTMLElement && (target.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(target.tagName));
    var commandK = (event.ctrlKey || event.metaKey) && (event.key || '').toLowerCase() === 'k';
    var slash = event.key === '/' && !isTyping && !event.ctrlKey && !event.metaKey && !event.altKey;
    if (!commandK && !slash) return;
    event.preventDefault();
    roots[0].open(document.activeElement);
  });
})();
