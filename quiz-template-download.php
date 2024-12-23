<?php

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// AJAX Handler to Download Quiz PDF (Questions Only)
function kw_download_quiz_pdf_callback() {


    // Check user permissions
    if (!current_user_can('edit_posts')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'wp-quiz-plugin'));
    }

    // Validate and sanitize input
    if (!isset($_GET['quiz_id']) || !is_numeric($_GET['quiz_id'])) {
        wp_die(__('Invalid request.', 'wp-quiz-plugin'));
    }

    
    global $wpdb;
    $quiz_id = intval($_GET['quiz_id']);
    $table_name = $wpdb->prefix . 'quiz_questions';

    // Fetch the title of the quiz post securely
    $quiz_title = esc_html(get_the_title($quiz_id));

    // Fetch the author ID of the quiz post
    $author_id = get_post_field('post_author', $quiz_id);

    // Fetch the author's display name
    $author_name = get_the_author_meta('display_name', $author_id);

    // Output or use the author's name
    if ($author_name) {
        $quiz_author = esc_html($author_name); // Sanitize output
    } else {
        $quiz_author = 'Unknown Author'; // Fallback in case author is not found
    }


    // Fetch quiz questions from the database
    $questions = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE QuizID = %d ORDER BY `Order`", $quiz_id), ARRAY_A);

    if (empty($questions)) {
        wp_die(__('No questions found for this quiz.', 'wp-quiz-plugin'));
    }

    // Get settings from options
    $pdf_quiz_title = $quiz_title;
    $pdf_generated_by_text = $quiz_author;

    // Get style settings from options
    $question_font_size = get_option('wp_quiz_plugin_question_font_size', '12');
    $question_font_color = get_option('wp_quiz_plugin_question_font_color', '#000000');
    $question_font_family = get_option('wp_quiz_plugin_question_font_family', 'dejavusans');
    $question_background_color = get_option('wp_quiz_plugin_question_background_color', '#E0E6F8');

    $student_date_font_size = get_option('wp_quiz_plugin_student_date_font_size', '12');
    $student_date_font_color = get_option('wp_quiz_plugin_student_date_font_color', '#000000');
    $student_date_font_family = get_option('wp_quiz_plugin_student_date_font_family', 'dejavusans');
    $student_date_background_color = get_option('wp_quiz_plugin_student_date_background_color', '#F0F0F0');

    $header_font_size = get_option('wp_quiz_plugin_header_font_size', '16');
    $header_font_color = get_option('wp_quiz_plugin_header_font_color', '#000000');
    $header_font_family = get_option('wp_quiz_plugin_header_font_family', 'dejavusans');

    $title_font_size = get_option('wp_quiz_plugin_title_font_size', '16');
    $title_font_color = get_option('wp_quiz_plugin_title_font_color', '#000000');
    $title_font_family = get_option('wp_quiz_plugin_title_font_family', 'dejavusans');

    $answer_font_size = get_option('wp_quiz_plugin_answer_font_size', '12');
    $answer_font_color = get_option('wp_quiz_plugin_answer_font_color', '#000000');
    $answer_font_family = get_option('wp_quiz_plugin_answer_font_family', 'dejavusans');
    $answer_background_color = get_option('wp_quiz_plugin_answer_background_color', '#FFFFFF');

    // Helper function to convert HEX to RGB
    function hex2rgb($hex) {
        $hex = str_replace("#", "", $hex);
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return array($r, $g, $b);
    }

    $pdf_image_url = esc_url(get_option('wp_quiz_plugin_pdf_image_url'));
    // Create a new PDF document
    $pdf = new TCPDF();

    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor(__($pdf_generated_by_text, 'wp-quiz-plugin'));
    $pdf->SetTitle(__($pdf_quiz_title, 'wp-quiz-plugin'));    

    // Set default header data without an image
    $pdf->SetHeaderData('', 0, __($pdf_quiz_title, 'wp-quiz-plugin'), __($pdf_generated_by_text, 'wp-quiz-plugin'), hex2rgb($header_font_color), array(0, 64, 128));

    
    $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));

    // Set header and footer fonts
    $pdf->setHeaderFont(array($header_font_family, '', $header_font_size));
    $pdf->setFooterFont(array($header_font_family, '', $header_font_size));

    // Set margins
    $pdf->SetMargins(15, 27, 15);
    $pdf->SetHeaderMargin(10);
    $pdf->SetFooterMargin(10);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 25);

    // Set font for the document
    $pdf->SetFont($header_font_family, '', $header_font_size);

    // Add a page
    $pdf->AddPage();
    
    // Add the image to the header if it exists
    if (!empty($pdf_image_url)) {
        // Convert the URL to a file path
        $image_path = ABSPATH . str_replace(home_url('/'), '', $pdf_image_url);

        // Check if the image file exists
        if (file_exists($image_path)) {
            // Position the image more like a logo in the header
            $pdf->Image($image_path, 150, 10, 40, 15, '', '', '', false, 300, '', false, false, 1, false, false, false);

            // Add bottom padding by moving the cursor down after the image
            $pdf->SetY($pdf->GetY() + 5); // Adjusted from 15 to 20 to add 10px extra padding
        } else {
            error_log("Image file not found: " . $image_path); // Log an error if the image file is not found
        }
    }
    
    // Loop through questions and add them to the PDF
    foreach ($questions as $index => $question) {
        // Ensure data is sanitized before outputting to PDF
        $question_title = esc_html($question['Title']);

        // Question Title with Shaded Background
        $pdf->SetFont($question_font_family, 'B', $question_font_size);
        $pdf->SetTextColorArray(hex2rgb($question_font_color));
        $pdf->SetFillColorArray(hex2rgb($question_background_color));
        $pdf->MultiCell(0, 10, ($index + 1) . '. ' . __($question_title, 'wp-quiz-plugin'), 0, 'L', 1);
        $pdf->Ln(2);



        // Show the image if it exists
        if (!empty($question['TitleImage'])) {
            $pdf->Ln(2);
            $image_url = esc_url($question['TitleImage']);
            $pdf->Image($image_url, $pdf->GetX(), $pdf->GetY(), 50, 30, '', '', '', false, 300, '', false, false, 1, false, false, false);
            $pdf->Ln(35);  // Move cursor after image
        } else {
            $pdf->Ln(2); // Add a little space if no image
        }

        // Decode answers JSON safely
        $answers = json_decode($question['Answer'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_die(__('Invalid answer format.', 'wp-quiz-plugin'));
        }

        // Handle different types of questions and display answer options
        $pdf->SetFont($answer_font_family, '', $answer_font_size);
        $pdf->SetTextColorArray(hex2rgb($answer_font_color));
        $pdf->SetFillColorArray(hex2rgb($answer_background_color));
        if ($question['QuestionType'] === 'MCQ') {
            foreach ($answers as $answer) {
                $pdf->MultiCell(0, 10, '☐ ' . esc_html($answer['text']), 0, 'L', 1); // Use MultiCell to break into multiple lines

                // Display image if available
                if (!empty($answer['image'])) {
                    $answer_image_url = esc_url($answer['image']);
                    $pdf->Image($answer_image_url, $pdf->GetX(), $pdf->GetY(), 50, 30, '', '', '', false, 300, '', false, false, 1, false, false, false);
                    $pdf->Ln(35); // Adjust line to move after the image
                }
            }
        } elseif ($question['QuestionType'] === 'T/F') {
            $pdf->MultiCell(0, 10, '( ) True', 0, 'L', 1);  // True option on the first line
            $pdf->MultiCell(0, 10, '( ) False', 0, 'L', 1); // False option on the second line
        } elseif ($question['QuestionType'] === 'Text') {
            $pdf->SetFont($answer_font_family, 'I', $answer_font_size);
            $pdf->MultiCell(0, 10, __('Answer:', 'wp-quiz-plugin'), 0, 'L');
            $pdf->MultiCell(0, 10, '___________________________________________', 0, 'L');
            $pdf->MultiCell(0, 10, '___________________________________________', 0, 'L');
            $pdf->MultiCell(0, 10, '___________________________________________', 0, 'L');
        }

        $pdf->Ln(10); // Add space after each question
    }

    // Output Quiz PDF securely
    $pdf->Output('quiz_' . $quiz_id . '.pdf', 'D'); // D for download, I for inline

    wp_die(); // Stop the execution after generating PDF
}
add_action('wp_ajax_kw_download_quiz_pdf', 'kw_download_quiz_pdf_callback');



