document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('dbw-immo-suite');
    if (!container) return;

    // View Switcher Logic
    const gridBtn = document.getElementById('dbw-view-grid');
    const listBtn = document.getElementById('dbw-view-list');
    const propertyGrid = container.querySelector('.dbw-property-grid');

    if (gridBtn && listBtn && propertyGrid) {

        // Function to set view
        const setView = (view) => {
            // Remove active classes from buttons
            gridBtn.classList.remove('active');
            listBtn.classList.remove('active');

            // Remove view classes from grid
            propertyGrid.classList.remove('is-grid-view', 'is-list-view');

            if (view === 'list') {
                listBtn.classList.add('active');
                propertyGrid.classList.add('is-list-view');
                listBtn.setAttribute('aria-pressed', 'true');
                gridBtn.setAttribute('aria-pressed', 'false');
            } else {
                gridBtn.classList.add('active');
                propertyGrid.classList.add('is-grid-view');
                gridBtn.setAttribute('aria-pressed', 'true');
                listBtn.setAttribute('aria-pressed', 'false');
            }

            // Save to localStorage
            try { localStorage.setItem('dbw_immo_view', view); } catch(e) {}
        };

        // Load saved state
        let savedView = 'grid';
        try { savedView = localStorage.getItem('dbw_immo_view') || 'grid'; } catch(e) {}
        setView(savedView);

        // Animated switch via View Transition API (with fallback)
        const switchView = (view) => {
            if (document.startViewTransition) {
                const t = document.startViewTransition(() => setView(view));
                t.finished.catch(() => {});
            } else {
                setView(view);
            }
        };

        // Click Handlers
        gridBtn.addEventListener('click', () => switchView('grid'));
        listBtn.addEventListener('click', () => switchView('list'));
    }
});
