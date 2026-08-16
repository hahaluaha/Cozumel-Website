(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var cards = document.querySelectorAll('.host-card');
        if (!cards.length) return;

        var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (prefersReducedMotion || !('IntersectionObserver' in window)) {
            cards.forEach(function (card) { card.classList.add('is-visible'); });
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.25 });

        cards.forEach(function (card) { observer.observe(card); });
    });
})();
