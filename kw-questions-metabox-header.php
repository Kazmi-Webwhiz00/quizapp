<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Top 3 buttons in questions meta box
function kw_render_quiz_buttons($quiz_id) {
   // Condition to Check if there are any questions for this quiz/not
    global $wpdb;
    $table_name = $wpdb->prefix . 'quiz_questions';
    $questions = $wpdb->get_results($wpdb->prepare("SELECT QuesID FROM $table_name WHERE QuizID = %d", $quiz_id), ARRAY_A);

    if (empty($questions)) {
        return '';
    }
    
    $button_font = esc_attr(get_option('wp_quiz_plugin_button_font', 'Arial'));
    $button_color = esc_attr(get_option('wp_quiz_plugin_button_color', '#007bff'));
    $button_font_color = esc_attr(get_option('wp_quiz_plugin_button_font_color', '#ffffff'));
    $button_font_size = esc_attr(get_option('wp_quiz_plugin_button_font_size', '16px'));
    $download_quiz_text = esc_html(get_option('wp_quiz_plugin_download_quiz_text', __('Download Quiz', 'wp-quiz-plugin')));
    $download_answer_key_text = esc_html(get_option('wp_quiz_plugin_download_answer_key_text', __('Download Answer Key', 'wp-quiz-plugin')));
    ob_start();
    ?>
    <div class="download-buttons">
    <!-- Download Quiz Button -->
    <button type="button" id="kw_download-quiz-btn" class="kw_button kw_button-primary" 
            style="font-family: <?php echo esc_attr($button_font); ?>; 
                background-color: <?php echo esc_attr($button_color); ?>; 
                color: <?php echo esc_attr($button_font_color); ?>;font-size: <?php echo esc_attr($button_font_size);?>;">
        <?php echo __($download_quiz_text, 'wp-quiz-plugin') ?>
    </button>

    <!-- Download Answer Key Button -->
    <button type="button" id="kw_download-answer-key-btn" class="kw_button kw_button-secondary" 
            style="font-family: <?php echo esc_attr($button_font); ?>; 
                background-color: <?php echo esc_attr($button_color); ?>; 
                color: <?php echo esc_attr($button_font_color); ?>;font-size: <?php echo esc_attr($button_font_size);?>;">
        <?php echo __($download_answer_key_text, 'wp-quiz-plugin') ?>
    </button>
    </div>
    <!-- Hidden Input for PDF Image URL -->
    <input type="hidden" id="kw_pdf_image_url" name="kw_pdf_image_url" value="<?= esc_attr(get_post_meta($quiz_id, 'kw_quiz_plugin_pdf_image', true)); ?>">

    <script>
        jQuery(document).ready(function ($) {
            // Download Quiz Button Click Event
            $('#kw_download-quiz-btn').on('click', function () {
                window.location.href = ajaxurl + '?action=kw_download_quiz_pdf&quiz_id=<?= esc_js($quiz_id); ?>';
            });

            // Download Answer Key Button Click Event
            $('#kw_download-answer-key-btn').on('click', function () {
                window.location.href = ajaxurl + '?action=kw_download_answer_key_pdf&quiz_id=<?= esc_js($quiz_id); ?>';
            });
        });
    </script>
    <?php
    return ob_get_clean();
}
