<?php
// Fetch existing words and clues
$words_clues = get_post_meta($post->ID, '_crossword_words_clues', true);
if (empty($words_clues)) {
    $words_clues = [];
}
?>

<div id="crossword-words-clues-container">
    <?php foreach ($words_clues as $index => $entry) : ?>
        <div class="crossword-word-clue" data-index="<?php echo $index; ?>">
            <span class="word-number"><?php echo $index + 1; ?>.</span>
            <input type="text" name="crossword_words[<?php echo $index; ?>][word]" placeholder="Word" value="<?php echo esc_attr($entry['word']); ?>" />
            <input type="text" name="crossword_words[<?php echo $index; ?>][clue]" placeholder="Clue" value="<?php echo esc_attr($entry['clue']); ?>" />
            <input type="file" name="crossword_words[<?php echo $index; ?>][image]" accept="image/*" />
            <button type="button" class="remove-word">
            <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="trash" class="svg-inline--fa fa-trash fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="20" height="20">
                <path fill="currentColor" d="M135.2 176c-8.5 0-15.2 6.9-15.2 15.2v224c0 8.5 6.9 15.2 15.2 15.2h18.5c8.5 0 15.2-6.9 15.2-15.2v-224c0-8.5-6.9-15.2-15.2-15.2h-18.5zM319.7 176c-8.5 0-15.2 6.9-15.2 15.2v224c0 8.5 6.9 15.2 15.2 15.2h18.5c8.5 0 15.2-6.9 15.2-15.2v-224c0-8.5-6.9-15.2-15.2-15.2h-18.5zM432 32H312l-9.4-18.8C295.5 5.4 286.6 0 276.6 0H171.5c-10 0-18.8 5.4-26 13.2L135.2 32H16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h416c8.8 0 16-7.2 16-16V48c0-8.8-7.2-16-16-16zM53.2 472.1c1.8 26.1 23.4 47.9 49.7 47.9H345c26.3 0 47.9-21.8 49.7-47.9l20.1-336.1H32.9l20.3 336.1z"></path>
            </svg>
            </button>
        </div>
    <?php endforeach; ?>
</div>
<button type="button" id="add-word-button">Add a Word</button>
<button type="button" id="clear-list-button">Clear List</button>

<script type="text/template" id="crossword-word-clue-template">
    <div class="crossword-word-clue" data-index="{{index}}">
        <span class="word-number">{{number}}.</span>
        <input type="text" name="crossword_words[{{index}}][word]" placeholder="Word" value="" />
        <input type="text" name="crossword_words[{{index}}][clue]" placeholder="Clue" value="" />
        <input type="file" name="crossword_words[{{index}}][image]" accept="image/*" />
        <button type="button" class="remove-word">
        <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="trash" class="svg-inline--fa fa-trash fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="20" height="20">
                <path fill="currentColor" d="M135.2 176c-8.5 0-15.2 6.9-15.2 15.2v224c0 8.5 6.9 15.2 15.2 15.2h18.5c8.5 0 15.2-6.9 15.2-15.2v-224c0-8.5-6.9-15.2-15.2-15.2h-18.5zM319.7 176c-8.5 0-15.2 6.9-15.2 15.2v224c0 8.5 6.9 15.2 15.2 15.2h18.5c8.5 0 15.2-6.9 15.2-15.2v-224c0-8.5-6.9-15.2-15.2-15.2h-18.5zM432 32H312l-9.4-18.8C295.5 5.4 286.6 0 276.6 0H171.5c-10 0-18.8 5.4-26 13.2L135.2 32H16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h416c8.8 0 16-7.2 16-16V48c0-8.8-7.2-16-16-16zM53.2 472.1c1.8 26.1 23.4 47.9 49.7 47.9H345c26.3 0 47.9-21.8 49.7-47.9l20.1-336.1H32.9l20.3 336.1z"></path>
            </svg>
        </button>
    </div>
</script>
