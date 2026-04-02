<?php

namespace DBW\ImmoSuite\Admin;

/**
 * Settings Page for DBW ImmoSuite
 */
class Settings
{

	private $option_group = 'dbw_immo_suite_options';
	private $option_name = 'dbw_immo_suite_settings';

	public function init()
	{
		add_action('admin_menu', array($this, 'add_plugin_page'));
		add_action('admin_init', array($this, 'page_init'));
		add_action('wp_ajax_dbw_immo_validate_path', array($this, 'ajax_validate_path'));
	}

	public function add_plugin_page()
	{
		add_submenu_page(
			'edit.php?post_type=immobilie',
			__('ImmoSuite Einstellungen', 'dbw-immo-suite'),
			__('Einstellungen', 'dbw-immo-suite'),
			'manage_options',
			'dbw-immo-suite-settings',
			array($this, 'create_admin_page')
		);
	}

	public function create_admin_page()
	{
?>
<div class="wrap">
	<h1>
		<?php echo esc_html(get_admin_page_title()); ?>
	</h1>
	<form method="post" action="options.php">
		<?php
		settings_fields($this->option_group);
		do_settings_sections('dbw-immo-suite-settings');
		submit_button();
?>
	</form>

	<hr>

	<h2>
		<?php esc_html_e('Manueller Import', 'dbw-immo-suite'); ?>
	</h2>
	<p>
		<?php esc_html_e('Starten Sie den Import manuell. Dies verarbeitet alle XML-Dateien im konfigurierten Verzeichnis.', 'dbw-immo-suite'); ?>
	</p>
	<button id="dbw-immo-trigger-import" type="button" class="button button-large button-primary">
		<?php esc_html_e('Import jetzt starten', 'dbw-immo-suite'); ?>
	</button>
	<div id="dbw-immo-import-status" style="margin-top: 10px;"></div>
</div>
<?php
	}

	public function page_init()
	{
		register_setting(
			$this->option_group,
			$this->option_name,
			array($this, 'sanitize')
		);

		add_settings_section(
			'setting_section_id',
			__('OpenImmo Import Einstellungen', 'dbw-immo-suite'),
			array($this, 'print_section_info'),
			'dbw-immo-suite-settings'
		);

		add_settings_field(
			'xml_path',
			__('Pfad zu XML-Dateien', 'dbw-immo-suite'),
			array($this, 'xml_path_callback'),
			'dbw-immo-suite-settings',
			'setting_section_id'
		);

		add_settings_field(
			'cpt_slug',
			__('URL Slug (Permalink)', 'dbw-immo-suite'),
			array($this, 'cpt_slug_callback'),
			'dbw-immo-suite-settings',
			'setting_section_id'
		);

		add_settings_field(
			'enable_garbage_collection',
			__('Garbage Collection (Full Sync)', 'dbw-immo-suite'),
			array($this, 'enable_garbage_collection_callback'),
			'dbw-immo-suite-settings',
			'setting_section_id'
		);

		// -- Reference Section --
		add_settings_section(
			'reference_section_id',
			__('Referenzen & Verkaufte Objekte', 'dbw-immo-suite'),
			array($this, 'print_reference_section_info'),
			'dbw-immo-suite-settings'
		);

		add_settings_field(
			'enable_references',
			__('Aktivieren', 'dbw-immo-suite'),
			array($this, 'enable_references_callback'),
			'dbw-immo-suite-settings',
			'reference_section_id'
		);

		add_settings_field(
			'reference_slug',
			__('Seiten-Slug (URL)', 'dbw-immo-suite'),
			array($this, 'reference_slug_callback'),
			'dbw-immo-suite-settings',
			'reference_section_id'
		);

		add_settings_field(
			'filter_sold_from_main',
			__('Archiv bereinigen', 'dbw-immo-suite'),
			array($this, 'filter_sold_from_main_callback'),
			'dbw-immo-suite-settings',
			'reference_section_id'
		);

		add_settings_field(
			'reference_badge_text',
			__('Badge: Referenz', 'dbw-immo-suite'),
			array($this, 'reference_badge_text_callback'),
			'dbw-immo-suite-settings',
			'reference_section_id'
		);

		add_settings_field(
			'sold_badge_text',
			__('Badge: Verkauft', 'dbw-immo-suite'),
			array($this, 'sold_badge_text_callback'),
			'dbw-immo-suite-settings',
			'reference_section_id'
		);

		add_settings_field(
			'hide_price_sold',
			__('Preise ausblenden', 'dbw-immo-suite'),
			array($this, 'hide_price_sold_callback'),
			'dbw-immo-suite-settings',
			'reference_section_id'
		);

		add_settings_field(
			'show_sold_date',
			__('Verkaufsdatum', 'dbw-immo-suite'),
			array($this, 'show_sold_date_callback'),
			'dbw-immo-suite-settings',
			'reference_section_id'
		);
	}

