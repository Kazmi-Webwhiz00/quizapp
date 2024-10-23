<?php
// Get the post title
$post_title = get_the_title($post->ID);
?>

<div class="crossword-preview-container">
    <h2><?php echo esc_html($post_title); ?></h2>
    <div class="crossword-preview-content">
        <!-- This area will be used for your future UI elements -->
    </div>
</div>
