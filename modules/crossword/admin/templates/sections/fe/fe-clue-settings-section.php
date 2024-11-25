<div class="kw-settings-section">
    <h2><?php esc_html_e('Clues Settings', 'wp-quiz-plugin'); ?></h2>

    <?php
    // Default values for Clue settings
    $clue_defaults = [
        'kw_fe_clue_title_font_color' => '#000000',
        'kw_fe_clue_title_font_size' => 25,
        'kw_fe_clue_title_font_family' => 'Arial',
        'kw_fe_body_text_font_color' => 'rgb(85, 85, 85)',
        'kw_fe_body_text_font_size' => 16,
        'kw_fe_body_text_font_family' => 'Arial',
    ];
    ?>

    <!-- Clue Title Font Settings -->
    <div class="kw-settings-group">
        <h3><?php esc_html_e('Clue Title Settings', 'wp-quiz-plugin'); ?></h3>
        <div class="kw-settings-field">
            <label for="kw_fe_clue_title_font_color"><?php esc_html_e('Font Color', 'wp-quiz-plugin'); ?></label>
            <input type="text" id="kw_fe_clue_title_font_color" name="kw_fe_clue_title_font_color" class="kw-color-picker wp-color-picker"
                value="<?php echo esc_attr(get_option('kw_fe_clue_title_font_color', $clue_defaults['kw_fe_clue_title_font_color'])); ?>">
        </div>
        <div class="kw-settings-field">
            <label for="kw_fe_clue_title_font_size"><?php esc_html_e('Font Size (px)', 'wp-quiz-plugin'); ?></label>
            <input type="number" id="kw_fe_clue_title_font_size" name="kw_fe_clue_title_font_size" class="regular-text"
                value="<?php echo intval(get_option('kw_fe_clue_title_font_size', $clue_defaults['kw_fe_clue_title_font_size'])); ?>"> px
        </div>
        <div class="kw-settings-field">
            <label for="kw_fe_clue_title_font_family"><?php esc_html_e('Font Family', 'wp-quiz-plugin'); ?></label>
            <select id="kw_fe_clue_title_font_family" name="kw_fe_clue_title_font_family" class="regular-select">
                <?php
                $font_family_options = [
                    'Arial', 'Helvetica', 'Times New Roman', 'Courier New', 'Georgia',
                    'Verdana', 'Trebuchet MS', 'Lucida Sans',
                ];
                foreach ($font_family_options as $font_family) :
                    ?>
                    <option value="<?php echo esc_attr($font_family); ?>"
                        <?php selected(get_option('kw_fe_clue_title_font_family', $clue_defaults['kw_fe_clue_title_font_family']), $font_family); ?>>
                        <?php echo esc_html($font_family); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Body Text Font Settings -->
    <div class="kw-settings-group">
        <h3><?php esc_html_e('Body Text Settings', 'wp-quiz-plugin'); ?></h3>
        <div class="kw-settings-field">
            <label for="kw_fe_body_text_font_color"><?php esc_html_e('Font Color', 'wp-quiz-plugin'); ?></label>
            <input type="text" id="kw_fe_body_text_font_color" name="kw_fe_body_text_font_color" class="kw-color-picker wp-color-picker"
                value="<?php echo esc_attr(get_option('kw_fe_body_text_font_color', $clue_defaults['kw_fe_body_text_font_color'])); ?>">
        </div>
        <div class="kw-settings-field">
            <label for="kw_fe_body_text_font_size"><?php esc_html_e('Font Size (px)', 'wp-quiz-plugin'); ?></label>
            <input type="number" id="kw_fe_body_text_font_size" name="kw_fe_body_text_font_size" class="regular-text"
                value="<?php echo intval(get_option('kw_fe_body_text_font_size', $clue_defaults['kw_fe_body_text_font_size'])); ?>"> px
        </div>
        <div class="kw-settings-field">
            <label for="kw_fe_body_text_font_family"><?php esc_html_e('Font Family', 'wp-quiz-plugin'); ?></label>
            <select id="kw_fe_body_text_font_family" name="kw_fe_body_text_font_family" class="regular-select">
                <?php foreach ($font_family_options as $font_family) : ?>
                    <option value="<?php echo esc_attr($font_family); ?>"
                        <?php selected(get_option('kw_fe_body_text_font_family', $clue_defaults['kw_fe_body_text_font_family']), $font_family); ?>>
                        <?php echo esc_html($font_family); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Reset Button -->
    <div class="kw-settings-reset">
        <button type="button" class="kw-reset-button" id="reset-clue-settings"><?php esc_html_e('Reset to Default', 'wp-quiz-plugin'); ?></button>
    </div>
</div>

<script>
document.getElementById('reset-clue-settings').addEventListener('click', function () {
    // Default values map
    const defaultValues = {
        kw_fe_clue_title_font_color: '<?php echo esc_js($clue_defaults['kw_fe_clue_title_font_color']); ?>',
        kw_fe_clue_title_font_size: '<?php echo esc_js($clue_defaults['kw_fe_clue_title_font_size']); ?>',
        kw_fe_clue_title_font_family: '<?php echo esc_js($clue_defaults['kw_fe_clue_title_font_family']); ?>',
        kw_fe_body_text_font_color: '<?php echo esc_js($clue_defaults['kw_fe_body_text_font_color']); ?>',
        kw_fe_body_text_font_size: '<?php echo esc_js($clue_defaults['kw_fe_body_text_font_size']); ?>',
        kw_fe_body_text_font_family: '<?php echo esc_js($clue_defaults['kw_fe_body_text_font_family']); ?>',
    };

    // Reset fields to their default values
    for (const [key, value] of Object.entries(defaultValues)) {
        const element = document.getElementById(key);
        if (element) {
            if (element.tagName === 'INPUT') {
                element.value = value;
                if (element.classList.contains('wp-color-picker')) {
                    jQuery(element).wpColorPicker('color', value); // Update the color picker UI
                }
            } else if (element.tagName === 'SELECT') {
                element.value = value;
            }
        }
    }
});
</script>
