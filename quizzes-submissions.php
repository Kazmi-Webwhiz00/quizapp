<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Add Submissions Page under Quizzes
function wp_quiz_plugin_add_submissions_page() {
    // Fetch the dynamic setting for 'Submissions' menu text
    $quizzes_submissions_menu_text = get_option('wp_quiz_plugin_menu_submissions_text', __('Submissions', 'wp-quiz-plugin'));

    add_submenu_page(
        'edit.php?post_type=quizzes',   // Parent slug (custom post type 'quizzes')
        __($quizzes_submissions_menu_text, 'wp-quiz-plugin'),                 // Page title
        __($quizzes_submissions_menu_text, 'wp-quiz-plugin'),                 // Menu title
        'read',                        // Capability required to access the page (changed to 'read')
        'wp-quiz-submissions',         // Menu slug
        'wp_quiz_plugin_submissions_page' // Callback function to display the page
    );
}
add_action('admin_menu', 'wp_quiz_plugin_add_submissions_page');

function wp_quiz_plugin_submissions_page() {
    global $wpdb;
    $current_user = wp_get_current_user();
    $user_id = get_current_user_id();
    $table_name = $wpdb->prefix . 'kw_submissions';

    // Query to fetch submissions
    if (current_user_can('administrator')) {
        // Admins see all submissions with additional info regarding quiz authors
        $submissions = $wpdb->get_results("
            SELECT s.*, 
                   s.LearnerName AS learner_name, 
                   p.post_author AS quiz_author_id,
                   p.post_title AS quiz_title,  /* Added to fetch quiz title */
                   au.user_login AS quiz_author 
            FROM $table_name s 
            LEFT JOIN {$wpdb->posts} p ON s.QuizID = p.ID 
            LEFT JOIN {$wpdb->users} au ON p.post_author = au.ID", ARRAY_A);

        // Render the bulk delete buttons for admins
        echo '<div class="bulk-delete-container" style="margin-bottom: 20px; margin-top: 20px;">
                <button id="bulk-delete-button" class="button button-danger">Delete Submissions Older Than 30 Days</button>
                <button id="delete-all-button" class="button button-danger">Delete All Submissions</button> <!-- New Button -->
            </div>';
    } else {
        // Other users see only their quiz submissions where they are the quiz author
        $submissions = $wpdb->get_results($wpdb->prepare("
            SELECT s.*, 
                   s.LearnerName AS learner_name, 
                   p.post_author AS quiz_author_id,
                   p.post_title AS quiz_title,  /* Added to fetch quiz title */
                   au.user_login AS quiz_author 
            FROM $table_name s 
            LEFT JOIN {$wpdb->posts} p ON s.QuizID = p.ID 
            LEFT JOIN {$wpdb->users} au ON p.post_author = au.ID 
            WHERE p.post_author = %d", $user_id), ARRAY_A);
    }

    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Quiz Submissions', 'wp-quiz-plugin'); ?></h1>
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Quiz ID', 'wp-quiz-plugin'); ?></th>
                    <th><?php esc_html_e('Quiz Title', 'wp-quiz-plugin'); ?></th> 
                    <th><?php esc_html_e('Learner Name', 'wp-quiz-plugin'); ?></th>
                    <th><?php esc_html_e('Score', 'wp-quiz-plugin'); ?></th>
                    <th><?php esc_html_e('Submission Date', 'wp-quiz-plugin'); ?></th>
                    <th><?php esc_html_e('Quiz Author', 'wp-quiz-plugin'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($submissions)) : ?>
                    <?php foreach ($submissions as $submission) : ?>
                        <tr>
                            <td><?php echo esc_html($submission['QuizID']); ?></td>
                            <td>
                                <a href="<?php echo esc_url(get_permalink($submission['QuizID'])); ?>"> <!-- Link to Quiz -->
                                    <?php echo esc_html($submission['quiz_title']); ?> <!-- Display Quiz Title -->
                                </a>
                            </td>
                            <td><?php echo esc_html($submission['learner_name']); ?></td>
                            <td><?php echo esc_html($submission['Score']); ?>%</td>
                            <td><?php echo esc_html($submission['SubmittedAt']); ?></td>
                            <td><?php echo esc_html($submission['quiz_author']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5"><?php esc_html_e('No submissions found.', 'wp-quiz-plugin'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Inline JavaScript for AJAX handling -->
    <script>
    jQuery(document).ready(function ($) {
        $('#bulk-delete-button').on('click', function () {
            if (!confirm('Are you sure you want to delete submissions older than 30 days? This action cannot be undone.')) {
                return;
            }

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'bulk_delete_old_submissions'
                },
                success: function (response) {
                    console.log(response); // Log the response for debugging
                    if (response.success) {
                        alert('Submissions older than 30 days have been deleted.');
                        location.reload(); // Reload the page to reflect changes
                    } else {
                        alert('Failed to delete old submissions. Please try again.');
                    }
                },
                error: function (xhr, status, error) {
                    console.log(xhr, status, error); // Log the error for debugging
                    alert('An error occurred while processing your request.');
                }
            });
        });

        $('#delete-all-button').on('click', function () {
            if (!confirm('Are you sure you want to delete ALL submissions? This action cannot be undone.')) {
                return;
            }

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'delete_all_submissions'
                },
                success: function (response) {
                    console.log(response); // Log the response for debugging
                    if (response.success) {
                        alert('All submissions have been deleted.');
                        location.reload(); // Reload the page to reflect changes
                    } else {
                        alert('Failed to delete all submissions. Please try again.');
                    }
                },
                error: function (xhr, status, error) {
                    console.log(xhr, status, error); // Log the error for debugging
                    alert('An error occurred while processing your request.');
                }
            });
        });
    });
    </script>
    <?php
}

// Handle AJAX request to bulk delete submissions older than 30 days
function my_plugin_bulk_delete_old_submissions() {
    if (!current_user_can('administrator')) {
        wp_send_json_error('Unauthorized access.');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'kw_submissions';

    $deleted = $wpdb->query("DELETE FROM $table_name WHERE SubmittedAt < (NOW() - INTERVAL 30 DAY)");

    if ($deleted !== false) {
        wp_send_json_success('Submissions older than 30 days have been deleted.');
    } else {
        wp_send_json_error('Failed to delete old submissions.');
    }
}

add_action('wp_ajax_bulk_delete_old_submissions', 'my_plugin_bulk_delete_old_submissions');

// Handle AJAX request to delete all submissions
function my_plugin_delete_all_submissions() {
    if (!current_user_can('administrator')) {
        wp_send_json_error('Unauthorized access.');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'kw_submissions';

    $deleted = $wpdb->query("DELETE FROM $table_name");

    if ($deleted !== false) {
        wp_send_json_success('All submissions have been deleted.');
    } else {
        wp_send_json_error('Failed to delete all submissions.');
    }
}

add_action('wp_ajax_delete_all_submissions', 'my_plugin_delete_all_submissions');

?>
