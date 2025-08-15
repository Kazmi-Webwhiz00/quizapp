<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Top 3 buttons in questions meta box
function kw_render_quiz_buttons($quiz_id) {
    error_log("Rendering quiz buttons for quiz ID: $quiz_id");
   // Condition to Check if there are any questions for this quiz/not
    global $wpdb;
    $table_name = $wpdb->prefix . 'quiz_questions';
    $questions = $wpdb->get_results($wpdb->prepare("SELECT QuesID FROM $table_name WHERE QuizID = %d", $quiz_id), ARRAY_A);

    // if (empty($questions)) {
    //     return '';
    // }
    
    $button_font = esc_attr(get_option('wp_quiz_plugin_button_font', 'Arial'));
    $button_color = esc_attr(get_option('wp_quiz_plugin_button_color', '#007bff'));
    $button_font_color = esc_attr(get_option('wp_quiz_plugin_button_font_color', '#ffffff'));
    $button_font_size = esc_attr(get_option('wp_quiz_plugin_button_font_size', '16px'));

    $download_quiz_text = esc_html(get_option('wp_quiz_plugin_download_quiz_text', __('Download Quiz', 'wp-quiz-plugin')));
    $download_answer_key_text = esc_html(get_option('wp_quiz_plugin_download_answer_key_text', __('Download Answer Key', 'wp-quiz-plugin')));

    // Fetch settings for the "Download PDF" button
    $download_pdf_text_color = esc_attr(get_option('quiz_download_pdf_text_color', '#ffffff'));
    $download_pdf_bg_color = esc_attr(get_option('quiz_download_pdf_bg_color', '#ffffff'));
    $download_pdf_font_size = esc_attr(get_option('quiz_download_pdf_font_size', '14'));
    // Fetch settings for the "Download Answer Key" button
    $download_answer_key_text_color = esc_attr(get_option('quiz_download_answer_key_text_color', '#ffffff'));
    $download_answer_key_bg_color = esc_attr(get_option('quiz_download_answer_key_bg_color', '#ffffff'));
    $download_answer_key_font_size = esc_attr(get_option('quiz_download_answer_key_font_size', '14'));
    // Fetch settings for the "Add Question" button
    $add_question_with_ai_text =  __('Add Question with AI', 'wp-quiz-plugin');

    ob_start();
    ?>
<div class="header-section">
    <div class="questions-buttons">
    <!-- Add Question Button -->
    <div class="kw_btn kw_btn-primary kw_add-question-btn" id="kw_add-question-btn" 
        style="
            background-color: <?php echo $add_question_bg_color; ?>; 
            color: <?php echo $add_question_text_color; ?>; 
            font-size: <?php echo $add_question_font_size; ?>px;
        ">
        <span class="kw_plus-icon">+</span> 
        <?php echo esc_html(__('Add Question', 'wp-quiz-plugin')); ?>
    </div>

    <!-- AI Generate Button with Dropdown -->
    <div class="kw_ai-generate-container">
        <div class="kw_btn kw_btn-outline kw_add-question-btn" id="kw_generate-question-btn"
            style="
                background-color: <?php echo esc_attr( $generate_with_ai_bg_color ); ?>; 
                color: <?php echo esc_attr( $generate_with_ai_text_color ); ?>; 
                font-size: <?php echo esc_attr( $generate_with_ai_font_size ); ?>px;
            ">
            <span class="kw_ai-icon">🖌️</span>
            <span class="kw_button-text"><?php echo esc_html( $add_question_with_ai_text ); ?></span>
            <span class="dropdown-arrow">▼</span>
        </div>
        <div class="kw_ai-dropdown">
            <div class="kw_ai-option" data-type="prompt">
                <span class="kw_ai-option-icon">💬</span>
                <?php echo esc_html(__('Generate By Prompt', 'wp-quiz-plugin')); ?>
            </div>
            <div class="kw_ai-option" data-type="text">
                <span class="kw_ai-option-icon">📝</span>
                <?php echo esc_html(__('Generate By Text', 'wp-quiz-plugin')); ?>
            </div>
            <div class="kw_ai-option" data-type="image">
                <span class="kw_ai-option-icon">🖼️</span>
                <?php echo esc_html(__('Generate By Image', 'wp-quiz-plugin')); ?>
            </div>
            <div class="kw_ai-option" data-type="pdf">
                <span class="kw_ai-option-icon">📄</span>
                <?php echo esc_html(__('Generate By PDF', 'wp-quiz-plugin')); ?>
            </div>
        </div>
    </div>
    </div>
    <!-- Download Quiz Button -->
    <?php if (!empty($questions)) : ?>
        <div class="download-buttons">
            <button type="button" id="kw_download-quiz-btn" class="kw_button kw_button-primary export-button" 
                style="
                    background-color: <?php echo $download_pdf_bg_color; ?>; 
                    color: <?php echo $download_pdf_text_color; ?>; 
                    font-size: <?php echo $download_pdf_font_size; ?>px;
                ">
                <svg class="icon download-icon" viewBox="0 0 24 24">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7,10 12,15 17,10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span class="text"><?php echo esc_html(__('Download Quiz', 'wp-quiz-plugin')); ?></span>
            </button>

            <button type="button" id="kw_download-answer-key-btn" class="kw_button kw_button-secondary export-button" 
                style="
                    background-color: <?php echo $download_answer_key_bg_color; ?>; 
                    color: <?php echo $download_answer_key_text_color; ?>; 
                    font-size: <?php echo $download_answer_key_font_size; ?>px;
                ">
                <svg class="icon shield-icon" viewBox="0 0 24 24">
                    <path d="M9,12 L11,14 L15,10"></path>
                    <path d="M12,1 L12,1 C17.5,3 21,7 21,12 C21,17 17.5,21 12,23 C6.5,21 3,17 3,12 C3,7 6.5,3 12,1 Z"></path>
                </svg>
                <span class="text"><?php echo esc_html(__('Download Answer Key', 'wp-quiz-plugin')); ?></span>
            </button>
        </div>
    <?php endif; ?>
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
