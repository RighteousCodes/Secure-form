<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Secure_Contact_Form {

    public function __construct() {
        add_action('wp_footer', array($this, 'display_form'));
        add_action('init', array($this, 'handle_form_submission'));
    }

    // 1️⃣ Display the form in the footer
    public function display_form() {
        ?>
        <form method="post" style="max-width:400px;margin:20px auto;text-align:center;">
            <h3>Contact Us</h3>
            <input type="text" name="scf_name" placeholder="Your Name" required><br><br>
            <input type="email" name="scf_email" placeholder="Your Email" required><br><br>
            <textarea name="scf_message" placeholder="Your Message" required></textarea><br><br>
            <?php wp_nonce_field('scf_submit_form', 'scf_nonce'); ?>
            <input type="submit" name="scf_submit" value="Send Message">
        </form>
        <?php
    }

    // 2️⃣ Handle the form submission securely
    public function handle_form_submission() {
        if ( isset($_POST['scf_submit']) && isset($_POST['scf_nonce']) ) {
            if ( ! wp_verify_nonce($_POST['scf_nonce'], 'scf_submit_form') ) {
                wp_die('Security check failed!');
            }

            $name    = sanitize_text_field($_POST['scf_name']);
            $email   = sanitize_email($_POST['scf_email']);
            $message = sanitize_textarea_field($_POST['scf_message']);

            // Save message to database (you can use wp_mail or custom table later)
            $to = get_option('admin_email');
            $subject = "New Contact Message from $name";
            $body = "Name: $name\nEmail: $email\n\nMessage:\n$message";

            wp_mail($to, $subject, $body);

            add_action('wp_footer', function() {
                echo '<p style="text-align:center;color:blue;">🥳 Message sent successfully!</p>';
            });
        }
    }
}
