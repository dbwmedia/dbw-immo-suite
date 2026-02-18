<?php
/**
 * Template Name: Single Immobilie
 */

get_header(); ?>

<div id="dbw-immo-suite" class="dbw-single-property-container">

	<?php
while (have_posts()):
	the_post();
	$id = get_the_ID();

	// 1. Fetch Data
	// Meta (Pricing, Areas)
	$price_kauf = get_post_meta($id, 'kaufpreis', true);
	$price_miete = get_post_meta($id, 'kaltmiete', true);
	$area = get_post_meta($id, 'wohnflaeche', true);
	$use_area = get_post_meta($id, 'nutzflaeche', true);
	$land_area = get_post_meta($id, 'grundstuecksflaeche', true);
	$rooms = get_post_meta($id, 'anzahl_zimmer', true);
	$parking = get_post_meta($id, 'anzahl_stellplaetze', true);

	// Geo
	$plz = get_post_meta($id, 'plz', true);
	$city = get_post_meta($id, 'ort', true);
	$street = get_post_meta($id, 'strasse', true);
	$house_num = get_post_meta($id, 'hausnummer', true);
	$lat = get_post_meta($id, 'geo_breite', true);
	$lng = get_post_meta($id, 'geo_laenge', true);

	// Texts
	$text_lage = get_post_meta($id, 'text_lage', true);
	$text_ausstattung = get_post_meta($id, 'text_ausstattung', true);
	$text_sonstiges = get_post_meta($id, 'text_sonstiges', true);

	// Energy
	$energy_pass_art = get_post_meta($id, 'energiepass_art', true);
	$energy_end = get_post_meta($id, 'energiepass_endenergie', true);
	$energy_class = get_post_meta($id, 'energiepass_wertklasse', true);
	$energy_source = get_post_meta($id, 'energiepass_traeger', true);
	$energy_valid = get_post_meta($id, 'energiepass_gueltig_bis', true);
	$energy_year = get_post_meta($id, 'energiepass_baujahr', true);

	// Contact Person
	$contact_name = get_post_meta($id, 'kontaktperson_name', true);
	$contact_firstname = get_post_meta($id, 'kontaktperson_vorname', true);
	$contact_email = get_post_meta($id, 'kontaktperson_email', true);
	$contact_tel = get_post_meta($id, 'kontaktperson_tel', true);
	$contact_img_url = get_post_meta($id, 'kontaktperson_bild_url', true);

	// Gallery & Attachments Processing
	$raw_attachments = get_attached_media('image', $id);
	$contact_img_id = get_post_meta($id, 'kontaktperson_bild_id', true);

	$gallery_images = array();
	$floor_plans = array();

	$seen_urls = array();

	foreach ($raw_attachments as $att_id => $att_post) {
		$img_url = wp_get_attachment_image_url($att_id, 'large');

		// 1. Skip if no URL
		if (!$img_url)
			continue;

		// 2. Skip Contact Image (ID check match OR URL match)
		if ($contact_img_id && (int)$att_id === (int)$contact_img_id) {
			continue;
		}
		if ($contact_img_url && $img_url === $contact_img_url) {
			continue;
		}

		// 3. Dedup by URL (prevent same image appearing multiple times due to bad imports)
		if (in_array($img_url, $seen_urls)) {
			continue;
		}
		$seen_urls[] = $img_url;

		$group = get_post_meta($att_id, '_openimmo_gruppe', true);
		$img_url = wp_get_attachment_image_url($att_id, 'large');
		$full_url = wp_get_attachment_image_url($att_id, 'full');
		$alt = get_post_meta($att_id, '_wp_attachment_image_alt', true);

		$item = array(
			'id' => $att_id,
			'url' => $img_url,
			'full' => $full_url,
			'alt' => $alt
		);

		if ($group === 'GRUNDRISS') {
			$floor_plans[] = $item;
		}
		else {
			// TITELBILD or BILD
			// If it's the featured image, put it first or handle main display separately
			$gallery_images[] = $item;
		}
	}

	// Sort gallery: Featured first? Or XML order? XML order is preserved in ID fetch usually or we can rely on menu_order
	// For now, simple array is fine.
	$main_image_item = !empty($gallery_images) ? $gallery_images[0] : null;
