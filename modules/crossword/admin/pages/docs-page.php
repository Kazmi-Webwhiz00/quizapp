<?php

function crossword_render_docs_page() {
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

    <div class="shortcode-box">
        <!-- Hidden input for Quiz Shortcode -->
        <input id="quiz-copy-input" type="text" value="[quiz_description]" readonly class="hidden-input">
        <code>[quiz_description]</code>
        <button id="quiz-copy-button" class="copy-button">Copy</button>
        <span id="quiz-copy-message" class="copy-message">Copied to clipboard!</span>
    </div>

    <div class="shortcode-box">
        <!-- Hidden input for Crossword Shortcode -->
        <input id="crossword-copy-input" type="text" value="[quiz_seo_text]" readonly class="hidden-input">
        <code>[quiz_seo_text]</code>
        <button id="crossword-copy-button" class="copy-button">Copy</button>
        <span id="crossword-copy-message" class="copy-message">Copied to clipboard!</span>
    </div>

    <h4><?php esc_html_e('Crossword Module Shortcode', 'wp-quiz-plugin'); ?></h4>
    <div class="shortcode-box">
        <!-- Hidden input for Crossword Shortcode -->
        <input id="crossword-copy-input" type="text" value="[crossword_fe_template]" readonly class="hidden-input">
        <code>[crossword_fe_template]</code>
        <button id="crossword-copy-button" class="copy-button">Copy</button>
        <span id="crossword-copy-message" class="copy-message">Copied to clipboard!</span>
    </div>

    <div class="shortcode-box">
        <!-- Hidden input for Crossword Shortcode -->
        <input id="crossword-copy-input" type="text" value="[crossword_description]" readonly class="hidden-input">
        <code>[crossword_description]</code>
        <button id="crossword-copy-button" class="copy-button">Copy</button>
        <span id="crossword-copy-message" class="copy-message">Copied to clipboard!</span>
    </div>

    <div class="shortcode-box">
        <!-- Hidden input for Crossword Shortcode -->
        <input id="crossword-copy-input" type="text" value="[crossword_seo_text]" readonly class="hidden-input">
        <code>[crossword_seo_text]</code>
        <button id="crossword-copy-button" class="copy-button">Copy</button>
        <span id="crossword-copy-message" class="copy-message">Copied to clipboard!</span>
    </div>

    <?php
}