<?php
// Fetch existing words and clues
$words_clues = get_post_meta($post->ID, '_crossword_words_clues', true);

// Ensure $words_clues is always an array
if (empty($words_clues) || !is_array($words_clues)) {
    $words_clues = [];
}
?>

<div id="crossword-container">
    <!-- Existing crossword words and clues section -->
     <div style="flex: 1">
        <div id="crossword-words-clues-container">
            <?php foreach ($words_clues as $index => $entry) : ?>
                <div class="crossword-word-clue" data-index="<?php echo $index; ?>">
                <span class="word-number"><?php echo $index + 1; ?>.</span>
                    <div class="kw-crossword-words-clues-container">
                        <input type="text" name="crossword_words[<?php echo $index; ?>][word]" placeholder="Word" value="<?php echo esc_attr($entry['word']); ?>" />
                        <input type="text" name="crossword_words[<?php echo $index; ?>][clue]" placeholder="Clue" value="<?php echo esc_attr($entry['clue']); ?>" />
                    </div>
                    <?php if (!empty($entry['image'])): ?>
                        <input type="hidden" class="crossword-image-url" name="crossword_words[<?php echo $index; ?>][image]" value="<?php echo esc_url($entry['image']); ?>" />
                        <div class="crossword-image-preview">
                            <img src="<?php echo esc_url($entry['image']); ?>" />
                        </div>
                    <?php endif; ?>
                    <div class="kw-crossword-words-clues-action-button-container">
                    <span class="upload-crossword-image-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M160 80l352 0c8.8 0 16 7.2 16 16l0 224c0 8.8-7.2 16-16 16l-21.2 0L388.1 178.9c-4.4-6.8-12-10.9-20.1-10.9s-15.7 4.1-20.1 10.9l-52.2 79.8-12.4-16.9c-4.5-6.2-11.7-9.8-19.4-9.8s-14.8 3.6-19.4 9.8L175.6 336 160 336c-8.8 0-16-7.2-16-16l0-224c0-8.8 7.2-16 16-16zM96 96l0 224c0 35.3 28.7 64 64 64l352 0c35.3 0 64-28.7 64-64l0-224c0-35.3-28.7-64-64-64L160 32c-35.3 0-64 28.7-64 64zM48 120c0-13.3-10.7-24-24-24S0 106.7 0 120L0 344c0 75.1 60.9 136 136 136l320 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-320 0c-48.6 0-88-39.4-88-88l0-224zm208 24a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/></svg>
                    </span>
                    <button type="button" class="remove-word" id="kw-cross-word-remove-word-button">
                        <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="trash" class="svg-inline--fa fa-trash fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="20" height="20">
                            <path fill="currentColor" d="M135.2 176c-8.5 0-15.2 6.9-15.2 15.2v224c0 8.5 6.9 15.2 15.2 15.2h18.5c8.5 0 15.2-6.9 15.2-15.2v-224c0-8.5-6.9-15.2-15.2-15.2h-18.5zM319.7 176c-8.5 0-15.2 6.9-15.2 15.2v224c0 8.5 6.9 15.2 15.2 15.2h18.5c8.5 0 15.2-6.9 15.2-15.2v-224c0-8.5-6.9-15.2-15.2-15.2h-18.5zM432 32H312l-9.4-18.8C295.5 5.4 286.6 0 276.6 0H171.5c-10 0-18.8 5.4-26 13.2L135.2 32H16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h416c8.8 0 16-7.2 16-16V48c0-8.8-7.2-16-16-16zM53.2 472.1c1.8 26.1 23.4 47.9 49.7 47.9H345c26.3 0 47.9-21.8 49.7-47.9l20.1-336.1H32.9l20.3 336.1z"></path>
                        </svg>
                    </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="crossword-add-button-container">
            <button type="button" id="add-word-button">Add a Word</button>
            <button type="button" id="clear-list-button">Clear List</button>
        </div>
    </div>

</div>


<script type="text/template" id="crossword-word-clue-template">
    <div class="crossword-word-clue" data-index="{{index}}">
    <span class="word-number">{{number}}.</span>
        <div class="kw-crossword-words-clues-container" >
            
            <input type="text" name="crossword_words[{{index}}][word]" placeholder="Word" value="" />
            <input type="text" name="crossword_words[{{index}}][clue]" placeholder="Clue" value="" />
        </div>
        <span class="upload-crossword-image-btn">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M160 80l352 0c8.8 0 16 7.2 16 16l0 224c0 8.8-7.2 16-16 16l-21.2 0L388.1 178.9c-4.4-6.8-12-10.9-20.1-10.9s-15.7 4.1-20.1 10.9l-52.2 79.8-12.4-16.9c-4.5-6.2-11.7-9.8-19.4-9.8s-14.8 3.6-19.4 9.8L175.6 336 160 336c-8.8 0-16-7.2-16-16l0-224c0-8.8 7.2-16 16-16zM96 96l0 224c0 35.3 28.7 64 64 64l352 0c35.3 0 64-28.7 64-64l0-224c0-35.3-28.7-64-64-64L160 32c-35.3 0-64 28.7-64 64zM48 120c0-13.3-10.7-24-24-24S0 106.7 0 120L0 344c0 75.1 60.9 136 136 136l320 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-320 0c-48.6 0-88-39.4-88-88l0-224zm208 24a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/></svg>
        </span>
        <button type="button" class="remove-word" id="kw-cross-word-remove-word-button">
            <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="trash" class="svg-inline--fa fa-trash fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="20" height="20">
                <path fill="currentColor" d="M135.2 176c-8.5 0-15.2 6.9-15.2 15.2v224c0 8.5 6.9 15.2 15.2 15.2h18.5c8.5 0 15.2-6.9 15.2-15.2v-224c0-8.5-6.9-15.2-15.2-15.2h-18.5zM319.7 176c-8.5 0-15.2 6.9-15.2 15.2v224c0 8.5 6.9 15.2 15.2 15.2h18.5c8.5 0 15.2-6.9 15.2-15.2v-224c0-8.5-6.9-15.2-15.2-15.2h-18.5zM432 32H312l-9.4-18.8C295.5 5.4 286.6 0 276.6 0H171.5c-10 0-18.8 5.4-26 13.2L135.2 32H16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h416c8.8 0 16-7.2 16-16V48c0-8.8-7.2-16-16-16zM53.2 472.1c1.8 26.1 23.4 47.9 49.7 47.9H345c26.3 0 47.9-21.8 49.7-47.9l20.1-336.1H32.9l20.3 336.1z"></path>
            </svg>
        </button>
    </div>
</script>
