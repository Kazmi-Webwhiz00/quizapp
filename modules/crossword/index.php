<?php

// Include the necessary files for the crossword module
include_once plugin_dir_path(__FILE__) . '/crossword-functions.php';
include_once plugin_dir_path(__FILE__) . '/crossword-settings.php';
include_once plugin_dir_path(__FILE__) . '/utils/crossword-helpers.php';
include_once plugin_dir_path(__FILE__) . '/custom-post-type-registration.php';
include_once plugin_dir_path(__FILE__) . '/crossword-download.php';
include_once plugin_dir_path(__FILE__) . '/public/crossword-fe-index.php';
include_once plugin_dir_path(__FILE__) . '/custom-metaboxes/kw-crossword-features-buttons.php';
include_once plugin_dir_path(__FILE__) . '/custom-metaboxes/crossword_seo_text.php';
include_once plugin_dir_path(__FILE__) . '/admin/admin-settings.php';

function load_crossword_assets($hook) {
    // Get the current screen information
    $screen = get_current_screen();

    // Check if the current screen is for the 'crossword' post typegenerate
        // Enqueue the CSS file
        wp_enqueue_style('crossword-style', plugin_dir_url(__FILE__) . 'assets/css/crossword-styles.css');
        
        // Enqueue the JS file with jQuery as a dependency
        wp_enqueue_script('crossword-script', plugin_dir_url(__FILE__) . 'assets/js/crossword-scripts.js', array('jquery'), null, true);
        wp_enqueue_script('crossword-utils-js', plugin_dir_url(__FILE__) . 'assets/js/utils.js', array('jquery'), null, true);
        wp_enqueue_script('generate-pdf-script', plugin_dir_url(__FILE__) . 'assets/js/crossword-pdfGenerator.js', array('jquery'), null, true);

        wp_localize_script('generate-pdf-script', 'crosswordPdfScriptVar', array(
            'strings' => array(
                'errorMessage' => _x('An error occurred while generating the PDF.', 'crossword_pdf_error', 'wp-quiz-plugin'),
                'successMessage' => _x('PDF generated successfully.', 'crossword_pdf_success', 'wp-quiz-plugin')
            )
        ));
        

        wp_enqueue_script('crossword-generate-with-ai', plugin_dir_url(__FILE__) . 'assets/js/crossword-generate-with-ai.js', array('jquery'), null, true);
        

        wp_localize_script(
            'crossword-utils-js',
            'crosswordLabels',
            array(
                'acrossLabel' => esc_html(get_option('kw_crossword_admin_across_label', __('Across', 'wp-quiz-plugin'))),
                'downLabel'   => esc_html(get_option('kw_crossword_admin_down_label', __('Down', 'wp-quiz-plugin'))),
                'filledCellColor' => esc_html(get_option('kw_crossword_admin_filled_cell_color', '#e1f5fe')),
            )
        );
        
        

        // Default values for prompts
        $default_context_prompt = 'Avoid using the following words:[existing_words]';
        $default_generation_prompt = 'Generate a crossword with [number] words on the topic [topic] suitable for users aged [age]. The crossword should be created in the [language] language.';
        $default_return_format_prompt = "\nProvide the output in the following JSON array format, with no additional text:\n\n[\n{ \"word\": \"exampleWord1\", \"clue\": \"Example clue for word 1\" },\n{ \"word\": \"exampleWord2\", \"clue\": \"Example clue for word 2\" },\n...]\n";
        $ai_button_label = get_option('kw_genreate_with_ai_button_label', __('Generate with AI', 'wp-quiz-plugin'));

        // Localize variables for use in JavaScript
        wp_localize_script('crossword-generate-with-ai', 'wpQuizPlugin', [
            // Crossword GPT API settings
            'apiKey' => get_option('kw_crossword_openai_api_key'),
            'model' => get_option('kw_crossword_openai_model', 'gpt-4o-mini'),
            'maxTokens' => (int) get_option('kw_crossword_openai_max_tokens', 50),  // Cast to integer
            'temperature' => (float) get_option('kw_crossword_openai_temperature', 0.5),  // Cast to float

            'maxNumberOfWords' => get_option('kw_genreate_with_ai_max_limit', 10),
            // AJAX URL for WordPress admin-ajax
            'ajaxUrl' => admin_url('admin-ajax.php'),

            // UI text settings
            'generatingText' => __('Generating...', 'wp-quiz-plugin'),
            'generateWithAiText' => esc_html__($ai_button_label,'wp-quiz-plugin'),

            // Localized prompt settings
            'defaultContextPrompt' => get_option('kw_crossword_context_prompt', 'Avoid using the following words:[existing_words]'),
            'defaultGenerationPrompt' => get_option(
                'kw_crossword_generation_prompt',
                'Generate a crossword with [number] words on the topic [topic] suitable for users aged [age]. The crossword should be created in the [language] language.'
            ),
            'defaultReturnFormatPrompt' => get_option(
                'kw_crossword_return_format_prompt',
                "\nProvide the output in the following JSON array format, with no additional text:\n\n[\n{ \"word\": \"exampleWord1\", \"clue\": \"Example clue for word 1\" },\n{ \"word\": \"exampleWord2\", \"clue\": \"Example clue for word 2\" },\n...]\n"
            ),

            'strings' => array(
                'errorTitle' => _x('Error', 'crossword_ai', 'wp-quiz-plugin'),
                'successTitle' => _x('Success', 'crossword_ai', 'wp-quiz-plugin'),
                'errorMessage' => _x('Could not parse the response. Ensure the AI response follows the expected format.', 'crossword_ai_error', 'wp-quiz-plugin'),
                'successMessage' => _x('Generated Content.', 'crossword_ai_success', 'wp-quiz-plugin'),
                'numberError' => _x('The number must be between 1 and', 'crossword_ai_error', 'wp-quiz-plugin'),
            )
        ]);

        wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array(), null, true);
}

