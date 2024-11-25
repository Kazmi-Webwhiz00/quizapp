<?php
// Ensure this file is loaded in the correct context
if (!defined('ABSPATH')) {
    exit;
}

// Default values for Frontend UI settings
$default_settings = [
    'restart_button' => [
        'color' => '#00796b',
    ],
    'download_button' => [
        'text' => __('Download', 'wp-quiz-plugin'),
        'bg_color' => '#00796b',
        'text_color' => '#ffffff',
    ],
    'check_crossword_button' => [
        'text' => __('Check Crossword', 'wp-quiz-plugin'),
        'bg_color' => '#00796b',
        'text_color' => '#ffffff',
    ],
    'enable_live_word_check_button' => [
        'text' => __('Enable Live Word Check', 'wp-quiz-plugin'),
        'bg_color' => '#ffffff',
        'enabled_color' => '#00796b',
    ],
    'filled_cell' => [
        'bg_color' => '#e1f5fe', // Default background color for filled cells
    ],
];

// Retrieve saved values or use defaults
$settings = [];
foreach ($default_settings as $key => $values) {
    foreach ($values as $sub_key => $default_value) {
        $option_name = "kw_fe_{$key}_{$sub_key}";
        $settings[$key][$sub_key] = get_option($option_name, $default_value);
    }
}
?>

