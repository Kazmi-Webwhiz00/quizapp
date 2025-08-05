<?php
include_once plugin_dir_path(__FILE__) . '/wordsearch-grid-shortcode.php';
// include_once plugin_dir_path(__FILE__) . 'assets/templates/wordsearch-limit-modal.php'; 




// Register the meta box for the 'wordsearch' post type.
function add_wordsearch_meta_box() {
    // Retrieve the meta value for the container label, or fall back to the default
$meta_label = get_option('kw_wordsearch_admin_add_words_container_label',  __('Add Words', 'wp-quiz-plugin'));
    add_meta_box(
        'wordsearch_meta_box',                   // Unique ID for the meta box.
        esc_html__( $meta_label, 'wp-quiz-plugin' ), // Use the retrieved label
        'render_wordsearch_meta_box',            // Callback function to render the meta box.
        'wordsearch',                            // Post type where it should appear.
        'normal',                                // Context.
        'default'                                // Priority.
    );
}
add_action('add_meta_boxes', 'add_wordsearch_meta_box');

// Enqueue the JavaSwordsearch-image-previewipt file for the meta box.
function enqueue_style_script( $hook ) {
    // Entry Limit Popup
    $default_entry_limit_popup_title     = __( "Entry Limit Reached", 'wp-quiz-plugin' );
    $default_entry_limit_popup_body_text = __( "You cannot add more than 15 entries to the word search.", 'wp-quiz-plugin' );
    $clear_list_body_text               = __( "Are you sure you want to clear the list?", 'wp-quiz-plugin' );
    $add_question_with_ai_text =  __("Add Question with AI", 'wp-quiz-plugin');
    $forget_warning_msg                 = __("Don't forget to save! Your changes may be lost.", 'wp-quiz-plugin');
    $entry_limit_popup_title             = get_option( 'kw_wordsearch_entry_limit_popup_title', $default_entry_limit_popup_title );
    $entry_limit_popup_body_text         = get_option( 'kw_wordsearch_entry_limit_popup_body_text', $default_entry_limit_popup_body_text );
    
    $screen = get_current_screen();
    if ( $screen && $screen->post_type === 'wordsearch' && ( $hook === 'post-new.php' || $hook === 'post.php' ) ) {
        wp_enqueue_script( 'jquery' );
        // Enqueue jQuery UI Dialog script.
        wp_enqueue_script( 'jquery-ui-dialog' );
        // Optionally, enqueue a jQuery UI theme CSS.
        wp_enqueue_style( 'jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css' );
        wp_enqueue_script( 'custom-admin-js', plugin_dir_url( __FILE__ ) . '/assets/js/metabox.js', array( 'jquery' ), null, true );
        wp_enqueue_style( 'custom-admin-css', plugin_dir_url( __FILE__ ) . '/assets/css/style.css' );

        // Localize the data: Pass the word entries and a nonce to your JS file.
        wp_localize_script( 'custom-admin-js', 'entryLimit', array(
            'entryLimitTitle'    => $entry_limit_popup_title,
            'entryLimitBodyText' => $entry_limit_popup_body_text,
            'clearListBodyText' => $clear_list_body_text,
            'selectImageTitle' => esc_js( esc_html__( 'Select Image', 'wp-quiz-plugin' ) ),
            'useImageText'     => esc_js( esc_html__( 'Use this image', 'wp-quiz-plugin' ) ),
            'forgetWarningMsg'   => $forget_warning_msg,
            'addQuestionWithAI' => $add_question_with_ai_text,
        ) );
    }
}
add_action( 'admin_enqueue_scripts', 'enqueue_style_script' );


