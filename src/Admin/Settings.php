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

		printf(
			'<input type="text" id="xml_path" name="%s[xml_path]" value="%s" class="regular-text" />',
			$this->option_name,
			esc_attr($val)
		);
		echo '<p class="description">' . __('Relativer Pfad vom WordPress-Root (z.B. "openimmo") oder absoluter Server-Pfad.', 'dbw-immo-suite') . '</p>';
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
}