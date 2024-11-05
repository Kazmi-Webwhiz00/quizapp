<?php

include_once plugin_dir_path(__FILE__) . 'utils/constants.php'; 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Add Quiz Catergory meta box

// Add the meta box to post editing screen
function add_quiz_category_meta_box() {
    $select_category_label_text = get_option('wp_quiz_plugin_category_lable_text', 'Quiz Category');
    add_meta_box(
        'quiz_category_meta_box', // Unique ID for meta box
        __($select_category_label_text, 'wp-quiz-plugin'),           // Meta box title
        'render_quiz_category_meta_box', // Callback function
        'quizzes',                 // Post type (custom post type 'quizzes')
        'normal',                    // Context (display on side)
        'high'                     // Priority
    );
}
add_action('add_meta_boxes', 'add_quiz_category_meta_box');

function remove_default_quiz_category_meta_box() {
    // Remove the default meta box for the quiz_category taxonomy
    remove_meta_box('quiz_categorydiv', 'quizzes', 'normal'); // 'quizzes' is your custom post type
}
add_action('admin_menu', 'remove_default_quiz_category_meta_box', 999);

function render_quiz_category_meta_box($post) {
    $select_category_school_text = get_option('wp_quiz_plugin_category_select_School_text', 'Select School');
    $select_category_class_text = get_option('wp_quiz_plugin_category_select_class_text', 'Select Class');
    $select_category_subject_text = get_option('wp_quiz_plugin_category_select_Subject_text', '----------');

    // Retrieve associated terms for the quizzes post
    $selected_schools = wp_get_post_terms($post->ID, 'quiz_category', array('fields' => 'ids', 'parent' => 0)); // Top-level (School)
    $selected_school = !empty($selected_schools) ? $selected_schools[0] : '';

    // Retrieve child categories (Class) only if a school is selected
    $selected_classes = !empty($selected_school) ? wp_get_post_terms($post->ID, 'quiz_category', array('parent' => $selected_school)) : array();
    $selected_class = !empty($selected_classes) ? $selected_classes[0]->term_id : '';

    // Retrieve child categories (Subject) only if a class is selected
    $selected_subjects = !empty($selected_class) ? wp_get_post_terms($post->ID, 'quiz_category', array('parent' => $selected_class)) : array();
    $selected_subject = !empty($selected_subjects) ? $selected_subjects[0]->term_id : '';
    
    // Fetch all top-level categories (Schools)
    $schools = get_terms(array(
        'taxonomy' => 'quiz_category',
        'parent' => 0,
        'hide_empty' => false,
    ));

    // Fetch classes for the selected school
    $classes = [];
    if ($selected_school) {
        $classes = get_terms(array(
            'taxonomy' => 'quiz_category',
            'parent' => $selected_school,
            'hide_empty' => false,
        ));
    }


    // Fetch subjects for the selected class
    $subjects = [];
    if ($selected_class) {
        $subjects = get_terms(array(
            'taxonomy' => 'quiz_category',
            'parent' => $selected_class,
            'hide_empty' => false,
        ));
    }

    error_log("count of the subejcts");
    error_log(empty($subjects));
    ?>
    <div class="quiz-category-dropdowns">
        <label for="selected_school"><?php _e($select_category_school_text, 'wp-quiz-plugin'); ?></label>
        <select name="selected_school" id="selected_school">
            <option value=""><?php _e('----------', 'wp-quiz-plugin'); ?></option>
            <?php foreach ($schools as $school) { ?>
                <option value="<?php echo esc_attr($school->term_id); ?>" <?php selected($selected_school, $school->term_id); ?>>
                    <?php echo esc_html($school->name); ?>
                </option>
            <?php } ?>
        </select>

        <div id="class_select_container" <?php if (empty($classes)) echo 'style="display:none;"'; ?>>
            <select name="selected_class" id="selected_class">
                <option value=""><?php _e('----------', 'wp-quiz-plugin');  ?></option>
                <?php foreach ($classes as $class) { ?>
                    <option value="<?php echo esc_attr($class->term_id); ?>" <?php selected($selected_class, $class->term_id); ?>>
                        <?php echo esc_html($class->name); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div id="subject_select_container" <?php if (empty($subjects)) echo 'style="display:none;"'; ?>>
            <select name="selected_subject" id="selected_subject">
                <option value=""><?php _e('----------', 'wp-quiz-plugin'); ?></option>
                <?php foreach ($subjects as $subject) { ?>
                    <option value="<?php echo esc_attr($subject->term_id); ?>" <?php selected($selected_subject, $subject->term_id); ?>>
                        <?php echo esc_html($subject->name); ?>
                    </option>
                <?php } ?>
            </select>
        </div>
    </div>

    <?php
    // Add a nonce for security
    wp_nonce_field(basename(__FILE__), 'quiz_category_nonce');
}


add_action('add_meta_boxes', 'add_quiz_category_meta_box');
function quiz_category_cascade_script() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {

            // On change of school dropdown, fetch and populate classes
            $('#selected_school').on('change', function() {
                var selectedSchool = $(this).val();
                $('#class_select_container').hide(); // Hide class and subject containers
                $('#subject_select_container').hide(); // Hide subject container

                if (selectedSchool) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'fetch_quiz_classes',
                            parent_id: selectedSchool
                        },
                        success: function(response) {
                            $('#selected_class').html(response);
                            if ($('#selected_class option').length > 1) {
                                $('#class_select_container').show(); // Show class container if options exist
                            }
                            $('#selected_subject').html('<option value=""><?php _e('----------', 'wp-quiz-plugin'); ?></option>');
                        }
                    });
                }
            });

            // On change of class dropdown, fetch and populate subjects
            $('#selected_class').on('change', function() {
                var selectedClass = $(this).val();
                $('#subject_select_container').hide(); // Hide subject container

                if (selectedClass) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'fetch_quiz_subjects',
                            parent_id: selectedClass
                        },
                        success: function(response) {
                            $('#selected_subject').html(response);
                            if ($('#selected_subject option').length > 1) {
                                $('#subject_select_container').show(); // Show subject container if options exist
                            }
                        }
                    });
                }
            });
        });
    </script>
    <?php
}

