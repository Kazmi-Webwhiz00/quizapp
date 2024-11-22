<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ==========================================================
 * Register the Settings, Sections, and Fields
 * ==========================================================
 * This function registers the settings, sections, and fields 
 * for the "General Settings" page using the page slug.
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
        'slugurl_section', // Section ID
        __('Custom Slug Settings', 'wp-quiz-plugin'), // Section Title
        'crossword_render_slugurl_section', // Callback for rendering the section
        'kw-crossword-general-settings-page' // Page slug
    );
}
add_action('admin_init', 'crossword_register_general_settings');

/**
 * ==========================================================
 * Render the Settings Page
 * ==========================================================
 * This function renders the "General Settings" page for 
 * crossword settings.
 */
function crossword_render_general_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('General Settings', 'wp-quiz-plugin'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('crossword_general_settings'); // Option group
            do_settings_sections('kw-crossword-general-settings-page'); // Render sections
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * ==========================================================
 * Render the Slug Section
 * ==========================================================
 * This function includes the template for the "Custom Slug Settings" section.
 */
function crossword_render_slugurl_section() {
    include plugin_dir_path(__FILE__) . '../templates/sections/slugurl-section.php';
}
