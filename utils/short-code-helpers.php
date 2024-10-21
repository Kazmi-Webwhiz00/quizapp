<?php
function get_image_style($quiz_id, $type = 'question') {
    if ($type === 'question') {
        $width = get_post_meta($quiz_id, 'question_image_width', true);
        $height = get_post_meta($quiz_id, 'question_image_height', true);
    } else if($type === 'answer') {
        $width = get_post_meta($quiz_id, 'answer_image_width', true);
        $height = get_post_meta($quiz_id, 'answer_image_height', true);
    }

    // Set default values
    $default_size = 200;
    $width = ($width && $width != 0) ? $width : $default_size;
    $height = ($height && $height != 0) ? $height : $default_size;

    // Return the style string
    return "height: {$height}px; width: {$width}px;";
}

function get_associated_quiz_categories($post_id) {
    // Get the terms associated with the post for the 'quiz_category' taxonomy
    $terms = wp_get_post_terms($post_id, 'quiz_category');

    // Check for errors or empty results
    if (is_wp_error($terms) || empty($terms)) {
        return []; // Return an empty array if there are no categories
    }

    // Prepare an array to hold the category names
    $category_names = [];

    // Loop through the terms and collect their names
    foreach ($terms as $term) {
        $category_names[] = $term->name; // Collect the name of each category
    }

    return $category_names; // Return the array of category names
}

?>