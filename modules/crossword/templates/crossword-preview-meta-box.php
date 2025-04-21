<?php
// Fetch existing words and clues from the post meta
$grid_data = get_post_meta($post->ID, '_crossword_grid_data', true);

// Retrieve settings for buttons and checkbox
$default_settings = [
    'shuffle' => [
        'label' => __('Shuffle', 'wp-quiz-plugin'),
        'bg_color' => '#0073aa',
        'text_color' => '#ffffff',
    ],
    'download_pdf' => [
        'label' => __('Download as PDF', 'wp-quiz-plugin'),
        'bg_color' => '#0073aa',
        'text_color' => '#ffffff',
    ],
    'download_key' => [
        'label' => __('Download Key', 'wp-quiz-plugin'),
        'bg_color' => '#0073aa',
        'text_color' => '#ffffff',
    ],
    'show_answers' => [
        'label' => __('Show Answers', 'wp-quiz-plugin'),
    ],
];

// Retrieve saved settings or use defaults
$settings = [];
foreach ($default_settings as $key => $values) {
    $settings[$key]['label'] = get_option("kw_crossword_admin_{$key}_button_label", $values['label']);
    if (isset($values['bg_color'])) {
        $settings[$key]['bg_color'] = get_option("kw_crossword_admin_{$key}_button_color", $values['bg_color']);
        $settings[$key]['text_color'] = get_option("kw_crossword_admin_{$key}_button_text_color", $values['text_color']);
    }
}
?>

<div class="be-crossword-preview-action-container">
    <!-- Shuffle Button -->
    <span id="shuffle-button" class="cross-word-primary-button" style="background-color: <?php echo esc_attr($settings['shuffle']['bg_color']); ?>; color: <?php echo esc_attr($settings['shuffle']['text_color']); ?>;">
        <?php echo esc_html__($settings['shuffle']['label'], 'wp-quiz-plugin'); ?>
    </span>

    <?php if(!empty($grid_data)):?>
        
        <!-- Download as PDF Button -->
        <span id="download-pdf-button" class="cross-word-primary-button" data-crossword-id="<?php echo esc_attr($post->ID); ?>" style="background-color: <?php echo esc_attr($settings['download_pdf']['bg_color']); ?>; color: <?php echo esc_attr($settings['download_pdf']['text_color']); ?>;">
            <?php echo esc_html__($settings['download_pdf']['label'],'wp-quiz-plugin'); ?>
        </span>

        <!-- Download Key Button -->
        <span id="crossword-download-key" class="cross-word-primary-button" data-crossword-id="<?php echo esc_attr($post->ID); ?>" style="background-color: <?php echo esc_attr($settings['download_key']['bg_color']); ?>; color: <?php echo esc_attr($settings['download_key']['text_color']); ?>;">
            <?php echo esc_html__($settings['download_key']['label'],'wp-quiz-plugin'); ?>
        </span>

    <?php endif;?>
    <!-- Show Answers Checkbox -->
    <label>
        <input type="checkbox" id="toggle-answers">
        <?php  echo esc_attr__(get_option('kw_crossword_admin_show_answers_checkbox_label','Show Answers'),'wp-quiz-plugin'); ?>
    </label>
</div>

<!-- Error Message Display -->
<div id="error-message" style="display: none; color: red;"></div>

<!-- Hidden Fields -->
<input type="hidden" id="crossword-data" name="crossword_data" value="<?php echo $grid_data ? esc_attr($grid_data) : ''; ?>">

<!-- Crossword Container -->
<div id="crossword-container" class="preview-container">
    <!-- Crossword Grid Container -->
    <div id="crossword-grid">
        <?php esc_html_e('Please add some words to generate the crossword.', 'wp-quiz-plugin'); ?>
    </div>

    <!-- Clues Container -->
    <div id="clues-container"></div>
</div>

<!-- Nonce Field for Security -->
<?php wp_nonce_field('crossword_save_meta_box_data', 'crossword_meta_box_nonce'); ?>
