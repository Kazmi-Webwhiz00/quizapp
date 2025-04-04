<?php
include_once plugin_dir_path(__FILE__) . '/backend/wordsearch-cpt.php';
include_once plugin_dir_path(__FILE__) . '/word-search-metaboxes.php';
include_once plugin_dir_path(__FILE__) . '/word-search-metabox-preview.php';
include_once plugin_dir_path(__FILE__) . '/word-search-timer-metabox.php';
include_once plugin_dir_path(__FILE__) . '/word-search-metabox-ai.php';
include_once plugin_dir_path(__FILE__) . '/admin/admin-settings.php';


function load_wordsearch_assets($hook) {
    // Get the current screen information
    $screen = get_current_screen();

    // Check if the current screen is for the 'wordsearch' post type
    if (!isset($screen->post_type) || $screen->post_type !== 'wordsearch') {
        return;
    }

    // Non-AI related asset enqueuing is commented out
    wp_enqueue_style('wordsearch-ai-style', plugin_dir_url(__FILE__) . 'assets/css/wordsearch-style.css', array(),
    '1.0.0','all');

    wp_enqueue_script('wordsearch-generate-with-ai', plugin_dir_url(__FILE__) . 'assets/js/wordsearch-generate-with-ai.js', array('jquery'), null, true);

    // Enqueue the AI-specific JavaScript file for Wordsearch

    // Localize variables for use in the Wordsearch AI JavaScript
    $ws_button_label = get_option('kw_generate_with_ai_button_label_ws', __('Generate with AI', 'wp-quiz-plugin'));
    $default_entry_limit_popup_label = __("Entry Limit Reached", 'wp-quiz-plugin');
    $default_entry_limit__body_text = __('You cannot add more than 15 entries to the word search.', 'wp-quiz-plugin');
    $entry_limit__popup_title = get_option('kw_wordsearch_entry_limit_popup_title', $default_entry_limit_popup_label);
    $entry_limit__popup_body_text = get_option('kw_wordsearch_entry_limit_popup_body_text', $default_entry_limit__body_text);

    wp_localize_script('wordsearch-generate-with-ai', 'wordsearchScriptVar', [        
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('wordsearch_ajax_nonce'),

        'entryLimitTitle' => $entry_limit__popup_title,
        'entryLimitBodyText' => $entry_limit__popup_body_text,

        // Wordsearch GPT API settings
        'isAdmin' => current_user_can('manage_options'),
        'wsApiKey' => get_option('kw_wordsearch_openai_api_key'),
        'wsModel' => get_option('kw_wordsearch_openai_model', 'gpt-4o-mini'),
        'wsMaxTokens' => (int) get_option('kw_wordsearch_openai_max_tokens', 50),
        'wsTemperature' => (float) get_option('kw_wordsearch_openai_temperature', 0.5),
        'wsMaxNumberOfWords' => get_option('kw_generate_with_ai_max_limit_ws', 10),
        // AJAX URL for WordPress admin-ajax
        'ajaxUrl' => admin_url('admin-ajax.php'),
        // UI text settings
        'wsGeneratingText' => __('Generating...', 'wp-quiz-plugin'),
        'wsGenerateWithAiText' => esc_html__($ws_button_label, 'wp-quiz-plugin'),
        // Localized prompt settings
        'wsDefaultContextPrompt' => get_option('kw_wordsearch_context_prompt', 'Avoid using the following words:[existing_words]'),
        'wsDefaultGenerationPrompt' => get_option(
            'kw_wordsearch_generation_prompt',
            'Generate a wordsearch with [number] words on the topic [topic] suitable for users aged [age]. The wordsearch should be created in the [language] language.'
        ),
        'wsDefaultReturnFormatPrompt' => get_option(
            'kw_wordsearch_return_format_prompt',
            "\nProvide the output in the following JSON array format, with no additional text:\n\n[\n{ \"wordText\": \"exampleWord1\", \"clue\": \"Example clue for word 1\" },\n{ \"wordText\": \"exampleWord2\", \"clue\": \"Example clue for word 2\" },\n...]\n"
        ),
        'wsStrings' => array(
            'errorTitle' => _x('Error', 'wordsearch_ai', 'wp-quiz-plugin'),
            'successTitle' => _x('Success', 'wordsearch_ai', 'wp-quiz-plugin'),
            'errorMessage' => _x('Could not parse the response. Ensure the AI response follows the expected format.', 'wordsearch_ai_error', 'wp-quiz-plugin'),
            'successMessage' => _x('Generated Content.', 'wordsearch_ai_success', 'wp-quiz-plugin'),
            'numberError' => _x('The number must be between 1 and', 'wordsearch_ai_error', 'wp-quiz-plugin'),
        ),
        'defaultCategory'=> get_option('kw_wordsearch_default_category_value', __('Physics', 'wp-quiz-plugin')),

    ]);

    // Enqueue SweetAlert2 for alerts
    wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array(), null, true);
}

add_action('admin_enqueue_scripts', 'load_wordsearch_assets');

