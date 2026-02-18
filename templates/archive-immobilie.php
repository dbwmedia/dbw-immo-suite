<?php
/**
 * Template Name: Immobilien Archiv
 */

get_header(); ?>

<div id="dbw-immo-suite" class="dbw-immo-archive-container" style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
	<header class="page-header">
		<h1 class="page-title"><?php post_type_archive_title(); ?></h1>
	</header>

	<?php 
	// Filter Bar
	if (class_exists('DBW\ImmoSuite\Frontend\Filter')) {
		\DBW\ImmoSuite\Frontend\Filter::render_filter_bar();
		\DBW\ImmoSuite\Frontend\Filter::render_archive_header();
	}
	?>

	<?php if ( have_posts() ) : ?>
		<div class="dbw-property-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				$price = get_post_meta( get_the_ID(), 'kaufpreis', true );
				if ( ! $price ) {
					$price = get_post_meta( get_the_ID(), 'kaltmiete', true );
					if ( $price ) $price .= ' (Miete)';
				}
				
				$area = get_post_meta( get_the_ID(), 'wohnflaeche', true );
				$rooms = get_post_meta( get_the_ID(), 'anzahl_zimmer', true );
				$location = get_post_meta( get_the_ID(), 'ort', true );
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'dbw-property-card' ); ?>>
					<!-- Image -->
					<a href="<?php the_permalink(); ?>" class="dbw-property-image" style="<?php echo has_post_thumbnail() ? 'background-image: url(' . get_the_post_thumbnail_url( get_the_ID(), 'medium-large' ) . ');' : ''; ?>">
						<?php 
						// Status Tag (Rent/Buy)
						if (class_exists('DBW\ImmoSuite\Frontend\Filter')) {
							$tag_data = \DBW\ImmoSuite\Frontend\Filter::get_status_label(get_the_ID());
							if ($tag_data) {
								echo '<span class="dbw-property-tag ' . esc_attr($tag_data['class']) . '">' . esc_html($tag_data['label']) . '</span>';
							}
						}
						?>
					</a>

					<div class="dbw-property-content">
						<div class="dbw-card-body">
							<h2 class="dbw-property-title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h2>
							
							<?php if ( $location ) : ?>
							<div class="dbw-property-address">
								<span class="dashicons dashicons-location"></span> <?php echo esc_html( $location ); ?>
							</div>
							<?php endif; ?>

							<!-- Meta Grid (Uniform 4 Items) -->
							<div class="dbw-card-meta-grid">
								<!-- 1. Wohnfläche -->
								<?php if ( $area && get_theme_mod( 'dbw_immo_archive_show_area', true ) ) : ?>
								<div class="dbw-meta-item">
									<div class="dbw-meta-icon">
										<!-- Icon: Area/Home -->
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
											<path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
										</svg>
									</div>
									<div class="dbw-meta-data">
										<span class="dbw-meta-value"><?php echo esc_html( $area ); ?> m²</span>
										<span class="dbw-meta-label">Wohnfläche</span>
									</div>
								</div>
								<?php endif; ?>

								<!-- 2. Zimmer -->
								<?php if ( $rooms && get_theme_mod( 'dbw_immo_archive_show_rooms', true ) ) : ?>
								<div class="dbw-meta-item">
									<div class="dbw-meta-icon">
										<!-- Icon: Rooms/Grid -->
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
											<path d="M3 3h8v8H3zm10 0h8v8h-8zM3 13h8v8H3zm10 0h8v8h-8z"/>
										</svg>
									</div>
									<div class="dbw-meta-data">
										<span class="dbw-meta-value"><?php echo esc_html( $rooms ); ?></span>
										<span class="dbw-meta-label">Zimmer</span>
									</div>
								</div>
								<?php endif; ?>

								<!-- 3. Schlafzimmer -->
								<?php 
								$bedrooms = get_post_meta( get_the_ID(), 'anzahl_schlafzimmer', true );
								if ( $bedrooms ) : ?>
								<div class="dbw-meta-item">
									<div class="dbw-meta-icon">
										<!-- Icon: Bed -->
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
											<path d="M20 10V7c0-1.1-.9-2-2-2H6c-1.1 0-2 .9-2 2v3c-1.1 0-2 .9-2 2v5h1.33L4 19h1v-1h14v1h1l.67-2H22v-5c0-1.1-.9-2-2-2zm-9-3h-4v2h4V7zm5 0h-4v2h4V7z"/>
										</svg>
									</div>
									<div class="dbw-meta-data">
										<span class="dbw-meta-value"><?php echo esc_html( $bedrooms ); ?></span>
										<span class="dbw-meta-label">Schlafzimmer</span>
									</div>
								</div>
								<?php endif; ?>

								<!-- 4. Baujahr -->
								<?php 
								$year = get_post_meta( get_the_ID(), 'energiepass_baujahr', true );
								if ( $year && get_theme_mod( 'dbw_immo_archive_show_year', true ) ) : ?>
								<div class="dbw-meta-item">
									<div class="dbw-meta-icon">
										<!-- Icon: Calendar -->
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
											<path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/>
										</svg>
									</div>
									<div class="dbw-meta-data">
										<span class="dbw-meta-value"><?php echo esc_html( $year ); ?></span>
										<span class="dbw-meta-label">Baujahr</span>
									</div>
								</div>
								<?php endif; ?>
							</div>
						</div>

						<!-- Card Footer (Price & Action) -->
						<div class="dbw-card-footer">
							<?php if ( $price && get_theme_mod( 'dbw_immo_archive_show_price', true ) ) : ?>
							<div class="dbw-property-price">
								<span class="dbw-price-label">Kaufpreis</span>
								<span class="dbw-price-value"><?php echo esc_html( number_format_i18n( (float)$price, 0 ) ); ?> €</span>
							</div>
							<?php endif; ?>

							<a href="<?php the_permalink(); ?>" class="dbw-button-expose"><?php echo esc_html( get_theme_mod( 'dbw_immo_expose_btn_text', 'Zum Exposé' ) ); ?></a>
						</div>
					</div>
				</article>
			<?php endwhile; ?>
		</div>

		<?php
		if (class_exists('DBW\ImmoSuite\Frontend\Filter')) {
			\DBW\ImmoSuite\Frontend\Filter::pagination();
		} else {
			the_posts_pagination( array(
				'prev_text' => '<span class="screen-reader-text">' . __( 'Vorherige Seite', 'dbw-immo-suite' ) . '</span>',
				'next_text' => '<span class="screen-reader-text">' . __( 'Nächste Seite', 'dbw-immo-suite' ) . '</span>',
			) );
		}

	else :
		echo '<p>' . __( 'Keine Immobilien gefunden.', 'dbw-immo-suite' ) . '</p>';
	endif;
	?>
</div>

<?php
get_footer();
