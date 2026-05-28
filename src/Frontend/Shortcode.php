<?php

namespace DBW\ImmoSuite\Frontend;

/**
 * Shortcode Handler
 *
 * Available Shortcodes:
 *
 * [dbw_immo_grid]
 *   Displays active property listings in a grid.
 *   Attributes:
 *     count     - Number of properties (default: 6)
 *     columns   - Grid columns 1-4 (default: 3)
 *     marketing - Filter by Vermarktungsart slug (e.g. "kauf", "miete")
 *     type      - Filter by Objektart slug (e.g. "haus", "wohnung")
 *     location  - Filter by Ort slug (e.g. "muenchen", "berlin")
 *     highlights - "yes" to show only highlighted properties
 *     hide_price - "yes" to hide prices
 *     show_date  - "yes" to show listing date
 *
 * [dbw_immo_references]
 *   Displays sold/reference properties.
 *   Attributes:
 *     count     - Number of properties (default: 12)
 *     columns   - Grid columns 1-4 (default: 3)
 *     location  - Filter by Ort slug
 *     status    - Comma-separated statuses (default: "verkauft,referenz")
 *     hide_price - "yes"/"no" (default: from settings)
 *     show_date  - "yes"/"no" (default: from settings)
 */
class Shortcode
{

    public function init()
    {
        add_shortcode('dbw_immo_references', array($this, 'render_references'));
        add_shortcode('dbw_immo_grid', array($this, 'render_grid'));
    }

    /**
     * Render the Grid Shortcode.
     * Usage: [dbw_immo_grid count="6" columns="3" marketing="kauf" type="haus" location="muenchen" highlights="yes"]
     */
    public function render_grid($atts)
    {
        $atts = shortcode_atts(array(
            'count'      => 6,
            'columns'    => 3,
            'marketing'  => '',
            'type'       => '',
            'location'   => '',
            'highlights' => 'no',
            'hide_price' => 'no',
            'show_date'  => 'no',
        ), $atts, 'dbw_immo_grid');

        $posts_per_page = intval($atts['count']);
        $columns = max(1, min(4, intval($atts['columns'])));
        $hide_price = ($atts['hide_price'] === 'yes');
        $show_date = ($atts['show_date'] === 'yes');
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

        $args = array(
            'post_type'      => 'immobilie',
            'post_status'    => 'publish',
            'posts_per_page' => $posts_per_page,
            'paged'          => $paged,
            'order'          => 'DESC',
            'orderby'        => 'date',
        );

        // Tax Queries
        $tax_query = array();

        if (!empty($atts['marketing'])) {
            $tax_query[] = array(
                'taxonomy' => 'vermarktungsart',
                'field'    => 'slug',
                'terms'    => sanitize_text_field($atts['marketing']),
            );
        }

        if (!empty($atts['type'])) {
            $tax_query[] = array(
                'taxonomy' => 'objektart',
                'field'    => 'slug',
                'terms'    => sanitize_text_field($atts['type']),
            );
        }

        if (!empty($atts['location'])) {
            $tax_query[] = array(
                'taxonomy' => 'ort',
                'field'    => 'slug',
                'terms'    => sanitize_text_field($atts['location']),
            );
        }

        if (!empty($tax_query)) {
            if (count($tax_query) > 1) {
                $tax_query['relation'] = 'AND';
            }
            $args['tax_query'] = $tax_query;
        }

        // Meta Query
        $meta_query = array('relation' => 'AND');

        // Exclude sold from grid
        $settings = get_option('dbw_immo_suite_settings');
        if (isset($settings['filter_sold_from_main']) && $settings['filter_sold_from_main']) {
            $meta_query[] = array(
                'relation' => 'OR',
                array('key' => '_dbw_immo_status', 'compare' => 'NOT EXISTS'),
                array('key' => '_dbw_immo_status', 'value' => array('verkauft', 'referenz'), 'compare' => 'NOT IN'),
            );
        }

        if ($atts['highlights'] === 'yes') {
            $meta_query[] = array(
                'key'     => '_dbw_immo_is_highlight',
                'value'   => '1',
                'compare' => '=',
            );
        }

        if (count($meta_query) > 1) {
            $args['meta_query'] = $meta_query;
        }

        $query = new \WP_Query($args);
        $has_filter = class_exists('\DBW\ImmoSuite\Frontend\Filter');

        ob_start();

        if ($query->have_posts()) {
            $grid_style = ($columns !== 3) ? ' style="grid-template-columns: repeat(' . $columns . ', 1fr);"' : '';

            echo '<div id="dbw-immo-suite">';
            echo '<div class="dbw-immo-suite-block dbw-immo-grid-block">';
            echo '<div class="dbw-property-grid"' . $grid_style . '>';

            while ($query->have_posts()) {
                $query->the_post();
                $this->render_grid_card($hide_price, $show_date, $has_filter);
            }

            echo '</div>';
            $this->render_pagination($query);
            echo '</div>';
            echo '</div>';
        } else {
            echo '<p>' . __('Keine Immobilien gefunden.', 'dbw-immo-suite') . '</p>';
        }

        wp_reset_postdata();
        return ob_get_clean();
    }

