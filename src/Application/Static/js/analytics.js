/**
 * Google Analytics for framework.semitexa.com
 *
 * Same GA4 tag as semitexa.com (G-47KYEC7JNJ), so one report follows a reader
 * from an article to the install command. Only production hosts report.
 *
 * gtag.js is ~175 KB that paints nothing, so it loads on the visitor's first
 * interaction or once the page has sat idle a few seconds after load; calls
 * made before that wait in dataLayer and are replayed when it arrives.
 *
 * Events:
 *  - install_command_copy  a copied command that runs install.sh
 *  - code_copy             any other copied code or command
 *  - cta_click             a click on a link that leaves this host
 * Copies are reported from the `semitexa:code-copied` event code-tabs.js
 * dispatches, so the copy handler itself knows nothing about analytics.
 */
(function () {
  'use strict';

  if (!/(^|\.)semitexa\.com$/.test(location.hostname)) {
    return;
  }

  var MEASUREMENT_ID = 'G-47KYEC7JNJ';

  window.dataLayer = window.dataLayer || [];
  function gtag() { window.dataLayer.push(arguments); }
  window.gtag = window.gtag || gtag;
  gtag('js', new Date());
  gtag('config', MEASUREMENT_ID);

  var started = false;
  var triggers = ['pointerdown', 'keydown', 'scroll', 'touchstart'];

  function start() {
    if (started) return;
    started = true;
    triggers.forEach(function (name) { window.removeEventListener(name, start, true); });
    var s = document.createElement('script');
    s.async = true;
    s.src = 'https://www.googletagmanager.com/gtag/js?id=' + MEASUREMENT_ID;
    document.head.appendChild(s);
  }

  triggers.forEach(function (name) {
    window.addEventListener(name, start, { capture: true, passive: true, once: true });
  });

  function startWhenIdle() {
    setTimeout(function () {
      if ('requestIdleCallback' in window) {
        requestIdleCallback(start, { timeout: 2000 });
      } else {
        start();
      }
    }, 4000);
  }

  if (document.readyState === 'complete') {
    startWhenIdle();
  } else {
    window.addEventListener('load', startWhenIdle, { once: true });
  }

  document.addEventListener('semitexa:code-copied', function (event) {
    var text = (event.detail && event.detail.text) || '';
    var isInstall = /install\.sh/.test(text);
    gtag('event', isInstall ? 'install_command_copy' : 'code_copy', {
      command: text.replace(/\s+/g, ' ').trim().slice(0, 100)
    });
  });

  function ctaTarget(host) {
    if (host === 'github.com') return 'github';
    if (/(^|\.)paypal\.com$/.test(host)) return 'donate';
    var sub = host.match(/^([a-z0-9-]+)\.semitexa\.com$/);
    if (sub) return sub[1];
    return host === 'semitexa.com' ? 'site' : host;
  }

  document.addEventListener('click', function (event) {
    var link = event.target.closest ? event.target.closest('a[href]') : null;
    if (!link || link.hostname === location.hostname || !/^https?:$/.test(link.protocol)) return;
    var area = link.closest('header, footer') || link.closest('[id], main');
    gtag('event', 'cta_click', {
      cta_target: ctaTarget(link.hostname),
      cta_location: area ? (area.id || area.tagName.toLowerCase()) : 'page',
      link_url: link.href,
      link_text: (link.textContent || '').replace(/\s+/g, ' ').trim().slice(0, 100),
      transport_type: 'beacon'
    });
  }, { capture: true, passive: true });
})();
