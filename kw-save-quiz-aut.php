<?php


function save_generated_quiz_question_backend($quiz_id, $question_data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'quiz_questions';

    // Prepare the question data
    $title = sanitize_text_field($question_data['title'] ?? '');
    $title_image = esc_url($question_data['title_image'] ?? '');
    $question_type = sanitize_text_field($question_data['question_type'] ?? '');
    
    $answers = $question_data['answers'] ?? [];

    // Prepare answers according to the question type
    $prepared_answers = [];

    if ($question_type === 'T/F') {
        // Determine which option is marked as correct inside the answers array
        $correct_index = -1; // Default to no correct answer
    
        foreach ($answers as $index => $answer) {
            if (!empty($answer['correct']) && intval($answer['correct']) === 1) {
                $correct_index = $index; // Store correct answer index
                break;
            }
        }
    
        // Ensure True and False answers are formatted correctly
        $prepared_answers[] = [
            'text' => __('True', 'wp-quiz-plugin'),
            'correct' => ($correct_index === 0) ? 1 : 0, // Mark correct answer
        ];
        $prepared_answers[] = [
            'text' => __('False', 'wp-quiz-plugin'),
            'correct' => ($correct_index === 1) ? 1 : 0, // Mark correct answer
        ];
    }else {
        // Process answers for other question types
        foreach ($answers as $answer) {
            $prepared_answers[] = [
                'text' => ($question_type === 'Text') ? wp_kses_post($answer['text']) : sanitize_text_field($answer['text']),
                'correct' => ($question_type === 'Text') ? 1 : (isset($answer['correct']) && $answer['correct'] == 1 ? 1 : 0),
                'image' => isset($answer['image']) ? esc_url_raw($answer['image']) : '' // Add image URL if available
            ];
        }
    }

    // Convert to JSON format
    $answers_json = wp_json_encode($prepared_answers);

    // Debugging: Log sanitized values before inserting
    error_log("✅:::::::::::Inserting Data - QuizID: $quiz_id, Title: $title, Image: $title_image, Type: $question_type, Answers: $answers_json");

    // Insert the data into the database
    $result = $wpdb->insert(
        $table_name,
        [
            'QuizID'       => $quiz_id,
            'Title'        => $title,
            'TitleImage'   => $title_image,
            'Answer'       => $answers_json,  // Store formatted answers
            'QuestionType' => $question_type,
            'Order'        => 0
        ],
        ['%d', '%s', '%s', '%s', '%s', '%d']
    );

    // Debugging: Log SQL query and error
    if ($wpdb->last_error) {
        error_log("❌ Database Insert Error: " . $wpdb->last_error);
        error_log("❌ SQL Query: " . $wpdb->last_query);
        return ['success' => false, 'message' => 'Error saving question: ' . $wpdb->last_error];
    }

    return ['success' => true, 'question_id' => $wpdb->insert_id];
}



function ajax_save_generated_quiz_question() {
    error_log("postata:::::::::::");
    error_log(print_r($_POST,true));
    if (!isset($_POST['question_data']) || !isset($_POST['security']) || !isset($_POST['post_id'])) {
        error_log("❌ AJAX Error: Missing data in request.");
        wp_send_json_error(['message' => 'Invalid request - Missing data']);
    }

    if (!wp_verify_nonce($_POST['security'], 'auto-save-quiz-noce')) {
        error_log("❌ AJAX Error: Nonce verification failed.");
        wp_send_json_error(['message' => 'Security check failed']);
    }

    $quiz_id = intval($_POST['post_id']);
    if (!$quiz_id) {
        error_log("❌ AJAX Error: Invalid quiz ID.");
        wp_send_json_error(['message' => 'Invalid quiz ID']);
    }

    $question_data = json_decode(stripslashes($_POST['question_data']), true);
    
    // Debugging: Log received question data
    error_log("✅ ::::::::Received Question Data: " . print_r($question_data, true));

    if (!$question_data) {
        error_log("❌ AJAX Error: Failed to decode question data.");
        wp_send_json_error(['message' => 'Invalid question data format']);
    }

    $result = save_generated_quiz_question_backend($quiz_id, $question_data);
    if ($result['success']) {
        wp_send_json_success(['question_id' => $result['question_id']]);
    } else {
        error_log("❌ Final Insert Error: " . $result['message']);
        wp_send_json_error(['message' => $result['message']]);
    }
}
add_action('wp_ajax_save_generated_quiz_question', 'ajax_save_generated_quiz_question');




add_action('wp_ajax_update_autodraft_post', 'update_autodraft_post_callback');
function update_autodraft_post_callback() {
    
    // Get post ID and title from AJAX request
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $post_title = isset($_POST['post_title']) ? sanitize_text_field($_POST['post_title']) : '';
    $post_status = isset($_POST['post_status']) ? sanitize_text_field($_POST['post_status']) : 'auto-draft';
    

    if ($post_id && !empty($post_title)) {
        $post = get_post($post_id);
       
        if ($post && $post_status === 'auto-draft') {
            // Update post status to 'draft' and set new title
            wp_update_post([
                'ID'          => $post_id,
                'post_status' => 'draft',
                'post_title'  => $post_title
            ]);

            wp_send_json_success(['message' => 'Post updated successfully.']);
        } else {
            wp_send_json_error(['message' => 'Post is not in auto-draft status.']);
        }
    } else {
        wp_send_json_error(['message' => 'Invalid post data.']);
    }


}