    /**
     * Render the References Shortcode.
     * Usage: [dbw_immo_references count="12" columns="3" location="muenchen" status="verkauft,referenz"]
     */
    public function render_references($atts)
    {
        $atts = shortcode_atts(array(
            'count'      => 12,
            'columns'    => 3,
            'location'   => '',
            'status'     => 'verkauft,referenz',
            'hide_price' => '',
            'show_date'  => '',
        ), $atts, 'dbw_immo_references');

        $settings = get_option('dbw_immo_suite_settings');
        $posts_per_page = intval($atts['count']);
        $columns = max(1, min(4, intval($atts['columns'])));
        $statuses = array_map('trim', explode(',', sanitize_text_field($atts['status'])));

        // Use shortcode attr if provided, otherwise fall back to settings
        $hide_price = ($atts['hide_price'] !== '')
            ? ($atts['hide_price'] === 'yes')
            : (isset($settings['hide_price_sold']) && $settings['hide_price_sold']);
        $show_date = ($atts['show_date'] !== '')
            ? ($atts['show_date'] === 'yes')
            : (isset($settings['show_sold_date']) && $settings['show_sold_date']);

        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

        $args = array(
            'post_type'      => 'immobilie',
            'post_status'    => 'publish',
            'posts_per_page' => $posts_per_page,
            'paged'          => $paged,
            'meta_query'     => array(
                array(
                    'key'     => '_dbw_immo_status',
                    'value'   => $statuses,
                    'compare' => 'IN',
                )
            ),
            'orderby'  => 'meta_value',
            'meta_key' => '_dbw_immo_sales_date',
            'order'    => 'DESC',
        );

        // Location filter
        if (!empty($atts['location'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'ort',
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field($atts['location']),
                )
            );
        }

        $query = new \WP_Query($args);

        ob_start();

        if ($query->have_posts()) {
            $grid_style = ($columns !== 3) ? ' style="grid-template-columns: repeat(' . $columns . ', 1fr);"' : '';

            echo '<div id="dbw-immo-suite">';
            echo '<div class="dbw-immo-references-container">';
            echo '<div class="dbw-property-grid"' . $grid_style . '>';

            while ($query->have_posts()) {
                $query->the_post();
                $this->render_reference_card($hide_price, $show_date);
            }

            echo '</div>';
            $this->render_pagination($query);
            echo '</div>';
            echo '</div>';
        } else {
            echo '<p>' . __('Keine Referenzen gefunden.', 'dbw-immo-suite') . '</p>';
        }

        wp_reset_postdata();
        return ob_get_clean();
    }

    /**
     * Render a single grid card.
     */
    private function render_grid_card($hide_price, $show_date, $has_filter)
    {
        $post_id = get_the_ID();
        $price = get_post_meta($post_id, 'kaufpreis', true);
        $is_rent = false;
        if (!$price) {
            $price = get_post_meta($post_id, 'kaltmiete', true);
            $is_rent = !empty($price);
        }

        $area = get_post_meta($post_id, 'wohnflaeche', true);
        $rooms = get_post_meta($post_id, 'anzahl_zimmer', true);
        $location = get_post_meta($post_id, 'ort', true);
        $date_added = get_the_date();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('dbw-property-card'); ?>>
            <a href="<?php the_permalink(); ?>" class="dbw-property-image" style="<?php echo has_post_thumbnail() ? 'background-image: url(' . get_the_post_thumbnail_url($post_id, 'medium-large') . ');' : ''; ?>">
                <?php
                if ($has_filter) {
                    $tag_data = Filter::get_status_label($post_id);
                    if ($tag_data) {
                        echo '<span class="dbw-property-tag ' . esc_attr($tag_data['class']) . '">' . esc_html($tag_data['label']) . '</span>';
                    }
                }

                if (class_exists('\DBW\ImmoSuite\Frontend\EnergyRenderer')) {
                    EnergyRenderer::render_archive_flag($post_id);
                }
                ?>
            </a>

            <div class="dbw-property-content">
                <div class="dbw-card-body">
                    <h2 class="dbw-property-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h2>

                    <?php if ($location) : ?>
                        <div class="dbw-property-address">
                            <span class="dashicons dashicons-location"></span> <?php echo esc_html($location); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($show_date) : ?>
                        <div class="dbw-sales-date"><?php echo esc_html($date_added); ?></div>
                    <?php endif; ?>

                    <div class="dbw-card-meta-grid">
                        <?php if ($area) : ?>
                            <div class="dbw-meta-item">
                                <div class="dbw-meta-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="24" height="24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                                </div>
                                <div class="dbw-meta-data">
                                    <span class="dbw-meta-value"><?php echo esc_html($area); ?> m²</span>
                                    <span class="dbw-meta-label"><?php _e('Wohnfläche', 'dbw-immo-suite'); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($rooms) : ?>
                            <div class="dbw-meta-item">
                                <div class="dbw-meta-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="24" height="24"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                                </div>
                                <div class="dbw-meta-data">
                                    <span class="dbw-meta-value"><?php echo esc_html($rooms); ?> Zi.</span>
                                    <span class="dbw-meta-label"><?php _e('Zimmer', 'dbw-immo-suite'); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="dbw-card-footer">
                    <?php if (!$hide_price && $price) :
                        $price_label = $is_rent ? __('Kaltmiete', 'dbw-immo-suite') : __('Kaufpreis', 'dbw-immo-suite');
                    ?>
                        <div class="dbw-property-price">
                            <span class="dbw-price-label"><?php echo esc_html($price_label); ?></span>
                            <span class="dbw-price-value"><?php echo esc_html(number_format_i18n((float) $price, 0)); ?> €</span>
                        </div>
                    <?php else : ?>
                        <div class="dbw-property-price"></div>
                    <?php endif; ?>

                    <a href="<?php the_permalink(); ?>" class="dbw-button-expose"><?php echo esc_html(get_theme_mod('dbw_immo_expose_btn_text', __('Zum Exposé', 'dbw-immo-suite'))); ?></a>
                </div>
            </div>
        </article>
        <?php
    }