?>

	<!-- Header -->
	<div class="dbw-single-header">
		<h1 class="dbw-single-title">
			<?php the_title(); ?>
		</h1>
		<div class="dbw-single-address">
			<span class="dashicons dashicons-location"></span>
			<?php echo esc_html(implode(' ', array_filter(array($street, $house_num))) . ', ' . implode(' ', array_filter(array($plz, $city)))); ?>
		</div>
	</div>

	<!-- Gallery Slider -->
	<?php if (!empty($gallery_images) && get_theme_mod('dbw_immo_single_show_gallery', true)): ?>
	<div class="dbw-gallery-wrapper" style="position: relative; margin-bottom: 2rem;">
		<!-- Main Slider -->
		<div class="dbw-gallery-slider" id="dbwGallerySlider"
			style="display: flex; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); height: 500px; -ms-overflow-style: none; scrollbar-width: none;">
			<style>
				.dbw-gallery-slider::-webkit-scrollbar {
					display: none;
				}
			</style>
			<?php foreach ($gallery_images as $index => $img): ?>
			<div class="dbw-gallery-slide" id="slide-<?php echo $index; ?>"
				style="flex: 0 0 100%; scroll-snap-align: start; position: relative; background-color: #f0f0f0;">
				<img src="<?php echo esc_url($img['full']); ?>" alt="<?php echo esc_attr($img['alt']); ?>"
					style="width: 100%; height: 100%; object-fit: cover; display: block;">
				<?php if ($img['alt']): ?>
				<div
					style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.7)); color: white; padding: 20px; font-size: 0.9rem;">
					<?php echo esc_html($img['alt']); ?>
				</div>
				<?php
			endif; ?>
			</div>
			<?php
		endforeach; ?>
		</div>

		<!-- Navigation Buttons -->
		<button onclick="document.getElementById('dbwGallerySlider').scrollBy({left: -600, behavior: 'smooth'})"
			style="position: absolute; top: 50%; left: 20px; transform: translateY(-50%); background: rgba(255,255,255,0.8); border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 10;">&#10094;</button>
		<button onclick="document.getElementById('dbwGallerySlider').scrollBy({left: 600, behavior: 'smooth'})"
			style="position: absolute; top: 50%; right: 20px; transform: translateY(-50%); background: rgba(255,255,255,0.8); border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 10;">&#10095;</button>

		<!-- Thumbnails Strip -->
		<div class="dbw-gallery-thumbs"
			style="display: flex; gap: 10px; margin-top: 15px; overflow-x: auto; padding-bottom: 5px;">
			<?php foreach ($gallery_images as $index => $img): ?>
			<div onclick="document.getElementById('slide-<?php echo $index; ?>').scrollIntoView({behavior: 'smooth', block: 'nearest'})"
				style="flex: 0 0 80px; height: 60px; cursor: pointer; border-radius: 4px; overflow: hidden; opacity: 0.7; transition: opacity 0.2s;"
				onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
				<img src="<?php echo esc_url($img['url']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
			</div>
			<?php
		endforeach; ?>
		</div>
	</div>
	<?php
	endif; ?>

	<!-- Main Layout Grid -->
	<div class="dbw-details-grid">

		<!-- Left Content Content -->
		<div class="dbw-main-col">

			<!-- Key Facts Row -->
			<div class="dbw-features-list" style="margin-bottom: 2rem;">
				<?php if ($area > 0): ?>
				<div class="dbw-feature-item">
					<span class="dbw-meta-label">Wohnfläche</span><br>
					<span class="dbw-meta-value">
						<?php echo esc_html(number_format_i18n((float)$area, 2)); ?> m²
					</span>
				</div>
				<?php
	endif; ?>

				<?php if ($rooms > 0): ?>
				<div class="dbw-feature-item">
					<span class="dbw-meta-label">Zimmer</span><br>
					<span class="dbw-meta-value">
						<?php echo esc_html($rooms); ?>
					</span>
				</div>
				<?php
	endif; ?>

				<?php if ($land_area > 0): ?>
				<div class="dbw-feature-item">
					<span class="dbw-meta-label">Grundstück</span><br>
					<span class="dbw-meta-value">
						<?php echo esc_html(number_format_i18n((float)$land_area, 2)); ?> m²
					</span>
				</div>
				<?php
	endif; ?>

				<?php if ($use_area > 0): ?>
				<div class="dbw-feature-item">
					<span class="dbw-meta-label">Nutzfläche</span><br>
					<span class="dbw-meta-value">
						<?php echo esc_html(number_format_i18n((float)$use_area, 2)); ?> m²
					</span>
				</div>
				<?php
	endif; ?>
			</div>

			<div class="dbw-section">
				<h3 class="dbw-section-title">Beschreibung</h3>
				<div class="dbw-description">
					<?php the_content(); ?>
				</div>
			</div>

			<?php if ($text_ausstattung || $parking > 0): ?>
			<div class="dbw-section">
				<h3 class="dbw-section-title">Ausstattung</h3>
				<div class="dbw-description">
					<?php if ($parking > 0): ?>
					<p><strong>Stellplätze:</strong>
						<?php echo esc_html($parking); ?>
					</p>
					<?php
		endif; ?>
					<?php echo wpautop(esc_html($text_ausstattung)); ?>
				</div>
			</div>
			<?php
	endif; ?>

			<?php if ($text_lage && get_theme_mod('dbw_immo_single_show_map', true)): ?>
			<div class="dbw-section">
				<h3 class="dbw-section-title">Lage</h3>
				<div class="dbw-description">
					<?php echo wpautop(esc_html($text_lage)); ?>
				</div>

				<!-- Infrastructure Distances -->
				<?php
		// Helper to get all distanz_ meta
		$infra_keys = array('kindergaerten', 'grundschule', 'realschule', 'gymnasium', 'einkaufsmoeglichkeiten', 'oepnv'); // Common ones
