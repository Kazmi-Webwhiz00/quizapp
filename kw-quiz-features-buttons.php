<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Hook to add the meta box for quizzes post type
function quizzes_custom_meta_box() {
    add_meta_box(
        'quizzes_action_buttons_meta_box', // Updated ID for uniqueness
        'Quiz Actions',                    // Title
        'quizzes_meta_box_callback',       // Callback function
        'quizzes',                         // Post type
        'normal',                          // Context
        'high'                             // Priority
    );
}
add_action('add_meta_boxes', 'quizzes_custom_meta_box');

// Callback function for the meta box content
function quizzes_meta_box_callback($post) {
    // Display buttons only if the post already exists
    if ($post->ID) {
        $post_url = get_permalink($post->ID);
        ?>
        <div id="quizzes_action_buttons_container">
            <!-- Button to open post in a new tab -->
            <button class ="kw_button kw_button-primary" type="button" onclick="window.open('<?php echo esc_url($post_url); ?>', '_blank')">Open in New Tab</button>

            <!-- Hidden input field for URL copying -->
            <input id="copyable-url" type="text" value="<?php echo esc_url($post_url); ?>" readonly style="position:absolute; left:-9999px;">
            
            <!-- Button to copy URL to clipboard -->
            <button class ="kw_button kw_button-primary" type="button" id="copy-url-button">Copy URL to Clipboard</button>

            <!-- Button to open mailbox with default message -->
            <button class ="kw_button kw_button-primary" type="button" onclick="openMailClient('<?php echo esc_url($post_url); ?>')">Share via Email</button>

            <!-- Div to show copy confirmation message -->
            <div id="quizzes_copy_message" style="display:none; color:green; margin-top:10px;">Copied to clipboard!</div>
        </div>

        <!-- jQuery script to handle clipboard copy and email functions -->
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Copy to clipboard function
                $('#copy-url-button').on('click', function() {
                    var $input = $('#copyable-url');
                    $input.focus().select();
                    if (document.execCommand('copy')) {
                        $('#quizzes_copy_message').show().delay(2000).fadeOut();
                    }
                });

                // Function to open mail client with a default message
                window.openMailClient = function(url) {
                    var subject = "Check out this quiz!";
                    var body = "Hi,\n\nCheck out this quiz: " + url + "\n\nBest regards,";
                    window.location.href = "mailto:?subject=" + encodeURIComponent(subject) + "&body=" + encodeURIComponent(body);
                };
            });
        </script>
        <?php
    } else {
        echo '<p>Save the post to see additional options.</p>';
    }
}
?>
