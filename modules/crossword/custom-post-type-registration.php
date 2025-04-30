<?php
// In your plugin's main file or a dedicated file for custom post types
function register_crossword_post_type() {
    $custom_slug = get_option('crossword_custom_url_slug', 'crossword'); // Fetch the saved slug

    $labels = array(
        'name'               => __( 'Crosswords', 'wp-quiz-plugin' ),
        'singular_name'      => __( 'Crossword', 'wp-quiz-plugin' ),
        'menu_name'          => __( 'Crosswords', 'wp-quiz-plugin' ),
        'add_new'            => __( 'Add New', 'wp-quiz-plugin' ),
        'add_new_item'       => __( 'Add New Crossword', 'wp-quiz-plugin' ),
        'edit_item'          => __( 'Edit Crossword', 'wp-quiz-plugin' ),
        'new_item'           => __( 'New Crossword', 'wp-quiz-plugin' ),
        'view_item'          => __( 'View Crossword', 'wp-quiz-plugin' ),
        'search_items'       => __( 'Search Crosswords', 'wp-quiz-plugin' ),
        'not_found'          => __( 'No crosswords found', 'wp-quiz-plugin' ),
        'not_found_in_trash' => __( 'No crosswords found in Trash', 'wp-quiz-plugin' ),
    );

    $args = array(
        'labels'             => $labels,
        'description'        => __( 'Crosswords custom post type.', 'wp-quiz-plugin' ),
        'public'             => true,
        'menu_icon'          => 'dashicons-editor-table',
        'supports'           => array( 'title', 'thumbnail' ),
        'has_archive'        => true,
        'rewrite'            => array('slug' => $custom_slug), // Singular slug
    );


    add_filter( 'admin_post_thumbnail_html', 'kw_crossword_default_admin_thumbnail', 10, 3 );
    function kw_crossword_default_admin_thumbnail( $content, $post_id, $thumbnail_id ) {
    
        if ( get_post_type( $post_id ) !== 'crossword' || $thumbnail_id ) {
            return $content;          // wrong post-type or the post already has its own image
        }
    
        $fallback_id = (int) get_option( 'kw_crossword_admin_featured_image', 0 );
        if ( ! $fallback_id ) {      // option is zero → use WP’s native “Set featured image” box
            return $content;
        }
    
        // thumbnail-ID FIRST, post-ID second
        return _wp_post_thumbnail_html( $fallback_id, $post_id );
    }

    register_post_type( 'crossword', $args ); // Singular post type key
}
add_action( 'init', 'register_crossword_post_type' );

function register_crossword_taxonomy() {
    $labels = array(
        'name'              => _x('Crossword Categories', 'taxonomy general name', 'wp-quiz-plugin'),
        'singular_name'     => _x('Crossword Category', 'taxonomy singular name', 'wp-quiz-plugin'),
        'search_items'      => __('Search Crossword Categories', 'wp-quiz-plugin'),
        'all_items'         => __('All Crossword Categories', 'wp-quiz-plugin'),
        'parent_item'       => __('Parent Crossword Category', 'wp-quiz-plugin'),
        'parent_item_colon' => __('Parent Crossword Category:', 'wp-quiz-plugin'),
        'edit_item'         => __('Edit Crossword Category', 'wp-quiz-plugin'),
        'update_item'       => __('Update Crossword Category', 'wp-quiz-plugin'),
        'add_new_item'      => __('Add New Crossword Category', 'wp-quiz-plugin'),
        'new_item_name'     => __('New Crossword Category Name', 'wp-quiz-plugin'),
        'menu_name'         => __('Crossword Categories', 'wp-quiz-plugin'),
    );

    $args = array(
        'hierarchical'      => true, // Set to false if you want a non-hierarchical taxonomy (like tags)
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'crossword-category'),
    );

    register_taxonomy('crossword_category', array('crossword'), $args);
}
add_action('init', 'register_crossword_taxonomy');


