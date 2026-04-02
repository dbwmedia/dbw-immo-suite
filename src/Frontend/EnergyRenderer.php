<?php

namespace DBW\ImmoSuite\Frontend;

class EnergyRenderer
{
    /**
     * Map of Energy Classes to their official EnEV color hex codes.
     */
    const COLOR_MAP = [
        'A+' => '#188a38',
        'A' => '#37a431',
        'B' => '#8eb32c',
        'C' => '#c6cc26',
        'D' => '#eae01c',
        'E' => '#f8ca12',
        'F' => '#e48325',
        'G' => '#c83f2a',
        'H' => '#b32822'
    ];

    /**
     * Renders the small flag for the archive grid.
     */
    public static function render_archive_flag($post_id)
    {
        if (!get_theme_mod('dbw_immo_archive_show_energy_class', true)) {
            return;
        }

        $class = get_post_meta($post_id, 'energiepass_wertklasse', true);

        if (empty($class) || !array_key_exists($class, self::COLOR_MAP)) {
            return;
        }

        $color = self::COLOR_MAP[$class];

        // It's a small badge positioned top-right
        echo sprintf(
            '<div class="dbw-energy-flag" style="position: absolute; top: 20px; right: 20px; background: %s; color: #fff; padding: 0px 15px 0px 10px; font-weight: bold; font-size: 14px; clip-path: polygon(0 0, calc(100%% - 8px) 0, 100%% 50%%, calc(100%% - 8px) 100%%, 0 100%%); z-index: 1; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">%s</div>',
            esc_attr($color),
            esc_html($class)
        );
    }

