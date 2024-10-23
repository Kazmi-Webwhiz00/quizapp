<?php
// In your plugin's main file or a dedicated file for custom post types
function register_crossword_post_type() {
    $labels = array(
        'name'               => __( 'Crosswords', 'your-text-domain' ),
        'singular_name'      => __( 'Crossword', 'your-text-domain' ),
        'menu_name'          => __( 'Crosswords', 'your-text-domain' ),
        'add_new'            => __( 'Add New', 'your-text-domain' ),
        'add_new_item'       => __( 'Add New Crossword', 'your-text-domain' ),
        'edit_item'          => __( 'Edit Crossword', 'your-text-domain' ),
        'new_item'           => __( 'New Crossword', 'your-text-domain' ),
        'view_item'          => __( 'View Crossword', 'your-text-domain' ),
        'search_items'       => __( 'Search Crosswords', 'your-text-domain' ),
        'not_found'          => __( 'No crosswords found', 'your-text-domain' ),
        'not_found_in_trash' => __( 'No crosswords found in Trash', 'your-text-domain' ),
    );

    $args = array(
        'labels'             => $labels,
        'description'        => __( 'Crosswords custom post type.', 'your-text-domain' ),
        'public'             => true,
        'menu_icon'          => 'dashicons-editor-table',
        'supports'           => array( 'title' ),
        'has_archive'        => true,
        'rewrite'            => array( 'slug' => 'crossword' ), // Singular slug
    );

    register_post_type( 'crossword', $args ); // Singular post type key
}
add_action( 'init', 'register_crossword_post_type' );
?>
