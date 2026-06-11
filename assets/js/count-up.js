(function () {
    'use strict';

    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    if (!('IntersectionObserver' in window)) return;

    document.addEventListener('DOMContentLoaded', function () {
        var targets = document.querySelectorAll(
            '#dbw-immo-suite.dbw-single-property-container .dbw-feature-item .dbw-meta-value'
        );
        if (!targets.length) return;

        function animate(el) {
            var text = el.textContent.trim();
            var match = text.match(/^([\d.,]+)/);
            if (!match) return;

            var numStr = match[1];
            var suffix = text.slice(numStr.length);
            var hasDecimal = numStr.indexOf(',') !== -1;
            var value = parseFloat(numStr.replace(/\./g, '').replace(',', '.'));
            if (!isFinite(value) || value <= 0) return;

            var decimals = hasDecimal ? 1 : 0;
            var start = null;
            var duration = 900;

            function frame(now) {
                if (start === null) start = now;
                var t = Math.min(1, (now - start) / duration);
                var eased = 1 - Math.pow(1 - t, 3);
                var current = value * eased;
                el.textContent = current.toLocaleString('de-DE', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                }) + suffix;
                if (t < 1) requestAnimationFrame(frame);
            }
            requestAnimationFrame(frame);
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    observer.unobserve(entry.target);
                    animate(entry.target);
                }
            });
        }, { threshold: 0.6 });

        targets.forEach(function (el) { observer.observe(el); });
    });
})();
