<?php

namespace DBW\ImmoSuite\Frontend;

if (!defined('ABSPATH')) { exit; }

/**
 * Kaufnebenkosten- & Finanzierungsrechner for single property view.
 * Only displayed for Kaufobjekte (kaufpreis > 0).
 */
class FinanceCalculator
{
    public function init()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * Enqueue JS only on single immobilie pages with a Kaufpreis.
     */
    public function enqueue_assets()
    {
        if (!is_singular('immobilie')) {
            return;
        }

        if (!get_theme_mod('dbw_immo_single_show_calculator', true)) {
            return;
        }

        $post_id = get_the_ID();
        $kaufpreis = (float) get_post_meta($post_id, 'kaufpreis', true);

        if ($kaufpreis <= 0) {
            return;
        }

        $provision_raw = get_post_meta($post_id, 'provision_kaeufer', true);
        $plz = get_post_meta($post_id, 'plz', true);

        wp_enqueue_script(
            'dbw-immo-finance-calculator',
            DBW_IMMO_SUITE_URL . 'assets/js/finance-calculator.js',
            array(),
            DBW_IMMO_SUITE_VERSION,
            true
        );

        wp_localize_script('dbw-immo-finance-calculator', 'dbwFinanceCalc', array(
            'kaufpreis'  => $kaufpreis,
            'provision'  => $provision_raw,
            'plz'        => $plz,
            'i18n'       => array(
                'headline'          => \DBW\ImmoSuite\dbw_anrede(
                    __('Ihre Kaufnebenkosten & Finanzierung', 'dbw-immo-suite'),
                    __('Deine Kaufnebenkosten & Finanzierung', 'dbw-immo-suite')
                ),
                'kaufpreis'         => __('Kaufpreis', 'dbw-immo-suite'),
                'grunderwerbsteuer' => __('Grunderwerbsteuer', 'dbw-immo-suite'),
                'notarkosten'       => __('Notarkosten', 'dbw-immo-suite'),
                'grundbuchamt'      => __('Grundbuchamt', 'dbw-immo-suite'),
                'maklerprovision'   => __('Maklerprovision', 'dbw-immo-suite'),
                'gesamtkosten'      => __('Gesamtkosten', 'dbw-immo-suite'),
                'eigenkapital'      => __('Eigenkapital', 'dbw-immo-suite'),
                'zinssatz'          => __('Zinssatz', 'dbw-immo-suite'),
                'tilgung'           => __('Tilgung', 'dbw-immo-suite'),
                'darlehenssumme'    => __('Darlehenssumme', 'dbw-immo-suite'),
                'monatliche_rate'   => __('Monatliche Rate', 'dbw-immo-suite'),
                'zinskosten_10j'    => __('Zinskosten nach 10 Jahren', 'dbw-immo-suite'),
                'finanzierung'      => \DBW\ImmoSuite\dbw_anrede(
                    __('Ihre Finanzierung berechnen', 'dbw-immo-suite'),
                    __('Deine Finanzierung berechnen', 'dbw-immo-suite')
                ),
                'hinweis'           => \DBW\ImmoSuite\dbw_anrede(
                    __('Die Berechnung dient als unverbindliche Orientierung. Sprechen Sie uns fuer ein individuelles Finanzierungsangebot an.', 'dbw-immo-suite'),
                    __('Die Berechnung dient als unverbindliche Orientierung. Sprich uns fuer ein individuelles Finanzierungsangebot an.', 'dbw-immo-suite')
                ),
                'bundesland_unknown' => __('Bundesland nicht erkannt', 'dbw-immo-suite'),
            ),
        ));
    }

