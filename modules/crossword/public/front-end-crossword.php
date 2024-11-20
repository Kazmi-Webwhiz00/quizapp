<?php
/*
Template Name: Crossword Puzzle Template
*/

// Ensure this file is accessed within WordPress
if (!defined('ABSPATH')) {
    exit;
}

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
    <div id="crossword-container">
        <!-- Error Message Display -->
        <div id="error-message" style="display: none; color: red;"></div>

        <!-- Hidden Field to Store Crossword Data -->
        <input type="hidden" id="crossword-data" name="crossword_data" value="<?php echo esc_attr($grid_data); ?>">

        <!-- Crossword Grid Container -->
        <div class="fe-crossword-grid-wrapper">
            <!-- Download Button -->
            <div class="fe-download-button-container">
                <span class="kw-crossword-reset-button" id="kw-reset-crossword">
                    <svg width="30" height="30" fill="#fff" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                        <path d="M426.667 106.667v42.666l-68.666-.003c36.077 31.659 58.188 77.991 58.146 128.474-.065 78.179-53.242 146.318-129.062 165.376s-154.896-15.838-191.92-84.695C58.141 289.63 72.637 204.42 130.347 151.68a85.33 85.33 0 0 0 33.28 30.507 124.59 124.59 0 0 0-46.294 97.066c1.05 69.942 58.051 126.088 128 126.08 64.072 1.056 118.71-46.195 126.906-109.749 6.124-47.483-15.135-92.74-52.236-118.947L320 256h-42.667V106.667zM202.667 64c23.564 0 42.666 19.103 42.666 42.667s-19.102 42.666-42.666 42.666S160 130.231 160 106.667 179.103 64 202.667 64" fill-rule="white" />
                    </svg>
                </span>
                <div>
                    <div class="fe-download-button-wrapper">
                        <button class="kw-validate-crossword-button" id="validate-crossword">Check Crossword</button>
                        <span id="download-pdf-button" data-crossword-id="<?php echo esc_attr($post->ID); ?>">Download</span>
                    </div>
                    <div class="kw-crossword-fe-replay-container">
                <div class="checkbox-wrapper-16">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" class="checkbox-input" id="check-words" />
                        <span class="checkbox-tile">
                            <span class="checkbox-icon"></span>
                            <span class="checkbox-label">Enable Live Word Check</span>
                        </span>
                    </label>
                </div>
            </div>
                </div>
            </div>
            </div>
            <div id="crossword-grid"></div>

                    <!-- Clues Container -->
        <div id="clues-container-fe">
            <div class="kw-across-clue-wrapper">
                <h3>Across</h3>
                <ul id="across-clues"></ul>
            </div>
            <div class="kw-down-clue-wrapper">
                <h3>Down</h3>
                <ul id="down-clues"></ul>
            </div>
        </div>
        </div>


    </div>
</div>
