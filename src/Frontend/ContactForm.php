<?php

namespace DBW\ImmoSuite\Frontend;

/**
 * Simple contact form for property detail pages.
 * Sends inquiry via wp_mail to the property's contact person or site admin.
 */
class ContactForm
{

    public function init()
    {
        add_action('wp_ajax_dbw_immo_contact', array($this, 'handle_submission'));
        add_action('wp_ajax_nopriv_dbw_immo_contact', array($this, 'handle_submission'));
    }

    /**
     * Handle AJAX form submission.
     */
    public function handle_submission()
    {
        check_ajax_referer('dbw_immo_contact_nonce', 'nonce');

        $post_id = intval($_POST['property_id'] ?? 0);
        $name    = sanitize_text_field($_POST['name'] ?? '');
        $email   = sanitize_email($_POST['email'] ?? '');
        $phone   = sanitize_text_field($_POST['phone'] ?? '');
        $message = sanitize_textarea_field($_POST['message'] ?? '');

        if (!$post_id || !$name || !$email || !$message) {
            wp_send_json_error(__('Bitte alle Pflichtfelder ausfuellen.', 'dbw-immo-suite'));
        }

        if (!is_email($email)) {
            wp_send_json_error(__('Bitte eine gueltige E-Mail-Adresse eingeben.', 'dbw-immo-suite'));
        }

        // Determine recipient
        $contact_email = get_post_meta($post_id, 'kontaktperson_email', true);
        $to = is_email($contact_email) ? $contact_email : get_option('admin_email');

        $property_title = get_the_title($post_id);
        $property_url = get_permalink($post_id);

        $subject = sprintf(
            __('Anfrage: %s', 'dbw-immo-suite'),
            $property_title
        );

        $body = sprintf(
            "Neue Anfrage über die Website:\n\n" .
            "Immobilie: %s\n" .
            "Link: %s\n\n" .
            "Name: %s\n" .
            "E-Mail: %s\n" .
            "Telefon: %s\n\n" .
            "Nachricht:\n%s\n",
            $property_title,
            $property_url,
            $name,
            $email,
            $phone ?: '-',
            $message
        );

        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'Reply-To: ' . $name . ' <' . $email . '>',
        );

        $sent = wp_mail($to, $subject, $body, $headers);

        if ($sent) {
            wp_send_json_success(__('Ihre Anfrage wurde erfolgreich versendet. Wir melden uns bei Ihnen.', 'dbw-immo-suite'));
        } else {
            wp_send_json_error(__('Beim Versand ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut.', 'dbw-immo-suite'));
        }
    }

    /**
     * Render the contact form HTML.
     *
     * @param int $post_id Property post ID.
     */
    public static function render($post_id)
    {
        $nonce = wp_create_nonce('dbw_immo_contact_nonce');
        $property_title = get_the_title($post_id);
        ?>
        <div class="dbw-contact-form-wrapper" id="dbw-contact-form">
            <h4 style="margin-top: 1.5rem; margin-bottom: 1rem;"><?php _e('Anfrage senden', 'dbw-immo-suite'); ?></h4>

            <form id="dbwContactForm" style="display: flex; flex-direction: column; gap: 12px;">
                <input type="hidden" name="property_id" value="<?php echo esc_attr($post_id); ?>">
                <input type="hidden" name="nonce" value="<?php echo esc_attr($nonce); ?>">

                <input type="text" name="name" placeholder="<?php esc_attr_e('Ihr Name *', 'dbw-immo-suite'); ?>" required
                    style="padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem;">

                <input type="email" name="email" placeholder="<?php esc_attr_e('Ihre E-Mail *', 'dbw-immo-suite'); ?>" required
                    style="padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem;">

                <input type="tel" name="phone" placeholder="<?php esc_attr_e('Telefon (optional)', 'dbw-immo-suite'); ?>"
                    style="padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem;">

                <textarea name="message" rows="4" required
                    placeholder="<?php echo esc_attr(sprintf(__('Ich interessiere mich fuer "%s" und bitte um weitere Informationen.', 'dbw-immo-suite'), $property_title)); ?>"
                    style="padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem; resize: vertical;"></textarea>

                <button type="submit" id="dbwContactSubmit"
                    style="background: var(--dbw-accent, #3498db); color: #fff; border: none; padding: 12px; border-radius: 6px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                    <?php _e('Anfrage absenden', 'dbw-immo-suite'); ?>
                </button>

                <div id="dbwContactStatus" style="display: none; padding: 10px; border-radius: 6px; font-size: 0.9rem; text-align: center;"></div>
            </form>
        </div>

        <script>
        (function() {
            var form = document.getElementById('dbwContactForm');
            if (!form) return;
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                var btn = document.getElementById('dbwContactSubmit');
                var status = document.getElementById('dbwContactStatus');
                btn.disabled = true;
                btn.textContent = '<?php echo esc_js(__('Wird gesendet...', 'dbw-immo-suite')); ?>';
                status.style.display = 'none';

                var data = new FormData(form);
                data.append('action', 'dbw_immo_contact');

                fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {
                    method: 'POST',
                    body: data
                })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    status.style.display = 'block';
                    if (res.success) {
                        status.style.background = '#d4edda';
                        status.style.color = '#155724';
                        status.textContent = res.data;
                        form.reset();
                    } else {
                        status.style.background = '#f8d7da';
                        status.style.color = '#721c24';
                        status.textContent = res.data;
                    }
                    btn.disabled = false;
                    btn.textContent = '<?php echo esc_js(__('Anfrage absenden', 'dbw-immo-suite')); ?>';
                })
                .catch(function() {
                    status.style.display = 'block';
                    status.style.background = '#f8d7da';
                    status.style.color = '#721c24';
                    status.textContent = '<?php echo esc_js(__('Netzwerkfehler. Bitte versuchen Sie es erneut.', 'dbw-immo-suite')); ?>';
                    btn.disabled = false;
                    btn.textContent = '<?php echo esc_js(__('Anfrage absenden', 'dbw-immo-suite')); ?>';
                });
            });
        })();
        </script>
        <?php
    }
}
