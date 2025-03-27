<?php
// Ensure this file is loaded in the correct context
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Register Admin Strings Settings
 */

 function wordsearch_register_admin_strings_settings() {
    // Register individual settings with default values for labels
    register_setting('kw_wordsearch_admin_view_strings', 'kw_wordsearch_admin_add_word_button_label', [
        'default' => __('Add a Word', 'wp-quiz-plugin'),
    ]);
    register_setting('kw_wordsearch_admin_view_strings', 'kw_wordsearch_admin_clear_list_button_label', [
        'default' => __('Clear List', 'wp-quiz-plugin'),
    ]);
    register_setting('kw_wordsearch_admin_view_strings', 'kw_wordsearch_admin_shuffle_button_label', [
        'default' => __('Shuffle', 'wp-quiz-plugin'),
    ]);
    register_setting('kw_wordsearch_admin_view_strings', 'kw_wordsearch_admin_show_answers_checkbox_label', [
        'default' => __('Show Answers', 'wp-quiz-plugin'),
    ]);

    register_setting('kw_wordsearch_admin_view_strings', 'kw_admin_show_words_checkbox_label', [
        'default' => __('Show Words', 'wp-quiz-plugin'),
    ]);

    register_setting('kw_wordsearch_admin_view_strings', 'kw_wordsearch_admin_add_words_container_label', [
        'default' => __('Add Words', 'wp-quiz-plugin'),
    ]);

    register_setting('kw_wordsearch_admin_view_strings', 'kw_no_entries_label', ['default' =>  __('No word search entries found', 'wp-quiz-plugin')]);
    
    register_setting('kw_wordsearch_admin_view_strings', 'kw_wordsearch_default_category_value', [
        'default' => __('Physics', 'wp-quiz-plugin'),
    ]);
    register_setting('kw_wordsearch_admin_view_strings', 'kw_wordsearch_admin_full_view_container_label', [
        'default' => __('Preview Wordsearch', 'wp-quiz-plugin'),
    ]);

    // Register individual settings with default values for background colors
    register_setting('kw_wordsearch_admin_view_strings', 'kw_wordsearch_admin_add_word_button_color', [
        'default' => '#0073aa', // Default color
    ]);
    register_setting('kw_wordsearch_admin_view_strings', 'kw_wordsearch_admin_clear_list_button_color', [
        'default' => '#0073aa', // Default color
    ]);
    register_setting('kw_wordsearch_admin_view_strings', 'kw_wordsearch_admin_shuffle_button_color', [
        'default' => '#00796B', // Default color
    ]);
    // Register individual settings with default values for text colors
    register_setting('kw_wordsearch_admin_view_strings', 'kw_wordsearch_admin_add_word_button_text_color', [
        'default' => '#ffffff', // Default text color
    ]);
    register_setting('kw_wordsearch_admin_view_strings', 'kw_wordsearch_admin_clear_list_button_text_color', [
        'default' => '#ffffff', // Default text color
    ]);
    register_setting('kw_wordsearch_admin_view_strings', 'kw_wordsearch_admin_shuffle_button_text_color', [
        'default' => '#ffffff', // Default text color
    ]);

    register_setting('kw_wordsearch_admin_view_strings', 'kw_wordsearch_admin_shuffle_button_color', [
        'default' => '#0073aa', // Default background color
    ]);

    // Add a settings section for Admin Strings
    add_settings_section(
        'kw_wordsearch_admin_strings_section',
        null,
        'wordsearch_render_admin_strings_section',
        'kw-wordsearch-admin-strings-settings-page'
    );
}
add_action('admin_init', 'wordsearch_register_admin_strings_settings');

/**
 * Render Admin Strings Settings Page
 */
function ws_render_admin_strings_settings_page() {
    ?>
    <div class="kw-settings-wrap">
        <h1><?php esc_html_e('Admin Strings Settings', 'wp-quiz-plugin'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('kw_wordsearch_admin_view_strings');
            do_settings_sections('kw-wordsearch-admin-strings-settings-page');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * Render the Admin Strings Section
 */
function wordsearch_render_admin_strings_section() {
    include plugin_dir_path(__FILE__) . '/../templates/sections/admin-view-strings-setting.php';
}