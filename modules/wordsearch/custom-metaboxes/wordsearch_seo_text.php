<?php
// 1) Add custom meta box for SEO Text on the WordSearch CPT
function add_seo_text_meta_box_wordsearch() {
    // Dynamic box title (falling back if empty)
    $meta_box_title = get_option(
        'wp_quiz_plugin_meta_box_title',
        __( 'SEO Text (Admin Only)', 'wp-quiz-plugin' )
    );
    if ( empty( $meta_box_title ) ) {
        $meta_box_title = __( 'SEO Text (Admin Only)', 'wp-quiz-plugin' );
    }

    add_meta_box(
        'wordsearch_seo_text_meta_box',        // box ID
        esc_html( $meta_box_title ),           // title
        'render_seo_text_meta_box_wordsearch', // callback
        'wordsearch',                          // post type
        'side',                                // context
        'low'                                  // priority
    );
}
add_action( 'add_meta_boxes', 'add_seo_text_meta_box_wordsearch' );


// 2) Render the input field for the WordSearch SEO text
function render_seo_text_meta_box_wordsearch( $post ) {
    wp_nonce_field( 'save_seo_text', 'wordsearch_seo_text_nonce' );

    // load existing meta
    $seo_text = get_post_meta( $post->ID, '_wordsearch_seo_text', true );

    // dynamic admin-only messages
    $admin_only_msg  = get_option(
        'wp_quiz_plugin_admin_only_message',
        __( 'Only administrators can edit this SEO text. Shortcode: [wordsearch_seo_text]', 'wp-quiz-plugin' )
    );
    $disabled_msg    = get_option(
        'wp_quiz_plugin_disabled_message',
        __( 'Only administrators can edit this SEO text.', 'wp-quiz-plugin' )
    );

    if ( current_user_can( 'manage_options' ) ) {
        // full WP editor
        wp_editor(
            esc_html( $seo_text ),
            'wordsearch_seo_text',
            [
                'textarea_name' => 'wordsearch_seo_text',
                'editor_class'  => 'wp-editor-area',
                'textarea_rows' => 10,
                'teeny'         => true,
                'quicktags'     => false,
            ]
        );
        echo '<p>' . esc_html( $admin_only_msg ) . '</p>';
    } else {
        // readonly <textarea>
        echo '<textarea ' .
             'style="width:100%;height:150px;" ' .
             'id="wordsearch_seo_text" ' .
             'name="wordsearch_seo_text" ' .
             'disabled>' .
             esc_textarea( $seo_text ) .
             '</textarea>';
        echo '<p>' . esc_html( $disabled_msg ) . '</p>';
    }
}


// 3) Save the SEO text when a WordSearch post is saved
function save_seo_text_meta_box_wordsearch( $post_id ) {
    // nonce & permissions
    if (
        ! isset( $_POST['wordsearch_seo_text_nonce'] ) ||
        ! wp_verify_nonce( $_POST['wordsearch_seo_text_nonce'], 'save_seo_text' ) ||
        ! current_user_can( 'manage_options' )
    ) {
        return;
    }

    if ( isset( $_POST['wordsearch_seo_text'] ) ) {
        update_post_meta(
            $post_id,
            '_wordsearch_seo_text',
            sanitize_textarea_field( $_POST['wordsearch_seo_text'] )
        );
    }
}
add_action( 'save_post', 'save_seo_text_meta_box_wordsearch' );


// 4) Shortcode to display the SEO text for WordSearch
function display_wordsearch_seo_text( $atts ) {
    global $post;
    if ( 'wordsearch' !== get_post_type( $post ) ) {
        return '';
    }

    $seo_text = get_post_meta( $post->ID, '_wordsearch_seo_text', true );
    if ( ! empty( $seo_text ) ) {
        return '<div class="wordsearch-seo-text">' . wpautop( esc_html( $seo_text ) ) . '</div>';
    }
    return '';
}
add_shortcode( 'wordsearch_seo_text', 'display_wordsearch_seo_text' );
