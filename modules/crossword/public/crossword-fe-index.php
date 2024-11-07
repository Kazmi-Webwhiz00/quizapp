<?php

function load_crossword_assets_fe() {
    // Check if the shortcode exists on the page
    global $post;
  
        
        // Enqueue the CSS file
        wp_enqueue_style('fe-crossword-style', plugin_dir_url(__FILE__) . 'assets/css/fe-crossword-styles.css');
        wp_enqueue_script('fe-crossword-script', plugin_dir_url(__FILE__) . 'assets/js/fe-crossword-script.js', array('jquery'), null, true);
        
}

// Hook into frontend script enqueue
add_action('wp_enqueue_scripts', 'load_crossword_assets_fe');

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
    include plugin_dir_path(__FILE__) . '/front-end-crossword.php';

    // Get the buffered content
    return ob_get_clean();
}

// Register the shortcode
add_shortcode('crossword_fe_template', 'display_crossword_template_shortcode');

?>
