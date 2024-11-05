<?php
// Initialize Action Buttons Settings
function wp_quiz_plugin_action_buttons_settings_init() {
    // Register settings for each field
    // First Heading: Open in New Tab
    register_setting('wp_quiz_plugin_action_buttons_settings', 'wp_quiz_plugin_open_in_new_tab_button_label');
    register_setting('wp_quiz_plugin_action_buttons_settings', 'wp_quiz_plugin_open_in_new_tab_button_color');
    register_setting('wp_quiz_plugin_action_buttons_settings', 'wp_quiz_plugin_open_in_new_tab_font_size');

    // Second Heading: Copy URL
    register_setting('wp_quiz_plugin_action_buttons_settings', 'wp_quiz_plugin_copy_url_button_label');
    register_setting('wp_quiz_plugin_action_buttons_settings', 'wp_quiz_plugin_copy_url_button_color');
    register_setting('wp_quiz_plugin_action_buttons_settings', 'wp_quiz_plugin_copy_url_font_size');

    // Third Heading: Share Via Email
    register_setting('wp_quiz_plugin_action_buttons_settings', 'wp_quiz_plugin_share_email_button_label');
    register_setting('wp_quiz_plugin_action_buttons_settings', 'wp_quiz_plugin_share_email_button_color');
    register_setting('wp_quiz_plugin_action_buttons_settings', 'wp_quiz_plugin_share_email_font_size');
    register_setting('wp_quiz_plugin_action_buttons_settings', 'wp_quiz_plugin_share_email_subject');
    register_setting('wp_quiz_plugin_action_buttons_settings', 'wp_quiz_plugin_share_email_body');

    // Add sections
    add_settings_section('wp_quiz_plugin_action_buttons_open_new_tab', 'Open in New Tab', null, 'wp_quiz_plugin_action_buttons');
    add_settings_section('wp_quiz_plugin_action_buttons_copy_url', 'Copy URL', null, 'wp_quiz_plugin_action_buttons');
    add_settings_section('wp_quiz_plugin_action_buttons_share_email', 'Share Via Email', null, 'wp_quiz_plugin_action_buttons');

    // Add settings fields
    // Open in New Tab
    add_settings_field('wp_quiz_plugin_open_in_new_tab_button_label', 'Button Label', 'wp_quiz_plugin_text_callback', 'wp_quiz_plugin_action_buttons', 'wp_quiz_plugin_action_buttons_open_new_tab', ['option_name' => 'wp_quiz_plugin_open_in_new_tab_button_label']);
    add_settings_field('wp_quiz_plugin_open_in_new_tab_button_color', 'Button Color', 'wp_quiz_plugin_color_picker_callback', 'wp_quiz_plugin_action_buttons', 'wp_quiz_plugin_action_buttons_open_new_tab', ['option_name' => 'wp_quiz_plugin_open_in_new_tab_button_color']);
    add_settings_field('wp_quiz_plugin_open_in_new_tab_font_size', 'Font Size (px)', 'wp_quiz_plugin_number_callback', 'wp_quiz_plugin_action_buttons', 'wp_quiz_plugin_action_buttons_open_new_tab', ['option_name' => 'wp_quiz_plugin_open_in_new_tab_font_size']);
    
    // Copy URL
    add_settings_field('wp_quiz_plugin_copy_url_button_label', 'Button Label', 'wp_quiz_plugin_text_callback', 'wp_quiz_plugin_action_buttons', 'wp_quiz_plugin_action_buttons_copy_url', ['option_name' => 'wp_quiz_plugin_copy_url_button_label']);
    add_settings_field('wp_quiz_plugin_copy_url_button_color', 'Button Color', 'wp_quiz_plugin_color_picker_callback', 'wp_quiz_plugin_action_buttons', 'wp_quiz_plugin_action_buttons_copy_url', ['option_name' => 'wp_quiz_plugin_copy_url_button_color']);
    add_settings_field('wp_quiz_plugin_copy_url_font_size', 'Font Size (px)', 'wp_quiz_plugin_number_callback', 'wp_quiz_plugin_action_buttons', 'wp_quiz_plugin_action_buttons_copy_url', ['option_name' => 'wp_quiz_plugin_copy_url_font_size']);

    // Share Via Email
    add_settings_field('wp_quiz_plugin_share_email_button_label', 'Button Label', 'wp_quiz_plugin_text_callback', 'wp_quiz_plugin_action_buttons', 'wp_quiz_plugin_action_buttons_share_email', ['option_name' => 'wp_quiz_plugin_share_email_button_label']);
    add_settings_field('wp_quiz_plugin_share_email_button_color', 'Button Color', 'wp_quiz_plugin_color_picker_callback', 'wp_quiz_plugin_action_buttons', 'wp_quiz_plugin_action_buttons_share_email', ['option_name' => 'wp_quiz_plugin_share_email_button_color']);
    add_settings_field('wp_quiz_plugin_share_email_font_size', 'Font Size (px)', 'wp_quiz_plugin_number_callback', 'wp_quiz_plugin_action_buttons', 'wp_quiz_plugin_action_buttons_share_email', ['option_name' => 'wp_quiz_plugin_share_email_font_size']);
    add_settings_field('wp_quiz_plugin_share_email_subject', 'Email Subject', 'wp_quiz_plugin_text_callback', 'wp_quiz_plugin_action_buttons', 'wp_quiz_plugin_action_buttons_share_email', ['option_name' => 'wp_quiz_plugin_share_email_subject']);
    add_settings_field('wp_quiz_plugin_share_email_body', 'Email Body', 'wp_quiz_plugin_textarea_callback', 'wp_quiz_plugin_action_buttons', 'wp_quiz_plugin_action_buttons_share_email', ['option_name' => 'wp_quiz_plugin_share_email_body']);
}
add_action('admin_init', 'wp_quiz_plugin_action_buttons_settings_init');

// Callback functions for fields
// Text input
function wp_quiz_plugin_text_callback($args) {
    $option_name = $args['option_name'];
    $value = get_option($option_name, '');
    echo '<input type="text" id="' . esc_attr($option_name) . '" name="' . esc_attr($option_name) . '" value="' . esc_attr($value) . '" class="regular-text" />';
}

// Color picker
function wp_quiz_plugin_color_picker_callback($args) {
    $option_name = $args['option_name'];
    $value = get_option($option_name, '#000000');
    echo '<input type="color" id="' . esc_attr($option_name) . '" name="' . esc_attr($option_name) . '" value="' . esc_attr($value) . '" />';
}

// Number input
function wp_quiz_plugin_number_callback($args) {
    $option_name = $args['option_name'];
    $value = get_option($option_name, '14');
    echo '<input type="number" id="' . esc_attr($option_name) . '" name="' . esc_attr($option_name) . '" value="' . esc_attr($value) . '" style="width:60px;" /> px';
}

// Textarea input
function wp_quiz_plugin_textarea_callback($args) {
    $option_name = $args['option_name'];
    $value = get_option($option_name, '');
    echo '<textarea id="' . esc_attr($option_name) . '" name="' . esc_attr($option_name) . '" rows="4" cols="50">' . esc_textarea($value) . '</textarea>';
}