add_action('admin_enqueue_scripts', 'load_crossword_assets');

function crossword_enqueue_preview_assets($hook) {
    global $post;

    if ($hook === 'post.php' || $hook === 'post-new.php') {
        if ($post->post_type === 'crossword') {
            // Enqueue the custom JavaScript file
            wp_enqueue_script(
                'crossword-preview-script',
                plugin_dir_url(__FILE__) . 'assets/js/crossword-preview.js',
                array('jquery'),
                '1.0',
                true
            );
            
            // Localize script to pass "across" and "down" labels
            wp_localize_script(
                'crossword-preview-script',
                'crosswordLabels',
                array(
                    'acrossLabel' => esc_html(get_option('kw_crossword_admin_across_label', __('Across', 'wp-quiz-plugin'))),
                    'downLabel'   => esc_html(get_option('kw_crossword_admin_down_label', __('Down', 'wp-quiz-plugin'))),
                    'filledCellColor' => esc_html(get_option('kw_crossword_admin_filled_cell_color', '#e1f5fe')),
                    'emptyCrosswordMessage' => esc_html__('Please add some words to generate the crossword.', 'wp-quiz-plugin'),
                )
            );
            

            // Enqueue the custom CSS for the crossword styling
            wp_enqueue_style('crossword-preview-style', plugin_dir_url(__FILE__) . 'assets/css/crossword-preview-styles.css');

            // Fetch the crossword data from post meta
            $words_clues = get_post_meta($post->ID, '_crossword_words_clues', true);
            if (empty($words_clues) || !is_array($words_clues)) {
                $words_clues = [];
            }

            // Retrieve settings for "Download as PDF" and "Download Key" buttons
            $default_pdf_label = __('Download as PDF', 'wp-quiz-plugin');
            $default_key_label = __('Download Key', 'wp-quiz-plugin');

            $pdf_button_text = get_option('kw_crossword_admin_download_pdf_button_label', $default_pdf_label);
            $key_button_text = get_option('kw_crossword_admin_download_key_button_label', $default_key_label);

            wp_localize_script(
                'generate-pdf-script',
                'cross_ajax_obj',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'downloadingText' => __('Downloading...', 'wp-quiz-plugin'),
                    'pdfButtonText' => $pdf_button_text,
                    'keyButtonText' => $key_button_text,
                )
            );
        }
    }
}
add_action('admin_enqueue_scripts', 'crossword_enqueue_preview_assets');

?>