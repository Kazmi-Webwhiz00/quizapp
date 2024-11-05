jQuery(document).ready(function ($) {
    // Function to generate a simple crossword grid
    function generateCrosswordGrid(container) {
        // Fetch words and clues from the input fields
        const wordsClues = [];
        $('.crossword-word-clue').each(function () {
            const index = $(this).data('index');
            const word = $(`input[name="crossword_words[${index}][word]"]`).val();
            const clue = $(`input[name="crossword_words[${index}][clue]"]`).val();

            if (word) {
                wordsClues.push({ word: word.toUpperCase(), clue });
            }
        });

        // Define the grid dimensions (rows and columns)
        const gridSize = 10; // Example size; adjust as needed
        const grid = [];

        // Initialize an empty grid
        for (let i = 0; i < gridSize; i++) {
            grid[i] = new Array(gridSize).fill('');
        }

        // Function to place a word in the grid (simple implementation)
        function placeWord(word, startX, startY, direction) {
            if (direction === 'across') {
                for (let i = 0; i < word.length; i++) {
                    grid[startY][startX + i] = word[i];
                }
            } else if (direction === 'down') {
                for (let i = 0; i < word.length; i++) {
                    grid[startY + i][startX] = word[i];
                }
            }
        }

        // Iterate over words and clues and place them in the grid
        wordsClues.forEach((entry, index) => {
            const word = entry.word;
            const direction = index % 2 === 0 ? 'across' : 'down';
            const startX = Math.floor(Math.random() * (gridSize - word.length));
            const startY = Math.floor(Math.random() * (gridSize - word.length));
            placeWord(word, startX, startY, direction);
        });

        // Render the grid in the container
        const table = $('<table class="crossword-table"></table>');
        grid.forEach((row) => {
            const tableRow = $('<tr></tr>');
            row.forEach((cell) => {
                const tableCell = $('<td></td>');
                if (cell) {
                    tableCell.text(cell);
                    tableCell.addClass('filled-cell');
                } else {
                    tableCell.addClass('empty-cell');
                }
                tableRow.append(tableCell);
            });
            table.append(tableRow);
        });

        $(container).empty().append(table);
    }

    // Function to show/hide crossword answers based on the checkbox state
    function toggleAnswers() {
        const showAnswers = $('#toggle-answers').is(':checked');
        $('.crossword-table td.filled-cell').each(function () {
            if (showAnswers) {
                $(this).css('color', '#000'); // Show the letters (default color)
            } else {
                $(this).css('color', 'transparent'); // Hide the letters (make them transparent)
            }
        });
    }

    // Update grid when inputs change
    $(document).on('input', '.crossword-word-clue input', function () {
        generateCrosswordGrid('#crossword-grid');
        toggleAnswers(); // Make sure the grid is updated based on checkbox state
    });

    // Update grid when the checkbox is toggled
    $('#toggle-answers').on('change', toggleAnswers);

    // Update grid when a new word is added
    $('#add-word-button').on('click', function () {
        setTimeout(() => {
            generateCrosswordGrid('#crossword-grid');
            toggleAnswers(); // Make sure the grid is updated based on checkbox state
        }, 100);
    });

    // Trigger grid generation and set the initial visibility when the page loads
    generateCrosswordGrid('#crossword-grid');
    toggleAnswers();
});
