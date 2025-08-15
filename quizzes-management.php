<?php
/*
Plugin Name: OmniS
Description: A WordPress plugin to create and manage quizzes with questions and user submissions.
Version: 5.5.1
Author: Kazmi Webwhiz
Author URI: https://kazmiwebwhiz.com
Text Domain: wp-quiz-plugin
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Include necessary files
include_once plugin_dir_path(__FILE__) . 'public/public-functions.php';  // Include public functions
include_once plugin_dir_path(__FILE__) . 'quizzes-admin.php';  // Include public functions
include_once plugin_dir_path(__FILE__) . 'quizzes-submissions.php';  // Include Submissions page file
include_once plugin_dir_path(__FILE__) . 'quiz-template-download.php';  // Include Submissions page file
include_once plugin_dir_path(__FILE__) . 'kw-quiz-post-and-taxonomies.php';  // Include Submissions page file
include_once plugin_dir_path(__FILE__) . 'kw-questions-metabox-header.php'; 
include_once plugin_dir_path(__FILE__) . './custom-meta-boxes/quiz_seo_text.php';
include_once plugin_dir_path(__FILE__) . 'utils/short-code-helpers.php'; 
include_once plugin_dir_path(__FILE__) . 'utils/constants.php'; 
include_once plugin_dir_path(__FILE__) . 'kw-quiz-features-buttons.php';
include_once plugin_dir_path(__FILE__) . 'admin-docs.php';
include_once plugin_dir_path(__FILE__) . 'kw-author-name-admin-view.php';
include_once plugin_dir_path(__FILE__) .  'kw-save-quiz-aut.php';
include_once plugin_dir_path(__FILE__) . 'wp-source-text-choice.php';


// Enqueue Plugin Styles for Admin

function wp_quiz_plugin_enqueue_styles($hook) {
    global $post;
    $screen = get_current_screen();
    $forget_warning_msg = __("Don't forget to save! Your changes may be lost.", 'wp-quiz-plugin');
    $onlyOnePdfText = __("Please remove your existing PDF before uploading a new one.", 'wp-quiz-plugin');
    $onlyFourImagesText = __("You can only upload a maximum of 4 images.", 'wp-quiz-plugin');

    $stylesheets = array(
        'stylesheet.css',
        'quiz-post-taxonomies.css',
    );

    foreach ($stylesheets as $stylesheet) {
        wp_register_style(
            'wp-quiz-plugin' . sanitize_title($stylesheet), // Unique handle name
            plugins_url('assets/' . $stylesheet, __FILE__), // URL to the stylesheet
            array(), // Dependencies
            '1.0', // Version
            'all' // Media
        );

        wp_enqueue_style('wp-quiz-plugin' . sanitize_title($stylesheet));
    }
    wp_enqueue_style('main-style', plugins_url('main-style.css', __FILE__));

    wp_enqueue_script('wp-quiz-plugin-admin-js', plugin_dir_url(__FILE__) . 'assets/js/admin.js', ['jquery'], null, true);

    global $post;
    if (isset($post->ID)) {
        $quizId = $post->ID;
        error_log("Quiz ID set to: " . $quizId); // Debugging line to check quiz ID
    } else {
        $quizId = 0; // Default value if no post is set
    }
    wp_localize_script('wp-quiz-plugin-admin-js', 'quizAdminData', [
        'message' => __("Don't forget to save! Your changes may be lost.", 'wp-quiz-plugin'),
        "forgetWarningMsg" => $forget_warning_msg,
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('gpt_source_choice'),
        'quizId'  => $quizId ?? 0, // Replace if you know the quiz/post ID on front-end.
    ]);

    // Ensure scripts and styles are loaded only on plugin-related admin pages
    if ($screen && $screen->post_type === 'quizzes' && ( $hook === 'post-new.php' || $hook === 'post.php' )) {
        wp_enqueue_style(
            'ocrspace-admin-css',
            plugin_dir_url(__FILE__) . 'assets/css/ocrspace-admin.css',
            array(),
            '1.0.0',
            'all'
        );
        
        $translated_text = __('No files selected', 'wp-quiz-plugin');
        $custom_css = ".file-preview-container:empty::after { content: '$translated_text'; }";

        wp_add_inline_style( 'ocrspace-admin-css', $custom_css );

        wp_enqueue_script(
            'ocrspace-admin-js',
            plugin_dir_url(__FILE__) . 'assets/js/ocrspace-admin.js',
            ['jquery'],
            '1.0.0',
            true
        );

    wp_localize_script('ocrspace-admin-js', 'OCRSPACE', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('wqo_ocrspace_nonce'),
        'imageNonce' => wp_create_nonce('wqo_ocrspace_nonce_image'),
        'uploadImageText' => esc_js(__('Upload Image(s)', 'wp-quiz-plugin')),
        'apiKey' => get_option('wp_quiz_plugin_openai_api_key'), // Fetch the API key from settings
        'onlyOnePdfText' => esc_attr($onlyOnePdfText),
        'onlyFourImagesText' => esc_attr($onlyFourImagesText),
    ]);
    // Register pdf-lib from unpkg CDN as ES module

        // Register pdf-lib from unpkg CDN as ES module

    // PDF.js core
    wp_enqueue_script(
        'pdf-js',
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.0.379/pdf.min.mjs',
        array(),
        '4.0.379',
        true
    );

    wp_enqueue_script(
        'pdf-js-worker',
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.0.379/pdf.worker.min.js', // <-- **CHANGED TO .js (UMD)**
        array(),
        '4.0.379',
        false
    );

    // Enqueue Tesseract.js library (from CDN)
    wp_enqueue_script(
        'tesseract-js',
        'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js',
        array(),
        '5.0.0',
        true
    );
}
}
add_action('admin_enqueue_scripts', 'wp_quiz_plugin_enqueue_styles'); // Change to admin_enqueue_scripts for admin area


// 4) Tell WP to output type="module" for these scripts

add_filter( 'script_loader_tag', 'myquizplugin_module_loader', 10, 2 );

function myquizplugin_module_loader( $tag, $handle ) {
    // Only modify our two handles
    if ( $handle === 'ocrspace-admin-js' || $handle === 'pdf-js' || $handle === 'pdf-js-worker' || $handle === 'tesseract-js') {
        // Inject type="module" into the <script> tag
        $tag = str_replace( '<script ', '<script type="module" ', $tag );
    }
    return $tag;
}

    // Check if the crossword module is enabled
    function load_crossword_module() {
        //$is_crossword_enabled = get_option('enable_crossword_module'); // Replace with your option name if different

        //if ($is_crossword_enabled) {
            include_once plugin_dir_path(__FILE__) . 'modules/crossword/index.php';
        //}

        include_once plugin_dir_path(__FILE__) . 'modules/wordsearch/index.php';
    }
    add_action('plugins_loaded', 'load_crossword_module');


    // AJAX Handler to Save PDF Image URL
function kw_save_pdf_image() {
    check_ajax_referer('kw_save_pdf_image_nonce', '_wpnonce'); // Verify nonce

    $post_id = intval($_POST['post_id']);
    $pdf_image_url = sanitize_text_field($_POST['pdf_image_url']);

    if (!current_user_can('edit_post', $post_id)) {
        wp_send_json_error('You do not have permission to edit this post.');
    }

    update_post_meta($post_id, 'kw_quiz_plugin_pdf_image', $pdf_image_url); // Save image URL to post meta

    wp_send_json_success('PDF Image saved successfully.');
}
add_action('wp_ajax_kw_save_pdf_image', 'kw_save_pdf_image');

// Save the custom field data
function kw_save_quiz_meta_box_data($post_id) {
    // Check if our nonce is set
    if (!isset($_POST['kw_quiz_plugin_pdf_image_nonce_field'])) {
        return $post_id;
    }

    // Verify that the nonce is valid
    if (!wp_verify_nonce($_POST['kw_quiz_plugin_pdf_image_nonce_field'], 'kw_quiz_plugin_pdf_image_nonce')) {
        return $post_id;
    }

    // Check if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    // Check the user's permissions
    if (isset($_POST['post_type']) && 'quizzes' === $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }
    }

    // Sanitize user input and update the meta field
    if (isset($_POST['kw_quiz_plugin_pdf_image'])) {
        $image_url = sanitize_text_field($_POST['kw_quiz_plugin_pdf_image']);
        update_post_meta($post_id, 'kw_quiz_plugin_pdf_image', $image_url);
    } else {
        delete_post_meta($post_id, 'kw_quiz_plugin_pdf_image');
    }
}
add_action('save_post', 'kw_save_quiz_meta_box_data');

// Create custom database questions table
function create_quiz_questions_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'quiz_questions'; // Table name with WordPress prefix

    $charset_collate = $wpdb->get_charset_collate();

    // SQL statement for creating the custom table with all fields
    $sql = "CREATE TABLE $table_name (
        QuesID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        QuizID BIGINT(20) UNSIGNED NOT NULL,
        Title VARCHAR(255) NOT NULL,
        TitleImage VARCHAR(255),
        Answer LONGTEXT NOT NULL,
        QuestionType ENUM('MCQ', 'T/F', 'Text') NOT NULL,
        `Order` INT(11) DEFAULT 0,
        PRIMARY KEY (QuesID)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql); // Create or upgrade the table

    // Debugging - Check for errors
    if (!empty($wpdb->last_error)) {
        error_log('Error creating table: ' . $wpdb->last_error);
    }
    }
register_activation_hook(__FILE__, 'create_quiz_questions_table');

// 1) Add your “Quiz Questions” box
add_action( 'add_meta_boxes', 'add_questions_meta_box' );
function add_questions_meta_box() {
    add_meta_box(
        'quiz_questions_meta_box',       // Meta box ID
         esc_html__( 'Quiz Questions', 'wp-quiz-plugin' ), // Title
        'display_questions_meta_box',    // Callback
        'quizzes',                       // Post type (make sure this matches your CPT slug)
        'normal',                        // Context
        'high'                           // Priority
    );
}

// 2) At a super-late priority, pull out & prepend your five side-panel boxes
/**
 * Enqueue our meta-box reorderer on the quiz/crossword/wordsearch edit screens.
 */
add_action( 'admin_enqueue_scripts', function( $hook ) {
    // Only run on post-new.php and post.php
    if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
        return;
    }

    $screen = get_current_screen();
    if ( ! in_array( $screen->post_type, [ 'quizzes', 'crossword', 'wordsearch' ], true ) ) {
        return;
    }

    wp_enqueue_script(
        'kw-reorder-side-metaboxes',
        plugin_dir_url( __FILE__ ) . 'assets/js/kw-reorder-side-metaboxes.js',
        [ 'jquery' ],
        '1.0',
        true
    );
    // Pass the current post type into JS
    wp_localize_script( 'kw-reorder-side-metaboxes', 'KW_MetaBox_Order', [
        'postType' => $screen->post_type,
    ] );
} );