// Register Crossword Categories
function register_crossword_categories() {
    $labels = array(
        'name'              => _x('Crossword Categories', 'taxonomy general name', 'wp-quiz-plugin'),
        'singular_name'     => _x('Crossword Category', 'taxonomy singular name', 'wp-quiz-plugin'),
        'search_items'      => __('Search Crossword Categories', 'wp-quiz-plugin'),
        'all_items'         => __('All Crossword Categories', 'wp-quiz-plugin'),
        'parent_item'       => __('Parent Category', 'wp-quiz-plugin'),
        'parent_item_colon' => __('Parent Category:', 'wp-quiz-plugin'),
        'edit_item'         => __('Edit Category', 'wp-quiz-plugin'),
        'update_item'       => __('Update Category', 'wp-quiz-plugin'),
        'add_new_item'      => __('Add New Category', 'wp-quiz-plugin'),
        'new_item_name'     => __('New Category Name', 'wp-quiz-plugin'),
        'menu_name'         => __('Crossword Categories', 'wp-quiz-plugin'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'crossword-category'),
    );

    register_taxonomy('crossword_category', array('crossword'), $args);
}

add_action('init', 'register_crossword_categories');

// Add Dropdown to Crossword Category Add Form
function add_crossword_category_single_dropdown() {
    ?>
    <div class="form-field">
        <label for="crossword_type"><?php _e('Category Type', 'wp-quiz-plugin'); ?></label>
        <select name="crossword_type" id="crossword_type">
            <option value=""><?php _e('Select Category Type', 'wp-quiz-plugin'); ?></option>
            <option value="school"><?php _e('School', 'wp-quiz-plugin'); ?></option>
            <option value="subject"><?php _e('Subject', 'wp-quiz-plugin'); ?></option>
            <option value="class"><?php _e('Class', 'wp-quiz-plugin'); ?></option>
        </select>
        <p><?php _e('Select the category type (School, Subject, or Class).', 'wp-quiz-plugin'); ?></p>
    </div>
    <?php
}
add_action('crossword_category_add_form_fields', 'add_crossword_category_single_dropdown');

// Add Dropdown to Crossword Category Edit Form
function edit_crossword_category_single_dropdown($term) {
    $crossword_type = get_term_meta($term->term_id, 'crossword_type', true);
    ?>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="crossword_type"><?php _e('Category Type', 'wp-quiz-plugin'); ?></label>
        </th>
        <td>
            <select name="crossword_type" id="crossword_type">
                <option value=""><?php _e('Select Category Type', 'wp-quiz-plugin'); ?></option>
                <option value="school" <?php selected($crossword_type, 'school'); ?>><?php _e('School', 'wp-quiz-plugin'); ?></option>
                <option value="subject" <?php selected($crossword_type, 'subject'); ?>><?php _e('Subject', 'wp-quiz-plugin'); ?></option>
                <option value="class" <?php selected($crossword_type, 'class'); ?>><?php _e('Class', 'wp-quiz-plugin'); ?></option>
            </select>
            <p class="description"><?php _e('Select the category type (School, Subject, or Class).', 'wp-quiz-plugin'); ?></p>
        </td>
    </tr>
    <?php
}
add_action('crossword_category_edit_form_fields', 'edit_crossword_category_single_dropdown');

// Save Dropdown Field Value
function save_crossword_category_single_dropdown($term_id) {
    if (isset($_POST['crossword_type'])) {
        update_term_meta($term_id, 'crossword_type', sanitize_text_field($_POST['crossword_type']));
    }
}
add_action('created_crossword_category', 'save_crossword_category_single_dropdown', 10, 2);
add_action('edited_crossword_category', 'save_crossword_category_single_dropdown', 10, 2);

// Add Custom Column to Crossword Category Admin Table
function add_crossword_category_columns($columns) {
    $columns['crossword_type'] = __('Category Type', 'wp-quiz-plugin');
    return $columns;
}
add_filter('manage_edit-crossword_category_columns', 'add_crossword_category_columns');

// Populate Custom Column
function manage_crossword_category_custom_column($content, $column_name, $term_id) {
    if ($column_name === 'crossword_type') {
        $crossword_type = get_term_meta($term_id, 'crossword_type', true);
        switch ($crossword_type) {
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
                $content = __('__', 'wp-quiz-plugin');
        }
    }
    return $content;
}
add_filter('manage_crossword_category_custom_column', 'manage_crossword_category_custom_column', 10, 3);


