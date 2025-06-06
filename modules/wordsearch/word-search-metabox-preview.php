<?php
    
include_once plugin_dir_path(__FILE__) . '/wordsearch-grid-shortcode.php';

// WordSearch Metabox Code

// include_once plugin_dir_path(__FILE__) . '/wordsearch-grid-shortcode.php';

// Enqueue the required JavaScript (and styles if needed) for the metabox preview.
function enqueue_wordsearch_metabox_preview_assets( $hook ) {
    // Check if we are on the WordSearch post edit screen.
    $screen = get_current_screen();

        // Fetch the filled cell background color with a default value
        $grid_even_cell_bg_color = get_option('kw_grid_even_cell_bg_color', '#ecd8b3');
        $grid_odd_cell_bg_color = get_option('kw_grid_odd_cell_bg_color', '#f5e9d1');
        $higlightedCellTextColor = get_option('kw_highlight_cell_text_color', '#ffffff');
        $lineColor = get_option('kw_grid_line_color', '#b8860b99');
        $gridTextColor = get_option('kw_grid_text_font_color', '#5c4012');
        // error_log("color" . print_r($gridTextColor , true));
        $gridTextFontFamily = get_option('kw_grid_text_font_family', 'Roboto');
        $toggleGridLettersSound = get_option('kw_grid_text_sound_setting', 0);
        // Find Words Label
        $default_find_words_label = __('Find These Words:', 'wp-quiz-plugin');
        $gridFindWordsLabel = get_option('kw_find_words_label', $default_find_words_label);
        $showImagesLabel = __('Show Images', 'wp-quiz-plugin');
        $hideImagesLabel = __('Hide Images', 'wp-quiz-plugin');
        $default_download_pdf_label = __('Download Pdf', 'wp-quiz-plugin');
        $default_downloading_button_label = __('Downloading...', 'wp-quiz-plugin');
        $downloadPdfLabel = get_option('kw_download_pdf_label', $default_download_pdf_label);
        // Words List Text Settings
        $default_word_text_font_size = 14.4;
        $default_word_text_font_color = '#4a5568';
        $wordListTextFontSize = get_option('kw_wordsearch_word_font_size', $default_word_text_font_size);
        $wordListTextFontColor = get_option('kw_wordsearch_word_font_color', $default_word_text_font_color);


    if ( $screen && $screen->post_type === 'wordsearch' && 
         ( $hook === 'post-new.php' || $hook === 'post.php' ) ) {
        
        // Enqueue jQuery (if not already loaded).
        wp_enqueue_script( 'jquery' );

            // Enqueue frontend styles.
    wp_enqueue_style(
        'wordsearch-style',
        plugin_dir_url(__FILE__) . '/assets/css/grid-style.css',
        array(),
        '1.0',
        "screen"
    );
 
        // Enqueue Phaser from a CDN.
        wp_enqueue_script( 'phaser', plugin_dir_url(__FILE__) . './assets/js/phaser.js', array(), null, true );
        
        // Enqueue your WordSearch frontend JS (if it’s not already loaded).
        wp_enqueue_script(
            'wordsearch',
            plugin_dir_url(__FILE__) . '/assets/js/wordsearch.js',
            array('jquery'),
            '1.0',
            true
        );

        wp_localize_script( 'wordsearch', 'pluginUrl', array(
          'url' => plugin_dir_url( __FILE__ ),
      ));
        
        // Enqueue the grid JS file which initializes the game.
        wp_enqueue_script(
          'wordsearch-grid',
          plugin_dir_url(__FILE__) . '/assets/js/index.js',
          array('jquery', 'phaser', 'wordsearch'),
          '1.0' . time(),
          true
      );
        // Initialize sanitized_entries as an empty array.
        // $sanitized_entries = [];
        
        global $post;
        $word_entries = get_post_meta( $post->ID, 'word_search_entries', true );
        error_log("Word Entries" . print_r($word_entries,true));
        if ( ! is_array( $word_entries ) ) {
            $word_entries = []; // Ensure it's always an array.
        }
    $timer_value = get_post_meta($post->ID, '_wordsearch_timer_value', true);
    $title = get_the_title( $post->ID );

    wp_localize_script('wordsearch-generate-with-ai', 'wordsearchScriptVar', array(
      'ajaxUrl' => admin_url('admin-ajax.php'),
      'nonce'   => wp_create_nonce('wordsearch_ajax_nonce'),
  ));

    wp_localize_script( 'wordsearch-grid', 'frontendData', array(
      'url' => plugin_dir_url( __FILE__ ),
      'entries'          => json_encode($word_entries),
      'maximunGridSize'  => 10,
      'shuffleElement'   => 'shuffleButton',
      'downloadElement'   => 'downloadButton',
      'checkBoxElement'  => 'toggle-checkbox',
      'toogleWordsBox'  => 'toggle-words-checkbox',
      'timerValue' => esc_attr($timer_value),
      'ajaxUrl' => admin_url('admin-ajax.php'),
      'nonce'   => wp_create_nonce('wp_rest'),
      'showImagesLabel' => esc_html__($showImagesLabel),
      'hideImagesLabel' => esc_html__($hideImagesLabel),
      'downloadPdfLabel' => esc_html__($downloadPdfLabel),
      'downloadingButtonLabel' => esc_html__($default_downloading_button_label),
      'gridStyles'       => array( 
          'fontColor'              => esc_attr( $gridTextColor ),
          'fontFamily'             => esc_attr( $gridTextFontFamily ),
          'evenCellBgColor'                => esc_attr( $grid_even_cell_bg_color ),
          'oddCellBgColor'                => esc_attr( $grid_odd_cell_bg_color  ),
          'higlightedCellTextColor'=> esc_attr( $higlightedCellTextColor ),
          'lineColor'              => esc_attr( $lineColor ),
          'toggleGridLettersSound'   => esc_attr($toggleGridLettersSound),
          'wordListTextFontSize'   => esc_attr($wordListTextFontSize),
          'wordListTextFontColor'  => esc_attr($wordListTextFontColor),
        ),
      'pdfText'   => array(
        'postTitle' => $title,
        'findWordsLabel' => $gridFindWordsLabel,
    ),
  ));
    }
}
add_action( 'admin_enqueue_scripts', 'enqueue_wordsearch_metabox_preview_assets' );

