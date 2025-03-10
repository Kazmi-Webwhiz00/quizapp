<?php
/**
 * Registers and renders the meta box for Wordsearch AI generation.
 */

function ws_register_create_with_ai_meta_box() {

    $ai_box_title = get_option('kw_generate_with_ai_box_title_ws', __('Generate with AI', 'wp-quiz-plugin'));
    add_meta_box(
        'wordsearch_ai_meta_box',                  // Meta box ID
        esc_html__($ai_box_title, 'wp-quiz-plugin'), // Title
        'ws_render_ai_meta_box',                   // Callback function
        'Wordsearch',                              // Post type (ensure this matches your custom post type if applicable)
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'ws_register_create_with_ai_meta_box');

function ws_render_ai_meta_box($post) {
    wp_nonce_field('wordsearch_save_meta_box_data', 'wordsearch_meta_box_nonce');

    // Include the template file where the HTML is defined.
    // This file is assumed to be located in the "templates" folder within the current module.
    include plugin_dir_path(__FILE__) . '/templates/generate-with-ai.php';
}