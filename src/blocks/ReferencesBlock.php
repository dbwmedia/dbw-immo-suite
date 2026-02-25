<?php

namespace DBW\ImmoSuite\blocks;

/**
 * References Block Handler
 */
class ReferencesBlock
{

    public function init()
    {
        $this->register_block();
    }

    /**
     * Register the block.
     */
    public function register_block()
    {
        // Enqueue block script
        register_block_type_from_metadata(DBW_IMMO_SUITE_PATH . 'build/blocks/immo-references', array(
            'render_callback' => array($this, 'render_block')
        ));
    }

    /**
     * Render the block on the front end.
     *
     * @param array $attributes Block attributes.
     * @param string $content Block content.
     * @return string Rendered block content.
     */
    public function render_block($attributes, $content)
    {
        $status = isset($attributes['status']) ? $attributes['status'] : array('verkauft', 'referenz');
        $posts_per_page = isset($attributes['postsPerPage']) ? intval($attributes['postsPerPage']) : 12;
        $hide_price = isset($attributes['hidePrice']) ? $attributes['hidePrice'] : true;
        $show_date = isset($attributes['showDate']) ? $attributes['showDate'] : true;
        
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        
        $args = array(
            'post_type'      => 'immobilie',
            'post_status'    => 'publish',
            'posts_per_page' => $posts_per_page,
            'paged'          => $paged,
            'meta_query'     => array(
                array(
                    'key'     => '_dbw_immo_status',
                    'value'   => $status,
                    'compare' => 'IN'
                )
            )
        );

        // Sorting (Newest sales first)
        $args['orderby'] = 'meta_value';
        $args['meta_key'] = '_dbw_immo_sales_date';
        $args['order'] = 'DESC';

        $query = new \WP_Query($args);

        ob_start();
        
        if ($query->have_posts()) {
            echo '<div class="dbw-immo-references-block">';
            echo '<div class="dbw-property-grid">';
            
            while ($query->have_posts()) {
                $query->the_post();
                // We should ideally reuse a template part here to avoid duplication with Shortcode.php
                // For this iteration, I'll inline a minimal version or use a helper but wait, 
                // Shortcode.php has a private method. 
                // Let's copy the logic for now, and note to refactor later.
                $this->render_card($hide_price, $show_date);
            }
            
            echo '</div>'; // grid

            // Pagination (only if enough posts)
            if ($query->max_num_pages > 1) {
                echo '<div class="dbw-pagination">';
                echo paginate_links(array(
                    'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                    'format' => '?paged=%#%',
                    'current' => max(1, get_query_var('paged')),
                    'total' => $query->max_num_pages,
                    'prev_text' => '&larr;',
                    'next_text' => '&rarr;',
                ));
                echo '</div>';
            }

            echo '</div>'; // block container
            
        } else {
            if (is_admin()) {
                echo '<p>' . __('Keine Referenzen gefunden (Vorschau).', 'dbw-immo-suite') . '</p>';
            }
        }
        
        wp_reset_postdata();

        return ob_get_clean();
    }

    private function render_card($hide_price, $show_date)
    {
        $post_id = get_the_ID();
        $settings = get_option('dbw_immo_suite_settings');
        
        // Settings override block attributes if not set? No, block attrs take precedence usually
        // But badges text comes from settings
        
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

                    <!-- Meta -->
                    <div class="dbw-card-meta-grid">
                        <?php if ( $area ) : ?>
                        <div class="dbw-meta-item">
                            <span class="dbw-meta-value"><?php echo esc_html( $area ); ?> m²</span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ( $rooms ) : ?>
                        <div class="dbw-meta-item">
                            <span class="dbw-meta-value"><?php echo esc_html( $rooms ); ?> Zi.</span>
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
                        <div class="dbw-property-price"></div>
                    <?php endif; ?>

                    <a href="<?php the_permalink(); ?>" class="dbw-button-expose"><?php _e('Details', 'dbw-immo-suite'); ?></a>
                </div>
            </div>
        </article>
        <?php
    }
}