function wordsearch_enqueue_assets() {
    global $post;
    $post_type = get_post_type($post);

        // Fetch the filled cell background color with a default value
        $grid_even_cell_bg_color = get_option('kw_grid_even_cell_bg_color', '#ecd8b3');
        $grid_odd_cell_bg_color = get_option('kw_grid_odd_cell_bg_color', '#f5e9d1');
        $higlightedCellTextColor = get_option('kw_highlight_cell_text_color', '#ffffff');
        $lineColor = get_option('kw_grid_line_color', '#b8860b99');
        $gridTextColor = get_option('kw_grid_text_font_color', '#5c4012');
        // error_log("color" . print_r($gridTextColor , true));
        $gridTextFontFamily = get_option('kw_grid_text_font_family', 'Roboto');

        $default_success_popup_label = __('Congratulations', 'wp-quiz-plugin');
        $default_success_body_text = __('You have successfully completed the wordsearch!', 'wp-quiz-plugin');
        $default_popup_challenge_text = __('Ready for another challenge?', 'wp-quiz-plugin');
        $default_popup_button_text = __('Play Again', 'wp-quiz-plugin');
        $success_popup_title = get_option('kw_wordsearch_success_popup_title', $default_success_popup_label);
        $success_popup_body_text = get_option('kw_wordsearch_success_popup_body_text', $default_success_body_text);
        $success_popup_challenge_text = get_option('kw_wordsearch_success_popup_challenge_text', $default_popup_challenge_text);
        $success_popup_button_text = get_option('kw_wordsearch_success_popup_button_text', $default_popup_button_text);
        /*  Render Time's up Popup Section */
        $default_timeup_popup_label = __("Time's Up!", 'wp-quiz-plugin');
        $default_timeup_body_text = __('Your time has expired for this word search puzzle.', 'wp-quiz-plugin');
        $default_timeup_challenge_text = __('Would you like to start a new game?', 'wp-quiz-plugin');
        $default_timeup_button_text = __('Play Again', 'wp-quiz-plugin');
        $timeup_popup_title = get_option('kw_wordsearch_timeup_popup_title', $default_timeup_popup_label);
        $timeup_popup_body_text = get_option('kw_wordsearch_timeup_popup_body_text', $default_timeup_body_text);
        $timeup_popup_challenge_text = get_option('kw_wordsearch_timeup_popup_challenge_text', $default_timeup_challenge_text);
        $timeup_popup_button_text = get_option('kw_wordsearch_timeup_popup_button_text', $default_timeup_button_text);
        $toggleGridLettersSound = get_option('kw_grid_text_sound_setting', 0);
        // Download Button Label
        $default_download_pdf_label = __('Download Pdf', 'wp-quiz-plugin');
        $default_downloading_button_label = __('Downloading...', 'wp-quiz-plugin');
        $downloadPdfLabel = get_option('kw_download_pdf_label', $default_download_pdf_label);
        // Find Words Label
        $default_find_words_label = __('Find These Words:', 'wp-quiz-plugin');
        $gridFindWordsLabel = get_option('kw_find_words_label', $default_find_words_label);
        $showImagesLabel = __('Show Images', 'wp-quiz-plugin');
        $hideImagesLabel = __('Hide Images', 'wp-quiz-plugin');
        $quizQuestionsLabel = __('Quiz Questions', 'wp-quiz-plugin');
        $openText = __('Open Text', 'wp-quiz-plugin');
        $trueFalseText = __('True/False', 'wp-quiz-plugin');
        $multipleChoiceText = __('Multiple Choice', 'wp-quiz-plugin');
        $quizAiModalTitle = __('Enter your prompt for ChatGPT:', 'wp-quiz-plugin');
        $quizAiModalBody = __('Enter your prompt below:', 'wp-quiz-plugin');
        $quizAiModalTagsTitle = __('Selected Related Tags below:', 'wp-quiz-plugin');
        $quizAiModalPromptPlaceholder = __('Type your prompt here...', 'wp-quiz-plugin');
        $uploadImagesText = __('Upload Image', 'wp-quiz-plugin');
        $uploadImagesText = __('Upload PDF', 'wp-quiz-plugin');
        $uploadImagesLabel = __('Upload Image(s):', 'wp-quiz-plugin');
        $uploadPdfLabel = __('Upload PDF(s):', 'wp-quiz-plugin');
        $uploadPdfButtonLabel = __('Choose PDFs', 'wp-quiz-plugin');
        $chooseImagesLabel = __('Choose Images:', 'wp-quiz-plugin');
        $noImageLabel = __('No images chosen', 'wp-quiz-plugin');
        $noPdfLabel = __('No PDFs chosen', 'wp-quiz-plugin');
        $noFilesSelectedLabel = __('No files selected', 'wp-quiz-plugin');
        $generateByTextLabel = __('Provide Text to Generate Questions:', 'wp-quiz-plugin');
        $onlyOnePdfText = __('Please remove your existing PDF before uploading a new one.', 'wp-quiz-plugin');
        $onlyFourImagesText = __("You can only upload a maximum of 4 images.", 'wp-quiz-plugin');

        // Word List Text Settings
        $default_word_text_font_size = 14.4;
        $default_word_text_font_color = '#4a5568';
        $wordListTextFontSize = get_option('kw_wordsearch_word_font_size', $default_word_text_font_size);
        $wordListTextFontColor = get_option('kw_wordsearch_word_font_color', $default_word_text_font_color);
        

    // Enqueue jQuery if not already loaded.
    wp_enqueue_script( 'jquery' );

    // Enqueue frontend styles.
    wp_enqueue_style(
        'wordsearch-style',
        plugin_dir_url(__FILE__) . '/assets/css/grid-style.css',
        array(),
        '1.0',
        "all"
    );

    wp_enqueue_script('phaser', plugin_dir_url(__FILE__) . './assets/js/phaser.js', array(), null, true);

        // Enqueue the frontend JS.
        wp_enqueue_script(
            'wordsearch',
            plugin_dir_url(__FILE__) . '/assets/js/wordsearch.js',
            array('jquery'),
            '1.0',
            true
        );

        wp_localize_script( 'wordsearch', 'pluginUrl', array(
            'url' => plugin_dir_url( __FILE__ ),
        ));

    // Enqueue the frontend JS.
    if( $post && $post_type === 'wordsearch' ) {
    wp_enqueue_script(
        'wordsearch-grid',
        plugin_dir_url(__FILE__) . '/assets/js/index.js',
        array('jquery','phaser','wordsearch'),
        '1.0' . time(),
        true
    );
}
    wp_script_add_data( 'wordsearch-grid', 'type', 'module' );

        // Pass plugin URL to JavaScript
        wp_localize_script('wordsearch-grid', 'pluginURL', array(
            'url' => plugin_dir_url(__FILE__) // Ensure correct plugin path
        ));
    

    // Only load post meta data if we are on a single WordSearch post.
    if ( is_singular('wordsearch') ) {
        global $post;
        $word_entries = get_post_meta( $post->ID, 'word_search_entries', true );
        if ( ! is_array( $word_entries ) ) {
            $word_entries = []; // Ensure it's always an array.
        }
        $timer_value = get_post_meta($post->ID, '_wordsearch_timer_value', true);
        $title = get_the_title( $post->ID );

        // Localize the data: Pass the word entries and a nonce to your JS file.
        wp_localize_script( 'wordsearch-grid', 'wordSearchData', array(
            'url' => plugin_dir_url( __FILE__ ),
            'entries' => json_encode( $word_entries ),
            'downloadElement'   => 'downloadButton',
            // 'containerWidth' => "55%",
            'maximunGridSize' => 10,
            'nonce'   => wp_create_nonce( 'wordsearch_nonce' ),
            'timerValue' => $timer_value,
            'showImagesLabel' => esc_html__($showImagesLabel),
            'hideImagesLabel' => esc_html__($hideImagesLabel),
            'downloadPdfLabel' => esc_html__($downloadPdfLabel),
            'downloadingButtonLabel' => esc_html__($default_downloading_button_label),
            'gridStyles'       => array( 
                'fontColor'              => esc_attr( $gridTextColor ),
                'fontFamily'             => esc_attr( $gridTextFontFamily ),
                'evenCellBgColor'        => esc_attr( $grid_even_cell_bg_color ),
                'oddCellBgColor'         => esc_attr( $grid_odd_cell_bg_color  ),
                'higlightedCellTextColor'=> esc_attr( $higlightedCellTextColor ),
                'lineColor'              => esc_attr( $lineColor ),
                'successPopupTitle'    =>  esc_attr( $success_popup_title),
                'successPopupBodyText' =>   esc_attr( $success_popup_body_text ),
                'successPopupChallengeText'  => esc_attr($success_popup_challenge_text),
                'successPopupButtonText'    => esc_attr($success_popup_button_text),
                'toggleGridLettersSound'   => esc_attr($toggleGridLettersSound),

                'timeupPopupTitle'         => esc_attr($timeup_popup_title),
                'timeupPopupBodyText'      => esc_attr( $timeup_popup_body_text),
                'timeupPopupChallengeText' => esc_attr($timeup_popup_challenge_text),
                'timeupPopupButtonText'    => esc_attr($timeup_popup_button_text),
                // Word List Text Settings
                'wordListTextFontSize'     => esc_attr($wordListTextFontSize),
                'wordListTextFontColor'    => esc_attr($wordListTextFontColor),
            ),
            'pdfText'   => array(
                'postTitle' => $title,
                'findWordsLabel' => $gridFindWordsLabel,
            ),

        ));


    } else {
        // If not on a single WordSearch post, you can pass default data.
        wp_localize_script( 'wordsearch-grid', 'wordSearchData', array(
            'entries' => '[]',
            'nonce'   => wp_create_nonce( 'wordsearch_nonce' )
        ) );

    }
}
add_action( 'wp_enqueue_scripts', 'wordsearch_enqueue_assets' );