// Display Meta Box Content
function display_questions_meta_box($post) {
    global $wpdb;
    wp_nonce_field('save_quiz_questions_meta', 'quiz_questions_meta_nonce'); // Security nonce

    $quiz_id = $post->ID;
    $table_name = $wpdb->prefix . 'quiz_questions';

    // Get existing questions from custom table
    $questions = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE QuizID = %d ORDER BY `Order`", $quiz_id), ARRAY_A);

    echo kw_render_quiz_buttons($quiz_id);
    
    ?>
    <?php
    $text_font = get_option('wp_quiz_plugin_text_font', 'Arial');  // Get text font setting
    $font_size = get_option('wp_quiz_plugin_text_font_size', '16px');  // Get text font size setting
    $text_color = get_option('wp_quiz_plugin_text_color', '#000000');  // Get text color setting
    $answer_text_font = get_option('wp_quiz_plugin_answer_text_font', 'Arial');  // Get answer text font setting
    $answer_text_color = get_option('wp_quiz_plugin_answer_text_color', '#2c3338');  // Get answer text color setting
    $answer_text_font_size = get_option('wp_quiz_plugin_answer_text_font_size', '16px'); // Get answer text font setting
    
    // Retrieve settings for "Add New Question" button
    $add_question_btn_font = get_option('wp_quiz_plugin_add_question_btn_font', 'Arial');
    $add_question_btn_color = get_option('wp_quiz_plugin_add_question_btn_color', '#28a745');
    $add_question_btn_font_color = get_option('wp_quiz_plugin_add_question_btn_font_color', '#ffffff');
    $add_question_btn_font_size = get_option('wp_quiz_plugin_add_question_btn_font_size', '16px');  // New font size
    

    // Fetch settings for the "Generate with AI" button
    $generate_with_ai_text_color = esc_attr(get_option('quiz_generate_with_ai_text_color', '#ffffff'));
    $generate_with_ai_bg_color = esc_attr(get_option('quiz_generate_with_ai_bg_color', '#007BFF
'));
    $generate_with_ai_font_size = esc_attr(get_option('quiz_generate_with_ai_font_size', '14'));

    // Fetch settings for the "Add Question" button
    $add_question_text_color = esc_attr(get_option('quiz_add_question_text_color', '#000000'));
    $add_question_bg_color = esc_attr(get_option('quiz_add_question_bg_color', '#ffffff'));
    $add_question_font_size = esc_attr(get_option('quiz_add_question_font_size', '14'));

    $add_question_text = get_option('wp_quiz_plugin_add_question_text', __('Add New Question', 'wp-quiz-plugin'));
    $add_ai_question_text = get_option('wp_quiz_plugin_add_ai_question_text', __('Add New Question With AI', 'wp-quiz-plugin'));
    $upload_question_image_text = get_option('wp_quiz_plugin_upload_question_image_text', __('Upload Question Image', 'wp-quiz-plugin'));
    $add_option_text = get_option('wp_quiz_plugin_add_option_text', __('Add New Option', 'wp-quiz-plugin'));
    $type_question_text = get_option('wp_quiz_plugin_type_question_text', __('Type Question Here', 'wp-quiz-plugin'));
    $add_answer_text = get_option('wp_quiz_plugin_add_answer_text', __('Click to Add Answer', 'wp-quiz-plugin'));
    $image_height_settings_notification = get_option('wp_quiz_plugin_image_height_settings_notofication', __('Image Height Setting Notification Text', 'wp-quiz-plugin'));

    // Retrive Lables
    $set_question_image_size_text = get_option('wp_quiz_plugin_question_image_label_text', 'Set Question Image Size:');
    $set_answer_image_size_text = get_option('wp_quiz_plugin_answer_image_label_text', 'Set Answer Image Size:');
    $image_width_lable_text = get_option('wp_quiz_plugin_image_width_label_text', 'Width:');
    $image_height_lable_text = get_option('wp_quiz_plugin_image_height_label_text', 'Height:');
    $open_ended_question_lable = get_option('wp_quiz_plugin_open_text_area_label_text', 'Actual Answer');

      // Retrieve existing values
    $question_image_width = get_post_meta($post->ID, 'question_image_width', true);
    $question_image_height = get_post_meta($post->ID, 'question_image_height', true);
    $answer_image_width = get_post_meta($post->ID, 'answer_image_width', true);
    $answer_image_height = get_post_meta($post->ID, 'answer_image_height', true);
    
    // Image Notification settings

    $notification_text_color = get_option('wp_quiz_plugin_notification_text_color', '#000000');
    $notification_background_color = get_option('wp_quiz_plugin_notification_background_color', '#ffffff');
    $notification_font_size = get_option('wp_quiz_plugin_notification_font_size', '16px');
    $notification_font_family = get_option('wp_quiz_plugin_notification_font_family', 'Arial');

    // Fetch prompt templates from admin settings
    $defaultMcqPrompt = "Generate a quiz question in the same language as the provided prompt. For example, if the prompt is in Polish, generate the question in Polish, and if the prompt is in English, generate the question in English.Use the following plain text format:\nQuestion: [Your question text]\nAnswer Options: A) [Option A], B) [Option B], C) [Option C], D) [Option D]\nCorrect Answer: [A) Option A/B) Option B/C) Option C/D) Option D]";
    $defaultTfPrompt = "Generate a True or False quiz question in the same language as the provided prompt. For example, if the prompt is in Polish, generate the question in Polish, and if the prompt is in English, generate the question in English. Use the following plain text format:\nQuestion: [Your question text]\nCorrect Answer: [1/0] (Use 1 for True and 0 for False)";
    $defaultTextPrompt = "Generate a quiz question that requires a text answer in the same language as the provided prompt. For example, if the prompt is in Polish, generate the question in Polish, and if the prompt is in English, generate the question in English. Use the following plain text format:\nQuestion: [Your question text]\nCorrect Answer: [Your text answer]";

    $mcqPromptTemplate = get_option('wp_quiz_plugin_mcq_prompt_template', $defaultMcqPrompt);
    $tfPromptTemplate = get_option('wp_quiz_plugin_tf_prompt_template', $defaultTfPrompt);
    $textPromptTemplate = get_option('wp_quiz_plugin_text_prompt_template', $defaultTextPrompt);
    $defaultAgePrompt = 'The learners\' age is [age]';
    $learnersAgePromptTemplate = get_option('wp_quiz_plugin_learners_age_prompt_template', $defaultAgePrompt);
    $default_category_value = get_option('wp_quiz_plugin_category_select_default_prompt', 'Physics');
    // Fetch settings for the "Download PDF" button
    $download_pdf_text_color = esc_attr(get_option('quiz_download_pdf_text_color', '#ffffff'));
    $download_pdf_bg_color = esc_attr(get_option('quiz_download_pdf_bg_color', '#ffffff'));
    $download_pdf_font_size = esc_attr(get_option('quiz_download_pdf_font_size', '14'));
    // Fetch settings for the "Download Answer Key" button
    $download_answer_key_text_color = esc_attr(get_option('quiz_download_answer_key_text_color', '#ffffff'));
    $download_answer_key_bg_color = esc_attr(get_option('quiz_download_answer_key_bg_color', '#ffffff'));
    $download_answer_key_font_size = esc_attr(get_option('quiz_download_answer_key_font_size', '14'));

    $uploadImagesLabel = __('Upload Image(s):', 'wp-quiz-plugin');
    $uploadPdfLabel = __('Upload PDF(s):', 'wp-quiz-plugin');
    $uploadPdfButtonLabel = __('Choose PDFs', 'wp-quiz-plugin');
    $chooseImagesLabel = __('Choose Images:', 'wp-quiz-plugin');
    $noImageLabel = __('No images chosen', 'wp-quiz-plugin');
    $noPdfLabel = __('No PDFs chosen', 'wp-quiz-plugin');
    $noFilesSelectedLabel = __('No files selected', 'wp-quiz-plugin');
    $generateByTextLabel = __('Provide Text to Generate Questions:', 'wp-quiz-plugin');
    $includeText = get_post_meta($quiz_id, '_quiz_include_source', true);
    $sourceText = get_post_meta($quiz_id, '_quiz_source_text', true) ?? '';
    ?>


    <div class="kw-loading" style="display:none">
      <div class="kw-loading-text"><?php echo esc_html(__('Generating Quiz...', 'wp-quiz-plugin')); ?></div>
    </div>


    <div id="kw_quiz-questions-container" style="font-family: <?php echo esc_attr($text_font); ?>; color: <?php echo esc_attr($text_color); ?>; font-size: <?php echo esc_attr($font_size); ?>">

    <?php if ( $includeText && $sourceText ) : ?>
    <div class="include-text">
        <p class="text"><?php echo esc_html( $sourceText ); ?></p>
    </div>
    <?php endif; ?>

        <div class="kw_right">
            <div id="kw-quiz-questions-list">
                <?php if (!empty($questions)): ?>
                <?php foreach ($questions as $index => $question): ?>
                <div class="kw-question-item" data-index="<?php echo $index; ?>">

                <div class="kw_question-header kw_close-expand kw_toggle-question-btn">
                <div class="question-content">
                    <div class="question-badge">
                        <span class="question-text">Question <?php echo ($index + 1); ?></span>
                    </div>
                    <div class="question-row">
                        <span class="kw_handle-icon" style="cursor: move;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="16" height="16">
                                <rect y="4" width="20" height="2" rx="1"></rect>
                                <rect y="9" width="20" height="2" rx="1"></rect>
                                <rect y="14" width="20" height="2" rx="1"></rect>
                            </svg>
                        </span>
                        <div class="question-title"><?php echo esc_html($question['Title']); ?></div>
                        <span class="kw_remove-question-btn kw_ml-2" data-id="<?php echo esc_attr($question['QuesID']); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                            </svg>
                        </span>
                    </div>
                </div>
                <input type="hidden" name="quiz_questions[<?php echo $index; ?>][order]" value="<?php echo $index; ?>">
            </div>

                    
                    <div class="kw_question-body">
                        <!--<label>Question Type:</label>-->
                        <select name="quiz_questions[<?php echo $index; ?>][type]" class="kw_question-type-select">
                            <option selected disabled><?php echo __('Question Type', 'wp-quiz-plugin'); ?>
                            </option>
                            <option value="MCQ" <?php selected($question['QuestionType'], 'MCQ'); ?>>
                                <?php echo __('Multiple Choice', 'wp-quiz-plugin'); ?>
                            </option>
                            <option value="T/F" <?php selected($question['QuestionType'], 'T/F'); ?>>
                                <?php echo __('True/False', 'wp-quiz-plugin'); ?>
                            </option>
                            <option value="Text" <?php selected($question['QuestionType'], 'Text'); ?>>
                                <?php echo __('Open Text', 'wp-quiz-plugin'); ?>
                            </option>
                        </select>
                        <!-- Add hidden field to store existing question ID -->
                        <input type="hidden" name="quiz_questions[<?php echo $index; ?>][id]"
                            value="<?php echo esc_attr($question['QuesID']); ?>">
                        
                            <textarea class="kw_styled-box" 
                                style="
                                    font-family: <?php echo esc_attr($answer_text_font); ?>; 
                                    color: <?php echo esc_attr($answer_text_color); ?>; 
                                    font-size: <?php echo esc_attr($answer_text_font_size); ?>;" 
                                placeholder="<?php echo esc_attr($type_question_text); ?>" 
                                name="quiz_questions[<?php echo $index; ?>][title]" 
                                required><?php echo esc_textarea($question['Title']); ?></textarea>

                                                    
                        <div class="kw_btn-add-img kw_upload-question-image-btn">
                            <span class="kw_plus-icon">+</span> <?php echo esc_html(__($upload_question_image_text, 'wp-quiz-plugin')); ?>
                            <span class="kw_upload-image-btn-q-svg">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M160 80l352 0c8.8 0 16 7.2 16 16l0 224c0 8.8-7.2 16-16 16l-21.2 0L388.1 178.9c-4.4-6.8-12-10.9-20.1-10.9s-15.7 4.1-20.1 10.9l-52.2 79.8-12.4-16.9c-4.5-6.2-11.7-9.8-19.4-9.8s-14.8 3.6-19.4 9.8L175.6 336 160 336c-8.8 0-16-7.2-16-16l0-224c0-8.8 7.2-16 16-16zM96 96l0 224c0 35.3 28.7 64 64 64l352 0c35.3 0 64-28.7 64-64l0-224c0-35.3-28.7-64-64-64L160 32c-35.3 0-64 28.7-64 64zM48 120c0-13.3-10.7-24-24-24S0 106.7 0 120L0 344c0 75.1 60.9 136 136 136l320 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-320 0c-48.6 0-88-39.4-88-88l0-224zm208 24a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/></svg>
                            </span>  
                            <div class="kw_ques-image-preview" style="padding-left: 10px">
                                <?php if ($question['TitleImage']) echo '<img src="' . esc_url($question['TitleImage']) . '" style="max-width: 100px; max-height: 100px;" />'; ?>
                            </div>
                        </div>
                        <input class="kw_question-image-url"
                            type="hidden" name="quiz_questions[<?php echo $index; ?>][title_image]"
                            value="<?php echo esc_url($question['TitleImage']); ?>">
                        

                        <div class="kw_four-column-wrapper">
                        <div class="kw_answers-container kw_four-column-container">
                            <?php
                                // Decode the answers JSON stored in the database
                                $answers = json_decode($question['Answer'], true);

                                // Dynamically generate answer fields based on question type
                                if ($question['QuestionType'] === 'MCQ') {
                                    $option_letters = ['A', 'B', 'C', 'D'];
                                
                                    foreach ($answers as $ans_index => $answer) {
                                        $image_url = isset($answer['image']) ? esc_url($answer['image']) : ''; // Retrieve image URL if exists
                            ?>
                            <div class="kw_answer-item kw_column-item">
                            <?php if (!empty($answer['correct']) && $answer['correct'] == '1') : ?>
                                <span class="answer-ribbon correct-ribbon"><?php echo _x('Correct Answer', 'MCQ Hover', 'wp-quiz-plugin'); ?></span>
                            <?php else : ?>
                                <span class="answer-ribbon incorrect-ribbon"><?php echo _x('Incorrect Answer', 'MCQ Hover', 'wp-quiz-plugin'); ?></span>
                            <?php endif; ?>

                            <span class="kw_option-letter"><?php echo $option_letters[$ans_index]; ?>.</span>
                            <textarea
                                class="kw_answerinputs"
                                placeholder="<?php echo esc_attr(__($add_answer_text, 'wp-quiz-plugin')); ?>"
                                name="quiz_questions[<?php echo $index; ?>][answers][<?php echo $ans_index; ?>][text]"
                                required 
                                rows="1"
                                style="
                                    font-family: <?php echo esc_attr($answer_text_font); ?>;
                                    color: <?php echo esc_attr($answer_text_color); ?>;
                                    font-size: <?php echo esc_attr($answer_text_font_size); ?>;
                                    resize: none;
                                    overflow: auto;
                                " required><?php echo esc_attr($answer['text']); ?></textarea>
                                <label>  <input type="checkbox"
                                        name="quiz_questions[<?php echo $index; ?>][answers][<?php echo $ans_index; ?>][correct]"
                                        <?php checked($answer['correct'], '1'); ?>></label>
                                                    <span class="kw_upload-image-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M160 80l352 0c8.8 0 16 7.2 16 16l0 224c0 8.8-7.2 16-16 16l-21.2 0L388.1 178.9c-4.4-6.8-12-10.9-20.1-10.9s-15.7 4.1-20.1 10.9l-52.2 79.8-12.4-16.9c-4.5-6.2-11.7-9.8-19.4-9.8s-14.8 3.6-19.4 9.8L175.6 336 160 336c-8.8 0-16-7.2-16-16l0-224c0-8.8 7.2-16 16-16zM96 96l0 224c0 35.3 28.7 64 64 64l352 0c35.3 0 64-28.7 64-64l0-224c0-35.3-28.7-64-64-64L160 32c-35.3 0-64 28.7-64 64zM48 120c0-13.3-10.7-24-24-24S0 106.7 0 120L0 344c0 75.1 60.9 136 136 136l320 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-320 0c-48.6 0-88-39.4-88-88l0-224zm208 24a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/></svg>
                        </span>        
                                <!--<button type="button" class="kw_upload-image-btn">Upload Image</button>-->
                                <input type="hidden" class="kw_answer-image-url"
                                    name="quiz_questions[<?php echo $index; ?>][answers][<?php echo $ans_index; ?>][image]"
                                    value="<?php echo $image_url; ?>">
                                <div class="kw_image-preview">
                                    <?php if ($image_url) echo '<img src="' . $image_url . '" style="max-width: 70px; max-height: 70px;border-radius: 5%;
        padding-left: 10px;" />'; ?>
                                </div>
                            </div>
                            
                            <?php
                                                }
                                                echo '<div class="kw_btn-add-option kw_add-more-answers-btn">
    <span class="kw_plus-icon">+</span>' . esc_html($add_option_text) . '
</div>';
                                            } elseif ($question['QuestionType'] === 'T/F') {
                                                $true_checked = ($answers[0]['correct']) ? 'checked' : '';
                                                $false_checked = ($answers[1]['correct']) ? 'checked' : '';
                                                ?>
                            <div class="kw_answer-item kw_column-item">
                                <input type="text" class="kw_answerinputs"  name="quiz_questions[<?php echo $index; ?>][answers][0][text]"
                                    value="<?php echo esc_attr(__('True', 'wp-quiz-plugin')); ?>" readonly
                                    style="font-family: <?php echo esc_attr($answer_text_font); ?>; color: <?php echo esc_attr($answer_text_color); ?>;font-size: <?php echo esc_attr($answer_text_font_size);?>;">
                                <label style="padding-bottom: 0;">  
                                    <input type="radio" name="quiz_questions[<?php echo $index; ?>][correct]" value="0" <?php echo $true_checked; ?>>
                                </label>

                            </div>
                            <div class="kw_answer-item kw_column-item">
                                <input type="text" class="kw_answerinputs" name="quiz_questions[<?php echo $index; ?>][answers][1][text]"
                                    value="<?php echo esc_attr(__('False', 'wp-quiz-plugin')); ?>"  readonly style="font-family: <?php echo esc_attr($answer_text_font); ?>; color: <?php echo esc_attr($answer_text_color); ?>;font-size: <?php echo esc_attr($answer_text_font_size);?>;">
                                <label style="padding-bottom: 0;">  <input type="radio" name="quiz_questions[<?php echo $index; ?>][correct]"
                                        value="1" <?php echo $false_checked; ?>></label>
                            </div>
                            <?php
                                            } elseif ($question['QuestionType'] === 'Text') {
                                                
                                                $text_answer = isset($answers[0]['text']) ? $answers[0]['text'] : '';
                                                // Add a translatable label for the open-ended answer field
                                                echo '<label style="color: #646970" for="quiz_questions_' . $index . '_answers_0_text">' . __($open_ended_question_lable, 'wp-quiz-plugin') . '</label>';
                                                wp_editor(
                                                    $text_answer,
                                                    'quiz_questions_' . $index . '_answers_0_text',
                                                    array(
                                                        'textarea_name' => 'quiz_questions[' . $index . '][answers][0][text]',
                                                        'media_buttons'  => false,
                                                        'textarea_rows'  => 5,
                                                        'teeny'          => true,
                                                        'quicktags'      => false,
                                                        'tinymce'        => array(
                                                            'toolbar1' => 'bold,italic,underline',
                                                            'toolbar2' => '',
                                                        ),
                                                    )
                                                );
                                            }
                                            ?>

                        </div>

                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            <!-- </div> -->
        </div>
        </div>

            <!-- Buttons with dynamically applied styles -->
    <div class="kw_left kw_quiz-content">
        <!-- Image size Settings -->
        <div class="image-size-settings">
            
    <svg xmlns="http://www.w3.org/2000/svg" style="display:none">
    <symbol id="check-circle-fill" fill="currentColor" viewBox="0 0 16 16">
        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
    </symbol>
    <symbol id="info-fill" fill="currentColor" viewBox="0 0 16 16">
        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
    </symbol>
    <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
        <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
    </symbol>
    </svg>

<div class="alert kw_modern-alert" style="display: flex; background: <?php echo esc_attr($notification_background_color); ?>; padding: 1rem 1rem; margin-bottom: 10px; border-radius: 5px" role="alert">
  <svg style="padding-right: 10px; color:<?php echo esc_attr($notification_text_color );?> " class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Info:">
    <use xlink:href="#info-fill"/>
  </svg>
  <div class="kw_alert-content" style="font-family: <?php echo esc_attr($notification_font_family ); ?>; color: <?php echo esc_attr($notification_text_color ); ?>; font-size: <?php echo esc_attr($notification_font_size ); ?>">
    <?php echo __($image_height_settings_notification, 'wp-quiz-plugin'); ?>
  </div>
</div>
    <!-- Question Image Size Settings -->

    <div class="kw_settings-container">
        <div class="kw_settings-card collapsed">
        <div class="kw_settings-header">
        <span class="kw_toggle-icon">
    <svg width="24" height="24" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M2 4L6 8L10 4" fill="#4F883D"/>
    </svg>
    </span>
        <!-- <span class="kw_settings-icon">📝</span> -->
    <h3><?php _e($set_question_image_size_text, 'wp-quiz-plugin'); ?></h3>
    <p class="kw-quiz-tooltip" data-tooltip="<?php echo __("Specify the size of the question image in pixels. The maximum allowed size is 350px, and the default size is 200px.", 'wp-quiz-plugin'); ?>">ℹ️</p>
    </div>
    <div class="kw_settings-content">
    <div class="kw_size-inputs-container">

        <div class="kw_size-input-group">
        <label for="question_image_width" class="kw_input-label"><?php _e($image_width_lable_text, 'wp-quiz-plugin'); ?></label>
        <input class="kw_size-input" type="number" id="question_image_width" name="question_image_width" value="<?php echo esc_attr($question_image_width); ?>" min="0" max="350" placeholder="px"/>
        </div>

        <div class="kw_size-input-group">
        <label for="question_image_height" class="kw_input-label"><?php _e($image_height_lable_text, 'wp-quiz-plugin'); ?></label>
        <input class="kw_size-input" type="number" id="question_image_height" name="question_image_height" value="<?php echo esc_attr($question_image_height); ?>" min="0" max="350" placeholder="px"/>  
    </div>
        </div>
    </div>
</div>

    <!-- Answer Image Size Settings -->
    <div class="kw_settings-card collapsed">
    <div class="kw_settings-header">
    <span class="kw_toggle-icon">
    <svg width="24" height="24" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M2 4L6 8L10 4" fill="#4F883D"/>
    </svg>
</span>
    <!-- <span class="kw_settings-icon">💬</span> -->

    <h3><?php _e($set_answer_image_size_text, 'wp-quiz-plugin'); ?></h3>
    <p class="kw-quiz-tooltip" data-tooltip="<?php echo __("Specify the size of the question image in pixels. The maximum allowed size is 250px, and the default size is 200px.", 'wp-quiz-plugin');?>">ℹ️</p>
    </div>
    <div class="kw_settings-content">
    <div class="kw_size-inputs-container">

        <div class="kw_size-input-group">
        <label for="answer_image_width" class="kw_input-label"><?php _e($image_width_lable_text, 'wp-quiz-plugin'); ?></label>
        <input class="kw_size-input" type="number" id="answer_image_width" name="answer_image_width" value="<?php echo esc_attr($answer_image_width); ?>" min="0" max="250" placeholder="px"/>
        </div>

        <div class="kw_size-input-group">
        <label for="answer_image_height" class="kw_input-label"><?php _e($image_height_lable_text, 'wp-quiz-plugin'); ?></label>
        <input class="kw_size-input" type="number" id="answer_image_height" name="answer_image_height" value="<?php echo esc_attr($answer_image_height); ?>" min="0" max="250" placeholder="px"/>
    </div>
        </div>
</div>
    </div>
    </div>
</div>
    </div>

        <!-- Hidden input to track deleted questions -->
        <input type="hidden" id="kw_deleted_questions" name="deleted_questions" value="">

    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    
    <script>
        jQuery(document).ready(function ($) {
            const aiContainer = document.querySelector(".kw_ai-generate-container");
            var quizL10n = {
        selectImageTitle: '<?php echo esc_js( esc_html__( 'Select Image',    'wp-quiz-plugin' ) ); ?>',
        useImageText:     '<?php echo esc_js( esc_html__( 'Use this image', 'wp-quiz-plugin' ) ); ?>'
    };
            var quizQuestionsCount = 0;
            var aiType = 'prompt'; // Default AI type
            var maxTokens = 100; // Default max tokens
            window.sourceText = ''; // Global variable to store the source text
            // Initialize sortable on kw-quiz-questions-list to make kw-question-item sortable
            $("#kw-quiz-questions-list").sortable({
                items: ".kw-question-item",
                handle: ".kw_handle-icon",
                axis:   "y",
                helper: "clone",
                placeholder:    "kw-placeholder",
                forcePlaceholderSize: true,
                tolerance:      "pointer",
                update: function (event, ui) {
                    $("#kw-quiz-questions-list .kw-question-item").each(function (index) {
                        $(this).attr('data-index', index);
                        // Update the hidden input field to reflect the new order
                        $(this).find('input[name*="[order]"]').val(index);  // Add hidden input for the order
                    });
                }
            });

            var generatedQuestionsList = []; // Global list to keep track of generated questions

            

            // Updated code in handleImageUpload function to ensure the image is set correctly
            function handleImageUpload(buttonSelector, itemSelector, inputSelector, previewSelector) {
                $(document).on('click', buttonSelector, function (e) {
                    e.preventDefault();
                    var button = $(this);
                    var item = button.closest(itemSelector);
                    
                    // Ensure the item exists, or create a temporary element for the image
                    if (item.length === 0) {
                        item = $('<div class="temp-item"></div>').appendTo('body');
                    }

                    var customUploader = wp.media({
                        title: quizL10n.selectImageTitle,
                        button: { text: quizL10n.useImageText },
                        multiple: false // Single image upload
                    }).on('select', function () {
                        var attachment = customUploader.state().get('selection').first().toJSON();
                        var imageUrl = attachment.url; // Get the image URL
                        // Fetch the question index dynamically
                        // Determine if the upload is for a question or an answer
                        if (item.is('.kw_question-body')) {
                            // Question image logic
                            var questionIndex = item.closest('.kw-question-item').data('index'); // Get the data-index attribute
                            var inputName = 'quiz_questions[' + questionIndex + '][title_image]'; // Construct the name attribute dynamically

                            // Set the image URL in the corresponding hidden input field
                            if (item.find(inputSelector).length === 0) {
                                item.append('<input type="hidden" class="kw_question-image-url" name="' + inputName + '" value="' + imageUrl + '">');
                            } else {
                                item.find(inputSelector).val(imageUrl);
                            }
                            // Display the uploaded image
                            if (item.find(previewSelector).length === 0) {
                                item.find('.kw_upload-image-btn-q-svg').after('<div class="kw_ques-image-preview"><img src="' + imageUrl + '" style="max-width: 70px; max-height: 70px; border-radius: 5%; padding-left: 10px;" /></div>');
                        } else {
                                item.find('.kw_upload-image-btn-q-svg').after('<div class="kw_ques-image-preview"><img src="' + imageUrl + '" style="max-width: 70px; max-height: 70px; border-radius: 5%; padding-left: 10px;" /></div>');

                            }
                        } else if (item.is('.kw_answer-item')) {
                            // Answer image logic
                            var questionIndex = item.closest('.kw-question-item').data('index'); // Get the question index
                            var answerIndex = item.index(); // Get the answer index within the question
                            var inputName = 'quiz_questions[' + questionIndex + '][answers][' + answerIndex + '][image]'; // Construct the name attribute dynamically

                            // Set the image URL in the corresponding hidden input field
                            if (item.find(inputSelector).length === 0) {
                                item.append('<input type="hidden" class="kw_answer-image-url" name="' + inputName + '" value="' + imageUrl + '">');
                            } else {
                                item.find(inputSelector).val(imageUrl);
                            }
                            // Display the uploaded image
                            if (item.find(previewSelector).length === 0) {
                                item.append('<div class="kw_image-preview"><img src="' + imageUrl + '" style="max-width: 70px; max-height: 70px; border-radius: 5%; padding-left: 10px;" /></div>');
                            } else {
                                item.find(previewSelector).html('<img src="' + imageUrl + '" style="max-width: 70px; max-height: 70px; border-radius: 5%; padding-left: 10px;" />');
                            }
                        }

                        

                        // Remove the temporary element if it was created
                        if (item.hasClass('temp-item')) {
                            item.remove();
                        }
                    }).open();
                });
            }


            // Initialize image upload handlers
            handleImageUpload('.kw_upload-question-image-btn', '.kw_question-body', '.kw_question-image-url', '.kw_image-preview');
            handleImageUpload('.kw_upload-image-btn', '.kw_answer-item', '.kw_answer-image-url', '.kw_image-preview');

            // Function to create SweetAlert popups with dynamic styling
            function createSwalPopup(options, styles) {
                // Save the user-defined didOpen callback (if any)
                const userDidOpen = options.didOpen;
                return Swal.fire({
                    ...options,
                    customClass: { popup: 'custom-swal-popup' },
                    didOpen: function () {
                        // Apply the popup styles (including font size)
                        $('.swal2-popup').css({
                            'color': styles.fontColor,
                            'font-family': styles.fontFamily,
                            'font-size': styles.fontSize // Apply popup font size
                        });
                        // Apply the button styles (including font size)
                        $('.swal2-confirm').css({
                            'font-family': styles.buttonFontFamily,
                            'color': styles.buttonFontColor,
                            'background-color': styles.buttonBackgroundColor,
                            'font-size': styles.buttonFontSize // Apply button font size
                        });
                        $('.swal2-cancel').css({
                            'font-family': styles.buttonFontFamily,
                            'color': styles.buttonFontColor,
                            'font-size': styles.buttonFontSize 
                        });

                        // Then call the user-defined didOpen callback if it exists
                        if (typeof userDidOpen === 'function') {
                        userDidOpen.call(this);
                        }
                    }
                });
            }


            // ChatGPT API integration for generating questions     
            $(document).on('click', '.kw_ai-dropdown .kw_ai-option', function(e) {
                // e.stopPropagation();
                aiType = $(this).data('type');
                window.numberOfPdfs = 0;
                window.numberOfImages = 0;
                window.uploadedImages = [];
                window.uploadedPDFs = [];
                window.sourceText = '';
                aiContainer.classList.toggle("active");
                // Fetch dynamic styles from PHP options with proper escaping
                var swalStyles = {
                    fontColor: '<?php echo esc_js(get_option('wp_quiz_plugin_swal_font_color', '#000000')); ?>',
                    fontFamily: '<?php echo esc_js(get_option('wp_quiz_plugin_swal_font_family', 'Noto Sans, sans-serif')); ?>',
                    buttonFontFamily: '<?php echo esc_js(get_option('wp_quiz_plugin_swal_button_font_family', 'Arial')); ?>',
                    buttonFontColor: '<?php echo esc_js(get_option('wp_quiz_plugin_swal_button_font_color', '#ffffff')); ?>',
                    buttonBackgroundColor: '<?php echo esc_js(get_option('wp_quiz_plugin_swal_button_background_color', '#007bff')); ?>',
                    fontSize: '<?php echo esc_js(get_option('wp_quiz_plugin_swal_font_size', '16px')); ?>',  // Popup font size
                    buttonFontSize: '<?php echo esc_js(get_option('wp_quiz_plugin_swal_button_font_size', '14px')); ?>'  // Button font size
                };
            
                const questionTypeOptions = [
                { id: 'qt-mcq',  value: 'MCQ', label: '<?php echo esc_js(__('Multiple Choice','wp-quiz-plugin')); ?>' },
                { id: 'qt-tf',   value: 'T/F', label: '<?php echo esc_js(__('True/False','wp-quiz-plugin')); ?>' },
                { id: 'qt-text', value: 'Text',label: '<?php echo esc_js(__('Open Text','wp-quiz-plugin')); ?>' }
                ];

                const typeHtml = `
                <div class="question-box" id="kw-qt-box">
                    ${questionTypeOptions.map(o => `
                    <div class="kw-checkbox-wrapper-promot">
                        <input type="checkbox" id="${o.id}" name="qt[]" value="${o.value}">
                        <label for="${o.id}">${o.label}</label>
                    </div>
                    `).join('')}
                </div>
                `;

                // Enforce single selection (checkboxes behave like radios)
                $(document).off('change', '#kw-qt-box input[type="checkbox"]')
                .on('change', '#kw-qt-box input[type="checkbox"]', function () {
                $('#kw-qt-box input[type="checkbox"]').not(this).prop('checked', false);
                });

                createSwalPopup({
                    title: '<?php echo esc_js(__('Select Question Type', 'wp-quiz-plugin')); ?>',
                    html: typeHtml,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: '<?php echo __('Next', 'wp-quiz-plugin'); ?>',
                    cancelButtonText: '<?php echo __('Cancel', 'wp-quiz-plugin'); ?>',
                    preConfirm: () => {
                        const checked = Array.from(document.querySelectorAll('#kw-qt-box input[type="checkbox"]:checked'))
                        .map(el => el.value);
                        if (checked.length !== 1) {
                        Swal.showValidationMessage('<?php echo esc_js(__('Please check exactly one question type.','wp-quiz-plugin')); ?>');
                        return false;
                        }
                        return checked[0]; // returns e.g. "MCQ"
                    }
                }, swalStyles).then((typeResult) => {
                    if (typeResult.isConfirmed) {
                        var selectedType = typeResult.value;
                        var numberOfQuestionsOptions = '<?php echo esc_js(get_option('wp_quiz_plugin_number_of_questions', '1,5,10')); ?>'.split(',');
            
                        var inputOptions = {};
                        numberOfQuestionsOptions.forEach(option => inputOptions[option.trim()] = option.trim());

                        var checkboxOptions = <?php echo json_encode(get_option('wp_quiz_plugin_prompt_checkboxes', [])); ?>;
                        var baseHtml = '';
                        if (aiType === 'prompt') {
                        baseHtml = `<label style="display:block; font-weight:bold; margin-bottom:8px;">
                        <?php echo wp_kses_post(__('Enter your prompt below:', 'wp-quiz-plugin')); ?>
                        </label>
                        <input
                        type="text"
                        id="kw_user_prompt"
                        class="swal2-input"
                        placeholder="<?php echo esc_attr(__('Type your prompt here...', 'wp-quiz-plugin')); ?>"
                        > ` ;
                        }

                        var uploadHtml = '';
                        if (aiType === 'image') {
                        uploadHtml = `
                            <div class="container">
                            <!-- IMAGES upload -->
                            <div class="upload-section">
                                    🖼️ <?php echo __($uploadImagesLabel, 'wp-quiz-plugin'); ?>
                                <label></label>
                                <div class="upload-wrapper">
                                <button type="button" id="kw_image_upload_btn" class="swal2-confirm swal2-styled upload-btn">
                                    🖼️ <?php echo __($chooseImagesLabel, 'wp-quiz-plugin'); ?>
                                </button>
                                <span id="kw_image_upload_label" class="upload-status"><?php echo __($noImageLabel, 'wp-quiz-plugin'); ?></span>

                                <input type="file" id="kw_image_upload_input" accept="image/*" multiple style="display:none;">
                                </div>
                                <div id="kw_images_preview" class="file-preview-container"></div>
                            </div>
                            </div>
                        `;
                        }

                        else if (aiType === 'pdf') {
                        uploadHtml = `
                            <div class="container">
                            <!-- PDF upload -->
                            <div class="upload-section">
                                <label><?php echo __($uploadPdfLabel, 'wp-quiz-plugin'); ?></label>
                                <div class="upload-wrapper">
                                <button type="button" id="kw_pdf_upload_btn" class="swal2-confirm swal2-styled upload-btn">
                                    📄 <?php echo __($uploadPdfButtonLabel, 'wp-quiz-plugin'); ?>
                                </button>
                                <span id="kw_pdf_upload_label" class="upload-status">
                                    📄 <?php echo __($noPdfLabel, 'wp-quiz-plugin'); ?>
                                </span>
                                <input type="file" id="kw_pdf_upload_input" accept="application/pdf" multiple style="display:none;">
                                </div>
                                <div id="kw_pdfs_preview" class="file-preview-container"></div>
                            </div>
                            </div>
                        `;
                        }
                        else if (aiType === 'text') {
                        uploadHtml = `
                            <div class="text-container">
                            <!-- TEXT input for generation -->
                            <div class="text-input-section">
                            <label><?php echo __($generateByTextLabel, 'wp-quiz-plugin'); ?></label>
                            <textarea id="kw_text_generation_input" class="swal2-textarea" placeholder="<?php echo __('Type your source text here...', 'wp-quiz-plugin'); ?>"></textarea>
                            </div>
                            <!-- Include / exclude source text -->
                            <div class="source_text_choice">
                            <h4 class="source-text-label">
                                <?php echo esc_html__('Show the source text to learners?', 'wp-quiz-plugin'); ?>
                            </h4>

                            <div class="source_text_options">
                            <label class="source-text-option">
                                <input type="checkbox" id="include_text_source" />
                                <span><?php echo esc_html__('Include the text above the quiz', 'wp-quiz-plugin'); ?></span>
                            </label>

                            <label class="source-text-option">
                                <input type="checkbox" id="exclude_text_source" />
                                <span><?php echo esc_html__('Do not include the text above the quiz', 'wp-quiz-plugin'); ?></span>
                            </label>
                            </div>
                            </div>
                            </div>
                        `;
                        }

                        var tagsHtml = `
                        <label style="display:block; font-weight:bold; margin-bottom:8px; margin-top:20px;">
                        <?php echo wp_kses_post(__('Selected Related Tags below:', 'wp-quiz-plugin')); ?>
                        </label>
                        <div id="kw-checkbox-container-promot" style="margin-top:16px;">
                        ${checkboxOptions.map((option, index) => `
                        <div class="kw-checkbox-wrapper-promot">
                        <input type="checkbox" id="cb-promot-${index}" value="${option}">
                        <label for="cb-promot-${index}">${option}</label>
                        </div>
                        `).join('')}
                        </div>
                        `;

                        // If you already have inputOptions (object like {5:'5',10:'10',...}), reuse its keys:
                        const numberOptions = Object.keys(inputOptions || {}).map(n => parseInt(n, 10)).sort((a,b)=>a-b);

                        const countHtml = `
                        <div class="question-box" id="kw-qn-box">
                            ${numberOptions.map((n,i) => `
                            <div class="kw-checkbox-wrapper-promot">
                                <input type="checkbox" id="qn-${i}" name="qn[]" value="${n}">
                                <label for="qn-${i}">${n}</label>
                            </div>
                            `).join('')}
                        </div>
                        `;

                        // Enforce single selection
                        $(document).off('change', '#kw-qn-box input[type="checkbox"]')
                        .on('change', '#kw-qn-box input[type="checkbox"]', function () {
                        $('#kw-qn-box input[type="checkbox"]').not(this).prop('checked', false);
                        });
                        createSwalPopup({
                            title: '<?php echo esc_js(__('Select Number of Questions', 'wp-quiz-plugin')); ?>',
                            html: countHtml,
                            inputOptions: inputOptions,
                            focusConfirm: false,
                            showCancelButton: true,
                            confirmButtonText: '<?php echo __('Next', 'wp-quiz-plugin'); ?>',
                            cancelButtonText: '<?php echo __('Cancel', 'wp-quiz-plugin'); ?>',
                            preConfirm: (questionCount) => {
                                const checked = Array.from(document.querySelectorAll('#kw-qn-box input[type="checkbox"]:checked'))
      .map(el => el.value);
    if (checked.length !== 1) {
      Swal.showValidationMessage('<?php echo esc_js(__('Please check exactly one number of questions.','wp-quiz-plugin')); ?>');
      return false;
    }
    return parseInt(checked[0], 10); // returns e.g. 10
  }
                        }, swalStyles).then((countResult) => {

                            if (countResult.isConfirmed) {
                                var selectedCount = parseInt(countResult.value);
                                
                                // New popup for learners' age input
                                createSwalPopup({
                                    title: '<?php echo esc_js(__('Enter Learners Age', 'wp-quiz-plugin')); ?>',
                                    input: 'text',
                                    inputPlaceholder: '<?php echo esc_attr(__('Type learners age here...', 'wp-quiz-plugin')); ?>',
                                    showCancelButton: true,
                                    confirmButtonText: '<?php echo esc_js(__('Next', 'wp-quiz-plugin')); ?>',
                                    cancelButtonText: '<?php echo __('Cancel', 'wp-quiz-plugin'); ?>',
                                    preConfirm: (learnerAge) => {
                                        if (!learnerAge) {
                                            Swal.showValidationMessage('<?php echo __('Learners age is required!', 'wp-quiz-plugin'); ?>');
                                        }
                                        return learnerAge;
                                    }
                                }, swalStyles).then((ageResult) => {
                                    if (ageResult.isConfirmed) {
                                        var learnerAge = ageResult.value;

                                        <?php $test = __('Enter your prompt below:', 'wp-quiz-plugin');?>
                                        // Combine all with tagsHtml always last
                                        var modalHtml = baseHtml + uploadHtml + tagsHtml;
                                        createSwalPopup({
                                            title: '<?php echo esc_js(__('Enter your prompt for ChatGPT:', 'wp-quiz-plugin')); ?>',
                                            html:modalHtml,
                                            showCancelButton: true,
                                            confirmButtonText: '<?php echo esc_js(__('Generate', 'wp-quiz-plugin')); ?>',
                                            cancelButtonText: '<?php echo esc_js(__('Cancel', 'wp-quiz-plugin')); ?>',
                                            didOpen: () => {
                                            window.setupFileUploadHandlers();
                                            },
                                            preConfirm: () => {
                                            // safely grab the prompt input if it exists
                                            const promptEl = document.getElementById('kw_user_prompt');
                                            const userPrompt = promptEl ? promptEl.value.trim() : '';
                                            const textEl = document.getElementById('kw_text_generation_input');
                                            window.sourceText = textEl ? textEl.value.trim() : '';
                                            const selectedCheckboxes = Array.from(document.querySelectorAll('#kw-checkbox-container-promot input[type="checkbox"]:checked'))
                                                .map(checkbox => checkbox.value);
                                                if (!userPrompt && window.uploadedImages.length === 0 && window.uploadedPDFs.length === 0 && !window.sourceText) {
                                                Swal.showValidationMessage('<?php echo esc_js(__('Prompt is required!', 'wp-quiz-plugin')); ?>');
                                                return false;
                                                }

                                                return { 
                                                userPrompt, 
                                                selectedCheckboxes,
                                                uploadedImages: window.uploadedImages,
                                                uploadedPDFs: window.uploadedPDFs
                                                };
                                            }
                                }, swalStyles).then((promptResult) => {
                                    if (promptResult.isConfirmed) {
                                        const { userPrompt, selectedCheckboxes } = promptResult.value;
                                        var apiKey = '<?php echo esc_js(get_option('wp_quiz_plugin_openai_api_key')); ?>';
                                        const isAdmin = <?php echo current_user_can('manage_options') ? 'true' : 'false'; ?>;
            
                                        console.log('User Prompt:', userPrompt);
                                        console.log('Selected Checkboxes:', selectedCheckboxes);
                                        let postId = '<?php echo get_the_ID(); ?>';
                                        let postStatus= '<?php echo get_post_status(get_the_ID());?>'

                                        $.fn.updatePostAsDraft(postId, postStatus);

                                        async function sendRequest(retryCount, count, totalCount) {
                                        // No more recursion? hide loader + re‑enable
                                        if (count <= 0) {
                                            $('.kw-loading').hide();
                                            $('#kw_generate-question-btn')
                                            .text('<?php echo esc_js(__('Generate with ChatGPT', 'wp-quiz-plugin')); ?>')
                                            .prop('disabled', false);
                                            return;
                                        }

                                        // Show loader on first pass
                                        if (count === totalCount) {
                                            $('.kw-loading').show();
                                        }

                                        const promptText = generatePromptForType(selectedType, userPrompt, generatedQuestionsList, learnerAge, selectedCheckboxes);
                                        console.log('Generated Prompt Text:', promptText);
                                        const contentList = await window.prepareMultimodalContent(promptText);
                                        if((aiType === 'image' && window.uploadedImages.length > 1)) 
                                        {
                                        maxTokens = 1500;
                                        }
                                        else if(aiType === 'pdf'){
                                        maxTokens = 900;
                                        }
                                        else if(aiType === 'text'){
                                        maxTokens = 700;
                                        }
                                        else {
                                        maxTokens = 300;
                                        }
                                        console.log('Prepared user content:', contentList);
                                        const payload = {
                                            model: "gpt-4o-mini",
                                            messages: [ { type: "message", role: "user", content: contentList } ],
                                            max_tokens: maxTokens ? maxTokens : <?php echo esc_js(get_option('wp_quiz_plugin_openai_max_tokens', 50)); ?>, 
                                            temperature: <?php echo esc_js(get_option('wp_quiz_plugin_openai_temperature', 0.5)); ?>,
                                            store: false
                                        };

                                        $.ajax({
                                            url: 'https://api.openai.com/v1/chat/completions',
                                            method: 'POST',
                                            headers: {
                                            'Authorization': 'Bearer ' + apiKey,
                                            'Content-Type': 'application/json'
                                            },
                                            data: JSON.stringify(payload),
                                            beforeSend: function () {
                                            console.log('→ Sending request to OpenAI', payload);
                                            if (isAdmin) {
                                                $.fn.showAdminPrompt(payload.messages[0].content[0].text);
                                            }
                                            $('#kw_generate-question-btn')
                                                .text('<?php echo esc_js(__('Generating...', 'wp-quiz-plugin')); ?>')
                                                .prop('disabled', true);
                                            },
                                            success: async function (response) {
                                            console.log('← Received response:', response);
                                            try {
                                                var generatedContent = response.choices[0].message.content.trim();
                                                console.log('✔ Generated content:', generatedContent);
                                                //   const data = await resp.json();
                                                //   console.log("GPT-4o-mini Response:", data.choices[0].message.content);
                                                // parse the **response** (not your payload) for output_text / output
                                                handleGeneratedContent(selectedType, generatedContent);

                                                // recurse
                                                await sendRequest(3, count - 1, totalCount);

                                            } catch (parseErr) {
                                                console.error('Error parsing OpenAI response:', parseErr);
                                                Swal.fire(
                                                '<?php echo esc_js(__('Error', 'wp-quiz-plugin')); ?>',
                                                '<?php echo esc_js(__('Could not parse the response. Ensure the AI response follows the expected format.', 'wp-quiz-plugin')); ?>',
                                                'error'
                                                );
                                                $('.kw-loading').hide();
                                                $.fn.highlightPublishButton();
                                                console.log('Changing button:');
                                                $('#kw_generate-question-btn')
                                                .text('<?php echo esc_js(__('Generate with ChatGPT', 'wp-quiz-plugin')); ?>')
                                                .prop('disabled', false);
                                            } finally {
                                            // Ensure Tesseract worker is terminated after all OCR tasks are done
                                            await window.terminateTesseractWorker();
                                                }
                                            },
                                            error(xhr, status, err) {
                                            console.error('API request error:', xhr.responseText);
                                            if (retryCount > 0) {
                                                console.log(`Retrying… attempts left: ${retryCount}`);
                                                setTimeout(() => sendRequest(retryCount - 1, count, totalCount), 2000);
                                            } else {
                                                const serverMsg = xhr.responseJSON?.error?.message || err;
                                                Swal.fire(
                                                '<?php echo esc_js(__('Error', 'wp-quiz-plugin')); ?>',
                                                '<?php echo esc_js(__('Failed to generate question.', 'wp-quiz-plugin')); ?> ' + serverMsg,
                                                'error'
                                                );
                                                $('.kw-loading').hide();
                                                $('#kw_generate-question-btn')
                                                .find('.kw_button-text') // Target only the text span
                                                .text('<?php echo esc_js(__('Generate with ChatGPT', 'wp-quiz-plugin')); ?>')
                                                .prop('disabled', false);
                                            }
                                            }
                                        });
                                        }

                                        sendRequest(3, selectedCount, selectedCount);
                                    }
                                });
                                    }
                                });

                            }
                        });
                    }
                });
            });




                function generatePromptForType(type, userPrompt, generatedQuestionsList, learnerAge, selectedCheckboxes) {
                    // Fetch the custom prompt template from the options table
                    const templates = <?php echo wp_json_encode([
                    'prompt' => get_option('wp_quiz_plugin_custom_prompt_template', ''),
                    'image'   => get_option('wp_quiz_plugin_custom_images_prompt_template', ''),
                    'pdf'     => get_option('wp_quiz_plugin_custom_pdf_prompt_template', ''),
                    'text'    => get_option('wp_quiz_plugin_custom_text_prompt_template', ''),
                    ]); ?>;

                    // Pick the template based on aiType, fallback to default
                    const customPromptTemplate = templates[aiType] ?? templates.prompt;

                    // Generate the previous questions context
                    let previousQuestionsContext = generatedQuestionsList.length 
                        ? `Avoid generating questions similar to these: ${generatedQuestionsList.join("; ")}.` 
                        : '';

                    // Get categories from the page using JavaScript/jQuery
                    let selectedCategories = [];
                    let selectedParentCategory = $('#selected_school_quiz').find(':selected').text().trim();
                    let selectedChildCategory1 = $('#selected_class_quiz').find(':selected').text().trim();
                    let selectedChildCategory2 = $('#selected_subject').find(':selected').text().trim();

                    // Function to check if the category is valid
                    const isValidCategory = (category) => {
                        return category.length > 0 && !(category.match(/^-{3,}$/)); // Not empty and not only hyphens
                    };

                    // Collect valid selected categories
                    if (isValidCategory(selectedParentCategory)) selectedCategories.push(selectedParentCategory);
                    if (isValidCategory(selectedChildCategory1)) selectedCategories.push(selectedChildCategory1);
                    if (isValidCategory(selectedChildCategory2)) selectedCategories.push(selectedChildCategory2);

                    // Format categories for replacement
                    let categoriesContext = selectedCategories.length > 0 
                        ? selectedCategories.join(' > ') 
                        : '';

                    // Add learners' age context
                    let learnersAgeContext = learnerAge ? learnerAge : '';

                    // Add checkbox context
                    let checkboxContext = selectedCheckboxes.length > 0 
                        ? selectedCheckboxes.join(', ') 
                        : '';

                    // Generate the specific question type template
                    let questionTemplate = '';
                    switch (type) {
                        case 'MCQ':
                            questionTemplate = '<?php echo esc_js($mcqPromptTemplate); ?>';
                            break;
                        case 'T/F':
                            questionTemplate = '<?php echo esc_js($tfPromptTemplate); ?>';
                            break;
                        case 'Text':
                            questionTemplate = '<?php echo esc_js($textPromptTemplate); ?>';
                            break;
                        default:
                            questionTemplate = '';
                    }

                    // Append {previousQuestionsContext} and {questionTemplate} to the custom template if missing
                    if (!customPromptTemplate.includes('{previousQuestionsContext}')) {
                        customPromptTemplate += ` {previousQuestionsContext}`;
                    }
                    if (!customPromptTemplate.includes('{questionTemplate}')) {
                        customPromptTemplate += ` {questionTemplate}`;
                    }
                    // Use fallback logic before replacing
                    let defaultCategoryValue = <?php echo json_encode($default_category_value); ?>;
                    let categoriesValue = categoriesContext || defaultCategoryValue;
                    // Replace variables in the custom prompt template
                    let finalPrompt = customPromptTemplate
                        .replace(/{learnerAge}/g, learnersAgeContext)
                        .replace(/{selectedCategories}/g, categoriesValue)
                        .replace(/{userPrompt}/g, userPrompt)
                        .replace(/{selectedCheckboxes}/g, checkboxContext)
                        .replace(/{questionTemplate}/g, questionTemplate)
                        .replace(/{previousQuestionsContext}/g, previousQuestionsContext);

                    // Log the final prompt for debugging
                    console.log("Final Prompt:", finalPrompt);

                    // Return the final prompt
                    return finalPrompt;
                }
                

            // Function to handle dynamic content generation for questions and answers
            function handleGeneratedContent(type, generatedContent) {
                var index = $('#kw-quiz-questions-list').children().length;
                console.log("::quizQuestionsCount",quizQuestionsCount);
                quizQuestionsCount = index +1;
                var answersHtml = '';
                var questionData = {}; // Store extracted data for saving

                try {
                    if (type === 'MCQ') {
                        var questionMatch = generatedContent.match(/Question:\s*(.+?)(?=\s*Answer Options:)/is);
                        var answersMatch = generatedContent.match(/[A]\)\s*(.+?)\s*[B]\)\s*(.+?)\s*[C]\)\s*(.+?)\s*[D]\)\s*(.+?)(?=\s*Correct Answer:|\s*$)/is);
                        var correctAnswerMatch = generatedContent.match(/Correct Answer:\s*([A-D])\)/i);

                        if (!questionMatch || !answersMatch || !correctAnswerMatch) {
                            throw new Error("Could not find the question or correct answer. Please check the AI response format.");
                        }

                        var questionText = questionMatch[1].trim();
                        generatedQuestionsList.push(questionText);
                        var answers = [
                            answersMatch[1].trim(),
                            answersMatch[2].trim(),
                            answersMatch[3].trim(),
                            answersMatch[4].trim()
                        ];
                        answers = answers.map(answer => answer.replace(/,$/, ''));
                        var correctAnswerLetter = correctAnswerMatch[1].trim();

                        answers.forEach((answer, i) => {
                        let isCorrect = correctAnswerLetter === String.fromCharCode(65 + i);
                        answersHtml += `
                            <div class="kw_answer-item kw_column-item">
                                <span class="kw_option-letter">${String.fromCharCode(65 + i)}.</span>
                                <textarea
                                class="kw_answerinputs"
                                placeholder="Answer" 
                                name="quiz_questions[${index}][answers][${i}][text]"
                                required 
                                rows="1"
                                style="
                                    font-family: <?php echo esc_attr($answer_text_font); ?>;
                                    color: <?php echo esc_attr($answer_text_color); ?>;
                                    font-size: <?php echo esc_attr($answer_text_font_size); ?>;
                                    resize: none;
                                    overflow: auto;
                                ">${answer}</textarea>
                                <span class="kw_upload-image-btn">
                                    <!-- SVG icon here -->
                                </span>
                                <label>
                                    <input type="checkbox" name="quiz_questions[${index}][answers][${i}][correct]" ${isCorrect ? 'checked' : ''}>
                                </label>
                                <span class="answer-ribbon ${isCorrect ? 'correct-ribbon' : 'incorrect-ribbon'}">
                                    ${isCorrect ? "<?php echo _x('Correct Answer', 'MCQ Hover', 'wp-quiz-plugin'); ?>" : "<?php echo _x('Incorrect Answer', 'MCQ Hover', 'wp-quiz-plugin'); ?>"}
                                </span>
                                </div>`;
                    });

                    } else if (type === 'T/F') {
                        // Similar processing for T/F type...
                        var questionMatch = generatedContent.match(/Question:\s*(.*?)(?=\s*n?Correct Answer|$)/i);
                        var correctAnswerMatch = generatedContent.match(/Correct Answer:\s*(0|1)/i);
                        if (!questionMatch || !correctAnswerMatch) {
                            throw new Error("Could not find the question or correct answer. Please check the AI response format.");
                        }
                        var questionText = questionMatch[1].trim();
                        generatedQuestionsList.push(questionText);
                        var correctAnswer = parseInt(correctAnswerMatch[1], 10);
                        var trueAnswerChecked = correctAnswer === 1 ? 'checked' : '';
                        var falseAnswerChecked = correctAnswer === 0 ? 'checked' : '';

                        answersHtml = `
                            <div class="kw_answer-item kw_column-item">
                                <input type="text" class="kw_answerinputs" name="quiz_questions[${index}][answers][0][text]" value="True" readonly>
                                <label style="padding-bottom: 0;"><input type="radio" name="quiz_questions[${index}][correct]" value="1" ${trueAnswerChecked}></label>
                            </div>
                            <div class="kw_answer-item kw_column-item">
                                <input type="text" class="kw_answerinputs" name="quiz_questions[${index}][answers][1][text]" value="False" readonly>
                                <label style="padding-bottom: 0;"><input type="radio" name="quiz_questions[${index}][correct]" value="0" ${falseAnswerChecked}></label>
                            </div>`;
                    } else if (type === 'Text') {
                        var questionMatch = generatedContent.match(/Question:\s*(.*?)(?=\s*Correct Answer|$)/i);
                        var correctAnswerTextMatch = generatedContent.match(/Correct Answer:\s*(.+)$/i);
                        if (!questionMatch || !correctAnswerTextMatch) {
                            throw new Error("Could not find the question or correct answer. Please check the AI response format.");
                        }
                        var questionText = questionMatch[1].trim();
                        generatedQuestionsList.push(questionText);
                        var correctAnswerText = correctAnswerTextMatch[1].trim();
                        answersHtml = `
                            <div class="kw_answer-item" style="width: 100%;">
                                <label>Actual Answer</label>
                                <textarea class="kw_text-answer-editor" name="quiz_questions[${index}][answers][0][text]" rows="4" style="width: 100%; font-family: <?php echo esc_attr($answer_text_font); ?>; color: <?php echo esc_attr($answer_text_color); ?>; font-size: <?php echo esc_attr($answer_text_font_size); ?>;">${correctAnswerText}</textarea>
                            </div>`;
                    }

                    // Format answers for saving
                    let formattedAnswers = [];
                    if (type === 'MCQ') {
                        formattedAnswers = answers.map((answer, i) => ({
                            text: answer,
                            correct: correctAnswerLetter === String.fromCharCode(65 + i) ? 1 : 0,
                            image: ""
                        }));
                    } else if (type === 'T/F') {
                        let correctIndex = correctAnswer === 1 ? 0 : 1;
                        formattedAnswers = [
                            { text: 'True', correct: correctIndex === 0 ? 1 : 0 },
                            { text: 'False', correct: correctIndex === 1 ? 1 : 0 }
                        ];
                    } else if (type === 'Text') {
                        formattedAnswers = [{ text: correctAnswerText, correct: 1 }];
                    }

                    questionData = {
                        title: questionText,
                        question_type: type,
                        answers: formattedAnswers
                    };

                    console.log("Formatted Question Data:", questionData);

                    // Save the question via AJAX and then update the DOM
                    saveGeneratedQuestion(questionData).done(function(response) {
                        if (response.success) {
                            console.log('Question saved with ID:', response.data.question_id);
                            // Append the question element with the returned ID as a hidden input
                            $('#kw-quiz-questions-list').append(`
                                <div class="kw-question-item" data-index="${index}">
                                <div class="kw_question-header kw_close-expand kw_toggle-question-btn">
                                <div class="question-content">
                                    <div class="question-badge">
                                    <span class="question-text">Question ${quizQuestionsCount}</span>
                                    </div>
                                    <div class="question-row">
                                    <span class="kw_handle-icon" style="cursor: move;">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="16" height="16">
                                        <rect y="4" width="20" height="2" rx="1"></rect>
                                        <rect y="9" width="20" height="2" rx="1"></rect>
                                        <rect y="14" width="20" height="2" rx="1"></rect>
                                        </svg>
                                    </span>
                                    <div class="question-title">${questionText}</div>
                                    <span class="kw_remove-question-btn kw_ml-2" data-id="${response.data.question_id}">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                        </svg>
                                    </span>
                                    </div>
                                </div>
                                <input type="hidden" name="quiz_questions[${index}][order]" value="${index}">
                                </div>
                                    <div class="kw_question-body">
                                        <select name="quiz_questions[${index}][type]" class="kw_question-type-select">
                                            <option value="MCQ" ${type === 'MCQ' ? 'selected' : ''}><?php echo __('Multiple Choice', 'wp-quiz-plugin'); ?></option>
                                            <option value="T/F" ${type === 'T/F' ? 'selected' : ''}><?php echo __('True/False', 'wp-quiz-plugin'); ?></option>
                                            <option value="Text" ${type === 'Text' ? 'selected' : ''}><?php echo __('Open Text', 'wp-quiz-plugin'); ?></option>
                                        </select>
                                        <!-- Hidden input to store the question ID returned from the auto-save -->
                                        <input type="hidden" name="quiz_questions[${index}][id]" value="${response.data.question_id}">
                                        <textarea class="kw_styled-box" name="quiz_questions[${index}][title]" required>${questionText}</textarea>
                                        <div class="kw_btn-add-img kw_upload-question-image-btn">
                                            <span class="kw_plus-icon">+</span> <?php echo esc_html($upload_question_image_text); ?>
                                            <span class="kw_upload-image-btn-q-svg">
                                                <!-- SVG icon -->
                                            </span>
                                        </div>
                                        <div class="kw_answers-container kw_four-column-container">${answersHtml}</div>
                                    </div>
                                </div>`);
                            // $('.kw-loading').hide();
                            $.fn.highlightPublishButton();
                            $('#kw_generate-question-btn').text('<?php echo esc_js(__('Generate with ChatGPT', 'wp-quiz-plugin')); ?>').prop('disabled', false);
                        } else {
                            console.error('Error saving generated question:', response.message);
                            // $('.kw-loading').hide();
                            $.fn.highlightPublishButton();
                            $('#kw_generate-question-btn').text('<?php echo esc_js(__('Generate with ChatGPT', 'wp-quiz-plugin')); ?>').prop('disabled', false);
                        }
                    }).fail(function(xhr, status, error) {
                        console.error('AJAX error saving generated question:', error);
                        // $('.kw-loading').hide();
                        $.fn.highlightPublishButton();
                        $('#kw_generate-question-btn').text('<?php echo esc_js(__('Generate with ChatGPT', 'wp-quiz-plugin')); ?>').prop('disabled', false);
                    });

                } catch (error) {
                    console.error('Error generating content:', error.message);
                    Swal.fire("Error", "Could not find the question or correct answer. Please check the AI response format.", "error");
                    $('.kw-loading').hide();
                    $.fn.highlightPublishButton();
                    $('#kw_generate-question-btn').text('<?php echo esc_js(__('Generate with ChatGPT', 'wp-quiz-plugin')); ?>').prop('disabled', false);
                }
            }


            function saveGeneratedQuestion(questionData) {
                let quizAutoSaveNonce = '<?php echo esc_js(wp_create_nonce('auto-save-quiz-noce')); ?>';
                let ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
                let postId = '<?php echo get_the_ID(); ?>';

                return $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'save_generated_quiz_question',
                        security: quizAutoSaveNonce,
                        question_data: JSON.stringify(questionData),
                        post_id: postId,
                    }
                });
            }




            // Event delegation for various actions (adding/removing questions, toggling visibility, etc.)
            $(document).on('click', '.kw_toggle-question-btn', function () {
                $(this).closest('.kw-question-item').find('.kw_question-body').toggleClass('active');
            });

            $(document).on('click', '#kw_add-question-btn', function () {
                var index = $('.kw-question-item').length;
                let defaultType = 'MCQ';
                $('#kw-quiz-questions-list').append(`
                    <div class="kw-question-item" data-index="${index}">
                        <div class="kw_question-header kw_close-expand kw_toggle-question-btn">
                        <div class="question-content">
                            <div class="question-badge">
                            <span class="question-text">
                                Question ${index + 1}
                            </span>
                            </div>
                            <div class="question-row">
                            <span class="kw_handle-icon" style="cursor: move;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="16" height="16">
                                <rect y="4" width="20" height="2" rx="1"></rect>
                                <rect y="9" width="20" height="2" rx="1"></rect>
                                <rect y="14" width="20" height="2" rx="1"></rect>
                                </svg>
                            </span>
                            <div class="question-title"><?php echo __('New Question', 'wp-quiz-plugin'); ?></div>
                            <span
                                class="kw_remove-question-btn kw_ml-2"
                                data-id="<?php echo esc_attr( $question['QuesID'] ?? '' ); ?>"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                </svg>
                            </span>
                            </div>
                        </div
                        </div>
                        </div>

                        <div class="kw_question-body">
                           <!-- new checkbox group -->
                             <input
                            type="hidden"
                            class="kw-question-type-hidden"
                            name="quiz_questions[${index}][type]"
                            value="${defaultType}"
                        />
                            <div class="kw-question-type-checkboxes" name="quiz_questions[${index}][type]">
                                <label>
                                    <input type="checkbox"
                                        class="kw-question-type-checkbox"
                                        data-type="MCQ"
                                        ${ defaultType === 'MCQ' ? 'checked' : '' }
                                        />
                                    <?php echo __('Multiple Choice', 'wp-quiz-plugin'); ?>
                                </label>
                                <label>
                                    <input type="checkbox"
                                        class="kw-question-type-checkbox"
                                        data-type="T/F" 
                                        ${ defaultType === 'T/F' ? 'checked' : '' }
                                        />
                                    <?php echo __('True/False', 'wp-quiz-plugin'); ?>
                                </label>
                                <label>
                                    <input type="checkbox"
                                        class="kw-question-type-checkbox"
                                        data-type="Text" 
                                        ${ defaultType === 'Text' ? 'checked' : '' }
                                        />
                                        
                                    <?php echo __('Open Text', 'wp-quiz-plugin'); ?>
                                </label>
                                </div>
                            
                            <textarea 
                                class="kw_styled-box" 
                                placeholder="<?php echo esc_attr(__($type_question_text, 'wp-quiz-plugin')); ?>" 
                                name="quiz_questions[${index}][title]" 
                                required 
                                style=" 
                                    font-family: <?php echo esc_attr($answer_text_font); ?>; 
                                    color: <?php echo esc_attr($answer_text_color); ?>; 
                                    font-size: <?php echo esc_attr($answer_text_font_size); ?>;"></textarea>


                                                        <div class="kw_btn-add-img kw_upload-question-image-btn">
                                                            <span class="kw_plus-icon">+</span> <?php echo esc_html(__($upload_question_image_text, 'wp-quiz-plugin')); ?>
                                                            <span class="kw_upload-image-btn-q-svg">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M160 80l352 0c8.8 0 16 7.2 16 16l0 224c0 8.8-7.2 16-16 16l-21.2 0L388.1 178.9c-4.4-6.8-12-10.9-20.1-10.9s-15.7 4.1-20.1 10.9l-52.2 79.8-12.4-16.9c-4.5-6.2-11.7-9.8-19.4-9.8s-14.8 3.6-19.4 9.8L175.6 336 160 336c-8.8 0-16-7.2-16-16l0-224c0-8.8 7.2-16 16-16zM96 96l0 224c0 35.3 28.7 64 64 64l352 0c35.3 0 64-28.7 64-64l0-224c0-35.3-28.7-64-64-64L160 32c-35.3 0-64 28.7-64 64zM48 120c0-13.3-10.7-24-24-24S0 106.7 0 120L0 344c0 75.1 60.9 136 136 136l320 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-320 0c-48.6 0-88-39.4-88-88l0-224zm208 24a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/></svg>
                                                        </span> 
                                                        </div>
                                                        
                                                        <div class="kw_answers-container kw_four-column-container"></div>
                                                    </div>
                                                </div>
                                            `);


                $('.kw-question-item').each(function(){
                // var $item  = $(this);
                var $newItem = $('#kw-quiz-questions-list .kw-question-item').last();
                var type= $newItem.find('.kw-question-type-hidden').val();
                renderByType(type, $newItem);
                });
                });

            $(document).on('click', '.kw_remove-question-btn', function () {
                var questionItem = $(this).closest('.kw-question-item');
                var questionIndex = questionItem.data('index');
                quizQuestionsCount--;

                if (confirm('<?php echo esc_js(__('Are you sure you want to delete the question?', 'wp-quiz-plugin')); ?>')) {
                    var questionId = $('input[name="quiz_questions[' + questionIndex + '][id]"]').val();
                    if (questionId) {
                        var deletedInput = $('#kw_deleted_questions');
                        var deletedQuestions = deletedInput.val() ? deletedInput.val().split(',') : [];
                        deletedQuestions.push(questionId);
                        deletedInput.val(deletedQuestions.join(','));
                    }

                    questionItem.remove();
                }
            });

            // Helper: run your existing logic
            function renderByType(type, questionItem) {
                var idx         = questionItem.data('index');
                var container   = questionItem.find('.kw_answers-container');
                var fourColWrap = questionItem.find('.kw_four-column-container');

                container.empty();

                if (type === 'MCQ') {
                container.append(generateMCQAnswerFields(idx));
                fourColWrap.css('display','flex');
                }
                else if (type === 'T/F') {
                container.append(generateTFAnswerFields(idx));
                fourColWrap.css('display','flex');
                }
                else if (type === 'Text') {
                container.append(generateTextAnswerField(idx));
                fourColWrap.css('display','block');
                loadTextEditor(idx);
                }
            }

            // Listen to both dropdown *and* the new checkboxes
            $(document).on('change', '.kw_question-type-select, .kw-question-type-checkbox', function(){
                var $this      = $(this);
                var $question  = $this.closest('.kw-question-item');
                var type;

                var $cb   = $(this),
                $body = $cb.closest('.kw_question-body'),
                type  = $cb.data('type');

                // uncheck siblings
                $body.find('.kw-question-type-checkbox')
                .not($cb)
                .prop('checked', false);

                // write the single hidden value
                $body.find('.kw-question-type-hidden').val(type);

                if ($this.hasClass('kw_question-type-select')) {
                type = $this.val();
                // also uncheck all checkboxes to keep UI in sync:
                $question.find('.kw-question-type-checkbox').prop('checked', false);
                // check the matching one:
                $question
                    .find('.kw-question-type-checkbox[data-type="'+ type +'"]')
                    .prop('checked', true);
                }
                else {
                // a checkbox was clicked — uncheck siblings so only one stays active
                $question
                    .find('.kw-question-type-checkbox')
                    .not($this)
                    .prop('checked', false);

                type = $this.data('type');
                // keep the <select> in sync too (if you’re still using it)
                $question
                    .find('.kw_question-type-select')
                    .val(type);
                }

                // finally render the answers
                renderByType(type, $question);
            });
             // Check for "Text" on page load and remove the class if it's selected
            $('.kw_question-type-select').each(function() {
                if ($(this).val() === 'Text') {
                    var container = $(this).closest('.kw-question-item').find('.kw_answers-container');
                    container.removeClass('kw_four-column-container');
                }
            });


            function generateMCQAnswerFields(questionIndex) {
                return `
                    <div class="kw_answer-item kw_column-item">
                        <span class="kw_option-letter" style="padding-bottom: 0;">A.</span>
                        <input type="text" style="font-family: <?php echo esc_attr($answer_text_font); ?>; color: <?php echo esc_attr($answer_text_color); ?>; font-size: <?php echo esc_attr($answer_text_font_size); ?>;" class="kw_answerinputs" placeholder="<?php echo esc_attr(__($add_answer_text, 'wp-quiz-plugin')); ?>" name="quiz_questions[${questionIndex}][answers][0][text]" required>
                        <label style="padding-bottom: 0;"><input type="checkbox" name="quiz_questions[${questionIndex}][answers][0][correct]"></label>
                        <span class="kw_upload-image-btn" style="padding-bottom: 0;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M160 80l352 0c8.8 0 16 7.2 16 16l0 224c0 8.8-7.2 16-16 16l-21.2 0L388.1 178.9c-4.4-6.8-12-10.9-20.1-10.9s-15.7 4.1-20.1 10.9l-52.2 79.8-12.4-16.9c-4.5-6.2-11.7-9.8-19.4-9.8s-14.8 3.6-19.4 9.8L175.6 336 160 336c-8.8 0-16-7.2-16-16l0-224c0-8.8 7.2-16 16-16zM96 96l0 224c0 35.3 28.7 64 64 64l352 0c35.3 0 64-28.7 64-64l0-224c0-35.3-28.7-64-64-64L160 32c-35.3 0-64 28.7-64 64zM48 120c0-13.3-10.7-24-24-24S0 106.7 0 120L0 344c0 75.1 60.9 136 136 136l320 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-320 0c-48.6 0-88-39.4-88-88l0-224zm208 24a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/></svg>
                        </span>
                    </div>
                    <div class="kw_answer-item kw_column-item">
                        <span class="kw_option-letter" style="padding-bottom: 0;">B.</span>
                        <input type="text" class="kw_answerinputs" style="font-family: <?php echo esc_attr($answer_text_font); ?>; color: <?php echo esc_attr($answer_text_color); ?>; font-size: <?php echo esc_attr($answer_text_font_size); ?>;" placeholder="<?php echo esc_attr(__($add_answer_text, 'wp-quiz-plugin')); ?>" name="quiz_questions[${questionIndex}][answers][1][text]" required>
                        <label style="padding-bottom: 0;"><input type="checkbox" name="quiz_questions[${questionIndex}][answers][1][correct]"></label>
                        <span class="kw_upload-image-btn" style="padding-bottom: 0;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M160 80l352 0c8.8 0 16 7.2 16 16l0 224c0 8.8-7.2 16-16 16l-21.2 0L388.1 178.9c-4.4-6.8-12-10.9-20.1-10.9s-15.7 4.1-20.1 10.9l-52.2 79.8-12.4-16.9c-4.5-6.2-11.7-9.8-19.4-9.8s-14.8 3.6-19.4 9.8L175.6 336 160 336c-8.8 0-16-7.2-16-16l0-224c0-8.8 7.2-16 16-16zM96 96l0 224c0 35.3 28.7 64 64 64l352 0c35.3 0 64-28.7 64-64l0-224c0-35.3-28.7-64-64-64L160 32c-35.3 0-64 28.7-64 64zM48 120c0-13.3-10.7-24-24-24S0 106.7 0 120L0 344c0 75.1 60.9 136 136 136l320 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-320 0c-48.6 0-88-39.4-88-88l0-224zm208 24a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/></svg>
                        </span>
                    </div>
                    <div class="kw_btn-add-option kw_add-more-answers-btn">
                        <span class="kw_plus-icon">+</span> <?php echo esc_html($add_option_text); ?>
                    </div>
                `;
            }


            function generateTFAnswerFields(questionIndex) {
                return `
                    <div class="kw_answer-item kw_column-item">
                        <input type="text" class="kw_answerinputs" name="quiz_questions[${questionIndex}][answers][0][text]" value="<?php echo esc_attr(__('True', 'wp-quiz-plugin')); ?>" readonly>
                        <label style="padding-bottom: 0;"><input type="radio" name="quiz_questions[${questionIndex}][correct]" value="0"></label>
                    </div>
                    <div class="kw_answer-item kw_column-item">
                        <input type="text" class="kw_answerinputs" name="quiz_questions[${questionIndex}][answers][1][text]" value="<?php echo esc_attr(__('False', 'wp-quiz-plugin')); ?>" readonly>
                        <label style="padding-bottom: 0;"><input type="radio" name="quiz_questions[${questionIndex}][correct]" value="1"></label>
                    </div>`;
            }

            function generateTextAnswerField(questionIndex) {
                return `
                    <div class="kw_answer-item">
                        <div class="kw_text-answer-editor" data-editor-id="editor-${questionIndex}"></div>
                    </div>`;
            }

            function loadTextEditor(questionIndex) {
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'add_text_editor',
                        editor_id: 'editor-' + questionIndex,
                        question_index: questionIndex,
                    },
                    success: function (response) {
                        $(`[data-editor-id="editor-${questionIndex}"]`).replaceWith(response);
                    }
                });
            }

            // Add more answers for MCQ questions
            $(document).on('click', '.kw_add-more-answers-btn', function () {
            var container = $(this).closest('.kw_answers-container');
            var questionIndex = $(this).closest('.kw-question-item').data('index');
            var answerCount = container.find('.kw_answer-item').length;

            if (answerCount < 4) { // Ensure limit of 4 answers
                // Compute letters client‐side
                var optionLetters = ['A', 'B', 'C', 'D'];

                var newAnswer = `
                    <div class="kw_answer-item kw_column-item">
                        <span class="kw_option-letter" style="padding-bottom: 0;">
                            ${ optionLetters[answerCount] }.
                        </span>
                        <input
                        type="text"
                        style="
                            font-family: <?php echo esc_attr($answer_text_font); ?>;
                            color:       <?php echo esc_attr($answer_text_color); ?>;
                            font-size:   <?php echo esc_attr($answer_text_font_size); ?>;
                        "
                        class="kw_answerinputs"
                        placeholder="<?php echo esc_attr(__($add_answer_text, 'wp-quiz-plugin')); ?>"
                        name="quiz_questions[${questionIndex}][answers][${answerCount}][text]"
                        required
                        >
                        <label style="padding-bottom: 0;">
                        <input
                            type="checkbox"
                            name="quiz_questions[${questionIndex}][answers][${answerCount}][correct]"
                        >
                        </label>
                        <span class="kw_upload-image-btn" style="padding-bottom: 0;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M160 80l352 0c8.8 0 16 7.2 16 16l0 224c0 8.8-7.2 16-16 16l-21.2 0L388.1 178.9c-4.4-6.8-12-10.9-20.1-10.9s-15.7 4.1-20.1 10.9l-52.2 79.8-12.4-16.9c-4.5-6.2-11.7-9.8-19.4-9.8s-14.8 3.6-19.4 9.8L175.6 336 160 336c-8.8 0-16-7.2-16-16l0-224c0-8.8-7.2-16 16-16zM96 96l0 224c0 35.3 28.7 64 64 64l352 0c35.3 0 64-28.7 64-64l0-224c0-35.3-28.7-64-64-64L160 32c-35.3 0-64 28.7-64 64zM48 120c0-13.3-10.7-24-24-24S0 106.7 0 120L0 344c0 75.1 60.9 136 136 136l320 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-320 0c-48.6 0-88-39.4-88-88l0-224zm208 24a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z"/></svg>
                        </span>
                    </div>
                `;

                // Insert in the correct position
                if (answerCount === 2) {
                    $(newAnswer).insertAfter(container.children().eq(1));
                } else if (answerCount === 3) {
                    $(newAnswer).insertAfter(container.children().eq(2));
                } else {
                    container.append(newAnswer);
                }
            } else {
                alert('You can only add up to 4 answers.');
            }
        });

        });
    </script>


