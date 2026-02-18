<?php
/**
 * Plugin Name: DBW ImmoSuite
 * Description: High-end Real Estate Management System with OpenImmo XML Support.
 * Version: 1.0.1
 * Author: Dennis Buchwald
 * Text Domain: dbw-immo-suite
 * Domain Path: /languages
 */

namespace DBW\ImmoSuite;

if (!defined('ABSPATH')) {
	exit;
}

// Define Constants
define('DBW_IMMO_SUITE_VERSION', '1.0.1');
define('DBW_IMMO_SUITE_PATH', plugin_dir_path(__FILE__));
define('DBW_IMMO_SUITE_URL', plugin_dir_url(__FILE__));

// PSR-4 Autoloader
spl_autoload_register(function ($class) {
	$prefix = 'DBW\\ImmoSuite\\';
	$base_dir = DBW_IMMO_SUITE_PATH . 'src/';

	$len = strlen($prefix);
	if (strncmp($prefix, $class, $len) !== 0) {
		return;
	}

	$relative_class = substr($class, $len);
	$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

	if (file_exists($file)) {
		require $file;
	}
});

// Initialize Plugin
function run_dbw_immo_suite()
{
	$plugin = new Core\Plugin();
	$plugin->run();
}
add_action('plugins_loaded', 'DBW\ImmoSuite\run_dbw_immo_suite');