// 1) Add the column header
add_filter( 'manage_posts_columns', 'add_author_name_column' );
function add_author_name_column( $columns ) {
    // Insert after the title column:
    $new = [];
    foreach ( $columns as $key => $label ) {
        $new[ $key ] = $label;
        if ( 'title' === $key ) {
            $new['post_author_name'] = __( 'Author', 'wp-quiz-plugin' );
        }
    }
    return $new;
}

// 2) Render the column’s contents
add_action( 'manage_posts_custom_column', 'render_author_name_column', 10, 2 );
function render_author_name_column( $column, $post_id ) {
    if ( 'post_author_name' === $column ) {
        $author_id   = get_post_field( 'post_author', $post_id );
        $author_name = get_the_author_meta( 'display_name', $author_id );
        echo esc_html( $author_name );
    }
}


// Save the meta box data.
function save_wordsearch_meta_box_data( $post_id ) {
    // Bail out for AJAX requests.
    if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
        return $post_id;
    }
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
    
    if ( isset( $_POST['wordsearch_words'] ) && is_array( $_POST['wordsearch_words'] ) ) {
        
        // Retrieve existing entries (for reference only).
        $existing_entries = get_post_meta( $post_id, 'word_search_entries', true );
        if ( ! is_array( $existing_entries ) ) {
            $existing_entries = array();
        }
        
        // Build a lookup of existing entries keyed by normalized unique ID.
        $existing_lookup = array();
        foreach ( $existing_entries as $ex_entry ) {
            if ( isset( $ex_entry['id'] ) ) {
                $normalized = strtolower( trim( $ex_entry['id'] ) );
                $existing_lookup[ $normalized ] = $ex_entry;
            }
        }
        
        // Build updated entries solely from POST data.
        $updated_entries = array();
        foreach ( $_POST['wordsearch_words'] as $entry ) {
            // Only process if the word is non-empty.
            if ( ! empty( trim( $entry['word'] ) ) ) {
                $word = sanitize_text_field( $entry['word'] );
                $image_url = esc_url_raw( isset( $entry['image'] ) ? $entry['image'] : '' );
                // Use provided uniqueId or generate one using md5 of word and image.
                if ( ! empty( $entry['uniqueId'] ) ) {
                    $unique_id = sanitize_text_field( $entry['uniqueId'] );
                } else {
                    $unique_id = md5( $word . $image_url );
                }
                $normalized_unique_id = strtolower( trim( $unique_id ) );
                
                // Check if this entry already exists in the DB.
                if ( isset( $existing_lookup[ $normalized_unique_id ] ) ) {
                    $existing_entry = $existing_lookup[ $normalized_unique_id ];
                    // Update the image URL if a new one is provided.
                    if ( ! empty( $image_url ) && ( empty( $existing_entry['imageUrl'] ) || $existing_entry['imageUrl'] !== $image_url ) ) {
                        $existing_entry['imageUrl'] = $image_url;
                    }
                    // Also update word text in case it changed.
                    $existing_entry['wordText'] = $word;
                    $updated_entries[] = $existing_entry;
                } else {
                    // New entry.
                    error_log("Adding new entry with ID: " . $normalized_unique_id);
                    $updated_entries[] = array(
                        'id'       => $normalized_unique_id,
                        'wordText' => $word,
                        'imageUrl' => $image_url,
                        'hidden'   => false,
                    );
                }
            }
        }
        
        error_log("Final updated entries: " . print_r($updated_entries, true));
        update_post_meta( $post_id, 'word_search_entries', $updated_entries );
    } else {
        // If POST data is not set or empty, delete the meta.
        delete_post_meta( $post_id, 'word_search_entries' );
    }
}
add_action( 'save_post', 'save_wordsearch_meta_box_data' );

