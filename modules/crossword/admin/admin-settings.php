<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Admin Settings File
 */

// Include the general settings file
require_once plugin_dir_path(__FILE__) . 'general-settings.php';

/**
 * Register all settings pages for the plugin.
 */
function crossword_register_all_settings_pages() {
    // General settings page
    if (function_exists('crossword_register_general_settings_page')) {
        crossword_register_general_settings_page();
    }
}

// Hook into WordPress admin menu action
add_action('admin_menu', 'crossword_register_all_settings_pages');