?>
				<h4 style="margin-top:20px; font-size:1rem; color:#666;">Entfernungen</h4>
				<ul class="dbw-infra-list"
					style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; padding:0; list-style:none;">
					<?php
		// Try fetching specific keys we mapped or all meta
		$custom_fields = get_post_custom($id);
		foreach ($custom_fields as $key => $val) {
			if (strpos($key, 'distanz_') === 0) {
				$label = ucfirst(str_replace('distanz_', '', $key));
				$value = $val[0];
				if (!$value)
					continue;
				echo "<li style='display:flex; justify-content:space-between; border-bottom:1px solid #eee; padding:5px 0;'><span>{$label}:</span> <strong>{$value} km</strong></li>";
			}
		}
?>
				</ul>
			</div>
			<?php
	endif; ?>

			<?php if (($energy_class || $energy_end) && get_theme_mod('dbw_immo_single_show_energy', true)): ?>
			<div class="dbw-section">
				<h3 class="dbw-section-title">Energieeffizienz</h3>
				<div class="dbw-features-list">
					<?php if ($energy_pass_art): ?>
					<div class="dbw-feature-item">
						<span class="dbw-meta-label">Art</span><br>
						<?php echo esc_html($energy_pass_art === 'BEDARF' ? 'Bedarfsausweis' : 'Verbrauchsausweis'); ?>
					</div>
					<?php
		endif; ?>

					<?php if ($energy_class): ?>
					<div class="dbw-feature-item">
						<span class="dbw-meta-label">Klasse</span><br>
						<span style="font-size:1.5rem; font-weight:bold; color:var(--dbw-accent);">
							<?php echo esc_html($energy_class); ?>
						</span>
					</div>
					<?php
		endif; ?>

					<?php if ($energy_end): ?>
					<div class="dbw-feature-item">
						<span class="dbw-meta-label">Endenergie</span><br>
						<?php echo esc_html($energy_end); ?> kWh/(m²*a)
					</div>
					<?php
		endif; ?>

					<?php if ($energy_source): ?>
					<div class="dbw-feature-item">
						<span class="dbw-meta-label">Träger</span><br>
						<?php echo esc_html($energy_source); ?>
					</div>
					<?php
		endif; ?>
				</div>
			</div>
			<?php
	endif; ?>

			<?php if (!empty($floor_plans)): ?>
			<div class="dbw-section">
				<h3 class="dbw-section-title">Grundrisse</h3>
				<div class="dbw-gallery-grid"
					style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); height:auto; gap:1rem;">
					<?php foreach ($floor_plans as $fp): ?>
					<a href="<?php echo esc_url($fp['full']); ?>" target="_blank" class="dbw-gallery-item"
						style="height:200px; display:block; background-image: url(<?php echo esc_url($fp['url']); ?>); background-size: contain; background-repeat:no-repeat; background-position: center; border:1px solid #ddd;"></a>
					<?php
		endforeach; ?>
				</div>
			</div>
			<?php
	endif; ?>

			<?php if ($text_sonstiges): ?>
			<div class="dbw-section">
				<h3 class="dbw-section-title">Sonstiges</h3>
				<div class="dbw-description">
					<?php echo wpautop(esc_html($text_sonstiges)); ?>
				</div>
			</div>
			<?php
	endif; ?>

		</div>

		<!-- Sidebar -->
		<aside class="dbw-sidebar">
			<div class="dbw-agent-card">
				<!-- Price Block -->
				<?php if ($price_kauf > 0): ?>
				<div class="dbw-property-price">
					<?php echo esc_html(number_format_i18n((float)$price_kauf, 0)); ?> €
					<span
						style="font-size: 0.8rem; color: #666; font-weight: 400; display: block; text-transform:uppercase;">Kaufpreis</span>
				</div>
				<?php
	elseif ($price_miete > 0): ?>
				<div class="dbw-property-price">
					<?php echo esc_html(number_format_i18n((float)$price_miete, 0)); ?> €
					<span
						style="font-size: 0.8rem; color: #666; font-weight: 400; display: block; text-transform:uppercase;">Kaltmiete</span>
				</div>
				<?php
	else: ?>
				<div class="dbw-property-price">
					Auf Anfrage
				</div>
				<?php
	endif; ?>

				<hr style="border: 0; border-top: 1px solid #eee; margin: 1.5rem 0;">

				<?php if (get_theme_mod('dbw_immo_single_show_contact', true)): ?>
				<!-- Contact Person -->
				<h4 style="margin-top: 0; margin-bottom: 1rem;">Ihr Ansprechpartner</h4>

				<div class="dbw-contact-flex"
					style="display: flex; align-items: center; gap: 15px; margin-bottom: 1.5rem;">
					<?php if ($contact_img_url): ?>
					<img src="<?php echo esc_url($contact_img_url); ?>" alt="<?php echo esc_attr($contact_name); ?>"
						style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
					<?php
		else: ?>
					<div
						style="width: 60px; height: 60px; border-radius: 50%; background: #eee; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #ccc;">
						<span class="dashicons dashicons-admin-users"></span>
					</div>
					<?php
		endif; ?>

					<div>
						<div style="font-weight: bold;">
							<?php echo esc_html($contact_firstname . ' ' . $contact_name); ?>
						</div>
						<?php if ($contact_tel): ?>
						<div style="font-size: 0.9rem; color: #666;"><a href="tel:<?php echo esc_attr($contact_tel); ?>"
								style="text-decoration:none; color:inherit;">
								<?php echo esc_html($contact_tel); ?>
							</a></div>
						<?php
		endif; ?>
					</div>
				</div>

				<?php if ($contact_email): ?>
				<a href="mailto:<?php echo esc_attr($contact_email); ?>?subject=Anfrage: <?php echo rawurlencode(get_the_title()); ?>"
					class="button button-primary"
					style="display:block; width: 100%; padding: 12px; height: auto; font-size: 1rem; text-transform: uppercase; font-weight: bold; background-color: var(--dbw-accent, #0073aa); border: none; color: #fff; cursor: pointer; border-radius: 4px; text-decoration:none; text-align:center;">Anfrage
					senden</a>
				<?php
		else: ?>
				<button disabled class="button" style="width: 100%;">Kontakt nur telefonisch</button>
				<?php
		endif; ?>

				<p style="font-size: 0.8rem; margin-top: 10px; color: #888; text-align: center;">Kontaktieren Sie uns
					für eine Besichtigung.</p>
				<?php
	endif; ?>
			</div>
		</aside>

	</div>

	<?php
endwhile; ?>

</div>

<?php
get_footer();
?>