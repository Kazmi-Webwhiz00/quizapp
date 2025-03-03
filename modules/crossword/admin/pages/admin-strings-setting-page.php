<?php
// Ensure this file is loaded in the correct context
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Admin Strings Settings
 */

 function crossword_register_admin_strings_settings() {
    // Register individual settings with default values for labels
    register_setting('kw_crossword_admin_view_strings', 'kw_crossword_admin_add_word_button_label', [
        'default' => __('Add a Word', 'wp-quiz-plugin'),
    ]);
    register_setting('kw_crossword_admin_view_strings', 'kw_crossword_admin_clear_list_button_label', [
        'default' => __('Clear List', 'wp-quiz-plugin'),
    ]);
    register_setting('kw_crossword_admin_view_strings', 'kw_crossword_admin_shuffle_button_label', [
        'default' => __('Shuffle', 'wp-quiz-plugin'),
    ]);
    register_setting('kw_crossword_admin_view_strings', 'kw_crossword_admin_download_pdf_button_label', [
        'default' => __('Download as PDF', 'wp-quiz-plugin'),
    ]);
    register_setting('kw_crossword_admin_view_strings', 'kw_crossword_admin_download_key_button_label', [
        'default' => __('Download Key', 'wp-quiz-plugin'),
    ]);
    register_setting('kw_crossword_admin_view_strings', 'kw_crossword_admin_show_answers_checkbox_label', [
        'default' => __('Show Answers', 'wp-quiz-plugin'),
    ]);
    register_setting('kw_crossword_admin_view_strings', 'kw_crossword_admin_across_label', [
        'default' => __('Across', 'wp-quiz-plugin'),
    ]);
    register_setting('kw_crossword_admin_view_strings', 'kw_crossword_admin_down_label', [
        'default' => __('Down', 'wp-quiz-plugin'),
    ]);

    register_setting('kw_crossword_admin_view_strings', 'kw_crossword_admin_words_clue_container_label', [
        'default' => __('Words and Clues', 'wp-quiz-plugin'),
    ]);
    register_setting('kw_crossword_admin_view_strings', 'kw_crossword_admin_full_view_container_label', [
        'default' => __('Crossword Full View', 'wp-quiz-plugin'),
    ]);

    // Register individual settings with default values for background colors
    register_setting('kw_crossword_admin_view_strings', 'kw_crossword_admin_add_word_button_color', [
        'default' => '#0073aa', // Default color
    ]);
    register_setting('kw_crossword_admin_view_strings', 'kw_crossword_admin_clear_list_button_color', [
        'default' => '#0073aa', // Default color
    ]);
    register_setting('kw_crossword_admin_view_strings', 'kw_crossword_admin_shuffle_button_color', [
        'default' => '#00796B', // Default color
    ]);
    register_setting('kw_crossword_admin_view_strings', 'kw_crossword_admin_download_pdf_button_color', [
        'default' => '#00796B', // Default color
    ]);
    register_setting('kw_crossword_admin_view_strings', 'kw_crossword_admin_download_key_button_color', [
        'default' => '#00796B', // Default color
    ]);

    // Register individual settings with default values for text colors
    register_setting('kw_crossword_admin_view_strings', 'kw_crossword_admin_add_word_button_text_color', [
        'default' => '#ffffff', // Default text color
    ]);
    register_setting('kw_crossword_admin_view_strings', 'kw_crossword_admin_clear_list_button_text_color', [
        'default' => '#ffffff', // Default text color
    ]);
    register_setting('kw_crossword_admin_view_strings', 'kw_crossword_admin_shuffle_button_text_color', [
        'default' => '#ffffff', // Default text color
    ]);
    register_setting('kw_crossword_admin_view_strings', 'kw_crossword_admin_download_pdf_button_text_color', [
        'default' => '#ffffff', // Default text color
    ]);
    register_setting('kw_crossword_admin_view_strings', 'kw_crossword_admin_download_key_button_text_color', [
        'default' => '#ffffff', // Default text color
    ]);

    register_setting('kw_crossword_admin_view_strings', 'kw_crossword_admin_filled_cell_color', [
        'default' => '#e1f5fe', // Default text color
    ]);

    // Add a settings section for Admin Strings
    add_settings_section(
        'kw_crossword_admin_strings_section',
        null,
        'crossword_render_admin_strings_section',
        'kw-crossword-admin-strings-settings-page'
    );
}
add_action('admin_init', 'crossword_register_admin_strings_settings');

/**
 * Render Admin Strings Settings Page
 */
function crossword_render_admin_strings_settings_page() {
    ?>
    <div class="kw-settings-wrap">
        <h1><?php esc_html_e('Admin Strings Settings', 'wp-quiz-plugin'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('kw_crossword_admin_view_strings');
            do_settings_sections('kw-crossword-admin-strings-settings-page');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * Render the Admin Strings Section
 */
function crossword_render_admin_strings_section() {
    include plugin_dir_path(__FILE__) . '../templates/sections/admin-view-strings-section.php';
}
