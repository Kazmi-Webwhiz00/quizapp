<?php
/**
 * Wordsearch Module File
 * 
 * This file registers the Wordsearch post type, taxonomy, meta boxes,
 * and AJAX callbacks. It also enqueues an external JavaScript file
 * (located at js/script.js) for handling cascading dropdowns.
 */

// Enqueue the separate JavaScript file for cascading dropdowns
function wordsearch_category_enqueue_admin_script($hook) {
    // Enqueue only on edit or add post screens for the wordsearch post type.
    global $post_type;
    if ( ('post.php' === $hook || 'post-new.php' === $hook) && $post_type === 'wordsearch' ) {
        wp_enqueue_script(
            'wordsearch-cascade',
            plugin_dir_url(__FILE__) . '/assets/js/script.js',
            array('jquery'),
            '1.0',
            true
        );
        // Localize the script for AJAX
        wp_localize_script('wordsearch-cascade', 'wordsearch_ajax_obj', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));
    }
}
add_action('admin_enqueue_scripts', 'wordsearch_category_enqueue_admin_script');

// Register Wordsearch Post Type
function register_wordsearch_post_type() {
    $custom_slug = get_option('wordsearch_custom_url_slug', 'wordsearch'); // Fetch the saved slug

    $labels = array(
        'name'               => __( 'Wordsearches', 'wp-quiz-plugin' ),
        'singular_name'      => __( 'Wordsearch', 'wp-quiz-plugin' ),
        'menu_name'          => __( 'Wordsearches', 'wp-quiz-plugin' ),
        'add_new'            => __( 'Add New', 'wp-quiz-plugin' ),
        'add_new_item'       => __( 'Add New Wordsearch', 'wp-quiz-plugin' ),
        'edit_item'          => __( 'Edit Wordsearch', 'wp-quiz-plugin' ),
        'new_item'           => __( 'New Wordsearch', 'wp-quiz-plugin' ),
        'view_item'          => __( 'View Wordsearch', 'wp-quiz-plugin' ),
        'search_items'       => __( 'Search Wordsearches', 'wp-quiz-plugin' ),
        'not_found'          => __( 'No wordsearches found', 'wp-quiz-plugin' ),
        'not_found_in_trash' => __( 'No wordsearches found in Trash', 'wp-quiz-plugin' ),
    );

    $args = array(
        'labels'             => $labels,
        'description'        => __( 'Wordsearch custom post type.', 'wp-quiz-plugin' ),
        'public'             => true,
        'menu_icon'          => 'dashicons-editor-table',
        'supports'           => array( 'title' ),
        'has_archive'        => true,
        'rewrite'            => array('slug' => $custom_slug),
    );

    register_post_type( 'wordsearch', $args );
}
add_action( 'init', 'register_wordsearch_post_type' );

// Register Wordsearch Taxonomy
function register_wordsearch_taxonomy() {
    $labels = array(
        'name'              => _x('Wordsearch Categories', 'taxonomy general name', 'wp-quiz-plugin'),
        'singular_name'     => _x('Wordsearch Category', 'taxonomy singular name', 'wp-quiz-plugin'),
        'search_items'      => __('Search Wordsearch Categories', 'wp-quiz-plugin'),
        'all_items'         => __('All Wordsearch Categories', 'wp-quiz-plugin'),
        'parent_item'       => __('Parent Wordsearch Category', 'wp-quiz-plugin'),
        'parent_item_colon' => __('Parent Wordsearch Category:', 'wp-quiz-plugin'),
        'edit_item'         => __('Edit Wordsearch Category', 'wp-quiz-plugin'),
        'update_item'       => __('Update Wordsearch Category', 'wp-quiz-plugin'),
        'add_new_item'      => __('Add New Wordsearch Category', 'wp-quiz-plugin'),
        'new_item_name'     => __('New Wordsearch Category Name', 'wp-quiz-plugin'),
        'menu_name'         => __('Wordsearch Categories', 'wp-quiz-plugin'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'wordsearch-category'),
    );

    register_taxonomy('wordsearch_category', array('wordsearch'), $args);
}
add_action('init', 'register_wordsearch_taxonomy');

