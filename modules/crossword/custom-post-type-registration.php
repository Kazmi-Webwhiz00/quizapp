<?php
// In your plugin's main file or a dedicated file for custom post types
function register_crossword_post_type() {
    $custom_slug = get_option('crossword_custom_url_slug', 'crossword'); // Fetch the saved slug

    $labels = array(
        'name'               => __( 'Crosswords', 'wp-quiz-plugin' ),
        'singular_name'      => __( 'Crossword', 'wp-quiz-plugin' ),
        'menu_name'          => __( 'Crosswords', 'wp-quiz-plugin' ),
        'add_new'            => __( 'Add New', 'wp-quiz-plugin' ),
        'add_new_item'       => __( 'Add New Crossword', 'wp-quiz-plugin' ),
        'edit_item'          => __( 'Edit Crossword', 'wp-quiz-plugin' ),
        'new_item'           => __( 'New Crossword', 'wp-quiz-plugin' ),
        'view_item'          => __( 'View Crossword', 'wp-quiz-plugin' ),
        'search_items'       => __( 'Search Crosswords', 'wp-quiz-plugin' ),
        'not_found'          => __( 'No crosswords found', 'wp-quiz-plugin' ),
        'not_found_in_trash' => __( 'No crosswords found in Trash', 'wp-quiz-plugin' ),
    );

    $args = array(
        'labels'             => $labels,
        'description'        => __( 'Crosswords custom post type.', 'wp-quiz-plugin' ),
        'public'             => true,
        'menu_icon'          => 'dashicons-editor-table',
        'supports'           => array( 'title' ),
        'has_archive'        => true,
        'rewrite'            => array('slug' => $custom_slug), // Singular slug
    );

    register_post_type( 'crossword', $args ); // Singular post type key
}
add_action( 'init', 'register_crossword_post_type' );

function register_crossword_taxonomy() {
    $labels = array(
        'name'              => _x('Crossword Categories', 'taxonomy general name', 'wp-quiz-plugin'),
        'singular_name'     => _x('Crossword Category', 'taxonomy singular name', 'wp-quiz-plugin'),
        'search_items'      => __('Search Crossword Categories', 'wp-quiz-plugin'),
        'all_items'         => __('All Crossword Categories', 'wp-quiz-plugin'),
        'parent_item'       => __('Parent Crossword Category', 'wp-quiz-plugin'),
        'parent_item_colon' => __('Parent Crossword Category:', 'wp-quiz-plugin'),
        'edit_item'         => __('Edit Crossword Category', 'wp-quiz-plugin'),
        'update_item'       => __('Update Crossword Category', 'wp-quiz-plugin'),
        'add_new_item'      => __('Add New Crossword Category', 'wp-quiz-plugin'),
        'new_item_name'     => __('New Crossword Category Name', 'wp-quiz-plugin'),
        'menu_name'         => __('Crossword Categories', 'wp-quiz-plugin'),
    );

    $args = array(
        'hierarchical'      => true, // Set to false if you want a non-hierarchical taxonomy (like tags)
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'crossword-category'),
    );

    register_taxonomy('crossword_category', array('crossword'), $args);
}
add_action('init', 'register_crossword_taxonomy');

?>
