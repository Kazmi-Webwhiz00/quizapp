<?php
// Ensure this file is loaded in the correct context
if (!defined('ABSPATH')) {
    exit;
}

// Default values for Frontend UI settings
$default_settings = [
    'restart_button' => [
        'color' => '#00796b',
        'text_color' => '#ffffff',
        'font_size' => '16px',
        'font_family' => 'Arial',
    ],
    'download_button' => [
        'text' => __('Download', 'wp-quiz-plugin'),
        'bg_color' => '#00796b',
        'text_color' => '#ffffff',
        'font_size' => '16px',
        'font_family' => 'Arial',
    ],
    'check_crossword_button' => [
        'text' => __('Check Crossword', 'wp-quiz-plugin'),
        'bg_color' => '#00796b',
        'text_color' => '#ffffff',
        'font_size' => '16px',
        'font_family' => 'Arial',
    ],
    'enable_live_word_check_button' => [
        'text' => __('Enable Live Word Check', 'wp-quiz-plugin'),
        'bg_color' => '#ffffff',
        'text_color' => '#000000',
        'enabled_color' => '#00796b',
        'font_size' => '16px',
        'font_family' => 'Arial',
    ],
    'filled_cell' => [
        'bg_color' => '#e1f5fe', // Default background color for filled cells
    ],
    'corrected_cell' => [
        'bg_color' => '#d4edda', // Default background color for corrected cells
    ],
    'wrong_cell' => [
        'bg_color' => '#d66868', // Default background color for wrong cells
    ],
];

