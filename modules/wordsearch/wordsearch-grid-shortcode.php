<?php
// Shortcode function to display the WordSearch game on the frontend

function render_game(){
    ?>
    <div class="wordsearch-container">
        <ul id="wordList"></ul>
        <!-- <div id="outerWrapper"> -->
        <div id="game-container"></div>
        <!-- </div> -->
    </div>
    
    <!-- Word list panel -->
    <div id="completionBanner">Well Done!</div>
  
    <div id="completionModal" style="display: none; position: fixed; top: 50%; left: 50%; 
         transform: translate(-50%, -50%); background: white; border: 2px solid #333; padding: 20px; z-index: 9999;">
        <h2>Congratulations!</h2>
        <p>You have found all the words!</p>
        <button onclick="document.getElementById('completionModal').style.display='none';">Close</button>
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