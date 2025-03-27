<?php
// Ensure this file is loaded in the correct context
if (!defined('ABSPATH')) {
    exit;
}

// Default values for Frontend UI settings
$default_settings = [
    'kw_grid_even_cell_bg_color'   => "#ecd8b3",  // Filled Cell Background
    'kw_grid_odd_cell_bg_color'   => '#f5e9d1',
    'kw_grid_text_font_color'      => '#5c4012',      
    'kw_grid_text_font_family'      => 'Georgia, serif',  
    'kw_highlight_cell_text_color' => '#ffffff',               // Highlight Cell Background
    'kw_grid_line_color'     => '#b8860b99',  // Highlighted Cell Background
    'kw_wordsearch_cell_font_color'=> '#5c4012',  
    'kw_wordsearch_admin_filled_cell_color'  => '#fffff',                        // Cell Font Color
];

// Retrieve saved values or use defaults
$settings = [];
foreach ($default_settings as $key => $default_value) {
    $settings[$key] = get_option( $key, $default_value );
}
error_log("Color" . print_r($default_settings['kw_grid_even_cell_bg_color'],true));
?>

<div class="kw-settings-section">
    <h2 class="kw-section-heading"><?php esc_html_e( 'Frontend UI Settings', 'wp-quiz-plugin' ); ?></h2>
    <hr>

    <!-- Filled Cell Background Color -->
    <div class="kw-settings-field">
        <label for="kw_grid_even_cell_bg_color">
            <?php esc_html_e( 'Grid Even Cell Background Color', 'wp-quiz-plugin' ); ?>
        </label>
        <input type="text"
               id="kw_grid_even_cell_bg_color"
               name="kw_grid_even_cell_bg_color"
               class="kw-color-picker wp-color-picker"
               value="<?php echo esc_attr( $settings['kw_grid_even_cell_bg_color'] ); ?>"
               data-default="<?php echo esc_attr( $default_settings['kw_grid_even_cell_bg_color'] ); ?>">
    </div>

    <div class="kw-settings-field">
        <label for="kw_grid_odd_cell_bg_color">
            <?php esc_html_e( 'Grid Odd Cell Background Color', 'wp-quiz-plugin' ); ?>
        </label>
        <input type="text"
               id="kw_grid_odd_cell_bg_color"
               name="kw_grid_odd_cell_bg_color"
               class="kw-color-picker wp-color-picker"
               value="<?php echo esc_attr( $settings['kw_grid_odd_cell_bg_color'] ); ?>"
               data-default="<?php echo esc_attr( $default_settings['kw_grid_odd_cell_bg_color'] ); ?>">
    </div>

    <div class="kw-settings-field">
        <label for="kw_wordsearch_admin_filled_cell_color">
            <?php esc_html_e( 'Grid Selected Cell Background Color', 'wp-quiz-plugin' ); ?>
        </label>
        <input type="text"
               id="kw_wordsearch_admin_filled_cell_color"
               name="kw_wordsearch_admin_filled_cell_color"
               class="kw-color-picker wp-color-picker"
               value="<?php echo esc_attr( $settings['kw_wordsearch_admin_filled_cell_color'] ); ?>"
               data-default="<?php echo esc_attr( $default_settings['kw_wordsearch_admin_filled_cell_color'] ); ?>">
    </div>

        <!-- Grid Letters Text Color -->
        <div class="kw-settings-field">
        <label for="kw_grid_text_font_color">
            <?php esc_html_e( 'Grid Text Color', 'wp-quiz-plugin' ); ?>
        </label>
        <input type="text"
               id="kw_grid_text_font_color"
               name="kw_grid_text_font_color"
               class="kw-color-picker wp-color-picker"
               value="<?php echo esc_attr( $settings['kw_grid_text_font_color'] ); ?>"
               data-default="<?php echo esc_attr( $default_settings['kw_grid_text_font_color'] ); ?>">
    </div>

     <!--Grid Text Font Family -->
    <div class="kw-settings-field">
            <label for="kw_grid_text_font_family"><?php esc_html_e('Grid Text Font Family', 'wp-quiz-plugin'); ?></label>
            <select id="kw_grid_text_font_family" name="kw_grid_text_font_family" class="regular-select">
                <?php
                $font_family_options = [
                    'Arial', 'Roboto' ,'Helvetica', 'Times New Roman', 'Courier New', 'Georgia',
                    'Verdana', 'Trebuchet MS', 'Lucida Sans',
                ];
                foreach ($font_family_options as $font_family) :
                    ?>
                    <option value="<?php echo esc_attr($font_family); ?>"
                        <?php selected(get_option('kw_grid_text_font_family', $default_settings['kw_grid_text_font_family']), $font_family); ?>>
                        <?php echo esc_html($font_family); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>


    <!-- Highlight Cell Background Color -->
    <div class="kw-settings-field">
        <label for="kw_highlight_cell_text_color">
            <?php esc_html_e( 'Highlight Cell Text Color', 'wp-quiz-plugin' ); ?>
        </label>
        <input type="text"
               id="kw_highlight_cell_text_color"
               name="kw_highlight_cell_text_color"
               class="kw-color-picker wp-color-picker"
               value="<?php echo esc_attr( $settings['kw_highlight_cell_text_color'] ); ?>"
               data-default="<?php echo esc_attr( $default_settings['kw_highlight_cell_text_color'] ); ?>">
    </div>

    <!-- Highlighted Cell Background Color -->
    <div class="kw-settings-field">
        <label for="kw_grid_line_color">
            <?php esc_html_e( 'Highlighted Line Background Color', 'wp-quiz-plugin' ); ?>
        </label>
        <input type="text"
               id="kw_grid_line_color"
               name="kw_grid_line_color"
               class="kw-color-picker wp-color-picker"
               value="<?php echo esc_attr( $settings['kw_grid_line_color'] ); ?>"
               data-default="<?php echo esc_attr( $default_settings['kw_grid_line_color'] ); ?>"
               data-alpha-enabled="true"
               data-alpha-color-type="octohex"
               >
    </div>

    <!-- Reset Button -->
    <button type="button" class="button-secondary kw-reset-button">
        <?php esc_html_e( 'Reset to Default', 'wp-quiz-plugin' ); ?>
    </button>
</div>