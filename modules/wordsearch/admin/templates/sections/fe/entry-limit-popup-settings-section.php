<?php
// Ensure this file is loaded in the correct context
if (!defined('ABSPATH')) {
    exit;
}

// Default values for time's up popup settings
$entry_limit_defaults = [
    'title' => __("Entry Limit Reached", 'wp-quiz-plugin'),
    'body_text' => __('You cannot add more than 15 entries to the word search.', 'wp-quiz-plugin'),
];

// Retrieve saved values or use defaults
$entry_limit_settings = [
    'title' => get_option('kw_wordsearch_entry_limit_popup_title', $entry_limit_defaults['title']),
    'body_text' => get_option('kw_wordsearch_entry_limit_popup_body_text', $entry_limit_defaults['body_text']),
];
?>

<div class="kw-settings-section">
    <h2 class="kw-section-heading"><?php esc_html_e("Entry Limit Settings", 'wp-quiz-plugin'); ?></h2>
    <hr>

    <!-- Title Field -->
    <div class="kw-settings-field">
        <label for="kw_wordsearch_timeup_popup_title" class="kw-label">
            <?php esc_html_e('Popup Title', 'wp-quiz-plugin'); ?>
        </label>
        <input 
            type="text" 
            id="kw_wordsearch_entry_limit_popup_title" 
            name="kw_wordsearch_entry_limit_popup_title" 
            class="regular-text kw-input" 
            value="<?php echo esc_attr($entry_limit_settings['title']); ?>" 
            data-default="<?php echo esc_attr($entry_limit_defaults['title']); ?>"
        >
        <p class="description"><?php esc_html_e("Set the title for the entry limit popup.", 'wp-quiz-plugin'); ?></p>
    </div>

    <!-- Body Text Field -->
    <div class="kw-settings-field">
        <label for="kw_wordsearch_entry_limit_popup_body_text" class="kw-label">
            <?php esc_html_e('Popup Body Text', 'wp-quiz-plugin'); ?>
        </label>
        <textarea 
            id="kw_wordsearch_entry_limit_popup_body_text" 
            name="kw_wordsearch_entry_limit_popup_body_text" 
            class="kw-textarea" 
            rows="4" 
            data-default="<?php echo esc_attr($entry_limit_defaults['body_text']); ?>"
        ><?php echo esc_textarea($entry_limit_settings['body_text']); ?></textarea>
        <p class="description"><?php esc_html_e("Set the body text for the entry limit popup.", 'wp-quiz-plugin'); ?></p>
    </div>

    <!-- Reset Button -->
    <button type="button" class="button-secondary kw-reset-button">
        <?php esc_html_e('Reset to Default', 'wp-quiz-plugin'); ?>
    </button>
</div>
