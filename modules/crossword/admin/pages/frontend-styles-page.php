<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Frontend Styles Settings
 */
function crossword_register_frontend_styles_settings() {
    // Register settings with default values

    // Restart Button
    register_setting('kw_crossword_fe_settings', 'kw_fe_restart_button_color', ['default' => '#00796b']);
    register_setting('kw_crossword_fe_settings', 'kw_fe_restart_button_font_size', ['default' => '16px']);
    register_setting('kw_crossword_fe_settings', 'kw_fe_restart_button_font_family', ['default' => 'Arial, sans-serif']);
    register_setting('kw_crossword_fe_settings', 'kw_fe_restart_button_text_color', ['default' => '#ffffff']);

    // Download Button
    register_setting('kw_crossword_fe_settings', 'kw_fe_download_button_text', ['default' => __('Download', 'wp-quiz-plugin')]);
    register_setting('kw_crossword_fe_settings', 'kw_fe_download_button_bg_color', ['default' => '#00796b']);
    register_setting('kw_crossword_fe_settings', 'kw_fe_download_button_text_color', ['default' => '#ffffff']);
    register_setting('kw_crossword_fe_settings', 'kw_fe_download_button_font_size', ['default' => '16px']);
    register_setting('kw_crossword_fe_settings', 'kw_fe_download_button_font_family', ['default' => 'Arial, sans-serif']);

    // Check Crossword Button
    register_setting('kw_crossword_fe_settings', 'kw_fe_check_crossword_button_text', ['default' => __('Check Crossword', 'wp-quiz-plugin')]);
    register_setting('kw_crossword_fe_settings', 'kw_fe_check_crossword_button_bg_color', ['default' => '#00796b']);
    register_setting('kw_crossword_fe_settings', 'kw_fe_check_crossword_button_text_color', ['default' => '#ffffff']);
    register_setting('kw_crossword_fe_settings', 'kw_fe_check_crossword_button_font_size', ['default' => '16px']);
    register_setting('kw_crossword_fe_settings', 'kw_fe_check_crossword_button_font_family', ['default' => 'Arial, sans-serif']);

    // Enable Live Word Check Button
    register_setting('kw_crossword_fe_settings', 'kw_fe_enable_live_word_check_button_text', ['default' => __('Enable Live Word Check', 'wp-quiz-plugin')]);
    register_setting('kw_crossword_fe_settings', 'kw_fe_enable_live_word_check_button_bg_color', ['default' => '#ffffff']);
    register_setting('kw_crossword_fe_settings', 'kw_fe_enable_live_word_check_button_enabled_color', ['default' => '#00796b']);
    register_setting('kw_crossword_fe_settings', 'kw_fe_enable_live_word_check_button_text_color', ['default' => '#000000']);
    register_setting('kw_crossword_fe_settings', 'kw_fe_enable_live_word_check_button_font_size', ['default' => '16px']);
    register_setting('kw_crossword_fe_settings', 'kw_fe_enable_live_word_check_button_font_family', ['default' => 'Arial, sans-serif']);

    // Filled Cell Background
    register_setting('kw_crossword_fe_settings', 'kw_fe_filled_cell_bg_color', ['default' => '#e1f5fe']);

    // Corrected Cell Background
    register_setting('kw_crossword_fe_settings', 'kw_fe_corrected_cell_bg_color', ['default' => '#d4edda']);

    // Wrong Cell Background
    register_setting('kw_crossword_fe_settings', 'kw_fe_wrong_cell_bg_color', ['default' => '#d66868']);

    // Add a settings section for Frontend Styles
    add_settings_section(
        'kw_crossword_fe_settings_section',
        __('Frontend Styles', 'wp-quiz-plugin'),
        'crossword_render_frontend_styles_section',
        'kw-crossword-frontend-styles-page'
    );
}

add_action('admin_init', 'crossword_register_frontend_styles_settings');

/**
 * Render Frontend Styles Section
 */
function crossword_render_frontend_styles_section() {
    include plugin_dir_path(__FILE__) . '../templates/sections/fe/fe-main-ui-settings-section.php';
}

/**
 * Render Frontend Styles Page
 */
function crossword_render_frontend_styles_page() {
    ?>
    <div class="kw-settings-wrap">
        <h1><?php esc_html_e('Frontend Styles', 'wp-quiz-plugin'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('kw_crossword_fe_settings');
            do_settings_sections('kw-crossword-frontend-styles-page');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
?>
