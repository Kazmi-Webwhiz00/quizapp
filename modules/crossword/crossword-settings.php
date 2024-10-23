<?php
// Add meta box for crosswords
function crossword_add_meta_boxes() {
    add_meta_box(
        'crossword_words_clues',
        __( 'Words and Clues', 'your-text-domain' ),
        'crossword_words_clues_meta_box_callback',
        'crosswords', // Post type should be singular 'crossword' as registered
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'crossword_add_meta_boxes');

// Meta box callback function
function crossword_words_clues_meta_box_callback($post) {
    // Add nonce for security and authentication
    wp_nonce_field('crossword_save_meta_box_data', 'crossword_meta_box_nonce');

    // Include the template file where the HTML is defined
    include plugin_dir_path(__FILE__) . 'templates/crossword-words-clues.php';
}


function crossword_save_meta_box_data($post_id) {
    // Verify the nonce and permissions
    if (!isset($_POST['crossword_meta_box_nonce']) || 
        !wp_verify_nonce($_POST['crossword_meta_box_nonce'], 'crossword_save_meta_box_data') || 
        !current_user_can('edit_post', $post_id) || 
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
        return;
    }

    // Initialize an array to hold the crossword data
    $words_clues = [];

    // Check if crossword words are set in the POST data
    if (!empty($_POST['crossword_words']) && is_array($_POST['crossword_words'])) {
        error_log(print_r($_FILES, true)); // Log the entire $_FILES array for debugging

        foreach ($_POST['crossword_words'] as $index => $entry) {
            // Make sure the word field is not empty
            if (!empty(trim($entry['word']))) {
                $word = sanitize_text_field($entry['word']);
                $clue = !empty($entry['clue']) ? sanitize_text_field($entry['clue']) : '';
                $image_url = '';

                // Check if there's an existing image
                if (!empty($entry['existing_image'])) {
                    $image_url = esc_url($entry['existing_image']);
                }

                // Check if a new image is uploaded
                if (isset($_FILES['crossword_words']['name'][$index]['image']) && !empty($_FILES['crossword_words']['name'][$index]['image'])) {
                    error_log("Inside image upload condition"); // Debugging

                    // Prepare the file array for upload
                    $file = [
                        'name' => $_FILES['crossword_words']['name'][$index]['image'],
                        'type' => $_FILES['crossword_words']['type'][$index]['image'],
                        'tmp_name' => $_FILES['crossword_words']['tmp_name'][$index]['image'],
                        'error' => $_FILES['crossword_words']['error'][$index]['image'],
                        'size' => $_FILES['crossword_words']['size'][$index]['image'],
                    ];

                    // Upload the image to the media library
                    $upload = wp_handle_upload($file, ['test_form' => false]);
                    if (!isset($upload['error']) && isset($upload['url'])) {
                        $image_url = esc_url($upload['url']);
                    } else {
                        error_log("Upload error: " . $upload['error']); // Log any upload errors
                    }
                }

                // Add the word, clue, and image to the array
                $words_clues[] = [
                    'word' => $word,
                    'clue' => $clue,
                    'image' => $image_url,
                ];
            }
        }

        // Update the post meta with the crossword data
        update_post_meta($post_id, '_crossword_words_clues', $words_clues);
    } else {
        // If no data is submitted, delete the meta to keep the database clean
        delete_post_meta($post_id, '_crossword_words_clues');
    }
}
add_action('save_post', 'crossword_save_meta_box_data');
