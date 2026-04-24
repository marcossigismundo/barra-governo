/**
 * Barra do Governo Federal — Frontend JS
 *
 * Handles:
 *  - Mobile quick-links toggle
 *  - Dynamic body offset when the bar is fixed (so no theme content is hidden)
 *  - Admin bar compatibility when logged in
 */
(function () {
    'use strict';

    function closeMenu(toggleBtn, linksList) {
        toggleBtn.setAttribute('aria-expanded', 'false');
        linksList.classList.remove('bg-open');
    }

    function wireMobileMenu(bar) {
        var toggleBtn = bar.querySelector('.bg-toggle-links');
        var linksList = bar.querySelector('.bg-links-list');
        if (!toggleBtn || !linksList) return;

        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var expanded = toggleBtn.getAttribute('aria-expanded') === 'true';
            toggleBtn.setAttribute('aria-expanded', String(!expanded));
            linksList.classList.toggle('bg-open');
        });

        document.addEventListener('click', function (e) {
            if (!toggleBtn.contains(e.target) && !linksList.contains(e.target)) {
                closeMenu(toggleBtn, linksList);
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeMenu(toggleBtn, linksList);
        });
    }

    function wireFixedOffset(bar) {
        var position = bar.getAttribute('data-bg-position') || 'fixed';
        if (position !== 'fixed') return;

        var html = document.documentElement;
        html.classList.add('bg-has-fixed-bar');

        var applyHeight = function () {
            var h = bar.getBoundingClientRect().height;
            if (h > 0) {
                html.style.setProperty('--bg-bar-height', h + 'px');
            }
        };

        applyHeight();

        if (window.ResizeObserver) {
            var ro = new ResizeObserver(applyHeight);
            ro.observe(bar);
        } else {
            window.addEventListener('resize', applyHeight);
        }

        window.addEventListener('load', applyHeight);
    }

    function init() {
        var bar = document.getElementById('barra-governo');
        if (!bar) return;
        wireMobileMenu(bar);
        wireFixedOffset(bar);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
