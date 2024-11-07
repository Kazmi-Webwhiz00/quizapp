<?php
// Fetch existing words and clues from the post meta
$grid_data = get_post_meta($post->ID, '_crossword_grid_data', true);
?>

<div  class="be-crossword-preview-action-container">
    <span id="shuffle-button" class="cross-word-primary-button">Shuffle</span>
    <span id="download-pdf-button" class="cross-word-primary-button" data-crossword-id="<?php echo esc_attr($post->ID); ?>">Download as PDF</span>
    <span id="crossword-download-key" class="cross-word-primary-button" data-crossword-id="<?php echo esc_attr($post->ID); ?>">Download Key</span>
    <!-- Controls -->
    <label>
        <input type="checkbox" id="toggle-answers"> Show Answers
    </label>
</div>

<!-- Error Message Display -->
<div id="error-message" style="display: none; color: red;"></div>

<!-- Hidden Fields -->
<input type="hidden" id="crossword-data" name="crossword_data" value="<?php echo $grid_data ? esc_attr($grid_data) : ''; ?>">

<!-- Crossword Container -->
<div id="crossword-container">
    <!-- Crossword Grid Container -->
    <div id="crossword-grid"></div>

    <!-- Clues Container -->
    <div id="clues-container"></div>
</div>

<!-- Nonce Field for Security -->
<?php wp_nonce_field('crossword_save_meta_box_data', 'crossword_meta_box_nonce'); ?>