add_action('admin_footer', 'quiz_category_cascade_script');


// Fetch classes (child categories of selected school)
function fetch_quiz_classes() {
    $parent_id = intval($_POST['parent_id']);
    
    if ($parent_id) {
        $classes = get_terms(array(
            'taxonomy' => 'quiz_category',
            'parent' => $parent_id,
            'hide_empty' => false,
        ));

        echo '<option value="">' . __('----------', 'wp-quiz-plugin') . '</option>';
        foreach ($classes as $class) {
            echo '<option value="' . $class->term_id . '">' . $class->name . '</option>';
        }
    } else {
        echo '<option value="">' . __('----------', 'wp-quiz-plugin') . '</option>';
    }

    wp_die();
}
add_action('wp_ajax_fetch_quiz_classes', 'fetch_quiz_classes');

// Fetch subjects (child categories of selected class)
function fetch_quiz_subjects() {
    $parent_id = intval($_POST['parent_id']);
    
    if ($parent_id) {
        $subjects = get_terms(array(
            'taxonomy' => 'quiz_category',
            'parent' => $parent_id,
            'hide_empty' => false,
        ));

        echo '<option value="">' . __('----------', 'wp-quiz-plugin') . '</option>';
        foreach ($subjects as $subject) {
            echo '<option value="' . $subject->term_id . '">' . $subject->name . '</option>';
        }
    } else {
        echo '<option value="">' . __('----------', 'wp-quiz-plugin') . '</option>';
    }

    wp_die();
}
add_action('wp_ajax_fetch_quiz_subjects', 'fetch_quiz_subjects');

