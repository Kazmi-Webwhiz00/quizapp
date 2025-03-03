<?php
// Ensure this file is loaded in the correct context
if (!defined('ABSPATH')) {
    exit;
}

// Default values for failure popup settings
$failure_defaults = [
    'title' => __('Are you Sure ?', 'wp-quiz-plugin'),
    'body_text' => __('Not all words are typed !', 'wp-quiz-plugin'),
    'button_text' => __('Retry', 'wp-quiz-plugin'),
];

// Retrieve saved values or use defaults
$failure_settings = [
    'title' => get_option('kw_crossword_failure_popup_title', $failure_defaults['title']),
    'body_text' => get_option('kw_crossword_failure_popup_body_text', $failure_defaults['body_text']),
    'button_text' => get_option('kw_crossword_failure_popup_button_text', $failure_defaults['button_text']),
];
?>

<div class="kw-settings-section">
    <h2 class="kw-section-heading"><?php esc_html_e('Failure Popup Settings', 'wp-quiz-plugin'); ?></h2>
    <hr>

    <!-- Title Field -->
    <div class="kw-settings-field">
        <label for="kw_crossword_failure_popup_title" class="kw-label">
            <?php esc_html_e('Popup Title', 'wp-quiz-plugin'); ?>
        </label>
        <input 
            type="text" 
            id="kw_crossword_failure_popup_title" 
            name="kw_crossword_failure_popup_title" 
            class="regular-text kw-input" 
            value="<?php echo esc_attr($failure_settings['title']); ?>" 
            data-default="<?php echo esc_attr($failure_defaults['title']); ?>"
        >
        <p class="description"><?php esc_html_e('Set the title for the failure popup.', 'wp-quiz-plugin'); ?></p>
    </div>

    <!-- Body Text Field -->
    <div class="kw-settings-field">
        <label for="kw_crossword_failure_popup_body_text" class="kw-label">
            <?php esc_html_e('Popup Body Text', 'wp-quiz-plugin'); ?>
        </label>
        <textarea 
            id="kw_crossword_failure_popup_body_text" 
            name="kw_crossword_failure_popup_body_text" 
            class="kw-textarea" 
            rows="4" 
            data-default="<?php echo esc_attr($failure_defaults['body_text']); ?>"
        ><?php echo esc_textarea($failure_settings['body_text']); ?></textarea>
        <p class="description"><?php esc_html_e('Set the body text for the failure popup.', 'wp-quiz-plugin'); ?></p>
    </div>

    <!-- Button Text Field -->
    <div class="kw-settings-field">
        <label for="kw_crossword_failure_popup_button_text" class="kw-label">
            <?php esc_html_e('Button Text', 'wp-quiz-plugin'); ?>
        </label>
        <input 
            type="text" 
            id="kw_crossword_failure_popup_button_text" 
            name="kw_crossword_failure_popup_button_text" 
            class="regular-text kw-input" 
            value="<?php echo esc_attr($failure_settings['button_text']); ?>" 
            data-default="<?php echo esc_attr($failure_defaults['button_text']); ?>"
        >
        <p class="description"><?php esc_html_e('Set the text for the button in the failure popup.', 'wp-quiz-plugin'); ?></p>
    </div>

    <!-- Reset Button -->
    <button type="button" class="button-secondary kw-reset-button">
        <?php esc_html_e('Reset to Default', 'wp-quiz-plugin'); ?>
    </button>
</div>
