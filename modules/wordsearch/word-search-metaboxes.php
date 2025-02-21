<?php
include_once plugin_dir_path(__FILE__) . '/wordsearch-grid-shortcode.php';
// Register the meta box for the 'wordsearch' post type.
function add_wordsearch_meta_box() {
    add_meta_box(
        'wordsearch_meta_box',                   // Unique ID for the meta box.
        'Add Words',                             // Title of the meta box.
        'render_wordsearch_meta_box',            // Callback function to render the meta box.
        'wordsearch',                            // Post type where it should appear.
        'normal',                                // Context.
        'default'                                // Priority.
    );
}
add_action('add_meta_boxes', 'add_wordsearch_meta_box');

// Enqueue the JavaScrossword-image-previewipt file for the meta box.
function enqueue_style_script( $hook ) {
    $screen = get_current_screen();
    if ( $screen && $screen->post_type === 'wordsearch' && ($hook === 'post-new.php' || $hook === 'post.php') ) {
        wp_enqueue_script('custom-admin-js', plugin_dir_url(__FILE__) . '/assets/js/metabox.js', ['jquery'], null, true);
        wp_enqueue_style('custom-admin-css', plugin_dir_url(__FILE__) . '/assets/css/style.css');
    }
}
add_action('admin_enqueue_scripts', 'enqueue_style_script');

function wordsearch_enqueue_assets() {

    wp_enqueue_script( 'jquery' );

    // Enqueue wordfind.js – the puzzle logic.
    wp_enqueue_script(
        'wordfind',
        plugin_dir_url( __FILE__ ) . '/assets/js/wordfind.js',
        array(),
        '0.0.1',
        true
    );

    // Enqueue wordfindgame.js – the interactive game logic (depends on wordfind.js).
    wp_enqueue_script(
        'wordfindgame',
        plugin_dir_url( __FILE__ ) . '/assets/js/wordfindgame.js',
        array('wordfind'),
        '0.0.1',
        true
    );

    wp_enqueue_script(
        'konva-js',
        'https://cdn.jsdelivr.net/npm/konva@8.3.12/konva.min.js',
        array(),
        '8.3.12',
        true
      );

    // Enqueue frontend styles
    wp_enqueue_style(
        'wordsearch-style',
        plugin_dir_url(__FILE__) . '/assets/css/grid-style.css',
        array(),
        '1.0',
        'all'
    );

    // Enqueue frontend JavaScript
    wp_enqueue_script(
        'wordsearch-script',
        plugin_dir_url(__FILE__) . '/assets/js/wordsearch-grid.js',
        array('jquery'),
        '1.0',
        true
    );

        // Fetch words from post meta (only when viewing a WordSearch post)
        global $post;
        $word_entries = (is_singular('wordsearch') && isset($post->ID))
            ? get_post_meta($post->ID, '_wordsearch_word_entries', true)
            : array();

                // Debug: Print Word Entries before passing to JavaScript
    echo "<pre>PHP Debug - Word Entries:";
    print_r($word_entries);
    echo "</pre>";
    
        // Ensure we send a valid JSON array
        $word_entries = !empty($word_entries) && is_array($word_entries) ? $word_entries : array();
    
        // Pass data to JavaScript
        wp_localize_script('wordsearch-script', 'wordsearchData', array(
            'words' => $word_entries
        ));
}
add_action('wp_enqueue_scripts', 'wordsearch_enqueue_assets');

// Save the meta box data.
function save_wordsearch_meta_box_data($post_id) {

    // Check if nonce is set.
    if ( ! isset( $_POST['wordsearch_meta_box_nonce_field'] ) ) {
        return $post_id;
    }
    // Verify nonce.
    if ( ! wp_verify_nonce( $_POST['wordsearch_meta_box_nonce_field'], 'wordsearch_meta_box_nonce' ) ) {
        return $post_id;
    }
    // Check for autosave.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }
    // Check user permissions.
    if ( isset( $_POST['post_type'] ) && 'wordsearch' === $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }
    } else {
        return $post_id;
    }

    // Save our data from the hidden field "wordsearch_word_entries".
    if ( isset( $_POST['wordsearch_word_entries'] ) ) {
        error_log( 'POST DATA:' . print_r( $_POST['wordsearch_word_entries'], true ) );
        // Remove any slashes and decode the JSON.
        $entries = json_decode( stripslashes( $_POST['wordsearch_word_entries'] ), true );
        if ( is_array( $entries ) ) {
                        echo "<pre>PHP Debug - Word Entries:";
            print_r($entries);
            echo "</pre>";
            update_post_meta( $post_id, '_wordsearch_word_entries', $entries );
            error_log( 'Saved Data: ' . print_r( $entries, true ) );
        } else {
            // If JSON decoding fails, delete the meta.
            delete_post_meta( $post_id, '_wordsearch_word_entries' );
        }
    } else {
        delete_post_meta( $post_id, '_wordsearch_word_entries' );
    }
}
add_action( 'save_post', 'save_wordsearch_meta_box_data' );

