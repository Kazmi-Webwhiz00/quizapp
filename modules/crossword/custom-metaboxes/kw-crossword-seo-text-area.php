<?php

// Add custom meta box for SEO Text specific to Crossword
function add_crossword_seo_text_meta_box() { // Renamed function to avoid conflict
    // Fetch dynamic meta box title for crosswords
    $meta_box_title = get_option('wp_crossword_plugin_meta_box_title', __('SEO Text (Admin Only)', 'wp-crossword-plugin'));

    add_meta_box(
        'crossword_seo_text', // ID of the meta box
        __(esc_html($meta_box_title), 'wp-crossword-plugin'), // Title of the meta box dynamically set
        'render_crossword_seo_text_meta_box', // Callback function to display the input field
        'crossword', // Post type (crossword)
        'side', // Position
        'low' // Priority
    );
}
add_action('add_meta_boxes', 'add_crossword_seo_text_meta_box');

// Render the input field for SEO text specific to Crossword
function render_crossword_seo_text_meta_box($post) { // Renamed to avoid conflict
    // Security check
    wp_nonce_field('save_crossword_seo_text', 'crossword_seo_text_nonce');

    // Fetch existing value from the database
    $seo_text = get_post_meta($post->ID, '_crossword_seo_text', true);

    // Fetch dynamic message for admins
    $admin_only_message = get_option('wp_crossword_plugin_admin_only_message', __('Only administrators can edit this SEO text. Shortcode: [crossword_seo_text]', 'wp-crossword-plugin'));

    // Only allow admin users to edit this field
    if (current_user_can('manage_options')) {

        // Render the minimal editor
        wp_editor(esc_html($seo_text), 'crossword_seo_text', $settings);
        echo '<p style="font-size: small; color: #666;">' . esc_html($admin_only_message) . '</p>';
    } else {
        echo '<textarea style="width:100%;height:150px;" id="quiz_seo_text" name="quiz_seo_text" disabled>' . esc_textarea($seo_text) . '</textarea>';
        echo '<p>' . _e(esc_html($disabled_message),'wp-quiz-plugin') . '</p>';
    }
}

// Save the SEO text when the Crossword post is saved
function save_crossword_seo_text_meta_box($post_id) { // Renamed to avoid conflict
    // Security checks
    if (!isset($_POST['crossword_seo_text_nonce']) || !wp_verify_nonce($_POST['crossword_seo_text_nonce'], 'save_crossword_seo_text')) {
        return;
    }

    // Check if current user has permission to save data
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save the SEO text
    if (isset($_POST['crossword_seo_text'])) {
        update_post_meta($post_id, '_crossword_seo_text', sanitize_textarea_field($_POST['crossword_seo_text']));
    }
}
add_action('save_post', 'save_crossword_seo_text_meta_box');
