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
	}

	public function sanitize( $input ) {
		$new_input = array();
		if ( isset( $input['xml_path'] ) ) {
			$new_input['xml_path'] = sanitize_text_field( $input['xml_path'] );
		}
		if ( isset( $input['cpt_slug'] ) ) {
			$new_input['cpt_slug'] = sanitize_title( $input['cpt_slug'] );
		}
		return $new_input;
	}

	public function cpt_slug_callback() {
		$options = get_option( $this->option_name );
		$val = ! empty( $options['cpt_slug'] ) ? $options['cpt_slug'] : 'immobilien';
		
		printf(
			'<input type="text" id="cpt_slug" name="%s[cpt_slug]" value="%s" class="regular-text" />',
			$this->option_name,
			esc_attr( $val )
		);
		echo '<p class="description">' . __( 'Standard: immobilien. Nach Änderung bitte Permalinks neu speichern!', 'dbw-immo-suite' ) . '</p>';
	}

	public function print_section_info()
	{
		print __('Bitte geben Sie den absoluten Pfad zum Ordner an, in dem die OpenImmo-Dateien liegen. Standardmäßig sucht das Plugin im Uploads-Verzeichnis under /openimmo/.', 'dbw-immo-suite');
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
}