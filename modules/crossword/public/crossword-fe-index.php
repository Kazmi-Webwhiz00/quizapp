<?php

function load_crossword_assets_fe() { 
    // Enqueue the CSS file
    wp_enqueue_style('fe-crossword-style', plugin_dir_url(__FILE__) . 'assets/css/fe-crossword-styles.css');
    
    wp_enqueue_script('fe-crossword-download-script', plugin_dir_url(__FILE__) . '../assets/js/crossword-pdfGenerator.js', array('jquery'), null, true);
    
    // Fetch the filled cell background color with a default value
    $filled_cell_bg_color = get_option('kw_fe_filled_cell_bg_color', '#e1f5fe');
    $correctedCellColor = get_option('kw_fe_corrected_cell_bg_color', '#d4edda');

    // Get options for body text styling
    $body_text_font_color = esc_attr(get_option('kw_fe_body_text_font_color', 'red'));
    $body_text_font_size = intval(get_option('kw_fe_body_text_font_size', 16)) . 'px';
    $body_text_font_family = esc_attr(get_option('kw_fe_body_text_font_family', 'Arial'));

    // Enqueue the JavaScript file
    wp_enqueue_script('fe-crossword-script', plugin_dir_url(__FILE__) . 'assets/js/fe-crossword-script.js', array('jquery'), null, true);

    // Localize the script with the body text style settings
    wp_localize_script('fe-crossword-script', 'cross_ajax_obj', array(
        'fontColor' => $body_text_font_color,
        'fontSize' => $body_text_font_size,
        'fontFamily' => $body_text_font_family,
        'filledCellColor' => esc_attr($filled_cell_bg_color),
        'correctedCellColor' => esc_attr($correctedCellColor)
    ));

    // Localize the script with additional settings
    wp_localize_script(
        'fe-crossword-download-script',
        'cross_ajax_obj',
        array(
            'ajax_url' => admin_url('admin-ajax.php')
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