/**
 * AJAX handler for saving Wordsearch data.
 */
function save_wordsearch_ajax_handler() {
    // 1. Security: Verify the AJAX nonce.
    check_ajax_referer('wordsearch_ajax_nonce', 'security');
    if (isset($_POST['word_search_data']) && is_array($_POST['word_search_data'])) {

    }
    // 2. Validate the post ID and user capabilities.
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    if (!$post_id) {
        wp_send_json_error(array('message' => 'Invalid post ID.'));
    }
    if (!current_user_can('edit_post', $post_id)) {
        wp_send_json_error(array('message' => 'You do not have permission to edit this post.'));
    }

    // 3. Process and save the word/clue data (here we only have 'wordText' + 'imageUrl')
    if (isset($_POST['word_search_data']) && is_array($_POST['word_search_data'])) {
        $entries = array();
        foreach ($_POST['word_search_data'] as $entry) {
            $word_text = isset($entry['wordText']) ? trim($entry['wordText']) : '';
            if (!empty($word_text)) {
                $entries[] = array(
                    'id' => sanitize_text_field($entry['id'] ?? uniqid('ws_', true)),
                    'wordText' => sanitize_text_field($word_text),
                    'imageUrl' => esc_url($entry['imageUrl'] ?? ''),
                );
            }
        }
        if (!empty($entries)) {
            update_post_meta($post_id, 'word_search_entries', $entries);

        } else {
            delete_post_meta($post_id, 'word_search_entries');
        }
    } else {
        // If no data was passed, remove the meta.
        delete_post_meta($post_id, 'word_search_entries');
    }

    // 4. (Optional) If you have additional grid data or other structures, handle them here
    // E.g. if ( isset( $_POST['wordsearch_grid_data'] ) ) { ...similar pattern... }

    // Log that the data was saved successfully.
    error_log( "Wordsearch data saved successfully via AJAX for post {$post_id}" );
    
    // --- New Code to Verify Saved Data ---
    // Retrieve the saved meta data and log it.
    
    $storedData = get_post_meta( $post_id, 'word_search_entries', true );
    error_log("Data" . print_r($storedData , true));

        // Send response with the saved entries
        wp_send_json_success( array( 
            'message' => 'Word Search Entries saved successfully.',
            'data' => $storedData 
        ) );


}
add_action('wp_ajax_save_wordsearch_ajax', 'save_wordsearch_ajax_handler');


