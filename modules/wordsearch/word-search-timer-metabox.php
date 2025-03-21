<?php
/**
 * Word Search Timer Metabox
 * 
 * Adds a timer metabox to word search puzzles with a professional interface
 * for setting time limits in hours, minutes, and seconds.
 */

// Don't allow direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register and enqueue admin scripts and styles
 */
function wordsearch_timer_admin_enqueue_scripts($hook) {
    global $post_type;
    
    // Only load on post edit screens for our post type
    if (!in_array($hook, array('post.php', 'post-new.php')) || 'wordsearch' !== $post_type) {
        return;
    }
    
    // Get plugin directory URL - adjust this to your actual plugin path
    $plugin_url = plugin_dir_url(__FILE__);
    
    // Enqueue the CSS
    wp_enqueue_style(
        'wordsearch-timer-admin',
        $plugin_url . 'assets/css/wordsearch-timer-admin.css',
        array(),
        '1.0.0',
        'all'
    );
    
    // Enqueue the JavaScript
    wp_enqueue_script(
        'wordsearch-timer-admin',
        $plugin_url . 'assets/js/wordsearch-timer.js',
        array('jquery'),
        '1.0.0',
        true
    );
}
add_action('admin_enqueue_scripts', 'wordsearch_timer_admin_enqueue_scripts');

/**
 * Register the Word Search Timer metabox
 */
