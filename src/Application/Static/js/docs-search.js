/**
 * Keyboard-first documentation search over the server-rendered catalog index.
 */
(function () {
  'use strict';

  var dialog = document.querySelector('[data-docs-search-dialog]');
  if (!dialog) return;

  var input = dialog.querySelector('[data-docs-search-input]');
  var results = dialog.querySelector('[data-docs-search-results]');
  var items = Array.from(dialog.querySelectorAll('[data-docs-search-item]'));
  var status = dialog.querySelector('[data-docs-search-status]');
  var empty = dialog.querySelector('[data-docs-search-empty]');
  var opener = null;

  function normalize(value) {
    return (value || '').toLocaleLowerCase().normalize('NFKD').replace(/[\u0300-\u036f]/g, '').trim();
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

  function openSearch(trigger) {
    opener = trigger || document.activeElement;
    if (typeof dialog.showModal === 'function') dialog.showModal();
    else dialog.setAttribute('open', '');
    input.value = '';
    filter();
    window.requestAnimationFrame(function () { input.focus(); });
  }

  function closeSearch() {
    if (typeof dialog.close === 'function') dialog.close();
    else dialog.removeAttribute('open');
  }

  document.querySelectorAll('[data-docs-search-open]').forEach(function (button) {
    button.addEventListener('click', function () { openSearch(button); });
  });
  dialog.querySelector('[data-docs-search-close]').addEventListener('click', closeSearch);
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

  document.addEventListener('keydown', function (event) {
    var target = event.target;
    var isTyping = target instanceof HTMLElement && (target.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(target.tagName));
    var commandK = (event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k';
    var slash = event.key === '/' && !isTyping && !event.ctrlKey && !event.metaKey && !event.altKey;
    if (!commandK && !slash) return;
    event.preventDefault();
    openSearch(document.activeElement);
  });

  dialog.addEventListener('click', function (event) {
    if (event.target === dialog) closeSearch();
  });
  dialog.addEventListener('close', function () {
    if (opener && typeof opener.focus === 'function') opener.focus();
  });
})();