// Register REST route when the REST API initializes.
add_action('rest_api_init', 'myplugin_register_wordsearch_routes');

function myplugin_register_wordsearch_routes() {
    register_rest_route(
        'myplugin/v1',
        '/wordsearch/(?P<post_id>\d+)',
        array(
            'methods'  => 'POST',
            'callback' => 'myplugin_save_wordsearch_rest_callback',
            'permission_callback' => 'myplugin_wordsearch_permissions_check',
        )
    );
}

/**
 * Check if the current user can edit the specified post.
 */
function myplugin_wordsearch_permissions_check($request) {
    $post_id = (int) $request->get_param('post_id');
    return current_user_can('edit_post', $post_id);
}

/**
 * Callback function to save the word search entries.
 */
function myplugin_save_wordsearch_rest_callback($request) {
    // 1. Get the post ID from the route.
    $post_id = (int) $request->get_param('post_id');

    // 2. Retrieve the updated data from the JSON request body.
    $raw_data = $request->get_param('updated_word_search_data');
    if ( ! is_array($raw_data) ) {
        return new WP_Error(
            'invalid_data',
            'No valid word search data received.',
            array('status' => 400)
        );
    }

    // 3. Process the array similar to your original logic.
    $entries = array();
    foreach ($raw_data as $entry) {
        $word_text = isset($entry['wordText']) ? trim($entry['wordText']) : '';
        if (!empty($word_text)) {
            $entries[] = array(
                'id'       => sanitize_text_field(isset($entry['id']) ? $entry['id'] : uniqid('ws_', true)),
                'wordText' => sanitize_text_field($word_text),
                'hidden'   => isset($entry['hidden']) ? (bool) $entry['hidden'] : false,
                'imageUrl' => isset($entry['imageUrl']) ? esc_url($entry['imageUrl']) : '',
            );
        }
    }

    if (!empty($entries)) {
        update_post_meta($post_id, 'word_search_entries', $entries);
    } else {
        delete_post_meta($post_id, 'word_search_entries');
    }

    // Log for debugging.
    error_log("Wordsearch data saved successfully via REST API for post {$post_id}");
    $updatedData = get_post_meta($post_id, 'word_search_entries', true);
    error_log("Data: " . print_r($updatedData, true));

    return array(
        'success' => true,
        'message' => 'Word Search Entries saved successfully via REST API.',
        'data'    => $updatedData
    );
}




