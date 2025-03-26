<?php
// Ensure this file is loaded in the correct context
if (!defined('ABSPATH')) {
    exit;
}

// Default values for admin strings, button colors, and text colors
    $default_settings = [
        'add_word' => [
            'label' => __('Add a Word', 'wp-quiz-plugin'),
            'color' => '#0073aa', // Default background color
            'text_color' => '#ffffff', // Default text color
        ],
        'clear_list' => [
            'label' => __('Clear List', 'wp-quiz-plugin'),
            'color' => '#0073aa',
            'text_color' => '#ffffff',
        ],
        'shuffle' => [
            'label' => __('Shuffle', 'wp-quiz-plugin'),
            'color' => '#00796B',
            'text_color' => '#ffffff',
        ],
        // 'download_pdf' => [
        //     'label' => __('Download as PDF', 'wp-quiz-plugin'),
        //     'color' => '#00796B',
        //     'text_color' => '#ffffff',
        // ],
        // 'download_key' => [
        //     'label' => __('Download Key', 'wp-quiz-plugin'),
        //     'color' => '#00796B',
        //     'text_color' => '#ffffff',
        // ]
    ];

// Retrieve saved values or use defaults
$settings = [];
foreach ($default_settings as $key => $values) {
    $settings[$key]['label'] = get_option("kw_wordsearch_admin_{$key}_button_label", $values['label']);
    if (isset($values['color'])) {
        $settings[$key]['color'] = get_option("kw_wordsearch_admin_{$key}_button_color", $values['color']);
    }
    if (isset($values['text_color'])) {
        $settings[$key]['text_color'] = get_option("kw_wordsearch_admin_{$key}_button_text_color", $values['text_color']);
    }
}
?>

