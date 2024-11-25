<?php

function load_crossword_assets_fe() { 
    // Enqueue the CSS file
    wp_enqueue_style('fe-crossword-style', plugin_dir_url(__FILE__) . 'assets/css/fe-crossword-styles.css');
    wp_enqueue_script('fe-crossword-script', plugin_dir_url(__FILE__) . 'assets/js/fe-crossword-script.js', array('jquery'), null, true);
    wp_enqueue_script('fe-crossword-download-script', plugin_dir_url(__FILE__) . '../assets/js/crossword-pdfGenerator.js', array('jquery'), null, true);
    
    // Fetch the filled cell background color with a default value
    $filled_cell_bg_color = get_option('kw_fe_filled_cell_bg_color', '#e1f5fe');

    // Localize the script with additional settings
    wp_localize_script(
        'fe-crossword-download-script',
        'cross_ajax_obj',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'filledCellColor' => esc_attr($filled_cell_bg_color),
            'correctedCellColor' => 'green',
        )
    );


    wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array(), null, true);
}


// Hook into frontend script enqueue
add_action('wp_enqueue_scripts', 'load_crossword_assets_fe');

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
