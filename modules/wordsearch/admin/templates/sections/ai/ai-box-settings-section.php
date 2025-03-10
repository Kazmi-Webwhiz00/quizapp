<?php
// Ensure this file is loaded in the correct context
if (!defined('ABSPATH')) {
    exit;
}

// Default values for the AI Box settings for Wordsearch
$default_button_color = '#6c9bd1'; // Default to blue
$default_button_label = __('Generate with AI', 'wp-quiz-plugin');
$default_box_title = __('Generate with AI', 'wp-quiz-plugin');
$default_max_limit = 10;

// Get saved values or use defaults for Wordsearch
$button_color = get_option('kw_generate_with_ai_button_color_ws', $default_button_color);
$button_label = get_option('kw_generate_with_ai_button_label_ws', $default_button_label);
$box_title    = get_option('kw_generate_with_ai_box_title_ws', $default_box_title);
$max_limit    = get_option('kw_generate_with_ai_max_limit_ws', $default_max_limit);
?>

<div class="kw-settings-section">
    <h2 class="kw-section-heading"><?php esc_html_e('AI Box Settings', 'wp-quiz-plugin'); ?></h2>
    <hr>

    <!-- AI Box Title -->
    <div class="kw-settings-field">
        <label for="kw_generate_with_ai_box_title_ws" class="kw-label">
            <?php esc_html_e('AI Box Title', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_generate_with_ai_box_title_ws" name="kw_generate_with_ai_box_title_ws" class="regular-text kw-input" value="<?php echo esc_attr($box_title); ?>" data-default="<?php echo esc_attr($default_box_title); ?>">
        <p class="description">
            <?php esc_html_e('Set the title displayed on the AI box.', 'wp-quiz-plugin'); ?>
        </p>
    </div>

    <!-- AI Button Label -->
    <div class="kw-settings-field">
        <label for="kw_generate_with_ai_button_label_ws" class="kw-label">
            <?php esc_html_e('AI Button Label', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_generate_with_ai_button_label_ws" name="kw_generate_with_ai_button_label_ws" class="regular-text kw-input" value="<?php echo esc_attr($button_label); ?>" data-default="<?php echo esc_attr($default_button_label); ?>">
        <p class="description">
            <?php esc_html_e('Set the label text for the "Generate with AI" button.', 'wp-quiz-plugin'); ?>
        </p>
    </div>

    <!-- AI Button Color -->
    <div class="kw-settings-field">
        <label for="kw_generate_with_ai_button_color_ws" class="kw-label">
            <?php esc_html_e('Button Color', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_generate_with_ai_button_color_ws" name="kw_generate_with_ai_button_color_ws" class="kw-color-picker wp-color-picker" value="<?php echo esc_attr($button_color); ?>" data-default="<?php echo esc_attr($default_button_color); ?>">
        <p class="description">
            <?php esc_html_e('Select the color of the "Generate with AI" button using the color picker.', 'wp-quiz-plugin'); ?>
        </p>
    </div>

    <!-- AI Max Limit -->
    <div class="kw-settings-field">
        <label for="kw_generate_with_ai_max_limit_ws" class="kw-label">
            <?php esc_html_e('Max Limit', 'wp-quiz-plugin'); ?>
        </label>
        <input type="number" id="kw_generate_with_ai_max_limit_ws" name="kw_generate_with_ai_max_limit_ws" class="small-text kw-input" value="<?php echo esc_attr($max_limit); ?>" min="1" data-default="<?php echo esc_attr($default_max_limit); ?>">
        <p class="description">
            <?php esc_html_e('Set the maximum limit for AI-generated words in the wordsearch.', 'wp-quiz-plugin'); ?>
        </p>
        <button type="button" class="button-secondary kw-reset-button" data-default="<?php echo esc_js($default_max_limit); ?>">
            <?php esc_html_e('Reset to Default', 'wp-quiz-plugin'); ?>
        </button>
    </div>
</div>

<script>
document.querySelectorAll('.kw-reset-button').forEach(button => {
    button.addEventListener('click', function () {
        const field = this.previousElementSibling.previousElementSibling; // Target the associated input field
        const defaultValue = this.getAttribute('data-default'); // Get the default value

        field.value = defaultValue; // Reset the input field value

        // If it's a color picker, update its value and change the UI
        if (field.classList.contains('kw-color-picker')) {
            const colorPickerContainer = jQuery(field).closest('.wp-picker-container');
            const colorResultButton = colorPickerContainer.find('.wp-color-result'); // The color preview button

            // Update the color picker value
            field.value = defaultValue;
            colorResultButton.css('background-color', defaultValue); // Update the button background color

            // Trigger a change event for the color picker to refresh the UI
            field.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });
});
</script>
