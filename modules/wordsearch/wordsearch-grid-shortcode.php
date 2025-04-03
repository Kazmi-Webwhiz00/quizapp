<?php
// Shortcode function to display the WordSearch game on the frontend

function showWordLimitModal(){
  ob_start();
  include plugin_dir_path(__FILE__) . 'assets/templates/wordsearch-limit-modal.php'; 
  return ob_get_clean();
}

function render_game(){

    // Retrieve settings for Add Word and Clear List buttons
// Retrieve settings for Add Word and Clear List buttons
$default_success_popup_label = __('Congratulations', 'wp-quiz-plugin');
$success_popup_title = get_option('kw_wordsearch_success_popup_title', $default_success_popup_label);
$default_success_body_default_text = __('You have successfully completed the wordsearch!', 'wp-quiz-plugin');
$success_popup_body_text = get_option('kw_wordsearch_success_popup_body_text', $default_success_body_default_text);
$default_popup_button_text = __('Play Again', 'wp-quiz-plugin');
$success_popup_button_text = get_option('kw_wordsearch_success_popup_button_text', $default_popup_button_text);
$default_grid_title_label = __('Word Search Challenge', 'wp-quiz-plugin');
$default_find_words_label = __('Find These Words:', 'wp-quiz-plugin');
$default_download_pdf_label = __('Download Pdf', 'wp-quiz-plugin');
// Grid Title Label
$gridTitleLabel = get_option('kw_grid_title_label', $default_grid_title_label);
// Find Words Label
$gridFindWordsLabel = get_option('kw_find_words_label', $default_find_words_label);
// Download Pdf
$downloadPdfLabel = get_option('kw_download_pdf_label', $default_download_pdf_label);
$soundEnabled = get_option('kw_grid_text_sound_setting', 'true') ;
// Show or hide Images Label
$showImagesLabel = __('Show Images', 'wp-quiz-plugin');

    ?>
<div class="wordsearch-container">
<div class="game-header">
  <div class="game-controls">

    <div class="control-button timer-container">
      <div class="button-icon timer-icon">⏱️</div>
      <p id="timerDisplay" class="button-text">00:00</p>
    </div>
    
<!-- Replace the emoji sound icons with SVG speaker icons -->
<button id="soundToggleButton" class="control-button sound-toggle" aria-label="Toggle Sound" title="Toggle Sound">
  <span id="soundOnIcon" class="button-icon sound-icon">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
      <path d="M15 8a5 5 0 0 1 0 8"></path>
      <path d="M18 5a10 10 0 0 1 0 14"></path>
    </svg>
  </span>
  <span id="soundOffIcon" class="button-icon sound-icon" style="display: none;">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
      <line x1="23" y1="9" x2="17" y2="15"></line>
      <line x1="17" y1="9" x2="23" y2="15"></line>
    </svg>
  </span>
</button>

  </div>
  
  <h2 class="game-title"><?php echo esc_html__($gridTitleLabel); ?></h2>

  <button id="downloadButton" class="control-button download-button">
      <span class="button-icon">📥</span>
      <span class="button-text"><?php echo esc_html__($downloadPdfLabel); ?></span>
    </button>
</div>
  
  <!-- Add theme-container to wrap game content and image clues -->
  <div class="game-main-content">
    <!-- Visual clues container now as a separate column -->

    <div class="word-panel">
      <div class="word-panel-header">
      <button class="mobile-sidebar-toggle">
      <span class="button-icon">🖼️</span>
      <span class="button-text"><?php echo esc_html__($showImagesLabel); ?></span>
    </button>
        <h3 class="word-panel-title"><?php echo esc_html__($gridFindWordsLabel); ?></h3>
        </div>
        <ul id="wordList"></ul>
      </div>


      <div class="game-content-wrapper">
    <div class="game-content">
      <div id="game-container"></div>
       <!-- Desktop Clues Container: Shown on large screens only -->
  <div class="visual-clues-container" id="desktopCluesContainer">
      <!-- Images will be dynamically inserted here via JavaScript -->
    </div>
    </div>
      <!-- Mobile Sidebar: Holds clue images for small screens -->
      <div class="sidebar-panel" id="sidebarPanel">
    <!-- Close button inside the mobile sidebar -->
    <!-- <button class="close-sidebar-button">✖</button> -->
    <div class="visual-clues-container" id="mobileCluesContainer">

    </div>
  </div>
  </div>
  </div>

  
  <div id="completionBanner">Well Done!</div>
</div>

    <div id="completionModal" style="display: none; padding: 20px; z-index: 9999;">
  <div class="container-inner">
    <div class="content">
      <h2><?php echo $success_popup_title; ?></h2>
      <p><?php echo $success_popup_body_text; ?></p>
    </div>
    <div class="buttons">
      <button class="close" id="closeModal"><?php echo $success_popup_button_text; ?></button>
    </div>
  </div>
</div>
<!-- This will be shown/hidden programmatically -->
<div class="grid-loading-indicator" style="display: none;">
  <div class="grid-loading-spinner"></div>
  <div style="color: #473214; font-weight: bold;" class="renderingPara">Rendering grid...</div>
</div>

<?php
echo showWordLimitModal();
?>

    <?php
}

function display_word_search_game() {
    ob_start();
    error_log("display_word_search_game() has been called");
    // Directly call the render_game() function
    render_game();
    return ob_get_clean();
}
add_shortcode('word_search_puzzle', 'display_word_search_game');