<div class="kw-settings-section">
    <h2 class="kw-section-heading"><?php esc_html_e('Admin Strings, Button Colors, and Text Colors', 'wp-quiz-plugin'); ?></h2>
    <hr>

    <div class="kw-settings-field">


    <!-- Preview Full View -->
    <label for="kw_wordsearch_default_category_value" class="kw-label">
        <?php esc_html_e('Set value of default category', 'wp-quiz-plugin'); ?>
    </label>
    <input type="text" id="kw_wordsearch_default_category_value" name="kw_wordsearch_default_category_value" class="regular-text kw-input" 
        value="<?php echo esc_attr(get_option('kw_wordsearch_default_category_value', __('Physics', 'wp-quiz-plugin'))); ?>" 
        data-default="<?php echo esc_attr(__('Physics', 'wp-quiz-plugin')); ?>">
    <p class="description"><?php esc_html_e('Set the value for Default Category.', 'wp-quiz-plugin'); ?></p>

    </div>

    <!-- Add Words Metabox Label -->
    <div class="kw-settings-field">


    <!-- Preview Full View -->
    <label for="kw_wordsearch_admin_full_view_container_label" class="kw-label">
        <?php esc_html_e('Wordsearch Full View Label', 'wp-quiz-plugin'); ?>
    </label>
    <input type="text" id="kw_wordsearch_admin_full_view_container_label" name="kw_wordsearch_admin_full_view_container_label" class="regular-text kw-input" 
        value="<?php echo esc_attr(get_option('kw_wordsearch_admin_full_view_container_label', __('Preview Wordsearch', 'wp-quiz-plugin'))); ?>" 
        data-default="<?php echo esc_attr(__('Preview Wordsearch', 'wp-quiz-plugin')); ?>">
    <p class="description"><?php esc_html_e('Set the label for "Wordsearch Full View" container.', 'wp-quiz-plugin'); ?></p>

    </div>

    <!-- Add Word -->
    <div class="kw-settings-field">
        <label for="kw_wordsearch_admin_add_word_button_label" class="kw-label">
            <?php esc_html_e('Add Word Button Label', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_wordsearch_admin_add_word_button_label" name="kw_wordsearch_admin_add_word_button_label" class="regular-text kw-input" value="<?php echo esc_attr($settings['add_word']['label']); ?>" data-default="<?php echo esc_attr($default_settings['add_word']['label']); ?>">
        <p class="description"><?php esc_html_e('Set the label for the "Add Word" button.', 'wp-quiz-plugin'); ?></p>

        <label for="kw_wordsearch_admin_add_words_container_label" class="kw-label">
        <?php esc_html_e('Add Words Metabox Label', 'wp-quiz-plugin'); ?>
    </label>
    <input type="text" id="kw_wordsearch_admin_add_words_container_label" name="kw_wordsearch_admin_add_words_container_label" class="regular-text kw-input" 
        value="<?php echo esc_attr(get_option('kw_wordsearch_admin_add_words_container_label', __('Add Words', 'wp-quiz-plugin'))); ?>" 
        data-default="<?php echo esc_attr(__('Add Words', 'wp-quiz-plugin')); ?>">
    <p class="description"><?php esc_html_e('Set the label for "Add Words" container.', 'wp-quiz-plugin'); ?></p>

        <label for="kw_wordsearch_admin_add_word_button_color" class="kw-label">
            <?php esc_html_e('Button Background Color', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_wordsearch_admin_add_word_button_color" name="kw_wordsearch_admin_add_word_button_color" class="kw-color-picker wp-color-picker" value="<?php echo esc_attr($settings['add_word']['color']); ?>" data-default="<?php echo esc_attr($default_settings['add_word']['color']); ?>">
        <p class="description"><?php esc_html_e('Select the background color for the "Add Word" button.', 'wp-quiz-plugin'); ?></p>

        <label for="kw_wordsearch_admin_add_word_button_text_color" class="kw-label">
            <?php esc_html_e('Button Text Color', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_wordsearch_admin_add_word_button_text_color" name="kw_wordsearch_admin_add_word_button_text_color" class="kw-color-picker wp-color-picker" value="<?php echo esc_attr($settings['add_word']['text_color']); ?>" data-default="<?php echo esc_attr($default_settings['add_word']['text_color']); ?>">
        <p class="description"><?php esc_html_e('Select the text color for the "Add Word" button.', 'wp-quiz-plugin'); ?></p>
        <button type="button" class="button-secondary kw-reset-button" data-label-default="<?php echo esc_js($default_settings['add_word']['label']); ?>" data-color-default="<?php echo esc_js($default_settings['add_word']['color']); ?>" data-text-color-default="<?php echo esc_js($default_settings['add_word']['text_color']); ?>">
            <?php esc_html_e('Reset to Default', 'wp-quiz-plugin'); ?>
        </button>
    </div>

    <!-- Repeat similar fields for other buttons -->
    <?php /* foreach (['clear_list', 'shuffle', 'download_pdf', 'download_key'] as $key): */ ?>
    <?php foreach (['clear_list', 'shuffle'] as $key): ?>
    <div class="kw-settings-field">
        <label for="kw_wordsearch_admin_<?php echo esc_attr($key); ?>_button_label" class="kw-label">
            <?php esc_html_e(ucwords(str_replace('_', ' ', $key)) . ' Button Label', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_wordsearch_admin_<?php echo esc_attr($key); ?>_button_label" name="kw_wordsearch_admin_<?php echo esc_attr($key); ?>_button_label" class="regular-text kw-input" value="<?php echo esc_attr($settings[$key]['label']); ?>" data-default="<?php echo esc_attr($default_settings[$key]['label']); ?>">
        <p class="description"><?php esc_html_e("Set the label for the \"$key\" button.", 'wp-quiz-plugin'); ?></p>

        <label for="kw_wordsearch_admin_<?php echo esc_attr($key); ?>_button_color" class="kw-label">
            <?php esc_html_e('Button Background Color', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_wordsearch_admin_<?php echo esc_attr($key); ?>_button_color" name="kw_wordsearch_admin_<?php echo esc_attr($key); ?>_button_color" class="kw-color-picker wp-color-picker" value="<?php echo esc_attr($settings[$key]['color']); ?>" data-default="<?php echo esc_attr($default_settings[$key]['color']); ?>">
        <p class="description"><?php esc_html_e("Select the background color for the \"$key\" button.", 'wp-quiz-plugin'); ?></p>

        <label for="kw_wordsearch_admin_<?php echo esc_attr($key); ?>_button_text_color" class="kw-label">
            <?php esc_html_e('Button Text Color', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_wordsearch_admin_<?php echo esc_attr($key); ?>_button_text_color" name="kw_wordsearch_admin_<?php echo esc_attr($key); ?>_button_text_color" class="kw-color-picker wp-color-picker" value="<?php echo esc_attr($settings[$key]['text_color']); ?>" data-default="<?php echo esc_attr($default_settings[$key]['text_color']); ?>">
        <p class="description"><?php esc_html_e("Select the text color for the \"$key\" button.", 'wp-quiz-plugin'); ?></p>
        <button type="button" class="button-secondary kw-reset-button" data-label-default="<?php echo esc_js($default_settings[$key]['label']); ?>" data-color-default="<?php echo esc_js($default_settings[$key]['color']); ?>" data-text-color-default="<?php echo esc_js($default_settings[$key]['text_color']); ?>">
            <?php esc_html_e('Reset to Default', 'wp-quiz-plugin'); ?>
        </button>
    </div>
    <?php endforeach; ?>

    <!-- Show Answers Checkbox -->
    <div class="kw-settings-field">
        <label for="kw_wordsearch_admin_show_answers_checkbox_label" class="kw-label">
            <?php esc_html_e('Show Answers Checkbox Label', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_wordsearch_admin_show_answers_checkbox_label" name="kw_wordsearch_admin_show_answers_checkbox_label" class="regular-text kw-input" 
            value="<?php echo esc_attr(get_option('kw_wordsearch_admin_show_answers_checkbox_label', __('Show Answers', 'wp-quiz-plugin'))); ?>" 
            >
        <p class="description"><?php esc_html_e('Set the label for the "Show Answers" checkbox.', 'wp-quiz-plugin'); ?></p>
    </div>

    <div class="kw-settings-field">
        <label for="kw_admin_show_words_checkbox_label" class="kw-label">
            <?php esc_html_e('Show Words Label', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_admin_show_words_checkbox_label" name="kw_admin_show_words_checkbox_label" class="regular-text kw-input" 
            value="<?php echo esc_attr(get_option('kw_admin_show_words_checkbox_label', __('Show Words', 'wp-quiz-plugin'))); ?>" 
            >
        <p class="description"><?php esc_html_e('Set the label for the "Show Words" checkbox.', 'wp-quiz-plugin'); ?></p>
    </div>

    

</div>

<script>
document.querySelectorAll('.kw-reset-button').forEach(button => {
    button.addEventListener('click', function () {
        const parentField = this.closest('.kw-settings-field');
        const labelField = parentField.querySelector('input[type="text"]');
        const colorField = parentField.querySelector('.kw-color-picker');

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