// Register Wordsearch Categories (Duplicate Registration if needed)
function register_wordsearch_categories() {
    $labels = array(
        'name'              => _x('Wordsearch Categories', 'taxonomy general name', 'wp-quiz-plugin'),
        'singular_name'     => _x('Wordsearch Category', 'taxonomy singular name', 'wp-quiz-plugin'),
        'search_items'      => __('Search Wordsearch Categories', 'wp-quiz-plugin'),
        'all_items'         => __('All Wordsearch Categories', 'wp-quiz-plugin'),
        'parent_item'       => __('Parent Category', 'wp-quiz-plugin'),
        'parent_item_colon' => __('Parent Category:', 'wp-quiz-plugin'),
        'edit_item'         => __('Edit Category', 'wp-quiz-plugin'),
        'update_item'       => __('Update Category', 'wp-quiz-plugin'),
        'add_new_item'      => __('Add New Category', 'wp-quiz-plugin'),
        'new_item_name'     => __('New Category Name', 'wp-quiz-plugin'),
        'menu_name'         => __('Wordsearch Categories', 'wp-quiz-plugin'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'wordsearch-category'),
    );

    register_taxonomy('wordsearch_category', array('wordsearch'), $args);
}
add_action('init', 'register_wordsearch_categories');

// Add Dropdown to Wordsearch Category Add Form
function add_wordsearch_category_single_dropdown() {
    ?>
    <div class="form-field">
        <label for="wordsearch_type"><?php _e('Category Type', 'wp-quiz-plugin'); ?></label>
        <select name="wordsearch_type" id="wordsearch_type">
            <option value=""><?php _e('Select Category Type', 'wp-quiz-plugin'); ?></option>
            <option value="school"><?php _e('School', 'wp-quiz-plugin'); ?></option>
            <option value="subject"><?php _e('Subject', 'wp-quiz-plugin'); ?></option>
            <option value="class"><?php _e('Class', 'wp-quiz-plugin'); ?></option>
        </select>
        <p><?php _e('Select the category type (School, Subject, or Class).', 'wp-quiz-plugin'); ?></p>
    </div>
    <?php
}
add_action('wordsearch_category_add_form_fields', 'add_wordsearch_category_single_dropdown');

// Add Dropdown to Wordsearch Category Edit Form
function edit_wordsearch_category_single_dropdown($term) {
    $wordsearch_type = get_term_meta($term->term_id, 'wordsearch_type', true);
    ?>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="wordsearch_type"><?php _e('Category Type', 'wp-quiz-plugin'); ?></label>
        </th>
        <td>
            <select name="wordsearch_type" id="wordsearch_type">
                <option value=""><?php _e('Select Category Type', 'wp-quiz-plugin'); ?></option>
                <option value="school" <?php selected($wordsearch_type, 'school'); ?>><?php _e('School', 'wp-quiz-plugin'); ?></option>
                <option value="subject" <?php selected($wordsearch_type, 'subject'); ?>><?php _e('Subject', 'wp-quiz-plugin'); ?></option>
                <option value="class" <?php selected($wordsearch_type, 'class'); ?>><?php _e('Class', 'wp-quiz-plugin'); ?></option>
            </select>
            <p class="description"><?php _e('Select the category type (School, Subject, or Class).', 'wp-quiz-plugin'); ?></p>
        </td>
    </tr>
    <?php
}
add_action('wordsearch_category_edit_form_fields', 'edit_wordsearch_category_single_dropdown');

// Save Dropdown Field Value
function save_wordsearch_category_single_dropdown($term_id) {
    if (isset($_POST['wordsearch_type'])) {
        update_term_meta($term_id, 'wordsearch_type', sanitize_text_field($_POST['wordsearch_type']));
    }
}
add_action('created_wordsearch_category', 'save_wordsearch_category_single_dropdown', 10, 2);
add_action('edited_wordsearch_category', 'save_wordsearch_category_single_dropdown', 10, 2);

