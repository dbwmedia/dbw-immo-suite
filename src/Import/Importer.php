<?php

namespace DBW\ImmoSuite\Import;

/**
 * Handles OpenImmo XML Import
 */
class Importer
{

    /**
     * Current processing XML file path.
     * @var string
     */
    private $current_xml_file;

    /**
     * Run the import process.
     *
     * @return array Result stats.
     */
    public function run_import()
    {
        // 1. Check Lock
        if (get_transient('dbw_immo_import_lock')) {
            return array('success' => false, 'message' => 'Import läuft bereits. Bitte warten.');
        }

        // 2. Set Lock (expires in 10 mins security fallback)
        set_transient('dbw_immo_import_lock', time(), 600);

        $this->log_debug('--- Starte Import ---');

        try {
            // Increase limits
            @set_time_limit(600);
            @ini_set('max_execution_time', 600);
            @ini_set('memory_limit', '2048M');

            $options = get_option('dbw_immo_suite_settings');
            $xml_path = isset($options['xml_path']) ? $options['xml_path'] : '';

            // Path Logic (same as before)
            if (!empty($xml_path) && is_dir($xml_path)) {
                $xml_path = trailingslashit($xml_path);
            }
            elseif (!empty($xml_path) && is_dir(ABSPATH . ltrim($xml_path, '/'))) {
                $xml_path = trailingslashit(ABSPATH . ltrim($xml_path, '/'));
            }
            elseif (empty($xml_path)) {
                $upload_dir = wp_upload_dir();
                $xml_path = $upload_dir['basedir'] . '/openimmo/';
            }
            else {
                throw new \Exception(sprintf('Verzeichnis "%s" nicht gefunden.', $xml_path));
            }

            if (!is_dir($xml_path)) {
                throw new \Exception('Kein gültiges Import-Verzeichnis: ' . $xml_path);
            }

            // ZIP Extraction
            $zips = glob($xml_path . '*.zip');
            if (!empty($zips)) {
                foreach ($zips as $zip_file) {
                    $this->extract_zip($zip_file, $xml_path);
                }
            }

            // XML Search
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($xml_path));
            $xml_files = array();
            foreach ($files as $file) {
                if ($file->isFile() && strtolower($file->getExtension()) === 'xml') {
                    $xml_files[] = $file->getPathname();
                }
            }

            if (empty($xml_files)) {
                throw new \Exception('Keine XML-Dateien gefunden in: ' . $xml_path);
            }

            $stats = array(
                'created' => 0,
                'updated' => 0,
                'errors' => 0,
            );

            // Process Files
            foreach ($xml_files as $file) {
                $this->process_file($file, $stats);

                // Add to History
                $this->log_history($file, $stats, 'success');
            }

            // Release Lock
            delete_transient('dbw_immo_import_lock');

            return array(
                'success' => true,
                'message' => sprintf('Import fertig. Erstellt: %d, Aktualisiert: %d, Fehler: %d', $stats['created'], $stats['updated'], $stats['errors']),
            );

        }
        catch (\Exception $e) {
            // Error Handling
            delete_transient('dbw_immo_import_lock');
            $this->log_debug('Error: ' . $e->getMessage());

            // Log failed run
            $this->log_history('System', array('created' => 0, 'updated' => 0, 'errors' => 1), 'error');

            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    private function log_history($file, $stats, $status)
    {
        $history = get_option('dbw_immo_import_history', array());

        $entry = array(
            'date' => current_time('mysql'),
            'file' => basename($file),
            'status' => $status,
            'created' => $stats['created'],
            'updated' => $stats['updated'],
            'errors' => $stats['errors']
        );

        $history[] = $entry;

        // Keep last 50
        if (count($history) > 50) {
            $history = array_slice($history, -50);
        }

        update_option('dbw_immo_import_history', $history);
    }

    /**
     * Extract ZIP file.
     * 
     * @param string $zip_file
     * @param string $target_path
     */
    private function extract_zip($zip_file, $target_path)
    {
        $zip = new \ZipArchive;
        if ($zip->open($zip_file) === TRUE) {
            // Extract to a folder named after the zip to avoid collisions
            $extract_to = $target_path . pathinfo($zip_file, PATHINFO_FILENAME) . '/';
            if (!is_dir($extract_to)) {
                mkdir($extract_to, 0755, true);
            }
            $zip->extractTo($extract_to);
            $zip->close();

            // Optional: Rename/Move ZIP to '.processed' or similar to avoid re-extraction?
            // For now, we leave it. Real production code should move it.
            rename($zip_file, $zip_file . '.processed');
        }
    }

    /**
     * Process a single XML file.
     *
     * @param string $file Path to XML file.
     * @param array  $stats Statistics array (reference).
     */
    private function process_file($file, &$stats)
    {
        libxml_use_internal_errors(true);
        $this->current_xml_file = $file; // Store current file path for image resolution
        $xml = simplexml_load_file($file);

        if (!$xml) {
            $stats['errors']++;
            return;
        }

        // Support wrapping 'openimmo_feedback' or just 'anbieter' list
        if (isset($xml->anbieter)) {
            foreach ($xml->anbieter as $anbieter) {
                if (isset($anbieter->immobilie)) {
                    foreach ($anbieter->immobilie as $immobilie) {
                        $this->import_property($immobilie, $stats);
                    }
                }
            }
        }
    }

    /**
     * Import a single property node.
     *
     * @param SimpleXMLElement $immobilie Property XML node.
     * @param array            $stats Statistics array.
     */
    private function import_property($immobilie, &$stats)
    {
        $verwaltung_techn = $immobilie->verwaltung_techn;
        $openimmo_id = (string)$verwaltung_techn->openimmo_obid; // Standard path

        if (empty($openimmo_id) && isset($immobilie->obid)) {
            $openimmo_id = (string)$immobilie->obid;
        }

        if (empty($openimmo_id)) {
            $stats['errors']++;
            return;
        }

        // Check Action Type (DELETE, ARCHIVE, etc.)
        $action_type = '';
        if (isset($verwaltung_techn->aktion)) {
            $action_type = (string)$verwaltung_techn->aktion['actiontype'];
            // Sometimes it's the node value
            if (empty($action_type)) {
                $action_type = (string)$verwaltung_techn->aktion;
            }
            $action_type = strtoupper($action_type);
        }

        $existing_id = $this->get_property_by_openimmo_id($openimmo_id);

        // HANDLE DELETION
        if ($action_type === 'DELETE' || $action_type === 'LÖSCHEN' || $action_type === 'LOESCHEN') {
            if ($existing_id) {
                // Move to trash
                wp_trash_post($existing_id);
                $this->log_debug("Immobilie $openimmo_id ($existing_id) wurde in den Papierkorb verschoben (Action: $action_type).");
                $stats['updated']++; // Count as handled
            }
            return; // Stop processing this property
        }

        // HANDLE ARCHIVING
        if ($action_type === 'ARCHIVE' || $action_type === 'ARCHIVIEREN') {
            if ($existing_id) {
                $update_data = array(
                    'ID' => $existing_id,
                    'post_status' => 'draft' // Set to draft
                );
                wp_update_post($update_data);
                $this->log_debug("Immobilie $openimmo_id ($existing_id) wurde archiviert/Entwurf (Action: $action_type).");
                $stats['updated']++;
            }
            return; // Stop processing
        }


        // Title Generation
        $geo = $immobilie->geo;
        $freitexte = $immobilie->freitexte;
        $titel = isset($freitexte->objekttitel) ? (string)$freitexte->objekttitel : 'Immobilie ' . $openimmo_id;

        $post_data = array(
            'post_title' => $titel,
            'post_content' => isset($freitexte->objektbeschreibung) ? (string)$freitexte->objektbeschreibung : '',
            'post_status' => 'publish',
            'post_type' => 'immobilie',
        );

        if ($existing_id) {
            $post_data['ID'] = $existing_id;
            wp_update_post($post_data);
            $post_id = $existing_id;
            $stats['updated']++;
        }
        else {
            $post_id = wp_insert_post($post_data);
            $stats['created']++;
            update_post_meta($post_id, 'openimmo_id', $openimmo_id);
        }

        // Map Fields
        $this->map_fields($post_id, $immobilie);

        // Process Attachments
        // We need the base path of the XML file to find images
        if (isset($this->current_xml_file)) {
            $base_path = dirname($this->current_xml_file);
            $this->process_attachments($post_id, $immobilie, $base_path);
            $this->process_contact_image($post_id, $immobilie, $base_path);
        }
    }

    /**
     * Map XML fields to Post Meta.
     * 
     * @param int $post_id
     * @param SimpleXMLElement $xml
     */
    private function map_fields($post_id, $xml)
    {
        // Basic Pricing
        if (isset($xml->preise)) {
            $kaufpreis = (string)$xml->preise->kaufpreis;
            $kaltmiete = (string)$xml->preise->kaltmiete;
            $warmmiete = (string)$xml->preise->warmmiete;

            update_post_meta($post_id, 'kaufpreis', $kaufpreis);
            update_post_meta($post_id, 'kaltmiete', $kaltmiete);
            update_post_meta($post_id, 'warmmiete', $warmmiete);

            // Set Marketing Type Taxonomy
            $marketing_terms = array();
            if (!empty($kaufpreis) && floatval($kaufpreis) > 0) {
                $marketing_terms[] = 'Kauf';
            }
            if ((!empty($kaltmiete) && floatval($kaltmiete) > 0) || (!empty($warmmiete) && floatval($warmmiete) > 0)) {
                $marketing_terms[] = 'Miete';
            }

            // Fallback: Check OpenImmo <vermarktungsart> if available (often checked via attributes or subnodes)
            // For now, price-based is robust enough for standard XMLs.

            if (!empty($marketing_terms)) {
                wp_set_object_terms($post_id, $marketing_terms, 'vermarktungsart', false); // Append = false (overwrite)
            }
        }

        // Areas
        if (isset($xml->flaechen)) {
            update_post_meta($post_id, 'wohnflaeche', (string)$xml->flaechen->wohnflaeche);
            update_post_meta($post_id, 'nutzflaeche', (string)$xml->flaechen->nutzflaeche);
            update_post_meta($post_id, 'grundstuecksflaeche', (string)$xml->flaechen->grundstuecksflaeche);
            update_post_meta($post_id, 'anzahl_zimmer', (string)$xml->flaechen->anzahl_zimmer);
            update_post_meta($post_id, 'anzahl_schlafzimmer', (string)$xml->flaechen->anzahl_schlafzimmer);
            update_post_meta($post_id, 'anzahl_badezimmer', (string)$xml->flaechen->anzahl_badezimmer);
            update_post_meta($post_id, 'anzahl_stellplaetze', (string)$xml->flaechen->anzahl_stellplaetze);
        }

        // Geo
        if (isset($xml->geo)) {
            update_post_meta($post_id, 'plz', (string)$xml->geo->plz);
            update_post_meta($post_id, 'ort', (string)$xml->geo->ort);
            update_post_meta($post_id, 'strasse', (string)$xml->geo->strasse);
            update_post_meta($post_id, 'hausnummer', (string)$xml->geo->hausnummer);

            if (isset($xml->geo->geokoordinaten)) {
                $coord = $xml->geo->geokoordinaten->attributes();
                update_post_meta($post_id, 'geo_breite', (string)$coord['breitengrad']);
                update_post_meta($post_id, 'geo_laenge', (string)$coord['laengengrad']);
            }

            // Set Location Taxonomy
            wp_set_object_terms($post_id, (string)$xml->geo->ort, 'ort', true);
        }

        // Infrastructure
        if (isset($xml->infrastruktur)) {
            $infra_data = array();
            foreach ($xml->infrastruktur->distanzen as $dist) {
                $type = (string)$dist['distanz_zu'];
                $val = (string)$dist;
                update_post_meta($post_id, 'distanz_' . strtolower($type), $val);
                $infra_data[$type] = $val;
            }
            // Optional: Store all as array for easier looping if needed
            update_post_meta($post_id, 'infrastruktur_all', $infra_data);
        }

        // Zustand & Baujahr (Generic)
        if (isset($xml->zustand_angaben)) {
            $zustand = $xml->zustand_angaben;
            if (isset($zustand->baujahr)) {
                update_post_meta($post_id, 'energiepass_baujahr', (string)$zustand->baujahr);
            }
            if (isset($zustand->zustand_art)) {
                update_post_meta($post_id, 'zustand_art', (string)$zustand->zustand_art);
            }
        }

        // Energy Pass
        if (isset($xml->zustand_angaben->energiepass)) {
            $ep = $xml->zustand_angaben->energiepass;
            update_post_meta($post_id, 'energiepass_art', (string)$ep->epart);
            update_post_meta($post_id, 'energiepass_gueltig_bis', (string)$ep->gueltig_bis);
            update_post_meta($post_id, 'energiepass_endenergie', (string)$ep->endenergiebedarf);
            update_post_meta($post_id, 'energiepass_traeger', (string)$ep->primaerenergietraeger);
            update_post_meta($post_id, 'energiepass_wertklasse', (string)$ep->wertklasse);
            update_post_meta($post_id, 'energiepass_baujahr', (string)$ep->baujahr);
        }

        // Detailed Texts
        if (isset($xml->freitexte)) {
            update_post_meta($post_id, 'text_lage', (string)$xml->freitexte->lage);
            update_post_meta($post_id, 'text_ausstattung', (string)$xml->freitexte->ausstatt_beschr);
            update_post_meta($post_id, 'text_sonstiges', (string)$xml->freitexte->sonstige_angaben);
        }

        // Object Type Taxonomy
        if (isset($xml->objektkategorie)) {
            // Iterate over children to find the type (e.g. <objektart><haus>...</haus></objektart>)
            // Simplified: Just taking the key name of the detailed type
            if (isset($xml->objektkategorie->objektart)) {
                foreach ($xml->objektkategorie->objektart->children() as $child) {
                    $type_name = $child->getName(); // e.g. 'haus', 'wohnung'
                    wp_set_object_terms($post_id, ucfirst($type_name), 'objektart', true);
                    break;
                }
            }
            if (isset($xml->objektkategorie->nutzungsart)) {
                $usage_attributes = $xml->objektkategorie->nutzungsart->attributes();
                foreach ($usage_attributes as $name => $val) {
                    if ((string)$val === 'true' || (string)$val === '1') {
                        // e.g. WOHNEN or GEWERBE
                        wp_set_object_terms($post_id, ucfirst(strtolower($name)), 'objektart', true);
                    }
                }
            }
        }

        // Contact Person Data
        if (isset($xml->kontaktperson)) {
            $k = $xml->kontaktperson;
            update_post_meta($post_id, 'kontaktperson_vorname', (string)$k->vorname);
            update_post_meta($post_id, 'kontaktperson_name', (string)$k->name);
            update_post_meta($post_id, 'kontaktperson_firma', (string)$k->firma);

            $email = (string)$k->email_direkt;
            if (empty($email))
                $email = (string)$k->email_zentrale;
            update_post_meta($post_id, 'kontaktperson_email', $email);

            $tel = (string)$k->tel_durchw;
            if (empty($tel))
                $tel = (string)$k->tel_zentrale;
            update_post_meta($post_id, 'kontaktperson_tel', $tel);
        }
    }

    /**
     * Process Attachments (Images).
     *
     * @param int              $post_id
     * @param SimpleXMLElement $xml
     * @param string           $base_path
     */
    private function process_attachments($post_id, $xml, $base_path)
    {
        if (!isset($xml->anhaenge) || !isset($xml->anhaenge->anhang)) {
            return;
        }

        $menu_order = 0;
        foreach ($xml->anhaenge->anhang as $anhang) {
            $file_name = (string)$anhang->daten->pfad;
            $group = (string)$anhang->attributes()->gruppe; // TITELBILD, BILD, GRUNDRISS
            $title = (string)$anhang->anhangtitel;

            if (empty($file_name)) {
                continue;
            }

            $att_id = $this->upload_image($file_name, $base_path, $post_id);

            if (!is_wp_error($att_id) && $att_id) {
                // Save OpenImmo specific meta
                update_post_meta($att_id, '_openimmo_gruppe', $group);
                update_post_meta($att_id, '_openimmo_titel', $title);

                // Update Attachment post for title/alt/caption/order
                $att_update = array(
                    'ID' => $att_id,
                    'post_title' => $title ? $title : basename($file_name),
                    'post_excerpt' => $title, // Caption
                    'menu_order' => $menu_order
                );
                wp_update_post($att_update);

                // Update Alt Text
                if ($title) {
                    update_post_meta($att_id, '_wp_attachment_image_alt', $title);
                }

                // Set Featured Image (TITELBILD takes precedence, otherwise first found)
                if ($group === 'TITELBILD') {
                    set_post_thumbnail($post_id, $att_id);
                }
                elseif (!has_post_thumbnail($post_id) && $group !== 'GRUNDRISS') {
                    set_post_thumbnail($post_id, $att_id);
                }
            }
            $menu_order++;
        }
    }

    /**
     * Process Contact Person Image.
     */
    private function process_contact_image($post_id, $xml, $base_path)
    {
        if (!isset($xml->kontaktperson) || !isset($xml->kontaktperson->foto)) {
            return;
        }

        $foto_node = $xml->kontaktperson->foto;
        $file_name = (string)$foto_node->daten->pfad;

        if (empty($file_name)) {
            return;
        }

        $att_id = $this->upload_image($file_name, $base_path, $post_id);

        if (!is_wp_error($att_id) && $att_id) {
            $url = wp_get_attachment_url($att_id);
            update_post_meta($post_id, 'kontaktperson_bild_url', $url);
            update_post_meta($post_id, 'kontaktperson_bild_id', $att_id);
        }
    }

    /**
     * Helper to upload image.
     */
    private function upload_image($file_name, $base_path, $post_id)
    {
        // Check if already imported
        $existing_att = new \WP_Query(array(
            'post_type' => 'attachment',
            'post_parent' => $post_id,
            'post_status' => 'inherit', // Attachments use 'inherit' status
            'meta_key' => '_openimmo_filename',
            'meta_value' => $file_name,
            'fields' => 'ids',
        ));

        if ($existing_att->have_posts()) {
            return $existing_att->posts[0];
        }

        $full_path = trailingslashit($base_path) . $file_name;

        if (!file_exists($full_path)) {
            return false;
        }

        $file_array = array(
            'name' => basename($full_path),
            'tmp_name' => $full_path,
        );

        $tmp_file = sys_get_temp_dir() . '/' . basename($full_path);
        copy($full_path, $tmp_file);
        $file_array['tmp_name'] = $tmp_file;

        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $att_id = media_handle_sideload($file_array, $post_id);

        if (!is_wp_error($att_id)) {
            update_post_meta($att_id, '_openimmo_filename', $file_name);
        }

        return $att_id;
    }

    /**
     * Find existing property by OpenImmo ID.
     *
     * @param string $openimmo_id
     * @return int|false Post ID or false.
     */
    private function get_property_by_openimmo_id($openimmo_id)
    {
        $query = new \WP_Query(array(
            'post_type' => 'immobilie',
            'meta_key' => 'openimmo_id',
            'meta_value' => $openimmo_id,
            'fields' => 'ids',
            'posts_per_page' => 1,
            'post_status' => 'any',
        ));

        if ($query->have_posts()) {
            return $query->posts[0];
        }

        return false;
    }

    /**
     * AJAX: Prepare Import (Step 1)
     * Locates files, extracts ZIPs, returns file list and counts.
     */
    public function ajax_prepare_import()
    {
        try {
            if (!current_user_can('manage_options'))
                wp_send_json_error('Keine Berechtigung');
            @set_time_limit(600);

            // 1. Locate XML Path
            $options = get_option('dbw_immo_suite_settings');
            $xml_path = isset($options['xml_path']) ? $options['xml_path'] : '';
            if (!empty($xml_path) && is_dir($xml_path)) {
                $xml_path = trailingslashit($xml_path);
            }
            elseif (!empty($xml_path) && is_dir(ABSPATH . ltrim($xml_path, '/'))) {
                $xml_path = trailingslashit(ABSPATH . ltrim($xml_path, '/'));
            }
            elseif (empty($xml_path)) {
                $upload_dir = wp_upload_dir();
                $xml_path = $upload_dir['basedir'] . '/openimmo/';
            }
            else {
                wp_send_json_error('Pfad nicht gefunden.');
            }

            if (!is_dir($xml_path))
                wp_send_json_error('Verzeichnis existiert nicht: ' . $xml_path);

            // 2. Extract ZIPs
            $zips = glob($xml_path . '*.zip');
            if (!empty($zips)) {
                foreach ($zips as $zip_file) {
                    $this->extract_zip($zip_file, $xml_path);
                }
            }

            // 3. Find XMLs
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($xml_path));
            $xml_files_data = array();

            foreach ($files as $file) {
                if ($file->isFile() && strtolower($file->getExtension()) === 'xml') {
                    // Count properties
                    libxml_use_internal_errors(true);
                    $xml = @simplexml_load_file($file->getPathname());
                    if ($xml && isset($xml->anbieter->immobilie)) {
                        $count = count($xml->anbieter->immobilie);
                        if ($count > 0) {
                            $xml_files_data[] = array(
                                'file' => $file->getPathname(),
                                'count' => $count
                            );
                        }
                    }
                }
            }

            if (empty($xml_files_data))
                wp_send_json_error('Keine gültigen OpenImmo XML-Dateien gefunden in: ' . $xml_path);

            wp_send_json_success(array(
                'files' => $xml_files_data,
                'message' => 'Dateien analysiert. Starte Prozess...'
            ));

        }
        catch (\Throwable $e) {
            wp_send_json_error('Fehler bei Vorbereitung: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Process Single Batch (Step 2)
     */
    public function ajax_process_batch()
    {
        try {
            if (!current_user_can('manage_options'))
                wp_send_json_error('Keine Berechtigung');
            @set_time_limit(300); // 5 min per batch
            @ini_set('memory_limit', '2048M');

            $file = isset($_POST['file']) ? stripslashes($_POST['file']) : '';
            $index = isset($_POST['index']) ? intval($_POST['index']) : 0;

            if (!file_exists($file))
                wp_send_json_error('Datei nicht gefunden: ' . $file);

            $this->current_xml_file = $file; // IMPORTANT for images

            libxml_use_internal_errors(true);
            $xml = simplexml_load_file($file);
            if (!$xml || !isset($xml->anbieter->immobilie[$index])) {
                wp_send_json_error('Immobilie nicht gefunden bei Index ' . $index);
            }

            $node = $xml->anbieter->immobilie[$index];
            $stats = array('created' => 0, 'updated' => 0, 'errors' => 0);

            $this->import_property($node, $stats);

            if ($stats['errors'] > 0) {
                wp_send_json_error('Fehler beim Import der Immobilie Index ' . $index);
            }

            wp_send_json_success(array('message' => 'Immobilie ' . ($index + 1) . ' importiert.'));

        }
        catch (\Throwable $e) {
            $this->log_debug("Batch Error ($file #$index): " . $e->getMessage());
            wp_send_json_error('Batch Error: ' . $e->getMessage());
        }
    }

    /**
     * AJAX Handler for triggering import.
     */
    public function ajax_run_import()
    {
        try {
            // Verify Nonce (TODO: Add nonce check in JS and here)
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Keine Berechtigung');
            }

            $result = $this->run_import();

            if ($result['success']) {
                wp_send_json_success($result['message']);
            }
            else {
                wp_send_json_error($result['message']);
            }
        }
        catch (\Throwable $e) {
            wp_send_json_error('AJAX CRITICAL: ' . $e->getMessage());
        }
    }

    private function log_debug($msg)
    {
        // Log to wp-content root for reliability
        $log_file = WP_CONTENT_DIR . '/openimmo_import.log';
        $entry = date('Y-m-d H:i:s') . ' - ' . $msg . PHP_EOL;
        // Simple file append
        @file_put_contents($log_file, $entry, FILE_APPEND);
    }
}