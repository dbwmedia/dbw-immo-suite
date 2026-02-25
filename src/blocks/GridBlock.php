<?php

namespace DBW\ImmoSuite\blocks;

/**
 * Grid Block Handler
 */
class GridBlock
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
        register_block_type_from_metadata(DBW_IMMO_SUITE_PATH . 'build/blocks/immo-grid', array(
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
        $posts_per_page = isset($attributes['postsPerPage']) ? intval($attributes['postsPerPage']) : 3;
        $marketing = isset($attributes['marketing']) ? $attributes['marketing'] : '';
        $property_type = isset($attributes['propertyType']) ? $attributes['propertyType'] : '';
        $hide_price = isset($attributes['hidePrice']) ? $attributes['hidePrice'] : false;
        $show_date = isset($attributes['showDate']) ? $attributes['showDate'] : false;
        
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        
        $args = array(
            'post_type'      => 'immobilie',
            'post_status'    => 'publish',
            'posts_per_page' => $posts_per_page,
            'paged'          => $paged,
            'order'          => 'DESC',
            'orderby'        => 'date', // Default: Newest first
        );

        // Tax Queries based on editor selection
        $tax_query = array();

        if (!empty($marketing)) {
            $tax_query[] = array(
                'taxonomy' => 'vermarktungsart',
                'field'    => 'slug',
                'terms'    => $marketing,
            );
        }

        if (!empty($property_type)) {
            $tax_query[] = array(
                'taxonomy' => 'objektart',
                'field'    => 'slug',
                'terms'    => $property_type,
            );
        }

        if (!empty($tax_query)) {
            if (count($tax_query) > 1) {
                $tax_query['relation'] = 'AND';
            }
            $args['tax_query'] = $tax_query;
        }

        // Meta Query to exclude sold items by default from regular grid?
        // Let's exclude "referenz" and "verkauft" using the global setting if necessary, 
        // or just outright from the active listing block.
        $settings = get_option('dbw_immo_suite_settings');
        if (isset($settings['filter_sold_from_main']) && $settings['filter_sold_from_main']) {
            $args['meta_query'] = array(
                'relation' => 'OR',
                array(
                    'key'     => '_dbw_immo_status',
                    'compare' => 'NOT EXISTS' 
                ),
                array(
                    'key'     => '_dbw_immo_status',
                    'value'   => array('verkauft', 'referenz'),
                    'compare' => 'NOT IN'
                )
            );
        }

        $query = new \WP_Query($args);

        ob_start();
        
        if ($query->have_posts()) {
            
            // Required for rendering tags properly using our filter
            $has_filter = class_exists('\DBW\ImmoSuite\Frontend\Filter');

            echo '<div class="dbw-immo-suite-block dbw-immo-grid-block">'; // Generic wrapper
            echo '<div class="dbw-property-grid">';
            
            while ($query->have_posts()) {
                $query->the_post();
                
                $post_id = get_the_ID();
                $price = get_post_meta($post_id, 'kaufpreis', true);
                if ( ! $price ) {
                    $price = get_post_meta($post_id, 'kaltmiete', true );
                    if ( $price && !$hide_price) $price .= ' (Miete)';
                }

                $area = get_post_meta($post_id, 'wohnflaeche', true);
                $rooms = get_post_meta($post_id, 'anzahl_zimmer', true);
                $location = get_post_meta($post_id, 'ort', true);
                $date_added = get_the_date();

                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( 'dbw-property-card' ); ?>>
                    <!-- Image -->
                    <a href="<?php the_permalink(); ?>" class="dbw-property-image" style="<?php echo has_post_thumbnail() ? 'background-image: url(' . get_the_post_thumbnail_url( $post_id, 'medium-large' ) . ');' : ''; ?>">
                        <?php 
                        if ($has_filter) {
                            $tag_data = \DBW\ImmoSuite\Frontend\Filter::get_status_label($post_id);
                            if ($tag_data) {
                                echo '<span class="dbw-property-tag ' . esc_attr($tag_data['class']) . '">' . esc_html($tag_data['label']) . '</span>';
                            }
                        }
                        ?>
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

                            <?php if ($show_date): ?>
                                <div class="dbw-sales-date" style="font-size: 0.9em; color: #888; margin-top: 5px;">
                                    <?php echo esc_html($date_added); ?>
                                </div>
                            <?php endif; ?>

                            <!-- Meta -->
                            <div class="dbw-card-meta-grid">
                                <?php if ( $area ) : ?>
                                <div class="dbw-meta-item">
                                    <div class="dbw-meta-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                                    </div>
                                    <div class="dbw-meta-data">
                                        <span class="dbw-meta-value"><?php echo esc_html( $area ); ?> m²</span>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ( $rooms ) : ?>
                                <div class="dbw-meta-item">
                                    <div class="dbw-meta-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24"><path d="M3 3h8v8H3zm10 0h8v8h-8zM3 13h8v8H3zm10 0h8v8h-8z"/></svg>
                                    </div>
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
                                <span class="dbw-price-value"><?php echo esc_html( number_format_i18n( (float)intval($price), 0 ) ); ?> €</span>
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
            
            echo '</div>'; // grid

            // Pagination (only if enough posts and let's assume this block doesn't paginate by default, but it's good to have)
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
                echo '<p>' . __('Keine Immobilien für diesen Filter gefunden (Vorschau).', 'dbw-immo-suite') . '</p>';
            }
        }
        
        wp_reset_postdata();

        return ob_get_clean();
    }
}
