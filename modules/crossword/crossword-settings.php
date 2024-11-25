<?php
// Add meta box for crosswords
function crossword_add_meta_boxes() {
    // Retrieve the meta value for the container label, or fall back to the default
    $default_label = __('Words and Clues', 'wp-quiz-plugin');
    $meta_label = get_option('kw_crossword_admin_words_clue_container_label', $default_label);

    add_meta_box(
        'crossword_words_clues',
        esc_html($meta_label), // Use the retrieved label
        'crossword_words_clues_meta_box_callback',
        'crossword', // Ensure the post type matches the registered singular value
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'crossword_add_meta_boxes');


function crossword_preview_meta_box() {
    // Retrieve the meta value for the full view container label, or fall back to the default
    $default_label = __('Crossword Full View', 'wp-quiz-plugin');
    $meta_label = get_option('kw_crossword_admin_full_view_container_label', $default_label);

    add_meta_box(
        'crossword_preview_meta_box_id', // Unique ID for the meta box
        esc_html($meta_label), // Use the retrieved label as the meta box title
        'crossword_preview_meta_box_callback', // Callback function
        'crossword', // Post type where it should appear
        'normal', // Context ('normal', 'side', 'advanced')
        'high' // Priority
    );
}
add_action('add_meta_boxes', 'crossword_preview_meta_box');


function crossword_register_create_with_ai_meta_box() {
    add_meta_box(
        'crossword_ai_meta_box',         // Meta box ID
        'Generate with AI',              // Title
        'crossword_render_ai_meta_box',  // Callback function
        'Crossword',                          // Post type
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'crossword_register_create_with_ai_meta_box');


function crossword_render_ai_meta_box($post) {
    wp_nonce_field('crossword_save_meta_box_data', 'crossword_meta_box_nonce');

    // Include the template file where the HTML is defined
    include plugin_dir_path(__FILE__) . 'templates/generate-with-ai.php';
}

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

    if (isset($_POST['crossword_data'])) {
        // Remove any slashes added by WordPress
        $crossword_data_json = wp_unslash($_POST['crossword_data']);

        // Decode JSON to verify its validity
        $crossword_data_array = json_decode($crossword_data_json, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($crossword_data_array)) {
            // Extract grid data
            if (isset($crossword_data_array['grid']) && is_array($crossword_data_array['grid'])) {
                $grid_data = $crossword_data_array['grid'];

                // Optional: Perform additional sanitization on grid data
                foreach ($grid_data as &$row) {
                    foreach ($row as &$cell) {
                        if (isset($cell['letter'])) {
                            $cell['letter'] = sanitize_text_field($cell['letter']);
                        }
                        if (isset($cell['clueNumber'])) {
                            $cell['clueNumber'] = sanitize_text_field($cell['clueNumber']);
                        }
                    }
                }
                unset($row, $cell); // Break references

                // Re-encode to ensure proper formatting before saving
                $sanitized_grid_data = wp_json_encode($grid_data);

                // Save the sanitized grid data as post meta
                update_post_meta($post_id, '_crossword_grid_data', $crossword_data_json);
            } else {
                // If grid data is not set or invalid, delete the meta to avoid storing corrupted data
                delete_post_meta($post_id, '_crossword_grid_data');
            }

            
        } else {
            // If JSON is invalid, delete the grid meta to avoid storing corrupted data
            delete_post_meta($post_id, '_crossword_grid_data');
        }
    } else {
        // If crossword_data is not set, delete any existing grid meta
        delete_post_meta($post_id, '_crossword_grid_data');
    }
    error_log("save successfully ::");
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
?>
