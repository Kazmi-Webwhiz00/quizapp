<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Frontend Styles Settings
 */
function wordsearch_register_frontend_styles_settings() {
    // Register settings with default values

    // Filled Cell Background
    register_setting('kw_wordsearch_fe_settings', 'kw_grid_bg_color', ['default' => '#808080a1']);

    // Highlight Cell Background
    register_setting('kw_wordsearch_fe_settings', 'kw_highlight_cell_text_color', ['default' => '#fffff']);

    // Highlighted Cell Background
    register_setting('kw_wordsearch_fe_settings', 'kw_wordsearch_line_color', ['default' => 'rgba(0, 123, 255, 0.8)']);

    // Cell Font Color
    register_setting('kw_wordsearch_fe_settings', 'kw_wordsearch_cell_font_color', ['default' => 'black']);



    // Grid Settings
    register_setting('kw_wordsearch_fe_settings', 'kw_grid_text_font_color', ['default' => '#000']); // Default black
    register_setting('kw_wordsearch_fe_settings', 'kw_grid_text_font_family', ['default' => 'Roboto']); // Default font family

    // Wordsearch Image Settings
    // register_setting('kw_wordsearch_fe_settings', 'kw_word_search_image_height', ['default' => '100']); 
    // register_setting('kw_wordsearch_fe_settings', 'kw_word_search_image_width', ['default' => '100']); 


     // Success Popup Settings
     register_setting('kw_wordsearch_fe_settings', 'kw_wordsearch_success_popup_title', ['default' => __('Success!', 'wp-quiz-plugin')]);
     register_setting('kw_wordsearch_fe_settings', 'kw_wordsearch_success_popup_body_text', ['default' => __('You have successfully completed the wordsearch!', 'wp-quiz-plugin')]);
     register_setting('kw_wordsearch_fe_settings', 'kw_wordsearch_success_popup_button_text', ['default' => __('Close', 'wp-quiz-plugin')]);

     // Add a settings section for Success Popup
     add_settings_section(
         'kw_success_popup_settings_section',
         __('Success Popup Settings', 'wp-quiz-plugin'),
         'wordsearch_render_success_popup_section',
         'kw-wordsearch-frontend-styles-page'
     );

    // Add a settings section for Frontend Styles
    add_settings_section(
        'kw_wordsearch_fe_settings_section',
        __('Frontend Styles', 'wp-quiz-plugin'),
        'wordsearch_render_frontend_styles_section',
        'kw-wordsearch-frontend-styles-page'
    );

    add_settings_section(
        'kw_word_search_settings_section',
        null,
        'wordsearch_render_settings_section',
        'kw-wordsearch-frontend-styles-page'
    );
}

add_action('admin_init', 'wordsearch_register_frontend_styles_settings');

/**
 * Render Frontend Styles Section
 */
function wordsearch_render_frontend_styles_section() {
    include plugin_dir_path(__FILE__) . '../templates/sections/fe/fe-main-ui-settings-section.php';
}


/**
 * Render the Clues Settings Section
 */
function wordsearch_render_settings_section() {
    include plugin_dir_path(__FILE__) . '../templates/sections/fe/fe-clue-settings-section.php';
}


/**
 * Render Success Popup Section
 */
function wordsearch_render_success_popup_section() {
    include plugin_dir_path(__FILE__) . '../templates/sections/fe/success-popup-settings-section.php';
}

/**
 * Render Frontend Styles Page
 */
function ws_render_frontend_styles_page() {
    ?>
    <div class="kw-settings-wrap">
        <h1><?php esc_html_e('Frontend Styles', 'wp-quiz-plugin'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('kw_wordsearch_fe_settings');
            do_settings_sections('kw-wordsearch-frontend-styles-page');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}


?>
