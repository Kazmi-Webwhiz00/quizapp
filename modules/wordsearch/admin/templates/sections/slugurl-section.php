<div class="kw-settings-section">
<h2><?php esc_html_e('Custom  Slug Settings', 'wp-quiz-plugin'); ?></h2>


    <div class="kw-settings-field">
    <div class="kw-settings-notice-box">
            <span class="kw-settings-icon">ⓘ</span>
            <div class="kw-settings-notice-content">
                <strong><?php esc_html_e('Note:', 'wp-quiz-plugin'); ?></strong>
                <?php
                $permalink_settings_url = admin_url('options-permalink.php');
                printf(
                    __('After changing the slug, go to <strong><a href="%s" target="_blank">Settings > Permalinks</a></strong> and click "Save Changes" to update.', 'wp-quiz-plugin'),
                    esc_url($permalink_settings_url)
                );
                ?>
            </div>
        </div>
        
        <label for="wordsearch_custom_url_slug"><?php esc_html_e('Custom URL Slug', 'wp-quiz-plugin'); ?></label>
        <?php $url_slug = get_option('wordsearch_custom_url_slug', 'wordsearch'); ?>
        <input type="text" id="wordsearch_custom_url_slug" name="wordsearch_custom_url_slug" value="<?php echo esc_attr($url_slug); ?>" class="regular-text">
        <p class="description"><?php esc_html_e('Set a custom URL slug for wordsearch. Example: "my-wordsearch".', 'wp-quiz-plugin'); ?></p>
    </div>
</div>