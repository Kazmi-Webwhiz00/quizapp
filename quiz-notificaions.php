<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Create custom database table for notifications
function create_quiz_notifications_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'quiz_notifications'; // Table name with WordPress prefix

    $charset_collate = $wpdb->get_charset_collate();

    // SQL statement for creating the custom table with all fields
    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,  -- Primary Key
        message TEXT NOT NULL,                           -- Message Content
        users TEXT NOT NULL,                             -- JSON-encoded array of user IDs
        unread_users TEXT NOT NULL,                      -- JSON-encoded array of unread user IDs
        date DATETIME DEFAULT CURRENT_TIMESTAMP,         -- Date of Notification
        PRIMARY KEY (id)                                 -- Define Primary Key
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql); // Create or upgrade the table

    // Debugging - Check for errors
    if (!empty($wpdb->last_error)) {
        error_log('Error creating table: ' . $wpdb->last_error);
    }
}
register_activation_hook(__FILE__, 'create_quiz_notifications_table');

// Add Admin Page for Notifications under Quizzes
function wp_quiz_plugin_add_notification_page() {
    // Fetch the dynamic setting for 'Notifications' menu text
    $quizzes_notifications_menu_text = get_option('wp_quiz_plugin_menu_notifications_text', __('Notifications', 'wp-quiz-plugin'));

    add_submenu_page(
        'edit.php?post_type=quizzes',   // Parent slug (custom post type 'quizzes')
        __($quizzes_notifications_menu_text, 'wp-quiz-plugin'),               // Page title
        __($quizzes_notifications_menu_text, 'wp-quiz-plugin'),              // Menu title
        'read',                         // Capability required to access the page
        'wp-quiz-notifications',        // Menu slug
        'wp_quiz_plugin_notifications_page' // Callback function to display the page
    );
}
add_action('admin_menu', 'wp_quiz_plugin_add_notification_page');

// Display Notifications Page Content
function wp_quiz_plugin_notifications_page() {
    global $wpdb;
    $users = get_users(); // Get all registered users
    $table_name = $wpdb->prefix . 'quiz_notifications';

    // Mark notifications as read for the current user
    if (is_user_logged_in()) {
        mark_notifications_as_read(get_current_user_id());
    }

    // Handle Form Submission for Sending Messages (Admins only)
    if (current_user_can('manage_options') && isset($_POST['send_message'])) {
        check_admin_referer('send_message_nonce_action', 'send_message_nonce_field'); // Security check

        $message = sanitize_textarea_field($_POST['message']);
        $selected_users = isset($_POST['selected_users']) ? array_map('sanitize_text_field', $_POST['selected_users']) : [];

        $unread_users = $selected_users;  // Set all selected users as unread

        // Store message in the database
        $wpdb->insert(
            $table_name,  // Table name
            array(
                'message' => $message,
                'users' => json_encode($selected_users), // Store selected users as JSON
                'unread_users' => json_encode($unread_users), // Store unread users as JSON
                'date' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s')
        );

        echo '<div class="notice notice-success is-dismissible"><p>Message sent successfully!</p></div>';
    }

    // Handle Notification Deletion (Admins only)
    if (current_user_can('manage_options') && isset($_GET['delete_notification'])) {
        check_admin_referer('delete_notification_nonce_action'); // Correct nonce verification

        $notification_id = intval($_GET['delete_notification']);
        $wpdb->delete($table_name, array('id' => $notification_id));
        echo '<div class="notice notice-success is-dismissible"><p>Notification deleted successfully!</p></div>';
    }

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Notifications</h1>
        <hr class="wp-header-end">

        <?php if (current_user_can('manage_options')): ?>
        <!-- Admin View: Show Add Notification Form -->
        <h2>Add New Notification</h2>
        <form method="post" action="" class="notification-form">
            <?php wp_nonce_field('send_message_nonce_action', 'send_message_nonce_field'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="message">Message:</label>
                    </th>
                    <td>
                        <textarea name="message" id="message" rows="5" style="width: 100%;" required></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="selected_users">Select Users:</label>
                    </th>
                    <td>
                        <select name="selected_users[]" id="selected_users" class="widefat" multiple>
                            <option value="all">All Users</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo esc_attr($user->ID); ?>"><?php echo esc_html($user->display_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">Hold down the Ctrl (Windows) or Command (Mac) button to select multiple options.</p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="send_message" value="Send Message" class="button button-primary">
            </p>
        </form>
        <hr>
        <?php endif; ?>

        <!-- Show Notifications to All Users -->
        <h2>All Notifications</h2>
        <div class="quiz-notifications">
            <?php
                // Fetch notifications from the database
                $notifications = $wpdb->get_results("SELECT * FROM $table_name ORDER BY date DESC", ARRAY_A);

                if (!empty($notifications)) {
                    echo '<div class="accordion" id="notificationsAccordion">';
                    foreach ($notifications as $index => $notification) {
                        $collapse_id = 'collapse-' . $notification['id'];
                        echo '<div class="accordion-item">';
                        echo '<div id="' . $collapse_id . '" class="accordion-collapse collapse" aria-labelledby="heading-' . $notification['id'] . '" data-bs-parent="#notificationsAccordion">';
                        echo '<div class="accordion-body">';
                        echo '<p>' . esc_html($notification['message']) . '</p>';
                        echo '<small>Sent on: ' . esc_html($notification['date']) . '</small>';
                        if (current_user_can('manage_options')) {
                            // Add Delete button for Admins with correctly constructed URL
                            $delete_url = esc_url(add_query_arg(array(
                                'page' => 'wp-quiz-notifications',
                                'delete_notification' => intval($notification['id']),
                                '_wpnonce' => wp_create_nonce('delete_notification_nonce_action')
                            ), admin_url('admin.php')));

                            echo ' <a href="' . $delete_url . '" class="button button-secondary">Delete</a>';
                        }
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                    echo '</div>';
                } else {
                    echo '<p>No notifications found.</p>';
                }
            ?>
        </div>
    </div>
    <style>
        /* Custom styles for the notifications and form */
        .notification-form .form-table th {
            width: 150px;
        }
        .quiz-notifications {
            margin-top: 20px;
        }
        .accordion-button {
            background-color: #f8f9fa;
            border: none;
            font-weight: bold;
        }
        .accordion-item {
            border: 1px solid #e3e3e3;
            margin-bottom: 10px;
            border-radius: 4px;
            overflow: hidden;
        }
        .accordion-body {
            background-color: #fff;
            padding: 20px;
        }
    </style>
    <script>
        jQuery(document).ready(function($) {
            $('#selected_users').select2(); // Initialize Select2 for multi-select dropdown
        });
    </script>
    <?php
}

// Mark Notifications as Read When a User Views Them
function mark_notifications_as_read($user_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'quiz_notifications';
    
    // Fetch all notifications where the user is in the unread list
    $notifications = $wpdb->get_results("SELECT * FROM $table_name WHERE JSON_CONTAINS(unread_users, '\"$user_id\"')");

    foreach ($notifications as $notification) {
        $unread_users = json_decode($notification->unread_users, true);
        
        // Remove user from unread list
        if (($key = array_search($user_id, $unread_users)) !== false) {
            unset($unread_users[$key]);
        }

        // Update the notification
        $wpdb->update(
            $table_name,
            array('unread_users' => json_encode($unread_users)),
            array('id' => $notification->id),
            array('%s'),
            array('%d')
        );
    }
}