    /**
     * Render the calculator section.
     * Called from single-immobilie.php template.
     *
     * @param int $post_id
     */
    public static function render($post_id)
    {
        if (!get_theme_mod('dbw_immo_single_show_calculator', true)) {
            return;
        }

        $kaufpreis = (float) get_post_meta($post_id, 'kaufpreis', true);

        if ($kaufpreis <= 0) {
            return;
        }

        ?>
        <div class="dbw-section dbw-finance-calculator" id="dbw-finance-calculator">
            <h3 class="dbw-section-title" id="dbw-calc-headline"></h3>

            <!-- Kaufnebenkosten Tabelle -->
            <div class="dbw-calc-nebenkosten">
                <table class="dbw-calc-table">
                    <tbody>
                        <tr>
                            <td id="dbw-calc-label-kaufpreis"></td>
                            <td class="dbw-calc-value" id="dbw-calc-kaufpreis"></td>
                        </tr>
                        <tr>
                            <td>
                                <span id="dbw-calc-label-gest"></span>
                                <span class="dbw-calc-detail" id="dbw-calc-gest-detail"></span>
                            </td>
                            <td class="dbw-calc-value" id="dbw-calc-gest"></td>
                        </tr>
                        <tr>
                            <td>
                                <span id="dbw-calc-label-notar"></span>
                                <span class="dbw-calc-detail">(1,5%)</span>
                            </td>
                            <td class="dbw-calc-value" id="dbw-calc-notar"></td>
                        </tr>
                        <tr>
                            <td>
                                <span id="dbw-calc-label-grundbuch"></span>
                                <span class="dbw-calc-detail">(0,5%)</span>
                            </td>
                            <td class="dbw-calc-value" id="dbw-calc-grundbuch"></td>
                        </tr>
                        <tr id="dbw-calc-provision-row">
                            <td>
                                <span id="dbw-calc-label-provision"></span>
                                <span class="dbw-calc-detail" id="dbw-calc-provision-detail"></span>
                            </td>
                            <td class="dbw-calc-value" id="dbw-calc-provision"></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="dbw-calc-total">
                            <td id="dbw-calc-label-gesamt"></td>
                            <td class="dbw-calc-value" id="dbw-calc-gesamt"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Finanzierungsrechner -->
            <div class="dbw-calc-finanzierung">
                <h4 class="dbw-calc-subtitle" id="dbw-calc-fin-headline"></h4>

                <div class="dbw-calc-sliders">
                    <div class="dbw-calc-slider-group">
                        <div class="dbw-calc-slider-header">
                            <label for="dbw-calc-eigenkapital" id="dbw-calc-label-ek"></label>
                            <output id="dbw-calc-ek-output" class="dbw-calc-output"></output>
                        </div>
                        <input type="range" id="dbw-calc-eigenkapital" class="dbw-calc-range" min="0" step="1000">
                    </div>

                    <div class="dbw-calc-slider-group">
                        <div class="dbw-calc-slider-header">
                            <label for="dbw-calc-zinssatz" id="dbw-calc-label-zins"></label>
                            <output id="dbw-calc-zins-output" class="dbw-calc-output"></output>
                        </div>
                        <input type="range" id="dbw-calc-zinssatz" class="dbw-calc-range" min="1.0" max="6.0" step="0.1" value="3.5">
                    </div>

                    <div class="dbw-calc-slider-group">
                        <div class="dbw-calc-slider-header">
                            <label for="dbw-calc-tilgung" id="dbw-calc-label-tilgung"></label>
                            <output id="dbw-calc-tilgung-output" class="dbw-calc-output"></output>
                        </div>
                        <input type="range" id="dbw-calc-tilgung" class="dbw-calc-range" min="1.0" max="5.0" step="0.1" value="2.0">
                    </div>
                </div>

                <!-- Ergebnis -->
                <div class="dbw-calc-result">
                    <div class="dbw-calc-result-row">
                        <span id="dbw-calc-label-darlehen"></span>
                        <strong id="dbw-calc-darlehen"></strong>
                    </div>
                    <div class="dbw-calc-result-row dbw-calc-result-highlight">
                        <span id="dbw-calc-label-rate"></span>
                        <strong id="dbw-calc-rate"></strong>
                    </div>
                    <div class="dbw-calc-result-row">
                        <span id="dbw-calc-label-zinskosten"></span>
                        <strong id="dbw-calc-zinskosten"></strong>
                    </div>
                </div>

                <p class="dbw-calc-hinweis" id="dbw-calc-hinweis"></p>
            </div>
        </div>
        <?php
    }
}
