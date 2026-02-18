jQuery(document).ready(function ($) {
    $('#dbw-immo-trigger-import').on('click', function (e) {
        e.preventDefault();

        var $btn = $(this);
        var $status = $('#dbw-immo-import-status');

        $btn.prop('disabled', true).text('Initialisiere...');
        $status.html('<span class="spinner is-active" style="float:none; margin:0 5px 0 0;"></span> Prüfe Dateien und entpacke ZIPs...');

        // Step 1: Prepare Import (Scan files, extract ZIPs)
        $.post(ajaxurl, {
            action: 'dbw_immo_prepare_import'
        }, function (response) {
            if (!response.success) {
                $status.html('<div class="notice notice-error inline"><p>' + response.data + '</p></div>');
                $btn.prop('disabled', false).text('Import starten');
                return;
            }

            var files = response.data.files;
            var flattenQueue = [];

            // Build a flat queue of individual tasks: [ {file: 'path', index: 0}, ... ]
            $.each(files, function (i, f) {
                for (var j = 0; j < f.count; j++) {
                    flattenQueue.push({
                        file: f.file,
                        index: j
                    });
                }
            });

            var total = flattenQueue.length;
            if (total === 0) {
                $status.html('<div class="notice notice-warning inline"><p>Keine Immobilien in den XML-Dateien gefunden.</p></div>');
                $btn.prop('disabled', false).text('Import starten');
                return;
            }

            $status.html('<div class="notice notice-info inline"><p>Analyse fertig. ' + total + ' Immobilien gefunden. Starte Batch-Import...</p></div>');

            // Step 2: Process Queue
            processBatchQueue(0, flattenQueue, $status, $btn);

        }).fail(function (xhr, status, error) {
            $status.html('<div class="notice notice-error inline"><p>Server Fehler bei Vorbereitung: ' + status + ' ' + error + '</p></div>');
            $btn.prop('disabled', false).text('Import starten');
        });
    });

    /**
     * Recursive function to process the import queue one by one.
     */
    function processBatchQueue(currentIdx, queue, $status, $btn) {
        if (currentIdx >= queue.length) {
            $status.html('<div class="notice notice-success inline"><p><strong>Import erfolgreich abgeschlossen!</strong> ' + queue.length + ' Immobilien verarbeitet.</p></div>');
            $btn.prop('disabled', false).text('Import starten');
            return;
        }

        var item = queue[currentIdx];
        var progress = Math.round(((currentIdx + 1) / queue.length) * 100);

        // Update Status
        $status.html('<span class="spinner is-active" style="float:none; margin:0 5px 0 0;"></span> Importiere ' + (currentIdx + 1) + ' von ' + queue.length + ' (' + progress + '%)');

        $.post(ajaxurl, {
            action: 'dbw_immo_process_batch',
            file: item.file,
            index: item.index
        }, function (response) {
            if (!response.success) {
                console.error("Batch Error at index " + currentIdx + ": " + response.data);
                // We continue despite errors to try importing the rest
            }
            // Next item
            processBatchQueue(currentIdx + 1, queue, $status, $btn);
        }).fail(function (xhr) {
            console.error("Server Failure at index " + currentIdx);
            // Retry or skip? For now, we skip to next
            processBatchQueue(currentIdx + 1, queue, $status, $btn);
        });
    }
});
