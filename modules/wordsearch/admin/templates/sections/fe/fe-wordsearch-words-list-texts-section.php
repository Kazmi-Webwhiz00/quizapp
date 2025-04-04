<div class="kw-settings-section">
    <h2><?php esc_html_e('Word List Text Settings', 'wp-quiz-plugin'); ?></h2>

    <?php
    // Default values for Clue Image Settings
    $default_settings = [
        'fontSize'     => "14.4px",
        'fontColor'   => "#4a5568",
    ];

    $word_list_text_settings = [
        'fontSize' => get_option('kw_wordsearch_word_font_size', $default_settings['fontSize']),
        'fontColor' => get_option('kw_wordsearch_word_font_color',$default_settings ['fontColor']),
    ];


    ?>

    <!-- Title Field -->
    <div class="kw-settings-field">
        <label for="kw_grid_title_label" class="kw-label">
            <?php esc_html_e('Font Size', 'wp-quiz-plugin'); ?>
        </label>
        <input 
            type="text" 
            id="kw_wordsearch_word_font_size" 
            name="kw_wordsearch_word_font_size" 
            class="regular-text kw-input" 
            value="<?php echo esc_attr($word_list_text_settings['fontSize']); ?>" 
            data-default="<?php echo esc_attr($default_settings['fontSize']); ?>"
        >
        <p class="description"><?php esc_html_e('Set the title for the grid title label.', 'wp-quiz-plugin'); ?></p>
    </div>

        <!-- Words Listing Text Font Color -->
        <div class="kw-settings-field">
        <label for="kw_wordsearch_word_font_color">
            <?php esc_html_e( 'Font Color', 'wp-quiz-plugin' ); ?>
        </label>
        <input type="text"
               id="kw_wordsearch_word_font_color"
               name="kw_wordsearch_word_font_color"
               class="kw-color-picker wp-color-picker"
               value="<?php echo esc_attr( $word_list_text_settings['fontColor'] ); ?>"
               data-default="<?php echo esc_attr( $default_settings['fontColor'] ); ?>"
               data-alpha-enabled="true"
               data-alpha-color-type="octohex"
               >
    </div>


    <!-- Reset Button -->
    <button type="button" class="button-secondary kw-reset-button">
        <?php esc_html_e('Reset to Default', 'wp-quiz-plugin'); ?>
    </button>

</div>

