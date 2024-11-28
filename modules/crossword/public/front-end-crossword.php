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

// Fetch font and style options
$clue_title_font_color = esc_attr(get_option('kw_fe_clue_title_font_color', '#000000'));
$clue_title_font_size = intval(get_option('kw_fe_clue_title_font_size', 25)) . 'px';
$clue_title_font_family = esc_attr(get_option('kw_fe_clue_title_font_family', 'Arial'));

$restart_button_bg_color = esc_attr(get_option('kw_fe_restart_button_color', '#00796b'));
$restart_button_text_color = esc_attr(get_option('kw_fe_restart_button_text_color', '#ffffff'));
$restart_button_font_size = esc_attr(get_option('kw_fe_restart_button_font_size', '16px'));
$restart_button_font_family = esc_attr(get_option('kw_fe_restart_button_font_family', 'Arial, sans-serif'));
$restart_button_icon_color = esc_attr(get_option('kw_fe_restart_button_icon_color', '#ffffff'));
$restart_button_label = esc_html(get_option('kw_fe_restart_button_label', ''));

$download_button_bg_color = esc_attr(get_option('kw_fe_download_button_bg_color', '#00796b'));
$download_button_text_color = esc_attr(get_option('kw_fe_download_button_text_color', '#ffffff'));
$download_button_font_size = esc_attr(get_option('kw_fe_download_button_font_size', '16px'));
$download_button_font_family = esc_attr(get_option('kw_fe_download_button_font_family', 'Arial, sans-serif'));
$download_button_label = esc_html(get_option('kw_fe_download_button_text', 'Download'));

$check_button_bg_color = esc_attr(get_option('kw_fe_check_crossword_button_bg_color', '#00796b'));
$check_button_text_color = esc_attr(get_option('kw_fe_check_crossword_button_text_color', '#CE2525'));
$check_button_font_size = esc_attr(get_option('kw_fe_check_crossword_button_font_size', '16px'));
$check_button_font_family = esc_attr(get_option('kw_fe_check_crossword_button_font_family', 'Arial, sans-serif'));
$check_button_label = esc_html(get_option('kw_fe_check_crossword_button_text', 'Check Crossword'));

$check_live_enabled_bg_color = esc_attr(get_option('kw_fe_enable_live_word_check_button_enabled_color', 'white'));
$check_live_bg_color = esc_attr(get_option('kw_fe_enable_live_word_check_button_bg_color', 'white'));
$live_word_check_font_color = esc_attr(get_option('kw_fe_enable_live_word_check_button_text_color', '#000000'));
$live_word_check_font_size = intval(get_option('kw_fe_enable_live_word_check_button_font_size', 14)) . 'px';
$live_word_check_font_family = esc_attr(get_option('kw_fe_enable_live_word_check_button_font_family', 'Arial'));
$live_word_check_label = esc_html(get_option('kw_fe_enable_live_word_check_button_text', 'Enable Live Word Check'));

$clue_across_text = esc_html(get_option('kw_fe_clue_title_across', 'Across'));
$clue_down_text = esc_html(get_option('kw_fe_clue_title_down', 'Down'));

$error_message_color = esc_attr(get_option('kw_fe_error_message_color', '#ff0000'));

?>

<div class="fe-crossword-wrapper">
    <div id="crossword-container">
        <!-- Error Message Display -->
        <div id="error-message" style="display: none; color: <?php echo $error_message_color; ?>;"></div>

        <!-- Hidden Field to Store Crossword Data -->
        <input type="hidden" id="crossword-data" name="crossword_data" value="<?php echo esc_attr($grid_data); ?>">

        <!-- Crossword Grid Container -->
        <div class="fe-crossword-grid-wrapper">
            <!-- Download Button -->
            <div class="fe-download-button-container">
                <span class="kw-crossword-reset-button" id="kw-reset-crossword" style="background-color: <?php echo $restart_button_bg_color; ?>;">
                    <svg width="30" height="30" fill="<?php echo $restart_button_icon_color; ?>" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                        <path d="M426.667 106.667v42.666l-68.666-.003c36.077 31.659 58.188 77.991 58.146 128.474-.065 78.179-53.242 146.318-129.062 165.376s-154.896-15.838-191.92-84.695C58.141 289.63 72.637 204.42 130.347 151.68a85.33 85.33 0 0 0 33.28 30.507 124.59 124.59 0 0 0-46.294 97.066c1.05 69.942 58.051 126.088 128 126.08 64.072 1.056 118.71-46.195 126.906-109.749 6.124-47.483-15.135-92.74-52.236-118.947L320 256h-42.667V106.667zM202.667 64c23.564 0 42.666 19.103 42.666 42.667s-19.102 42.666-42.666 42.666S160 130.231 160 106.667 179.103 64 202.667 64" fill-rule="white" />
                    </svg>
                    <?php if ($restart_button_label) { ?>
                        <span style="color: <?php echo $restart_button_text_color; ?>; font-size: <?php echo $restart_button_font_size; ?>; font-family: <?php echo $restart_button_font_family; ?>;"><?php echo $restart_button_label; ?></span>
                    <?php } ?>
                </span>
                <div>
                    <div class="fe-download-button-wrapper">
                        <button class="kw-validate-crossword-button" id="validate-crossword" style="background-color: <?php echo $check_button_bg_color; ?>; color: <?php echo $check_button_text_color; ?>; font-size: <?php echo $check_button_font_size; ?>; font-family: <?php echo $check_button_font_family; ?>;"><?php echo __($check_button_label,'wp-quiz-plugin'); ?></button>
                        <span id="download-pdf-button-fe" data-crossword-id="<?php echo esc_attr($post->ID); ?>" style="background-color: <?php echo $download_button_bg_color; ?>; color: <?php echo $download_button_text_color; ?>; font-size: <?php echo $download_button_font_size; ?>; font-family: <?php echo $download_button_font_family; ?>;"><?php echo __($download_button_label,'wp-quiz-plugin'); ?></span>
                    </div>
                    <div class="kw-crossword-fe-replay-container">
                        <div class="checkbox-wrapper-16">
                            <label class="checkbox-wrapper">
                                <input type="checkbox" class="checkbox-input" id="check-words" />
                                <span class="checkbox-tile" style="background-color: <?php echo $check_live_bg_color; ?>;">
                                    <span class="checkbox-icon"></span>
                                    <span class="checkbox-label" style="color: <?php echo $live_word_check_font_color; ?>; font-size: <?php echo $live_word_check_font_size; ?>; font-family: <?php echo $live_word_check_font_family; ?>;"><?php echo __($live_word_check_label,'wp-quiz-plugin'); ?></span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div id="crossword-grid"></div>

            <!-- Clues Container -->
            <div id="clues-container-fe">
                <div class="kw-across-clue-wrapper">
                    <h3 style="color: <?php echo $clue_title_font_color; ?>; font-size: <?php echo $clue_title_font_size; ?>; font-family: <?php echo $clue_title_font_family; ?>; font-weight: bold;"><?php echo __($clue_across_text,'wp-quiz-plugin'); ?></h3>
                    <ul id="across-clues"></ul>
                </div>
                <div class="kw-down-clue-wrapper">
                    <h3 style="color: <?php echo $clue_title_font_color; ?>; font-size: <?php echo $clue_title_font_size; ?>; font-family: <?php echo $clue_title_font_family; ?>; font-weight: bold;"><?php echo __($clue_down_text,'wp-quiz-plugin'); ?></h3>
                    <ul id="down-clues"></ul>
                </div>
            </div>

        </div>


    </div>
</div>