// AJAX Handler to Download Answer Key PDF
function kw_download_answer_key_pdf_callback() {
    if (!current_user_can('edit_posts')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'wp-quiz-plugin'));
    }

    if (!isset($_GET['quiz_id']) || !is_numeric($_GET['quiz_id'])) {
        wp_die(__('Invalid request.', 'wp-quiz-plugin'));
    }

    global $wpdb;
    $quiz_id = intval($_GET['quiz_id']);
    $table_name = $wpdb->prefix . 'quiz_questions';

    // Fetch the title of the quiz post securely
    $quiz_title = esc_html(get_the_title($quiz_id));

    // Fetch quiz questions from the database
    $questions = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE QuizID = %d ORDER BY `Order`", $quiz_id), ARRAY_A);

    if (empty($questions)) {
        wp_die(__('No questions found for this quiz.', 'wp-quiz-plugin'));
    }

    // Get settings from options
    $pdf_answer_key_title = get_option('wp_quiz_plugin_pdf_answer_key_title', __('Answer Key', 'wp-quiz-plugin'));
    $pdf_correct_answer_header = get_option('wp_quiz_plugin_pdf_correct_answer_header', __('Correct Answer', 'wp-quiz-plugin'));
    $pdf_question_header = get_option('wp_quiz_plugin_pdf_question_header', __('Question', 'wp-quiz-plugin'));
    $pdf_generated_by_text = get_option('wp_quiz_plugin_pdf_generated_by_text', __('Generated by WP Quiz Plugin', 'wp-quiz-plugin'));

    // Get style settings from options
    $header_font_size = get_option('wp_quiz_plugin_header_font_size', '16');
    $header_font_color = get_option('wp_quiz_plugin_header_font_color', '#000000');
    $header_font_family = get_option('wp_quiz_plugin_header_font_family', 'dejavusans');

    $title_font_size = get_option('wp_quiz_plugin_title_font_size', '16');
    $title_font_color = get_option('wp_quiz_plugin_title_font_color', '#000000');
    $title_font_family = get_option('wp_quiz_plugin_title_font_family', 'dejavusans');

    $answer_font_size = get_option('wp_quiz_plugin_answer_font_size', '12');
    $answer_font_color = get_option('wp_quiz_plugin_answer_font_color', '#000000');
    $answer_font_family = get_option('wp_quiz_plugin_answer_font_family', 'dejavusans');
    $answer_background_color = get_option('wp_quiz_plugin_answer_background_color', '#FFFFFF');

    // Helper function to convert HEX to RGB
    function hex2rgb($hex) {
        $hex = str_replace("#", "", $hex);
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return array($r, $g, $b);
    }

    // Create a new PDF document for the answer key
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor(__($pdf_generated_by_text, 'wp-quiz-plugin'));
    $pdf->SetTitle(__($pdf_answer_key_title, 'wp-quiz-plugin'));
    $pdf->SetHeaderData('', 0, __($pdf_answer_key_title, 'wp-quiz-plugin'), __($pdf_generated_by_text, 'wp-quiz-plugin'), hex2rgb($header_font_color), array(0, 64, 128));
    $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));

    // Set header and footer fonts
    $pdf->setHeaderFont(array($header_font_family, '', $header_font_size));
    $pdf->setFooterFont(array($header_font_family, '', $header_font_size));

    // Set margins
    $pdf->SetMargins(15, 27, 15);
    $pdf->SetHeaderMargin(10);
    $pdf->SetFooterMargin(10);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 25);

    // Set font for the document
    $pdf->SetFont($header_font_family, '', $header_font_size);

    // Add a page
    $pdf->AddPage();

    // Header for Answer Key
    $pdf->SetFont($title_font_family, 'B', $title_font_size);
    $pdf->SetTextColorArray(hex2rgb($title_font_color));
    $pdf->Cell(0, 10, __($pdf_answer_key_title, 'wp-quiz-plugin') . ' for Quiz: ' . __($quiz_title, 'wp-quiz-plugin'), 0, 1, 'C');
    $pdf->Ln(5);

    // Array to store answer key for the table
    $answer_key = [];

    // Loop through questions and add answers to the PDF
    foreach ($questions as $question) {
        // Ensure data is sanitized before outputting to PDF
        $question_title = esc_html($question['Title']);

        // Decode answers JSON safely
        $answers = json_decode($question['Answer'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_die(__('Invalid answer format.', 'wp-quiz-plugin'));
        }

        // Handle different types of questions and extract correct answers
        if ($question['QuestionType'] === 'MCQ' || $question['QuestionType'] === 'T/F') {
            foreach ($answers as $answer) {
                if ($answer['correct']) {
                    $answer_key[] = ['question' => $question_title, 'answer' => esc_html($answer['text'])];
                }
            }
        } elseif ($question['QuestionType'] === 'Text') {
            $answer_key[] = ['question' => $question_title, 'answer' => esc_html($answers[0]['text'])];
        }
    }

    // Set Table Header for Answer Key
    $pdf->SetFont($title_font_family, 'B', 12);
    $pdf->SetFillColorArray(hex2rgb($answer_background_color)); // Background color for header
    $pdf->Cell(100, 10, __($pdf_question_header,'wp-quiz-plugin'), 1, 0, 'C', 1);
    $pdf->Cell(80, 10, __($pdf_correct_answer_header,'wp-quiz-plugin'), 1, 1, 'C', 1);

    // Add Answer Key Rows
    $pdf->SetFont($answer_font_family, '', $answer_font_size);
    $pdf->SetTextColorArray(hex2rgb($answer_font_color));
    foreach ($answer_key as $key) {
        $question_height = $pdf->getStringHeight(100, esc_html($key['question']));
        $answer_height = $pdf->getStringHeight(80, esc_html($key['answer']));
        $row_height = max($question_height, $answer_height);
        
        $pdf->MultiCell(100, $row_height, esc_html($key['question']), 1, 'L', 0, 0, '', '', true, 0, false, true, 0, 'M', false);
        $pdf->MultiCell(80, $row_height, esc_html($key['answer']), 1, 'L', 0, 1, '', '', true, 0, false, true, 0, 'M', false);
    }

    // Output Answer Key PDF securely
    $pdf->Output(__('quiz_odpowiedzi', 'wp-quiz-plugin') . '_' . $quiz_id . '.pdf', 'D'); // 'D' for download

    wp_die(); // Stop the execution after generating PDF
}
add_action('wp_ajax_kw_download_answer_key_pdf', 'kw_download_answer_key_pdf_callback');
