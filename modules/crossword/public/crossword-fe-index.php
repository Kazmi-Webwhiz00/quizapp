<?php



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

// Register shortcode to display front-end crossword template
function display_crossword_template_shortcode() {
    // Start output buffering
    ob_start();
    // Include the template file
    include plugin_dir_path(__FILE__) . '/../templates/front-end-crossword.php';

    // Get the buffered content
    return ob_get_clean();
}

// Register the shortcode
add_shortcode('crossword_fe_template', 'display_crossword_template_shortcode');

?>
