<?php
// Ensure this file is loaded in the correct context
if (!defined('ABSPATH')) {
    exit;
}

// Get saved values or use defaults
$ai_button_label = get_option('kw_genreate_with_ai_button_label', __('Generate with AI', 'wp-quiz-plugin'));
$ai_button_color = get_option('kw_genreate_with_ai_button_color', '#6c9bd1'); // Default to blue
?>

<!-- New left side section -->
<div class="kw-loading" style="display:none;">
      <div class="kw-loading-text"><?php echo esc_html(_x('Generating Crosswords...', 'crossword','wp-quiz-plugin')); ?></div>
    </div>
<div id="generate-ai-container">
    <input type="text" id="ai-topic" placeholder="<?php esc_attr_e('Topic', 'wp-quiz-plugin'); ?>" />
    <input type="text" id="ai-age" placeholder="<?php esc_attr_e('Age', 'wp-quiz-plugin'); ?>" />
    <input type="text" id="ai-language" placeholder="<?php esc_attr_e('Language', 'wp-quiz-plugin'); ?>" />
    <input type="number" id="ai-questions" placeholder="<?php esc_attr_e('Number', 'wp-quiz-plugin'); ?>" min="1" max="10" />

    <button type="button" id="generate-ai-button" style="background-color: <?php echo esc_attr($ai_button_color); ?>; width: 100%;">
        <?php echo esc_html__($ai_button_label, 'wp-quiz-plugin' ); ?>
    </button>
</div>