function save_quiz_category_meta_box($post_id) {
    if (!isset($_POST['quiz_category_nonce']) || !wp_verify_nonce($_POST['quiz_category_nonce'], basename(__FILE__))) {
        return $post_id;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    // Only save for 'quizzes' post type
    if ('quizzes' !== $_POST['post_type']) {
        return $post_id;
    }

    // Save the selected school, class, and subject as terms
    if (isset($_POST['selected_school'])) {
        wp_set_post_terms($post_id, intval($_POST['selected_school']), 'quiz_category');
    }
    if (isset($_POST['selected_class'])) {
        wp_set_post_terms($post_id, intval($_POST['selected_class']), 'quiz_category', true);
    }
    if (isset($_POST['selected_subject'])) {
        wp_set_post_terms($post_id, intval($_POST['selected_subject']), 'quiz_category', true);
    }
}
add_action('save_post', 'save_quiz_category_meta_box');


// Gernal Post Category
function register_quiz_categories() {
    $labels = array(
        'name'              => _x('Quiz Categories', 'taxonomy general name', 'wp-quiz-plugin'),
        'singular_name'     => _x('Quiz Category', 'taxonomy singular name', 'wp-quiz-plugin'),
        'search_items'      => __('Search Quiz Categories', 'wp-quiz-plugin'),
        'all_items'         => __('All Quiz Categories', 'wp-quiz-plugin'),
        'parent_item'       => __('Parent Category', 'wp-quiz-plugin'),
        'parent_item_colon' => __('Parent Category:', 'wp-quiz-plugin'),
        'edit_item'         => __('Edit Category', 'wp-quiz-plugin'),
        'update_item'       => __('Update Category', 'wp-quiz-plugin'),
        'add_new_item'      => __('Add New Category', 'wp-quiz-plugin'),
        'new_item_name'     => __('New Category Name', 'wp-quiz-plugin'),
        'menu_name'         => __('Quiz Categories', 'wp-quiz-plugin'),
    );

    $args = array(
        'hierarchical'      => true, // This makes it hierarchical (like categories)
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'quiz-category'),
    );

    register_taxonomy('quiz_category', array('quizzes'), $args); // 'quizzes' is your custom post type
}

add_action('init', 'register_quiz_categories');



function add_quiz_category_single_dropdown() {
    ?>
    <div class="form-field">
        <label for="quiz_type"><?php _e('Category Type', 'wp-quiz-plugin'); ?></label>
        <select name="quiz_type" id="quiz_type">
            <option value=""><?php _e('Select Category Type', 'wp-quiz-plugin'); ?></option> <!-- Placeholder option -->
            <option value="school"><?php _e('School', 'wp-quiz-plugin'); ?></option>
            <option value="subject"><?php _e('Subject', 'wp-quiz-plugin'); ?></option>
            <option value="class"><?php _e('Class', 'wp-quiz-plugin'); ?></option>
        </select>
        <p><?php _e('Select the category type (School, Subject, or Class).', 'wp-quiz-plugin'); ?></p>
    </div>
    <?php
}
add_action('quiz_category_add_form_fields', 'add_quiz_category_single_dropdown'); // For Add form


function edit_quiz_category_single_dropdown($term) {
    $quiz_type = get_term_meta($term->term_id, 'quiz_type', true); // Get the stored value.
    ?>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="quiz_type"><?php _e('Category Type', 'wp-quiz-plugin'); ?></label>
        </th>
        <td>
            <select name="quiz_type" id="quiz_type">
                <option value=""><?php _e('Select Category Type', 'wp-quiz-plugin'); ?></option> <!-- Placeholder option -->
                <option value="school" <?php selected($quiz_type, 'school'); ?>><?php _e('School', 'wp-quiz-plugin'); ?></option>
                <option value="subject" <?php selected($quiz_type, 'subject'); ?>><?php _e('Subject', 'wp-quiz-plugin'); ?></option>
                <option value="class" <?php selected($quiz_type, 'class'); ?>><?php _e('Class', 'wp-quiz-plugin'); ?></option>
            </select>
            <p class="description"><?php _e('Select the category type (School, Subject, or Class).', 'wp-quiz-plugin'); ?></p>
        </td>
    </tr>
    <?php
}
add_action('quiz_category_edit_form_fields', 'edit_quiz_category_single_dropdown'); // For Edit form

// Save the dropdown field value when the category is created or edited.
function save_quiz_category_single_dropdown($term_id) {
    if (isset($_POST['quiz_type'])) {
        update_term_meta($term_id, 'quiz_type', sanitize_text_field($_POST['quiz_type']));
    }
}
add_action('created_quiz_category', 'save_quiz_category_single_dropdown', 10, 2);
add_action('edited_quiz_category', 'save_quiz_category_single_dropdown', 10, 2);

// This code will show the quiz type in table

