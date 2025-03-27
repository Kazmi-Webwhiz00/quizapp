<?php
// Ensure this file is loaded in the correct context
if (!defined('ABSPATH')) {
    exit;
}

// Default values for time's up popup settings
$timeup_defaults = [
    'title' => __("Time's Up!", 'wp-quiz-plugin'),
    'body_text' => __('Your time has expired for this word search puzzle.', 'wp-quiz-plugin'),
    'challenge_text' => __('Would you like to start a new game?', 'wp-quiz-plugin'),
    'button_text'   =>   __('Play Again', 'wp-quiz-plugin'),
];

// Retrieve saved values or use defaults
$timeup_settings = [
    'title' => get_option('kw_wordsearch_timeup_popup_title', $timeup_defaults['title']),
    'body_text' => get_option('kw_wordsearch_timeup_popup_body_text', $timeup_defaults['body_text']),
    'challenge_text' => get_option('kw_wordsearch_timeup_popup_challenge_text', $timeup_defaults['challenge_text']),
    'button_text' => get_option('kw_wordsearch_timeup_popup_button_text', $timeup_defaults['button_text']),
];
?>

<div class="kw-settings-section">
    <h2 class="kw-section-heading"><?php esc_html_e("Time's Up Popup Settings", 'wp-quiz-plugin'); ?></h2>
    <hr>

    <!-- Title Field -->
    <div class="kw-settings-field">
        <label for="kw_wordsearch_timeup_popup_title" class="kw-label">
            <?php esc_html_e('Popup Title', 'wp-quiz-plugin'); ?>
        </label>
        <input 
            type="text" 
            id="kw_wordsearch_timeup_popup_title" 
            name="kw_wordsearch_timeup_popup_title" 
            class="regular-text kw-input" 
            value="<?php echo esc_attr($timeup_settings['title']); ?>" 
            data-default="<?php echo esc_attr($timeup_defaults['title']); ?>"
        >
        <p class="description"><?php esc_html_e("Set the title for the time's up popup.", 'wp-quiz-plugin'); ?></p>
    </div>

    <!-- Body Text Field -->
    <div class="kw-settings-field">
        <label for="kw_wordsearch_timeup_popup_body_text" class="kw-label">
            <?php esc_html_e('Popup Body Text', 'wp-quiz-plugin'); ?>
        </label>
        <textarea 
            id="kw_wordsearch_timeup_popup_body_text" 
            name="kw_wordsearch_timeup_popup_body_text" 
            class="kw-textarea" 
            rows="4" 
            data-default="<?php echo esc_attr($timeup_defaults['body_text']); ?>"
        ><?php echo esc_textarea($timeup_settings['body_text']); ?></textarea>
        <p class="description"><?php esc_html_e("Set the body text for the time's popup.", 'wp-quiz-plugin'); ?></p>
    </div>

    <!-- Challenge Text -->
    <div class="kw-settings-field">
        <label for="kw_wordsearch_timeup_popup_challenge_text" class="kw-label">
            <?php esc_html_e('Challenge Text', 'wp-quiz-plugin'); ?>
        </label>
        <input 
            type="text" 
            id="kw_wordsearch_timeup_popup_challenge_text" 
            name="kw_wordsearch_timeup_popup_challenge_text" 
            class="regular-text kw-input" 
            value="<?php echo esc_attr($timeup_settings['challenge_text']); ?>" 
            data-default="<?php echo esc_attr($timeup_defaults['challenge_text']); ?>"
        >
        <p class="description"><?php esc_html_e('Set the text for playing again.', 'wp-quiz-plugin'); ?></p>
    </div>

        <!-- Button Text -->
        <div class="kw-settings-field">
        <label for="kw_wordsearch_timeup_popup_challenge_text" class="kw-label">
            <?php esc_html_e('Button Text', 'wp-quiz-plugin'); ?>
        </label>
        <input 
            type="text" 
            id="kw_wordsearch_timeup_popup_button_text" 
            name="kw_wordsearch_timeup_popup_button_text" 
            class="regular-text kw-input" 
            value="<?php echo esc_attr($timeup_settings['button_text']); ?>" 
            data-default="<?php echo esc_attr($timeup_defaults['button_text']); ?>"
        >
        <p class="description"><?php esc_html_e('Set the text for playing again button.', 'wp-quiz-plugin'); ?></p>
    </div>

    <!-- Reset Button -->
    <button type="button" class="button-secondary kw-reset-button">
        <?php esc_html_e('Reset to Default', 'wp-quiz-plugin'); ?>
    </button>
</div>