function wordsearch_timer_register_meta_boxes() {
    add_meta_box(
        'wordsearch-timer',
        esc_html__('Word Search Timer', 'wordsearch'),
        'wordsearch_timer_display_callback',
        'wordsearch', // Change this to your actual post type
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'wordsearch_timer_register_meta_boxes');

/**
 * Meta box display callback
 * 
 * @param WP_Post $post Current post object
 */
function wordsearch_timer_display_callback($post) {
    // Add nonce for security and authentication
    wp_nonce_field('wordsearch_timer_save_meta_box_data', 'wordsearch_timer_meta_box_nonce');

    // Retrieve existing timer value
    $timer_value = get_post_meta($post->ID, '_wordsearch_timer_value', true);
    $timer_unit = get_post_meta($post->ID, '_wordsearch_timer_unit', true);
    
    // Set defaults if no values exist
    if (empty($timer_value)) {
        $timer_value = 0;
    }
    
    if (empty($timer_unit)) {
        $timer_unit = 'seconds';
    }
    
    // Calculate hours, minutes, seconds for display
    $hours = 0;
    $minutes = 0;
    $seconds = 0;
    
    switch ($timer_unit) {
        case 'hours':
            $hours = $timer_value;
            break;
        case 'minutes':
            $minutes = $timer_value;
            break;
        case 'seconds':
            $seconds = $timer_value;
            break;
        case 'total_seconds':
            // Convert from total seconds to hours, minutes, seconds
            $hours = floor($timer_value / 3600);
            $minutes = floor(($timer_value % 3600) / 60);
            $seconds = $timer_value % 60;
            break;
    }
    
    // Check if timer is enabled
    $timer_enabled = get_post_meta($post->ID, '_wordsearch_timer_enabled', true);
    $timer_enabled = empty($timer_enabled) ? 0 : 1;
    
    ?>
    <div class="wordsearch-timer-metabox">
        <p>
            <label for="wordsearch-timer-enabled">
                <input type="checkbox" id="wordsearch-timer-enabled" name="wordsearch_timer_enabled" value="1" <?php checked($timer_enabled, 1); ?>>
                <?php _e('Enable Timer', 'wordsearch'); ?>
            </label>
        </p>
        
        <div class="timer-fields" <?php echo $timer_enabled ? '' : 'style="display: none;"'; ?>>
            <p class="timer-heading"><?php _e('Set Time Limit:', 'wordsearch'); ?></p>
            
            <div class="timer-inputs">
                <div class="time-field">
                    <label for="wordsearch-timer-hours"><?php _e('Hours', 'wordsearch'); ?></label>
                    <input type="number" id="wordsearch-timer-hours" name="wordsearch_timer_hours" min="0" max="99" value="<?php echo esc_attr($hours); ?>" class="small-text">
                </div>
                
                <div class="time-field">
                    <label for="wordsearch-timer-minutes"><?php _e('Minutes', 'wordsearch'); ?></label>
                    <input type="number" id="wordsearch-timer-minutes" name="wordsearch_timer_minutes" min="0" max="59" value="<?php echo esc_attr($minutes); ?>" class="small-text">
                </div>
                
                <div class="time-field">
                    <label for="wordsearch-timer-seconds"><?php _e('Seconds', 'wordsearch'); ?></label>
                    <input type="number" id="wordsearch-timer-seconds" name="wordsearch_timer_seconds" min="0" max="59" value="<?php echo esc_attr($seconds); ?>" class="small-text">
                </div>
            </div>
            
            <p class="timer-message">
                <span class="timer-preview">
                    <?php _e('Preview:', 'wordsearch'); ?> <span id="timer-preview-value">00:00:00</span>
                </span>
            </p>
            
            <p class="timer-description">
                <?php _e('The timer will start when the player begins the word search game.', 'wordsearch'); ?>
            </p>
        </div>
    </div>
    <?php
}

/**
 * Save meta box content
 * 
 * @param int $post_id Post ID
 */
function wordsearch_timer_save_meta_box_data($post_id) {
    // Verify nonce
    if (!isset($_POST['wordsearch_timer_meta_box_nonce']) || 
        !wp_verify_nonce($_POST['wordsearch_timer_meta_box_nonce'], 'wordsearch_timer_save_meta_box_data')) {
        return;
    }

    // If this is an autosave, don't do anything
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check the user's permissions
    if (isset($_POST['post_type']) && 'wordsearch' === $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }

    // Save timer enabled status
    $timer_enabled = isset($_POST['wordsearch_timer_enabled']) ? 1 : 0;
    update_post_meta($post_id, '_wordsearch_timer_enabled', $timer_enabled);

    error_log("Timer Enabled" . print_r($timer_enabled, true));
    
    if ($timer_enabled) {
        // Get hours, minutes, seconds
        $hours = isset($_POST['wordsearch_timer_hours']) ? absint($_POST['wordsearch_timer_hours']) : 0;
        $minutes = isset($_POST['wordsearch_timer_minutes']) ? absint($_POST['wordsearch_timer_minutes']) : 0;
        $seconds = isset($_POST['wordsearch_timer_seconds']) ? absint($_POST['wordsearch_timer_seconds']) : 0;
        
        // Validate minutes and seconds (0-59)
        $minutes = min(59, $minutes);
        $seconds = min(59, $seconds);
        
        // Convert to total seconds for storage
        $total_seconds = ($hours * 3600) + ($minutes * 60) + $seconds;
        
        // Save total seconds
        update_post_meta($post_id, '_wordsearch_timer_value', $total_seconds);
        update_post_meta($post_id, '_wordsearch_timer_unit', 'total_seconds');

    }else {
        // Delete timer value and unit if timer is disabled (i.e. timer_enabled is zero)
        delete_post_meta($post_id, '_wordsearch_timer_value');
        delete_post_meta($post_id, '_wordsearch_timer_unit');
    }
}
add_action('save_post', 'wordsearch_timer_save_meta_box_data');

/**
 * Get timer value for a word search puzzle
 * 
 * @param int $post_id Post ID
 * @param string $format Format to return ('seconds', 'array', 'formatted')
 * @return mixed Timer value in requested format
 */
function wordsearch_get_timer($post_id = null, $format = 'seconds') {
    if (null === $post_id) {
        $post_id = get_the_ID();
    }
    
    $timer_enabled = get_post_meta($post_id, '_wordsearch_timer_enabled', true);
    
    if (empty($timer_enabled)) {
        return false; // Timer not enabled
    }
    
    $timer_value = get_post_meta($post_id, '_wordsearch_timer_value', true);
    
    if (empty($timer_value)) {
        $timer_value = 0;
    }
    
    switch ($format) {
        case 'array':
            // Return as hours, minutes, seconds array
            $hours = floor($timer_value / 3600);
            $minutes = floor(($timer_value % 3600) / 60);
            $seconds = $timer_value % 60;
            
            return array(
                'hours' => $hours,
                'minutes' => $minutes,
                'seconds' => $seconds,
                'total_seconds' => $timer_value
            );
            
        case 'formatted':
            // Return as formatted string (HH:MM:SS)
            $hours = floor($timer_value / 3600);
            $minutes = floor(($timer_value % 3600) / 60);
            $seconds = $timer_value % 60;
            
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            
        case 'seconds':
        default:
            // Return total seconds
            return $timer_value;
    }
}