function add_module_type_attribute( $tag, $handle, $src ) {
  // List the handles that should be treated as modules.
  $module_handles = array( 'wordsearch-grid' );  // Add your handle here
  
  // Check if the current handle is in the list of module handles.
  if ( in_array( $handle, $module_handles, true ) ) {
      // Modify the tag to add type="module"
      $tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
  }
  
  return $tag;
}
add_filter( 'script_loader_tag', 'add_module_type_attribute', 10, 3 );

// Register the meta box for the 'wordsearch' post type.
function add_wordsearch_preview_meta_box() {
   // Retrieve the meta value for the full view container label, or fall back to the default
   $default_label = __('Preview Word Search', 'wp-quiz-plugin');
   $meta_label = get_option('kw_wordsearch_admin_full_view_container_label', $default_label);
   
    add_meta_box(
        'wordsearch_preview_meta_box',                // Unique ID.
        esc_html__($meta_label, 'wp-quiz-plugin'), // Use the retrieved label as the meta box title
        'render_wordsearch_preview_meta_box', // Callback to render the meta box.
        'wordsearch',                         // Post type.
        'normal',                             // Context.
        'default'                             // Priority.
    );
}
add_action('add_meta_boxes', 'add_wordsearch_preview_meta_box');



// Render the preview meta box content.
function render_wordsearch_preview_meta_box($post) {
$default_show_answers_label = __('Show Answers', 'wp-quiz-plugin');
$default_show_words_label = __('Show Words', 'wp-quiz-plugin');
$show_answer_label = get_option('kw_wordsearch_admin_show_answers_checkbox_label', $default_show_answers_label);
$show_words_label = get_option('kw_admin_show_words_checkbox_label', $default_show_words_label);
$default_shuffle_button_label = __('Shuffle', 'wp-quiz-plugin');
$shuffle_label =  get_option('kw_wordsearch_admin_shuffle_button_label', $default_shuffle_button_label);
$shuffle_bg_color = get_option('kw_wordsearch_admin_shuffle_button_color', '#0073aa');
$shuffle_text_color = get_option('kw_wordsearch_admin_shuffle_button_text_color', '#ffffff');
$default_show_words_label = __('Show Words', 'wp-quiz-plugin');
$default_download_pdf_label = __('Download Pdf', 'wp-quiz-plugin');
$downloadPdfLabel = get_option('kw_download_pdf_label', $default_download_pdf_label);
$default_no_entries_label = __('No word search entries found.', 'wp-quiz-plugin');
$noEntriesLabel = get_option('kw_no_entries_label', $default_no_entries_label);


global $post;
$word_entries = get_post_meta( $post->ID, 'word_search_entries', true );
if ( ! is_array( $word_entries ) ) {
    $word_entries = []; // Ensure it's always an array.
}

  
  // Fallback for the shuffle label
    ?>
    <div id="wordsearch-preview-container">
    <div class="button-checkbox-container">
    <label class="wordsearch-checkbox-label">
    <input type="checkbox" class="toggle-checkbox" id="toggle-answers">
    <?php echo esc_html__($show_answer_label); ?>
    </label>

    <label class="wordsearch-checkbox-label">
    <input type="checkbox" class="toggle-words-checkbox" id="toggle-words">
    <?php echo esc_html__($show_words_label); ?>
    </label>

    <button id="shuffleButton" class="shuffle-button" style="background-color: <?php echo esc_attr($shuffle_bg_color); ?>; color: <?php echo esc_attr($shuffle_text_color); ?>;">
    <?php echo esc_html__($shuffle_label); ?>
    </button>
    <?php 
    if ($word_entries) {
    ?>
      <button id="downloadButton" class="control-button kw-grid-download-button">
      <span class="button-icon">📥</span>
      <span class="button-text"><?php echo esc_html__($downloadPdfLabel); ?></span>
    </button>
    <?php 
    } 
    ?>

  <!-- <button id="shuffleButton" class="shuffle-button" >Shuffle</button> -->

</div>
      <!-- Empty state message; hidden by default -->
      <div id="wordsearch-empty-box" style="text-align: center; display: none;">
       <p class="wordsearch-empty-message"> <?php echo esc_html__($noEntriesLabel); ?> </p>
</div>
      <!-- Game preview content is rendered but hidden -->
      <div id="game-preview-content" style="display: none;">
         <?php render_game(); ?>
      </div>
    </div>

    <script>
(function(){

// Global variable for storing updated entries.
var updatedEntries = [];

// Event listener for the custom event.
document.addEventListener("entriesUpdated", function (event) {
  // Update the outer variable (do not re-declare it).
  updatedEntries = event.detail;
  console.log("::event1", event);
  // Immediately re-check the preview whenever the event fires.
  checkAndTogglePreview();
});

// Checks the cookie and toggles the preview accordingly.
function checkAndTogglePreview() {
  var emptyMsg = document.getElementById('wordsearch-empty-box');
  var gameContent = document.getElementById('game-preview-content');

  const wordDataAdded = updatedEntries && updatedEntries.length > 0;

  if (wordDataAdded) {
    // If entries exist, show game content and hide the empty message.
    gameContent.style.display = 'block';
    emptyMsg.style.display = 'none';
    // Restart the timer if it's not running.
    if (typeof window.startWordsearchGridTimer === 'function') {
      window.startWordsearchGridTimer();
    }
  } else {
    // If no entries, hide game content and show the empty message.
    gameContent.style.display = 'none';
    emptyMsg.style.display = 'block';
    if (typeof window.stopWordsearchGridTimer === 'function') {
      window.stopWordsearchGridTimer();
    }
  }
}

// Run check on page load.
checkAndTogglePreview();

// Removed setInterval since the custom event now triggers checkAndTogglePreview directly.
})();
    </script>
    <?php
}