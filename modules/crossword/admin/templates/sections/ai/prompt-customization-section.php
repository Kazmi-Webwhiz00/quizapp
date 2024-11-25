<?php
// Ensure this file is loaded in the correct context
if (!defined('ABSPATH')) {
    exit;
}

// Default values for prompts
$default_context_prompt = 'Avoid using the following words:[existing_words]';
$default_generation_prompt = 'Generate a crossword with [number] words on the topic [topic] suitable for users aged [age]. The crossword should be created in the [language] language.';
$default_return_format_prompt = "\nProvide the output in the following JSON array format, with no additional text:\n\n[\n{ \"word\": \"exampleWord1\", \"clue\": \"Example clue for word 1\" },\n{ \"word\": \"exampleWord2\", \"clue\": \"Example clue for word 2\" },\n...]\n";

// Get saved values or use defaults
$context_prompt_value = get_option('kw_crossword_context_prompt', $default_context_prompt);
$generation_prompt_value = get_option('kw_crossword_generation_prompt', $default_generation_prompt);
$return_format_prompt_value = get_option('kw_crossword_return_format_prompt', $default_return_format_prompt);
?>
<div class="kw-settings-section">
    <h2 class="kw-section-heading"><?php esc_html_e('Prompt Customization', 'wp-quiz-plugin'); ?></h2>
    <hr>

    <!-- Context Prompt -->
    <div class="kw-settings-field">
        <label for="kw_crossword_context_prompt" class="kw-label">
            <?php esc_html_e('Context Prompt', 'wp-quiz-plugin'); ?>
        </label>
        <textarea id="kw_crossword_context_prompt" name="kw_crossword_context_prompt" class="large-text kw-textarea"><?php echo esc_textarea($context_prompt_value); ?></textarea>
        <p class="description">
            <?php esc_html_e('Set the context prompt. This helps guide the generation by avoiding specific words.', 'wp-quiz-plugin'); ?>
        </p>
        <button type="button" class="button-secondary kw-reset-button" data-default="<?php echo esc_js($default_context_prompt); ?>">
            <?php esc_html_e('Reset to Default', 'wp-quiz-plugin'); ?>
        </button>
    </div>

    <!-- Generate Prompt -->
    <div class="kw-settings-field">
        <label for="kw_crossword_generation_prompt" class="kw-label">
            <?php esc_html_e('Generate Prompt', 'wp-quiz-plugin'); ?>
        </label>
        <textarea id="kw_crossword_generation_prompt" name="kw_crossword_generation_prompt" class="large-text kw-textarea"><?php echo esc_textarea($generation_prompt_value); ?></textarea>
        <p class="description">
            <?php esc_html_e('Set the generation prompt for creating crosswords.', 'wp-quiz-plugin'); ?>
        </p>
        <button type="button" class="button-secondary kw-reset-button" data-default="<?php echo esc_js($default_generation_prompt); ?>">
            <?php esc_html_e('Reset to Default', 'wp-quiz-plugin'); ?>
        </button>
    </div>

    <!-- Return Format Prompt -->
    <div class="kw-settings-field">
        <label for="kw_crossword_return_format_prompt" class="kw-label">
            <?php esc_html_e('Return Format Prompt', 'wp-quiz-plugin'); ?>
        </label>
        <textarea id="kw_crossword_return_format_prompt" name="kw_crossword_return_format_prompt" class="large-text kw-textarea"><?php echo esc_textarea($return_format_prompt_value); ?></textarea>
        <p class="description">
            <?php esc_html_e('Set the return format prompt for the output of generated crosswords.', 'wp-quiz-plugin'); ?>
        </p>
        <button type="button" class="button-secondary kw-reset-button" data-default="<?php echo esc_js($default_return_format_prompt); ?>">
            <?php esc_html_e('Reset to Default', 'wp-quiz-plugin'); ?>
        </button>
    </div>
</div>

<script>
document.querySelectorAll('.kw-reset-button').forEach(button => {
    button.addEventListener('click', function() {
        const textarea = this.previousElementSibling.previousElementSibling;
        textarea.value = this.getAttribute('data-default');
    });
});
</script>