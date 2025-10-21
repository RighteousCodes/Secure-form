<?php
/*
Plugin Name: Secure Contact Form
Description: A simple contact form built with OOP and WordPress security.
Version: 1.1
Author: Righteous francis
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Prevent direct access
}

require_once plugin_dir_path(__FILE__) . 'includes/class-secure-contact-form.php';

function run_secure_contact_form() {
    $plugin = new Secure_Contact_Form();
}
register_activation_hook(__FILE__, 'scf_create_table');

function scf_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'secure_contact_form';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        email varchar(100) NOT NULL,
        message text NOT NULL,
        submitted_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

add_action('plugins_loaded', 'run_secure_contact_form');
