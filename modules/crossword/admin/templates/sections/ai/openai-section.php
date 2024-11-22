<?php
// Fetch values for the fields
$model = get_option('kw_crossword_openai_model', 'text-davinci-003');
$max_tokens = get_option('kw_crossword_openai_max_tokens', 100);
$temperature = get_option('kw_crossword_openai_temperature', 0.7);
?>

<div class="openai-settings-section">
    <h2><?php esc_html_e('OpenAI API Settings', 'wp-quiz-plugin'); ?></h2>
    <p><?php esc_html_e('Configure the OpenAI API settings for generating content.', 'wp-quiz-plugin'); ?></p>

    <!-- OpenAI Model Field -->
    <div class="openai-settings-field">
        <label for="kw_crossword_openai_model"><?php esc_html_e('OpenAI Model', 'wp-quiz-plugin'); ?></label>
        <input type="text" id="kw_crossword_openai_model" name="kw_crossword_openai_model" value="<?php echo esc_attr($model); ?>" class="regular-text">
        <p class="description"><?php esc_html_e('Enter the OpenAI model name (e.g., text-davinci-003).', 'wp-quiz-plugin'); ?></p>
    </div>

    <!-- Max Tokens Field -->
    <div class="openai-settings-field">
        <label for="kw_crossword_openai_max_tokens"><?php esc_html_e('Max Tokens', 'wp-quiz-plugin'); ?></label>
        <input type="number" id="kw_crossword_openai_max_tokens" name="kw_crossword_openai_max_tokens" value="<?php echo esc_attr($max_tokens); ?>" class="small-text">
        <p class="description"><?php esc_html_e('Enter the maximum number of tokens (e.g., 100).', 'wp-quiz-plugin'); ?></p>
    </div>

    <!-- Temperature Field -->
    <div class="openai-settings-field">
        <label for="kw_crossword_openai_temperature"><?php esc_html_e('Temperature', 'wp-quiz-plugin'); ?></label>
        <input type="number" step="0.1" id="kw_crossword_openai_temperature" name="kw_crossword_openai_temperature" value="<?php echo esc_attr($temperature); ?>" class="small-text">
        <p class="description"><?php esc_html_e('Enter the temperature value (e.g., 0.7).', 'wp-quiz-plugin'); ?></p>
    </div>
</div>
