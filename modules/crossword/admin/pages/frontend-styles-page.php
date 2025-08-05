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
    register_setting('kw_crossword_fe_settings', 'kw_fe_download_button_downloading_text', ['default' => __('Downloading...', 'wp-quiz-plugin')]);
    register_setting('kw_crossword_fe_settings', 'kw_fe_download_button_bg_color', ['default' => '#00796b']);
    register_setting('kw_crossword_fe_settings', 'kw_fe_download_button_text_color', ['default' => '#ffffff']);
    register_setting('kw_crossword_fe_settings', 'kw_fe_download_button_font_size', ['default' => '16px']);
    register_setting('kw_crossword_fe_settings', 'kw_fe_download_button_font_family', ['default' => 'Arial, sans-serif']);

    

    // Check Crossword Button
    register_setting('kw_crossword_fe_settings', 'kw_fe_check_crossword_button_text', ['default' => __('Check Crossword', 'wp-quiz-plugin')]);
    register_setting('kw_crossword_fe_settings', 'kw_fe_check_crossword_button_bg_color', ['default' => '#00796b']);
    register_setting('kw_crossword_fe_settings', 'kw_fe_check_crossword_button_text_color', ['default' => '#CE2525']);
    register_setting('kw_crossword_fe_settings', 'kw_fe_check_crossword_button_font_size', ['default' => '16px']);
    register_setting('kw_crossword_fe_settings', 'kw_fe_check_crossword_button_font_family', ['default' => 'Arial, sans-serif']);

    // Enable Live Word Check Button
    register_setting('kw_crossword_fe_settings', 'kw_fe_enable_live_word_check_button_text', ['default' => __('Enable Live Word Check', 'wp-quiz-plugin')]);
    register_setting('kw_crossword_fe_settings', 'kw_fe_enable_live_word_check_button_bg_color', ['default' => '#ffffff']);
    register_setting('kw_crossword_fe_settings', 'kw_fe_enable_live_word_check_button_enabled_color', ['default' => '#00796b']);
    register_setting('kw_crossword_fe_settings', 'kw_fe_enable_live_word_check_button_text_color', ['default' => '#000000']);
    register_setting('kw_crossword_fe_settings', 'kw_fe_enable_live_word_check_button_font_size', ['default' => '16px']);
    register_setting('kw_crossword_fe_settings', 'kw_fe_enable_live_word_check_button_font_family', ['default' => 'Noto Sans, sans-serif']);    

    // Filled Cell Background
    register_setting('kw_crossword_fe_settings', 'kw_fe_filled_cell_bg_color', ['default' => '#e1f5fe']);

    // Corrected Cell Background
    register_setting('kw_crossword_fe_settings', 'kw_fe_corrected_cell_bg_color', ['default' => '#d4edda']);

    // Wrong Cell Background
    register_setting('kw_crossword_fe_settings', 'kw_fe_wrong_cell_bg_color', ['default' => '#d66868']);

    // Highlighted Cell Background
    register_setting('kw_crossword_fe_settings', 'kw_crossword_highlight_cell_color', ['default' => 'yellow']);

    // Cell Font Color
    register_setting('kw_crossword_fe_settings', 'kw_crossword_cell_font_color', ['default' => 'black']);

    // Cell Clue Font Color
    register_setting('kw_crossword_fe_settings', 'kw_crossword_cell_clue_font_color', ['default' => 'black']);
    // Cell Border Color
    register_setting('kw_crossword_fe_settings', 'kw_crossword_cell_border_color', ['default' => 'lightgrey']);



    // Clues Settings
    register_setting('kw_crossword_fe_settings', 'kw_fe_clue_title_font_color', ['default' => '#000000']); // Default black
    register_setting('kw_crossword_fe_settings', 'kw_fe_clue_title_font_size', ['default' => '25']); // Default font size
    register_setting('kw_crossword_fe_settings', 'kw_fe_clue_title_font_family', ['default' => 'Arial']); // Default font family
    register_setting('kw_crossword_fe_settings', 'kw_fe_body_text_font_color', ['default' => 'rgb(85, 85, 85)']); // Default gray
    register_setting('kw_crossword_fe_settings', 'kw_fe_body_text_font_size', ['default' => '16']); // Default font size
    register_setting('kw_crossword_fe_settings', 'kw_fe_body_text_font_family', ['default' => 'Arial']); // Default font family

    // Clue Image Settings
    register_setting('kw_crossword_fe_settings', 'kw_fe_clue_image_height', ['default' => '100']); // Default height
    register_setting('kw_crossword_fe_settings', 'kw_fe_clue_image_width', ['default' => '100']); // Default width


     // Success Popup Settings  
     register_setting('kw_crossword_fe_settings', 'kw_crossword_success_popup_title', ['default' => __('Success!', 'wp-quiz-plugin')]);
     register_setting('kw_crossword_fe_settings', 'kw_crossword_success_popup_body_text', ['default' => __('You have successfully completed the crossword!', 'wp-quiz-plugin')]);
     register_setting('kw_crossword_fe_settings', 'kw_crossword_success_popup_button_text', ['default' => __('Close', 'wp-quiz-plugin')]);
     register_setting('kw_crossword_fe_settings', 'kw_crossword_success_popup_button_color', ['default' => '#00796b']);
     register_setting('kw_crossword_fe_settings', 'kw_crossword_success_popup_button_text_color', ['default' => '#fffff']);
 
     // Failure Popup Settings
     register_setting('kw_crossword_fe_settings', 'kw_crossword_failure_popup_title', ['default' => __('Are you Sure ?', 'wp-quiz-plugin')]);
     register_setting('kw_crossword_fe_settings', 'kw_crossword_failure_popup_body_text', ['default' => __('Not all words are typed !', 'wp-quiz-plugin')]);
     register_setting('kw_crossword_fe_settings', 'kw_crossword_failure_popup_button_text', ['default' => __('Retry', 'wp-quiz-plugin')]);
 
     // Add a settings section for Success Popup
     add_settings_section(
         'kw_success_popup_settings_section',
         __('Success Popup Settings', 'wp-quiz-plugin'),
         'crossword_render_success_popup_section',
         'kw-crossword-frontend-styles-page'
     );
 
     // Add a settings section for Failure Popup
     add_settings_section(
         'kw_failure_popup_settings_section',
         __('Failure Popup Settings', 'wp-quiz-plugin'),
         'crossword_render_failure_popup_section',
         'kw-crossword-frontend-styles-page'
     );

    // Add a settings section for Frontend Styles
    add_settings_section(
        'kw_crossword_fe_settings_section',
        __('Frontend Styles', 'wp-quiz-plugin'),
        'crossword_render_frontend_styles_section',
        'kw-crossword-frontend-styles-page'
    );

    add_settings_section(
        'kw_fe_clues_settings_section',
        null,
        'crossword_render_clues_settings_section',
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
 * Render the Clues Settings Section
 */
function crossword_render_clues_settings_section() {
    include plugin_dir_path(__FILE__) . '../templates/sections/fe/fe-clue-settings-section.php';
}


/**
 * Render Success Popup Section
 */
function crossword_render_success_popup_section() {
    include plugin_dir_path(__FILE__) . '../templates/sections/fe/success-popup-settings-section.php';
}

/**
 * Render Failure Popup Section
 */
function crossword_render_failure_popup_section() {
    include plugin_dir_path(__FILE__) . '../templates/sections/fe/failure-popup-settings-section.php';
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
