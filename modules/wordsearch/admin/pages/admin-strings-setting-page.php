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

    register_setting(
    'kw_wordsearch_admin_view_strings', 'kw_wordsearch_admin_featured_image',[
        'default' => plugin_dir_url(__FILE__) . '../../../assets/images/wordsearch.png',
    ]);

    // Open in New Tab Button Settings
    register_setting('kw_wordsearch_admin_view_strings', 'wordsearch_open_tab_button_label', [
        'default' => __('Open in New Tab', 'wp-quiz-plugin'),    ]);

    register_setting('kw_wordsearch_admin_view_strings', 'wordsearch_open_tab_button_color', [
        'default' => '#007BFF', 
    ]);

    register_setting('kw_wordsearch_admin_view_strings', 'wordsearch_open_tab_button_font_size', [
        'default' => '16', // Default background color
    ]);

        //Copy url button settings
        register_setting('kw_wordsearch_admin_view_strings', 'wordsearch_copy_url_button_label', [
            'default' =>  __('Copy URL to Clipboard', 'wp-quiz-plugin'), // Default button label
        ]);
    
        register_setting('kw_wordsearch_admin_view_strings', 'wordsearch_copy_url_button_color', [
            'default' => '#007BFF', // Default background color
        ]);
    
        register_setting('kw_wordsearch_admin_view_strings', 'wordsearch_copy_url_button_font_size', [
            'default' => '16', // Default background color
        ]);

        //Email button settings
        register_setting('kw_wordsearch_admin_view_strings', 'wordsearch_share_email_button_label', [
            'default' => __('Share via Email', 'wp-quiz-plugin'), // Default button label
        ]);

        register_setting('kw_wordsearch_admin_view_strings', 'wordsearch_share_email_subject', [
            'default' => __('New Wordsearch Assessment Available', 'wp-quiz-plugin'),
        ]);

        register_setting('kw_wordsearch_admin_view_strings', 'wordsearch_share_email_body', [
            'default' => __('Hello,\n\nPlease attempt this wordsearch on time. Here is the wordsearch link:\n\n[URL]\n\nBest regards,', 'wp-quiz-plugin'),
        ]);

        register_setting('kw_wordsearch_admin_view_strings', 'wordsearch_share_email_button_color', [
            'default' => '#007BFF', // Default background color
        ]);
        register_setting('kw_wordsearch_admin_view_strings', 'wordsearch_share_email_button_font_size', [
            'default' => '16', // Default background color
        ]);

    // Register the Admin Strings Section
    add_settings_section(
        'kw_wordsearch_admin_strings_section',
        null,
        'wordsearch_render_admin_strings_section',
        'kw-wordsearch-admin-strings-settings-page'
    );

    // Register the Action Buttons Section with a unique ID
    add_settings_section(
        'kw_wordsearch_admin_action_buttons_section',
        __('Word Search Action Buttons', 'wp-quiz-plugin'),
        'wordsearch_render_action_buttons_section',
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

/**
 * Render the Action Buttons Section
 */
function wordsearch_render_action_buttons_section() {
    include plugin_dir_path(__FILE__) . '/../templates/sections/admin-action-buttons-setting.php';
}