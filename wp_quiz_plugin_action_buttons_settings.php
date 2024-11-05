<?php
// Initialize Action Buttons Settings with Reset to Default button
function wp_quiz_plugin_action_buttons_settings_init() {
    // Register settings with default values
    register_setting('wp_quiz_plugin_action_buttons_settings', 'wp_quiz_plugin_open_tab_button_label', [
        'default' => __('Open in New Tab', 'wp_quiz_plugin')
    ]);
    register_setting('wp_quiz_plugin_action_buttons_settings', 'wp_quiz_plugin_open_tab_button_color', [
        'default' => '#007BFF'
    ]);
    register_setting('wp_quiz_plugin_action_buttons_settings', 'wp_quiz_plugin_open_tab_button_font_size', [
        'default' => '16px'
    ]);

    // Second Heading: Copy URL
    register_setting('wp_quiz_plugin_action_buttons_settings', 'wp_quiz_plugin_copy_url_button_label', [
        'default' => __('Copy URL to Clipboard', 'wp_quiz_plugin')
    ]);
    register_setting('wp_quiz_plugin_action_buttons_settings', 'wp_quiz_plugin_copy_url_button_color', [
        'default' => '#007BFF'
    ]);
    register_setting('wp_quiz_plugin_action_buttons_settings', 'wp_quiz_plugin_copy_url_button_font_size', [
        'default' => '16px'
    ]);

    // Third Heading: Share Via Email
    register_setting('wp_quiz_plugin_action_buttons_settings', 'wp_quiz_plugin_share_email_button_label', [
        'default' => __('Share via Email', 'wp_quiz_plugin')
    ]);
    register_setting('wp_quiz_plugin_action_buttons_settings', 'wp_quiz_plugin_share_email_button_color', [
        'default' => '#007BFF'
    ]);
    register_setting('wp_quiz_plugin_action_buttons_settings', 'wp_quiz_plugin_share_email_button_font_size', [
        'default' => '16px'
    ]);
    register_setting('wp_quiz_plugin_action_buttons_settings', 'wp_quiz_plugin_share_email_subject', [
        'default' => __('New Quiz Assessment Available', 'wp_quiz_plugin')
    ]);
    register_setting('wp_quiz_plugin_action_buttons_settings', 'wp_quiz_plugin_share_email_body', [
        'default' => __('Hello,\n\nPlease attempt this quiz on time. Here is the quiz link:\n\n[URL]\n\nBest regards,', 'wp_quiz_plugin')
    ]);

    // Add sections and fields
    add_settings_section('wp_quiz_plugin_open_tab_section', 'Open in New Tab', null, 'wp_quiz_plugin_action_buttons');
    add_settings_section('wp_quiz_plugin_copy_url_section', 'Copy URL', null, 'wp_quiz_plugin_action_buttons');
    add_settings_section('wp_quiz_plugin_share_email_section', 'Share Via Email', null, 'wp_quiz_plugin_action_buttons');

    // Open in New Tab Fields
    add_settings_field('wp_quiz_plugin_open_tab_button_label_field', 'Button Label', 'wp_quiz_plugin_text_callback', 'wp_quiz_plugin_action_buttons', 'wp_quiz_plugin_open_tab_section', ['option_name' => 'wp_quiz_plugin_open_tab_button_label']);
    add_settings_field('wp_quiz_plugin_open_tab_button_color_field', 'Button Color', 'wp_quiz_plugin_color_picker_callback', 'wp_quiz_plugin_action_buttons', 'wp_quiz_plugin_open_tab_section', ['option_name' => 'wp_quiz_plugin_open_tab_button_color']);
    add_settings_field('wp_quiz_plugin_open_tab_font_size_field', 'Font Size (px)', 'wp_quiz_plugin_number_callback', 'wp_quiz_plugin_action_buttons', 'wp_quiz_plugin_open_tab_section', ['option_name' => 'wp_quiz_plugin_open_tab_button_font_size']);
    
    // Copy URL Fields
    add_settings_field('wp_quiz_plugin_copy_url_button_label_field', 'Button Label', 'wp_quiz_plugin_text_callback', 'wp_quiz_plugin_action_buttons', 'wp_quiz_plugin_copy_url_section', ['option_name' => 'wp_quiz_plugin_copy_url_button_label']);
    add_settings_field('wp_quiz_plugin_copy_url_button_color_field', 'Button Color', 'wp_quiz_plugin_color_picker_callback', 'wp_quiz_plugin_action_buttons', 'wp_quiz_plugin_copy_url_section', ['option_name' => 'wp_quiz_plugin_copy_url_button_color']);
    add_settings_field('wp_quiz_plugin_copy_url_font_size_field', 'Font Size (px)', 'wp_quiz_plugin_number_callback', 'wp_quiz_plugin_action_buttons', 'wp_quiz_plugin_copy_url_section', ['option_name' => 'wp_quiz_plugin_copy_url_button_font_size']);

    // Share Via Email Fields
    add_settings_field('wp_quiz_plugin_share_email_button_label_field', 'Button Label', 'wp_quiz_plugin_text_callback', 'wp_quiz_plugin_action_buttons', 'wp_quiz_plugin_share_email_section', ['option_name' => 'wp_quiz_plugin_share_email_button_label']);
    add_settings_field('wp_quiz_plugin_share_email_button_color_field', 'Button Color', 'wp_quiz_plugin_color_picker_callback', 'wp_quiz_plugin_action_buttons', 'wp_quiz_plugin_share_email_section', ['option_name' => 'wp_quiz_plugin_share_email_button_color']);
    add_settings_field('wp_quiz_plugin_share_email_font_size_field', 'Font Size (px)', 'wp_quiz_plugin_number_callback', 'wp_quiz_plugin_action_buttons', 'wp_quiz_plugin_share_email_section', ['option_name' => 'wp_quiz_plugin_share_email_button_font_size']);
    add_settings_field('wp_quiz_plugin_share_email_subject_field', 'Email Subject', 'wp_quiz_plugin_text_callback', 'wp_quiz_plugin_action_buttons', 'wp_quiz_plugin_share_email_section', ['option_name' => 'wp_quiz_plugin_share_email_subject']);
    add_settings_field('wp_quiz_plugin_share_email_body_field', 'Email Body', 'wp_quiz_plugin_textarea_callback', 'wp_quiz_plugin_action_buttons', 'wp_quiz_plugin_share_email_section', ['option_name' => 'wp_quiz_plugin_share_email_body']);

    // Add Reset to Default Button
    add_settings_field(
        'wp_quiz_plugin_reset_defaults_field',
        __('Reset to Default', 'wp_quiz_plugin'),
        'wp_quiz_plugin_reset_defaults_callback',
        'wp_quiz_plugin_action_buttons',
        'wp_quiz_plugin_share_email_section'
    );
}
add_action('admin_init', 'wp_quiz_plugin_action_buttons_settings_init');

