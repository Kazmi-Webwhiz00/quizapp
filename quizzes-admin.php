<?php

include_once plugin_dir_path(__FILE__) . 'utils/constants.php'; 

if (!defined('ABSPATH')) exit; 

include_once plugin_dir_path(__FILE__) . 'kw-quiz-shortcode-settings.php';
include_once plugin_dir_path(__FILE__) . './custom-meta-boxes/quiz_seo_text_admin_settings.php';


function wp_quiz_plugin_enqueue_admin_scripts($hook_suffix) {
    if ($hook_suffix !== 'quizzes_page_wp-quiz-settings') return;

    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    wp_add_inline_script('wp-color-picker', "jQuery(document).ready(function($) { $('.wp-color-picker-field').wpColorPicker(); });");
    
}
add_action('admin_enqueue_scripts', 'wp_quiz_plugin_enqueue_admin_scripts');

function wp_quiz_plugin_add_quizzes_settings_page() {
    if (!current_user_can('manage_options')) return;

    // Fetch the dynamic setting for 'Quizzes Settings' menu text
    $quizzes_settings_menu_text = get_option('wp_quiz_plugin_menu_settings_text', __('Quizzes Settings', 'wp-quiz-plugin'));

    add_submenu_page(
        'edit.php?post_type=' . get_option('wp_quiz_plugin_quizzes_slug', 'quizzes'),
        __($quizzes_settings_menu_text, 'wp-quiz-plugin'), // Page title dynamically set
        __($quizzes_settings_menu_text, 'wp-quiz-plugin'), // Menu title dynamically set
        'manage_options', 
        'wp-quiz-settings', 
        'wp_quiz_plugin_render_quizzes_settings_page'
    );

    if (isset($_POST['wp_quiz_plugin_quizzes_slug'])) flush_rewrite_rules();
}
add_action('admin_menu', 'wp_quiz_plugin_add_quizzes_settings_page');


function wp_quiz_plugin_render_quizzes_settings_page() { ?>
    <div class="wrap">
        <h1><?php esc_html_e('Quizzes Settings', 'wp_quiz_plugin'); ?></h1>
        
        <!-- Tab Navigation -->
        <h2 class="nav-tab-wrapper">
            <a href="#general-settings" class="nav-tab nav-tab-active"><?php esc_html_e('General Settings', 'wp_quiz_plugin'); ?></a>
            <a href="#style-settings" class="nav-tab"><?php esc_html_e('Admin Style Settings', 'wp_quiz_plugin'); ?></a>
            <a href="#frontend-style-settings" class="nav-tab"><?php esc_html_e('Frontend Style Settings', 'wp_quiz_plugin'); ?></a>
            <a href="#strings-text-settings" class="nav-tab"><?php esc_html_e('Admin Strings Text', 'wp_quiz_plugin'); ?></a> 
            <a href="#pdf-strings-text-settings" class="nav-tab"><?php esc_html_e('PDF Strings Text', 'wp_quiz_plugin'); ?></a> 
            <a href="#pdf-style-settings" class="nav-tab"><?php esc_html_e('PDF Style Settings', 'wp_quiz_plugin'); ?></a> 
            <a href="#kw-quiz-shortcode-settings" class="nav-tab"><?php esc_html_e('Shortcode Settings', 'wp_quiz_plugin'); ?></a> 
        </h2>

        <!-- General Settings Tab Content -->
        <div id="general-settings" class="tab-content" style="display: block;">
            <form method="post" action="options.php">
                <?php 
                settings_fields('wp_quiz_plugin_general_settings');  // Updated settings group
                do_settings_sections('wp_quiz_plugin_general'); 
                submit_button(); 
                ?>
            </form>
        </div>

        <!-- Style Settings Tab Content -->
        <div id="style-settings" class="tab-content" style="display: none;">
            <form method="post" action="options.php">
                <?php 
                settings_fields('wp_quiz_plugin_quizzes_settings'); 
                do_settings_sections('wp_quiz_plugin'); 
                submit_button(); 
                ?>
            </form>
        </div>

        <!-- Frontend Style Settings Tab Content -->
        <div id="frontend-style-settings" class="tab-content" style="display: none;"> <!-- New section -->
            <form method="post" action="options.php">
                <?php 
                settings_fields('wp_quiz_plugin_frontend_styles_settings');  // New settings group
                do_settings_sections('wp_quiz_plugin_frontend');  // New settings page identifier
                submit_button(); 
                ?>
            </form>
        </div>

        <!-- Strings Text Tab Content -->
        <div id="strings-text-settings" class="tab-content" style="display: none;">
            <form method="post" action="options.php">
                <?php 
                settings_fields('wp_quiz_plugin_strings_text_settings');  // New settings group
                do_settings_sections('wp_quiz_plugin_strings_text');  // New settings page identifier
                submit_button(); 
                ?>
            </form>
        </div>

        <!-- PDF Strings -->
        <div id="pdf-strings-text-settings" class="tab-content" style="display: none;">
            <form method="post" action="options.php">
                <?php 
                settings_fields('wp_quiz_plugin_pdf_strings_text_settings');  // New settings group
                do_settings_sections('wp_quiz_plugin_pdf_strings_text');  // New settings page identifier
                submit_button(); 
                ?>
            </form>
        </div>

        <!-- Pdf Style Settings -->
        <div id="pdf-style-settings" class="tab-content" style="display: none;">
            <form method="post" action="options.php">
                <?php 
                settings_fields('wp_quiz_plugin_pdf_style_settings');  // New settings group
                do_settings_sections('wp_quiz_plugin_pdf_style');  // New settings page identifier
                submit_button(); 
                ?>
            </form>
        </div>

        <!-- Shortcode Settings -->
        <div id="kw-quiz-shortcode-settings" class="tab-content" style="display: none;">
            <form method="post" action="options.php">
                <?php 
                settings_fields('kw_quiz_shortcode_settings');  // New settings group
                do_settings_sections('kw_quiz_shortcode_styles');  // New settings page identifier
                submit_button(); 
                ?>
            </form>
        </div>


    </div>

    <!-- JavaScript to handle tab switching -->
    <script>
    jQuery(document).ready(function($) {
        $('.nav-tab').click(function(e) {
            e.preventDefault();
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            $('.tab-content').hide();
            $($(this).attr('href')).show();
        });
    });
    </script>
<?php }

function wp_quiz_plugin_strings_text_settings_init() {
    // Add settings section once before the loop
    add_settings_section(
        'wp_quiz_plugin_strings_text_section',
        __('Customize Button and Placeholder Texts', 'wp_quiz_plugin'),
        null,
        'wp_quiz_plugin_strings_text'
    );

    // Define settings fields array with default values
    $settings_fields = [
        'wp_quiz_plugin_download_quiz_text' => [
            'label' => 'Download Quiz Button Text',
            'default' => __('Download Quiz', 'wp-quiz-plugin'),
        ],
        'wp_quiz_plugin_download_answer_key_text' => [
            'label' => 'Download Answer Key Button Text',
            'default' => __('Download Answer Key', 'wp-quiz-plugin'),
        ],
        'wp_quiz_plugin_add_question_text' => [
            'label' => 'Add New Question Button Text',
            'default' => __('Add New Question', 'wp-quiz-plugin'),
        ],
        'wp_quiz_plugin_add_ai_question_text' => [
            'label' => 'Add New Question With AI Button Text',
            'default' => __('Add New Question With AI', 'wp-quiz-plugin'),
        ],
        'wp_quiz_plugin_upload_question_image_text' => [
            'label' => 'Upload Question Image Text',
            'default' => __('Upload Question Image', 'wp-quiz-plugin'),
        ],
        'wp_quiz_plugin_add_option_text' => [
            'label' => 'Add New Option Text',
            'default' => __('Add New Option', 'wp-quiz-plugin'),
        ],
        'wp_quiz_plugin_type_question_text' => [
            'label' => 'Type Question Here Placeholder',
            'default' => __('Type Question Here', 'wp-quiz-plugin'),
        ],
        'wp_quiz_plugin_add_answer_text' => [
            'label' => 'Click to Add Answer Placeholder',
            'default' => __('Click to Add Answer', 'wp-quiz-plugin'),
        ],
        'wp_quiz_plugin_menu_quizzes_text' => [
            'label' => 'Quizzes Menu Text',
            'default' => __('Quizzes', 'wp-quiz-plugin'),
        ],
        'wp_quiz_plugin_menu_all_quizzes_text' => [
            'label' => 'All Quizzes Menu Text',
            'default' => __('All Quizzes', 'wp-quiz-plugin'),
        ],
        'wp_quiz_plugin_menu_add_new_text' => [
            'label' => 'Add New Menu Text',
            'default' => __('Add New', 'wp-quiz-plugin'),
        ],
        'wp_quiz_plugin_menu_settings_text' => [
            'label' => 'Quizzes Settings Menu Text',
            'default' => __('Quizzes Settings', 'wp-quiz-plugin'),
        ],
        'wp_quiz_plugin_menu_submissions_text' => [
            'label' => 'Submissions Menu Text',
            'default' => __('Submissions', 'wp-quiz-plugin'),
        ],
        'wp_quiz_plugin_menu_notifications_text' => [
            'label' => 'Notifications Menu Text',
            'default' => __('Notifications', 'wp-quiz-plugin'),
        ],
        'wp_quiz_plugin_category_lable_text' => [
            'label' => 'Quiz Category Label Text',
            'default' => __('Quiz Category', 'wp-quiz-plugin'),
        ],
        'wp_quiz_plugin_category_select_School_text' => [
            'label' => 'Quiz Category Select School Label Text',
            'default' => __('Select School', 'wp-quiz-plugin'),
        ],
        'wp_quiz_plugin_category_select_Subject_text' => [
            'label' => 'Quiz Category Select Subject Label Text',
            'default' => __('----------', 'wp-quiz-plugin'),
        ],
        'wp_quiz_plugin_category_select_class_text' => [
            'label' => 'Quiz Category Select Class Label Text',
            'default' => __('Select Class', 'wp-quiz-plugin'),
        ],
        'wp_quiz_plugin_question_image_label_text' => [
            'label' => 'Question Image Sizing Text',
            'default' => __('Set Question Image Size:', 'wp-quiz-plugin'),
        ],
        'wp_quiz_plugin_answer_image_label_text' => [
            'label' => 'Answer Image Sizing Text',
            'default' => __('Set Answer Image Size:', 'wp-quiz-plugin'),
        ],
        'wp_quiz_plugin_image_width_label_text' => [
            'label' => 'Image Width Text',
            'default' => __('Width:', 'wp-quiz-plugin'),
        ],
        'wp_quiz_plugin_image_height_label_text' => [
            'label' => 'Image Height Text',
            'default' => __('Height:', 'wp-quiz-plugin'),
        ],
        'wp_quiz_plugin_image_height_settings_notofication' => [
            'label' => 'Image Height Setting Notification Text',
            'default' => __('Image Height Setting Notification Text', 'wp-quiz-plugin'),
        ],
        'wp_quiz_plugin_open_text_area_place_holder_text' => [
            'label' => 'Open text area place holder',
            'default' => __('Type your answer here...', 'wp-quiz-plugin'),
        ],
        'wp_quiz_plugin_open_text_area_label_text' => [
            'label' => 'Open text area label',
            'default' => __('Actual Answer', 'wp-quiz-plugin'),
        ],
    ];
    

    // Register settings and add settings fields
    foreach ($settings_fields as $option_name => $info) {
        // Register the setting
        register_setting('wp_quiz_plugin_strings_text_settings', $option_name);

        // Add the settings field
        add_settings_field(
            $option_name,
            __($info['label'], 'wp_quiz_plugin'),
            'wp_quiz_plugin_strings_text_callback',
            'wp_quiz_plugin_strings_text',
            'wp_quiz_plugin_strings_text_section',
            array(
                'option_name' => $option_name,
                'default_value' => $info['default'] // Pass the default value from the array
            )
        );
    }
}
add_action('admin_init', 'wp_quiz_plugin_strings_text_settings_init');

