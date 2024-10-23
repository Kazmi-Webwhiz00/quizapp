<?php
// Add meta box for crosswords
function crossword_add_meta_boxes() {
    add_meta_box(
        'crossword_words_clues',
        __( 'Words and Clues 2', 'your-text-domain' ),
        'crossword_words_clues_meta_box_callback',
        'Crossword', // Post type should be singular 'crossword' as registered
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'crossword_add_meta_boxes');

// Meta box callback function
function crossword_words_clues_meta_box_callback($post) {
    // Add  // Add nonce for security and authentication
    wp_nonce_field('crossword_save_meta_box_data', 'crossword_meta_box_nonce');

    // Include the template file where the HTML is defined
    include plugin_dir_path(__FILE__) . 'templates/crossword-words-clues.php';
}


function crossword_save_meta_box_data($post_id) {
    if (!isset($_POST['crossword_meta_box_nonce']) || 
        !wp_verify_nonce($_POST['crossword_meta_box_nonce'], 'crossword_save_meta_box_data') || 
        !current_user_can('edit_post', $post_id) || 
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
        return;
    }

    $words_clues = [];

    if (isset($_POST['crossword_words']) && is_array($_POST['crossword_words'])) {
        foreach ($_POST['crossword_words'] as $index => $entry) {
            if (!empty(trim($entry['word']))) {
                $word = sanitize_text_field($entry['word']);
                $clue = !empty($entry['clue']) ? sanitize_text_field($entry['clue']) : '';
                $image_url = !empty($entry['image']) ? esc_url($entry['image']) : '';

                $words_clues[] = [
                    'word' => $word,
                    'clue' => $clue,
                    'image' => $image_url,
                ];
            }
        }

        update_post_meta($post_id, '_crossword_words_clues', $words_clues);
    } else {
        delete_post_meta($post_id, '_crossword_words_clues');
    }
}
add_action('save_post', 'crossword_save_meta_box_data');

// Callback function for the meta box
function crossword_preview_meta_box_callback($post) {
    // Add a nonce field for security
    wp_nonce_field('crossword_save_preview_meta_box_data', 'crossword_preview_meta_box_nonce');

    // Include the template file
    $template_path = plugin_dir_path(__FILE__) . 'templates/crossword-preview-meta-box.php';
    
    if (file_exists($template_path)) {
        include $template_path;
    } else {
        echo '<p>Template file not found.</p>';
    }
}

// Function to add the preview meta box
function crossword_preview_meta_box() {
    add_meta_box(
        'crossword_preview_meta_box_id', // Unique ID for the meta box
        __('Crossword Full View', 'your-text-domain'), // Meta box title
        'crossword_preview_meta_box_callback', // Callback function
        'crossword', // Post type where it should appear
        'normal', // Context ('normal', 'side', 'advanced')
        'high' // Priority
    );
}
add_action('add_meta_boxes', 'crossword_preview_meta_box');
?>