// Add Custom Column to Wordsearch Category Admin Table
function add_wordsearch_category_columns($columns) {
    $columns['wordsearch_type'] = __('Category Type', 'wp-quiz-plugin');
    return $columns;
}
add_filter('manage_edit-wordsearch_category_columns', 'add_wordsearch_category_columns');

// Populate Custom Column
function manage_wordsearch_category_custom_column($content, $column_name, $term_id) {
    if ($column_name === 'wordsearch_type') {
        $wordsearch_type = get_term_meta($term_id, 'wordsearch_type', true);
        switch ($wordsearch_type) {
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
add_filter('manage_wordsearch_category_custom_column', 'manage_wordsearch_category_custom_column', 10, 3);

// Add Wordsearch Category Meta Box
function add_wordsearch_category_meta_box() {
    $select_category_label_text = get_option('wp_wordsearch_plugin_category_label_text', _x('Wordsearch Category','wordsearch','wp-quiz-plugin'));
    add_meta_box(
        'wordsearch_category_meta_box',
        $select_category_label_text,
        'render_wordsearch_category_meta_box',
        'wordsearch',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_wordsearch_category_meta_box');

function remove_default_wordsearch_category_meta_box() {
    // Remove the default meta box for the wordsearch_category taxonomy
    remove_meta_box('wordsearch_categorydiv', 'wordsearch', 'normal');
}
add_action('admin_menu', 'remove_default_wordsearch_category_meta_box', 999);

// Render the Wordsearch Category Meta Box
function render_wordsearch_category_meta_box($post) {
    $select_category_school_text  = get_option('wp_wordsearch_plugin_category_select_school_text', _x('Select School','wordsearch','wp-quiz-plugin'));
    $select_category_class_text   = get_option('wp_wordsearch_plugin_category_select_class_text', _x('Select Class','wordsearch','wp-quiz-plugin'));
    $select_category_subject_text = get_option('wp_wordsearch_plugin_category_select_subject_text', _x('Select Subject','wordsearch','wp-quiz-plugin'));

    // Retrieve associated terms for the wordsearch post
    $selected_schools = wp_get_post_terms($post->ID, 'wordsearch_category', array('fields' => 'ids', 'parent' => 0));
    $selected_school  = !empty($selected_schools) ? $selected_schools[0] : '';
    error_log("Selected". print_r($selected_school,true));
    // Retrieve term objects then extract the ID for selected class
    $selected_classes = !empty($selected_school) ? wp_get_post_terms($post->ID, 'wordsearch_category', array('parent' => $selected_school)) : array();
    $selected_class   = !empty($selected_classes) ? $selected_classes[0]->term_id : '';

    // Retrieve term objects then extract the ID for selected subject
    $selected_subjects = !empty($selected_class) ? wp_get_post_terms($post->ID, 'wordsearch_category', array('parent' => $selected_class)) : array();
    $selected_subject  = !empty($selected_subjects) ? $selected_subjects[0]->term_id : '';

    $schools = get_terms(array(
        'taxonomy'   => 'wordsearch_category',
        'parent'     => 0,
        'hide_empty' => false,
    ));

    $classes = $selected_school ? get_terms(array(
        'taxonomy'   => 'wordsearch_category',
        'parent'     => $selected_school,
        'hide_empty' => false,
    )) : array();

    $subjects = $selected_class ? get_terms(array(
        'taxonomy'   => 'wordsearch_category',
        'parent'     => $selected_class,
        'hide_empty' => false,
    )) : array();
    ?>
<div class="wordsearch-category-dropdowns" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
    <!-- School Dropdown -->
    <div class="dropdown-group">
        <label for="selected_school_wordsearch"><?php echo esc_html($select_category_school_text); ?></label>
        <select name="selected_school" id="selected_school_wordsearch">
            <option value=""><?php _e('----------', 'wp-quiz-plugin'); ?></option>
            <?php foreach ($schools as $school) { ?>
                <option value="<?php echo esc_attr($school->term_id); ?>" <?php selected($selected_school, $school->term_id); ?>>
                    <?php echo esc_html($school->name); ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <div id="class_select_container" class="dropdown-group" style="<?php echo ($selected_school && count($classes) > 1) ? '' : 'display: none;'; ?>">
        <label for="selected_class_wordsearch"><?php echo esc_html($select_category_class_text); ?></label>
        <select name="selected_class" id="selected_class_wordsearch">
            <option value=""><?php _e('----------', 'wp-quiz-plugin'); ?></option>
            <?php 
            if (!empty($classes)) {
                foreach ($classes as $class) { ?>
                    <option value="<?php echo esc_attr($class->term_id); ?>" <?php selected($selected_class, $class->term_id); ?>>
                        <?php echo esc_html($class->name); ?>
                    </option>
                <?php }
            }
            ?>
        </select>
    </div>

    <!-- Subject Dropdown (initially hidden if no options exist) -->
    <div id="subject_select_container_wordsearch" class="dropdown-group" style="<?php echo ($selected_class && count($subjects) > 1) ? '' : 'display: none;'; ?>">
        <label for="selected_subject_wordsearch"><?php echo esc_html($select_category_subject_text); ?></label>
        <select name="selected_subject" id="selected_subject_wordsearch">
            <option value=""><?php _e('----------', 'wp-quiz-plugin'); ?></option>
            <?php 
            if (!empty($subjects)) {
                foreach ($subjects as $subject) { ?>
                    <option value="<?php echo esc_attr($subject->term_id); ?>" <?php selected($selected_subject, $subject->term_id); ?>>
                        <?php echo esc_html($subject->name); ?>
                    </option>
                <?php }
            }
            ?>
        </select>
    </div>
</div>
<?php wp_nonce_field(basename(__FILE__), 'wordsearch_category_nonce'); ?>
    <?php
}

// Save the selected categories
function save_wordsearch_category_meta_box($post_id) {
    if (!isset($_POST['wordsearch_category_nonce']) || !wp_verify_nonce($_POST['wordsearch_category_nonce'], basename(__FILE__))) {
        return $post_id;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    if ('wordsearch' !== $_POST['post_type']) {
        return $post_id;
    }

    if (isset($_POST['selected_school'])) {
        wp_set_post_terms($post_id, intval($_POST['selected_school']), 'wordsearch_category');
    }
    if (isset($_POST['selected_class'])) {
        wp_set_post_terms($post_id, intval($_POST['selected_class']), 'wordsearch_category', true);
    }
    if (isset($_POST['selected_subject'])) {
        wp_set_post_terms($post_id, intval($_POST['selected_subject']), 'wordsearch_category', true);
    }
}
add_action('save_post', 'save_wordsearch_category_meta_box');

// Fetch classes dynamically via AJAX
function fetch_wordsearch_classes() {
    $parent_id = intval($_POST['parent_id']);

    if ($parent_id) {
        $classes = get_terms(array(
            'taxonomy'   => 'wordsearch_category',
            'parent'     => $parent_id,
            'hide_empty' => false,
        ));

        echo '<option value="">' . __('----------', 'wp-quiz-plugin') . '</option>';
        foreach ($classes as $class) {
            echo '<option value="' . esc_attr($class->term_id) . '">' . esc_html($class->name) . '</option>';
        }
    }
    wp_die();
}
add_action('wp_ajax_fetch_wordsearch_classes', 'fetch_wordsearch_classes');

function fetch_wordsearch_subjects() {
    $parent_id = intval($_POST['parent_id']);

    if ($parent_id) {
        $subjects = get_terms(array(
            'taxonomy'   => 'wordsearch_category',
            'parent'     => $parent_id,
            'hide_empty' => false,
        ));

        echo '<option value="">' . __('----------', 'wp-quiz-plugin') . '</option>';
        foreach ($subjects as $subject) {
            echo '<option value="' . esc_attr($subject->term_id) . '">' . esc_html($subject->name) . '</option>';
        }
    }
    wp_die();
}
add_action('wp_ajax_fetch_wordsearch_subjects', 'fetch_wordsearch_subjects');

// -------------------------------------------------------------------
// Wordsearch Visibility Meta Box
// -------------------------------------------------------------------
function wordsearch_visibility_meta_box() {
    $meta_label = _x('Wordsearch Visibility','wordsearch', 'wp-quiz-plugin');

    add_meta_box(
        'wordsearch_visibility_meta_box',
        $meta_label,
        'render_wordsearch_visibility_meta_box',
        'wordsearch',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'wordsearch_visibility_meta_box');

// Render the Wordsearch Visibility Meta Box
function render_wordsearch_visibility_meta_box($post) {
    $listing_visibility_status = get_post_meta($post->ID, 'wordsearch_listing_visibility_status', true);
    if (empty($listing_visibility_status)) {
        $listing_visibility_status = 'public';
    }

    wp_nonce_field('save_wordsearch_visibility_meta', 'wordsearch_visibility_nonce');
    ?>
    <p class="wordsearch-visibility-options">
        <label>
            <input type="radio" name="wordsearch_visibility" value="public" <?php checked($listing_visibility_status, 'public'); ?>>
            <?php echo _x('Public', 'wordsearch','wp-quiz-plugin'); ?>
        </label>
        <label>
            <input type="radio" name="wordsearch_visibility" value="private" <?php checked($listing_visibility_status, 'private'); ?>>
            <?php echo _x('Private','wordsearch', 'wp-quiz-plugin'); ?>
        </label>
    </p>
    <?php
}

// Save the Wordsearch Visibility Meta Box Value
function save_wordsearch_visibility_meta($post_id, $post) {
    if (!isset($_POST['wordsearch_visibility_nonce']) || !wp_verify_nonce($_POST['wordsearch_visibility_nonce'], 'save_wordsearch_visibility_meta')) {
        return $post_id;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    if (isset($_POST['wordsearch_visibility'])) {
        $new_visibility_status = sanitize_text_field($_POST['wordsearch_visibility']);
        update_post_meta($post_id, 'wordsearch_listing_visibility_status', $new_visibility_status);
    }
}
add_action('save_post_wordsearch', 'save_wordsearch_visibility_meta', 10, 2);

// -------------------------------------------------------------------
// Taxonomy Validation & Error Display
// -------------------------------------------------------------------
function wp_wordsearch_plugin_validate_taxonomies($data, $postarr) {
    $default_category_value = get_option('kw_wordsearch_default_category_value', __('Add your value here.'));

    if ($data['post_type'] !== 'wordsearch') {
        return $data;
    }

    if (!current_user_can('edit_post', $postarr['ID'])) {
        return $data;
    }

    if (in_array($data['post_status'], array('trash', 'auto-draft', 'inherit'), true)) {
        return $data;
    }

    if (isset($_REQUEST['action']) && in_array($_REQUEST['action'], array('trash', 'delete', 'untrash'), true)) {
        return $data;
    }

    $wordsearch_visibility = '';
    if (isset($postarr['meta_input']['wordsearch_listing_visibility_status'])) {
        $wordsearch_visibility = sanitize_text_field($postarr['meta_input']['wordsearch_listing_visibility_status']);
    } elseif (isset($_POST['wordsearch_visibility'])) {
        $wordsearch_visibility = sanitize_text_field($_POST['wordsearch_visibility']);
    } else {
        $post_id = isset($postarr['ID']) ? intval($postarr['ID']) : 0;
        if ($post_id) {
            $wordsearch_visibility = get_post_meta($post_id, 'wordsearch_listing_visibility_status', true);
        }
    }

    if ($wordsearch_visibility === 'private') {
        return $data;
    }

    $default = intval($default_category_value);

    $error_messages = array();
    $selected_school  = isset($_POST['selected_school']) ? intval($_POST['selected_school']) : 0;
    $selected_class   = isset($_POST['selected_class']) ? intval($_POST['selected_class']) : 0;
    $selected_subject = isset($_POST['selected_subject']) ? intval($_POST['selected_subject']) : 0;
    
    // School: if no school is provided, assign the default value.
    if (empty($selected_school)) {
        $selected_school = $default;
    }
    
    // Fetch class terms under the selected school.
    $class_terms = get_terms(array(
        'taxonomy'   => 'wordsearch_category',
        'parent'     => $selected_school,
        'hide_empty' => false,
    ));
    
    if (!is_wp_error($class_terms) && !empty($class_terms)) {
    
        // Class: if no class is provided, assign the default value.
        if (empty($selected_class)) {
            $selected_class = $default;
        } else {
            // Verify that the selected class is a valid child of the chosen school.
            $class_ids = wp_list_pluck($class_terms, 'term_id');
            if (!in_array($selected_class, $class_ids, true)) {
                $error_messages[] = __('Selected Class is invalid for the chosen School.', 'wp-quiz-plugin');
            }
        }
    
        // Subject: Only require subject if the user explicitly selected a class.
        if ($selected_class !== $default) {
            $subject_terms = get_terms(array(
                'taxonomy'   => 'wordsearch_category',
                'parent'     => $selected_class,
                'hide_empty' => false,
            ));
    
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
    
    if (!empty($error_messages)) {
        if ($data['post_status'] === 'publish') {
            $data['post_status'] = 'draft';
        }
        set_transient('wp_wordsearch_plugin_errors_' . $postarr['ID'], $error_messages, 30);
    }

    return $data;
}
add_filter('wp_insert_post_data', 'wp_wordsearch_plugin_validate_taxonomies', 10, 2);

// Display validation errors for wordsearch
function wp_wordsearch_plugin_display_errors() {
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'wordsearch') {
        global $post;
        $post_id = isset($post->ID) ? $post->ID : 0;
        if ($post_id) {
            $error_messages = get_transient('wp_wordsearch_plugin_errors_' . $post_id);
            if ($error_messages) {
                delete_transient('wp_wordsearch_plugin_errors_' . $post_id);
                echo '<div class="notice notice-error is-dismissible">';
                foreach ($error_messages as $message) {
                    echo '<p>' . esc_html($message) . '</p>';
                }
                echo '</div>';
            }
        }
    }
}
add_action('admin_notices', 'wp_wordsearch_plugin_display_errors');

// Restrict wordsearch posts to current user for non-admins
function wp_wordsearch_plugin_filter_wordsearch_for_non_admins($query) {
    if (is_admin() && $query->is_main_query() && $query->get('post_type') === 'wordsearch') {
        if (!current_user_can('administrator')) {
            $query->set('author', get_current_user_id());
        }
    }
}
add_action('pre_get_posts', 'wp_wordsearch_plugin_filter_wordsearch_for_non_admins');

// Adjust post counts for wordsearch post type
function wp_wordsearch_plugin_adjust_wordsearch_counts($views) {
    if (!current_user_can('administrator') && isset($views['all'])) {
        global $current_user, $typenow;
        if ($typenow === 'wordsearch') {
            $current_user_id = get_current_user_id();
            $published_count = (int)get_users_posts_count($current_user_id, 'wordsearch', 'publish');

            if (isset($views['publish'])) {
                $views['publish'] = preg_replace('/\(.+\)/', '(' . $published_count . ')', $views['publish']);
            }

            $mine_count = (int)get_users_posts_count($current_user_id, 'wordsearch');
            $draft_count = (int)get_users_posts_count($current_user_id, 'wordsearch', 'draft');
            $trash_count = (int)get_users_posts_count($current_user_id, 'wordsearch', 'trash');

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
add_filter('views_edit-wordsearch', 'wp_wordsearch_plugin_adjust_wordsearch_counts');
