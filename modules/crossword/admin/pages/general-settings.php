<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ==========================================================
 * Register the Settings Page
 * ==========================================================
 * This function registers the settings page under the custom 
 * post type menu.
 */
function crossword_register_general_settings_page() {
    add_submenu_page(
        'edit.php?post_type=crossword',
        __('General Settings', 'your-text-domain'), // Updated title for the page
        __('Settings', 'your-text-domain'),
        'manage_options',
        'crossword-general-settings',
        'crossword_render_general_settings_page'
    );
}

// Render the settings page
function crossword_render_general_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('General Settings', 'your-text-domain'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('crossword_general_settings');
            do_settings_sections('crossword-general-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * ==========================================================
 * Register the Settings, Sections, and Fields
 * ==========================================================
 * This function registers the settings, sections, and fields 
 * for the "General Settings" page.
 */
function crossword_register_general_settings() {
    // Register the setting
    register_setting(
        'crossword_general_settings', // Option group
        'crossword_custom_url_slug',  // Option name
        array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'crossword',
        )
    );

    // Register the section
    add_settings_section(
        'slugurl_section', // Updated Section ID
        __('Custom Slug Settings', 'your-text-domain'), // Section Title
        'crossword_render_slugurl_section', // Callback for rendering the section
        'crossword-general-settings'
    );

    // Register the field
    add_settings_field(
        'crossword_custom_url_slug',
        '', // No label here since it's rendered in the template
        '__return_false', // No separate callback for the field since it's rendered in the section
        'crossword-general-settings',
        'slugurl_section' // Updated Section ID
    );
}
add_action('admin_init', 'crossword_register_general_settings');

/**
 * ==========================================================
 * Render the Custom Slug Section
 * ==========================================================
 * This function includes the template for the "Custom Slug Settings" section.
 */
function crossword_render_slugurl_section() {
    include plugin_dir_path(__FILE__) . '../templates/sections/slugurl-section.php';
}
