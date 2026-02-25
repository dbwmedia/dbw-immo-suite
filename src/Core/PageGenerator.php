<?php

namespace DBW\ImmoSuite\Core;

/**
 * Handles automatic page generation
 */
class PageGenerator
{

    public function init()
    {
        add_action('dbw_immo_references_enabled', array($this, 'create_reference_page'));
    }

    /**
     * Create the reference page if it doesn't exist.
     *
     * @param array $settings
     */
    public function create_reference_page($settings)
    {
        $slug = !empty($settings['reference_slug']) ? $settings['reference_slug'] : 'referenzen';
        $title = !empty($settings['reference_badge_text']) ? $settings['reference_badge_text'] : 'Referenzen';

        // Check if page exists by slug
        $existing_page = get_page_by_path($slug);

        $page_id = 0;

        if ($existing_page) {
            // Page exists, do nothing but ensure ID is saved
            $page_id = $existing_page->ID;
        }
        else {
            // Create Page
            $page_data = array(
                'post_title' => $title,
                // Insert the new Gutenberg Block
                'post_content' => '<!-- wp:dbw/immo-references {"status":["verkauft","referenz"],"hidePrice":true,"showDate":true,"postsPerPage":12} /-->',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => $slug,
            );

            $page_id = wp_insert_post($page_data);
        }

        // Save page ID to settings if valid
        if ($page_id && !is_wp_error($page_id)) {
            $current_options = get_option('dbw_immo_suite_settings');
            if (empty($current_options['reference_page_id']) || $current_options['reference_page_id'] != $page_id) {
                // Update specific setting without triggering infinite loop
                $current_options['reference_page_id'] = $page_id;
                update_option('dbw_immo_suite_settings', $current_options);
            }
        }

        // Flush rewrite rules
        flush_rewrite_rules();
    }
}