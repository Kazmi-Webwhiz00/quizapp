<?php

if (!defined('ABSPATH')) exit;

// Fetch the selected preset from the saved options
$preset = get_option('kw_quiz_shortcode_preset', 'preset1');

// Use switch to include the correct preset file based on the selected value
switch ($preset) {
    case 'preset2':
        include_once plugin_dir_path(__FILE__) . 'quiz-preset-2.php';
        break;

    case 'preset1':
    default:
        include_once plugin_dir_path(__FILE__) . 'quiz-preset-1.php';
        break;
}

// Shortcode to display the quiz
function wp_quiz_display_shortcode($atts) {
    // Fetch style settings from options
    // $font_family = esc_attr(get_option('wp_quiz_plugin_font_family', 'Arial'));
    // $font_color = esc_attr(get_option('wp_quiz_plugin_font_color', '#000000'));
    $background_color = esc_attr(get_option('wp_quiz_plugin_background_color', '#ffffff'));
    $button_background_color = esc_attr(get_option('wp_quiz_plugin_button_background_color', '#007bff'));
    $button_text_color = esc_attr(get_option('wp_quiz_plugin_button_text_color', '#ffffff'));
    $progress_bar_color = esc_attr(get_option('wp_quiz_plugin_progress_bar_color', '#4CAF50'));
    $progress_bar_background_color = esc_attr(get_option('wp_quiz_plugin_progress_bar_background_color', '#f1f1f1'));
    
    // Fetch style settings from options for questions
    $question_font_family = esc_attr(get_option('wp_quiz_plugin_frontend_question_font_family', 'Arial'));
    $question_font_color = esc_attr(get_option('wp_quiz_plugin_frontend_question_font_color', '#000000'));
    $question_font_size = esc_attr(get_option('wp_quiz_plugin_frontend_question_font_size', '16px'));
    // Fetch style settings from options for answers
    $answer_font_family = esc_attr(get_option('wp_quiz_plugin_frontend_answer_font_family', 'Arial'));
    $answer_font_color = esc_attr(get_option('wp_quiz_plugin_frontend_answer_font_color', '#000000'));
    $answer_font_size = esc_attr(get_option('wp_quiz_plugin_frontend_answer_font_size', '16px'));


    // 1) Parse explicit `id` attribute
    $atts    = shortcode_atts( [ 'id' => 0 ], $atts, 'wp_quiz' );
    $quiz_id = absint( $atts['id'] );

    // 2) Try Divi builder preview params
    if ( ! $quiz_id ) {
        $quiz_id = isset( $_GET['p'] )       ? absint( $_GET['p'] )
                 : ( isset( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : 0 );
    }

    // 3) Finally fall back to the queried object (the real post slug)
    if ( ! $quiz_id ) {
        $quiz_id = absint( get_queried_object_id() );
    }

    // 4) Ensure it’s a valid “quizzes” post
    if ( !is_singular('quizzes')) {
        return '<p>This shortcode is only valid on a quiz post.</p>';
    }
    else if ( ! $quiz_id ) {
        return '<p>No quiz ID provided.</p>';
    }
    // 5) Ensure the quiz exists

    // Fetch quiz questions
    global $wpdb;
    $table_name = $wpdb->prefix . 'quiz_questions';
    $questions = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE QuizID = %d ORDER BY `Order`", $quiz_id), ARRAY_A);

    if (empty($questions)) {
        return '<p>No questions found for this quiz.</p>';
    }

    // Render initial quiz container
    // Call the reusable UI function
    return wp_quiz_render_ui($quiz_id, $questions , $background_color, $button_background_color, $button_text_color, $progress_bar_color, $progress_bar_background_color, $question_font_family, $question_font_color, $question_font_size, $answer_font_family, $answer_font_color, $answer_font_size);

  // return ob_get_clean();
}
add_shortcode('wp_quiz', 'wp_quiz_display_shortcode');
function custom_quiz_footer_styles() {
    $next_button_gap = get_option('kw_quiz_preset_back_next_button_gap', '10'); // Default is 10px
    ?>
    <style>
    .pf_quiz-footer {
        display: flex;
        justify-content: space-between;
        gap: <?php echo esc_attr($next_button_gap); ?>px;
        margin-top: 20px;
        background-color: #ffffff;
        z-index: 2;
    }
    </style>
    <?php
}
add_action('wp_head', 'custom_quiz_footer_styles');
