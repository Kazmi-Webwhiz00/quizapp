<?php
// Add custom meta box for SEO Text
function add_seo_text_meta_box_crossword() {

        // Fetch dynamic meta box title
        $meta_box_title = get_option('wp_quiz_plugin_meta_box_title', __('SEO Text (Admin Only)', 'wp-quiz-plugin'));
        if (empty($meta_box_title)) {
            $meta_box_title = __('SEO Text (Admin Only)', 'wp-quiz-plugin'); // Fallback in case the option is empty
        }

    add_meta_box(
        'crossword_seo_text_meta_box', // ID of the meta box
         esc_html($meta_box_title), // Title of the meta box dynamically set
        'render_seo_text_meta_box_crossword', // Callback function to display the input field
        'crossword', // Post type (crosswords)
        'side', // Position
        'low' // Priority
    );
}
add_action('add_meta_boxes', 'add_seo_text_meta_box_crossword');


// Render the input field for SEO text
function render_seo_text_meta_box_crossword($post) {
    // Security check
    wp_nonce_field('save_seo_text', 'crossword_seo_text_nonce');

    // Fetch existing value from the database
    $seo_text = get_post_meta($post->ID, '_crossword_seo_text', true);

    // Fetch dynamic messages
    $admin_only_message = get_option('wp_quiz_plugin_admin_only_message', __('Only administrators can edit this SEO text. Shortcode: [crossword_seo_text]', 'wp-quiz-plugin'));
    $disabled_message = get_option('wp_quiz_plugin_disabled_message', __('Only administrators can edit this SEO text.', 'wp-quiz-plugin'));

    // Only allow admin users to edit this field
    if (current_user_can('manage_options')) {
        $settings = array(
            'textarea_name' => 'crossword_seo_text',
            'editor_class' => 'wp-editor-area',
            'quicktags' => false,
            'textarea_rows' => 10, // Adjust height
            'teeny' => true, // Optional: use a simplified version of the editor
        );
        
        wp_editor(esc_html($seo_text), 'crossword_seo_text', $settings);
        echo '<p>' . _e(esc_html($admin_only_message),'wp-quiz-plugin') . '</p>';
    } else {
        echo '<textarea style="width:100%;height:150px;" id="crossword_seo_text" name="crossword_seo_text" disabled>' . esc_textarea($seo_text) . '</textarea>';
        echo '<p>' . _e(esc_html($disabled_message),'wp-quiz-plugin') . '</p>';
    }
}


// Save the SEO text when the post is saved
function save_seo_text_meta_box_crossword($post_id) {
    // Security checks
    if (!isset($_POST['crossword_seo_text_nonce']) || !wp_verify_nonce($_POST['crossword_seo_text_nonce'], 'save_seo_text')) {
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
add_action('save_post', 'save_seo_text_meta_box_crossword');


// Shortcode to display the SEO text for crosswords
function display_crossword_seo_text($atts) {
    global $post;

    // Ensure we are on a 'crossword' post type page
    if ('crossword' !== get_post_type($post)) {
        return '';
    }

    // Get the SEO text from post meta
    $seo_text = get_post_meta($post->ID, '_crossword_seo_text', true);

    // Output the SEO text (if available)
    if (!empty($seo_text)) {
        return '<div class="crossword-seo-text">' . wpautop(esc_html($seo_text)) . '</div>';
    }

    return '';
}
add_shortcode('crossword_seo_text', 'display_crossword_seo_text');