// Add Crossword Category Meta Box
function add_crossword_category_meta_box() {

    $select_category_label_text = get_option('wp_crossword_plugin_category_label_text', _x('Crossword Category','crossword','wp-quiz-plugin' ));
    add_meta_box(
        'crossword_category_meta_box',
        $select_category_label_text,
        'render_crossword_category_meta_box',
        'crossword',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_crossword_category_meta_box');

function remove_default_crossword_category_meta_box() {
    // Remove the default meta box for the crossword_category taxonomy
    remove_meta_box('crossword_categorydiv', 'crossword', 'normal');
}
add_action('admin_menu', 'remove_default_crossword_category_meta_box', 999);

function render_crossword_category_meta_box($post) {
    $select_category_school_text = get_option('wp_crossword_plugin_category_select_school_text', _x('Select School','crossword','wp-quiz-plugin' ));
    $select_category_class_text = get_option('wp_crossword_plugin_category_select_class_text', _x('Select Class','crossword','wp-quiz-plugin' ));
    $select_category_subject_text = get_option('wp_crossword_plugin_category_select_subject_text', _x('Select Subject','crossword','wp-quiz-plugin' ));

    // Retrieve associated terms for the crossword post
    $selected_schools = wp_get_post_terms($post->ID, 'crossword_category', ['fields' => 'ids', 'parent' => 0]);
    $selected_school = !empty($selected_schools) ? $selected_schools[0] : '';

    $selected_classes = !empty($selected_school) ? wp_get_post_terms($post->ID, 'crossword_category', ['parent' => $selected_school]) : [];
    $selected_class = !empty($selected_classes) ? $selected_classes[0]->term_id : '';

    $selected_subjects = !empty($selected_class) ? wp_get_post_terms($post->ID, 'crossword_category', ['parent' => $selected_class]) : [];
    $selected_subject = !empty($selected_subjects) ? $selected_subjects[0]->term_id : '';

    $schools = get_terms([
        'taxonomy' => 'crossword_category',
        'parent' => 0,
        'hide_empty' => false,
    ]);

    $classes = $selected_school ? get_terms([
        'taxonomy' => 'crossword_category',
        'parent' => $selected_school,
        'hide_empty' => false,
    ]) : [];

    $subjects = $selected_class ? get_terms([
        'taxonomy' => 'crossword_category',
        'parent' => $selected_class,
        'hide_empty' => false,
    ]) : [];

    ?>
    <div class="crossword-category-dropdowns">
        <label for="selected_school"><?php _e($select_category_school_text, 'wp-quiz-plugin'); ?></label>
        <select name="selected_school" id="selected_school_crossword">
            <option value=""><?php _e('----------', 'wp-quiz-plugin'); ?></option>
            <?php foreach ($schools as $school) { ?>
                <option value="<?php echo esc_attr($school->term_id); ?>" <?php selected($selected_school, $school->term_id); ?>>
                    <?php echo esc_html($school->name); ?>
                </option>
            <?php } ?>
        </select>

        <div id="class_select_container" <?php if (empty($classes)) echo 'style="display:none;"'; ?>>
            <select name="selected_class" id="selected_class_crossowrd">
                <option value=""><?php _e('----------', 'wp-quiz-plugin'); ?></option>
                <?php foreach ($classes as $class) { ?>
                    <option value="<?php echo esc_attr($class->term_id); ?>" <?php selected($selected_class, $class->term_id); ?>>
                        <?php echo esc_html($class->name); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div id="subject_select_container_crossword" <?php if (empty($subjects)) echo 'style="display:none;"'; ?>>
            <select name="selected_subject" id="selected_subject_crossword">
                <option value=""><?php _e('----------', 'wp-quiz-plugin'); ?></option>
                <?php foreach ($subjects as $subject) { ?>
                    <option value="<?php echo esc_attr($subject->term_id); ?>" <?php selected($selected_subject, $subject->term_id); ?>>
                        <?php echo esc_html($subject->name); ?>
                    </option>
                <?php } ?>
            </select>
        </div>
    </div>

    <?php wp_nonce_field(basename(__FILE__), 'crossword_category_nonce');
}

// Save the selected categories
function save_crossword_category_meta_box($post_id) {
    if (!isset($_POST['crossword_category_nonce']) || !wp_verify_nonce($_POST['crossword_category_nonce'], basename(__FILE__))) {
        return $post_id;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    if ('crossword' !== $_POST['post_type']) {
        return $post_id;
    }

    if (isset($_POST['selected_school'])) {
        wp_set_post_terms($post_id, intval($_POST['selected_school']), 'crossword_category');
    }
    if (isset($_POST['selected_class'])) {
        wp_set_post_terms($post_id, intval($_POST['selected_class']), 'crossword_category', true);
    }
    if (isset($_POST['selected_subject'])) {
        wp_set_post_terms($post_id, intval($_POST['selected_subject']), 'crossword_category', true);
    }
}
add_action('save_post', 'save_crossword_category_meta_box');

// Fetch classes and subjects dynamically via AJAX
function fetch_crossword_classes() {
    $parent_id = intval($_POST['parent_id']);

    if ($parent_id) {
        $classes = get_terms([
            'taxonomy' => 'crossword_category',
            'parent' => $parent_id,
            'hide_empty' => false,
        ]);

        echo '<option value="">' . __('----------', 'wp-quiz-plugin') . '</option>';
        foreach ($classes as $class) {
            echo '<option value="' . esc_attr($class->term_id) . '">' . esc_html($class->name) . '</option>';
        }
    }
    wp_die();
}
add_action('wp_ajax_fetch_crossword_classes', 'fetch_crossword_classes');

function fetch_crossword_subjects() {
    $parent_id = intval($_POST['parent_id']);

    if ($parent_id) {
        $subjects = get_terms([
            'taxonomy' => 'crossword_category',
            'parent' => $parent_id,
            'hide_empty' => false,
        ]);

        echo '<option value="">' . __('----------', 'wp-quiz-plugin') . '</option>';
        foreach ($subjects as $subject) {
            echo '<option value="' . esc_attr($subject->term_id) . '">' . esc_html($subject->name) . '</option>';
        }
    }
    wp_die();
}
add_action('wp_ajax_fetch_crossword_subjects', 'fetch_crossword_subjects');

// Add JavaScript for cascading dropdowns
function crossword_category_cascade_script() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#selected_school_crossword').on('change', function() {
                var selectedSchool = $(this).val();
                $('#class_select_container, #subject_select_container_crossword').hide();

                if (selectedSchool) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'fetch_crossword_classes',
                            parent_id: selectedSchool
                        },
                        success: function(response) {
                            $('#selected_class_crossowrd').html(response);
                            if ($('#selected_class_crossowrd option').length > 1) {
                                $('#class_select_container').show();
                            }
                            $('#selected_subject_crossword').html('<option value=""><?php _e('----------', 'wp-quiz-plugin'); ?></option>');
                        }
                    });
                }
            });

            $('#selected_class_crossowrd').on('change', function() {
                var selectedClass = $(this).val();
                $('#subject_select_container_crossword').hide();

                if (selectedClass) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'fetch_crossword_subjects',
                            parent_id: selectedClass
                        },
                        success: function(response) {
                            $('#selected_subject_crossword').html(response);
                            if ($('#selected_subject_crossword option').length > 1) {
                                $('#subject_select_container_crossword').show();
                            }
                        }
                    });
                }
            });
        });
    </script>
    <?php
}
add_action('admin_footer', 'crossword_category_cascade_script');



