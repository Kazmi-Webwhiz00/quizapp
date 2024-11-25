<?php
// Ensure this file is loaded in the correct context
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register AI Settings
 */
function crossword_register_ai_settings() {
    // Register existing settings
    register_setting('kw_crossword_ai_settings', 'kw_crossword_openai_api_key');
    register_setting('kw_crossword_ai_settings', 'kw_crossword_openai_model');
    register_setting('kw_crossword_ai_settings', 'kw_crossword_openai_max_tokens');
    register_setting('kw_crossword_ai_settings', 'kw_crossword_openai_temperature');   

    // Add the OpenAI settings section
    add_settings_section(
        'kw_crossword_openai_settings_section',
        null, // No title for this section
        'crossword_render_openai_settings_section',
        'kw-crossword-ai-settings-page'
    );

    // Register the new Prompt Customization settings
    register_setting('kw_crossword_ai_settings', 'kw_crossword_context_prompt');
    register_setting('kw_crossword_ai_settings', 'kw_crossword_generation_prompt');
    register_setting('kw_crossword_ai_settings', 'kw_crossword_return_format_prompt');

    add_settings_section(
        'kw_crossword_prompt_customization_section',
        '',
        'crossword_render_prompt_customization_section',
        'kw-crossword-ai-settings-page'
    );
}
add_action('admin_init', 'crossword_register_ai_settings');

/**
 * Render the AI Settings Page
 */
function crossword_render_ai_settings_page() {
    ?>
    <div class="kw-settings-wrap">
        <h1><?php esc_html_e('AI Settings', 'wp-quiz-plugin'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('kw_crossword_ai_settings');
            do_settings_sections('kw-crossword-ai-settings-page');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * Render the OpenAI Settings Section
 */
function crossword_render_openai_settings_section() {
    include plugin_dir_path(__FILE__) . '../templates/sections/ai/open-ai-settings-section.php';
}

/**
 * Render the Prompt Customization Section
 */
function crossword_render_prompt_customization_section() {
    include plugin_dir_path(__FILE__) . '../templates/sections/ai/prompt-customization-section.php';
}
