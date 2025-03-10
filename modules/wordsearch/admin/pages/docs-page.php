<?php

function ws_render_docs_page() {
    ?>
    <h2><?php esc_html_e('General Information', 'wp-quiz-plugin'); ?></h2>
    <p><?php esc_html_e('This is the general documentation for the plugin. It provides an overview and instructions on how to use the plugin.', 'wp-quiz-plugin'); ?></p>
    
    <h3><?php esc_html_e('Shortcodes', 'wp-quiz-plugin'); ?></h3>
    <p><?php esc_html_e('Below are the available shortcodes for the Quiz and Wordsearch modules. Click "Copy" to copy the shortcode to your clipboard.', 'wp-quiz-plugin'); ?></p>
    
    <h4><?php esc_html_e('Quiz Module Shortcode', 'wp-quiz-plugin'); ?></h4>

    <div class="shortcode-box">
        <!-- Hidden input for Quiz Description Shortcode -->
        <input id="quiz-copy-input-desc" type="text" value="[quiz_description]" readonly class="hidden-input">
        <code>[quiz_description]</code>
        <button id="quiz-copy-button-desc" class="copy-button">Copy</button>
        <span id="quiz-copy-message-desc" class="copy-message">Copied to clipboard!</span>
    </div>
    
    <h4><?php esc_html_e('Wordsearch Module Shortcode', 'wp-quiz-plugin'); ?></h4>
    <div class="shortcode-box">
        <!-- Hidden input for Wordsearch Template Shortcode -->
        <input id="wordsearch-copy-input" type="text" value="[word_search_puzzle]" readonly class="hidden-input">
        <code>[word_search_puzzle]</code>
        <button id="wordsearch-copy-button" class="copy-button">Copy</button>
        <span id="wordsearch-copy-message" class="copy-message">Copied to clipboard!</span>
    </div>
    <?php
}
