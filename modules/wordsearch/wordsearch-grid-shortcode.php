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
$default_popup_button_text = __('Close', 'wp-quiz-plugin');
$success_popup_button_text = get_option('kw_wordsearch_success_popup_button_text', $default_popup_button_text);

    ?>
<div class="wordsearch-container">
  <div class="game-header">
    <h2 class="game-title">Word Search Challenge</h2>
    <div class="timer-container">
      <div class="timer-icon">⏱️</div>
      <p id="timerDisplay">00:00</p>
    </div>
  </div>
  
  <div class="game-content">
    <div id="game-container"></div>
    
    <div class="word-panel">
      <h3 class="word-panel-title">Find These Words:</h3>
      <ul id="wordList"></ul>
      <!-- <button id="shuffle-btn" class="shuffle-button">Shuffle</button> -->
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