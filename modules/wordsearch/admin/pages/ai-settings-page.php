<?php
// Ensure this file is loaded in the correct context
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register AI Settings for Wordsearch
 */
function ws_register_ai_settings() {
    // Register OpenAI settings
    register_setting('kw_wordsearch_ai_settings', 'kw_wordsearch_openai_api_key');
    register_setting('kw_wordsearch_ai_settings', 'kw_wordsearch_openai_model');
    register_setting('kw_wordsearch_ai_settings', 'kw_wordsearch_openai_max_tokens');
    register_setting('kw_wordsearch_ai_settings', 'kw_wordsearch_openai_temperature');

    // Add the OpenAI settings section
    add_settings_section(
        'kw_wordsearch_openai_settings_section',
        null, // No title for this section
        'ws_render_openai_settings_section',
        'kw-wordsearch-ai-settings-page'
    );

    // Register the Prompt Customization settings
    register_setting('kw_wordsearch_ai_settings', 'kw_wordsearch_context_prompt');
    register_setting('kw_wordsearch_ai_settings', 'kw_wordsearch_generation_prompt');
    register_setting('kw_wordsearch_ai_settings', 'kw_wordsearch_return_format_prompt');

    add_settings_section(
        'kw_wordsearch_prompt_customization_section',
        '',
        'ws_render_prompt_customization_section',
        'kw-wordsearch-ai-settings-page'
    );

    // Register the new AI Box settings
    register_setting('kw_wordsearch_ai_settings', 'kw_generate_with_ai_button_color_ws', [
        'default' => 'red',
    ]);
    register_setting('kw_wordsearch_ai_settings', 'kw_generate_with_ai_button_label_ws', [
        'default' => __('Generate with AI', 'wp-quiz-plugin'),
    ]);
    register_setting('kw_wordsearch_ai_settings', 'kw_generate_with_ai_box_title_ws', [
        'default' => __('Generate with AI', 'wp-quiz-plugin'),
    ]);
    register_setting('kw_wordsearch_ai_settings', 'kw_generate_with_ai_max_limit_ws', [
        'default' => 20,
    ]);

    // Add the AI Box settings section
    add_settings_section(
        'kw_wordsearch_ai_box_settings_section',
        null,
        'ws_render_ai_box_settings_section',
        'kw-wordsearch-ai-settings-page'
    );
}
add_action('admin_init', 'ws_register_ai_settings');

/**
 * Render the AI Settings Page for Wordsearch
 */
function ws_render_ai_settings_page() {
    ?>
    <div class="kw-settings-wrap">
        <h1><?php esc_html_e('AI Settings', 'wp-quiz-plugin'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('kw_wordsearch_ai_settings');
            do_settings_sections('kw-wordsearch-ai-settings-page');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * Render the OpenAI Settings Section for Wordsearch
 */
function ws_render_openai_settings_section() {
    include plugin_dir_path(__FILE__) . '../templates/sections/ai/open-ai-settings-section.php';
}

/**
 * Render the Prompt Customization Section for Wordsearch
 */
function ws_render_prompt_customization_section() {
    include plugin_dir_path(__FILE__) . '../templates/sections/ai/prompt-customization-section.php';
}

/**
 * Render the AI Box Settings Section for Wordsearch
 */
function ws_render_ai_box_settings_section() {
    include plugin_dir_path(__FILE__) . '../templates/sections/ai/ai-box-settings-section.php';
}
