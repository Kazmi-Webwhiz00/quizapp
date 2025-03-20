<?php
// Ensure this file is loaded in the correct context
if (!defined('ABSPATH')) {
    exit;
}

// Get saved values or use defaults for wordsearch
$ws_button_label = get_option('kw_generate_with_ai_button_label_ws', __('Generate with AI', 'wp-quiz-plugin'));
$ws_button_color = get_option('kw_generate_with_ai_button_color_ws', '#6c9bd1'); // Default to blue
?>

<!-- New left side section for Wordsearch -->
<div class="kw-loading" style="display:none;">
    <div class="kw-loading-text"><?php echo _x('Generating Wordsearch...', 'wordsearch','wp-quiz-plugin'); ?></div>
</div>
<div id="ws-generate-ai-container">
    <input type="text" id="ws-topic" placeholder="<?php esc_attr_e('Topic', 'wp-quiz-plugin'); ?>" />
    <input type="text" id="ws-age" placeholder="<?php esc_attr_e('Age', 'wp-quiz-plugin'); ?>" />
    <input type="text" id="ws-language" placeholder="<?php esc_attr_e('Language', 'wp-quiz-plugin'); ?>" />
    <input type="number" id="ws-words" placeholder="<?php esc_attr_e('Number', 'wp-quiz-plugin'); ?>" min="1" max="20" />

    <button type="button" id="ws-generate-ai-button" style="background-color: <?php echo esc_attr($ws_button_color); ?>; width: 100%;">
        <?php echo esc_html__($ws_button_label, 'wp-quiz-plugin' ); ?>
    </button>
</div>
