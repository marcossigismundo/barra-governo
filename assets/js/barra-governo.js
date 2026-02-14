/**
 * Barra do Governo Federal — JavaScript
 * Mobile menu toggle.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var bar = document.getElementById('barra-governo');
        if (!bar) return;

        var toggleBtn = bar.querySelector('.bg-toggle-links');
        var linksList = bar.querySelector('.bg-links-list');

        if (toggleBtn && linksList) {
            toggleBtn.addEventListener('click', function () {
                var expanded = toggleBtn.getAttribute('aria-expanded') === 'true';
                toggleBtn.setAttribute('aria-expanded', String(!expanded));
                linksList.classList.toggle('bg-open');
            });

            document.addEventListener('click', function (e) {
                if (!toggleBtn.contains(e.target) && !linksList.contains(e.target)) {
                    toggleBtn.setAttribute('aria-expanded', 'false');
                    linksList.classList.remove('bg-open');
                }
            });
        }
    });
})();
