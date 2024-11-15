<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * General Settings
 */

// Register the General Settings Page
function crossword_register_general_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    add_submenu_page(
        'edit.php?post_type=crossword',             // Parent slug
        __('Crossword Settings', 'your-text-domain'), // Page title
        __('Settings', 'your-text-domain'),          // Menu title
        'manage_options',                            // Capability
        'crossword-settings',                        // Menu slug
        'crossword_render_general_settings_page'     // Callback function
    );
}

// Render the General Settings Page
function crossword_render_general_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Crossword Settings', 'your-text-domain'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('crossword_general_settings');
            do_settings_sections('crossword-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register General Settings
function crossword_register_general_settings() {
    // Register the setting for the URL slug
    register_setting(
        'crossword_general_settings', // Option group
        'crossword_custom_url_slug',  // Option name
        array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'crossword',
        )
    );

    // Add settings section
    add_settings_section(
        'crossword_general_section',                // Section ID
        __('General Settings', 'your-text-domain'), // Title
        '__return_false',                           // Callback (empty here)
        'crossword-settings'                        // Page slug
    );

    // Add the URL slug field
    add_settings_field(
        'crossword_custom_url_slug',                          // Field ID
        __('Custom URL Slug', 'your-text-domain'),            // Label
        'crossword_custom_url_slug_callback',                 // Callback to render the field
        'crossword-settings',                                 // Page slug
        'crossword_general_section'                          // Section ID
    );
}
add_action('admin_init', 'crossword_register_general_settings');

// Render the Custom URL Slug Field
function crossword_custom_url_slug_callback() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $url_slug = get_option('crossword_custom_url_slug', 'crossword');
    $permalink_settings_url = admin_url('options-permalink.php'); // URL to Permalink Settings page
    ?>

    <!-- Notice Box -->
    <div style="
        background-color: #cce5ff96;
        color: #2a2a2a;
        padding: 12px;
        border-left: 4px solid #3b82f6;
        border-radius: 4px;
        margin: 12px 0px;
        display: flex;
        align-items: start;
        max-width: 500px;
    ">
        <span style="font-size: 18px; font-weight: bold; color: #3b82f6; margin-right: 12px;">ⓘ</span>
        <div>
            <strong>Note:</strong> After changing the slug, please go to <strong><a href="<?php echo esc_url($permalink_settings_url); ?>" target="_blank">Settings > Permalinks</a></strong> and click "Save Changes" to update and activate the new URL structure.
        </div>
    </div>

    <input type="text" id="crossword_custom_url_slug" name="crossword_custom_url_slug" value="<?php echo esc_attr($url_slug); ?>" class="regular-text">
    <p class="description"><?php esc_html_e('Set a custom URL slug for crosswords. Example: "my-crosswords".', 'your-text-domain'); ?></p>
    
    <?php
}


// Flush rewrite rules securely
function crossword_check_and_flush_rewrite_rules($old_value, $new_value, $option) {
    if ('crossword_custom_url_slug' === $option && $old_value !== $new_value) {
        flush_rewrite_rules();
    }
}
add_action('update_option_crossword_custom_url_slug', 'crossword_check_and_flush_rewrite_rules', 10, 3);
