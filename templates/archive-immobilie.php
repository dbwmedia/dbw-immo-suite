<?php
/**
 * Template Name: Immobilien Archiv
 */

get_header(); ?>

<div id="dbw-immo-suite" class="dbw-immo-archive-container"
	style="max-width: 1200px; min-width: 100%; margin: 0 auto;">
	<header class="page-header">
		<h1 class="page-title" style="margin-bottom: 1rem;"><?php post_type_archive_title(); ?></h1>
	</header>

	<?php
	if (class_exists('DBW\ImmoSuite\Frontend\Filter')) {
		\DBW\ImmoSuite\Frontend\Filter::render_filter_bar();
		\DBW\ImmoSuite\Frontend\Filter::render_archive_header();
	}
	?>

	<?php if (have_posts()): ?>
		<div class="dbw-property-grid">
			<?php
			while (have_posts()):
				the_post();
				\DBW\ImmoSuite\Frontend\CardRenderer::render();
			endwhile;
			?>
		</div>

		<?php
		if (class_exists('DBW\ImmoSuite\Frontend\Filter')) {
			\DBW\ImmoSuite\Frontend\Filter::pagination();
		} else {
			the_posts_pagination();
		}
		?>

	<?php else: ?>
		<div class="dbw-property-grid">
			<p class="dbw-no-results"><?php _e('Keine Immobilien gefunden.', 'dbw-immo-suite'); ?></p>
		</div>
	<?php endif; ?>

	<?php
	// Map view container — rendered outside the have_posts() branch so the
	// view switcher and favorites view keep working on empty result pages
	if (class_exists('DBW\ImmoSuite\Frontend\ArchiveMap')) {
		\DBW\ImmoSuite\Frontend\ArchiveMap::render();
	}
	?>
</div>

<?php
get_footer();