// Render the meta box content.
function render_wordsearch_meta_box($post) {
    // Add a nonce field for security.
    wp_nonce_field('wordsearch_meta_box_nonce', 'wordsearch_meta_box_nonce_field');

    // Fetch existing words from the '_wordsearch_word_entries' meta key
    $word_entries = get_post_meta($post->ID, '_wordsearch_word_entries', true);

    if (empty($word_entries) || !is_array($word_entries)) {
        $word_entries = array();
    }



    // Retrieve button settings.
    
    
    $default_add_word_label   = __('Add a Word', 'wp-quiz-plugin');
    $add_word_label           = get_option('kw_wordsearch_admin_add_word_button_label', $default_add_word_label);
    $add_word_bg_color        = get_option('kw_wordsearch_admin_add_word_button_color', '#0073aa');
    $add_word_text_color      = get_option('kw_wordsearch_admin_add_word_button_text_color', '#ffffff');

    $default_clear_list_label = __('Clear List', 'wp-quiz-plugin');
    $clear_list_label         = get_option('kw_wordsearch_admin_clear_list_button_label', $default_clear_list_label);
    $clear_list_bg_color      = get_option('kw_wordsearch_admin_clear_list_button_color', '#0073aa');
    $clear_list_text_color    = get_option('kw_wordsearch_admin_clear_list_button_text_color', '#ffffff');
    ?>

<div id="wordsearch-container">
    <div style="flex: 1">
    <div id="wordsearch-words-container">
    <?php 
    if (!empty($word_entries) && is_array($word_entries)) :
        foreach ($word_entries as $index => $entry) :
            $uniqueId = isset($entry['id']) ? esc_attr($entry['id']) : uniqid();
            ?>
            <div class="add-word-container" data-index="<?php echo esc_attr($index); ?>" data-unique-id="<?php echo $uniqueId; ?>">
                <span class="word-number"><?php echo esc_html($index + 1); ?>.</span>
                <div class="kw-wordsearch-words-container">
                    <input type="text" class="word-input word-input-<?php echo $uniqueId; ?>" name="wordsearch_words[<?php echo esc_attr($index); ?>][word]" 
                        placeholder="<?php esc_attr_e('Word', 'wp-quiz-plugin'); ?>" 
                        value="<?php echo esc_attr(isset($entry['wordText']) ? $entry['wordText'] : ''); ?>" />
                    <input type="hidden" name="wordsearch_words[<?php echo esc_attr($index); ?>][id]" value="<?php echo $uniqueId; ?>" />
                    <div class="actions">
                        <div class="wordsearch-image-preview wordsearch-image-preview-<?php echo $uniqueId; ?>">
                            <?php if (!empty($entry['imageUrl'])) : ?>
                                <img src="<?php echo esc_url($entry['imageUrl']); ?>" style="max-width: 70px; max-height: 70px; border-radius: 5%; padding-left: 10px;" alt="<?php esc_attr_e('Word image preview', 'wp-quiz-plugin'); ?>" />
                                <input type="hidden" class="wordsearch-image-url" name="wordsearch_words[<?php echo esc_attr($index); ?>][image]" 
                                    value="<?php echo esc_url($entry['imageUrl']); ?>" />
                            <?php endif; ?>
                        </div>
                        <span class="upload-word-image-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 576 512">
                                <path fill="currentColor" d="M160 80l352 0c8.8 0 16 7.2 16 16l0 224c0 8.8-7.2 16-16 16l-21.2 0L388.1 178.9c-4.4-6.8-12-10.9-20.1-10.9s-15.7 4.1-20.1 10.9l-52.2 79.8-12.4-16.9c-4.5-6.2-11.7-9.8-19.4-9.8s-14.8 3.6-19.4 9.8L175.6 336 160 336c-8.8 0-16-7.2-16-16l0-224c0-8.8 7.2-16 16-16zM96 96l0 224c0 35.3 28.7 64 64 64l352 0c35.3 0 64-28.7 64-64l0-224c0-35.3-28.7-64-64-64L160 32c-35.3 0-64 28.7-64 64z"/>
                            </svg>
                        </span>
                        <button type="button" class="remove-word">
                            <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="trash" width="20" height="20" viewBox="0 0 448 512">
                                <path fill="currentColor" d="M135.2 176c-8.5 0-15.2 6.9-15.2 15.2v224c0 8.5 6.9 15.2 15.2 15.2h18.5c8.5 0 15.2-6.9 15.2-15.2v-224c0-8.5-6.9-15.2-15.2-15.2h-18.5zM319.7 176c-8.5 0-15.2 6.9-15.2 15.2v224c0 8.5 6.9 15.2 15.2 15.2h18.5c8.5 0 15.2-6.9 15.2-15.2v-224c0-8.5-6.9-15.2-15.2-15.2h-18.5zM432 32H312l-9.4-18.8C295.5 5.4 286.6 0 276.6 0H171.5c-10 0-18.8 5.4-26 13.2L135.2 32H16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h416c8.8 0 16-7.2 16-16V48c0-8.8-7.2-16-16-16zM53.2 472.1c1.8 26.1 23.4 47.9 49.7 47.9H345c26.3 0 47.9-21.8 49.7-47.9l20.1-336.1H32.9l20.3 336.1z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach;
    endif;
    ?>
</div>

        <div class="wordsearch-add-button-container">
            <button type="button" id="add-wordsearch-button" 
                style="border:none; background-color: <?php echo esc_attr($add_word_bg_color); ?>; color: <?php echo esc_attr($add_word_text_color); ?>;">
                <?php echo esc_html($add_word_label); ?>
            </button>
            <!-- Hidden field stores the JSON-encoded word entries -->
            <input type="hidden" id="wordEntriesData" name="wordsearch_word_entries" 
    value='<?php echo esc_attr( json_encode( $word_entries ? $word_entries : [] ) ); ?>'>
            <button type="button" id="clear-wordsearch-list-button" 
                style="border:none; background-color: <?php echo esc_attr($clear_list_bg_color); ?>; color: <?php echo esc_attr($clear_list_text_color); ?>;">
                <?php echo esc_html($clear_list_label); ?>
            </button>
        </div>
    </div>
</div>


    
    <script type="text/template" id="wordsearch-word-template">
  <div class="add-word-container" data-index="{{index}}" data-unique-id="{{uniqueId}}">
    <span class="word-number">{{number}}.</span>
    <div class="kw-wordsearch-words-container" style="">
      <input type="text" class="word-input word-input-{{uniqueId}}" name="wordsearch_words[{{index}}][word]" placeholder="<?php esc_attr_e('Word', 'wp-quiz-plugin'); ?>" value="" />
      <input type="hidden" name="wordsearch_words[{{index}}][{{uniqueId}}]" value="{{uniqueId}}" />
      <div class="actions">
      <div class="wordsearch-image-preview wordsearch-image-preview-{{uniqueId}}"></div>
      <span class="upload-word-image-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 576 512">
          <path fill="currentColor" d="M160 80l352 0c8.8 0 16 7.2 16 16l0 224c0 8.8-7.2 16-16 16l-21.2 0L388.1 178.9c-4.4-6.8-12-10.9-20.1-10.9s-15.7 4.1-20.1 10.9l-52.2 79.8-12.4-16.9c-4.5-6.2-11.7-9.8-19.4-9.8s-14.8 3.6-19.4 9.8L175.6 336 160 336c-8.8 0-16-7.2-16-16l0-224c0-8.8 7.2-16 16-16zM96 96l0 224c0 35.3 28.7 64 64 64l352 0c35.3 0 64-28.7 64-64l0-224c0-35.3-28.7-64-64-64L160 32c-35.3 0-64 28.7-64 64z"/>
        </svg>
      </span>
      <button type="button" class="remove-word">
        <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="trash" width="20" height="20" viewBox="0 0 448 512">
          <path fill="currentColor" d="M135.2 176c-8.5 0-15.2 6.9-15.2 15.2v224c0 8.5 6.9 15.2 15.2 15.2h18.5c8.5 0 15.2-6.9 15.2-15.2v-224c0-8.5-6.9-15.2-15.2-15.2h-18.5zM319.7 176c-8.5 0-15.2 6.9-15.2 15.2v224c0 8.5 6.9 15.2 15.2 15.2h18.5c8.5 0 15.2-6.9 15.2-15.2v-224c0-8.5-6.9-15.2-15.2-15.2h-18.5zM432 32H312l-9.4-18.8C295.5 5.4 286.6 0 276.6 0H171.5c-10 0-18.8 5.4-26 13.2L135.2 32H16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h416c8.8 0 16-7.2 16-16V48c0-8.8-7.2-16-16-16zM53.2 472.1c1.8 26.1 23.4 47.9 49.7 47.9H345c26.3 0 47.9-21.8 49.7-47.9l20.1-336.1H32.9l20.3 336.1z"></path>
        </svg>
      </button>
      </div>
    </div>
  </div>
</script>
    <?php
}
