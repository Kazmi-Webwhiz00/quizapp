<?php

if (!defined('ABSPATH')) exit;


/**
 * AJAX: save selection.
 * Priority: post meta (if quiz_id provided & user can edit) → user meta (if logged-in) → option (guest fallback).
 */

add_action('wp_ajax_save_source_text_choice', 'save_source_text_choice_cb');
add_action('wp_ajax_nopriv_save_source_text_choice', 'save_source_text_choice_cb');

function save_source_text_choice_cb() {
    if (empty($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'gpt_source_choice')) {
        wp_send_json_error(array('message' => 'Invalid nonce'), 403);
    }

    // Normalize inputs
    $include = isset($_POST['include_source']) ? intval($_POST['include_source']) : 0;
    $include = $include === 1 ? 1 : 0;

    $quiz_id = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;

    $source_text = isset($_POST['source_text']) ? sanitize_textarea_field( wp_unslash( $_POST['source_text'] ) ) : '';

    // Try saving to the quiz post
    if ($quiz_id > 0) {
        if (!current_user_can('edit_post', $quiz_id)) {
            wp_send_json_error(array('message' => 'Permission denied for quiz_id ' . $quiz_id), 403);
        }
        update_post_meta($quiz_id, '_quiz_include_source', $include);
        update_post_meta($quiz_id, '_quiz_source_text', $source_text);

        $includeText = get_post_meta($quiz_id, '_quiz_include_source', true);
        $sourceText = get_post_meta($quiz_id, '_quiz_source_text', true);

        wp_send_json_success(array(
            'saved'          => true,
            'scope'          => 'post_meta',
            'quiz_id'        => $quiz_id,
            'include_source' => $include,
            'source_text'    => $sourceText,
        ));
    }
}

/* -------------------------------
 * Public helper accessors (optional)
 * Use anywhere in PHP to read the saved value.
 * Returns 1 (include) or 0 (exclude).
 * ------------------------------- */

if (!function_exists('get_quiz_include_source')) {
    function get_quiz_include_source($quiz_id) {
        $quiz_id = intval($quiz_id);
        if ($quiz_id <= 0) return 0;
        $val = get_post_meta($quiz_id, '_quiz_include_source', true);
        return (string)$val === '1' ? 1 : 0;
    }
}
