<?php

namespace DBW\ImmoSuite\Admin;

/**
 * Dedicated Dashboard for Import Management
 */
class ImportDashboard
{

    public function init()
    {
        add_action('admin_menu', array($this, 'add_menu_page'));
    }

    public function add_menu_page()
    {
        add_submenu_page(
            'edit.php?post_type=immobilie',
            __('Import Zentrale', 'dbw-immo-suite'),
            __('Import Dashboard', 'dbw-immo-suite'),
            'manage_options',
            'dbw-immo-import',
            array($this, 'render_dashboard')
        );
    }

    public function render_dashboard()
    {
        // Get Last Import Status
        $lock = get_transient('dbw_immo_import_lock');
        $history = get_option('dbw_immo_import_history', array());
        $history = array_reverse($history); // Newest first

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e('OpenImmo Import Zentrale', 'dbw-immo-suite'); ?></h1>
            <hr class="wp-header-end">

            <!-- Status Card -->
            <div class="card" style="max-width: 100%; margin-top: 20px; padding: 20px;">
                <h2 class="title"><?php _e('System Status', 'dbw-immo-suite'); ?></h2>
                
                <?php if ($lock) : ?>
                    <div class="notice notice-warning inline" style="margin: 10px 0;">
                        <p><strong>⚠️ <?php _e('Import läuft gerade!', 'dbw-immo-suite'); ?></strong></p>
                        <p><?php printf(__('Gestartet: %s', 'dbw-immo-suite'), date('H:i:s', $lock)); ?></p>
                    </div>
                <?php else : ?>
                    <div class="notice notice-success inline" style="margin: 10px 0;">
                        <p>✅ <?php _e('System bereit. Kein Import aktiv.', 'dbw-immo-suite'); ?></p>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <button id="dbw-immo-trigger-import" type="button" class="button button-primary button-hero">
                            <?php _e('Import jetzt manuell starten', 'dbw-immo-suite'); ?>
                        </button>
                        <p class="description" style="margin-top: 10px;">
                            <?php _e('Verarbeitet alle XML-Dateien im konfigurierten Verzeichnis.', 'dbw-immo-suite'); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <div id="dbw-immo-import-status" style="margin-top: 20px; padding: 15px; background: #f0f0f1; border-radius: 4px; display: none;"></div>
            </div>

            <!-- History Table -->
            <h2 style="margin-top: 30px;"><?php _e('Import Historie (Letzte 20)', 'dbw-immo-suite'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 150px;"><?php _e('Datum', 'dbw-immo-suite'); ?></th>
                        <th><?php _e('Datei(en)', 'dbw-immo-suite'); ?></th>
                        <th style="width: 100px;"><?php _e('Erstellt', 'dbw-immo-suite'); ?></th>
                        <th style="width: 100px;"><?php _e('Aktualisiert', 'dbw-immo-suite'); ?></th>
                        <th style="width: 100px;"><?php _e('Fehler', 'dbw-immo-suite'); ?></th>
                        <th style="width: 100px;"><?php _e('Status', 'dbw-immo-suite'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history)) : ?>
                        <tr>
                            <td colspan="6"><?php _e('Keine Import-Historie gefunden.', 'dbw-immo-suite'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php 
                        $count = 0;
                        foreach ($history as $entry) : 
                            if ($count >= 20) break;
                            $count++;
                            $status_color = ($entry['status'] === 'success') ? '#46b450' : '#dc3232';
                        ?>
                        <tr>
                            <td><?php echo esc_html($entry['date']); ?></td>
                            <td><?php echo esc_html(basename($entry['file'])); ?></td>
                            <td><?php echo esc_html($entry['created']); ?></td>
                            <td><?php echo esc_html($entry['updated']); ?></td>
                            <td><?php echo esc_html($entry['errors']); ?></td>
                            <td style="color: <?php echo $status_color; ?>; font-weight: bold;">
                                <?php echo ($entry['status'] === 'success') ? 'OK' : 'Fehler'; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>
        <?php
    }
}
