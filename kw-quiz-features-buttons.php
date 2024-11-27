<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Hook to add the meta box for quizzes post type
function quizzes_custom_meta_box() {
    global $post;

    // Only add the meta box if the post is published
    if ($post && $post->post_type === 'quizzes' && $post->post_status === 'publish') {
        add_meta_box(
            'quizzes_action_buttons_meta_box', // Updated ID for uniqueness
            __('Quiz Actions','wp-quiz-plugin'),                    // Title
            'quizzes_meta_box_callback',       // Callback function
            'quizzes',                         // Post type
            'normal',                          // Context
            'high'                             // Priority
        );
    }
}
add_action('add_meta_boxes', 'quizzes_custom_meta_box');

// Callback function for the meta box content
function quizzes_meta_box_callback($post) {

    // Get the post URL for dynamic button actions
    $post_url = get_permalink($post->ID);
    // Retrieve stored options for the buttons
    $open_tab_label = esc_html(get_option('wp_quiz_plugin_open_tab_button_label', __('Open in New Tab', 'wp-quiz-plugin')));
    $open_tab_color = get_option('wp_quiz_plugin_open_tab_button_color', '#007BFF');
    $open_tab_font_size = get_option('wp_quiz_plugin_open_tab_button_font_size', '16');

    $copy_url_label = esc_html(get_option('wp_quiz_plugin_copy_url_button_label', __('Copy URL to Clipboard', 'wp-quiz-plugin')));
    $copy_url_color = get_option('wp_quiz_plugin_copy_url_button_color', '#007BFF');
    $copy_url_font_size = get_option('wp_quiz_plugin_copy_url_button_font_size', '16');

    $email_label = esc_html(get_option('wp_quiz_plugin_share_email_button_label', __('Share via Email', 'wp-quiz-plugin')));
    $email_color = get_option('wp_quiz_plugin_share_email_button_color', '#007BFF');
    $email_font_size = get_option('wp_quiz_plugin_share_email_button_font_size', '16');
    $email_subject = esc_html(get_option('wp_quiz_plugin_share_email_subject', __('New Quiz Assessment Available', 'wp-quiz-plugin')));
    $email_body = esc_html(get_option('wp_quiz_plugin_share_email_body', __('Hello,\n\nPlease attempt this quiz on time. Here is the quiz link:\n\n[URL]\n\nBest regards,', 'wp-quiz-plugin')));

        

    ?>
    <div id="quizzes_action_buttons_container">
        <!-- Button to open post in a new tab -->
        <button class="kw_button kw_button-primary" type="button" 
            onclick="window.open('<?php echo esc_url($post_url); ?>', '_blank')"
            style="background-color: <?php echo esc_attr($open_tab_color); ?>; font-size: <?php echo esc_attr($open_tab_font_size); ?>px;">
            <?php echo __($open_tab_label); ?>
        </button>

        <!-- Hidden input field for URL copying -->
        <input id="copyable-url" type="text" value="<?php echo esc_url($post_url); ?>" readonly style="position:absolute; left:-9999px;">
        
        <!-- Button to copy URL to clipboard -->
        <button class="kw_button kw_button-primary" type="button" id="copy-url-button" 
            style="background-color: <?php echo esc_attr($copy_url_color); ?>; font-size: <?php echo esc_attr($copy_url_font_size); ?>px;">
            <?php echo __($copy_url_label); ?>
        </button>

        <!-- Button to open mailbox with default message -->
        <button class="kw_button kw_button-primary" type="button" 
            onclick="openMailClient('<?php echo esc_url($post_url); ?>')" 
            style="background-color: <?php echo esc_attr($email_color); ?>; font-size: <?php echo esc_attr($email_font_size); ?>px;">
            <?php echo __($email_label); ?>
        </button>

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
                var subject = "<?php echo esc_js($email_subject); ?>";
                var body = "<?php echo esc_js(str_replace('[URL]', $post_url, $email_body)); ?>";
                window.location.href = "mailto:?subject=" + encodeURIComponent(subject) + "&body=" + encodeURIComponent(body);
            };
        });
    </script>
    <?php
}

?>
