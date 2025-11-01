<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Prevent direct access
}

class Secure_Contact_Form {

    public function __construct() {
        add_shortcode('secure_contact_form', array($this, 'display_form'));
        add_action('init', array($this, 'handle_form_submission'));
        add_action('admin_menu', array($this, 'register_admin_page'));
    }

    // 1️⃣ Display the contact form
    public function display_form() {
        ob_start();

        // ✅ Show success message if redirected after submission
        if ( isset($_GET['scf_success']) && $_GET['scf_success'] === 'true' ) {
            echo '<p id="scf-success-message" style="text-align:center;color:green;font-weight:bold;">✅ Message sent successfully!</p>';
        }
        ?>

        <form method="post" style="max-width:400px;margin:20px auto;text-align:center;padding:20px;border:1px solid #ddd;border-radius:8px;background:#fff;">
            <h3>Contact Us</h3>
            <input type="text" name="scf_name" placeholder="Your Name" required style="width:100%;padding:8px;margin-bottom:10px;"><br>
            <input type="email" name="scf_email" placeholder="Your Email" required style="width:100%;padding:8px;margin-bottom:10px;"><br>
            <textarea name="scf_message" placeholder="Your Message" required style="width:100%;padding:8px;margin-bottom:10px;"></textarea><br>
            <?php wp_nonce_field('scf_submit_form', 'scf_nonce'); ?>
            <input type="submit" name="scf_submit" value="Send Message" style="background:#0073aa;color:#fff;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;">
        </form>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const msg = document.getElementById('scf-success-message');
            if (msg) {
                setTimeout(() => {
                    msg.style.transition = 'opacity 1s ease';
                    msg.style.opacity = '0';
                    setTimeout(() => msg.remove(), 1000); // remove element after fade
                }, 8000); // fade after 8 seconds
            }
        });
        </script>

        <?php
        return ob_get_clean();
    }

    // 2️⃣ Handle form submission securely
    public function handle_form_submission() {
        if ( isset($_POST['scf_submit']) && isset($_POST['scf_nonce']) ) {
            if ( ! wp_verify_nonce($_POST['scf_nonce'], 'scf_submit_form') ) {
                wp_die('Security check failed!');
            }

            $name    = sanitize_text_field($_POST['scf_name']);
            $email   = sanitize_email($_POST['scf_email']);
            $message = sanitize_textarea_field($_POST['scf_message']);

            global $wpdb;
            $table_name = $wpdb->prefix . 'secure_contact_form';

            $wpdb->insert(
                $table_name,
                array(
                    'name'    => $name,
                    'email'   => $email,
                    'message' => $message,
                )
            );

            // Send email notification to admin
            $to = get_option('admin_email');
            $subject = "New Contact Message from $name";
            $body = "Name: $name\nEmail: $email\n\nMessage:\n$message";

            wp_mail($to, $subject, $body);

            // ✅ Redirect back to same page with success flag
            wp_redirect(add_query_arg('scf_success', 'true', wp_get_referer()));
            exit;
        }
    }

    // 3️⃣ Add admin menu page
    public function register_admin_page() {
        add_menu_page(
            'Contact Messages',
            'Contact Messages',
            'manage_options',
            'scf-messages',
            array($this, 'display_messages'),
            'dashicons-email',
            20
        );
    }

    // 4️⃣ Display submitted messages in admin
    public function display_messages() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'secure_contact_form';
        $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY submitted_at DESC");

        echo '<div class="wrap"><h1>📬 Contact Form Submissions</h1>';
        echo '<table class="widefat fixed striped">';
        echo '<thead><tr><th>Name</th><th>Email</th><th>Message</th><th>Submitted</th></tr></thead><tbody>';

        if ($results) {
            foreach ($results as $row) {
                echo "<tr>
                        <td>{$row->name}</td>
                        <td>{$row->email}</td>
                        <td>{$row->message}</td>
                        <td>{$row->submitted_at}</td>
                      </tr>";
            }
        } else {
            echo '<tr><td colspan="4" style="text-align:center;">No messages yet 😅</td></tr>';
        }

        echo '</tbody></table></div>';
    }
}
