<?php
/*
Template Name: Crossword Puzzle Template
*/

// Ensure this file is accessed within WordPress
if (!defined('ABSPATH')) exit;

// Fetch existing crossword grid data from the post meta
global $post;
$grid_data = get_post_meta($post->ID, '_crossword_grid_data', true);
?>

<div id="crossword-container">
    <!-- Error Message Display -->
    <div id="error-message" style="display: none; color: red;"></div>

    <!-- Hidden Field to Store Crossword Data -->
    <input type="hidden" id="crossword-data" name="crossword_data" value="<?php echo esc_attr($grid_data); ?>">

    <!-- Crossword Grid Container -->
    <div id="crossword-grid"></div>

    <!-- Clues Container -->
    <div id="clues-container">
        <h3>Across</h3>
        <ul id="across-clues"></ul>
        <h3>Down</h3>
        <ul id="down-clues"></ul>
    </div>
</div>