    /**
     * Render a single reference card.
     */
    private function render_reference_card($hide_price = null, $show_date = null)
    {
        $post_id = get_the_ID();
        $settings = get_option('dbw_immo_suite_settings');

        if ($hide_price === null) {
            $hide_price = isset($settings['hide_price_sold']) && $settings['hide_price_sold'];
        }
        if ($show_date === null) {
            $show_date = isset($settings['show_sold_date']) && $settings['show_sold_date'];
        }

        $price = get_post_meta($post_id, 'kaufpreis', true);
        $area = get_post_meta($post_id, 'wohnflaeche', true);
        $rooms = get_post_meta($post_id, 'anzahl_zimmer', true);
        $location = get_post_meta($post_id, 'ort', true);
        $sales_date = get_post_meta($post_id, '_dbw_immo_sales_date', true);
        $status = get_post_meta($post_id, '_dbw_immo_status', true);

        $badge_text = ($status === 'referenz')
            ? (isset($settings['reference_badge_text']) ? $settings['reference_badge_text'] : 'Referenz')
            : (isset($settings['sold_badge_text']) ? $settings['sold_badge_text'] : 'Verkauft');

        $badge_class = ($status === 'referenz') ? 'dbw-tag-reference' : 'dbw-tag-sold';
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('dbw-property-card dbw-reference-card'); ?>>
            <a href="<?php the_permalink(); ?>" class="dbw-property-image" style="<?php echo has_post_thumbnail() ? 'background-image: url(' . get_the_post_thumbnail_url($post_id, 'medium-large') . '); filter: grayscale(100%);' : ''; ?>">
                <span class="dbw-property-tag <?php echo esc_attr($badge_class); ?>"><?php echo esc_html($badge_text); ?></span>
            </a>

            <div class="dbw-property-content">
                <div class="dbw-card-body">
                    <h2 class="dbw-property-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h2>

                    <?php if ($location) : ?>
                        <div class="dbw-property-address">
                            <span class="dashicons dashicons-location"></span> <?php echo esc_html($location); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($show_date && $sales_date) : ?>
                        <div class="dbw-sales-date">
                            <?php echo date_i18n(get_option('date_format'), strtotime($sales_date)); ?>
                        </div>
                    <?php endif; ?>

                    <div class="dbw-card-meta-grid">
                        <?php if ($area) : ?>
                            <div class="dbw-meta-item">
                                <div class="dbw-meta-data">
                                    <span class="dbw-meta-value"><?php echo esc_html($area); ?> m²</span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($rooms) : ?>
                            <div class="dbw-meta-item">
                                <div class="dbw-meta-data">
                                    <span class="dbw-meta-value"><?php echo esc_html($rooms); ?> Zi.</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="dbw-card-footer">
                    <?php if (!$hide_price && $price) : ?>
                        <div class="dbw-property-price">
                            <span class="dbw-price-value"><?php echo esc_html(number_format_i18n((float) $price, 0)); ?> €</span>
                        </div>
                    <?php else : ?>
                        <div class="dbw-property-price"></div>
                    <?php endif; ?>

                    <a href="<?php the_permalink(); ?>" class="dbw-button-expose"><?php _e('Details', 'dbw-immo-suite'); ?></a>
                </div>
            </div>
        </article>
        <?php
    }

    /**
     * Render pagination for a custom query.
     */
    private function render_pagination($query)
    {
        if ($query->max_num_pages <= 1) {
            return;
        }

        $big = 999999999;
        $pages = paginate_links(array(
            'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format'    => '?paged=%#%',
            'current'   => max(1, get_query_var('paged')),
            'total'     => $query->max_num_pages,
            'type'      => 'array',
            'prev_text' => '&larr;',
            'next_text' => '&rarr;',
        ));

        if (is_array($pages)) {
            echo '<div class="dbw-pagination"><ul class="dbw-page-list">';
            foreach ($pages as $page) {
                echo '<li class="dbw-page-item">' . $page . '</li>';
            }
            echo '</ul></div>';
        }
    }
}
