<?php
/**
 * Add "Author Name" column and make it sortable for Quiz and Crossword post types.
 */


// 1. Add the "Author Name" column to the admin list for Quiz and Crossword post types.
function wp_custom_add_author_column( $columns ) {
    $new_columns = array();
    // Loop through existing columns and add a new one after the Title column.
    foreach ( $columns as $key => $value ) {
        $new_columns[ $key ] = $value;
        if ( 'title' === $key ) {
            $new_columns['author_name'] = __( 'Author Name', 'wp-quiz-plugin' );
        }
    }
    return $new_columns;
}
add_filter( 'manage_quizzes_posts_columns', 'wp_custom_add_author_column' );
add_filter( 'manage_crossword_posts_columns', 'wp_custom_add_author_column' );

// 2. Populate the "Author Name" column with data.
function wp_custom_show_author_column( $column, $post_id ) {
    if ( 'author_name' === $column ) {
        $author_id   = get_post_field( 'post_author', $post_id );
        $author_name = get_the_author_meta( 'display_name', $author_id );
        echo esc_html( $author_name );
    }
}
add_action( 'manage_quizzes_posts_custom_column', 'wp_custom_show_author_column', 10, 2 );
add_action( 'manage_crossword_posts_custom_column', 'wp_custom_show_author_column', 10, 2 );

// 3. Make the "Author Name" column sortable.
function wp_custom_sortable_author_column( $columns ) {
    // This will use WordPress’s built-in ordering by author.
    $columns['author_name'] = 'author';
    return $columns;
}
add_filter( 'manage_edit-quizzes_sortable_columns', 'wp_custom_sortable_author_column' );
add_filter( 'manage_edit-crossword_sortable_columns', 'wp_custom_sortable_author_column' );

// 4. Adjust the query for sorting by the "Author Name" column.
function wp_custom_author_orderby( $query ) {
    if ( ! is_admin() ) {
        return;
    }
    
    $orderby = $query->get( 'orderby' );
    
    // When sorting by author, let WordPress handle it.
    if ( 'author' === $orderby ) {
        $query->set( 'orderby', 'author' );
    }
}
add_action( 'pre_get_posts', 'wp_custom_author_orderby' );
