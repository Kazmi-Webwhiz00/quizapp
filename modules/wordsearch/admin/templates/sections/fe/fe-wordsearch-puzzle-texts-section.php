<div class="kw-settings-section">
    <h2><?php esc_html_e('Grid Text Settings', 'wp-quiz-plugin'); ?></h2>

    <?php
    // Default values for Clue Image Settings
    $default_settings = [
        'gridTitle'     => "Word Search Challenge",
        'findWordsLabel'   => "Find These Words:",
        'downloadPdfLabel' => "Download Pdf",
        'soundSetting'    => 0
    ];

    $grid_text_settings = [
        'gridTitle' => get_option('kw_grid_title_label', $default_settings['gridTitle']),
        'findWordsLabel' => get_option('kw_find_words_label',$default_settings ['findWordsLabel']),
        'downloadPdfLabel' => get_option('kw_download_pdf_label',$default_settings ['downloadPdfLabel']),
        'soundSetting'    =>    get_option('kw_grid_text_sound_setting',$default_settings ['soundSetting']),
    ];

    ?>

    <!-- Title Field -->
    <div class="kw-settings-field">
        <label for="kw_grid_title_label" class="kw-label">
            <?php esc_html_e('Grid Title', 'wp-quiz-plugin'); ?>
        </label>
        <input 
            type="text" 
            id="kw_grid_title_label" 
            name="kw_grid_title_label" 
            class="regular-text kw-input" 
            value="<?php echo esc_attr($grid_text_settings['gridTitle']); ?>" 
            data-default="<?php echo esc_attr($default_settings['gridTitle']); ?>"
        >
        <p class="description"><?php esc_html_e('Set the title for the grid title label.', 'wp-quiz-plugin'); ?></p>
    </div>

        <!-- Find Words Label -->
        <div class="kw-settings-field">
        <label for="kw_find_words_label" class="kw-label">
            <?php esc_html_e('Find Words Label', 'wp-quiz-plugin'); ?>
        </label>
        <input 
            type="text" 
            id="kw_find_words_label" 
            name="kw_find_words_label" 
            class="regular-text kw-input" 
            value="<?php echo esc_attr($grid_text_settings['findWordsLabel']); ?>" 
            data-default="<?php echo esc_attr($default_settings['findWordsLabel']); ?>"
        >
        <p class="description"><?php esc_html_e('Set the title for the find words label.', 'wp-quiz-plugin'); ?></p>
    </div>

    <div class="kw-settings-field">
        <label for="kw_download_pdf_label" class="kw-label">
            <?php esc_html_e('Download button Text', 'wp-quiz-plugin'); ?>
        </label>
        <input 
            type="text" 
            id="kw_download_pdf_label" 
            name="kw_download_pdf_label" 
            class="regular-text kw-input" 
            value="<?php echo esc_attr($grid_text_settings['downloadPdfLabel']); ?>" 
            data-default="<?php echo esc_attr($default_settings['downloadPdfLabel']); ?>"
        >
        <p class="description"><?php esc_html_e('Set the text for the download pdf button.', 'wp-quiz-plugin'); ?></p>
    </div>

    <div class="kw-settings-field">
    <label for="kw_grid_text_sound_setting" class="kw-label">
        <?php esc_html_e('Enable Grid Text Sound', 'wp-quiz-plugin'); ?>
    </label>

    <label class="kw-toggle-switch">
        <input 
            type="checkbox" 
            id="kw_grid_text_sound_setting" 
            name="kw_grid_text_sound_setting" 
            value="1"
            <?php checked( $grid_text_settings['soundSetting'], 1 ); ?>
            data-default="<?php echo esc_attr($default_settings['soundSetting']); ?>"
        >
        <span class="kw-slider"></span>
    </label>

    <p class="description"><?php esc_html_e('Toggle the grid text sound to on or off.', 'wp-quiz-plugin'); ?></p>
</div>

</div>

