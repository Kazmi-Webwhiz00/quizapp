<?php

if (!defined('ABSPATH')) exit; 

include_once plugin_dir_path(__FILE__) . 'quiz-shortcode.php';  // Include Quiz shortcode file

// Enqueue styles and scripts
function my_plugin_enqueue_assets() {
    $plugin_url = plugin_dir_url(__FILE__);

    // Enqueue the CSS file
    wp_enqueue_style(
        'my-plugin-public-functions-style', 
        $plugin_url . 'public-functions-styles.css', 
        array(), 
        '1.0.0', 
        'all'
    );

    // Enqueue the JavaScript file
    // wp_enqueue_script(
    //     'my-plugin-quiz-script',
    //     $plugin_url . 'quiz-script.js', 
    //     array('jquery'), 
    //     '1.0.0', 
    //     true
    // );

    // Localize script to pass data to JavaScript
    wp_localize_script(
        'my-plugin-quiz-script',
        'quiz_ajax_obj',
        array('ajax_url' => admin_url('admin-ajax.php'))
    );
}
add_action('wp_enqueue_scripts', 'my_plugin_enqueue_assets');

// Handle AJAX request to load a quiz question
function wp_quiz_load_question() {
    global $wpdb;
    $quiz_id = intval($_POST['quiz_id']);
    $question_index = intval($_POST['question_index']);
    $table_name = $wpdb->prefix . 'quiz_questions';

    // Fetch questions
    $questions = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE QuizID = %d ORDER BY `Order`", $quiz_id), ARRAY_A);

    if ($question_index < count($questions)) {
        $question = $questions[$question_index];
        $answers = json_decode($question['Answer'], true);

        ob_start(); ?>
        <div class="pf_question">
            <h3><?php echo esc_html($question['Title']); ?></h3>
            <?php if ($question['QuestionType'] === 'MCQ') : ?>
                <?php foreach ($answers as $index => $answer): ?>
                    <div class="pf_answer-option" data-correct="<?php echo $answer['correct']; ?>">
                        <input type="radio" name="answer" value="<?php echo $index; ?>">
                        <label><?php echo esc_html($answer['text']); ?></label>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php
        echo ob_get_clean();
    } else {
        echo '<p>Quiz Completed. Thank you for participating!</p>';
    }
    wp_die();
}
add_action('wp_ajax_load_question', 'wp_quiz_load_question');
add_action('wp_ajax_nopriv_load_question', 'wp_quiz_load_question');

// Include TCPDF library
require_once(plugin_dir_path(__FILE__) . '../lib/tcpdf.php');

// Function to generate PDF
// Function to generate PDF
function my_plugin_generate_pdf() {
    if (!isset($_POST['quiz_data'])) {
        wp_send_json_error('Invalid data.');
        return;
    }

    $quiz_data = json_decode(stripslashes($_POST['quiz_data']), true);

    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('dejavusans', '', 12);

    // Add Title
    $pdf->Cell(0, 10, _x('Quiz Report Card', 'For Report Card PDF', 'wp-quiz-plugin'), 0, 1, 'C');

    // Student Details
    $pdf->Ln(10);
    $pdf->Cell(0, 10, _x('Student Name: ', 'For Report Card PDF', 'wp-quiz-plugin') . $quiz_data['userName'], 0, 1);
    $pdf->Cell(0, 10, _x('Total Questions: ', 'For Report Card PDF', 'wp-quiz-plugin') . $quiz_data['totalQuestions'], 0, 1);
    $pdf->Cell(0, 10, _x('Correct Answers: ', 'For Report Card PDF', 'wp-quiz-plugin') . $quiz_data['correctCount'], 0, 1);
    $pdf->Cell(0, 10, _x('Incorrect Answers: ', 'For Report Card PDF', 'wp-quiz-plugin') . $quiz_data['incorrectCount'], 0, 1);
    $pdf->Cell(0, 10, _x('Score: ', 'For Report Card PDF', 'wp-quiz-plugin') . $quiz_data['scorePercentage'] . '%', 0, 1);


    // Add Question and Answers
    $pdf->Ln(10);
    $pdf->Cell(0, 10, _x('Details:', 'For Report Card PDF', 'wp-quiz-plugin'), 0, 1);

    foreach ($quiz_data['answersData'] as $answer) {
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 10, _x('Question: ', 'For Report Card PDF', 'wp-quiz-plugin') . $answer['question'], 0, 1);

        $pdf->SetFont('dejavusans', '', 12);
        $pdf->Cell(95, 10, _x('Correct Answer:', 'For Report Card PDF', 'wp-quiz-plugin'), 0, 0);
        $pdf->Cell(95, 10, _x('Your Answer:', 'For Report Card PDF', 'wp-quiz-plugin'), 0, 1);


        // Fetch correct answer and user's answer text
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->MultiCell(95, 10, $answer['correctAnswer'], 1, 'L', false, 0);
        $userAnswer = isset($answer['userAnswer']) ? $answer['userAnswer'] : 'No Answer Provided';
        $pdf->MultiCell(95, 10, $userAnswer, 1, 'L', false, 1);

        $pdf->Ln(5);
    }

    // Output PDF as download
    $pdf->Output('quiz_report_card.pdf', 'D');
    wp_die();
}


add_action('wp_ajax_generate_pdf', 'my_plugin_generate_pdf');
add_action('wp_ajax_nopriv_generate_pdf', 'my_plugin_generate_pdf');

// Store Quiz Submission
function my_plugin_store_quiz_submission() {
    global $wpdb;

    // Table name with prefix
    $table_name = $wpdb->prefix . 'kw_submissions';

    // Create the table if it doesn't exist
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        // SQL to create the table
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            UserID BIGINT(20) UNSIGNED NOT NULL,
            QuizID BIGINT(20) UNSIGNED NOT NULL,
            Score FLOAT NOT NULL,
            SubmissionData TEXT NOT NULL,
            LearnerName VARCHAR(255) NOT NULL,
            SubmittedAt DATETIME NOT NULL,
            PRIMARY KEY  (ID)
        ) $charset_collate;";

        // Include the file that provides dbDelta
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    if (!isset($_POST['quiz_data'])) {
        wp_send_json_error('Invalid data.');
        return;
    }

    // Process quiz submission data
    $quiz_data = json_decode(stripslashes($_POST['quiz_data']), true);
    $user_id = get_current_user_id();
    $quiz_id = intval($quiz_data['quizId']);
    $score = floatval($quiz_data['score']);
    $submission_data = json_encode($quiz_data['answersData']);
    $learner_name = sanitize_text_field($quiz_data['userName']);

    // Insert submission into database
    $wpdb->insert(
        $table_name,
        array(
            'UserID' => $user_id,
            'QuizID' => $quiz_id,
            'Score' => $score,
            'SubmissionData' => $submission_data,
            'LearnerName' => $learner_name,
            'SubmittedAt' => current_time('mysql'),
        ),
        array('%d', '%d', '%f', '%s', '%s', '%s')
    );

    // Check the result of the insert operation
    if ($wpdb->insert_id) {
        wp_send_json_success('Submission stored successfully.');
    } else {
        wp_send_json_error('Failed to store submission.');
    }
}
add_action('wp_ajax_store_quiz_submission', 'my_plugin_store_quiz_submission');
add_action('wp_ajax_nopriv_store_quiz_submission', 'my_plugin_store_quiz_submission');




?>
