document.addEventListener('DOMContentLoaded', function () {
    // Filter Toggle Logic
    var toggleBtn = document.getElementById('dbw-filter-toggle');
    var container = document.getElementById('dbw-filter-container');

    if (!toggleBtn || !container) return;

    var content = container.querySelector('.dbw-filter-content');
    if (!content) return;

    // On Load: Show content if expanded class is set (PHP sets this when filters are active)
    if (container.classList.contains('is-expanded')) {
        content.style.display = 'block';
    } else {
        content.style.display = 'none';
    }

    toggleBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (content.style.display === 'none' || content.style.display === '') {
            content.style.display = 'block';
            container.classList.add('is-expanded');
        } else {
            content.style.display = 'none';
            container.classList.remove('is-expanded');
        }
    });
});
