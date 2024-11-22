<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include individual settings pages
require_once plugin_dir_path(__FILE__) . 'pages/general-settings.php';
// Add more settings pages here
// require_once plugin_dir_path(__FILE__) . 'pages/advanced-settings.php';

/**
 * Register all settings pages.
 */
function crossword_register_all_settings_pages() {
    if (function_exists('crossword_register_general_settings_page')) {
        crossword_register_general_settings_page();
    }
    // Register other pages
}
add_action('admin_menu', 'crossword_register_all_settings_pages');

// Enqueue admin styles and scripts
function crossword_admin_enqueue_assets($hook) {
    // Ensure scripts and styles are loaded only on plugin-related admin pages
    if (strpos($hook, 'crossword') === false) {
        return;
    }

    wp_enqueue_style(
        'kw-crossword-admin-styles',
        plugin_dir_url(__FILE__) . 'css/settings.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'kw-crossword-admin-scripts',
        plugin_dir_url(__FILE__) . 'js/settings.js',
        array('jquery'),
        '1.0.0',
        true
    );

    wp_localize_script('kw-crossword-admin-scripts', 'kwCrosswordAdmin', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('kw-crossword-nonce'),
    ));
}
add_action('admin_enqueue_scripts', 'crossword_admin_enqueue_assets');
