<?php
// Shortcode function to display the WordSearch game on the frontend

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
        <div class="left-panel">
        <ul id="wordList"></ul>
        <div class="grid-timer arrow-right-center">
        <p id="timerDisplay"></p>
        </div>
        </div>
        <div id="game-container"></div>
    </div>
    
    <!-- Word list panel -->
    <div id="completionBanner">Well Done!</div>
        
    <!-- NEW: Timer display div for styling -->
    <!-- <div id="timerDisplay" style="font-size: 18px; font-weight: bold; margin-top: 10px;">
        Time: 0 seconds
    </div> -->
  
    <!-- <div id="completionModal" style="display: none; position: fixed; top: 50%; left: 50%; 
         transform: translate(-50%, -50%); background: white; border: 2px solid #333; padding: 20px; z-index: 9999;">
        <h2><?php echo $success_popup_title; ?></h2>
        <p><?php echo $success_popup_body_text; ?></p>
        <button id="closeModal"><?php echo $success_popup_button_text; ?></button>
    </div> -->

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