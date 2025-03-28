<?php
// Ensure this file is loaded in the correct context
if (!defined('ABSPATH')) {
    exit;
}

// Default values for prompts
$default_context_prompt = 'Avoid using the following words:[existing_words]';
$default_generation_prompt = 'Generate a wordsearch with [number] words on the topic [topic] suitable for users aged [age] and from the following categories: [categories]. The wordsearch should be created in the [language] language. Avoid any additional formatting such as markdown code blocks or triple backticks; provide only raw JSON output.';
$default_return_format_prompt = "\nProvide the output in the following JSON array format, with no additional text:\n\n[\n{ \"wordText\": \"exampleWord1\", \"clue\": \"Example clue for word 1\" },\n{ \"wordText\": \"exampleWord2\", \"clue\": \"Example clue for word 2\" },\n...]\n";

// Get saved values or use defaults
$context_prompt_value = get_option('kw_wordsearch_context_prompt', $default_context_prompt);
$generation_prompt_value = get_option('kw_wordsearch_generation_prompt', $default_generation_prompt);
$return_format_prompt_value = get_option('kw_wordsearch_return_format_prompt', $default_return_format_prompt);
?>
<div class="kw-settings-section">
    <h2 class="kw-section-heading"><?php esc_html_e('Prompt Customization', 'wp-quiz-plugin'); ?></h2>
    <hr>

    <!-- Context Prompt -->
    <div class="kw-settings-field">
        <div class="kw-settings-notice-box">
            <span class="kw-settings-icon">ⓘ</span>
            <div class="kw-settings-notice-content">
                <strong><?php esc_html_e('Note:', 'wp-quiz-plugin'); ?></strong>
                <?php esc_html_e('This prompt helps avoid using words that are already present in the current wordsearch game. It automatically includes all existing words as context to prevent duplicates in generated words.', 'wp-quiz-plugin'); ?>
                <br>
                <br>
                <?php esc_html_e('To use a custom-defined prompt, make sure to include the placeholder [existing_words], which will automatically populate with the existing words from the current wordsearch.', 'wp-quiz-plugin'); ?>
            </div>
        </div>
        <label for="kw_wordsearch_context_prompt" class="kw-label">
            <?php esc_html_e('Context Prompt', 'wp-quiz-plugin'); ?>
        </label>
        <textarea id="kw_wordsearch_context_prompt" name="kw_wordsearch_context_prompt" class="large-text kw-textarea"><?php echo esc_textarea($context_prompt_value); ?></textarea>
        <p class="description">
            <?php esc_html_e('Set the context prompt. This helps guide the generation by avoiding specific words.', 'wp-quiz-plugin'); ?>
        </p>
        <button type="button" class="button-secondary kw-reset-button" data-default="<?php echo esc_js($default_context_prompt); ?>">
            <?php esc_html_e('Reset to Default', 'wp-quiz-plugin'); ?>
        </button>
    </div>

    <!-- Generate Prompt -->
    <div class="kw-settings-field">
        <div class="kw-settings-notice-box">
            <span class="kw-settings-icon">ⓘ</span>
            <div class="kw-settings-notice-content">
                <strong><?php esc_html_e('Note:', 'wp-quiz-plugin'); ?></strong>
                <?php esc_html_e('This prompt defines the parameters for generating the wordsearch. Use the following placeholders to specify the details:', 'wp-quiz-plugin'); ?>
                <ul>
                    <li><strong><?php esc_html_e('[number]:', 'wp-quiz-plugin'); ?></strong> <?php esc_html_e('The number of words to include in the wordsearch.', 'wp-quiz-plugin'); ?></li>
                    <li><strong><?php esc_html_e('[topic]:', 'wp-quiz-plugin'); ?></strong> <?php esc_html_e('The topic on which the wordsearch will be based.', 'wp-quiz-plugin'); ?></li>
                    <li><strong><?php esc_html_e('[age]:', 'wp-quiz-plugin'); ?></strong> <?php esc_html_e('The age group for which the wordsearch should be created.', 'wp-quiz-plugin'); ?></li>
                    <li><strong><?php esc_html_e('[language]:', 'wp-quiz-plugin'); ?></strong> <?php esc_html_e('The language in which the wordsearch should be created.', 'wp-quiz-plugin'); ?></li>
                    <li><strong><?php esc_html_e('[categories]:', 'wp-quiz-plugin'); ?></strong> <?php esc_html_e('The selected categories (e.g. school > class > subject) to further contextualize the wordsearch.', 'wp-quiz-plugin'); ?></li>
                </ul>
                <?php esc_html_e('Ensure the placeholders are used correctly to guide the generation process effectively.', 'wp-quiz-plugin'); ?>
            </div>
        </div>
        <label for="kw_wordsearch_generation_prompt" class="kw-label">
            <?php esc_html_e('Generate Prompt', 'wp-quiz-plugin'); ?>
        </label>
        <textarea id="kw_wordsearch_generation_prompt" name="kw_wordsearch_generation_prompt" class="large-text kw-textarea"><?php echo esc_textarea($generation_prompt_value); ?></textarea>
        <p class="description">
            <?php esc_html_e('Set the generation prompt for creating wordsearch puzzles.', 'wp-quiz-plugin'); ?>
        </p>
        <button type="button" class="button-secondary kw-reset-button" data-default="<?php echo esc_js($default_generation_prompt); ?>">
            <?php esc_html_e('Reset to Default', 'wp-quiz-plugin'); ?>
        </button>
    </div>

    <!-- Return Format Prompt -->
    <div class="kw-settings-field">
        <div class="kw-settings-notice-box">
            <span class="kw-settings-icon">ⓘ</span>
            <div class="kw-settings-notice-content">
                <strong><?php esc_html_e('Note:', 'wp-quiz-plugin'); ?></strong>
                <?php esc_html_e('This prompt specifies the format of the generated wordsearch output. No additional placeholders are needed for this section.', 'wp-quiz-plugin'); ?>
                <br>
                <br>
                <strong><?php esc_html_e('⚠️ It is highly recommended not to change this prompt, as it is critical for the AI wordsearch generation process. Incorrect changes may result in errors or invalid output.', 'wp-quiz-plugin'); ?></strong>
            </div>
        </div>
        <label for="kw_wordsearch_return_format_prompt" class="kw-label">
            <?php esc_html_e('Return Format Prompt', 'wp-quiz-plugin'); ?>
        </label>
        <textarea id="kw_wordsearch_return_format_prompt" name="kw_wordsearch_return_format_prompt" class="large-text kw-textarea"><?php echo esc_textarea($return_format_prompt_value); ?></textarea>
        <p class="description">
            <?php esc_html_e('Set the return format prompt for the output of generated wordsearch puzzles.', 'wp-quiz-plugin'); ?>
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
