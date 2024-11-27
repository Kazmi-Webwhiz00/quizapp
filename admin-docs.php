<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Register the Documentation Page in the admin menu
// add_action('admin_menu', 'wp_quiz_plugin_register_docs_page');

function wp_quiz_plugin_register_docs_page() {
    add_menu_page(
        __('Documentation', 'wp-quiz-plugin'),
        __('Docs', 'wp-quiz-plugin'),
        'manage_options',
        'wp-quiz-plugin-docs',
        'wp_quiz_plugin_render_docs_page',
        'dashicons-media-document',
        25
    );
}

function wp_quiz_plugin_render_docs_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Plugin Documentation', 'wp-quiz-plugin'); ?></h1>
        
        <!-- Tab Navigation -->
        <h2 class="nav-tab-wrapper">
            <a href="#general" class="nav-tab nav-tab-active" onclick="openTab(event, 'general')"><?php esc_html_e('General', 'wp-quiz-plugin'); ?></a>
            <a href="#quiz" class="nav-tab" onclick="openTab(event, 'quiz')"><?php esc_html_e('Quiz Module', 'wp-quiz-plugin'); ?></a>
            <a href="#crossword" class="nav-tab" onclick="openTab(event, 'crossword')"><?php esc_html_e('Crossword Module', 'wp-quiz-plugin'); ?></a>
        </h2>

        <!-- Tab Content -->
        <div id="general" class="tab-content" style="display: block;">
            <?php wp_quiz_plugin_render_general_docs(); ?>
        </div>
        <div id="quiz" class="tab-content" style="display: none;">
            <?php wp_quiz_plugin_render_quiz_docs(); ?>
        </div>
        <div id="crossword" class="tab-content" style="display: none;">
            <?php wp_quiz_plugin_render_crossword_docs(); ?>
        </div>
    </div>

    <!-- JavaScript for Tab Switching and Copy to Clipboard -->
    <script type="text/javascript">
        function openTab(event, tabId) {
            const tabContents = document.getElementsByClassName('tab-content');
            for (let i = 0; i < tabContents.length; i++) {
                tabContents[i].style.display = 'none';
            }
            const tabs = document.getElementsByClassName('nav-tab');
            for (let i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove('nav-tab-active');
            }
            document.getElementById(tabId).style.display = 'block';
            event.currentTarget.classList.add('nav-tab-active');
        }

        jQuery(document).ready(function($) {
            // Copy to clipboard function for Quiz Shortcode
            $('#quiz-copy-button').on('click', function() {
                var $input = $('#quiz-copy-input');
                $input.focus().select();
                if (document.execCommand('copy')) {
                    showCopyMessage('#quiz-copy-message');
                }
            });

            // Copy to clipboard function for Crossword Shortcode
            $('#crossword-copy-button').on('click', function() {
                var $input = $('#crossword-copy-input');
                $input.focus().select();
                if (document.execCommand('copy')) {
                    showCopyMessage('#crossword-copy-message');
                }
            });

            // Function to show "Copied to clipboard" message
            function showCopyMessage(messageId) {
                $(messageId).fadeIn().delay(1500).fadeOut();
            }
        });
    </script>

    <style>
        .tab-content {
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
            background-color: #f9f9f9;
        }
        .nav-tab-wrapper {
            border-bottom: 2px solid #0073aa;
        }
        .nav-tab {
            color: #0073aa;
            padding: 8px 16px;
            border-radius: 8px 8px 0 0;
            margin-right: 5px;
            cursor: pointer;
        }
        .nav-tab-active {
            background-color: #0073aa;
            color: #fff;
        }
        .copy-button {
            margin-left: 10px;
            padding: 6px 12px;
            background-color: #0073aa;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .shortcode-box {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .hidden-input {
            position: absolute;
            left: -9999px;
        }
        .copy-message {
            display: none;
            color: #28a745;
            font-weight: bold;
            margin-left: 10px;
        }
    </style>
    <?php
}

function wp_quiz_plugin_render_general_docs() {
    ?>
    <h2><?php esc_html_e('General Information', 'wp-quiz-plugin'); ?></h2>
    <p><?php esc_html_e('This is the general documentation for the plugin. It provides an overview and instructions on how to use the plugin.', 'wp-quiz-plugin'); ?></p>
    
    <h3><?php esc_html_e('Shortcodes', 'wp-quiz-plugin'); ?></h3>
    <p><?php esc_html_e('Below are the available shortcodes for the Quiz and Crossword modules. Click "Copy" to copy the shortcode to your clipboard.', 'wp-quiz-plugin'); ?></p>
    
    <h4><?php esc_html_e('Quiz Module Shortcode', 'wp-quiz-plugin'); ?></h4>
    <div class="shortcode-box">
        <!-- Hidden input for Quiz Shortcode -->
        <input id="quiz-copy-input" type="text" value="[wp_quiz]" readonly class="hidden-input">
        <code>[wp_quiz]</code>
        <button id="quiz-copy-button" class="copy-button">Copy</button>
        <span id="quiz-copy-message" class="copy-message">Copied to clipboard!</span>
    </div>

    <h4><?php esc_html_e('Crossword Module Shortcode', 'wp-quiz-plugin'); ?></h4>
    <div class="shortcode-box">
        <!-- Hidden input for Crossword Shortcode -->
        <input id="crossword-copy-input" type="text" value="[crossword_fe_template]" readonly class="hidden-input">
        <code>[crossword_fe_template]</code>
        <button id="crossword-copy-button" class="copy-button">Copy</button>
        <span id="crossword-copy-message" class="copy-message">Copied to clipboard!</span>
    </div>
    <?php
}

function wp_quiz_plugin_render_quiz_docs() {
    ?>
    <h2><?php esc_html_e('Quiz Module Documentation', 'wp-quiz-plugin'); ?></h2>
    <p><?php esc_html_e('This section provides documentation specific to the Quiz module, including available shortcodes, usage, and settings.', 'wp-quiz-plugin'); ?></p>
    <?php
}

function wp_quiz_plugin_render_crossword_docs() {
    ?>
    <h2><?php esc_html_e('Crossword Module Documentation', 'wp-quiz-plugin'); ?></h2>
    <p><?php esc_html_e('This section provides documentation specific to the Crossword module, including available shortcodes, usage, and settings.', 'wp-quiz-plugin'); ?></p>
    <?php
}
