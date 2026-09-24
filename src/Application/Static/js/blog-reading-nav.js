(function () {
    'use strict';

    function init() {
        const nav = document.querySelector('[data-blog-reading-nav]');
        const article = document.querySelector('.blog-article');
        if (!nav || !article) return;

        const sections = Array.from(nav.querySelectorAll('a[href^="#"]'))
            .map(function (link) {
                const section = document.getElementById(decodeURIComponent(link.hash.slice(1)));
                return { link: link, section: section };
            })
            .filter(function (entry) { return entry.section && article.contains(entry.section); });
        if (!sections.length) return;

        let current = null;
        let pending = false;

        function update() {
            pending = false;
            // Match native anchor positioning beneath the sticky page header.
            const offset = parseFloat(getComputedStyle(sections[0].section).scrollMarginTop) || 0;
            let active = sections[0];
            sections.forEach(function (entry) {
                if (entry.section.getBoundingClientRect().top <= offset + 1) active = entry;
            });

            // A short final section may never reach the top reading position.
            if (window.scrollY > 0 && Math.ceil(window.scrollY + window.innerHeight) >= document.documentElement.scrollHeight - 2) {
                active = sections[sections.length - 1];
            }
            if (active === current) return;
            if (current) current.link.removeAttribute('aria-current');
            active.link.setAttribute('aria-current', 'location');
            current = active;
        }

        function schedule() {
            if (pending) return;
            pending = true;
            requestAnimationFrame(update);
        }

        window.addEventListener('scroll', schedule, { passive: true });
        window.addEventListener('resize', schedule);
        window.addEventListener('hashchange', schedule);
        window.addEventListener('pageshow', schedule);
        window.addEventListener('load', schedule);
        document.addEventListener('semitexa:block:rendered', schedule);
        if (typeof ResizeObserver !== 'undefined') {
            new ResizeObserver(schedule).observe(article);
        }
        update();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
}());