// Dropdown font-family options
$font_family_options = [
    'Arial',
    'Helvetica',
    'Times New Roman',
    'Courier New',
    'Georgia',
    'Verdana',
    'Trebuchet MS',
    'Lucida Sans',
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
    <div class="kw-settings-section">
        <h3><?php esc_html_e('Restart Button Settings', 'wp-quiz-plugin'); ?></h3>
        <div class="kw-settings-field">
            <label for="kw_fe_restart_button_color"><?php esc_html_e('Background Color', 'wp-quiz-plugin'); ?></label>
            <input type="text" id="kw_fe_restart_button_color" name="kw_fe_restart_button_color" class="kw-color-picker wp-color-picker"
                value="<?php echo esc_attr($settings['restart_button']['color']); ?>" 
                data-default="<?php echo esc_attr($default_settings['restart_button']['color']); ?>">
        </div>
        <div class="kw-settings-field">
            <label for="kw_fe_restart_button_text_color"><?php esc_html_e('Text Color', 'wp-quiz-plugin'); ?></label>
            <input type="text" id="kw_fe_restart_button_text_color" name="kw_fe_restart_button_text_color" class="kw-color-picker wp-color-picker" 
                value="<?php echo esc_attr($settings['restart_button']['text_color']); ?>" 
                data-default="<?php echo esc_attr($default_settings['restart_button']['text_color']); ?>">
        </div>
        <div class="kw-settings-field">
            <label for="kw_fe_restart_button_font_size"><?php esc_html_e('Font Size (px)', 'wp-quiz-plugin'); ?></label>
            <input type="number" id="kw_fe_restart_button_font_size" name="kw_fe_restart_button_font_size" class="regular-text"
                value="<?php echo intval($settings['restart_button']['font_size']); ?>" 
                data-default="<?php echo intval($default_settings['restart_button']['font_size']); ?>"> px
        </div>
        <div class="kw-settings-field">
            <label for="kw_fe_restart_button_font_family"><?php esc_html_e('Font Family', 'wp-quiz-plugin'); ?></label>
            <select id="kw_fe_restart_button_font_family" name="kw_fe_restart_button_font_family" class="regular-select">
                <?php foreach ($font_family_options as $font): ?>
                    <option value="<?php echo esc_attr($font); ?>" <?php selected($settings['restart_button']['font_family'], $font); ?>>
                        <?php echo esc_html($font); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Group similar settings for Download Button, Check Crossword Button, Enable Live Word Check Button -->
    <?php foreach (['download_button', 'check_crossword_button', 'enable_live_word_check_button'] as $button_key): ?>
        <div class="kw-settings-section">
            <h3><?php esc_html_e(ucwords(str_replace('_', ' ', $button_key)) . ' Settings', 'wp-quiz-plugin'); ?></h3>
            <?php foreach ($default_settings[$button_key] as $sub_key => $default_value): ?>
                <div class="kw-settings-field">
                    <label for="kw_fe_<?php echo esc_attr($button_key . '_' . $sub_key); ?>">
                        <?php echo esc_html(ucwords(str_replace('_', ' ', $sub_key))); ?>
                    </label>
                    <?php if ($sub_key === 'font_family'): ?>
                        <select id="kw_fe_<?php echo esc_attr($button_key . '_' . $sub_key); ?>" 
                            name="kw_fe_<?php echo esc_attr($button_key . '_' . $sub_key); ?>" 
                            class="regular-select">
                            <?php foreach ($font_family_options as $font): ?>
                                <option value="<?php echo esc_attr($font); ?>" <?php selected($settings[$button_key][$sub_key], $font); ?>>
                                    <?php echo esc_html($font); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif ($sub_key === 'font_size'): ?>
                        <input type="number" id="kw_fe_<?php echo esc_attr($button_key . '_' . $sub_key); ?>" 
                            name="kw_fe_<?php echo esc_attr($button_key . '_' . $sub_key); ?>" 
                            class="regular-text"
                            value="<?php echo intval($settings[$button_key][$sub_key]); ?>"
                            data-default="<?php echo intval($default_settings[$button_key][$sub_key]); ?>"> px
                    <?php else: ?>
                        <input type="text" id="kw_fe_<?php echo esc_attr($button_key . '_' . $sub_key); ?>" 
                            name="kw_fe_<?php echo esc_attr($button_key . '_' . $sub_key); ?>" 
                            class="<?php echo strpos($sub_key, 'color') !== false ? 'kw-color-picker wp-color-picker' : 'regular-text'; ?>"
                            value="<?php echo esc_attr($settings[$button_key][$sub_key]); ?>"
                            data-default="<?php echo esc_attr($default_settings[$button_key][$sub_key]); ?>">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <!-- Additional Settings -->
    <div class="kw-settings-section">
    <h3><?php esc_html_e('Cell and Clue Settings', 'wp-quiz-plugin'); ?></h3>
    
    <!-- Existing Cell Background Colors -->
    <?php foreach (['filled_cell', 'corrected_cell', 'wrong_cell'] as $cell_key): ?>
        <div class="kw-settings-field">
            <label for="kw_fe_<?php echo esc_attr($cell_key . '_bg_color'); ?>">
                <?php esc_html_e(ucwords(str_replace('_', ' ', $cell_key)) . ' Background Color', 'wp-quiz-plugin'); ?>
            </label>
            <input type="text" id="kw_fe_<?php echo esc_attr($cell_key . '_bg_color'); ?>" 
                name="kw_fe_<?php echo esc_attr($cell_key . '_bg_color'); ?>" 
                class="kw-color-picker wp-color-picker"
                value="<?php echo esc_attr($settings[$cell_key]['bg_color']); ?>"
                data-default="<?php echo esc_attr($default_settings[$cell_key]['bg_color']); ?>">
        </div>
    <?php endforeach; ?>

    <!-- Highlight Cell Background Color -->
    <div class="kw-settings-field">
        <label for="kw_crossword_highlight_cell_color">
            <?php esc_html_e('Highlight Cell Background Color', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_crossword_highlight_cell_color" 
            name="kw_crossword_highlight_cell_color" 
            class="kw-color-picker wp-color-picker"
            value="<?php echo esc_attr(get_option('kw_crossword_highlight_cell_color', 'yellow')); ?>"
            data-default="yellow">
    </div>

    <!-- Cell Font Color -->
    <div class="kw-settings-field">
        <label for="kw_crossword_cell_font_color">
            <?php esc_html_e('Cell Font Color', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_crossword_cell_font_color" 
            name="kw_crossword_cell_font_color" 
            class="kw-color-picker wp-color-picker"
            value="<?php echo esc_attr(get_option('kw_crossword_cell_font_color', 'black')); ?>"
            data-default="black">
    </div>

    <!-- Clue Font Color -->
    <div class="kw-settings-field">
        <label for="kw_crossword_cell_clue_font_color">
            <?php esc_html_e('Clue Font Color', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_crossword_cell_clue_font_color" 
            name="kw_crossword_cell_clue_font_color" 
            class="kw-color-picker wp-color-picker"
            value="<?php echo esc_attr(get_option('kw_crossword_cell_clue_font_color', 'black')); ?>"
            data-default="black">
    </div>

    <!-- Cell Border Color -->
    <div class="kw-settings-field">
        <label for="kw_crossword_cell_border_color">
            <?php esc_html_e('Cell Border Color', 'wp-quiz-plugin'); ?>
        </label>
        <input type="text" id="kw_crossword_cell_border_color" 
            name="kw_crossword_cell_border_color" 
            class="kw-color-picker wp-color-picker"
            value="<?php echo esc_attr(get_option('kw_crossword_cell_border_color', 'lightgrey')); ?>"
            data-default="lightgrey">
    </div>
</div>

</div>
