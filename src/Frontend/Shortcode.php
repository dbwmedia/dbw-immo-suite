<?php

namespace DBW\ImmoSuite\Frontend;

/**
 * Shortcode Handler
 */
class Shortcode
{

    public function init()
    {
        add_shortcode('dbw_immo_references', array($this, 'render_references'));
    }

    /**
     * Render the References Shortcode.
     * 
     * @param array $atts
     * @return string
     */
    public function render_references($atts)
    {
        $settings = get_option('dbw_immo_suite_settings');
        
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        
        $args = array(
            'post_type'      => 'immobilie',
            'post_status'    => 'publish',
            'posts_per_page' => 12,
            'paged'          => $paged,
            'meta_query'     => array(
                array(
                    'key'     => '_dbw_immo_status',
                    'value'   => array('verkauft', 'referenz'),
                    'compare' => 'IN'
                )
            )
        );

        // Sorting (Basic)
        $args['orderby'] = 'meta_value';
        $args['meta_key'] = '_dbw_immo_sales_date';
        $args['order'] = 'DESC'; // Newest sales first

        $query = new \WP_Query($args);

        ob_start();
        
        if ($query->have_posts()) {
            echo '<div class="dbw-immo-references-container">';
            echo '<div class="dbw-property-grid">';
            
            while ($query->have_posts()) {
                $query->the_post();
                // Reuse the same logic as archive, or better: perform a specialized render
                // For now, let's duplicate the card structure specifically for references to ensure it looks good
                // and we can handle the specific "Sold" requirements easily.
                $this->render_reference_card();
            }
            
            echo '</div>'; // grid

            // Pagination
            $this->render_pagination($query);

            echo '</div>'; // container
            
        } else {
            echo '<p>' . __('Keine Referenzen gefunden.', 'dbw-immo-suite') . '</p>';
        }
        
        wp_reset_postdata();

        return ob_get_clean();
    }

    private function render_reference_card()
    {
        $post_id = get_the_ID();
        $settings = get_option('dbw_immo_suite_settings');
        
        $hide_price = isset($settings['hide_price_sold']) && $settings['hide_price_sold'];
        $show_date = isset($settings['show_sold_date']) && $settings['show_sold_date'];
        
        // Data
        $price = get_post_meta($post_id, 'kaufpreis', true);
        $area = get_post_meta($post_id, 'wohnflaeche', true);
        $rooms = get_post_meta($post_id, 'anzahl_zimmer', true);
        $location = get_post_meta($post_id, 'ort', true);
        $sales_date = get_post_meta($post_id, '_dbw_immo_sales_date', true);
        $status = get_post_meta($post_id, '_dbw_immo_status', true); // verkauft / referenz

        $badge_text = ($status === 'referenz') 
            ? (isset($settings['reference_badge_text']) ? $settings['reference_badge_text'] : 'Referenz')
            : (isset($settings['sold_badge_text']) ? $settings['sold_badge_text'] : 'Verkauft');
        
        $badge_class = ($status === 'referenz') ? 'dbw-tag-reference' : 'dbw-tag-sold';

        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class( 'dbw-property-card dbw-reference-card' ); ?>>
            <!-- Image -->
            <a href="<?php the_permalink(); ?>" class="dbw-property-image" style="<?php echo has_post_thumbnail() ? 'background-image: url(' . get_the_post_thumbnail_url( $post_id, 'medium-large' ) . '); filter: grayscale(100%);' : ''; ?>">
                <span class="dbw-property-tag <?php echo esc_attr($badge_class); ?>"><?php echo esc_html($badge_text); ?></span>
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

                    <?php if ($show_date && $sales_date): ?>
                        <div class="dbw-sales-date" style="font-size: 0.9em; color: #888; margin-top: 5px;">
                            <?php echo date_i18n(get_option('date_format'), strtotime($sales_date)); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Meta Grid (Reduced for Reference?) -->
                    <div class="dbw-card-meta-grid">
                        <?php if ( $area ) : ?>
                        <div class="dbw-meta-item">
                            <div class="dbw-meta-data">
                                <span class="dbw-meta-value"><?php echo esc_html( $area ); ?> m²</span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ( $rooms ) : ?>
                        <div class="dbw-meta-item">
                            <div class="dbw-meta-data">
                                <span class="dbw-meta-value"><?php echo esc_html( $rooms ); ?> Zi.</span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Footer -->
                <div class="dbw-card-footer">
                    <?php if ( !$hide_price && $price ) : ?>
                    <div class="dbw-property-price">
                        <span class="dbw-price-value"><?php echo esc_html( number_format_i18n( (float)$price, 0 ) ); ?> €</span>
                    </div>
                    <?php else: ?>
                        <div class="dbw-property-price"></div> <!-- Spacer -->
                    <?php endif; ?>

                    <a href="<?php the_permalink(); ?>" class="dbw-button-expose"><?php _e('Details', 'dbw-immo-suite'); ?></a>
                </div>
            </div>
        </article>
        <?php
    }

    private function render_pagination($query) {
        $big = 999999999;
        $pages = paginate_links(array(
            'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format' => '?paged=%#%',
            'current' => max(1, get_query_var('paged')),
            'total' => $query->max_num_pages,
            'type'  => 'array',
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