// Add the Crossword Visibility meta box
function crossword_visibility_meta_box() {
    $meta_label = _x('Crossword Visibility','crossword', 'wp-quiz-plugin');

    add_meta_box(
        'crossword_visibility_meta_box',      // Meta box ID
        $meta_label,  // Meta box title
        'render_crossword_visibility_meta_box', // Callback function to display the meta box
        'crossword',                           // Custom post type 'crossword'
        'side',
        'default'                                // Priority (higher to place it closer to the top)
    );
}
add_action('add_meta_boxes', 'crossword_visibility_meta_box');

// Render the Crossword Visibility meta box
function render_crossword_visibility_meta_box($post) {
    // Retrieve the current crossword listing visibility status from the meta field
    $listing_visibility_status = get_post_meta($post->ID, 'crossword_listing_visibility_status', true);
    if (empty($listing_visibility_status)) {
        $listing_visibility_status = 'public'; // Set 'public' as default if no value is saved yet
    }

    // Use nonce for verification to prevent CSRF attacks
    wp_nonce_field('save_crossword_visibility_meta', 'crossword_visibility_nonce');
    ?>
    <p class="crossword-visibility-options">
        <label>
            <input type="radio" name="crossword_visibility" value="public" <?php checked($listing_visibility_status, 'public'); ?>>
            <?php echo _x('Public', 'crossword','wp-quiz-plugin'); ?>
        </label>

        <label>
            <input type="radio" name="crossword_visibility" value="private" <?php checked($listing_visibility_status, 'private'); ?>>
            <?php echo _x('Private','crossword', 'wp-quiz-plugin'); ?>
        </label>
    </p>
    <?php
}