	public function sanitize($input)
	{
		$new_input = array();
		if (isset($input['xml_path'])) {
			$new_input['xml_path'] = sanitize_text_field($input['xml_path']);
		}
		if (isset($input['cpt_slug'])) {
			$new_input['cpt_slug'] = sanitize_title($input['cpt_slug']);
		}
		$new_input['enable_garbage_collection'] = isset($input['enable_garbage_collection']) ? 1 : 0;

		// Reference Settings
		$new_input['enable_references'] = isset($input['enable_references']) ? 1 : 0;
		if (isset($input['reference_slug'])) {
			$new_input['reference_slug'] = sanitize_title($input['reference_slug']);
		}
		if (isset($input['reference_badge_text'])) {
			$new_input['reference_badge_text'] = sanitize_text_field($input['reference_badge_text']);
		}
		if (isset($input['sold_badge_text'])) {
			$new_input['sold_badge_text'] = sanitize_text_field($input['sold_badge_text']);
		}
		$new_input['hide_price_sold'] = isset($input['hide_price_sold']) ? 1 : 0;
		$new_input['show_sold_date'] = isset($input['show_sold_date']) ? 1 : 0;
		$new_input['filter_sold_from_main'] = isset($input['filter_sold_from_main']) ? 1 : 0; // Default off, user must enable

		// Trigger Page Generation if enabled and changed
		$old_options = get_option($this->option_name);
		$old_enable = isset($old_options['enable_references']) ? $old_options['enable_references'] : 0;

		if ($new_input['enable_references'] == 1 && $old_enable == 0) {
			// Just enabled
			do_action('dbw_immo_references_enabled', $new_input);
		}

		return $new_input;
	}

	public function cpt_slug_callback()
	{
		$options = get_option($this->option_name);
		$val = !empty($options['cpt_slug']) ? $options['cpt_slug'] : 'immobilien';

		printf(
			'<input type="text" id="cpt_slug" name="%s[cpt_slug]" value="%s" class="regular-text" />',
			$this->option_name,
			esc_attr($val)
		);
		echo '<p class="description">' . __('Standard: immobilien. Nach Änderung bitte Permalinks neu speichern!', 'dbw-immo-suite') . '</p>';
	}

	public function print_section_info()
	{
		print __('Bitte geben Sie den absoluten Pfad zum Ordner an, in dem die OpenImmo-Dateien liegen. Standardmäßig sucht das Plugin im Uploads-Verzeichnis under /openimmo/.', 'dbw-immo-suite');
	}

	public function print_reference_section_info()
	{
		print __('Konfiguration für das Referenz-System und verkaufte Immobilien.', 'dbw-immo-suite');
	}

