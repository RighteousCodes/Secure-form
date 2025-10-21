<?php
/*
Plugin Name: Secure Contact Form
Description: A simple contact form built with OOP and WordPress security.
Version: 1.0
Author: Righteous francis
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Prevent direct access
}

require_once plugin_dir_path(__FILE__) . 'includes/class-secure-contact-form.php';

function run_secure_contact_form() {
    $plugin = new Secure_Contact_Form();
}
add_action('plugins_loaded', 'run_secure_contact_form');
