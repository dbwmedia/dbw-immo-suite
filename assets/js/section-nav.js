(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var suite = document.getElementById('dbw-immo-suite');
        if (!suite || !suite.classList.contains('dbw-single-property-container')) return;

        var detailsGrid = suite.querySelector('.dbw-details-grid');
        if (!detailsGrid) return;

        // Collect sections that have a visible title
        var sections = [];
        suite.querySelectorAll('.dbw-main-col .dbw-section').forEach(function (section, i) {
            var titleEl = section.querySelector('.dbw-section-title');
            var title = titleEl ? titleEl.textContent.trim() : '';
            if (!title) return;
            if (!section.id) section.id = 'dbw-sec-' + i;
            sections.push({ el: section, id: section.id, title: title });
        });

        if (sections.length < 2) return;

        // Build the sticky nav
        var nav = document.createElement('nav');
        nav.className = 'dbw-section-nav';
        nav.setAttribute('aria-label', 'Abschnitte');

        var inner = document.createElement('div');
        inner.className = 'dbw-section-nav__inner';

        var links = [];
        sections.forEach(function (s) {
            var link = document.createElement('a');
            link.href = '#' + s.id;
            link.className = 'dbw-section-nav__link';
            link.textContent = s.title;
            link.addEventListener('click', function (e) {
                e.preventDefault();
                s.el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                try { history.replaceState(null, '', '#' + s.id); } catch (err) {}
            });
            inner.appendChild(link);
            links.push(link);
        });

        var progress = document.createElement('div');
        progress.className = 'dbw-section-nav__progress';
        var progressBar = document.createElement('div');
        progressBar.className = 'dbw-section-nav__progress-bar';
        progress.appendChild(progressBar);

        nav.appendChild(inner);
        nav.appendChild(progress);
        detailsGrid.parentNode.insertBefore(nav, detailsGrid);

        // Scroll spy
        function setActive(idx) {
            links.forEach(function (l, i) {
                l.classList.toggle('is-active', i === idx);
            });
            var active = links[idx];
            if (active && active.scrollIntoView) {
                active.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
            }
        }

        var ticking = false;
        function onScroll() {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(function () {
                ticking = false;

                // Active section: the last one whose top passed the nav
                var navBottom = nav.getBoundingClientRect().bottom + 24;
                var activeIdx = 0;
                sections.forEach(function (s, i) {
                    if (s.el.getBoundingClientRect().top <= navBottom) activeIdx = i;
                });
                setActive(activeIdx);

                // Reading progress across the details area
                var rect = detailsGrid.getBoundingClientRect();
                var total = rect.height - window.innerHeight;
                var done = total > 0 ? Math.min(1, Math.max(0, -rect.top / total)) : 1;
                progressBar.style.width = (done * 100) + '%';
            });
        }

        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    });
})();
