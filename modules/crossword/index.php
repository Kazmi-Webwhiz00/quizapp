<?php

// Include the necessary files for the crossword module
include_once plugin_dir_path(__FILE__) . '/crossword-functions.php';
include_once plugin_dir_path(__FILE__) . '/crossword-settings.php';
include_once plugin_dir_path(__FILE__) . '/utils/crossword-helpers.php';
include_once plugin_dir_path(__FILE__) . '/custom-post-type-registration.php';


function load_crossword_assets($hook) {
    // Get the current screen information
    $screen = get_current_screen();

    // Check if the current screen is for the 'crossword' post type
        // Enqueue the CSS file
        wp_enqueue_style('crossword-style', plugin_dir_url(__FILE__) . 'assets/css/crossword-styles.css');
        
        // Enqueue the JS file with jQuery as a dependency
        wp_enqueue_script('crossword-script', plugin_dir_url(__FILE__) . 'assets/js/crossword-scripts.js', array('jquery'), null, true);
    
}

add_action('admin_enqueue_scripts', 'load_crossword_assets');

// add_action('init', 'initialize_crossword_module');

// function initialize_crossword_module() {
//     // Logic to initialize crossword module (e.g., add shortcodes, hooks, etc.)
// }

?>