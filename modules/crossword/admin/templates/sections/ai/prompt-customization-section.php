<?php
// Ensure this file is loaded in the correct context
if (!defined('ABSPATH')) {
    exit;
}

$default_prompt = 'Generate a crossword puzzle prompt.';
$value = get_option('kw_crossword_prompt_main', $default_prompt);
?>
<div class="kw-settings-section">
    <h2><?php esc_html_e('Prompt Customization', 'wp-quiz-plugin'); ?></h2>
    <div class="kw-settings-field">
        <label for="kw_crossword_prompt_main" class="kw-label">
            <?php esc_html_e('Default Prompt', 'wp-quiz-plugin'); ?>
        </label>
        <textarea id="kw_crossword_prompt_main" name="kw_crossword_prompt_main" class="large-text kw-textarea"><?php echo esc_textarea($value); ?></textarea>
        <p class="description"><?php esc_html_e('Set the default prompt for generating crosswords.', 'wp-quiz-plugin'); ?></p>
        <button type="button" class="button-secondary" id="kw-reset-default-prompt"><?php esc_html_e('Reset to Default', 'wp-quiz-plugin'); ?></button>
    </div>
</div>
