<?php
// Fetch existing words and clues from the post meta
$words_clues = get_post_meta($post->ID, '_crossword_words_clues', true);

if (empty($words_clues) || !is_array($words_clues)) {
    $words_clues = [];
}
?>

<!-- Error Message Display -->
<div id="error-message" style="display: none; color: red;"></div>

<!-- Crossword Grid Container -->
<div id="crossword-grid"></div>

<!-- Clues Container -->
<div id="clues-container"></div>

<!-- Controls -->
<label>
    <input type="checkbox" id="toggle-answers"> Show Answers
</label>
<button id="shuffle-button">Shuffle</button>


