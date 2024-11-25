<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include individual settings pages
require_once plugin_dir_path(__FILE__) . 'pages/general-settings.php';
require_once plugin_dir_path(__FILE__) . 'pages/ai-settings-page.php';
require_once plugin_dir_path(__FILE__) . 'pages/admin-strings-setting-page.php';
require_once plugin_dir_path(__FILE__) . 'pages/frontend-styles-page.php'; // Include new Frontend Styles page

/**
 * Register all settings pages.
 */
function crossword_register_all_settings_pages() {
    add_submenu_page(
        'edit.php?post_type=crossword', // Parent menu (Crossword CPT)
        __('Settings', 'wp-quiz-plugin'), // Page title
        __('Settings', 'wp-quiz-plugin'), // Menu title
        'manage_options', // Capability required to access
        'crossword-settings', // Menu slug
        'crossword_render_settings_page' // Callback to render the settings page
    );
}
add_action('admin_menu', 'crossword_register_all_settings_pages');

/**
 * Render the settings page with tabs.
 */
function crossword_render_settings_page() {
    // Define tabs
    $tabs = array(
        'general'        => __('General Settings', 'wp-quiz-plugin'),
        'ai'             => __('AI Settings', 'wp-quiz-plugin'),
        'admin-strings'  => __('Admin Strings Text', 'wp-quiz-plugin'),
        'frontend-styles' => __('Frontend Styles', 'wp-quiz-plugin'), // New Tab
    );

    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Crossword Settings', 'wp-quiz-plugin'); ?></h1>

        <!-- Tab Navigation -->
        <h2 class="kw-crossword-nav-tab-wrapper">
            <?php foreach ($tabs as $tab_key => $tab_label): ?>
                <a href="#<?php echo esc_attr($tab_key); ?>" class="kw-crossword-nav-tab" data-tab="<?php echo esc_attr($tab_key); ?>">
                    <?php echo esc_html($tab_label); ?>
                </a>
            <?php endforeach; ?>
        </h2>

        <!-- Render Tab Content -->
        <div class="kw-crossword-tab-content">
            <div id="kw-crossword-general" class="kw-crossword-tab-pane" style="display: none;">
                <?php crossword_render_general_settings_page(); ?>
            </div>
            <div id="kw-crossword-ai" class="kw-crossword-tab-pane" style="display: none;">
                <?php crossword_render_ai_settings_page(); ?>
            </div>
            <div id="kw-crossword-admin-strings" class="kw-crossword-tab-pane" style="display: none;">
                <?php crossword_render_admin_strings_settings_page(); ?>
            </div>
            <div id="kw-crossword-frontend-styles" class="kw-crossword-tab-pane" style="display: none;">
                <?php crossword_render_frontend_styles_page(); ?>
            </div>
        </div>
    </div>

    <?php
}

// Enqueue admin styles and scripts
function crossword_admin_enqueue_assets($hook) {
    // Ensure scripts and styles are loaded only on plugin-related admin pages
    if (strpos($hook, 'crossword') === false) {
        return;
    }

    wp_enqueue_style(
        'kw-crossword-admin-styles',
        plugin_dir_url(__FILE__) . 'css/settings.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'kw-crossword-admin-scripts',
        plugin_dir_url(__FILE__) . 'js/settings.js',
        array('jquery'),
        '1.0.0',
        true
    );

    wp_localize_script('kw-crossword-admin-scripts', 'kwCrosswordAdmin', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('kw-crossword-nonce'),
    ));

    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('kw-color-picker-script', plugins_url('/js/color-picker.js', __FILE__), ['wp-color-picker'], false, true);
}
add_action('admin_enqueue_scripts', 'crossword_admin_enqueue_assets');

?>
