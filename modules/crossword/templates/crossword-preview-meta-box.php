<?php
// Fetch existing words and clues from the post meta
$words_clues = get_post_meta($post->ID, '_crossword_words_clues', true);

if (empty($words_clues) || !is_array($words_clues)) {
    $words_clues = [];
}
?>
<div style="padding: 20px 5px;">
<span id="shuffle-button" class="cross-word-primary-button">Shuffle</span>
<span id="download-pdf-button" class="cross-word-primary-button">Download as PDF</span>
</div>
<!-- Error Message Display -->
<div id="error-message" style="display: none; color: red;"></div>

<!-- Crossword Container -->
<div id="crossword-container">
    <!-- Crossword Grid Container -->
    <div id="crossword-grid"></div>

    <!-- Clues Container -->
    <div id="clues-container"></div>
</div>

<!-- Controls -->
<label>
    <input type="checkbox" id="toggle-answers"> Show Answers
</label>
