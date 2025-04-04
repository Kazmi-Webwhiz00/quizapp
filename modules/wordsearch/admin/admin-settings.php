<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include individual settings pages
require_once plugin_dir_path(__FILE__) . 'pages/general-settings.php';
require_once plugin_dir_path(__FILE__) . 'pages/admin-strings-setting-page.php';
require_once plugin_dir_path(__FILE__) . 'pages/frontend-styles-page.php'; // Include new Frontend Styles page
require_once plugin_dir_path(__FILE__) . 'pages/ai-settings-page.php';
require_once plugin_dir_path(__FILE__) . 'pages/docs-page.php'; // Include Docs page

/**
 * Register all settings pages.
 */
function ws_register_all_settings_pages() {
    add_submenu_page(
        'edit.php?post_type=wordsearch', // Parent menu (Wordsearch CPT)
        __('Settings', 'wp-quiz-plugin'), // Page title
        __('Settings', 'wp-quiz-plugin'), // Menu title
        'manage_options', // Capability required to access
        'wordsearch-settings', // Menu slug
        'ws_render_settings_page' // Callback to render the settings page
    );
}
add_action('admin_menu', 'ws_register_all_settings_pages');

/**
 * Render the settings page with tabs.
 */
function ws_render_settings_page() {
    // Define tabs - only AI and Docs tabs are active; the others are commented out.
    $tabs = array(
        'docs'           => __('Docs', 'wp-quiz-plugin'),
        'general'        => __('General Settings', 'wp-quiz-plugin'),
        'ai'             => __('AI Settings', 'wp-quiz-plugin'),
        'admin-strings'  => __('Admin Strings Text', 'wp-quiz-plugin'),
        'frontend-styles' => __('Frontend Styles', 'wp-quiz-plugin'),
    );

    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Wordsearch Settings', 'wp-quiz-plugin'); ?></h1>

        <!-- Tab Navigation -->
        <h2 class="kw-wordsearch-nav-tab-wrapper">
            <?php foreach ($tabs as $tab_key => $tab_label): ?>
                <a href="#<?php echo esc_attr($tab_key); ?>" class="kw-wordsearch-nav-tab" data-tab="<?php echo esc_attr($tab_key); ?>">
                    <?php echo esc_html($tab_label); ?>
                </a>
            <?php endforeach; ?>
        </h2>
        <!-- Render Tab Content -->
        <div class="kw-wordsearch-tab-content">
        <div id="kw-wordsearch-docs" class="kw-wordsearch-tab-pane" style="display: none;">
                <?php ws_render_docs_page(); ?>
            </div>
            <div id="kw-wordsearch-general" class="kw-wordsearch-tab-pane" style="display: none;">
                <?php ws_render_general_settings_page(); ?>
            </div>
            
            <div id="kw-wordsearch-ai" class="kw-wordsearch-tab-pane" style="display: none;">
                <?php ws_render_ai_settings_page(); ?>
            </div>
            <div id="kw-wordsearch-admin-strings" class="kw-wordsearch-tab-pane" style="display: none;">
                <?php ws_render_admin_strings_settings_page(); ?>
            </div>
            <div id="kw-wordsearch-frontend-styles" class="kw-wordsearch-tab-pane" style="display: none;">
                <?php ws_render_frontend_styles_page(); ?>
            </div>
        </div>
    </div>
    <?php
    }

/**
 * Enqueue admin styles and scripts for Wordsearch.
 */
function ws_admin_enqueue_assets($hook) {
    // Ensure scripts and styles are loaded only on Wordsearch plugin-related admin pages
    if (strpos($hook, 'wordsearch') === false) {
        return;
    }

    wp_enqueue_style(
        'kw-wordsearch-admin-styles',
        plugin_dir_url(__FILE__) . 'css/settings.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'kw-color-picker-script',
        'https://cdn.jsdelivr.net/npm/color-2-name@1.4/lib/browser/color-2-name.min.js',
        array(),
        null,
        true
      );

    wp_enqueue_script(
        'kw-wordsearch-admin-scripts',
        plugin_dir_url(__FILE__) . 'js/settings.js',
        array('jquery'),
        '1.0.0',
        true
    );

    wp_localize_script('kw-wordsearch-admin-scripts', 'kwWordsearchAdmin', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('kw-wordsearch-nonce'),
    ));

    // wp_enqueue_style('wp-color-picker')      
}
add_action('admin_enqueue_scripts', 'ws_admin_enqueue_assets');
?>
