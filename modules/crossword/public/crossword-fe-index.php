<?php

function load_crossword_assets_fe() { 

    global $post;
    $post_type = get_post_type($post);
    // Enqueue the CSS file
    wp_enqueue_style('fe-crossword-style', plugin_dir_url(__FILE__) . 'assets/css/fe-crossword-styles.css');
    
    wp_enqueue_script('fe-crossword-download-script', plugin_dir_url(__FILE__) . 'assets/js/crossword-pdfGeneratorFe.js', array('jquery'), null, true);

    
    // Fetch the filled cell background color with a default value
    $filled_cell_bg_color = get_option('kw_fe_filled_cell_bg_color', '#e1f5fe');
    $correctedCellColor = get_option('kw_fe_corrected_cell_bg_color', '#d4edda');
    $wrongCellColor = get_option('kw_fe_wrong_cell_bg_color', '#d66868');
    $highlightColor = get_option('kw_crossword_highlight_cell_color', 'yellow');

    // Get options for body text styling
    $body_text_font_color = esc_attr(get_option('kw_fe_body_text_font_color', 'red'));
    $body_text_font_size = intval(get_option('kw_fe_body_text_font_size', 16)) . 'px';
    $body_text_font_family = esc_attr(get_option('kw_fe_body_text_font_family', 'Noto Sans'));

    // Get options for clue image dimensions
    $clue_image_height = intval(get_option('kw_fe_clue_image_height', 150)); // Default height
    $clue_image_width = intval(get_option('kw_fe_clue_image_width', 150)); // Default width

    // Get option for download button text
    $default_download_text = __('Download', 'wp-quiz-plugin');  // Default download button text
    $default_downloading_text = __('Downloading...', 'wp-quiz-plugin');  // Default downloading button text
    $download_button_text = get_option('kw_fe_download_button_text', $default_download_text);
    $downloading_button_text = get_option('kw_fe_download_button_downloading_text', $default_downloading_text);


    // Enqueue the JavaScript file
    if ($post && $post_type === 'crossword') {
        // Only enqueue the script if the post type is 'quizzes'
        wp_enqueue_script('fe-crossword-script', plugin_dir_url(__FILE__) . 'assets/js/fe-crossword-script.js', array('jquery'), null, true);
    }

    // Localize the script with the body text style settings and popup settings
    wp_localize_script('fe-crossword-script', 'cross_ajax_obj', array(
        'fontColor' => $body_text_font_color,
        'fontSize' => $body_text_font_size,
        'fontFamily' => $body_text_font_family,
        'filledCellColor' => esc_attr($filled_cell_bg_color),
        'correctedCellColor' => esc_attr($correctedCellColor),
        'wrongCellColor' => esc_attr($wrongCellColor),
        'highlightColor' => esc_attr($highlightColor),
        'clueImageHeight' => $clue_image_height . 'px',
        'clueImageWidth' => $clue_image_width . 'px',

        // Success Popup Settings
        'successPopup' => array(
            'title' => get_option('kw_crossword_success_popup_title', __('Success!', 'wp-quiz-plugin')),
            'bodyText' => get_option('kw_crossword_success_popup_body_text', __('You have successfully completed the crossword!', 'wp-quiz-plugin')),
            'buttonText' => get_option('kw_crossword_success_popup_button_text', __('Awesome!', 'wp-quiz-plugin')),
            'iconColor' => '#00796b',
            'buttonColor' => get_option('kw_crossword_success_popup_button_color', '#00796b'),
            'buttonTextColor' => get_option('kw_crossword_success_popup_button_text_color', '#fffff')
        ),

        // Failure Popup Settings
        'failurePopup' => array(
            'title' => get_option('kw_crossword_failure_popup_title', __('Are you Sure ?', 'wp-quiz-plugin')),
            'bodyText' => get_option('kw_crossword_failure_popup_body_text', __('Not all words are typed !', 'wp-quiz-plugin')),
            'buttonText' => get_option('kw_crossword_failure_popup_button_text', __('Try Again', 'wp-quiz-plugin')),
            'iconColor' => '#d66868',
            'buttonColor' => '#d66868',
        ),

        'cellFontColor' => get_option('kw_crossword_cell_font_color', 'black'),
        'clueFontColor' => get_option('kw_crossword_cell_clue_font_color', 'black'),
        'cellBorderColor' => get_option('kw_crossword_cell_border_color', 'lightgrey'),

        'strings' => array(
        // Confirmation popup before revealing answers
        'confirmRevealTitle' => _x('Reveal Crossword?', 'crossword-frontend', 'wp-quiz-plugin'),
        'confirmRevealText' => _x('Are you sure you want to reveal the correct answers? This will show all solutions and disable further input.', 'crossword-frontend', 'wp-quiz-plugin'),
        'confirmRevealYes' => _x('Yes, Reveal', 'crossword-frontend', 'wp-quiz-plugin'),
        'confirmRevealNo' => _x('No, Keep Playing', 'crossword-frontend', 'wp-quiz-plugin'),

        'successIconColor' => '#00796b',
        'successButtonColor' => '#00796b',
        'failureIconColor' => '#d66868',
        'failureButtonColor' => '#d66868',
        ),

    ));

    wp_localize_script(
        'fe-crossword-download-script',
        'cross_ajax_download_obj',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'downloadingText' => $downloading_button_text,
            'pdfButtonText' =>  $download_button_text,
            'strings' => array(
                'errorMessage' => _x('An error occurred while generating the PDF.', 'FE-crossword_pdf_error', 'wp-quiz-plugin'),
                'successMessage' => _x('PDF generated successfully.', 'FE-crossword_pdf_success', 'wp-quiz-plugin')
            )
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
