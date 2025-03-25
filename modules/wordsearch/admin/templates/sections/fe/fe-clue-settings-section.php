<div class="kw-settings-section">
    <h2><?php esc_html_e('WordSearch Image Settings', 'wp-quiz-plugin'); ?></h2>

    <?php
    // Default values for Clue Image Settings
    $clue_defaults = [
        'kw_word_search_image_height' => 120,
        'kw_word_search_image_width'  => 140,
    ];
    ?>

    <!-- WordSearch Image Settings -->
    <div class="kw-settings-group">
        <h3><?php esc_html_e('Image Settings', 'wp-quiz-plugin'); ?></h3>
        <div class="kw-settings-field">
            <label for="kw_word_search_image_height"><?php esc_html_e('Image Height (px)', 'wp-quiz-plugin'); ?></label>
            <input type="number" id="kw_word_search_image_height" name="kw_word_search_image_height" class="regular-text"
                value="<?php echo intval(get_option('kw_word_search_image_height', $clue_defaults['kw_word_search_image_height'])); ?>"> px
        </div>
        <div class="kw-settings-field">
            <label for="kw_word_search_image_width"><?php esc_html_e('Image Width (px)', 'wp-quiz-plugin'); ?></label>
            <input type="number" id="kw_word_search_image_width" name="kw_word_search_image_width" class="regular-text"
                value="<?php echo intval(get_option('kw_word_search_image_width', $clue_defaults['kw_word_search_image_width'])); ?>"> px
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
    const defaultValues = <?php echo json_encode($clue_defaults); ?>;

    // Reset fields to their default values
    for (const [key, value] of Object.entries(defaultValues)) {
        const element = document.getElementById(key);
        if (element) {
            element.value = value;
        }
    }
});
</script>