	public function xml_path_callback()
	{
		$options = get_option($this->option_name);
		$val = isset($options['xml_path']) ? $options['xml_path'] : '';

		// Build preset paths for dropdown
		$upload_dir = wp_upload_dir();
		$presets = array(
			'' => __('-- Bitte wählen --', 'dbw-immo-suite'),
			$upload_dir['basedir'] . '/openimmo/' => 'wp-content/uploads/openimmo/ (Standard)',
			ABSPATH . 'openimmo/' => 'WordPress-Root/openimmo/',
		);

		// Check if current value is a custom path not in presets
		$is_custom = !empty($val) && !array_key_exists($val, $presets);

		echo '<div style="display:flex; flex-direction:column; gap:8px; max-width:600px;">';

		// Preset Dropdown
		echo '<select id="dbw_xml_path_preset" style="max-width:100%;" onchange="dbwPathPresetChange(this)">';
		foreach ($presets as $path => $label) {
			$selected = (!$is_custom && $val === $path) ? 'selected' : '';
			printf('<option value="%s" %s>%s</option>', esc_attr($path), $selected, esc_html($label));
		}
		$custom_selected = $is_custom ? 'selected' : '';
		echo '<option value="__custom__" ' . $custom_selected . '>' . __('Eigener Pfad...', 'dbw-immo-suite') . '</option>';
		echo '</select>';

		// Custom path input (hidden by default unless custom is selected)
		$custom_display = $is_custom ? 'flex' : 'none';
		printf(
			'<div id="dbw_custom_path_wrap" style="display:%s; align-items:center; gap:8px;">
				<input type="text" id="xml_path" name="%s[xml_path]" value="%s" class="regular-text" placeholder="%s" style="flex:1;" />
			</div>',
			$custom_display,
			$this->option_name,
			esc_attr($val),
			esc_attr('/var/www/vhosts/example.de/httpdocs/openimmo/')
		);

		// Hidden field for preset value (syncs with dropdown)
		if (!$is_custom) {
			printf(
				'<input type="hidden" id="xml_path_preset_hidden" name="%s[xml_path]" value="%s" />',
				$this->option_name,
				esc_attr($val)
			);
		}

		// Validate button + status
		echo '<div style="display:flex; align-items:center; gap:10px;">';
		echo '<button type="button" class="button" onclick="dbwValidatePath()" style="white-space:nowrap;">📂 Pfad prüfen</button>';
		echo '<span id="dbw_path_status"></span>';
		echo '</div>';

		// Info box
		echo '<div style="background:#f0f0f1; border-left:4px solid #2271b1; padding:8px 12px; font-size:12px; color:#555; border-radius:0 4px 4px 0;">';
		echo '<strong>Server-Info:</strong><br>';
		echo 'WordPress-Root: <code>' . esc_html(ABSPATH) . '</code><br>';
		echo 'Uploads: <code>' . esc_html($upload_dir['basedir']) . '</code>';
		echo '</div>';

		echo '</div>';

		// Inline JS for preset switching + AJAX validation
		?>
		<script>
		function dbwPathPresetChange(sel) {
			var customWrap = document.getElementById('dbw_custom_path_wrap');
			var presetHidden = document.getElementById('xml_path_preset_hidden');
			var customInput = document.getElementById('xml_path');

			if (sel.value === '__custom__') {
				customWrap.style.display = 'flex';
				if (presetHidden) presetHidden.remove();
				customInput.name = '<?php echo esc_js($this->option_name); ?>[xml_path]';
			} else {
				customWrap.style.display = 'none';
				// Sync preset value
				if (!presetHidden) {
					presetHidden = document.createElement('input');
					presetHidden.type = 'hidden';
					presetHidden.id = 'xml_path_preset_hidden';
					presetHidden.name = '<?php echo esc_js($this->option_name); ?>[xml_path]';
					sel.parentNode.appendChild(presetHidden);
				}
				presetHidden.value = sel.value;
				// Remove name from custom input to avoid conflict
				customInput.name = '';
			}
			document.getElementById('dbw_path_status').innerHTML = '';
		}

		function dbwValidatePath() {
			var preset = document.getElementById('dbw_xml_path_preset');
			var path = '';
			if (preset.value === '__custom__') {
				path = document.getElementById('xml_path').value;
			} else {
				path = preset.value;
			}
			var status = document.getElementById('dbw_path_status');
			status.innerHTML = '<span style="color:#666;">⏳ Prüfe...</span>';

			var data = new FormData();
			data.append('action', 'dbw_immo_validate_path');
			data.append('path', path);
			data.append('_wpnonce', '<?php echo wp_create_nonce('dbw_validate_path'); ?>');

			fetch(ajaxurl, { method: 'POST', body: data })
				.then(function(r) { return r.json(); })
				.then(function(res) {
					if (res.success) {
						status.innerHTML = '<span style="color:#00a32a; font-weight:bold;">✅ ' + res.data.message + '</span>';
					} else {
						status.innerHTML = '<span style="color:#d63638; font-weight:bold;">❌ ' + res.data.message + '</span>';
					}
				})
				.catch(function() {
					status.innerHTML = '<span style="color:#d63638;">❌ Fehler bei der Prüfung</span>';
				});
		}
		</script>
		<?php
	}

