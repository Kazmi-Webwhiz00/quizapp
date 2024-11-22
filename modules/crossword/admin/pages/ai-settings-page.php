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
        'kw_crossword_ai_section',
        __('OpenAI API Settings', 'wp-quiz-plugin'),
        'crossword_render_ai_settings_section',
        'kw-crossword-ai-settings-page' // Updated to match the page name
    );

    // Add settings fields
    add_settings_field(
        'kw_crossword_openai_model',
        __('OpenAI Model', 'wp-quiz-plugin'),
        'crossword_render_openai_model_field',
        'kw-crossword-ai-settings-page', // Updated to match the page name
        'kw_crossword_ai_section'
    );

    add_settings_field(
        'kw_crossword_openai_max_tokens',
        __('Max Tokens', 'wp-quiz-plugin'),
        'crossword_render_openai_max_tokens_field',
        'kw-crossword-ai-settings-page', // Updated to match the page name
        'kw_crossword_ai_section'
    );

    add_settings_field(
        'kw_crossword_openai_temperature',
        __('Temperature', 'wp-quiz-plugin'),
        'crossword_render_openai_temperature_field',
        'kw-crossword-ai-settings-page', // Updated to match the page name
        'kw_crossword_ai_section'
    );
}
add_action('admin_init', 'crossword_register_ai_settings');



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
 * Render the OpenAI API Settings Section
 */
function crossword_render_ai_settings_section() {
    echo '<p>' . esc_html__('Configure OpenAI API settings for generating crossword content.', 'wp-quiz-plugin') . '</p>';
}

/**
 * Render the OpenAI Model Field
 */
function crossword_render_openai_model_field() {
    $value = get_option('kw_crossword_openai_model', 'text-davinci-003');
    ?>
    <input type="text" id="kw_crossword_openai_model" name="kw_crossword_openai_model" value="<?php echo esc_attr($value); ?>" class="regular-text">
    <p class="description"><?php esc_html_e('Enter the OpenAI model name (e.g., text-davinci-003).', 'wp-quiz-plugin'); ?></p>
    <?php
}

/**
 * Render the Max Tokens Field
 */
function crossword_render_openai_max_tokens_field() {
    $value = get_option('kw_crossword_openai_max_tokens', 100);
    ?>
    <input type="number" id="kw_crossword_openai_max_tokens" name="kw_crossword_openai_max_tokens" value="<?php echo esc_attr($value); ?>" class="small-text">
    <p class="description"><?php esc_html_e('Set the maximum number of tokens for the response (e.g., 100).', 'wp-quiz-plugin'); ?></p>
    <?php
}

/**
 * Render the Temperature Field
 */
function crossword_render_openai_temperature_field() {
    $value = get_option('kw_crossword_openai_temperature', 0.7);
    ?>
    <input type="number" step="0.1" id="kw_crossword_openai_temperature" name="kw_crossword_openai_temperature" value="<?php echo esc_attr($value); ?>" class="small-text">
    <p class="description"><?php esc_html_e('Set the temperature for response randomness (e.g., 0.7).', 'wp-quiz-plugin'); ?></p>
    <?php
}
