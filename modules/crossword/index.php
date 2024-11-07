<?php

// Include the necessary files for the crossword module
include_once plugin_dir_path(__FILE__) . '/crossword-functions.php';
include_once plugin_dir_path(__FILE__) . '/crossword-settings.php';
include_once plugin_dir_path(__FILE__) . '/utils/crossword-helpers.php';
include_once plugin_dir_path(__FILE__) . '/custom-post-type-registration.php';
include_once plugin_dir_path(__FILE__) . '/crossword-download.php';
include_once plugin_dir_path(__FILE__) . '/public/crossword-fe-index.php';


function load_crossword_assets($hook) {
    // Get the current screen information
    $screen = get_current_screen();

    // Check if the current screen is for the 'crossword' post typegenerate
        // Enqueue the CSS file
        wp_enqueue_style('crossword-style', plugin_dir_url(__FILE__) . 'assets/css/crossword-styles.css');
        
        // Enqueue the JS file with jQuery as a dependency
        wp_enqueue_script('crossword-script', plugin_dir_url(__FILE__) . 'assets/js/crossword-scripts.js', array('jquery'), null, true);
        wp_enqueue_script('goku', plugin_dir_url(__FILE__) . 'assets/js/utils.js', array('jquery'), null, true);
        wp_enqueue_script('generate-pdf-script', plugin_dir_url(__FILE__) . 'assets/js/crossword-pdfGenerator.js', array('jquery'), null, true);

        wp_enqueue_script('crossword-generate-with-ai', plugin_dir_url(__FILE__) . 'assets/js/crossword-generate-with-ai.js', array('jquery'), null, true);
        
        // Localize variables for use in JavaScript
        wp_localize_script('crossword-generate-with-ai', 'wpQuizPlugin', array(
            'apiKey' => get_option('wp_quiz_plugin_openai_api_key'),
            'model' => get_option('wp_quiz_plugin_openai_model', 'gpt-4o-mini'),
            'maxTokens' => esc_js(get_option('wp_quiz_plugin_openai_max_tokens', 50)),  // Cast to integer
            'temperature' =>  esc_js(get_option('wp_quiz_plugin_openai_temperature', 0.5)),  // Cast to float
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'generatingText' => __('Generating...', 'wp-quiz-plugin'),
            'generateWithAiText' => __('Generate with AI', 'wp-quiz-plugin')
        ));

        wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array(), null, true);
}

add_action('admin_enqueue_scripts', 'load_crossword_assets');

function crossword_enqueue_preview_assets($hook) {
    global $post;

    if ($hook === 'post.php' || $hook === 'post-new.php') {
        if ($post->post_type === 'crossword') {
            // Enqueue the custom JavaScript file
            wp_enqueue_script(
                'crossword-preview-script',
                plugin_dir_url(__FILE__) . 'assets/js/crossword-preview.js',
                array('jquery'),
                '1.0',
                true
            );

            // Enqueue the custom CSS for the crossword styling
            wp_enqueue_style('crossword-preview-style', plugin_dir_url(__FILE__) . 'assets/css/crossword-preview-styles.css');

            // Fetch the crossword data from post meta
            $words_clues = get_post_meta($post->ID, '_crossword_words_clues', true);
            if (empty($words_clues) || !is_array($words_clues)) {
                $words_clues = [];
            }

            // Localize the script to pass the crossword data
            wp_localize_script(
                'generate-pdf-script',
                'cross_ajax_obj',
                array('ajax_url' => admin_url('admin-ajax.php'))
            );
        }
    }
}
add_action('admin_enqueue_scripts', 'crossword_enqueue_preview_assets');

?>