	public function enable_garbage_collection_callback()
	{
		$this->checkbox_callback('enable_garbage_collection', '<br>WICHTIG: Nur aktivieren, wenn Ihre Maklersoftware immer den <b>kompletten Bestand</b> (Full Sync) überträgt! Fehlt ein Objekt in der ZIP, wird es andernfalls archiviert.');
	}

	// -- Reference Callbacks --

	public function enable_references_callback()
	{
		$this->checkbox_callback('enable_references', 'Referenz-System aktivieren');
	}

	public function reference_slug_callback()
	{
		$options = get_option($this->option_name);
		$val = !empty($options['reference_slug']) ? $options['reference_slug'] : 'referenzen';
		printf(
			'<input type="text" id="reference_slug" name="%s[reference_slug]" value="%s" class="regular-text" />',
			$this->option_name,
			esc_attr($val)
		);
		echo '<p class="description">' . __('Slug der automatisch erstellten Seite.', 'dbw-immo-suite') . '</p>';
	}

	public function reference_badge_text_callback()
	{
		$this->text_callback('reference_badge_text', 'Referenz', 'Text für Referenz-Badge');
	}

	public function sold_badge_text_callback()
	{
		$this->text_callback('sold_badge_text', 'Verkauft', 'Text für Verkauft-Badge');
	}

	public function hide_price_sold_callback()
	{
		$this->checkbox_callback('hide_price_sold', 'Preis bei verkauften Objekten ausblenden');
	}

	public function show_sold_date_callback()
	{
		$this->checkbox_callback('show_sold_date', 'Verkaufsdatum anzeigen');
	}

	public function filter_sold_from_main_callback()
	{
		$this->checkbox_callback('filter_sold_from_main', 'Verkaufte Objekte aus normaler Liste ausblenden');
	}

	private function checkbox_callback($id, $label)
	{
		$options = get_option($this->option_name);
		$val = isset($options[$id]) && $options[$id] == 1 ? 'checked' : '';
		printf(
			'<input type="checkbox" id="%s" name="%s[%s]" value="1" %s /> <label for="%s">%s</label>',
			$id, $this->option_name, $id, $val, $id, $label
		);
	}

	private function text_callback($id, $default, $desc = '')
	{
		$options = get_option($this->option_name);
		$val = !empty($options[$id]) ? $options[$id] : $default;
		printf(
			'<input type="text" id="%s" name="%s[%s]" value="%s" class="regular-text" />',
			$id, $this->option_name, $id, esc_attr($val)
		);
		if ($desc)
			echo '<p class="description">' . $desc . '</p>';
	}

	/**
	 * AJAX handler to validate the import path
	 */
	public function ajax_validate_path()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => 'Keine Berechtigung.'));
		}

		check_ajax_referer('dbw_validate_path', '_wpnonce');

		$path = isset($_POST['path']) ? sanitize_text_field(wp_unslash($_POST['path'])) : '';

		if (empty($path)) {
			wp_send_json_error(array('message' => 'Kein Pfad angegeben.'));
		}

		// Try absolute path first
		$resolved = $path;
		if (!is_dir($resolved)) {
			// Try relative from ABSPATH
			$resolved = ABSPATH . ltrim($path, '/');
		}

		if (!is_dir($resolved)) {
			wp_send_json_error(array(
				'message' => sprintf('Verzeichnis nicht gefunden: %s', $path)
			));
		}

		// Check for XML/ZIP files
		$zips = glob(trailingslashit($resolved) . '*.zip');
		$xmls = glob(trailingslashit($resolved) . '*.xml');
		$file_count = count($zips) + count($xmls);

		$msg = sprintf('Verzeichnis existiert (%s)', $resolved);
		if ($file_count > 0) {
			$msg .= sprintf(' — %d Datei(en) gefunden (%d ZIP, %d XML)', $file_count, count($zips), count($xmls));
		} else {
			$msg .= ' — Noch keine Import-Dateien vorhanden.';
		}

		wp_send_json_success(array('message' => $msg));
	}
}