// Add custom column to the quiz category admin table.
function add_quiz_category_columns($columns) {
    // Add a new column after the 'Description' column
    $columns['quiz_type'] = __('Quiz Type', 'wp-quiz-plugin'); 
    return $columns;
}
add_filter('manage_edit-quiz_category_columns', 'add_quiz_category_columns');

// Populate the custom column with data.
function manage_quiz_category_custom_column($content, $column_name, $term_id) {
    if ($column_name === 'quiz_type') {
        // Get the quiz_type term meta value.
        $quiz_type = get_term_meta($term_id, 'quiz_type', true);
        
        // Display a user-friendly value based on the stored metadata.
        switch ($quiz_type) {
            case 'school':
                $content = __('School', 'wp-quiz-plugin');
                break;
            case 'subject':
                $content = __('Subject', 'wp-quiz-plugin');
                break;
            case 'class':
                $content = __('Class', 'wp-quiz-plugin');
                break;
            default:
                $content = __('__', 'wp-quiz-plugin'); // Display a default message if not set
        }
    }
    return $content;
}
add_filter('manage_quiz_category_custom_column', 'manage_quiz_category_custom_column', 10, 3);

// Register Custom Post Type 'Quizzes'
function register_quizzes_post_type() {

    // Fetch the dynamic settings from the WordPress options table
    $quizzes_menu_text = get_option('wp_quiz_plugin_menu_quizzes_text', __('Quizzes', 'wp-quiz-plugin'));
    $all_quizzes_menu_text = get_option('wp_quiz_plugin_menu_all_quizzes_text', __('All Quizzes', 'wp-quiz-plugin'));
    $add_new_menu_text = get_option('wp_quiz_plugin_menu_add_new_text', __('Add New', 'wp-quiz-plugin'));


    $labels = array(
        'name'                  => _x($quizzes_menu_text, 'Post Type General Name', 'wp-quiz-plugin'),
        'singular_name'         => _x('Quiz', 'Post Type Singular Name', 'wp-quiz-plugin'),
        'menu_name'             => __($quizzes_menu_text, 'wp-quiz-plugin'),
        'name_admin_bar'        => __('Quiz', 'wp-quiz-plugin'),
        'archives'              => __('Quiz Archives', 'wp-quiz-plugin'),
        'attributes'            => __('Quiz Attributes', 'wp-quiz-plugin'),
        'parent_item_colon'     => __('Parent Quiz:', 'wp-quiz-plugin'),
        'all_items'             => __($all_quizzes_menu_text, 'wp-quiz-plugin'),
        'add_new_item'          => __('Add New Quiz', 'wp-quiz-plugin'),
        'add_new'               => __($add_new_menu_text, 'wp-quiz-plugin'),
        'new_item'              => __('New Quiz', 'wp-quiz-plugin'),
        'edit_item'             => __('Edit Quiz', 'wp-quiz-plugin'),
        'update_item'           => __('Update Quiz', 'wp-quiz-plugin'),
        'view_item'             => __('View Quiz', 'wp-quiz-plugin'),
        'view_items'            => __('View Quizzes', 'wp-quiz-plugin'),
        'search_items'          => __('Search Quiz', 'wp-quiz-plugin'),
        'not_found'             => __('Not found', 'wp-quiz-plugin'),
        'not_found_in_trash'    => __('Not found in Trash', 'wp-quiz-plugin'),
        'featured_image'        => __('Featured Image', 'wp-quiz-plugin'),
        'set_featured_image'    => __('Set featured image', 'wp-quiz-plugin'),
        'remove_featured_image' => __('Remove featured image', 'wp-quiz-plugin'),
        'use_featured_image'    => __('Use as featured image', 'wp-quiz-plugin'),
        'insert_into_item'      => __('Insert into quiz', 'wp-quiz-plugin'),
        'uploaded_to_this_item' => __('Uploaded to this quiz', 'wp-quiz-plugin'),
        'items_list'            => __('Quizzes list', 'wp-quiz-plugin'),
        'items_list_navigation' => __('Quizzes list navigation', 'wp-quiz-plugin'),
        'filter_items_list'     => __('Filter quizzes list', 'wp-quiz-plugin'),
    );

    $args = array(
        'label'                 => __('Quiz', 'wp-quiz-plugin'),
        'description'           => __('Custom Post Type for Quizzes', 'wp-quiz-plugin'),
        'labels'                => $labels,
        'supports'              => array('title'),
        'taxonomies' => array('subject', 'standard'), // Ensure this is set to your taxonomy slugs
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-welcome-learn-more',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'capability_type'       => 'post',  // Use the default 'post' capabilities
        'map_meta_cap'          => true,
        'rewrite'               => array('slug' => get_option('wp_quiz_plugin_quizzes_url_slug', 'quizzes')),
    );

    register_post_type('quizzes', $args);
}
add_action('init', 'register_quizzes_post_type', 0);