<?php
}

// Hook to save meta fields when the post is saved
function wp_quiz_plugin_save_image_size_settings($post_id) {
    // Verify if this is an autosave, if the user has permission, and if the post type is 'quizzes'
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (get_post_type($post_id) !== 'quizzes') {
        return;
    }

    // Sanitize and save the question image width and height
    if (isset($_POST['question_image_width'])) {
        $question_image_width = intval($_POST['question_image_width']);
        update_post_meta($post_id, 'question_image_width', $question_image_width);
    }

    if (isset($_POST['question_image_height'])) {
        $question_image_height = intval($_POST['question_image_height']);
        update_post_meta($post_id, 'question_image_height', $question_image_height);
    }

    // Sanitize and save the answer image width and height
    if (isset($_POST['answer_image_width'])) {
        $answer_image_width = intval($_POST['answer_image_width']);
        update_post_meta($post_id, 'answer_image_width', $answer_image_width);
    }

    if (isset($_POST['answer_image_height'])) {
        $answer_image_height = intval($_POST['answer_image_height']);
        update_post_meta($post_id, 'answer_image_height', $answer_image_height);
    }
}
add_action('save_post', 'wp_quiz_plugin_save_image_size_settings');


// Save Meta Box Content to Custom Table
function save_quiz_questions_meta($post_id) {
    global $wpdb;

    // Ensure $question_image is defined
    $question_image = '';

    if (!isset($_POST['quiz_questions_meta_nonce']) || !wp_verify_nonce($_POST['quiz_questions_meta_nonce'], 'save_quiz_questions_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $quiz_id = intval($post_id);
    $table_name = $wpdb->prefix . 'quiz_questions';

    // Delete existing questions marked for deletion
    if (isset($_POST['deleted_questions']) && !empty($_POST['deleted_questions'])) {
        $deleted_questions = explode(',', sanitize_text_field($_POST['deleted_questions']));
        foreach ($deleted_questions as $question_id) {
            $wpdb->delete($table_name, ['QuesID' => intval($question_id)]);
        }
    }

    // Insert or update questions
    if (isset($_POST['quiz_questions']) && is_array($_POST['quiz_questions'])) {
        foreach ($_POST['quiz_questions'] as $question) {
            $question_title = wp_unslash(sanitize_text_field($question['title']));
            $question_image = isset($question['title_image']) ? esc_url_raw($question['title_image']) : ''; // Save title image URL
            
            // Log the value of $question_image
            $question_type = sanitize_text_field($question['type']);
            $answers = isset($question['answers']) ? $question['answers'] : [];
            $question_order = isset($question['order']) ? intval($question['order']) : 0;  // Get the order
            error_log('question_order: ' . print_r($question_order));
            error_log(":::::::::qeusionts ansers" .  print_r($answers,true));

            // Prepare the answers as JSON to store in the custom table
            $prepared_answers = [];

            if ($question_type === 'T/F') {
                // For True/False questions, determine which radio button is checked
                $correct_index = isset($question['correct']) ? intval($question['correct']) : -1;

                // True and False answers with correct selection
                $prepared_answers[] = [
                    'text' => __('True', 'wp-quiz-plugin'),
                    'correct' => ($correct_index === 0) ? 1 : 0,
                ];
                $prepared_answers[] = [
                    'text' => __('False', 'wp-quiz-plugin'),
                    'correct' => ($correct_index === 1) ? 1 : 0,
                ];
            } else {
                foreach ($answers as $answer) {
                    error_log(":::::::::only asnwer" .  print_r($answer['text'],true));
                    $prepared_answers[] = [
                        'text' => ($question_type === 'Text') ? wp_kses_post($answer['text']) : sanitize_text_field($answer['text']),
                        'correct' => ($question_type === 'Text') ? 1 : (isset($answer['correct']) ? 1 : 0),  // Set 'correct' to 1 (true) by default for 'Text' type
                        'image' => isset($answer['image']) ? esc_url_raw($answer['image']) : '' // Add image URL
                    ];
                }
            }

            $answers_json = wp_json_encode($prepared_answers);

            // Check if the question has an ID (existing question)
            if (!empty($question['id'])) {
                // Update existing question
                $wpdb->update(
                    $table_name,
                    [
                        'Title' => $question_title,
                        'TitleImage' => $question_image,  // Save Title Image
                        'Answer' => $answers_json,
                        'QuestionType' => $question_type,
                        'Order' => $question_order,  // Set order as needed
                    ],
                    ['QuesID' => intval($question['id'])],
                    ['%s', '%s', '%s', '%s', '%d'],
                    ['%d']
                );
            } else {
                // Insert new question
                $wpdb->insert(
                    $table_name,
                    [
                        'QuizID' => $quiz_id,
                        'Title' => $question_title,
                        'TitleImage' => $question_image,  // Save Title Image
                        'Answer' => $answers_json,
                        'QuestionType' => $question_type,
                        'Order' => 0,  // Set order as needed
                    ],
                    [
                        '%d',   // QuizID as integer
                        '%s',   // Title as string
                        '%s',   // Title Image as string
                        '%s',   // Answer as JSON string
                        '%s',   // QuestionType as string
                        '%d'    // Order as integer
                    ]
                );
            }
        }
    }
    
}
add_action('save_post', 'save_quiz_questions_meta');

// AJAX Handler to Add Text Editor
function add_text_editor_callback() {
    if (isset($_POST['editor_id']) && isset($_POST['question_index'])) {
        $editor_id = sanitize_text_field($_POST['editor_id']);
        $question_index = intval($_POST['question_index']);

        ob_start();
        $open_ended_question_lable = get_option('wp_quiz_plugin_open_text_area_label_text', 'Actual Answer');
        echo '<label style="color: #646970" for="quiz_questions_' . $index . '_answers_0_text">' . __($open_ended_question_lable, 'wp-quiz-plugin') . '</label>';
        wp_editor('', 'quiz_questions_' . $question_index . '_answers_0_text', array(
            'textarea_name' => 'quiz_questions[' . $question_index . '][answers][0][text]',
            'media_buttons' => false,
            'textarea_rows' => 5,
            'teeny' => true,
            'quicktags'     => false,
            'tinymce'       => [        // configure TinyMCE if you want to prune buttons
            'toolbar1' => 'bold,italic,underline', 
            'toolbar2' => '',
            ],
            'textarea_rows' => 5,
        ));
        $editor_html = ob_get_clean();

        echo $editor_html;
    }
    wp_die();
}
add_action('wp_ajax_add_text_editor', 'add_text_editor_callback');

// AJAX Handler to Upload Image for MCQ Answer
function upload_mcq_answer_image_callback() {
    // Check nonce for security
    check_ajax_referer('upload_mcq_answer_image', 'security');

    // Validate file upload
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }

    $uploadedfile = $_FILES['file'];
    $upload_overrides = array('test_form' => false);

    // Handle the file upload
    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

    if ($movefile && !isset($movefile['error'])) {
        // File is uploaded successfully, return the URL
        wp_send_json_success(['url' => $movefile['url']]);
    } else {
        // There was an error uploading the file
        wp_send_json_error(['error' => $movefile['error']]);
    }

    wp_die(); // End AJAX handler
}
add_action('wp_ajax_upload_mcq_answer_image', 'upload_mcq_answer_image_callback');


add_action('wp_trash_post', function ($post_id) {
    // Get the post object
    $post = get_post($post_id);

    if (!$post) {
        return;
    }

    // Check if the post type is 'quizzes' or 'crossword'
    if (in_array($post->post_type, ['quizzes', 'crossword'])) {
        // Get the current user's role
        $current_user = wp_get_current_user();

        // Check if the user is not an admin
        if (!in_array('administrator', $current_user->roles)) {
            // Reassign the post to the admin user
            $admin_user = get_users(['role' => 'administrator', 'number' => 1]);

            if (!empty($admin_user)) {
                $admin_user_id = $admin_user[0]->ID;

                // Update the post author to admin
                wp_update_post([
                    'ID'          => $post_id,
                    'post_author' => $admin_user_id,
                ]);

                // Prevent the post from being trashed by resetting its status
                wp_update_post([
                    'ID'          => $post_id,
                    'post_status' => 'draft', // Or any other status you want to set
                ]);

                // Redirect back to the same page
                $referer = wp_get_referer();
                if ($referer) {
                    wp_safe_redirect($referer);
                    exit;
                }
            }
        }
    }
});

function exclude_private_quizzes_in_divi($query) {
    // Check if this is a frontend query and not in the admin area
    if (!is_admin() && $query->is_main_query() && isset($query->query_vars['quiz_category'])) {
        // Exclude quizzes with 'quiz_listing_visibility_status' set to 'private'
        $meta_query = [
            'relation' => 'OR',
            [
                'key' => 'quiz_listing_visibility_status',
                'compare' => 'NOT EXISTS', // Include quizzes without the meta key
            ],
            [
                'key' => 'quiz_listing_visibility_status',
                'value' => 'private',
                'compare' => '!=', // Exclude quizzes with the meta value 'private'
            ]
        ];

        $query->set('meta_query', $meta_query);
    }
}
add_action('pre_get_posts', 'exclude_private_quizzes_in_divi');
