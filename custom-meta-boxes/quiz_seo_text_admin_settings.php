<?php

function wp_quiz_plugin_dynamic_strings_settings_init() {
    // Register dynamic strings settings
    register_setting('wp_quiz_plugin_strings_text_settings', 'wp_quiz_plugin_meta_box_title');
    register_setting('wp_quiz_plugin_strings_text_settings', 'wp_quiz_plugin_admin_only_message');
    register_setting('wp_quiz_plugin_strings_text_settings', 'wp_quiz_plugin_disabled_message');
    
    // Add a new section for dynamic strings
    add_settings_section(
        'wp_quiz_plugin_dynamic_strings_section',
        __('Customize Meta Box and Messages','wp-quiz-plugin'),
        null,
        'wp_quiz_plugin_strings_text'
    );

    // Add settings fields for dynamic strings
    add_settings_field('wp_quiz_plugin_meta_box_title', __('Meta Box Title','wp-quiz-plugin'), 'wp_quiz_plugin_meta_box_title_callback', 'wp_quiz_plugin_strings_text', 'wp_quiz_plugin_dynamic_strings_section');
    add_settings_field('wp_quiz_plugin_admin_only_message', __('Admin Only Message','wp-quiz-plugin'), 'wp_quiz_plugin_admin_only_message_callback', 'wp_quiz_plugin_strings_text', 'wp_quiz_plugin_dynamic_strings_section');
    add_settings_field('wp_quiz_plugin_disabled_message', __('Disabled Field Message','wp-quiz-plugin'), 'wp_quiz_plugin_disabled_message_callback', 'wp_quiz_plugin_strings_text', 'wp_quiz_plugin_dynamic_strings_section');
}
add_action('admin_init', 'wp_quiz_plugin_dynamic_strings_settings_init');

// Callback function for Meta Box Title
function wp_quiz_plugin_meta_box_title_callback() {
    $meta_box_title = get_option('wp_quiz_plugin_meta_box_title', __('SEO Text (Admin Only)','wp-quiz-plugin'));
    echo '<input type="text" id="wp_quiz_plugin_meta_box_title" name="wp_quiz_plugin_meta_box_title" value="' . _e(esc_attr($meta_box_title),'wp-quiz-plugin') . '" class="regular-text">';
}

// Callback function for Admin Only Message
function wp_quiz_plugin_admin_only_message_callback() {
    $admin_only_message = get_option('wp_quiz_plugin_admin_only_message', __('Only administrators can edit this SEO text.','wp-quiz-plugin'));
    echo '<input type="text" id="wp_quiz_plugin_admin_only_message" name="wp_quiz_plugin_admin_only_message" value="' . esc_attr($admin_only_message) . '" class="regular-text">';
}

// Callback function for Disabled Field Message
function wp_quiz_plugin_disabled_message_callback() {
    $disabled_message = get_option('wp_quiz_plugin_disabled_message', __('Only administrators can edit this SEO text.','wp-quiz-plugin'));
    echo '<input type="text" id="wp_quiz_plugin_disabled_message" name="wp_quiz_plugin_disabled_message" value="' . esc_attr($disabled_message) . '" class="regular-text">';
}