function wp_quiz_plugin_strings_text_callback($args) {
    // Retrieve the arguments
    $option_name = isset($args['option_name']) ? $args['option_name'] : '';
    $default_value = isset($args['default_value']) ? $args['default_value'] : '';

    // Use get_option() to retrieve the option value or the default if not set
    $text = get_option($option_name, $default_value);

    // Output the input field, using the option_name for both id and name attributes
    echo '<input type="text" id="' . esc_attr($option_name) . '" name="' . esc_attr($option_name) . '" value="' . esc_attr($text) . '" />';
}


function wp_quiz_plugin_pdf_strings_text_settings_init() {
    // Register settings for all the PDF strings
    register_setting('wp_quiz_plugin_pdf_strings_text_settings', 'wp_quiz_plugin_pdf_download_quiz_title');
    register_setting('wp_quiz_plugin_pdf_strings_text_settings', 'wp_quiz_plugin_pdf_student_name');
    register_setting('wp_quiz_plugin_pdf_strings_text_settings', 'wp_quiz_plugin_pdf_date_text');
    register_setting('wp_quiz_plugin_pdf_strings_text_settings', 'wp_quiz_plugin_pdf_answer_key_title');
    register_setting('wp_quiz_plugin_pdf_strings_text_settings', 'wp_quiz_plugin_pdf_correct_answer_header');
    register_setting('wp_quiz_plugin_pdf_strings_text_settings', 'wp_quiz_plugin_pdf_question_header');
    register_setting('wp_quiz_plugin_pdf_strings_text_settings', 'wp_quiz_plugin_pdf_generated_by_text');

    // Add a new section for the PDF strings
    add_settings_section(
        'wp_quiz_plugin_pdf_strings_text_section',
        __('Customize PDF Strings Texts', 'wp_quiz_plugin'),
        null,
        'wp_quiz_plugin_pdf_strings_text'
    );

    // Add settings fields
    add_settings_field('wp_quiz_plugin_pdf_download_quiz_title', __('Quiz PDF Title', 'wp_quiz_plugin'), 'wp_quiz_plugin_pdf_download_quiz_title_callback', 'wp_quiz_plugin_pdf_strings_text', 'wp_quiz_plugin_pdf_strings_text_section');
    
    add_settings_field('wp_quiz_plugin_pdf_student_name', __('Student Name Field Text', 'wp_quiz_plugin'), 'wp_quiz_plugin_pdf_student_name_callback', 'wp_quiz_plugin_pdf_strings_text', 'wp_quiz_plugin_pdf_strings_text_section');
    
    add_settings_field('wp_quiz_plugin_pdf_date_text', __('Date Field Text', 'wp_quiz_plugin'), 'wp_quiz_plugin_pdf_date_text_callback', 'wp_quiz_plugin_pdf_strings_text', 'wp_quiz_plugin_pdf_strings_text_section');
    
    add_settings_field('wp_quiz_plugin_pdf_answer_key_title', __('Answer Key PDF Title', 'wp_quiz_plugin'), 'wp_quiz_plugin_pdf_answer_key_title_callback', 'wp_quiz_plugin_pdf_strings_text', 'wp_quiz_plugin_pdf_strings_text_section');
    
    add_settings_field('wp_quiz_plugin_pdf_correct_answer_header', __('Correct Answer Header Text', 'wp_quiz_plugin'), 'wp_quiz_plugin_pdf_correct_answer_header_callback', 'wp_quiz_plugin_pdf_strings_text', 'wp_quiz_plugin_pdf_strings_text_section');
    
    add_settings_field('wp_quiz_plugin_pdf_question_header', __('Question Header Text', 'wp_quiz_plugin'), 'wp_quiz_plugin_pdf_question_header_callback', 'wp_quiz_plugin_pdf_strings_text', 'wp_quiz_plugin_pdf_strings_text_section');
    
    add_settings_field('wp_quiz_plugin_pdf_generated_by_text', __('Generated By Text', 'wp_quiz_plugin'), 'wp_quiz_plugin_pdf_generated_by_text_callback', 'wp_quiz_plugin_pdf_strings_text', 'wp_quiz_plugin_pdf_strings_text_section');

}
add_action('admin_init', 'wp_quiz_plugin_pdf_strings_text_settings_init');

// Callback function for Quiz PDF Title
function wp_quiz_plugin_pdf_download_quiz_title_callback() {
    $title = get_option('wp_quiz_plugin_pdf_download_quiz_title', 'Quiz Download');
    echo '<input type="text" id="wp_quiz_plugin_pdf_download_quiz_title" name="wp_quiz_plugin_pdf_download_quiz_title" value="' . esc_attr($title) . '" class="regular-text">';
}

// Callback function for Student Name Field Text
function wp_quiz_plugin_pdf_student_name_callback() {
    $student_name = get_option('wp_quiz_plugin_pdf_student_name', 'Student Name:');
    echo '<input type="text" id="wp_quiz_plugin_pdf_student_name" name="wp_quiz_plugin_pdf_student_name" value="' . esc_attr($student_name) . '" class="regular-text">';
}

// Callback function for Date Field Text
function wp_quiz_plugin_pdf_date_text_callback() {
    $date_text = get_option('wp_quiz_plugin_pdf_date_text', 'Date:');
    echo '<input type="text" id="wp_quiz_plugin_pdf_date_text" name="wp_quiz_plugin_pdf_date_text" value="' . esc_attr($date_text) . '" class="regular-text">';
}

// Callback function for Answer Key PDF Title
function wp_quiz_plugin_pdf_answer_key_title_callback() {
    $answer_key_title = get_option('wp_quiz_plugin_pdf_answer_key_title', 'Answer Key');
    echo '<input type="text" id="wp_quiz_plugin_pdf_answer_key_title" name="wp_quiz_plugin_pdf_answer_key_title" value="' . esc_attr($answer_key_title) . '" class="regular-text">';
}

// Callback function for Correct Answer Header Text
function wp_quiz_plugin_pdf_correct_answer_header_callback() {
    $correct_answer_header = get_option('wp_quiz_plugin_pdf_correct_answer_header', 'Correct Answer');
    echo '<input type="text" id="wp_quiz_plugin_pdf_correct_answer_header" name="wp_quiz_plugin_pdf_correct_answer_header" value="' . esc_attr($correct_answer_header) . '" class="regular-text">';
}

// Callback function for Question Header Text
function wp_quiz_plugin_pdf_question_header_callback() {
    $question_header = get_option('wp_quiz_plugin_pdf_question_header', 'Question');
    echo '<input type="text" id="wp_quiz_plugin_pdf_question_header" name="wp_quiz_plugin_pdf_question_header" value="' . esc_attr($question_header) . '" class="regular-text">';
}

// Callback function for Generated By Text
function wp_quiz_plugin_pdf_generated_by_text_callback() {
    $generated_by_text = get_option('wp_quiz_plugin_pdf_generated_by_text', 'Generated by WP Quiz Plugin');
    echo '<input type="text" id="wp_quiz_plugin_pdf_generated_by_text" name="wp_quiz_plugin_pdf_generated_by_text" value="' . esc_attr($generated_by_text) . '" class="regular-text">';
}

