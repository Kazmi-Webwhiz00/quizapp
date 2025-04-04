<?php
// Add meta box for wordsearch description
function add_wordsearch_description_meta_box() {
    add_meta_box(
        'wordsearh_description_meta_box',
        __('WordSearch Description', 'wp-quiz-plugin'),
        'display_wordsearch_description_meta_box',
        'wordsearch',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_wordsearch_description_meta_box');

function display_wordsearch_description_meta_box($post) {
    // Add nonce field for security
    wp_nonce_field('save_wordsearch_description', 'wordsearch_description_nonce');
    
    // Retrieve existing description
    $description = get_post_meta($post->ID, '_wordsearch_description', true);
    ?>
    <textarea name="wordsearch_description" style="width:100%; height:100px;"><?php echo esc_textarea($description); ?></textarea>
    <?php
}

// Save the wordsearch description
function save_wordsearch_description_meta_box($post_id) {
    // Verify nonce
    if ( !isset($_POST['wordsearch_description_nonce']) || 
         !wp_verify_nonce($_POST['wordsearch_description_nonce'], 'save_wordsearch_description') ) {
        return;
    }
    
    // Check for autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check user permissions for 'wordsearch' post type
    if (isset($_POST['post_type']) && 'wordsearch' == $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }
    
    // Update post meta if the field is set
    if (isset($_POST['wordsearch_description'])) {
        update_post_meta(
            $post_id,
            '_wordsearch_description',
            sanitize_text_field($_POST['wordsearch_description'])
        );
    }
}
add_action('save_post', 'save_wordsearch_description_meta_box');

// Register shortcode to display wordsearch description
function wordsearch_description_shortcode($atts) {
    // Set default attributes and allow overriding via shortcode attributes
    $atts = shortcode_atts(
        array(
            'id' => get_the_ID(), // Default to current post ID
        ),
        $atts,
        'wordsearch_description'
    );
    
    $wordsearch_id = intval($atts['id']);
    
    // Fetch the description from post meta
    $description = get_post_meta($wordsearch_id, '_wordsearch_description', true);
    
    // Return the description or an empty string if not set
    return !empty($description) ? esc_html($description) : '';
}
add_shortcode('wordsearch_description', 'wordsearch_description_shortcode');

