document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('dbw-immo-suite');
    if (!container) return;

    // View Switcher Logic (grid / list / map)
    const gridBtn = document.getElementById('dbw-view-grid');
    const listBtn = document.getElementById('dbw-view-list');
    const mapBtn = document.getElementById('dbw-view-map');
    const propertyGrid = container.querySelector('.dbw-property-grid');
    const mapWrapper = document.getElementById('dbw-archive-map-wrapper');

    if (gridBtn && listBtn && propertyGrid) {

        const buttons = [
            { btn: gridBtn, view: 'grid' },
            { btn: listBtn, view: 'list' },
        ];
        if (mapBtn && mapWrapper) {
            buttons.push({ btn: mapBtn, view: 'map' });
        }

        // Function to set view; persist=false for programmatic switches
        // (initial restore, leave-map-view) so user preference survives
        const setView = (view, persist) => {
            // Fall back to grid if the map view is unavailable on this page
            if (view === 'map' && (!mapBtn || !mapWrapper)) {
                view = 'grid';
            }

            buttons.forEach(function (entry) {
                const active = entry.view === view;
                entry.btn.classList.toggle('active', active);
                entry.btn.setAttribute('aria-pressed', active ? 'true' : 'false');
            });

            propertyGrid.classList.remove('is-grid-view', 'is-list-view');
            const isMap = view === 'map';

            propertyGrid.hidden = isMap;
            if (mapWrapper) mapWrapper.hidden = !isMap;

            // Pagination stays hidden in map view AND while the favorites view is active
            const favToggle = document.getElementById('dbw-fav-toggle');
            const favActive = favToggle && favToggle.classList.contains('is-active');
            document.querySelectorAll('.dbw-pagination').forEach(function (el) {
                el.hidden = isMap || favActive;
            });

            if (isMap) {
                document.dispatchEvent(new CustomEvent('dbw:enter-map-view'));
            } else {
                propertyGrid.classList.add(view === 'list' ? 'is-list-view' : 'is-grid-view');
            }

            if (persist) {
                try { localStorage.setItem('dbw_immo_view', view); } catch(e) {}
            }
        };

        // Load saved state (no persist — a fallback must not overwrite the preference)
        let savedView = 'grid';
        try { savedView = localStorage.getItem('dbw_immo_view') || 'grid'; } catch(e) {}
        setView(savedView, false);

        // Animated switch via View Transition API (with fallback)
        const switchView = (view) => {
            if (document.startViewTransition) {
                const t = document.startViewTransition(() => setView(view, true));
                t.finished.catch(() => {});
            } else {
                setView(view, true);
            }
        };

        // Click Handlers
        buttons.forEach(function (entry) {
            entry.btn.addEventListener('click', () => switchView(entry.view));
        });

        // Favorites view needs the grid visible — leave map view on request
        document.addEventListener('dbw:leave-map-view', function () {
            if (mapWrapper && !mapWrapper.hidden) {
                setView('grid', false);
            }
        });
    }
});