<div class="kw-settings-section">
    <h2 class="kw-section-heading"><?php esc_html_e('Frontend UI Settings', 'wp-quiz-plugin'); ?></h2>
    <hr>

    <!-- Restart Button -->
    <div class="kw-settings-field">
        <label for="kw_fe_restart_button_color" class="kw-label">
            <?php esc_html_e('Restart Button Background Color', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_fe_restart_button_color" name="kw_fe_restart_button_color" class="kw-color-picker wp-color-picker" 
            value="<?php echo esc_attr($settings['restart_button']['color']); ?>" 
            data-default="<?php echo esc_attr($default_settings['restart_button']['color']); ?>">
        <p class="description"><?php esc_html_e('Select the background color for the Restart button.', 'wp-quiz-plugin'); ?></p>
    </div>

    <!-- Download Button -->
    <div class="kw-settings-field">
        <label for="kw_fe_download_button_text" class="kw-label">
            <?php esc_html_e('Download Button Text', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_fe_download_button_text" name="kw_fe_download_button_text" class="regular-text kw-input" 
            value="<?php echo esc_attr($settings['download_button']['text']); ?>" 
            data-default="<?php echo esc_attr($default_settings['download_button']['text']); ?>">
        <p class="description"><?php esc_html_e('Set the label for the Download button.', 'wp-quiz-plugin'); ?></p>

        <label for="kw_fe_download_button_bg_color" class="kw-label">
            <?php esc_html_e('Download Button Background Color', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_fe_download_button_bg_color" name="kw_fe_download_button_bg_color" class="kw-color-picker wp-color-picker" 
            value="<?php echo esc_attr($settings['download_button']['bg_color']); ?>" 
            data-default="<?php echo esc_attr($default_settings['download_button']['bg_color']); ?>">
        <p class="description"><?php esc_html_e('Select the background color for the Download button.', 'wp-quiz-plugin'); ?></p>

        <label for="kw_fe_download_button_text_color" class="kw-label">
            <?php esc_html_e('Download Button Text Color', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_fe_download_button_text_color" name="kw_fe_download_button_text_color" class="kw-color-picker wp-color-picker" 
            value="<?php echo esc_attr($settings['download_button']['text_color']); ?>" 
            data-default="<?php echo esc_attr($default_settings['download_button']['text_color']); ?>">
        <p class="description"><?php esc_html_e('Select the text color for the Download button.', 'wp-quiz-plugin'); ?></p>
    </div>

    <!-- Check Crossword Button -->
    <div class="kw-settings-field">
        <label for="kw_fe_check_crossword_button_text" class="kw-label">
            <?php esc_html_e('Check Crossword Button Text', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_fe_check_crossword_button_text" name="kw_fe_check_crossword_button_text" class="regular-text kw-input" 
            value="<?php echo esc_attr($settings['check_crossword_button']['text']); ?>" 
            data-default="<?php echo esc_attr($default_settings['check_crossword_button']['text']); ?>">
        <p class="description"><?php esc_html_e('Set the label for the Check Crossword button.', 'wp-quiz-plugin'); ?></p>

        <label for="kw_fe_check_crossword_button_bg_color" class="kw-label">
            <?php esc_html_e('Check Crossword Button Background Color', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_fe_check_crossword_button_bg_color" name="kw_fe_check_crossword_button_bg_color" class="kw-color-picker wp-color-picker" 
            value="<?php echo esc_attr($settings['check_crossword_button']['bg_color']); ?>" 
            data-default="<?php echo esc_attr($default_settings['check_crossword_button']['bg_color']); ?>">
        <p class="description"><?php esc_html_e('Select the background color for the Check Crossword button.', 'wp-quiz-plugin'); ?></p>

        <label for="kw_fe_check_crossword_button_text_color" class="kw-label">
            <?php esc_html_e('Check Crossword Button Text Color', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_fe_check_crossword_button_text_color" name="kw_fe_check_crossword_button_text_color" class="kw-color-picker wp-color-picker" 
            value="<?php echo esc_attr($settings['check_crossword_button']['text_color']); ?>" 
            data-default="<?php echo esc_attr($default_settings['check_crossword_button']['text_color']); ?>">
        <p class="description"><?php esc_html_e('Select the text color for the Check Crossword button.', 'wp-quiz-plugin'); ?></p>
    </div>

    <!-- Enable Live Word Check Button -->
    <div class="kw-settings-field">
        <label for="kw_fe_enable_live_word_check_button_text" class="kw-label">
            <?php esc_html_e('Enable Live Word Check Button Text', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_fe_enable_live_word_check_button_text" name="kw_fe_enable_live_word_check_button_text" class="regular-text kw-input" 
            value="<?php echo esc_attr($settings['enable_live_word_check_button']['text']); ?>" 
            data-default="<?php echo esc_attr($default_settings['enable_live_word_check_button']['text']); ?>">
        <p class="description"><?php esc_html_e('Set the label for the Enable Live Word Check button.', 'wp-quiz-plugin'); ?></p>

        <label for="kw_fe_enable_live_word_check_button_bg_color" class="kw-label">
            <?php esc_html_e('Enable Live Word Check Button Background Color', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_fe_enable_live_word_check_button_bg_color" name="kw_fe_enable_live_word_check_button_bg_color" class="kw-color-picker wp-color-picker" 
            value="<?php echo esc_attr($settings['enable_live_word_check_button']['bg_color']); ?>" 
            data-default="<?php echo esc_attr($default_settings['enable_live_word_check_button']['bg_color']); ?>">
        <p class="description"><?php esc_html_e('Select the background color for the Enable Live Word Check button.', 'wp-quiz-plugin'); ?></p>

        <label for="kw_fe_enable_live_word_check_button_enabled_color" class="kw-label">
            <?php esc_html_e('Enable Live Word Check Button Enabled Color', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_fe_enable_live_word_check_button_enabled_color" name="kw_fe_enable_live_word_check_button_enabled_color" class="kw-color-picker wp-color-picker" 
            value="<?php echo esc_attr($settings['enable_live_word_check_button']['enabled_color']); ?>" 
            data-default="<?php echo esc_attr($default_settings['enable_live_word_check_button']['enabled_color']); ?>">
        <p class="description"><?php esc_html_e('Select the color for the Enabled state of the Enable Live Word Check button.', 'wp-quiz-plugin'); ?></p>
    </div>

    <!-- Filled Cell Background Color -->
    <div class="kw-settings-field">
        <label for="kw_fe_filled_cell_bg_color" class="kw-label">
            <?php esc_html_e('Filled Cell Background Color', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_fe_filled_cell_bg_color" name="kw_fe_filled_cell_bg_color" class="kw-color-picker wp-color-picker" 
            value="<?php echo esc_attr($settings['filled_cell']['bg_color']); ?>" 
            data-default="<?php echo esc_attr($default_settings['filled_cell']['bg_color']); ?>">
        <p class="description"><?php esc_html_e('Select the background color for filled cells in the crossword.', 'wp-quiz-plugin'); ?></p>
    </div>
</div>

<script>
document.querySelectorAll('.kw-reset-button').forEach(button => {
    button.addEventListener('click', function () {
        const parentField = this.closest('.kw-settings-field');
        const labelField = parentField.querySelector('input[type="text"]');
        const colorField = parentField.querySelector('.wp-color-picker');

        // Reset label field
        if (labelField) {
            const defaultLabel = this.getAttribute('data-label-default');
            labelField.value = defaultLabel;
        }

        // Reset color picker
        if (colorField) {
            const defaultColor = this.getAttribute('data-color-default');
            colorField.value = defaultColor;

            // Update color picker UI
            const colorPickerContainer = jQuery(colorField).closest('.wp-picker-container');
            const colorResultButton = colorPickerContainer.find('.wp-color-result');
            colorResultButton.css('background-color', defaultColor);
            colorField.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });
});
</script>