function wp_quiz_plugin_pdf_style_settings_init() {
    // Register settings for Questions
    register_setting('wp_quiz_plugin_pdf_style_settings', 'wp_quiz_plugin_question_font_size');
    register_setting('wp_quiz_plugin_pdf_style_settings', 'wp_quiz_plugin_question_font_color');
    register_setting('wp_quiz_plugin_pdf_style_settings', 'wp_quiz_plugin_question_font_family');
    register_setting('wp_quiz_plugin_pdf_style_settings', 'wp_quiz_plugin_question_background_color');

    // Register settings for Student Name & Date fields
    register_setting('wp_quiz_plugin_pdf_style_settings', 'wp_quiz_plugin_student_date_font_size');
    register_setting('wp_quiz_plugin_pdf_style_settings', 'wp_quiz_plugin_student_date_font_color');
    register_setting('wp_quiz_plugin_pdf_style_settings', 'wp_quiz_plugin_student_date_font_family');
    register_setting('wp_quiz_plugin_pdf_style_settings', 'wp_quiz_plugin_student_date_background_color');

    // Register settings for Quiz Download Header Texts
    register_setting('wp_quiz_plugin_pdf_style_settings', 'wp_quiz_plugin_header_font_size');
    register_setting('wp_quiz_plugin_pdf_style_settings', 'wp_quiz_plugin_header_font_color');
    register_setting('wp_quiz_plugin_pdf_style_settings', 'wp_quiz_plugin_header_font_family');

    // Register settings for Quiz Title
    register_setting('wp_quiz_plugin_pdf_style_settings', 'wp_quiz_plugin_title_font_size');
    register_setting('wp_quiz_plugin_pdf_style_settings', 'wp_quiz_plugin_title_font_color');
    register_setting('wp_quiz_plugin_pdf_style_settings', 'wp_quiz_plugin_title_font_family');

    // Register settings for Answers
    register_setting('wp_quiz_plugin_pdf_style_settings', 'wp_quiz_plugin_answer_font_size');
    register_setting('wp_quiz_plugin_pdf_style_settings', 'wp_quiz_plugin_answer_font_color');
    register_setting('wp_quiz_plugin_pdf_style_settings', 'wp_quiz_plugin_answer_font_family');
    register_setting('wp_quiz_plugin_pdf_style_settings', 'wp_quiz_plugin_answer_background_color');

    // Add a new section for the style settings
    add_settings_section(
        'wp_quiz_plugin_pdf_style_settings_section',
        __('Customize PDF Style Settings', 'wp_quiz_plugin'),
        null,
        'wp_quiz_plugin_pdf_style'
    );

    // Add settings fields for Questions
    add_settings_field('wp_quiz_plugin_question_font_size', __('Question Font Size', 'wp_quiz_plugin'), 'wp_quiz_plugin_question_font_size_callback', 'wp_quiz_plugin_pdf_style', 'wp_quiz_plugin_pdf_style_settings_section');
    add_settings_field('wp_quiz_plugin_question_font_color', __('Question Font Color', 'wp_quiz_plugin'), 'wp_quiz_plugin_question_font_color_callback', 'wp_quiz_plugin_pdf_style', 'wp_quiz_plugin_pdf_style_settings_section');
    add_settings_field('wp_quiz_plugin_question_font_family', __('Question Font Family', 'wp_quiz_plugin'), 'wp_quiz_plugin_question_font_family_callback', 'wp_quiz_plugin_pdf_style', 'wp_quiz_plugin_pdf_style_settings_section');
    add_settings_field('wp_quiz_plugin_question_background_color', __('Question Background Color', 'wp_quiz_plugin'), 'wp_quiz_plugin_question_background_color_callback', 'wp_quiz_plugin_pdf_style', 'wp_quiz_plugin_pdf_style_settings_section');

    // Add settings fields for Student Name & Date
    add_settings_field('wp_quiz_plugin_student_date_font_size', __('Student Name & Date Font Size', 'wp_quiz_plugin'), 'wp_quiz_plugin_student_date_font_size_callback', 'wp_quiz_plugin_pdf_style', 'wp_quiz_plugin_pdf_style_settings_section');
    add_settings_field('wp_quiz_plugin_student_date_font_color', __('Student Name & Date Font Color', 'wp_quiz_plugin'), 'wp_quiz_plugin_student_date_font_color_callback', 'wp_quiz_plugin_pdf_style', 'wp_quiz_plugin_pdf_style_settings_section');
    add_settings_field('wp_quiz_plugin_student_date_font_family', __('Student Name & Date Font Family', 'wp_quiz_plugin'), 'wp_quiz_plugin_student_date_font_family_callback', 'wp_quiz_plugin_pdf_style', 'wp_quiz_plugin_pdf_style_settings_section');
    add_settings_field('wp_quiz_plugin_student_date_background_color', __('Student Name & Date Background Color', 'wp_quiz_plugin'), 'wp_quiz_plugin_student_date_background_color_callback', 'wp_quiz_plugin_pdf_style', 'wp_quiz_plugin_pdf_style_settings_section');

    // Add settings fields for Quiz Download Header Texts
    add_settings_field('wp_quiz_plugin_header_font_size', __('Header Font Size', 'wp_quiz_plugin'), 'wp_quiz_plugin_header_font_size_callback', 'wp_quiz_plugin_pdf_style', 'wp_quiz_plugin_pdf_style_settings_section');
    add_settings_field('wp_quiz_plugin_header_font_color', __('Header Font Color', 'wp_quiz_plugin'), 'wp_quiz_plugin_header_font_color_callback', 'wp_quiz_plugin_pdf_style', 'wp_quiz_plugin_pdf_style_settings_section');
    add_settings_field('wp_quiz_plugin_header_font_family', __('Header Font Family', 'wp_quiz_plugin'), 'wp_quiz_plugin_header_font_family_callback', 'wp_quiz_plugin_pdf_style', 'wp_quiz_plugin_pdf_style_settings_section');

    // Add settings fields for Quiz Title
    add_settings_field('wp_quiz_plugin_title_font_size', __('Quiz Title Font Size', 'wp_quiz_plugin'), 'wp_quiz_plugin_title_font_size_callback', 'wp_quiz_plugin_pdf_style', 'wp_quiz_plugin_pdf_style_settings_section');
    add_settings_field('wp_quiz_plugin_title_font_color', __('Quiz Title Font Color', 'wp_quiz_plugin'), 'wp_quiz_plugin_title_font_color_callback', 'wp_quiz_plugin_pdf_style', 'wp_quiz_plugin_pdf_style_settings_section');
    add_settings_field('wp_quiz_plugin_title_font_family', __('Quiz Title Font Family', 'wp_quiz_plugin'), 'wp_quiz_plugin_title_font_family_callback', 'wp_quiz_plugin_pdf_style', 'wp_quiz_plugin_pdf_style_settings_section');

    // Add settings fields for Answers
    add_settings_field('wp_quiz_plugin_answer_font_size', __('Answer Font Size', 'wp_quiz_plugin'), 'wp_quiz_plugin_answer_font_size_callback', 'wp_quiz_plugin_pdf_style', 'wp_quiz_plugin_pdf_style_settings_section');
    add_settings_field('wp_quiz_plugin_answer_font_color', __('Answer Font Color', 'wp_quiz_plugin'), 'wp_quiz_plugin_answer_font_color_callback', 'wp_quiz_plugin_pdf_style', 'wp_quiz_plugin_pdf_style_settings_section');
    add_settings_field('wp_quiz_plugin_answer_font_family', __('Answer Font Family', 'wp_quiz_plugin'), 'wp_quiz_plugin_answer_font_family_callback', 'wp_quiz_plugin_pdf_style', 'wp_quiz_plugin_pdf_style_settings_section');
    add_settings_field('wp_quiz_plugin_answer_background_color', __('Answer Background Color', 'wp_quiz_plugin'), 'wp_quiz_plugin_answer_background_color_callback', 'wp_quiz_plugin_pdf_style', 'wp_quiz_plugin_pdf_style_settings_section');
}
add_action('admin_init', 'wp_quiz_plugin_pdf_style_settings_init');

function wp_quiz_plugin_question_font_size_callback() {
    $font_size = get_option('wp_quiz_plugin_question_font_size', '14px');
    echo '<input type="text" id="wp_quiz_plugin_question_font_size" name="wp_quiz_plugin_question_font_size" value="' . esc_attr($font_size) . '" class="regular-text">';
}

function wp_quiz_plugin_question_font_color_callback() {
    $font_color = get_option('wp_quiz_plugin_question_font_color', '#000000');
    echo '<input type="text" id="wp_quiz_plugin_question_font_color" name="wp_quiz_plugin_question_font_color" value="' . esc_attr($font_color) . '" class="wp-color-picker-field" data-default-color="#000000">';
}