    /**
     * Renders the detailed energy scale for the single property view.
     */
    public static function render_single_scale($post_id)
    {
        // 1. Fetch Energy Data
        $energy_pass_art = get_post_meta($post_id, 'energiepass_art', true); // BEDARF, VERBRAUCH
        $energy_end = get_post_meta($post_id, 'energiepass_endenergie', true);
        $energy_class = get_post_meta($post_id, 'energiepass_wertklasse', true);
        $energy_source = get_post_meta($post_id, 'energiepass_traeger', true);
        $energy_valid = get_post_meta($post_id, 'energiepass_gueltig_bis', true);
        $energy_year = get_post_meta($post_id, 'energiepass_baujahr', true);

        // Sanitize
        $class = trim(strtoupper($energy_class));

        // Start HTML Output
        ob_start();
        ?>
        <div class="dbw-energy-container" style="background: #f5f5f5; border-radius: 12px; padding: 2rem; margin-bottom: 2rem;">
            <h3 class="dbw-section-title" style="margin-top: 0; margin-bottom: 1.5rem; font-size: 1.25rem;">Energie & Heizung
            </h3>

            <!-- Data Grid -->
            <div class="dbw-energy-grid"
                style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">

                <?php if ($energy_year): ?>
                    <div class="dbw-energy-item"
                        style="display:flex; justify-content: space-between; border-bottom: 1px solid #e0e0e0; padding-bottom: 8px;">
                        <span style="color:#666; font-size:0.9rem;">Baujahr</span>
                        <strong style="color:#333; font-size:0.95rem;"><?php echo esc_html($energy_year); ?></strong>
                    </div>
                <?php endif; ?>

                <?php if ($energy_pass_art): ?>
                    <div class="dbw-energy-item"
                        style="display:flex; justify-content: space-between; border-bottom: 1px solid #e0e0e0; padding-bottom: 8px;">
                        <span style="color:#666; font-size:0.9rem;">Ausweistyp</span>
                        <strong
                            style="color:#333; font-size:0.95rem;"><?php echo esc_html(ucfirst(strtolower($energy_pass_art)) . 'sausweis'); ?></strong>
                    </div>
                <?php endif; ?>

                <?php if ($energy_end): ?>
                    <div class="dbw-energy-item"
                        style="display:flex; justify-content: space-between; border-bottom: 1px solid #e0e0e0; padding-bottom: 8px;">
                        <span style="color:#666; font-size:0.9rem;">Endenergieverbrauch</span>
                        <strong style="color:#333; font-size:0.95rem;"><?php echo esc_html($energy_end); ?>
                            kWh/(m²&middot;a)</strong>
                    </div>
                <?php endif; ?>

                <?php if ($energy_source): ?>
                    <div class="dbw-energy-item"
                        style="display:flex; justify-content: space-between; border-bottom: 1px solid #e0e0e0; padding-bottom: 8px;">
                        <span style="color:#666; font-size:0.9rem;">Energieträger</span>
                        <strong style="color:#333; font-size:0.95rem;"><?php echo esc_html($energy_source); ?></strong>
                    </div>
                <?php endif; ?>

                <?php if ($energy_valid): ?>
                    <div class="dbw-energy-item"
                        style="display:flex; justify-content: space-between; border-bottom: 1px solid #e0e0e0; padding-bottom: 8px;">
                        <span style="color:#666; font-size:0.9rem;">Gültig bis</span>
                        <strong
                            style="color:#333; font-size:0.95rem;"><?php echo esc_html(date_i18n('d.m.Y', strtotime($energy_valid))); ?></strong>
                    </div>
                <?php endif; ?>

                <?php if ($class): ?>
                    <div class="dbw-energy-item"
                        style="display:flex; justify-content: space-between; border-bottom: 1px solid #e0e0e0; padding-bottom: 8px;">
                        <span style="color:#666; font-size:0.9rem;">Energieeffizienzklasse</span>
                        <strong style="color:#333; font-size:0.95rem;"><?php echo esc_html($class); ?></strong>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Color Scale -->
            <?php if ($class && array_key_exists($class, self::COLOR_MAP)): ?>
                <div class="dbw-energy-scale-wrapper" style="position: relative; margin-top: 3rem; padding-bottom: 10px;">

                    <div class="dbw-energy-scale-arrow-container" style="position: relative; height: 15px; margin-bottom: 10px;">
                        <style>
                            .dbw-scale-bar {
                                display: flex;
                                height: 28px;
                                border-radius: 14px;
                                overflow: hidden;
                            }

                            .dbw-scale-segment {
                                flex: 1;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                color: rgba(255, 255, 255, 0.9);
                                font-weight: bold;
                                font-size: 0.8rem;
                                position: relative;
                            }

                            /* Separators */
                            .dbw-scale-segment:not(:last-child)::after {
                                content: '';
                                position: absolute;
                                right: 0;
                                top: 0;
                                bottom: 0;
                                width: 1px;
                                background: rgba(255, 255, 255, 0.4);
                            }

                            .dbw-scale-indicator {
                                position: absolute;
                                top: 0;
                                transform: translateX(-50%);
                                display: flex;
                                flex-direction: column;
                                align-items: center;
                                transition: left 0.3s ease;
                            }

                            .dbw-scale-indicator svg {
                                width: 16px;
                                height: 16px;
                                fill: #333;
                            }

                            .dbw-energy-container {
                                /* Responsive tweaks */
                                container-type: inline-size;
                            }

                            @container (max-width: 500px) {
                                .dbw-energy-grid {
                                    grid-template-columns: 1fr !important;
                                    gap: 1rem !important;
                                }
                            }
                        </style>
                        <?php
                        // Calculate left position for the indicator arrow
                        $keys = array_keys(self::COLOR_MAP);
                        $index = array_search($class, $keys);
                        $total = count($keys);

                        // Center of the segment: (index + 0.5) / total * 100
                        $left_percent = (($index + 0.5) / $total) * 100;
                        ?>
                        <div class="dbw-scale-indicator" style="left: <?php echo $left_percent; ?>%;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path d="M12 21l-9-9h18z" />
                            </svg>
                        </div>
                    </div>

                    <div class="dbw-scale-bar">
                        <?php foreach (self::COLOR_MAP as $key => $color): ?>
                            <div class="dbw-scale-segment" style="background-color: <?php echo esc_attr($color); ?>;">
                                <?php echo esc_html($key); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
        <?php
        return ob_get_clean();
    }
}
