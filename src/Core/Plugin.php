<?php

namespace DBW\ImmoSuite\Core;

/**
 * Main Plugin Class
 */
class Plugin
{

    /**
     * Loader instance
     *
     * @var Loader
     */
    protected $loader;

    /**
     * Initialize the plugin
     */
    public function __construct()
    {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies()
    {
        $this->loader = new Loader();
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     */
    private function define_admin_hooks()
    {
        $plugin_settings = new \DBW\ImmoSuite\Admin\Settings();
        $this->loader->add_action('init', $plugin_settings, 'init');

        // AJAX Import
        $importer = new \DBW\ImmoSuite\Import\Importer();
        $this->loader->add_action('wp_ajax_dbw_immo_run_import', $importer, 'ajax_run_import');
        $this->loader->add_action('wp_ajax_dbw_immo_prepare_import', $importer, 'ajax_prepare_import');
        $this->loader->add_action('wp_ajax_dbw_immo_process_batch', $importer, 'ajax_process_batch');

        // Admin Assets
        $this->loader->add_action('admin_enqueue_scripts', $this, 'enqueue_admin_scripts');
    }

    /**
     * Enqueue Admin Scripts
     */
    public function enqueue_admin_scripts()
    {
        wp_enqueue_script('dbw-immo-admin', DBW_IMMO_SUITE_URL . 'assets/js/admin.js', array('jquery'), DBW_IMMO_SUITE_VERSION, false);
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     */
    private function define_public_hooks()
    {
        $plugin_post_types = new \DBW\ImmoSuite\PostTypes\Property();
        $this->loader->add_action('init', $plugin_post_types, 'register_post_type');

        $plugin_tax_objektart = new \DBW\ImmoSuite\Taxonomies\PropertyType();
        $this->loader->add_action('init', $plugin_tax_objektart, 'register_taxonomy');

        $plugin_tax_vermarktung = new \DBW\ImmoSuite\Taxonomies\MarketingType();
        $this->loader->add_action('init', $plugin_tax_vermarktung, 'register_taxonomy');

        $plugin_tax_location = new \DBW\ImmoSuite\Taxonomies\Location();
        $this->loader->add_action('init', $plugin_tax_location, 'register_taxonomy');

        $plugin_customizer = new \DBW\ImmoSuite\Admin\Customizer();
        $this->loader->add_action('init', $plugin_customizer, 'init');

        $plugin_filter = new \DBW\ImmoSuite\Frontend\Filter();
        $this->loader->add_action('init', $plugin_filter, 'init');

        $plugin_templates = new \DBW\ImmoSuite\Frontend\TemplateLoader();
        $this->loader->add_action('init', $plugin_templates, 'init');

        $this->loader->add_action('wp_enqueue_scripts', $this, 'enqueue_public_scripts');
    }

    /**
     * Enqueue Public Scripts & Styles
     */
    public function enqueue_public_scripts()
    {
        wp_enqueue_style('dbw-immo-frontend', DBW_IMMO_SUITE_URL . 'assets/css/frontend.css', array(), DBW_IMMO_SUITE_VERSION, 'all');
        wp_enqueue_script('dbw-immo-frontend-js', DBW_IMMO_SUITE_URL . 'assets/js/frontend.js', array('jquery'), DBW_IMMO_SUITE_VERSION, true);
        wp_enqueue_script('dbw-immo-view-switch-js', DBW_IMMO_SUITE_URL . 'assets/js/view-switch.js', array(), DBW_IMMO_SUITE_VERSION, true);
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run()
    {
        $this->loader->run();
    }
}