function wp_quiz_plugin_question_font_family_callback() {
    $font_family = get_option('wp_quiz_plugin_question_font_family', 'helvetica'); // Default to 'helvetica' instead of 'Arial'
    $fonts = ['helvetica' => 'Helvetica', 'dejavusans' => 'DejaVu Sans', 'times' => 'Times', 'courier' => 'Courier']; // List of built-in fonts

    echo '<select id="wp_quiz_plugin_question_font_family" name="wp_quiz_plugin_question_font_family" class="regular-text">';
    foreach ($fonts as $key => $label) {
        echo '<option value="' . esc_attr($key) . '" ' . selected($font_family, $key, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
}

function wp_quiz_plugin_question_background_color_callback() {
    $background_color = get_option('wp_quiz_plugin_question_background_color', '#ffffff');
    echo '<input type="text" id="wp_quiz_plugin_question_background_color" name="wp_quiz_plugin_question_background_color" value="' . esc_attr($background_color) . '" class="wp-color-picker-field" data-default-color="#ffffff">';
}

function wp_quiz_plugin_student_date_font_size_callback() {
    $font_size = get_option('wp_quiz_plugin_student_date_font_size', '14px');
    echo '<input type="text" id="wp_quiz_plugin_student_date_font_size" name="wp_quiz_plugin_student_date_font_size" value="' . esc_attr($font_size) . '" class="regular-text">';
}

function wp_quiz_plugin_student_date_font_color_callback() {
    $font_color = get_option('wp_quiz_plugin_student_date_font_color', '#000000');
    echo '<input type="text" id="wp_quiz_plugin_student_date_font_color" name="wp_quiz_plugin_student_date_font_color" value="' . esc_attr($font_color) . '" class="wp-color-picker-field" data-default-color="#000000">';
}

function wp_quiz_plugin_student_date_font_family_callback() {
    $font_family = get_option('wp_quiz_plugin_student_date_font_family', 'helvetica'); // Default to 'helvetica' instead of 'Arial'
    $fonts = ['helvetica' => 'Helvetica', 'dejavusans' => 'DejaVu Sans', 'times' => 'Times', 'courier' => 'Courier']; // List of built-in fonts

    echo '<select id="wp_quiz_plugin_student_date_font_family" name="wp_quiz_plugin_student_date_font_family" class="regular-text">';
    foreach ($fonts as $key => $label) {
        echo '<option value="' . esc_attr($key) . '" ' . selected($font_family, $key, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
}


function wp_quiz_plugin_student_date_background_color_callback() {
    $background_color = get_option('wp_quiz_plugin_student_date_background_color', '#ffffff');
    echo '<input type="text" id="wp_quiz_plugin_student_date_background_color" name="wp_quiz_plugin_student_date_background_color" value="' . esc_attr($background_color) . '" class="wp-color-picker-field" data-default-color="#ffffff">';
}

function wp_quiz_plugin_header_font_size_callback() {
    $font_size = get_option('wp_quiz_plugin_header_font_size', '16px');
    echo '<input type="text" id="wp_quiz_plugin_header_font_size" name="wp_quiz_plugin_header_font_size" value="' . esc_attr($font_size) . '" class="regular-text">';
}

function wp_quiz_plugin_header_font_color_callback() {
    $font_color = get_option('wp_quiz_plugin_header_font_color', '#000000');
    echo '<input type="text" id="wp_quiz_plugin_header_font_color" name="wp_quiz_plugin_header_font_color" value="' . esc_attr($font_color) . '" class="wp-color-picker-field" data-default-color="#000000">';
}

function wp_quiz_plugin_header_font_family_callback() {
    $font_family = get_option('wp_quiz_plugin_header_font_family', 'helvetica'); // Default to 'helvetica' instead of 'Arial'
    $fonts = ['helvetica' => 'Helvetica', 'dejavusans' => 'DejaVu Sans', 'times' => 'Times', 'courier' => 'Courier']; // List of built-in fonts

    echo '<select id="wp_quiz_plugin_header_font_family" name="wp_quiz_plugin_header_font_family" class="regular-text">';
    foreach ($fonts as $key => $label) {
        echo '<option value="' . esc_attr($key) . '" ' . selected($font_family, $key, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
}


function wp_quiz_plugin_title_font_size_callback() {
    $font_size = get_option('wp_quiz_plugin_title_font_size', '16px');
    echo '<input type="text" id="wp_quiz_plugin_title_font_size" name="wp_quiz_plugin_title_font_size" value="' . esc_attr($font_size) . '" class="regular-text">';
}

function wp_quiz_plugin_title_font_color_callback() {
    $font_color = get_option('wp_quiz_plugin_title_font_color', '#000000');
    echo '<input type="text" id="wp_quiz_plugin_title_font_color" name="wp_quiz_plugin_title_font_color" value="' . esc_attr($font_color) . '" class="wp-color-picker-field" data-default-color="#000000">';
}

function wp_quiz_plugin_title_font_family_callback() {
    $font_family = get_option('wp_quiz_plugin_title_font_family', 'helvetica'); // Default to 'helvetica' instead of 'Arial'
    $fonts = ['helvetica' => 'Helvetica', 'dejavusans' => 'DejaVu Sans', 'times' => 'Times', 'courier' => 'Courier']; // List of built-in fonts

    echo '<select id="wp_quiz_plugin_title_font_family" name="wp_quiz_plugin_title_font_family" class="regular-text">';
    foreach ($fonts as $key => $label) {
        echo '<option value="' . esc_attr($key) . '" ' . selected($font_family, $key, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
}

function wp_quiz_plugin_answer_font_size_callback() {
    $font_size = get_option('wp_quiz_plugin_answer_font_size', '14px');
    echo '<input type="text" id="wp_quiz_plugin_answer_font_size" name="wp_quiz_plugin_answer_font_size" value="' . esc_attr($font_size) . '" class="regular-text">';
}

function wp_quiz_plugin_answer_font_color_callback() {
    $font_color = get_option('wp_quiz_plugin_answer_font_color', '#000000');
    echo '<input type="text" id="wp_quiz_plugin_answer_font_color" name="wp_quiz_plugin_answer_font_color" value="' . esc_attr($font_color) . '" class="wp-color-picker-field" data-default-color="#000000">';
}

function wp_quiz_plugin_answer_font_family_callback() {
    $font_family = get_option('wp_quiz_plugin_answer_font_family', 'helvetica'); // Default to 'helvetica' instead of 'Arial'
    $fonts = ['helvetica' => 'Helvetica', 'dejavusans' => 'DejaVu Sans', 'times' => 'Times', 'courier' => 'Courier']; // List of built-in fonts

    echo '<select id="wp_quiz_plugin_answer_font_family" name="wp_quiz_plugin_answer_font_family" class="regular-text">';
    foreach ($fonts as $key => $label) {
        echo '<option value="' . esc_attr($key) . '" ' . selected($font_family, $key, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
}


function wp_quiz_plugin_answer_background_color_callback() {
    $background_color = get_option('wp_quiz_plugin_answer_background_color', '#ffffff');
    echo '<input type="text" id="wp_quiz_plugin_answer_background_color" name="wp_quiz_plugin_answer_background_color" value="' . esc_attr($background_color) . '" class="wp-color-picker-field" data-default-color="#ffffff">';
}


function wp_quiz_plugin_quizzes_settings_init() {
    register_setting('wp_quiz_plugin_general_settings', 'wp_quiz_plugin_number_of_questions');
    register_setting('wp_quiz_plugin_general_settings', 'wp_quiz_plugin_quizzes_url_slug');
    
    add_settings_section('wp_quiz_plugin_sample_section', '', '', 'wp_quiz_plugin_general');
    
    add_settings_field('wp_quiz_plugin_quizzes_url_slug', 'Custom URL Slug for Quizzes', 'wp_quiz_plugin_quizzes_url_slug_callback', 'wp_quiz_plugin_general', 'wp_quiz_plugin_sample_section');
}
add_action('admin_init', 'wp_quiz_plugin_quizzes_settings_init');

//function wp_quiz

function wp_quiz_plugin_quizzes_url_slug_callback() {
    $url_slug = get_option('wp_quiz_plugin_quizzes_url_slug', 'quizzes');
    ?>
    <div style="margin-bottom: 15px;">
        <label for="wp_quiz_plugin_quizzes_url_slug">
            <strong><?php esc_html_e('Custom URL Slug for Quizzes:', 'wp_quiz_plugin'); ?></strong>
        </label>
        <input type="text" id="wp_quiz_plugin_quizzes_url_slug" name="wp_quiz_plugin_quizzes_url_slug" value="<?php echo esc_attr($url_slug); ?>" class="regular-text" style="max-width: 300px;">
        <p class="description"><?php esc_html_e('Set a custom slug for your quizzes URL. Example: "my-quizzes".', 'wp_quiz_plugin'); ?></p>
    </div>
    <?php
}

if (isset($_POST['wp_quiz_plugin_quizzes_url_slug'])) flush_rewrite_rules();

function wp_quiz_plugin_quizzes_questions_count_settins_init(){
    add_settings_section('wp_quiz_plugin_question_count_section', null, null, 'wp_quiz_plugin_general');
    add_settings_field(
        'wp_quiz_plugin_number_of_questions', 
        'Number of Questions Options', 
        'wp_quiz_plugin_number_of_questions_callback', 
        'wp_quiz_plugin_general', 
        'wp_quiz_plugin_question_count_section'
    );
}
add_action('admin_init', 'wp_quiz_plugin_quizzes_questions_count_settins_init');

function wp_quiz_plugin_number_of_questions_callback() {
    $options = get_option('wp_quiz_plugin_number_of_questions', '1,5,10');
    ?>
    <div style="margin-bottom: 15px;">
        <label for="wp_quiz_plugin_number_of_questions">
            <strong><?php esc_html_e('Comma-separated list of numbers for questions dropdown:', 'wp_quiz_plugin'); ?></strong>
        </label>
        <input type="text" id="wp_quiz_plugin_number_of_questions" name="wp_quiz_plugin_number_of_questions" value="<?php echo esc_attr($options); ?>" class="regular-text" style="max-width: 300px;">
        <p class="description"><?php esc_html_e('Enter numbers separated by commas (e.g., "1, 5, 10") to define the options for the number of questions users can select.', 'wp_quiz_plugin'); ?></p>
    </div>
    <?php
}

// Open AI API Settings
function wp_quiz_plugin_general_settings_init() {
    register_setting('wp_quiz_plugin_general_settings', 'wp_quiz_plugin_openai_api_key');

    add_settings_section(
        'wp_quiz_plugin_openai_section',
        'OpenAI API Settings',
        null,
        'wp_quiz_plugin_general'
    );

    add_settings_field(
        'wp_quiz_plugin_openai_api_key',
        'OpenAI API Key',
        'wp_quiz_plugin_openai_api_key_callback',
        'wp_quiz_plugin_general',
        'wp_quiz_plugin_openai_section'
    );
}

function wp_quiz_plugin_openai_api_key_callback() {
    $api_key = get_option('wp_quiz_plugin_openai_api_key');
    echo '<input type="text" name="wp_quiz_plugin_openai_api_key" value="' . esc_attr($api_key) . '" class="regular-text">';
}

add_action('admin_init', 'wp_quiz_plugin_general_settings_init');

// Button to add watermark image Golobally for all PDF download
function wp_quiz_plugin_quizzes_watermark_settings_init(){
    register_setting('wp_quiz_plugin_general_settings', 'wp_quiz_plugin_pdf_image_url');

    add_settings_section(
        'wp_quiz_plugin_pdf_watermark_section',
        'Upload PDF watermark',
        'wp_quiz_plugin_pdf_image_callback',
        'wp_quiz_plugin_general'
    );
}
add_action('admin_init', 'wp_quiz_plugin_quizzes_watermark_settings_init');

// Enqueue media scripts for the admin area
function wp_quiz_plugin_enqueue_media() {
    // Only load in admin and the specific page where your settings are
    if (is_admin()) {
        // Enqueue the WordPress media library
        wp_enqueue_media();
    }
}
add_action('admin_enqueue_scripts', 'wp_quiz_plugin_enqueue_media');

function wp_quiz_plugin_pdf_image_callback(){
    $default_image_url = plugin_dir_url(__FILE__) . 'assets/upload-icon.png';
    $pdf_image_url = esc_url(get_option('wp_quiz_plugin_pdf_image_url'));

    ?>
    <div id="kw_image_container" style="display: flex; align-items: center; margin-bottom: 10px;">
        <!-- Image Box -->
        <div style="position: relative; width: 100px; height: 100px; border: 1px dashed #ccc; border-radius: 4px; overflow: hidden;">
            <img id='kw_pdf_image_display' src="<?php echo $pdf_image_url ? $pdf_image_url : $default_image_url; ?>" alt="Uploaded PDF Image" style="width: 100%; height: 100%; object-fit: cover;">
        </div>

        <div style='display: flex; flex-direction: column; align-items: baseline'>
         <!-- Upload/Change PDF Image Button -->
            <button type="button" id="kw_upload-pdf-image-btn" class="kw_button kw_button-secondary" style="margin-top: 10px; margin-left: 10px">
                <?php echo $pdf_image_url ? 'Change Image  🔄': 'Upload PDF Image'; ?>
            </button>
        <!-- Remove Image Button -->
            <button type="button" id="kw_remove-pdf-image-btn" class="kw_button kw_button-secondary" style="margin-top: 10px;">
                Remove Image ❌
            </button>
        </div>
    </div>

    <!-- Hidden Input for PDF Image URL -->
    <input type="hidden" id="kw_pdf_image_url" name="wp_quiz_plugin_pdf_image_url" value="<?= esc_attr($pdf_image_url); ?>">

    <script>
        jQuery(document).ready(function ($) {
            $('#kw_upload-pdf-image-btn').on('click', function (e) {
                e.preventDefault();
                var frame = wp.media({
                    title: '<?php _e('Select or Upload PDF Image', 'wp-quiz-plugin'); ?>',
                    button: { text: '<?php _e('Use this Image', 'wp-quiz-plugin'); ?>' },
                    multiple: false
                });

                frame.on('select', function () {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#kw_pdf_image_url').val(attachment.url); // Set the image URL to the hidden input field
                    // Update the displayed image
                    $('#kw_pdf_image_display').attr('src', attachment.url).show();
                    $('#kw_image_placeholder').hide(); // Hide the placeholder

                    // Show the remove button after an image is uploaded
                    $('#kw_remove-pdf-image-btn').show();

                    // Change button text to "Change Image"
                    $('#kw_upload-pdf-image-btn').text('<?php echo __('Change Image', 'wp-quiz-plugin'); ?> 🔄');
                });

                frame.open();
            });

            // Remove image functionality
            $('#kw_remove-pdf-image-btn').on('click', function () {
                // Clear the hidden input field
                $('#kw_pdf_image_url').val('');

                // Remove the displayed image
                $('#kw_remove-pdf-image-btn').prev('img').remove();

                $('#kw_pdf_image_display').attr('src', '<?php echo  $default_image_url; ?>').show()

                // Reset button text
                $('#kw_upload-pdf-image-btn').text('Upload PDF Image⬆️');

                // Hide the remove button
                 $(this).hide();
            });
        });
    </script>
    <?php
}


// Model, Max Token and Temperature
function wp_quiz_plugin_openai_settings_init() {
    // Register settings
    register_setting('wp_quiz_plugin_general_settings', 'wp_quiz_plugin_openai_model');
    register_setting('wp_quiz_plugin_general_settings', 'wp_quiz_plugin_openai_max_tokens');
    register_setting('wp_quiz_plugin_general_settings', 'wp_quiz_plugin_openai_temperature');

    // Add a new section
    add_settings_section(
        'wp_quiz_plugin_openai_section',
        __('OpenAI API Settings', 'wp_quiz_plugin'),
        null,
        'wp_quiz_plugin_general'
    );

    // Add settings fields for model selection
    add_settings_field(
        'wp_quiz_plugin_openai_model',
        __('OpenAI Model', 'wp_quiz_plugin'),
        'wp_quiz_plugin_openai_model_callback',
        'wp_quiz_plugin_general',
        'wp_quiz_plugin_openai_section'
    );

    // Add settings fields for max tokens
    add_settings_field(
        'wp_quiz_plugin_openai_max_tokens',
        __('Max Tokens', 'wp_quiz_plugin'),
        'wp_quiz_plugin_openai_max_tokens_callback',
        'wp_quiz_plugin_general',
        'wp_quiz_plugin_openai_section'
    );

    // Add settings fields for temperature
    add_settings_field(
        'wp_quiz_plugin_openai_temperature',
        __('Temperature', 'wp_quiz_plugin'),
        'wp_quiz_plugin_openai_temperature_callback',
        'wp_quiz_plugin_general',
        'wp_quiz_plugin_openai_section'
    );
}
add_action('admin_init', 'wp_quiz_plugin_openai_settings_init');

// Callback function for OpenAI model field
function wp_quiz_plugin_openai_model_callback() {
    $model = get_option('wp_quiz_plugin_openai_model', 'gpt-3.5-turbo');
    ?>
    <select name="wp_quiz_plugin_openai_model">
        <option value="gpt-3.5-turbo" <?php selected($model, 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
        <option value="gpt-4o-mini" <?php selected($model, 'gpt-4o-mini'); ?>>GPT-4.0 Mini</option>
        <option value="gpt-4o" <?php selected($model, 'gpt-4o'); ?>>GPT-4.0</option>
        <option value="gpt-4" <?php selected($model, 'gpt-4'); ?>>GPT-4</option>
    </select>
    <p class="description"><?php esc_html_e('Select the OpenAI model to use.', 'wp_quiz_plugin'); ?></p>
    <?php
}

// Callback function for max tokens field
function wp_quiz_plugin_openai_max_tokens_callback() {
    $max_tokens = get_option('wp_quiz_plugin_openai_max_tokens', 50);
    ?>
    <input type="number" name="wp_quiz_plugin_openai_max_tokens" value="<?php echo esc_attr($max_tokens); ?>" min="50" max="300" step="1">
    <p class="description"><?php esc_html_e('Set the maximum number of tokens for the response (50 to 300).', 'wp_quiz_plugin'); ?></p>
    <?php
}

// Callback function for temperature field
function wp_quiz_plugin_openai_temperature_callback() {
    $temperature = get_option('wp_quiz_plugin_openai_temperature', 0.5);
    ?>
    <input type="number" name="wp_quiz_plugin_openai_temperature" value="<?php echo esc_attr($temperature); ?>" min="0" max="1" step="0.1">
    <p class="description"><?php esc_html_e('Set the temperature for response randomness (0 to 1).', 'wp_quiz_plugin'); ?></p>
    <?php
}

// --------------------------------
// ====================

function wp_quiz_plugin_quizzes_styles_settings_init() {
    register_setting('wp_quiz_plugin_quizzes_settings', 'wp_quiz_plugin_text_font');
    register_setting('wp_quiz_plugin_quizzes_settings', 'wp_quiz_plugin_text_color');
    register_setting('wp_quiz_plugin_quizzes_settings', 'wp_quiz_plugin_text_font_size');
    add_settings_field('wp_quiz_plugin_text_font_size', 'Font Size for Questions', 'wp_quiz_plugin_text_font_size_callback', 'wp_quiz_plugin', 'wp_quiz_plugin_question_styles_section');
    add_settings_section('wp_quiz_plugin_question_styles_section', 'Question Styles', null, 'wp_quiz_plugin');
    add_settings_field('wp_quiz_plugin_text_font', 'Text Font for Questions', 'wp_quiz_plugin_text_font_callback', 'wp_quiz_plugin', 'wp_quiz_plugin_question_styles_section');
    add_settings_field('wp_quiz_plugin_text_color', 'Text Color for Questions', 'wp_quiz_plugin_text_color_callback', 'wp_quiz_plugin', 'wp_quiz_plugin_question_styles_section');
}
add_action('admin_init', 'wp_quiz_plugin_quizzes_styles_settings_init');

function wp_quiz_plugin_text_font_callback() {
    $font = get_option('wp_quiz_plugin_text_font', 'Arial');
    $google_fonts = wp_quiz_plugin_get_google_fonts();
    echo '<div style="margin-bottom: 15px;">
            <label for="wp_quiz_plugin_text_font"><strong>' . esc_html__('Select Question Font:', 'wp_quiz_plugin') . '</strong></label>
            <select id="wp_quiz_plugin_text_font" name="wp_quiz_plugin_text_font" class="regular-text">';
    foreach ($google_fonts as $key => $label) {
        echo '<option value="' . esc_attr($key) . '" ' . selected($font, $key, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select><p class="description">' . esc_html__('Choose the font for quiz questions.', 'wp_quiz_plugin') . '</p></div>';
}

function wp_quiz_plugin_text_color_callback() {
    $color = get_option('wp_quiz_plugin_text_color', '#000000');
    echo '<div style="margin-bottom: 15px;">
            <label for="wp_quiz_plugin_text_color"><strong>' . esc_html__('Question Text Color:', 'wp_quiz_plugin') . '</strong></label>
            <input type="text" id="wp_quiz_plugin_text_color" name="wp_quiz_plugin_text_color" value="' . esc_attr($color) . '" class="wp-color-picker-field" data-default-color="#000000">
            <p class="description">' . esc_html__('Set the color for the quiz questions text.', 'wp_quiz_plugin') . '</p>
          </div>';
}
// Callback function for Font size
function wp_quiz_plugin_text_font_size_callback() {
    $font_size = get_option('wp_quiz_plugin_text_font_size', '16px');
    echo '<div style="margin-bottom: 15px;">
            <label for="wp_quiz_plugin_text_font_size"><strong>' . esc_html__('Font Size for Questions:', 'wp_quiz_plugin') . '</strong></label>
            <input type="text" id="wp_quiz_plugin_text_font_size" name="wp_quiz_plugin_text_font_size" value="' . esc_attr($font_size) . '" class="regular-text" style="max-width: 100px;">
            <p class="description">' . esc_html__('Set the font size for the quiz questions (e.g., "16px").', 'wp_quiz_plugin') . '</p>
          </div>';
}

function wp_quiz_plugin_answers_styles_settings_init() {
    register_setting('wp_quiz_plugin_quizzes_settings', 'wp_quiz_plugin_answer_text_font_size');
    register_setting('wp_quiz_plugin_quizzes_settings', 'wp_quiz_plugin_answer_text_font');
    register_setting('wp_quiz_plugin_quizzes_settings', 'wp_quiz_plugin_answer_text_color');
    add_settings_section('wp_quiz_plugin_answer_styles_section', 'Answer Styles', null, 'wp_quiz_plugin');
    add_settings_field('wp_quiz_plugin_answer_text_font_size', 'Font Size for Answers', 'wp_quiz_plugin_answer_text_font_size_callback', 'wp_quiz_plugin', 'wp_quiz_plugin_answer_styles_section');

    add_settings_field('wp_quiz_plugin_answer_text_font', 'Text Font for Answers', 'wp_quiz_plugin_answer_text_font_callback', 'wp_quiz_plugin', 'wp_quiz_plugin_answer_styles_section');
    add_settings_field('wp_quiz_plugin_answer_text_color', 'Text Color for Answers', 'wp_quiz_plugin_answer_text_color_callback', 'wp_quiz_plugin', 'wp_quiz_plugin_answer_styles_section');
}
add_action('admin_init', 'wp_quiz_plugin_answers_styles_settings_init');

function wp_quiz_plugin_answer_text_font_callback() {
    $font = get_option('wp_quiz_plugin_answer_text_font', 'Arial');
    $google_fonts = wp_quiz_plugin_get_google_fonts();
    echo '<div style="margin-bottom: 15px;">
            <label for="wp_quiz_plugin_answer_text_font"><strong>' . esc_html__('Select Answer Font:', 'wp_quiz_plugin') . '</strong></label>
            <select id="wp_quiz_plugin_answer_text_font" name="wp_quiz_plugin_answer_text_font" class="regular-text">';
    foreach ($google_fonts as $key => $label) {
        echo '<option value="' . esc_attr($key) . '" ' . selected($font, $key, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select><p class="description">' . esc_html__('Choose the font for quiz answers.', 'wp_quiz_plugin') . '</p></div>';
}

function wp_quiz_plugin_answer_text_color_callback() {
    $color = get_option('wp_quiz_plugin_answer_text_color', '#000000');
    echo '<div style="margin-bottom: 15px;">
            <label for="wp_quiz_plugin_answer_text_color"><strong>' . esc_html__('Answer Text Color:', 'wp_quiz_plugin') . '</strong></label>
            <input type="text" id="wp_quiz_plugin_answer_text_color" name="wp_quiz_plugin_answer_text_color" value="' . esc_attr($color) . '" class="wp-color-picker-field" data-default-color="#000000">
            <p class="description">' . esc_html__('Set the color for the quiz answers text.', 'wp_quiz_plugin') . '</p>
          </div>';
}
function wp_quiz_plugin_answer_text_font_size_callback() {
    $font_size = get_option('wp_quiz_plugin_answer_text_font_size', '16px');
    echo '<div style="margin-bottom: 15px;">
            <label for="wp_quiz_plugin_answer_text_font_size"><strong>' . esc_html__('Font Size for Answers:', 'wp_quiz_plugin') . '</strong></label>
            <input type="text" id="wp_quiz_plugin_answer_text_font_size" name="wp_quiz_plugin_answer_text_font_size" value="' . esc_attr($font_size) . '" class="regular-text" style="max-width: 100px;">
            <p class="description">' . esc_html__('Set the font size for the quiz answers (e.g., "16px").', 'wp_quiz_plugin') . '</p>
          </div>';
}

define('WP_QUIZ_PLUGIN_DEFAULT_FONT', 'Arial');
define('WP_QUIZ_PLUGIN_DEFAULT_BTN_COLOR', '#28a745');
define('WP_QUIZ_PLUGIN_DEFAULT_AI_BTN_COLOR', '#007bff');
define('WP_QUIZ_PLUGIN_DEFAULT_FONT_COLOR', '#ffffff');

function wp_quiz_plugin_get_google_fonts() {
    return ['Arial' => 'Arial', 'Roboto' => 'Roboto', 'Open Sans' => 'Open Sans', 'Lato' => 'Lato', 'Montserrat' => 'Montserrat', 'Oswald' => 'Oswald', 'Raleway' => 'Raleway', 'PT Sans' => 'PT Sans'];
}

function wp_quiz_plugin_initialize_button_settings() {
    $buttons = [
        ['prefix' => 'wp_quiz_plugin_add_question_btn', 'label' => 'Add New Question Button', 'default_color' => WP_QUIZ_PLUGIN_DEFAULT_BTN_COLOR],
        ['prefix' => 'wp_quiz_plugin_generate_question_btn', 'label' => 'Add New Question Button With AI', 'default_color' => WP_QUIZ_PLUGIN_DEFAULT_AI_BTN_COLOR],
        ['prefix' => 'wp_quiz_plugin_button', 'label' => 'Download Quiz Button', 'default_color' => WP_QUIZ_PLUGIN_DEFAULT_AI_BTN_COLOR]
    ];

    foreach ($buttons as $button) {
        add_settings_section("{$button['prefix']}_section", "{$button['label']} Settings", function() use ($button) { 
            wp_quiz_plugin_render_button_settings_group($button); 
        }, 'wp_quiz_plugin');

        // Register the settings for font, color, font color, and font size
        foreach (['font', 'color', 'font_color', 'font_size'] as $type) {
            register_setting('wp_quiz_plugin_quizzes_settings', "{$button['prefix']}_{$type}");
        }
    }
}
add_action('admin_init', 'wp_quiz_plugin_initialize_button_settings');


function wp_quiz_plugin_render_button_settings_group($button) {
    $types = [
        'font' => 'Font',
        'color' => 'Background Color',
        'font_color' => 'Font Color',
        'font_size' => 'Font Size'  // Add the font size here
    ];

    echo '<fieldset>';
    foreach ($types as $type => $label) {
        $option_name = "{$button['prefix']}_{$type}";
        $default_value = $type === 'font_size' ? '16px' : ($type === 'font' ? WP_QUIZ_PLUGIN_DEFAULT_FONT : $button['default_color']);
        wp_quiz_plugin_render_input_field($option_name, $type, $default_value, $button['label'], $label);
    }
    echo '</fieldset>';
}


function wp_quiz_plugin_render_input_field($option_name, $type, $default_value, $button_label, $label) {
    $value = get_option($option_name, $default_value);

    echo '<div style="margin-bottom: 20px;"><label for="' . esc_attr($option_name) . '" style="display:block; font-weight: bold;">' 
         . esc_html($button_label) . ' ' . esc_html($label) . '</label>';

    if ($type === 'font') {
        echo '<select id="' . esc_attr($option_name) . '" name="' . esc_attr($option_name) . '" class="regular-text">';
        foreach (wp_quiz_plugin_get_google_fonts() as $key => $font_label) {
            echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($font_label) . '</option>';
        }
        echo '</select>';
    } elseif ($type === 'font_size') {
        echo '<input type="text" id="' . esc_attr($option_name) . '" name="' . esc_attr($option_name) . '" value="' . esc_attr($value) . '" class="regular-text" style="max-width: 100px;">';
    } else {
        echo '<input type="text" id="' . esc_attr($option_name) . '" name="' . esc_attr($option_name) . '" value="' . esc_attr($value) . '" class="wp-color-picker-field" data-default-color="' . esc_attr($default_value) . '" style="max-width: 100px;">';
    }

    echo '<p class="description">' . esc_html__('Adjust the settings to customize the appearance of the button.', 'wp_quiz_plugin') . '</p></div>';
}

function wp_quiz_plugin_enqueue_admin_styles() {
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    wp_add_inline_script('wp-color-picker', "jQuery(document).ready(function($) { $('.wp-color-picker-field').wpColorPicker(); });");
    wp_enqueue_style('wp_quiz_plugin_admin_styles', plugins_url('admin-style.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'wp_quiz_plugin_enqueue_admin_styles');

add_action('admin_head', function() {
    echo '<style>
        .wp-color-picker-field { max-width: 100px; margin: 0 10px; }
        .form-table th { padding: 20px 10px; }
        .form-table td { padding: 10px 10px; }
        fieldset { border: 1px solid #ddd; padding: 10px; margin-bottom: 15px; }
        legend { font-weight: bold; margin-bottom: 5px; }
        .description { color: #666; font-size: 12px; }
    </style>';
});

// Popup style settings
// Initialize settings for SweetAlert popup styles
function wp_quiz_plugin_swal_styles_settings_init() {
    register_setting('wp_quiz_plugin_quizzes_settings', 'wp_quiz_plugin_swal_font_color');
    register_setting('wp_quiz_plugin_quizzes_settings', 'wp_quiz_plugin_swal_font_family');
    register_setting('wp_quiz_plugin_quizzes_settings', 'wp_quiz_plugin_swal_button_font_family');
    register_setting('wp_quiz_plugin_quizzes_settings', 'wp_quiz_plugin_swal_button_font_color');
    register_setting('wp_quiz_plugin_quizzes_settings', 'wp_quiz_plugin_swal_button_background_color');
    register_setting('wp_quiz_plugin_quizzes_settings', 'wp_quiz_plugin_swal_font_size');
    register_setting('wp_quiz_plugin_quizzes_settings', 'wp_quiz_plugin_swal_button_font_size');


    
    add_settings_section(
        'wp_quiz_plugin_swal_styles_section',
        'SweetAlert Popup Styles',
        null,
        'wp_quiz_plugin'
    );

    add_settings_field(
        'wp_quiz_plugin_swal_font_color',
        'Popup Font Color',
        'wp_quiz_plugin_swal_font_color_callback',
        'wp_quiz_plugin',
        'wp_quiz_plugin_swal_styles_section'
    );

    add_settings_field(
        'wp_quiz_plugin_swal_font_family',
        'Popup Font Family',
        'wp_quiz_plugin_swal_font_family_callback',
        'wp_quiz_plugin',
        'wp_quiz_plugin_swal_styles_section'
    );

    add_settings_field(
        'wp_quiz_plugin_swal_button_font_family',
        'Button Font Family',
        'wp_quiz_plugin_swal_button_font_family_callback',
        'wp_quiz_plugin',
        'wp_quiz_plugin_swal_styles_section'
    );

    add_settings_field(
        'wp_quiz_plugin_swal_button_font_color',
        'Button Font Color',
        'wp_quiz_plugin_swal_button_font_color_callback',
        'wp_quiz_plugin',
        'wp_quiz_plugin_swal_styles_section'
    );

    add_settings_field(
        'wp_quiz_plugin_swal_button_background_color',
        'Button Background Color',
        'wp_quiz_plugin_swal_button_background_color_callback',
        'wp_quiz_plugin',
        'wp_quiz_plugin_swal_styles_section'
    );
    add_settings_field(
        'wp_quiz_plugin_swal_font_size',
        'Popup Font Size',
        'wp_quiz_plugin_swal_font_size_callback',
        'wp_quiz_plugin',
        'wp_quiz_plugin_swal_styles_section'
    );
    add_settings_field(
        'wp_quiz_plugin_swal_button_font_size',
        'Popup Button Font Size',
        'wp_quiz_plugin_swal_button_font_size_callback',
        'wp_quiz_plugin',
        'wp_quiz_plugin_swal_styles_section'
    );
    
}
add_action('admin_init', 'wp_quiz_plugin_swal_styles_settings_init');

// Callback functions to render the fields
function wp_quiz_plugin_swal_font_color_callback() {
    $color = get_option('wp_quiz_plugin_swal_font_color', '#000000');
    echo '<input type="text" id="wp_quiz_plugin_swal_font_color" name="wp_quiz_plugin_swal_font_color" value="' . esc_attr($color) . '" class="wp-color-picker-field" data-default-color="#000000">';
}

function wp_quiz_plugin_swal_font_family_callback() {
    $font = get_option('wp_quiz_plugin_swal_font_family', 'Arial');
    echo '<input type="text" id="wp_quiz_plugin_swal_font_family" name="wp_quiz_plugin_swal_font_family" value="' . esc_attr($font) . '" class="regular-text">';
}

function wp_quiz_plugin_swal_button_font_family_callback() {
    $font = get_option('wp_quiz_plugin_swal_button_font_family', 'Arial');
    echo '<input type="text" id="wp_quiz_plugin_swal_button_font_family" name="wp_quiz_plugin_swal_button_font_family" value="' . esc_attr($font) . '" class="regular-text">';
}

function wp_quiz_plugin_swal_button_font_color_callback() {
    $color = get_option('wp_quiz_plugin_swal_button_font_color', '#ffffff');
    echo '<input type="text" id="wp_quiz_plugin_swal_button_font_color" name="wp_quiz_plugin_swal_button_font_color" value="' . esc_attr($color) . '" class="wp-color-picker-field" data-default-color="#ffffff">';
}

function wp_quiz_plugin_swal_button_background_color_callback() {
    $color = get_option('wp_quiz_plugin_swal_button_background_color', '#007bff');
    echo '<input type="text" id="wp_quiz_plugin_swal_button_background_color" name="wp_quiz_plugin_swal_button_background_color" value="' . esc_attr($color) . '" class="wp-color-picker-field" data-default-color="#007bff">';
}

function wp_quiz_plugin_swal_font_size_callback() {
    $font_size = get_option('wp_quiz_plugin_swal_font_size', '16px');
    echo '<div style="margin-bottom: 15px;">
            <label for="wp_quiz_plugin_swal_font_size"><strong>' . esc_html__('Popup Font Size:', 'wp_quiz_plugin') . '</strong></label>
            <input type="text" id="wp_quiz_plugin_swal_font_size" name="wp_quiz_plugin_swal_font_size" value="' . esc_attr($font_size) . '" class="regular-text" style="max-width: 100px;">
            <p class="description">' . esc_html__('Set the font size for the SweetAlert popup (e.g., "16px").', 'wp_quiz_plugin') . '</p>
          </div>';
}
function wp_quiz_plugin_swal_button_font_size_callback() {
    $button_font_size = get_option('wp_quiz_plugin_swal_button_font_size', '14px');
    echo '<div style="margin-bottom: 15px;">
            <label for="wp_quiz_plugin_swal_button_font_size"><strong>' . esc_html__('Popup Button Font Size:', 'wp_quiz_plugin') . '</strong></label>
            <input type="text" id="wp_quiz_plugin_swal_button_font_size" name="wp_quiz_plugin_swal_button_font_size" value="' . esc_attr($button_font_size) . '" class="regular-text" style="max-width: 100px;">
            <p class="description">' . esc_html__('Set the font size for the SweetAlert popup buttons (e.g., "14px").', 'wp_quiz_plugin') . '</p>
          </div>';
}

// ====================
// Frontend styles settings
// Register settings for frontend styles
function wp_quiz_plugin_frontend_styles_settings_init() {
    register_setting('wp_quiz_plugin_frontend_styles_settings', 'wp_quiz_plugin_font_family');
    register_setting('wp_quiz_plugin_frontend_styles_settings', 'wp_quiz_plugin_font_color');
    register_setting('wp_quiz_plugin_frontend_styles_settings', 'wp_quiz_plugin_background_color');
    register_setting('wp_quiz_plugin_frontend_styles_settings', 'wp_quiz_plugin_button_background_color');
    register_setting('wp_quiz_plugin_frontend_styles_settings', 'wp_quiz_plugin_button_text_color');


    // Progress bar settings
    register_setting('wp_quiz_plugin_frontend_styles_settings', 'wp_quiz_plugin_progress_bar_color');
    register_setting('wp_quiz_plugin_frontend_styles_settings', 'wp_quiz_plugin_progress_bar_background_color');

    // Add settings for Question and Answers Style Settings (New Addition)
    register_setting('wp_quiz_plugin_frontend_styles_settings', 'wp_quiz_plugin_frontend_question_font_family');
    register_setting('wp_quiz_plugin_frontend_styles_settings', 'wp_quiz_plugin_frontend_question_font_size');
    register_setting('wp_quiz_plugin_frontend_styles_settings', 'wp_quiz_plugin_frontend_question_font_color');
    register_setting('wp_quiz_plugin_frontend_styles_settings', 'wp_quiz_plugin_frontend_answer_font_family');
    register_setting('wp_quiz_plugin_frontend_styles_settings', 'wp_quiz_plugin_frontend_answer_font_color');
    register_setting('wp_quiz_plugin_frontend_styles_settings', 'wp_quiz_plugin_frontend_answer_font_size');

    // Add a new section for Question Style Settings under Frontend Styles
    add_settings_section(
        'wp_quiz_plugin_frontend_question_style_section',
        __('Question Style Settings', 'wp_quiz_plugin'),
        null,
        'wp_quiz_plugin_frontend'
    );

    // Add settings fields for font family, font size, and color
    add_settings_field(
        'wp_quiz_plugin_frontend_question_font_family',
        __('Font Family', 'wp_quiz_plugin'),
        'wp_quiz_plugin_frontend_question_font_family_callback',
        'wp_quiz_plugin_frontend',
        'wp_quiz_plugin_frontend_question_style_section'
    );

    add_settings_field(
        'wp_quiz_plugin_frontend_question_font_size',
        __('Font Size', 'wp_quiz_plugin'),
        'wp_quiz_plugin_frontend_question_font_size_callback',
        'wp_quiz_plugin_frontend',
        'wp_quiz_plugin_frontend_question_style_section'
    );

    add_settings_field(
        'wp_quiz_plugin_frontend_question_font_color',
        __('Font Color', 'wp_quiz_plugin'),
        'wp_quiz_plugin_frontend_question_font_color_callback',
        'wp_quiz_plugin_frontend',
        'wp_quiz_plugin_frontend_question_style_section'
    );

    // Add Reset to Default button
    add_settings_field(
        'wp_quiz_plugin_frontend_question_reset_defaults',
        __('Reset to Default', 'wp_quiz_plugin'),
        'wp_quiz_plugin_frontend_question_reset_defaults_callback',
        'wp_quiz_plugin_frontend',
        'wp_quiz_plugin_frontend_question_style_section'
    );
    
   // ------------------------------------
    // Add a new section for Answers Style Settings
    add_settings_section(
        'wp_quiz_plugin_frontend_answer_style_section',
        __('Answers Style Settings', 'wp_quiz_plugin'),
        null,
        'wp_quiz_plugin_frontend'
    );

    // Add new field for Answers font-family
    add_settings_field(
        'wp_quiz_plugin_frontend_answer_font_family',
        __('Font Family', 'wp_quiz_plugin'),
        'wp_quiz_plugin_frontend_answer_font_family_callback',
        'wp_quiz_plugin_frontend',
        'wp_quiz_plugin_frontend_answer_style_section'
    );

    // Add new field for Answers font-color
    add_settings_field(
        'wp_quiz_plugin_frontend_answer_font_color',
        __('Font Color', 'wp_quiz_plugin'),
        'wp_quiz_plugin_frontend_answer_font_color_callback',
        'wp_quiz_plugin_frontend',
        'wp_quiz_plugin_frontend_answer_style_section'
    );

    // Add new field for Answers font size
    add_settings_field(
        'wp_quiz_plugin_frontend_answer_font_size',
        __('Font Size', 'wp_quiz_plugin'),
        'wp_quiz_plugin_frontend_answer_font_size_callback',
        'wp_quiz_plugin_frontend',
        'wp_quiz_plugin_frontend_answer_style_section'
    );

    // Add the reset button for Answers styles
    add_settings_field(
        'wp_quiz_plugin_frontend_answer_reset_defaults',
        __('Reset to Default', 'wp_quiz_plugin'),
        'wp_quiz_plugin_frontend_answer_reset_defaults_callback',
        'wp_quiz_plugin_frontend',
        'wp_quiz_plugin_frontend_answer_style_section'
    );
    // Progress Bar Styles Section
    add_settings_section('wp_quiz_plugin_progress_bar_styles_section', 'Progress Bar Styles', null, 'wp_quiz_plugin_frontend');

    add_settings_field(
        'wp_quiz_plugin_progress_bar_color',
        'Progress Bar Color',
        function() {
            $color = esc_attr(get_option('wp_quiz_plugin_progress_bar_color', '#4CAF50'));
            echo '<input type="text" id="wp_quiz_plugin_progress_bar_color" name="wp_quiz_plugin_progress_bar_color" value="' . $color . '" class="wp-color-picker-field" data-default-color="#4CAF50">';
        },
        'wp_quiz_plugin_frontend',
        'wp_quiz_plugin_progress_bar_styles_section'  // Corrected section ID
    );

    add_settings_field(
        'wp_quiz_plugin_progress_bar_background_color',
        'Progress Bar Background Color',
        function() {
            $color = esc_attr(get_option('wp_quiz_plugin_progress_bar_background_color', '#f1f1f1'));
            echo '<input type="text" id="wp_quiz_plugin_progress_bar_background_color" name="wp_quiz_plugin_progress_bar_background_color" value="' . $color . '" class="wp-color-picker-field" data-default-color="#f1f1f1">';
        },
        'wp_quiz_plugin_frontend',
        'wp_quiz_plugin_progress_bar_styles_section'  // Corrected section ID
    );

    // Frontend Quiz Styles Section
    add_settings_section(
        'wp_quiz_plugin_frontend_styles_section', // Made sure this ID is unique
        'Frontend Quiz Buttons Styles',
        null,
        'wp_quiz_plugin_frontend'
    );

    // add_settings_field(
    //     'wp_quiz_plugin_font_family',
    //     'Font Family',
    //     'wp_quiz_plugin_font_family_callback',
    //     'wp_quiz_plugin_frontend',
    //     'wp_quiz_plugin_frontend_styles_section'  // Corrected section ID
    // );

    // add_settings_field(
    //     'wp_quiz_plugin_font_color',
    //     'Font Color',
    //     'wp_quiz_plugin_font_color_callback',
    //     'wp_quiz_plugin_frontend',
    //     'wp_quiz_plugin_frontend_styles_section'  // Corrected section ID
    // );

    add_settings_field(
        'wp_quiz_plugin_background_color',
        'Background Color',
        'wp_quiz_plugin_background_color_callback',
        'wp_quiz_plugin_frontend',
        'wp_quiz_plugin_frontend_styles_section'  // Corrected section ID
    );

    add_settings_field(
        'wp_quiz_plugin_button_background_color',
        'Button Background Color',
        'wp_quiz_plugin_button_background_color_callback',
        'wp_quiz_plugin_frontend',
        'wp_quiz_plugin_frontend_styles_section'  // Corrected section ID
    );

    add_settings_field(
        'wp_quiz_plugin_button_text_color',
        'Button Text Color',
        'wp_quiz_plugin_button_text_color_callback',
        'wp_quiz_plugin_frontend',
        'wp_quiz_plugin_frontend_styles_section'  // Corrected section ID
    );
}
add_action('admin_init', 'wp_quiz_plugin_frontend_styles_settings_init');

// Callback functions for each field
// function wp_quiz_plugin_font_family_callback() {
//     $font_family = get_option('wp_quiz_plugin_font_family', 'Arial');
//     echo '<input type="text" name="wp_quiz_plugin_font_family" value="' . esc_attr($font_family) . '" class="regular-text">';
// }

// function wp_quiz_plugin_font_color_callback() {
//     $font_color = get_option('wp_quiz_plugin_font_color', '#000000');
//     echo '<input type="text" name="wp_quiz_plugin_font_color" value="' . esc_attr($font_color) . '" class="wp-color-picker-field" data-default-color="#000000">';
// }

function wp_quiz_plugin_background_color_callback() {
    $background_color = get_option('wp_quiz_plugin_background_color', '#ffffff');
    echo '<input type="text" name="wp_quiz_plugin_background_color" value="' . esc_attr($background_color) . '" class="wp-color-picker-field" data-default-color="#ffffff">';
}

function wp_quiz_plugin_button_background_color_callback() {
    $button_background_color = get_option('wp_quiz_plugin_button_background_color', '#007bff');
    echo '<input type="text" name="wp_quiz_plugin_button_background_color" value="' . esc_attr($button_background_color) . '" class="wp-color-picker-field" data-default-color="#007bff">';
}

function wp_quiz_plugin_button_text_color_callback() {
    $button_text_color = get_option('wp_quiz_plugin_button_text_color', '#ffffff');
    echo '<input type="text" name="wp_quiz_plugin_button_text_color" value="' . esc_attr($button_text_color) . '" class="wp-color-picker-field" data-default-color="#ffffff">';
}
// Callback function for font family
function wp_quiz_plugin_frontend_question_font_family_callback() {
    $font_family = get_option('wp_quiz_plugin_frontend_question_font_family', 'Arial');
    $fonts = array(
        'Arial' => 'Arial',
        'Helvetica' => 'Helvetica',
        'Times New Roman' => 'Times New Roman',
        'Courier New' => 'Courier New',
        'Georgia' => 'Georgia',
        'Verdana' => 'Verdana',
        'Trebuchet MS' => 'Trebuchet MS',
        'Lucida Sans' => 'Lucida Sans'
    );
    
    echo '<select id="wp_quiz_plugin_frontend_question_font_family" name="wp_quiz_plugin_frontend_question_font_family" class="regular-text">';
    foreach ($fonts as $font_key => $font_label) {
        echo '<option value="' . esc_attr($font_key) . '" ' . selected($font_family, $font_key, false) . '>' . esc_html($font_label) . '</option>';
    }
    echo '</select>';
}


// Callback function for font size
function wp_quiz_plugin_frontend_question_font_size_callback() {
    $font_size = get_option('wp_quiz_plugin_frontend_question_font_size', '16px');
    echo '<input type="text" id="wp_quiz_plugin_frontend_question_font_size" name="wp_quiz_plugin_frontend_question_font_size" value="' . esc_attr($font_size) . '" class="regular-text">';
}

// Callback function for font color
function wp_quiz_plugin_frontend_question_font_color_callback() {
    $font_color = get_option('wp_quiz_plugin_frontend_question_font_color', '#000000');
    echo '<input type="text" id="wp_quiz_plugin_frontend_question_font_color" name="wp_quiz_plugin_frontend_question_font_color" value="' . esc_attr($font_color) . '" class="wp-color-picker-field" data-default-color="#000000">';
}

// Callback function for Reset to Default button
function wp_quiz_plugin_frontend_question_reset_defaults_callback() {
    echo '<button type="button" class="button-secondary" id="wp_quiz_plugin_reset_frontend_question_styles">' . __('Reset to Default', 'wp_quiz_plugin') . '</button>';
    
    // JavaScript to handle resetting
    ?>
    <script>
    jQuery(document).ready(function($) {
        $('#wp_quiz_plugin_reset_frontend_question_styles').on('click', function() {
            // Reset to default values
            $('#wp_quiz_plugin_frontend_question_font_family').val('Arial');
            $('#wp_quiz_plugin_frontend_question_font_size').val('16px');
            $('#wp_quiz_plugin_frontend_question_font_color').wpColorPicker('color', '#000000');
        });
    });
    </script>
    <?php
}
// ========================
// Callback function for answer font-family
function wp_quiz_plugin_frontend_answer_font_family_callback() {
    $font_family = get_option('wp_quiz_plugin_frontend_answer_font_family', 'Arial');
    $fonts = array(
        'Arial' => 'Arial',
        'Helvetica' => 'Helvetica',
        'Times New Roman' => 'Times New Roman',
        'Courier New' => 'Courier New',
        'Georgia' => 'Georgia',
        'Verdana' => 'Verdana',
        'Trebuchet MS' => 'Trebuchet MS',
        'Lucida Sans' => 'Lucida Sans'
    );
    
    echo '<select id="wp_quiz_plugin_frontend_answer_font_family" name="wp_quiz_plugin_frontend_answer_font_family" class="regular-text">';
    foreach ($fonts as $font_key => $font_label) {
        echo '<option value="' . esc_attr($font_key) . '" ' . selected($font_family, $font_key, false) . '>' . esc_html($font_label) . '</option>';
    }
    echo '</select>';
}

// Callback function for answer font color
function wp_quiz_plugin_frontend_answer_font_color_callback() {
    $font_color = get_option('wp_quiz_plugin_frontend_answer_font_color', '#000000');
    echo '<input type="text" id="wp_quiz_plugin_frontend_answer_font_color" name="wp_quiz_plugin_frontend_answer_font_color" value="' . esc_attr($font_color) . '" class="wp-color-picker-field" data-default-color="#000000">';
}

// Callback function for answer font size
function wp_quiz_plugin_frontend_answer_font_size_callback() {
    $font_size = get_option('wp_quiz_plugin_frontend_answer_font_size', '16px');
    echo '<input type="text" id="wp_quiz_plugin_frontend_answer_font_size" name="wp_quiz_plugin_frontend_answer_font_size" value="' . esc_attr($font_size) . '" class="regular-text">';
}

// Callback function for reset button (Answers)
function wp_quiz_plugin_frontend_answer_reset_defaults_callback() {
    echo '<button type="button" class="button-secondary" id="wp_quiz_plugin_reset_answer_styles">' . __('Reset to Default', 'wp_quiz_plugin') . '</button>';
    
    // JavaScript to handle resetting
    ?>
    <script>
    jQuery(document).ready(function($) {
        $('#wp_quiz_plugin_reset_answer_styles').on('click', function() {
            // Reset to default values for answers
            $('#wp_quiz_plugin_frontend_answer_font_family').val('Arial');
            $('#wp_quiz_plugin_frontend_answer_font_color').wpColorPicker('color', '#000000');
            $('#wp_quiz_plugin_frontend_answer_font_size').val('16px');
        });
    });
    </script>
    <?php
}
function wp_quiz_plugin_notification_styles_settings_init() {
    // Register the settings for notification text and background colors
    register_setting('wp_quiz_plugin_quizzes_settings', 'wp_quiz_plugin_notification_text_color');
    register_setting('wp_quiz_plugin_quizzes_settings', 'wp_quiz_plugin_notification_background_color');
    register_setting('wp_quiz_plugin_quizzes_settings', 'wp_quiz_plugin_notification_font_size');
    register_setting('wp_quiz_plugin_quizzes_settings', 'wp_quiz_plugin_notification_font_family');


    // Add a new section for Notification Settings
    add_settings_section(
        'wp_quiz_plugin_notification_styles_section',  // Section ID
        'Image size Notification Settings',  // Title of the section
        null,  // Optional description callback (null if not needed)
        'wp_quiz_plugin'  // Page/Settings group where this section belongs
    );

    // Add a field for Notification Text Color
    add_settings_field(
        'wp_quiz_plugin_notification_text_color',  // Option ID
        'Notification Text & Icon Color',  // Field label
        'wp_quiz_plugin_notification_text_color_callback',  // Callback function to render the field
        'wp_quiz_plugin',  // Page/Settings group where the field belongs
        'wp_quiz_plugin_notification_styles_section'  // Section ID where this field will be shown
    );

    // Add a field for Notification Background Color
    add_settings_field(
        'wp_quiz_plugin_notification_background_color',
        'Notification Background Color',
        'wp_quiz_plugin_notification_background_color_callback',
        'wp_quiz_plugin',
        'wp_quiz_plugin_notification_styles_section'
    );
        // Add a field for Notification Font Size
    add_settings_field(
        'wp_quiz_plugin_notification_font_size',
        'Notification Text Font Size',
        'wp_quiz_plugin_notification_font_size_callback',
        'wp_quiz_plugin',
        'wp_quiz_plugin_notification_styles_section'
    );

    // Add a field for Notification Font Family
    add_settings_field(
        'wp_quiz_plugin_notification_font_family',
        'Notification Text Font Family',
        'wp_quiz_plugin_notification_font_family_callback',
        'wp_quiz_plugin',
        'wp_quiz_plugin_notification_styles_section'
    );
}
add_action('admin_init', 'wp_quiz_plugin_notification_styles_settings_init');
// Callback for Notification Text Color
function wp_quiz_plugin_notification_text_color_callback() {
    $text_color = get_option('wp_quiz_plugin_notification_text_color', '#000000');
    echo '<input type="text" id="wp_quiz_plugin_notification_text_color" name="wp_quiz_plugin_notification_text_color" value="' . esc_attr($text_color) . '" class="wp-color-picker-field" data-default-color="#000000">';
}

// Callback for Notification Background Color
function wp_quiz_plugin_notification_background_color_callback() {
    $background_color = get_option('wp_quiz_plugin_notification_background_color', '#ffffff');
    echo '<input type="text" id="wp_quiz_plugin_notification_background_color" name="wp_quiz_plugin_notification_background_color" value="' . esc_attr($background_color) . '" class="wp-color-picker-field" data-default-color="#ffffff">';
}
// Callback for Notification Text Font Size
function wp_quiz_plugin_notification_font_size_callback() {
    $font_size = get_option('wp_quiz_plugin_notification_font_size', '16px');
    echo '<input type="text" id="wp_quiz_plugin_notification_font_size" name="wp_quiz_plugin_notification_font_size" value="' . esc_attr($font_size) . '" class="regular-text">';
    echo '<p class="description">Set the font size for the notification text (e.g., "16px").</p>';
}

// Callback for Notification Text Font Family
function wp_quiz_plugin_notification_font_family_callback() {
    $font_family = get_option('wp_quiz_plugin_notification_font_family', 'Arial');
    $fonts = array(
        'Arial' => 'Arial',
        'Helvetica' => 'Helvetica',
        'Times New Roman' => 'Times New Roman',
        'Courier New' => 'Courier New',
        'Georgia' => 'Georgia',
        'Verdana' => 'Verdana',
        'Trebuchet MS' => 'Trebuchet MS',
        'Lucida Sans' => 'Lucida Sans'
    );
    
    echo '<select id="wp_quiz_plugin_notification_font_family" name="wp_quiz_plugin_notification_font_family" class="regular-text">';
    foreach ($fonts as $font_key => $font_label) {
        echo '<option value="' . esc_attr($font_key) . '" ' . selected($font_family, $font_key, false) . '>' . esc_html($font_label) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">Choose the font family for the notification text.</p>';
}

// ====== OPEN API PROMPT SETTINGS =====
// Register settings for custom prompt templates
function wp_quiz_plugin_prompt_settings_init() {
    register_setting('wp_quiz_plugin_general_settings', 'wp_quiz_plugin_mcq_prompt_template');
    register_setting('wp_quiz_plugin_general_settings', 'wp_quiz_plugin_tf_prompt_template');
    register_setting('wp_quiz_plugin_general_settings', 'wp_quiz_plugin_text_prompt_template');

    // Add settings section for prompt templates
    add_settings_section(
        'wp_quiz_plugin_prompt_settings_section',
        __('Prompt Customization', 'wp_quiz_plugin'),
        null,
        'wp_quiz_plugin_general'
    );

    // MCQ Prompt Template
    add_settings_field(
        'wp_quiz_plugin_mcq_prompt_template',
        __('MCQ Prompt Template', 'wp_quiz_plugin'),
        'wp_quiz_plugin_mcq_prompt_template_callback',
        'wp_quiz_plugin_general',
        'wp_quiz_plugin_prompt_settings_section'
    );

    // True/False Prompt Template
    add_settings_field(
        'wp_quiz_plugin_tf_prompt_template',
        __('True/False Prompt Template', 'wp_quiz_plugin'),
        'wp_quiz_plugin_tf_prompt_template_callback',
        'wp_quiz_plugin_general',
        'wp_quiz_plugin_prompt_settings_section'
    );

    // Text Answer Prompt Template
    add_settings_field(
        'wp_quiz_plugin_text_prompt_template',
        __('Text Answer Prompt Template', 'wp_quiz_plugin'),
        'wp_quiz_plugin_text_prompt_template_callback',
        'wp_quiz_plugin_general',
        'wp_quiz_plugin_prompt_settings_section'
    );
}
add_action('admin_init', 'wp_quiz_plugin_prompt_settings_init');

// Callback function for MCQ prompt template
function wp_quiz_plugin_mcq_prompt_template_callback() {
    // Define the default prompt with newline characters
    $default_prompt = "Generate a quiz question in the same language as the provided prompt. For example, if the prompt is in Polish, generate the question in Polish, and if the prompt is in English, generate the question in English. Use the following format:\nQuestion: [Your question text]\nAnswer Options: A) [Option A], B) [Option B], C) [Option C], D) [Option D]\nCorrect Answer: [A) Option A/B) Option B/C) Option C/D) Option D]";
    
    // Retrieve the existing value or use the default prompt
    $mcq_prompt = get_option('wp_quiz_plugin_mcq_prompt_template', $default_prompt);
    
    // Output the textarea with proper escaping
    ?>
    <textarea id="wp_quiz_plugin_mcq_prompt_template" name="wp_quiz_plugin_mcq_prompt_template" class="large-text" rows="10"><?php echo esc_textarea(str_replace('\n', "\n", $mcq_prompt)); ?></textarea>
    <button type="button" onclick="document.getElementById('wp_quiz_plugin_mcq_prompt_template').value='<?php echo esc_js(str_replace('\n', "\\n", $default_prompt)); ?>';" class="button-secondary"><?php _e('Use Default', 'wp_quiz_plugin'); ?></button>
    <p class="description"><?php _e('Customize the prompt template for MCQ questions. Use variables like [Your question text], [Option A], [Correct Answer].', 'wp_quiz_plugin'); ?></p>
    <?php
}


// Callback function for T/F prompt template
function wp_quiz_plugin_tf_prompt_template_callback() {
    // Define the default prompt with newline characters
    $default_prompt = "Generate a True or False quiz question in the same language as the provided prompt. For example, if the prompt is in Polish, generate the question in Polish, and if the prompt is in English, generate the question in English. Use the following format:\nQuestion: [Your question text]\nCorrect Answer: [True/False]";
    
    // Retrieve the existing value or use the default prompt
    $tf_prompt = get_option('wp_quiz_plugin_tf_prompt_template', $default_prompt);
    
    // Output the textarea with proper escaping
    ?>
    <textarea id="wp_quiz_plugin_tf_prompt_template" name="wp_quiz_plugin_tf_prompt_template" class="large-text" rows="5"><?php echo esc_textarea(str_replace('\n', "\n", $tf_prompt)); ?></textarea>
    <button type="button" onclick="document.getElementById('wp_quiz_plugin_tf_prompt_template').value='<?php echo esc_js(str_replace('\n', "\\n", $default_prompt)); ?>';" class="button-secondary"><?php _e('Use Default', 'wp_quiz_plugin'); ?></button>
    <p class="description"><?php _e('Customize the prompt template for True/False questions.', 'wp_quiz_plugin'); ?></p>
    <?php
}


// Callback function for Text prompt template
function wp_quiz_plugin_text_prompt_template_callback() {
    // Define the default prompt with newline characters
    $default_prompt = "Generate a quiz question that requires a text answer in the same language as the provided prompt. For example, if the prompt is in Polish, generate the question in Polish, and if the prompt is in English, generate the question in English. Use the following format:\nQuestion: [Your question text]\nCorrect Answer: [Your text answer]";
    
    // Retrieve the existing value or use the default prompt
    $text_prompt = get_option('wp_quiz_plugin_text_prompt_template', $default_prompt);
    
    // Output the textarea with proper escaping
    ?>
    <textarea id="wp_quiz_plugin_text_prompt_template" name="wp_quiz_plugin_text_prompt_template" class="large-text" rows="5"><?php echo esc_textarea(str_replace('\n', "\n", $text_prompt)); ?></textarea>
    <button type="button" onclick="document.getElementById('wp_quiz_plugin_text_prompt_template').value='<?php echo esc_js(str_replace('\n', "\\n", $default_prompt)); ?>';" class="button-secondary"><?php _e('Use Default', 'wp_quiz_plugin'); ?></button>
    <p class="description"><?php _e('Customize the prompt template for Text answer questions.', 'wp_quiz_plugin'); ?></p>
    <?php
}


?>
