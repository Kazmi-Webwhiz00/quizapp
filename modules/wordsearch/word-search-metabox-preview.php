<?php
    
include_once plugin_dir_path(__FILE__) . '/wordsearch-grid-shortcode.php';

// WordSearch Metabox Code

// include_once plugin_dir_path(__FILE__) . '/wordsearch-grid-shortcode.php';

// Enqueue the required JavaScript (and styles if needed) for the metabox preview.
function enqueue_wordsearch_metabox_preview_assets( $hook ) {
    // Check if we are on the WordSearch post edit screen.
    $screen = get_current_screen();

        // Fetch the filled cell background color with a default value
        $grid_bg_color = get_option('kw_grid_bg_color', '#808080a1');
        $higlightedCellTextColor = get_option('kw_highlight_cell_text_color', '#d4edda');
        $lineColor = get_option('kw_wordsearch_line_color', 'rgba(0, 123, 255, 0.8)');
        $gridTextColor = get_option('kw_grid_text_font_color', '#000');
        // error_log("color" . print_r($gridTextColor , true));
        $gridTextFontFamily = get_option('kw_grid_text_font_family', 'Roboto');

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
        wp_enqueue_script( 'phaser', 'https://cdn.jsdelivr.net/npm/phaser@3.55.2/dist/phaser.js', array(), null, true );
        
        // Enqueue your WordSearch frontend JS (if it’s not already loaded).
        wp_enqueue_script(
            'wordsearch',
            plugin_dir_url(__FILE__) . '/assets/js/wordsearch.js',
            array('jquery'),
            '1.0',
            true
        );
        
        // Enqueue the grid JS file which initializes the game.
        wp_enqueue_script(
            'wordsearch-grid',
            plugin_dir_url(__FILE__) . '/assets/js/wordsearch-grid.js',
            array('jquery', 'phaser', 'wordsearch'),
            '1.0',
            true
        );
        
        // Pass any needed data to your JS.
        wp_localize_script( 'wordsearch-grid', 'pluginURL', array(
            'url' => plugin_dir_url( __FILE__ )
        ) );

        // Initialize sanitized_entries as an empty array.
        $sanitized_entries = [];
        
        if ( isset( $_COOKIE['wordsearch_entries'] ) && ! empty( $_COOKIE['wordsearch_entries'] ) ) {
            $raw_data = wp_unslash( $_COOKIE['wordsearch_entries'] );
            $entries  = json_decode( $raw_data, true );
    
            // Check if JSON decoded correctly and is an array.
            if ( json_last_error() === JSON_ERROR_NONE && is_array( $entries ) ) {
                // Sanitize each entry before saving.
                $sanitized_entries = array_map( function( $entry ) {
                    return array(
                        'id'       => sanitize_text_field( isset( $entry['id'] ) ? $entry['id'] : '' ),
                        'wordText' => sanitize_text_field( isset( $entry['wordText'] ) ? $entry['wordText'] : '' ),
                        'imageUrl' => esc_url_raw( isset( $entry['imageUrl'] ) ? $entry['imageUrl'] : '' ),
                    );
                }, $entries );

        // Optionally, if you need to pass word entries or other localized data:
        }
    }
    wp_localize_script( 'wordsearch-grid', 'frontendData', array(
      'entries'          => json_encode($sanitized_entries),
      'maximunGridSize'  => 10,
      'shuffleElement'   => 'shuffleButton',
      'checkBoxElement'  => 'toggle-checkbox',
      'gridStyles'       => array( 
          'fontColor'              => esc_attr( $gridTextColor ),
          'fontFamily'             => esc_attr( $gridTextFontFamily ),
          'bgColor'                => esc_attr( $grid_bg_color ),
          'higlightedCellTextColor'=> esc_attr( $higlightedCellTextColor ),
          'lineColor'              => esc_attr( $lineColor ),
      )
  ));
    }
}
add_action( 'admin_enqueue_scripts', 'enqueue_wordsearch_metabox_preview_assets' );

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
$show_answer_label = get_option('kw_wordsearch_admin_show_answers_checkbox_label', $default_show_answers_label);
$default_shuffle_button_label = __('Shuffle', 'wp-quiz-plugin');
$shuffle_label =  get_option('kw_wordsearch_admin_shuffle_button_label', $default_shuffle_button_label);
$shuffle_bg_color = get_option('kw_wordsearch_admin_shuffle_button_color', '#0073aa');
$shuffle_text_color = get_option('kw_wordsearch_admin_shuffle_button_text_color', '#ffffff');

  
  // Fallback for the shuffle label
    ?>
    <div id="wordsearch-preview-container">
    <div class="button-checkbox-container">
    <label class="checkbox-label">
    <input type="checkbox" class="toggle-checkbox" id="toggle-answers">
    <?php echo esc_html__($show_answer_label); ?>
    </label>

    <button id="shuffleButton" class="shuffle-button" style="background-color: <?php echo esc_attr($shuffle_bg_color); ?>; color: <?php echo esc_attr($shuffle_text_color); ?>;">
    <?php echo esc_html__($shuffle_label); ?>
    </button>

  <!-- <button id="shuffleButton" class="shuffle-button" >Shuffle</button> -->

</div>
      <!-- Empty state message; hidden by default -->
      <p id="empty-message" style="text-align: center; display: none;">
        No word search entries found. 
      </p>
      <!-- Game preview content is rendered but hidden -->
      <div id="game-preview-content" style="display: none;">
         <?php render_game(); ?>
      </div>
    </div>

    <script>
      (function(){

          // Helper: Retrieve a cookie by name.
          function getCookie(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(";");
            for (var i = 0; i < ca.length; i++) {
              var c = ca[i].trim();
              if (c.indexOf(nameEQ) === 0) {
                return c.substring(nameEQ.length, c.length);
              }
            }
            return null;
          }

          // Checks the cookie and toggles the preview accordingly.
          function checkAndTogglePreview() {
            var rawData = getCookie('wordsearch_entries');
            var entries = rawData ? JSON.parse(rawData) : [];
            var emptyMsg = document.getElementById('empty-message');
            var gameContent = document.getElementById('game-preview-content');

            if (entries.length > 0) {
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
              // Also stop the timer in wordsearch-grid.js if it's running.
              if (typeof window.stopWordsearchGridTimer === 'function') {
                      window.stopWordsearchGridTimer();
                  }
            }
          }

          // Run check on page load.
          checkAndTogglePreview();
          // Optionally re-check every second to reflect runtime changes.
          setInterval(checkAndTogglePreview, 1000);
      })();
    </script>
    <?php
}