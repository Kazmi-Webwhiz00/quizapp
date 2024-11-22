<?php
// Ensure this file is loaded in the correct context
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register AI Settings
 */
function crossword_register_ai_settings() {
    // Register settings
    register_setting('kw_crossword_ai_settings', 'kw_crossword_openai_model');
    register_setting('kw_crossword_ai_settings', 'kw_crossword_openai_max_tokens');
    register_setting('kw_crossword_ai_settings', 'kw_crossword_openai_temperature');

    // Add a new section
    add_settings_section(
        'kw_crossword_openai_settings_section', // Updated section ID for clarity
        __('OpenAI Settings Section', 'wp-quiz-plugin'), // Updated section name
        'crossword_render_openai_settings_section',
        'kw-crossword-ai-settings-page' // Page slug
    );
}
add_action('admin_init', 'crossword_register_ai_settings');

/**
 * Render the AI Settings Page
 */
function crossword_render_ai_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('AI Settings', 'wp-quiz-plugin'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('kw_crossword_ai_settings'); // Option group
            do_settings_sections('kw-crossword-ai-settings-page'); // Render sections
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
    include plugin_dir_path(__FILE__) . '../templates/sections/ai/open-ai-settings-section.php'; // Adjusted template file path
}