// Callback functions for fields
// Text input callback
function wp_quiz_plugin_text_callback($args) {
    $option_name = $args['option_name'];
    $value = get_option($option_name, '');
    echo '<input type="text" id="' . esc_attr($option_name) . '" name="' . esc_attr($option_name) . '" value="' . esc_attr($value) . '" class="regular-text" />';
}

// Color picker callback
function wp_quiz_plugin_color_picker_callback($args) {
    $option_name = $args['option_name'];
    $value = get_option($option_name, '#000000');
    echo '<input type="color" id="' . esc_attr($option_name) . '" name="' . esc_attr($option_name) . '" value="' . esc_attr($value) . '" />';
}

// Number input callback
function wp_quiz_plugin_number_callback($args) {
    $option_name = $args['option_name'];
    $value = get_option($option_name, '16');
    echo '<input type="number" id="' . esc_attr($option_name) . '" name="' . esc_attr($option_name) . '" value="' . esc_attr($value) . '" style="width:60px;" /> px';
}

function wp_quiz_plugin_textarea_callback($args) {
    $option_name = $args['option_name'];
    $value = get_option($option_name, '');
    
    // Informational text for the user
    echo '<p style="margin-bottom: 8px; color: #555;">';
    echo __('Use [URL] to insert the active quiz URL in your email. For example:', 'wp_quiz_plugin') . '<br>';
    echo __('"Hello,\n\nPlease attempt this quiz on time: [URL]\n\nBest regards,"', 'wp_quiz_plugin');
    echo '</p>';
    
    // Textarea for the email body
    echo '<textarea id="' . esc_attr($option_name) . '" name="' . esc_attr($option_name) . '" rows="8" cols="60">' . esc_textarea($value) . '</textarea>';
}

// Callback function for Reset to Default button
function wp_quiz_plugin_reset_defaults_callback() {
    echo '<button type="button" class="button-secondary" id="wp_quiz_plugin_reset_defaults">' . __('Reset to Default', 'wp_quiz_plugin') . '</button>';
    ?>
    <script>
    jQuery(document).ready(function($) {
        $('#wp_quiz_plugin_reset_defaults').on('click', function() {
            if (confirm("<?php _e('Are you sure you want to reset to default settings?', 'wp_quiz_plugin'); ?>")) {
                var defaultValues = {
                    'wp_quiz_plugin_open_tab_button_label': '<?php echo __('Open in New Tab', 'wp_quiz_plugin'); ?>',
                    'wp_quiz_plugin_open_tab_button_color': '#007BFF',
                    'wp_quiz_plugin_open_tab_button_font_size': '16',
                    'wp_quiz_plugin_copy_url_button_label': '<?php echo __('Copy URL to Clipboard', 'wp_quiz_plugin'); ?>',
                    'wp_quiz_plugin_copy_url_button_color': '#007BFF',
                    'wp_quiz_plugin_copy_url_button_font_size': '16',
                    'wp_quiz_plugin_share_email_button_label': '<?php echo __('Share via Email', 'wp_quiz_plugin'); ?>',
                    'wp_quiz_plugin_share_email_button_color': '#007BFF',
                    'wp_quiz_plugin_share_email_button_font_size': '16',
                    'wp_quiz_plugin_share_email_subject': '<?php echo __('New Quiz Assessment Available', 'wp_quiz_plugin'); ?>',
                    'wp_quiz_plugin_share_email_body': '<?php echo __('Hello,\n\nPlease attempt this quiz on time. Here is the quiz link:\n\n[URL]\n\nBest regards,', 'wp_quiz_plugin'); ?>'
                };

                // Reset each field to its default value in the input fields only
                $.each(defaultValues, function(key, value) {
                    $('#' + key).val(value);
                });
            }
        });
    });
    </script>
    <?php
}
?>
