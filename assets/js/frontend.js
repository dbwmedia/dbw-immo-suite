jQuery(document).ready(function ($) {

    // Filter Toggle Logic
    const $toggleBtn = $('#dbw-filter-toggle');
    const $content = $('.dbw-filter-content');
    const $container = $('#dbw-filter-container');
    const $arrow = $('.dbw-arrow');

    // On Load: Check if we have active search parameters. If so, keep expanded.
    // The PHP already sets 'is-expanded' class, but we can verify in JS if needed.
    if ($container.hasClass('is-expanded')) {
        $content.show();
        $arrow.addClass('is-open'); // Rotate arrow if needed
    } else {
        $content.hide();
    }

    $toggleBtn.on('click', function (e) {
        e.preventDefault();
        // Slide Toggle
        $content.slideToggle(300, function () {
            $container.toggleClass('is-expanded');
        });
        $arrow.toggleClass('is-open');
    });

});
