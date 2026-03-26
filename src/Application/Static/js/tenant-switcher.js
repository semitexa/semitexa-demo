/**
 * tenant-switcher.js
 * Persists the active tenant in sessionStorage/cookie.
 * Colored dot UI — clicking a dot activates that tenant and reloads the page with ?tenant=...
 */
(function () {
    'use strict';

    var COOKIE_NAME = 'demo_tenant';
    var STORAGE_KEY = 'semitexa_demo_tenant';

    function getActiveTenant() {
        var stored = sessionStorage.getItem(STORAGE_KEY);
        if (stored) return stored;

        var cookieValue = document.cookie.replace(new RegExp('(?:^|.*;)\\s*' + COOKIE_NAME + '\\s*=\\s*([^;]*).*$|^.*$'), '$1');
        if (!cookieValue) return 'acme';

        try {
            return decodeURIComponent(cookieValue);
        } catch (e) {
            return cookieValue;
        }
    }

    function setActiveTenant(tenantId) {
        sessionStorage.setItem(STORAGE_KEY, tenantId);
        document.cookie = COOKIE_NAME + '=' + encodeURIComponent(tenantId) + '; path=/; SameSite=Lax';
    }

    function updateUI(tenantId) {
        document.querySelectorAll('[data-tenant-dot]').forEach(function (btn) {
            btn.classList.toggle('tenant-dot--active', btn.getAttribute('data-tenant-id') === tenantId);
        });
        document.querySelectorAll('[data-active-tenant-label]').forEach(function (el) {
            el.textContent = tenantId;
        });
        document.querySelectorAll('[data-tenant-label]').forEach(function (el) {
            el.textContent = tenantId;
        });
    }

    function init() {
        var switchers = document.querySelectorAll('[data-tenant-switcher]');
        if (!switchers.length) return;

        var current = getActiveTenant();
        updateUI(current);

        switchers.forEach(function (switcher) {
            switcher.querySelectorAll('[data-tenant-id]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var id = btn.getAttribute('data-tenant-id');
                    if (!id || id === getActiveTenant()) return;

                    setActiveTenant(id);
                    updateUI(id);

                    // Reload page with tenant as query param so server can render correct data
                    var url = new URL(window.location.href);
                    url.searchParams.set('tenant', id);
                    window.location.href = url.toString();
                });
            });
        });
    }

    document.addEventListener('DOMContentLoaded', init);
}());
