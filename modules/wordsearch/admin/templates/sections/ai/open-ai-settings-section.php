<?php
// Ensure this file is loaded in the correct context
if (!defined('ABSPATH')) {
    exit;
}

// Get values from options for Wordsearch
$api_key = get_option('kw_wordsearch_openai_api_key', '');
$model = get_option('kw_wordsearch_openai_model', 'gpt-4o-mini');
$max_tokens = get_option('kw_wordsearch_openai_max_tokens', 299);
$temperature = get_option('kw_wordsearch_openai_temperature', 0.4);
?>

<div class="kw-settings-section">
    <h2><?php esc_html_e('OpenAI API Settings', 'wp-quiz-plugin'); ?></h2>

    <!-- OpenAI API Key -->
    <div class="kw-settings-field">
        <label for="kw_wordsearch_openai_api_key"><?php esc_html_e('OpenAI API Key', 'wp-quiz-plugin'); ?></label>
        <input type="text" id="kw_wordsearch_openai_api_key" name="kw_wordsearch_openai_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
        <p class="description"><?php esc_html_e('Enter your OpenAI API key.', 'wp-quiz-plugin'); ?></p>
    </div>

    <!-- OpenAI Model -->
    <div class="kw-settings-field">
        <label for="kw_wordsearch_openai_model"><?php esc_html_e('OpenAI Model', 'wp-quiz-plugin'); ?></label>
        <select id="kw_wordsearch_openai_model" name="kw_wordsearch_openai_model" class="regular-select">
            <option value="gpt-3.5-turbo" <?php selected($model, 'gpt-3.5-turbo'); ?>><?php esc_html_e('GPT-3.5 Turbo', 'wp-quiz-plugin'); ?></option>
            <option value="gpt-4o-mini" <?php selected($model, 'gpt-4o-mini'); ?>><?php esc_html_e('GPT-4.0 Mini', 'wp-quiz-plugin'); ?></option>
            <option value="gpt-4o" <?php selected($model, 'gpt-4o'); ?>><?php esc_html_e('GPT-4.0', 'wp-quiz-plugin'); ?></option>
            <option value="gpt-4" <?php selected($model, 'gpt-4'); ?>><?php esc_html_e('GPT-4', 'wp-quiz-plugin'); ?></option>
        </select>
        <p class="description"><?php esc_html_e('Select the OpenAI model to use.', 'wp-quiz-plugin'); ?></p>
    </div>

    <!-- Max Tokens -->
    <div class="kw-settings-field">
        <label for="kw_wordsearch_openai_max_tokens"><?php esc_html_e('Max Tokens', 'wp-quiz-plugin'); ?></label>
        <input type="number" id="kw_wordsearch_openai_max_tokens" name="kw_wordsearch_openai_max_tokens" value="<?php echo esc_attr($max_tokens); ?>" class="small-text" min="50" max="300">
        <p class="description"><?php esc_html_e('Set the maximum number of tokens for the response (50 to 300).', 'wp-quiz-plugin'); ?></p>
    </div>

    <!-- Temperature -->
    <div class="kw-settings-field">
        <label for="kw_wordsearch_openai_temperature"><?php esc_html_e('Temperature', 'wp-quiz-plugin'); ?></label>
        <input type="number" step="0.1" id="kw_wordsearch_openai_temperature" name="kw_wordsearch_openai_temperature" value="<?php echo esc_attr($temperature); ?>" class="small-text" min="0" max="1">
        <p class="description"><?php esc_html_e('Set the temperature for response randomness (0 to 1).', 'wp-quiz-plugin'); ?></p>
    </div>
</div>
