<?php
// Ensure debugging is enabled in wp-config.php
// define('WP_DEBUG', true);
// define('WP_DEBUG_LOG', true);

// Add SEO text meta box for quizzes only
function add_quiz_seo_text_meta_box() {
    error_log('::::::::::::add_quiz_seo_text_meta_box: Hook triggered.');

    // Retrieve dynamic meta box title from the database
    $meta_box_title = get_option('wp_quiz_plugin_meta_box_title', 'SEO Text (Admin Only)');
    error_log('::::::::::::Meta Box Title: ' . print_r($meta_box_title, true));

    add_meta_box(
        'quiz_seo_text_meta_box',
        esc_html($meta_box_title),
        'render_quiz_seo_text_meta_box',
        'quizzes',
        'side',
        'default'
    );

    error_log('::::::::::::add_quiz_seo_text_meta_box: Meta box added for "quizzes" post type.');
}
add_action('add_meta_boxes', 'add_quiz_seo_text_meta_box');

// Render the SEO text meta box
function render_quiz_seo_text_meta_box($post) {
    error_log('::::::::::::render_quiz_seo_text_meta_box: Function called.');

    // Nonce field for security
    wp_nonce_field('save_quiz_seo_text', 'quiz_seo_text_nonce');
    error_log('::::::::::::render_quiz_seo_text_meta_box: Nonce field added.');

    // Fetch existing value
    $seo_text = get_post_meta($post->ID, '_quiz_seo_text', true);
    if ($seo_text === '') {
        error_log('::::::::::::render_quiz_seo_text_meta_box: No existing SEO text found.');
    } else {
        error_log('::::::::::::render_quiz_seo_text_meta_box: Existing SEO Text: ' . print_r($seo_text, true));
    }

    // Fetch dynamic messages
    $admin_only_message = get_option('wp_quiz_plugin_admin_only_message', 'Only administrators can edit this SEO text.');
    $disabled_message   = get_option('wp_quiz_plugin_disabled_message', 'Only administrators can edit this SEO text.');

    error_log('::::::::::::Admin only message: ' . print_r($admin_only_message, true));
    error_log('::::::::::::Disabled message: ' . print_r($disabled_message, true));

    // Check user capabilities
    if (current_user_can('manage_options')) {
        error_log('::::::::::::render_quiz_seo_text_meta_box: Current user is admin level.');
        echo '<textarea style="width:100%;height:150px;" id="quiz_seo_text" name="quiz_seo_text">' 
            . esc_textarea($seo_text) . '</textarea>';
        echo '<p>' . esc_html($admin_only_message) . '</p>';
    } else {
        error_log('::::::::::::render_quiz_seo_text_meta_box: Current user does NOT have manage_options capability.');
        echo '<textarea style="width:100%;height:150px;" id="quiz_seo_text" name="quiz_seo_text" disabled>' 
            . esc_textarea($seo_text) . '</textarea>';
        echo '<p>' . esc_html($disabled_message) . '</p>';
    }
}

// Save the SEO text when the quiz is saved
function save_quiz_seo_text_meta_box($post_id) {
    error_log('::::::::::::save_quiz_seo_text_meta_box: Attempting to save.');

    // Verify nonce
    if (!isset($_POST['quiz_seo_text_nonce'])) {
        error_log('::::::::::::save_quiz_seo_text_meta_box: Nonce is missing.');
        return;
    }

    if (!wp_verify_nonce($_POST['quiz_seo_text_nonce'], 'save_quiz_seo_text')) {
        error_log('::::::::::::save_quiz_seo_text_meta_box: Nonce verification failed.');
        return;
    }
    error_log('::::::::::::save_quiz_seo_text_meta_box: Nonce verified.');

    // Check user capability
    if (!current_user_can('manage_options')) {
        error_log('::::::::::::save_quiz_seo_text_meta_box: Current user cannot manage options. Aborting save.');
        return;
    }

    // Save the SEO text if present
    if (isset($_POST['quiz_seo_text'])) {
        $new_value = sanitize_textarea_field($_POST['quiz_seo_text']);
        if (empty($new_value)) {
            error_log('::::::::::::save_quiz_seo_text_meta_box: Empty value provided for SEO text.');
        } else {
            error_log('::::::::::::save_quiz_seo_text_meta_box: New SEO text to save: ' . print_r($new_value, true));
        }
        update_post_meta($post_id, '_quiz_seo_text', $new_value);
        error_log('::::::::::::save_quiz_seo_text_meta_box: SEO text saved successfully.');
    } else {
        error_log('::::::::::::save_quiz_seo_text_meta_box: quiz_seo_text field not set in POST data.');
    }
}
add_action('save_post', 'save_quiz_seo_text_meta_box');

// Shortcode to display the SEO text on quizzes post type
function display_quiz_seo_text_shortcode($atts) {
    error_log('::::::::::::display_quiz_seo_text_shortcode: Called.');

    global $post;
    if (!is_singular('quizzes') || empty($post)) {
        error_log('::::::::::::display_quiz_seo_text_shortcode: Not a "quizzes" post type or no global post found.');
        return '';
    }

    $seo_text = get_post_meta($post->ID, '_quiz_seo_text', true);
    if ($seo_text === '') {
        error_log('::::::::::::display_quiz_seo_text_shortcode: No SEO text found for shortcode.');
    } else {
        error_log('::::::::::::display_quiz_seo_text_shortcode: Fetched SEO text for shortcode: ' . print_r($seo_text, true));
    }

    if (!empty($seo_text)) {
        return '<div class="quiz-seo-text">' . wpautop(esc_textarea($seo_text)) . '</div>';
    }

    return '';
}
add_shortcode('quiz_seo_text', 'display_quiz_seo_text_shortcode');