// Render the meta box content.
function render_wordsearch_meta_box($post) {
    // Add a nonce field for security.
    wp_nonce_field('wordsearch_meta_box_nonce', 'wordsearch_meta_box_nonce_field');

    $word_entries = get_post_meta($post->ID, 'word_search_entries', true);
    error_log("Values" . print_r($word_entries,true));

    if (empty($word_entries) || !is_array($word_entries)) {
        $word_entries = array();
    }


// Retrieve settings for Add Word and Clear List buttons
// Retrieve settings for Add Word and Clear List buttons
$default_add_word_label = __('Add a Word', 'wp-quiz-plugin');
$add_word_label = get_option('kw_wordsearch_admin_add_word_button_label', $default_add_word_label);
$add_word_bg_color = get_option('kw_wordsearch_admin_add_word_button_color', '#0073aa');
$add_word_text_color = get_option('kw_wordsearch_admin_add_word_button_text_color', '#ffffff');

$default_clear_list_label = __('Clear List', 'wp-quiz-plugin');
$clear_list_label = get_option('kw_wordsearch_admin_clear_list_button_label', $default_clear_list_label);
$clear_list_bg_color = get_option('kw_wordsearch_admin_clear_list_button_color', '#0073aa');
$clear_list_text_color = get_option('kw_wordsearch_admin_clear_list_button_text_color', '#ffffff');
    ?>

<div id="wordsearch-container">
    <div style="flex: 1">
        <div id="wordsearch-words-container">
            <?php 
            if ( ! empty( $word_entries ) && is_array( $word_entries ) ) :
                foreach ( $word_entries as $index => $entry ) :
                    $uniqueId = isset( $entry['id'] ) ? esc_attr( $entry['id'] ) : uniqid();
            ?>
            <div class="add-word-container" name ="add-word-container" data-index="<?php echo esc_attr( $index ); ?>" data-unique-id="<?php echo $uniqueId; ?>">
                <span class="word-number"><?php echo esc_html( $index + 1 ); ?>.</span>
                <div class="kw-wordsearch-words-container">
                    <input type="text" 
                           class="word-input word-input-<?php echo $uniqueId; ?>" 
                           name="wordsearch_words[<?php echo esc_attr( $index ); ?>][word]" 
                           placeholder="<?php esc_attr_e('Word', 'wp-quiz-plugin'); ?>" 
                           value="<?php echo esc_attr( isset( $entry['wordText'] ) ? $entry['wordText'] : '' ); ?>" />
                    <input type="hidden" 
                           name="wordsearch_words[<?php echo esc_attr( $index ); ?>][uniqueId]" 
                           value="<?php echo $uniqueId; ?>" />
                    <div class="actions">
                        <div class="wordsearch-image-preview wordsearch-image-preview-<?php echo $uniqueId; ?>">
                            <?php if ( ! empty( $entry['imageUrl'] ) ) : ?>
                                <img src="<?php echo esc_url( $entry['imageUrl'] ); ?>" 
                                     style="max-width: 70px; max-height: 70px; border-radius: 5%; padding-left: 10px;" 
                                     alt="<?php esc_attr_e('Word image preview', 'wp-quiz-plugin'); ?>" />
                                <input type="hidden" 
                                       class="wordsearch-image-url" 
                                       name="wordsearch_words[<?php echo esc_attr( $index ); ?>][image]" 
                                       value="<?php echo esc_url( $entry['imageUrl'] ); ?>" />
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
            <?php 
                endforeach;
            endif;
            ?>
        </div>

        <div class="wordsearch-add-button-container">
            <button type="button" id="add-wordsearch-button" 
                    style="background-color: <?php echo esc_attr($add_word_bg_color); ?>; color: <?php echo esc_attr($add_word_text_color); ?>;">
                <?php echo esc_html__($add_word_label); ?>
            </button>
            
            <button type="button" id="clear-wordsearch-list-button" 
                    style="background-color: <?php echo esc_attr($clear_list_bg_color); ?>; color: <?php echo esc_attr($clear_list_text_color); ?>;">
                <?php echo esc_html__($clear_list_label); ?>
            </button>
            <!-- Hidden field stores the JSON-encoded word entries -->
            <input type="hidden" id="word_search_entries" name="word_search_entries" 
                   value='<?php echo esc_attr( json_encode( $word_entries ? $word_entries : [] ) ); ?>'>
        </div>
    </div>
</div>

<?php
// Retrieve the stored word entries from post meta
$word_entries = get_post_meta($post->ID, 'word_search_entries', true);

// Ensure it's an array before counting
$entry_count = is_array($word_entries) ? count($word_entries) : 0;
?>

<script>
  // Initialize entryNumber in JavaScript with the count from PHP
  var entryNumber = <?php echo intval($entry_count); ?>;
  var entries = <?php echo json_encode($word_entries); ?>;
</script>

    
<script type="text/template" id="wordsearch-word-template">
  <div class="add-word-container" name ="add-word-container" data-index="{{index}}" data-unique-id="{{uniqueId}}">
    <span class="word-number">{{number}}.</span>
    <div class="kw-wordsearch-words-container">
      <input type="text" 
             class="word-input word-input-{{uniqueId}}" 
             name="wordsearch_words[{{index}}][word]" 
             placeholder="<?php esc_attr_e('Word', 'wp-quiz-plugin'); ?>" 
             value="" />
             <input type="hidden" name="wordsearch_words[{{index}}][uniqueId]" value="{{uniqueId}}" />
      <!-- <input type="hidden" id="word_search_entries" name="word_search_entries" /> -->
      <div class="actions">
        <div class="wordsearch-image-preview wordsearch-image-preview-{{uniqueId}}">
          <!-- Image preview will be appended here if needed -->
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
</script>


    <?php
}