// Add meta box for quiz description
function add_quiz_description_meta_box() {
    add_meta_box(
        'quiz_description_meta_box',
        __('Quiz Description', 'wp-quiz-plugin'),
        'display_quiz_description_meta_box',
        'quizzes',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_quiz_description_meta_box');

function display_quiz_description_meta_box($post) {
    $description = get_post_meta($post->ID, '_quiz_description', true);
    ?>
    <textarea name="quiz_description" style="width:100%; height:100px;"><?php echo esc_textarea($description); ?></textarea>
    <?php
}

// Save the quiz description
function save_quiz_description_meta_box($post_id) {
    if (array_key_exists('quiz_description', $_POST)) {
        update_post_meta(
            $post_id,
            '_quiz_description',
            sanitize_text_field($_POST['quiz_description'])
        );
    }
}
add_action('save_post', 'save_quiz_description_meta_box');



// Hook to modify post data before saving
function wp_quiz_plugin_validate_taxonomies($data, $postarr) {
    // Only apply to 'quizzes' post type
    if ($data['post_type'] !== 'quizzes') {
        return $data;
    }

    // Verify user capabilities
    if (!current_user_can('edit_post', $postarr['ID'])) {
        return $data;
    }

    // Skip validation if the post is being deleted or auto-drafted
    if (in_array($data['post_status'], array('trash', 'auto-draft', 'inherit'), true)) {
        return $data;
    }

    // Also, skip validation if the current action is 'trash', 'delete', or 'untrash'
    if (isset($_REQUEST['action']) && in_array($_REQUEST['action'], array('trash', 'delete', 'untrash'), true)) {
        return $data;
    }

    // **Retrieve 'quiz_listing_visibility_status' Meta Value**
    $quiz_visibility = '';

    // Attempt to get the value from $postarr['meta_input']
    if (isset($postarr['meta_input']['quiz_listing_visibility_status'])) {
        $quiz_visibility = sanitize_text_field($postarr['meta_input']['quiz_listing_visibility_status']);
    } elseif (isset($_POST['quiz_visibility'])) {
        // If not in meta_input, try to get it from $_POST
        $quiz_visibility = sanitize_text_field($_POST['quiz_visibility']);
    } else {
        // For existing posts, retrieve the current meta value
        $post_id = isset($postarr['ID']) ? intval($postarr['ID']) : 0;
        if ($post_id) {
            $quiz_visibility = get_post_meta($post_id, 'quiz_listing_visibility_status', true);
        }
    }

    // If 'quiz_visibility' is 'private', skip validation
    if ($quiz_visibility === 'private') {
        return $data;
    }

    // Initialize error messages array
    $error_messages = array();

    // **Retrieve Selected Terms from $_POST**
    $selected_school    = isset($_POST['selected_school']) ? intval($_POST['selected_school']) : 0;
    $selected_class     = isset($_POST['selected_class']) ? intval($_POST['selected_class']) : 0;
    $selected_subject   = isset($_POST['selected_subject']) ? intval($_POST['selected_subject']) : 0;

    // **Begin Validation Logic**

    // Check if a School is selected
    if (empty($selected_school)) {
        $error_messages[] = __('Please select a School.', 'wp-quiz-plugin');
    } else {
        // Get Classes under the selected School
        $class_terms = get_terms(array(
            'taxonomy'   => 'quiz_category',
            'parent'     => $selected_school,
            'hide_empty' => false,
        ));

        if (!is_wp_error($class_terms) && !empty($class_terms)) {
            // If Classes exist under the School, ensure a Class is selected
            if (empty($selected_class)) {
                $error_messages[] = __('Please select a Class.', 'wp-quiz-plugin');
            } else {
                // Verify that the selected Class is indeed a child of the selected School
                $class_ids = wp_list_pluck($class_terms, 'term_id');
                if (!in_array($selected_class, $class_ids, true)) {
                    $error_messages[] = __('Selected Class is invalid for the chosen School.', 'wp-quiz-plugin');
                } else {
                    // Get Subjects under the selected Class
                    $subject_terms = get_terms(array(
                        'taxonomy'   => 'quiz_category',
                        'parent'     => $selected_class,
                        'hide_empty' => false,
                    ));

                    if (!is_wp_error($subject_terms) && !empty($subject_terms)) {
                        // If Subjects exist under the Class, ensure a Subject is selected
                        if (empty($selected_subject)) {
                            $error_messages[] = __('Please select a Subject.', 'wp-quiz-plugin');
                        } else {
                            // Verify that the selected Subject is indeed a child of the selected Class
                            $subject_ids = wp_list_pluck($subject_terms, 'term_id');
                            if (!in_array($selected_subject, $subject_ids, true)) {
                                $error_messages[] = __('Selected Subject is invalid for the chosen Class.', 'wp-quiz-plugin');
                            }
                        }
                    }
                }
            }
        }
    }

    // **End Validation Logic**

    // If there are errors, handle accordingly
    if (!empty($error_messages)) {
        // Only change the post status if attempting to publish
        if ($data['post_status'] === 'publish') {
            $data['post_status'] = 'draft';
        }

        // Store error messages in a transient for display
        set_transient('wp_quiz_plugin_errors_' . $postarr['ID'], $error_messages, 30);
    }

    return $data;
}
add_filter('wp_insert_post_data', 'wp_quiz_plugin_validate_taxonomies', 10, 2);

// Function to display the admin notice with error messages
function wp_quiz_plugin_display_errors() {
    $screen = get_current_screen();

    // Only display on the 'quizzes' post type
    if ($screen && $screen->post_type === 'quizzes') {
        global $post;
        $post_id = isset($post->ID) ? $post->ID : 0;

        if ($post_id) {
            $error_messages = get_transient('wp_quiz_plugin_errors_' . $post_id);

            if ($error_messages) {
                delete_transient('wp_quiz_plugin_errors_' . $post_id);

                echo '<div class="notice notice-error is-dismissible">';
                foreach ($error_messages as $message) {
                    echo '<p>' . esc_html($message) . '</p>';
                }
                echo '</div>';
            }
        }
    }
}
add_action('admin_notices', 'wp_quiz_plugin_display_errors');

// Limit non-admin users to see only their own quizzes in the admin area
function wp_quiz_plugin_filter_quizzes_for_non_admins($query) {
    // Check if we're in the admin area, on the quizzes post type, and it's the main query
    if (is_admin() && $query->is_main_query() && $query->get('post_type') === 'quizzes') {
        // If the user is not an administrator
        if (!current_user_can('administrator')) {
            // Modify the query to show only the quizzes created by the current user
            $query->set('author', get_current_user_id());
        }
    }
}
add_action('pre_get_posts', 'wp_quiz_plugin_filter_quizzes_for_non_admins');

// Filter the post counts to show only 'Mine' for non-admin users
function wp_quiz_plugin_adjust_quiz_counts($views) {
    
    // Check if the user is not an administrator
    if (!current_user_can('administrator') && isset($views['all'])) {
        global $current_user, $typenow;

        

        if ($typenow === 'quizzes') {
            // Get the current user ID
            $current_user_id = get_current_user_id();

            $published_count = (int)get_users_posts_count($current_user_id, 'quizzes', 'publish');

    // Update 'Published' view count to show only the current user's published posts
    if (isset($views['publish'])) {
        $views['publish'] = preg_replace('/\(.+\)/', '(' . $published_count . ')', $views['publish']);
    }

            // Get the count for posts created by the current user
            $mine_count = (int)get_users_posts_count($current_user_id, 'quizzes');
            $draft_count = (int)get_users_posts_count($current_user_id, 'quizzes', 'draft');
            $trash_count = (int)get_users_posts_count($current_user_id, 'quizzes', 'trash');

            // Update 'All' view count to be the same as 'Mine' count
            if (isset($views['all'])) {
                $views['all'] = preg_replace('/\(.+\)/', '(' . $mine_count . ')', $views['all']);
            }

            // Update 'Draft' view count to show only the current user's drafts
            if (isset($views['draft'])) {
                $views['draft'] = preg_replace('/\(.+\)/', '(' . $draft_count . ')', $views['draft']);
            }

            // Update 'Trash' view count to show only the current user's trashed posts
            if (isset($views['trash'])) {
                $views['trash'] = preg_replace('/\(.+\)/', '(' . $trash_count . ')', $views['trash']);
            }
        }
    }

    return $views;
}
add_filter('views_edit-quizzes', 'wp_quiz_plugin_adjust_quiz_counts');

// Function to get the count of posts for a specific user and post status
function get_users_posts_count($user_id, $post_type = 'post', $status = 'publish') {
    global $wpdb;

    $query = $wpdb->prepare(
        "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s AND post_status = %s AND post_author = %d",
        $post_type,
        $status,
        $user_id
    );

    return $wpdb->get_var($query);
}

// Hide 'Trash' option for non-admin users
function wp_quiz_plugin_hide_trash_for_non_admins($views) {
    if (!current_user_can('administrator') && isset($views['trash'])) {
        unset($views['trash']); // Remove the Trash view link for non-admin users
    }
    return $views;
}
add_filter('views_edit-quizzes', 'wp_quiz_plugin_hide_trash_for_non_admins');

// Limists non admin users to see only their own media files
function wp_limit_media_library_access_to_own_uploads($wp_query) {
    // Check if the user is in the admin area, is not an administrator, and if we're querying the media library
    if (is_admin() && !current_user_can('administrator') && $wp_query->get('post_type') === 'attachment') {
        // Limit media library items to only those uploaded by the current user
        $wp_query->set('author', get_current_user_id());
    }
}
add_action('pre_get_posts', 'wp_limit_media_library_access_to_own_uploads');

// Hook into the 'add_meta_boxes' action to add the custom meta box for quizzes
add_action('add_meta_boxes', 'quiz_visibility_meta_box');

function quiz_visibility_meta_box() {
    add_meta_box(
        'quiz_visibility_meta_box',      // Meta box ID
        __('Quiz Visibility', 'wp-quiz-plugin'),  // Meta box title
        'render_quiz_visibility_meta_box', // Callback function to display the meta box
        'quizzes',                        // Custom post type 'quizzes'
        'normal',                         // Context (position at the top of the editor)
        'high'                            // Priority (higher to place it closer to the top)
    );
}

function render_quiz_visibility_meta_box($post) {
    // Retrieve the current quiz listing visibility status from the meta field
    $listing_visibility_status = get_post_meta($post->ID, 'quiz_listing_visibility_status', true);
    if (empty($listing_visibility_status)) {
        $listing_visibility_status = 'public'; // Set 'public' as default if no value is saved yet
    }

    // Use nonce for verification to prevent CSRF attacks
    wp_nonce_field('save_quiz_visibility_meta', 'quiz_visibility_nonce');
    
    ?>
    <p class="quiz-visibility-options">
        <label>
            <input type="radio" name="quiz_visibility" value="public" <?php checked($listing_visibility_status, 'public'); ?>>
            <?php _e('Public', 'wp-quiz-plugin'); ?>
        </label>

        <label>
            <input type="radio" name="quiz_visibility" value="private" <?php checked($listing_visibility_status, 'private'); ?>>
            <?php _e('Private', 'wp-quiz-plugin'); ?>
        </label>
    </p>
    <?php
}

add_action('save_post_quizzes', 'save_quiz_visibility_meta', 10, 2);

function save_quiz_visibility_meta($post_id, $post) {
    // Verify the nonce before proceeding
    if (!isset($_POST['quiz_visibility_nonce']) || !wp_verify_nonce($_POST['quiz_visibility_nonce'], 'save_quiz_visibility_meta')) {
        return $post_id;
    }

    // Prevent autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    // Check if the user has permission to edit the quiz
    if (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    // Get the new quiz listing visibility value
    if (isset($_POST['quiz_visibility'])) {
        $new_visibility_status = sanitize_text_field($_POST['quiz_visibility']);

        // Update the custom meta field for quiz_listing_visibility_status
        update_post_meta($post_id, 'quiz_listing_visibility_status', $new_visibility_status);
    }
}
