<?php

// Include the necessary files for the crossword module
include_once plugin_dir_path(__FILE__) . '/crossword-functions.php';
include_once plugin_dir_path(__FILE__) . '/crossword-settings.php';
include_once plugin_dir_path(__FILE__) . '/utils/crossword-helpers.php';

// Function to load crossword assets if the module is enabled
function load_crossword_assets() {
    wp_enqueue_style('crossword-style', plugin_dir_url(__FILE__) . 'assets/css/crossword-styles.css');
    wp_enqueue_script('crossword-script', plugin_dir_url(__FILE__) . 'assets/js/crossword-scripts.js', array('jquery'), null, true);
}

// in future will be adding this login if (get_option('enable_crossword_module')
add_action('wp_enqueue_scripts', 'load_crossword_assets');
add_action('init', 'initialize_crossword_module');

function initialize_crossword_module() {
    // Logic to initialize crossword module (e.g., add shortcodes, hooks, etc.)
}

?>