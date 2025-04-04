<div class="kw-settings-section">
    <h2><?php esc_html_e('Open In New Tab Action Button', 'wp-quiz-plugin'); ?></h2>

    <?php
    // Default values for Clue Image Settings
    $default_settings = [
        'openTabLabel' => __('Open in New Tab', 'wp-quiz-plugin'),
        'copyUrlLabel' => __('Copy URL to Clipboard', 'wp-quiz-plugin'),
        'emailLabel'   => __('Share via Email', 'wp-quiz-plugin'),
        'fontSize'     => "16",
        'fontColor'   => "#007BFF",
    ];

    $word_search_action_button_settings = [
        'openTabLabel' => esc_html(get_option('wordsearch_open_tab_button_label', $default_settings['openTabLabel'])),
        'openTabFontSize' => get_option('wordsearch_open_tab_button_font_size', $default_settings['fontSize']),
        'openTabFontColor' => get_option('wordsearch_open_tab_button_color',$default_settings ['fontColor']),
        'copyUrlLabel' => esc_html(get_option('wordsearch_copy_url_button_label', $default_settings['copyUrlLabel'])),
        'copyUrlFontSize' => get_option('wordsearch_copy_url_button_font_size', $default_settings['fontSize']),
        'copyUrlFontColor' => get_option('wordsearch_copy_url_button_color',$default_settings ['fontColor']),
        'emailLabel' => esc_html(get_option('wordsearch_share_email_button_label', $default_settings['emailLabel'])),
        'emailFontSize' => get_option('wordsearch_share_email_button_font_size', $default_settings['fontSize']),
        'emailFontColor' => get_option('wordsearch_share_email_button_color',$default_settings ['fontColor']),
    ];


    ?>

    <!-- Title Field -->
    <div class="kw-settings-field">
        <label for="wordsearch_open_tab_button_label" class="kw-label">
            <?php esc_html_e('Open In New Tab label', 'wp-quiz-plugin'); ?>
        </label>
        <input 
            type="text" 
            id="wordsearch_open_tab_button_label" 
            name="wordsearch_open_tab_button_label" 
            class="regular-text kw-input" 
            value="<?php echo esc_attr($word_search_action_button_settings['openTabLabel']); ?>" 
            data-default="<?php echo esc_attr($default_settings['openTabLabel']); ?>"
        >
        <p class="description"><?php esc_html_e('Set the label for the open tab action button.', 'wp-quiz-plugin'); ?></p>
    </div>

    <!-- Font Size Field -->
    <div class="kw-settings-field">
        <label for="wordsearch_open_tab_button_font_size" class="kw-label">
            <?php esc_html_e('Font Size', 'wp-quiz-plugin'); ?>
        </label>
        <input 
            type="text" 
            id="wordsearch_open_tab_button_font_size" 
            name="wordsearch_open_tab_button_font_size" 
            class="regular-text kw-input" 
            value="<?php echo esc_attr($word_search_action_button_settings['openTabFontSize']); ?>" 
            data-default="<?php echo esc_attr($default_settings['fontSize']); ?>"
        >
        <p class="description"><?php esc_html_e('Set the font size for the open tab action button.', 'wp-quiz-plugin'); ?></p>
    </div>
    <!-- Font Color Field -->
    <div class="kw-settings-field">
        <label for="wordsearch_open_tab_button_color" class="kw-label">
            <?php esc_html_e('Font Color', 'wp-quiz-plugin'); ?>
        </label>
        <input 
            type="text" 
            id="wordsearch_open_tab_button_color" 
            name="wordsearch_open_tab_button_color" 
            class="kw-color-picker wp-color-picker" 
            value="<?php echo esc_attr($word_search_action_button_settings['openTabFontColor']); ?>" 
            data-default="<?php echo esc_attr($default_settings['fontColor']); ?>"
            data-alpha-enabled="true"
            data-alpha-color-type="octohex"
        >
        <p class="description"><?php esc_html_e('Set the font color for the open tab action button.', 'wp-quiz-plugin'); ?></p>
    </div>

    <h2><?php esc_html_e('Copy Url Action Button', 'wp-quiz-plugin'); ?></h2>
    <!-- Title Field -->
    <div class="kw-settings-field">
        <label for="wordsearch_copy_url_button_label" class="kw-label">
            <?php esc_html_e('Copy URL label', 'wp-quiz-plugin'); ?>
        </label>
        <input 
            type="text" 
            id="wordsearch_copy_url_button_label" 
            name="wordsearch_copy_url_button_label" 
            class="regular-text kw-input" 
            value="<?php echo esc_attr($word_search_action_button_settings['copyUrlLabel']); ?>" 
            data-default="<?php echo esc_attr($default_settings['copyUrlLabel']); ?>"
        >
        <p class="description"><?php esc_html_e('Set the label for the copy URL action button.', 'wp-quiz-plugin'); ?></p>
    </div>
    <!-- Font Size Field -->
    <div class="kw-settings-field">
        <label for="wordsearch_copy_url_button_font_size" class="kw-label">
            <?php esc_html_e('Font Size', 'wp-quiz-plugin'); ?>
        </label>
        <input 
            type="text" 
            id="wordsearch_copy_url_button_font_size" 
            name="wordsearch_copy_url_button_font_size" 
            class="regular-text kw-input" 
            value="<?php echo esc_attr($word_search_action_button_settings['copyUrlFontSize']); ?>" 
            data-default="<?php echo esc_attr($default_settings['fontSize']); ?>"
        >
        <p class="description"><?php esc_html_e('Set the font size for the copy URL action button.', 'wp-quiz-plugin'); ?></p>
    </div>
    <!-- Font Color Field -->
    <div class="kw-settings-field">
        <label for="wordsearch_copy_url_button_font_color" class="kw-label">
            <?php esc_html_e('Font Color', 'wp-quiz-plugin'); ?>
        </label>
        <input 
            type="text" 
            id="wordsearch_copy_url_button_color" 
            name="wordsearch_copy_url_button_color" 
            class="kw-color-picker wp-color-picker" 
            value="<?php echo esc_attr($word_search_action_button_settings['copyUrlFontColor']); ?>" 
            data-default="<?php echo esc_attr($default_settings['fontColor']); ?>"
            data-alpha-enabled="true"
            data-alpha-color-type="octohex"
        >
        <p class="description"><?php esc_html_e('Set the font color for the copy URL action button.', 'wp-quiz-plugin'); ?></p>
    </div>

    <h2><?php esc_html_e('Share Email Action Button', 'wp-quiz-plugin'); ?></h2>
    <!-- Title Field -->
    <div class="kw-settings-field">
        <label for="wordsearch_share_email_button_label" class="kw-label">
            <?php esc_html_e('Share Email label', 'wp-quiz-plugin'); ?>
        </label>
        <input 
            type="text" 
            id="wordsearch_share_email_button_label" 
            name="wordsearch_share_email_button_label" 
            class="regular-text kw-input" 
            value="<?php echo esc_attr($word_search_action_button_settings['emailLabel']); ?>" 
            data-default="<?php echo esc_attr($default_settings['emailLabel']); ?>"
        >
        <p class="description"><?php esc_html_e('Set the label for the share email action button.', 'wp-quiz-plugin'); ?></p>
    </div>
    <!-- Font Size Field -->
    <div class="kw-settings-field">
        <label for="wordsearch_share_email_button_font_size" class="kw-label">
            <?php esc_html_e('Font Size', 'wp-quiz-plugin'); ?>
        </label>
        <input 
            type="text" 
            id="wordsearch_share_email_button_font_size" 
            name="wordsearch_share_email_button_font_size" 
            class="regular-text kw-input" 
            value="<?php echo esc_attr($word_search_action_button_settings['emailFontSize']); ?>" 
            data-default="<?php echo esc_attr($default_settings['fontSize']); ?>"
        >
        <p class="description"><?php esc_html_e('Set the font size for the share email action button.', 'wp-quiz-plugin'); ?></p>
    </div>
    <!-- Font Color Field -->
    <div class="kw-settings-field">
        <label for="wordsearch_share_email_button_font_color" class="kw-label">
            <?php esc_html_e('Font Color', 'wp-quiz-plugin'); ?>
        </label>
        <input 
            type="text" 
            id="wordsearch_share_email_button_color" 
            name="wordsearch_share_email_button_color" 
            class="kw-color-picker wp-color-picker" 
            value="<?php echo esc_attr($word_search_action_button_settings['emailFontColor']); ?>" 
            data-default="<?php echo esc_attr($default_settings['fontColor']); ?>"
            data-alpha-enabled="true"
            data-alpha-color-type="octohex"
        >
        <p class="description"><?php esc_html_e('Set the font color for the share email action button.', 'wp-quiz-plugin'); ?></p>
    </div>


    <!-- Reset Button -->
    <button type="button" class="button-secondary kw-reset-button">
        <?php esc_html_e('Reset to Default', 'wp-quiz-plugin'); ?>
    </button>

</div>

