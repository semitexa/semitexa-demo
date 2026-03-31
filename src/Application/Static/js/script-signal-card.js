(function () {
  'use strict';

  var assetBootCount = 1;
  var mountCount = 0;

  function mount(root) {
    if (!(root instanceof Element)) {
      return;
    }

    mountCount += 1;

    var clicks = 0;
    var boot = root.querySelector('[data-script-signal-boot]');
    var mountLabel = root.querySelector('[data-script-signal-mount]');
    var clickLabel = root.querySelector('[data-script-signal-clicks]');
    var status = root.querySelector('[data-script-signal-status]');
    var action = root.querySelector('[data-script-signal-action]');

    if (boot) {
      boot.textContent = String(assetBootCount);
    }

    if (mountLabel) {
      mountLabel.textContent = '#' + String(mountCount);
    }

    if (status) {
      status.textContent = 'Mounted via semitexa-demo:js:script-signal-card. This root now owns local enhancement state.';
    }

    root.setAttribute('data-script-signal-mounted', 'true');

    if (!(action instanceof HTMLButtonElement)) {
      return;
    }

    action.addEventListener('click', function () {
      clicks += 1;

      if (clickLabel) {
        clickLabel.textContent = String(clicks);
      }

      root.classList.toggle('script-signal-card--energized');

      if (status) {
        status.textContent = 'Interaction #' + String(clicks) + ' handled locally by the component enhancement asset.';
      }
    });
  }

  if (window.SemitexaComponent && typeof window.SemitexaComponent.register === 'function') {
    window.SemitexaComponent.register('demo-script-signal-card', mount);
    return;
  }

  document.querySelectorAll('[data-script-signal-card]').forEach(mount);
}());
