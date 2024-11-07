<?php
/*
Template Name: Crossword Puzzle Template
*/

// Ensure this file is accessed within WordPress
if (!defined('ABSPATH')) exit;

// Fetch existing crossword grid data from the post meta
global $post;
$grid_data = get_post_meta($post->ID, '_crossword_grid_data', true);
$filename = sanitize_title(get_the_title()) . '-crossword.json';

// Prepare data for download if requested
if (isset($_GET['download']) && $_GET['download'] === 'json') {
    header('Content-Type: application/json');
    header("Content-Disposition: attachment; filename=$filename");
    echo $grid_data;
    exit;
}
?>

<div class="fe-crossword-wrapper">

<div class="fe-corssword-header-container">
        <h1><?php the_title(); ?></h1>
    </div>
    <div id="crossword-container">
        <!-- Error Message Display -->
        <div id="error-message" style="display: none; color: red;"></div>

        <!-- Hidden Field to Store Crossword Data -->
        <input type="hidden" id="crossword-data" name="crossword_data" value="<?php echo esc_attr($grid_data); ?>">

        <!-- Crossword Grid Container -->
         <div class="fe-crossword-grid-wrapper">
            <div id="crossword-grid"></div>
        </div>
        <!-- Clues Container -->
        <div id="clues-container">
            <!-- Download Button -->
             <div class= "fe-download-button-container">
                 <a href="?download=json" style="padding: 10px 20px; background-color: #00796b; color: white; text-decoration: none; border-radius: 5px;">Download</a>
            </div>
            <h3>Across</h3>
            <ul id="across-clues"></ul>
            <h3>Down</h3>
            <ul id="down-clues"></ul>
        </div>
    </div>
</div>