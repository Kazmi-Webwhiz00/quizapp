<?php
// Shortcode function to display the WordSearch game on the frontend
 
function wsp_display_game($atts) {
    ob_start(); // Start output buffering

    ?>
    <div id="main" role="main">
        <h1>WordFind Word Search Puzzle</h1>
        <!-- Puzzle container: "position: relative" allows a Konva overlay -->
        <div id="puzzle" style="position: relative;"></div>
        <!-- Word list (the game code will insert words here) -->
        <ul id="words">
            <li><button id="add-word">Add word</button></li>
        </ul>
        <!-- Controls -->
        <fieldset id="controls">
            <label for="allowed-missing-words">Allowed missing words :
                <input id="allowed-missing-words" type="number" min="0" max="5" step="1" value="2">
            </label>
            <label for="max-grid-growth">Max grid growth :
                <input id="max-grid-growth" type="number" min="0" max="5" step="1" value="0">
            </label>
            <label for="extra-letters">Extra letters :
                <select id="extra-letters">
                    <option value="secret-word" selected>form a secret word</option>
                    <option value="none">none, allow blanks</option>
                    <option value="secret-word-plus-blanks">form a secret word but allow for extra blanks</option>
                    <option value="random">random</option>
                </select>
            </label>
            <label for="secret-word">Secret word :
                <input id="secret-word" type="text">
            </label>
            <button id="create-grid">Create grid</button>
            <p id="result-message"></p>
            <button id="solve">Solve Puzzle</button>
        </fieldset>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'word_search_puzzle', 'wsp_display_game' );
