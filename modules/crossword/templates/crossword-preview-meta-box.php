<?php
// Fetch existing words and clues from the post meta
$words_clues = get_post_meta($post->ID, '_crossword_words_clues', true);

if (empty($words_clues) || !is_array($words_clues)) {
    $words_clues = [];
}
?>

<div class="crossword-preview-container">
    <h2><?php echo esc_html(get_the_title($post->ID)); ?></h2>
    <div id="crossword-grid" class="crossword-grid">
        <!-- The crossword grid will be rendered here -->
    </div>
</div>