// Save the Crossword Visibility meta box value
function save_crossword_visibility_meta($post_id, $post) {
    // Verify the nonce before proceeding
    if (!isset($_POST['crossword_visibility_nonce']) || !wp_verify_nonce($_POST['crossword_visibility_nonce'], 'save_crossword_visibility_meta')) {
        return $post_id;
    }

    // Prevent autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    // Check if the user has permission to edit the crossword
    if (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    // Get the new crossword listing visibility value
    if (isset($_POST['crossword_visibility'])) {
        $new_visibility_status = sanitize_text_field($_POST['crossword_visibility']);

        // Update the custom meta field for crossword_listing_visibility_status
        update_post_meta($post_id, 'crossword_listing_visibility_status', $new_visibility_status);
    }
}
add_action('save_post_crossword', 'save_crossword_visibility_meta', 10, 2);



// Hook to modify post data before saving
function wp_crossword_plugin_validate_taxonomies($data, $postarr) {
    $default_category_value = get_option('kw_crossword_default_category_value', 'Physics');
    // Only apply to 'crossword' post type
    if ($data['post_type'] !== 'crossword') {
        return $data;
    }

    // Verify user capabilities
    if (!current_user_can('edit_post', $postarr['ID'])) {
        return $data;
    }

    // Skip validation if the post is being deleted or auto-drafted
    if (in_array($data['post_status'], ['trash', 'auto-draft', 'inherit'], true)) {
        return $data;
    }

    // Also, skip validation if the current action is 'trash', 'delete', or 'untrash'
    if (isset($_REQUEST['action']) && in_array($_REQUEST['action'], ['trash', 'delete', 'untrash'], true)) {
        return $data;
    }

    // Retrieve 'crossword_listing_visibility_status' Meta Value
    $crossword_visibility = '';

    // Attempt to get the value from $postarr['meta_input']
    if (isset($postarr['meta_input']['crossword_listing_visibility_status'])) {
        $crossword_visibility = sanitize_text_field($postarr['meta_input']['crossword_listing_visibility_status']);
    } elseif (isset($_POST['crossword_visibility'])) {
        // If not in meta_input, try to get it from $_POST
        $crossword_visibility = sanitize_text_field($_POST['crossword_visibility']);
    } else {
        // For existing posts, retrieve the current meta value
        $post_id = isset($postarr['ID']) ? intval($postarr['ID']) : 0;
        if ($post_id) {
            $crossword_visibility = get_post_meta($post_id, 'crossword_listing_visibility_status', true);
        }
    }

    // If 'crossword_visibility' is 'private', skip validation
    if ($crossword_visibility === 'private') {
        return $data;
    }

    // Initialize error messages array
    $default = intval($default_category_value);

    // Initialize error messages array
    $error_messages = [];
    
    // Retrieve Selected Terms from $_POST
    $selected_school  = isset($_POST['selected_school']) ? intval($_POST['selected_school']) : 0;
    $selected_class   = isset($_POST['selected_class']) ? intval($_POST['selected_class']) : 0;
    $selected_subject = isset($_POST['selected_subject']) ? intval($_POST['selected_subject']) : 0;
    
    // School: If no school is provided, use default
    if (empty($selected_school)) {
        $selected_school = $default;
    }
    
    // Get classes under the selected school
    $class_terms = get_terms([
        'taxonomy'   => 'crossword_category',
        'parent'     => $selected_school,
        'hide_empty' => false,
    ]);
    
    if (!is_wp_error($class_terms) && !empty($class_terms)) {
    
        // Class: If no class is provided, use default
        if (empty($selected_class)) {
            $selected_class = $default;
        } else {
            // Verify that the selected class is a child of the selected school
            $class_ids = wp_list_pluck($class_terms, 'term_id');
            if (!in_array($selected_class, $class_ids, true)) {
                $error_messages[] = __('Selected Class is invalid for the chosen School.', 'wp-quiz-plugin');
            }
        }
    
        // Only require subject if a user explicitly selects a class (i.e. the class is not the fallback default)
        if ($selected_class !== $default) {
            $subject_terms = get_terms([
                'taxonomy'   => 'crossword_category',
                'parent'     => $selected_class,
                'hide_empty' => false,
            ]);
    
            if (!is_wp_error($subject_terms) && !empty($subject_terms)) {
                if (empty($selected_subject)) {
                    $error_messages[] = __('Please select a Subject.', 'wp-quiz-plugin');
                } else {
                    $subject_ids = wp_list_pluck($subject_terms, 'term_id');
                    if (!in_array($selected_subject, $subject_ids, true)) {
                        $error_messages[] = __('Selected Subject is invalid for the chosen Class.', 'wp-quiz-plugin');
                    }
                }
            }
        }
    }
    
    // If there are errors, handle accordingly
    if (!empty($error_messages)) {
        if ($data['post_status'] === 'publish') {
            $data['post_status'] = 'draft';
        }
    
        set_transient('wp_crossword_plugin_errors_' . $postarr['ID'], $error_messages, 30);
    }

    return $data;
}
add_filter('wp_insert_post_data', 'wp_crossword_plugin_validate_taxonomies', 10, 2);



// Display validation errors for crossword
function wp_crossword_plugin_display_errors() {
    $screen = get_current_screen();

    if ($screen && $screen->post_type === 'crossword') {
        global $post;
        $post_id = isset($post->ID) ? $post->ID : 0;

        if ($post_id) {
            $error_messages = get_transient('wp_crossword_plugin_errors_' . $post_id);

            if ($error_messages) {
                delete_transient('wp_crossword_plugin_errors_' . $post_id);

                echo '<div class="notice notice-error is-dismissible">';
                foreach ($error_messages as $message) {
                    echo '<p>' . esc_html($message) . '</p>';
                }
                echo '</div>';
            }
        }
    }
}
add_action('admin_notices', 'wp_crossword_plugin_display_errors');



// Restrict crosswords to current user for non-admins
function wp_crossword_plugin_filter_crosswords_for_non_admins($query) {
    if (is_admin() && $query->is_main_query() && $query->get('post_type') === 'crossword') {
        if (!current_user_can('administrator')) {
            $query->set('author', get_current_user_id());
        }
    }
}
add_action('pre_get_posts', 'wp_crossword_plugin_filter_crosswords_for_non_admins');



// Adjust post counts for crossword post type
function wp_crossword_plugin_adjust_crossword_counts($views) {
    if (!current_user_can('administrator') && isset($views['all'])) {
        global $current_user, $typenow;

        if ($typenow === 'crossword') {
            $current_user_id = get_current_user_id();

            $published_count = (int)get_users_posts_count($current_user_id, 'crossword', 'publish');

            if (isset($views['publish'])) {
                $views['publish'] = preg_replace('/\(.+\)/', '(' . $published_count . ')', $views['publish']);
            }

            $mine_count = (int)get_users_posts_count($current_user_id, 'crossword');
            $draft_count = (int)get_users_posts_count($current_user_id, 'crossword', 'draft');
            $trash_count = (int)get_users_posts_count($current_user_id, 'crossword', 'trash');

            if (isset($views['all'])) {
                $views['all'] = preg_replace('/\(.+\)/', '(' . $mine_count . ')', $views['all']);
            }

            if (isset($views['draft'])) {
                $views['draft'] = preg_replace('/\(.+\)/', '(' . $draft_count . ')', $views['draft']);
            }

            if (isset($views['trash'])) {
                $views['trash'] = preg_replace('/\(.+\)/', '(' . $trash_count . ')', $views['trash']);
            }
        }
    }

    return $views;
}
add_filter('views_edit-crossword', 'wp_crossword_plugin_adjust_crossword_counts');
