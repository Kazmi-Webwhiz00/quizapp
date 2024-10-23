jQuery(document).ready(function ($) {
    // Access the localized data from PHP
    const wordsClues = crosswordData.wordsClues;

    // Function to generate a simple crossword grid
    function generateCrosswordGrid(container, wordsClues) {
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
            const word = entry.word.toUpperCase();
            const direction = index % 2 === 0 ? 'across' : 'down';
            const startX = Math.floor(Math.random() * (gridSize - word.length));
            const startY = Math.floor(Math.random() * (gridSize - word.length));
            placeWord(word, startX, startY, direction);
        });

        // Render the grid in the container
        const table = $('<table class="crossword-table"></table>');
        grid.forEach((row, rowIndex) => {
            const tableRow = $('<tr></tr>');
            row.forEach((cell, colIndex) => {
                const tableCell = $('<td></td>');
                if (cell) {
                    tableCell.text(cell);
                    tableCell.addClass('filled-cell');
                }
                tableRow.append(tableCell);
            });
            table.append(tableRow);
        });

        $(container).empty().append(table);
    }

    // Render the crossword grid when the DOM is ready
    generateCrosswordGrid('#crossword-grid', wordsClues);
});
