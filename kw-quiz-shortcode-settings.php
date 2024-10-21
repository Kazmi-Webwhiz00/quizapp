<?php
if (!defined('ABSPATH')) exit;

function kw_quiz_shortcode_settings_init() {
    // Register the setting for the shortcode preset selection
    register_setting('kw_quiz_shortcode_settings', 'kw_quiz_shortcode_preset');
    register_setting('kw_quiz_shortcode_settings', 'kw_quiz_preset_back_next_button_gap');

    // Add a settings section for shortcode settings
    add_settings_section(
        'kw_quiz_shortcode_section',
        __('Shortcode Preset Settings', 'kw_quiz_plugin'),
        null,
        'kw_quiz_shortcode_styles'
    );

    // Add a dropdown field for selecting the preset
    add_settings_field(
        'kw_quiz_shortcode_preset',
        __('Select Shortcode Preset', 'kw_quiz_plugin'),
        'kw_quiz_shortcode_preset_callback',
        'kw_quiz_shortcode_styles',
        'kw_quiz_shortcode_section'
    );

    add_settings_field(
        'kw_quiz_preset_back_next_button_gap',
        __('Gap between Back And Next Button', 'kw_quiz_plugin'),
        'kw_quiz_button_gap_callback',
        'kw_quiz_shortcode_styles',
        'kw_quiz_shortcode_section'
    );
}
add_action('admin_init', 'kw_quiz_shortcode_settings_init');

function kw_quiz_button_gap_callback(){
    $existing_button_gap = get_option('kw_quiz_preset_back_next_button_gap', '10');
    ?>
    <div>
        <input type="number" id='kw_quiz_preset_back_next_button_gap' name='kw_quiz_preset_back_next_button_gap' value="<?php echo esc_attr($existing_button_gap); ?>" min="0" max="350"/>
        <span class="unit-label">px</span>
    </div>
    <?php
}
// Callback function to render the dropdown
function kw_quiz_shortcode_preset_callback() {
    $selected_preset = get_option('kw_quiz_shortcode_preset', 'preset1');
    ?>
    <select name="kw_quiz_shortcode_preset" id="kw_quiz_shortcode_preset">
        <option value="preset1" <?php selected($selected_preset, 'preset1'); ?>><?php esc_html_e('Preset 1', 'kw_quiz_plugin'); ?></option>
        <option value="preset2" <?php selected($selected_preset, 'preset2'); ?>><?php esc_html_e('Preset 2', 'kw_quiz_plugin'); ?></option>
    </select>
    <?php
}
