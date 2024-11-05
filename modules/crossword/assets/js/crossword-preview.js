jQuery(document).ready(function ($) {
    function generateCrosswordGrid(container) {
        // Fetch words from the input fields
        let words = [];
        $('.crossword-word-clue').each(function () {
            const index = $(this).data('index');
            const word = $(`input[name="crossword_words[${index}][word]"]`).val();

            if (word) {
                words.push(word.toUpperCase());
            }
        });

        // Sort words by length in descending order
        words.sort((a, b) => b.length - a.length);

        // Initialize grid size
        const gridSize = 15; // Adjust as needed
        let grid = [];
        for (let i = 0; i < gridSize; i++) {
            grid[i] = new Array(gridSize).fill(null);
        }

        // Initialize placed words array
        let placedWords = [];

        // Directions
        const ACROSS = 'across';
        const DOWN = 'down';

        // Function to check if a word can be placed at the given position and direction
        function canPlaceWord(word, x, y, direction) {
            if (direction === ACROSS) {
                if (x < 0 || x + word.length > gridSize) return false;
                for (let i = 0; i < word.length; i++) {
                    const cell = grid[y][x + i];
                    if (cell && cell.letter !== word[i]) {
                        return false;
                    }
                    // Additional checks to ensure crossword rules are followed
                    // Check adjacent cells for conflicts
                    if (cell === null) {
                        if (y > 0 && grid[y - 1][x + i] && grid[y - 1][x + i].letter) return false;
                        if (y < gridSize - 1 && grid[y + 1][x + i] && grid[y + 1][x + i].letter) return false;
                    }
                }
            } else if (direction === DOWN) {
                if (y < 0 || y + word.length > gridSize) return false;
                for (let i = 0; i < word.length; i++) {
                    const cell = grid[y + i][x];
                    if (cell && cell.letter !== word[i]) {
                        return false;
                    }
                    // Additional checks to ensure crossword rules are followed
                    // Check adjacent cells for conflicts
                    if (cell === null) {
                        if (x > 0 && grid[y + i][x - 1] && grid[y + i][x - 1].letter) return false;
                        if (x < gridSize - 1 && grid[y + i][x + 1] && grid[y + i][x + 1].letter) return false;
                    }
                }
            }
            return true;
        }

        // Function to place a word on the grid
        function placeWord(word, x, y, direction) {
            const wordObj = { word, x, y, direction };
            placedWords.push(wordObj);

            if (direction === ACROSS) {
                for (let i = 0; i < word.length; i++) {
                    if (!grid[y][x + i]) {
                        grid[y][x + i] = { letter: word[i], across: wordObj, down: null };
                    } else {
                        grid[y][x + i].across = wordObj;
                    }
                }
            } else if (direction === DOWN) {
                for (let i = 0; i < word.length; i++) {
                    if (!grid[y + i][x]) {
                        grid[y + i][x] = { letter: word[i], across: null, down: wordObj };
                    } else {
                        grid[y + i][x].down = wordObj;
                    }
                }
            }
        }

        // Function to remove a word from the grid
        function removeWord(wordObj) {
            const { word, x, y, direction } = wordObj;
            placedWords = placedWords.filter(w => w !== wordObj);

            if (direction === ACROSS) {
                for (let i = 0; i < word.length; i++) {
                    const cell = grid[y][x + i];
                    if (cell) {
                        if (cell.across === wordObj) {
                            if (cell.down === null) {
                                grid[y][x + i] = null;
                            } else {
                                cell.across = null;
                            }
                        }
                    }
                }
            } else if (direction === DOWN) {
                for (let i = 0; i < word.length; i++) {
                    const cell = grid[y + i][x];
                    if (cell) {
                        if (cell.down === wordObj) {
                            if (cell.across === null) {
                                grid[y + i][x] = null;
                            } else {
                                cell.down = null;
                            }
                        }
                    }
                }
            }
        }

        // Function to find possible positions to place a word
        function findPositions(word) {
            let positions = [];

            if (placedWords.length === 0) {
                // Place the first word in the center
                const x = Math.floor((gridSize - word.length) / 2);
                const y = Math.floor(gridSize / 2);
                positions.push({ x, y, direction: ACROSS });
                positions.push({ x, y, direction: DOWN });
            } else {
                for (let placedWord of placedWords) {
                    for (let i = 0; i < word.length; i++) {
                        for (let j = 0; j < placedWord.word.length; j++) {
                            if (word[i] === placedWord.word[j]) {
                                let x, y, direction;

                                if (placedWord.direction === ACROSS) {
                                    direction = DOWN;
                                    x = placedWord.x + j;
                                    y = placedWord.y - i;
                                } else {
                                    direction = ACROSS;
                                    x = placedWord.x - i;
                                    y = placedWord.y + j;
                                }

                                if (canPlaceWord(word, x, y, direction)) {
                                    positions.push({ x, y, direction });
                                }
                            }
                        }
                    }
                }
            }
            return positions;
        }

        // Recursive function to try placing words
        function placeWords(index) {
            if (index >= words.length) {
                return true; // All words placed
            }

            const word = words[index];
            const positions = findPositions(word);

            for (let pos of positions) {
                placeWord(word, pos.x, pos.y, pos.direction);
                if (placeWords(index + 1)) {
                    return true;
                }
                removeWord(placedWords[placedWords.length - 1]);
            }
            return false; // Unable to place word
        }

        // Start placing words
        const success = placeWords(0);

        if (!success) {
            console.warn("Unable to place all words on the grid.");
        }

        // Render the grid in the container
        const table = $('<table class="crossword-table"></table>');
        for (let y = 0; y < gridSize; y++) {
            const tableRow = $('<tr></tr>');
            for (let x = 0; x < gridSize; x++) {
                const cell = grid[y][x];
                const tableCell = $('<td></td>');
                if (cell) {
                    tableCell.text(cell.letter);
                    tableCell.addClass('filled-cell');
                } else {
                    tableCell.addClass('empty-cell');
                }
                tableRow.append(tableCell);
            }
            table.append(tableRow);
        }

        $(container).empty().append(table);
    }

    // Function to show/hide crossword answers based on the checkbox state
    function toggleAnswers() {
        const showAnswers = $('#toggle-answers').is(':checked');
        $('.crossword-table td.filled-cell').each(function () {
            if (showAnswers) {
                $(this).css('color', '#000');
            } else {
                $(this).css('color', 'transparent');
            }
        });
    }

    // Update grid when inputs change
    $(document).on('input', '.crossword-word-clue input', function () {
        generateCrosswordGrid('#crossword-grid');
        toggleAnswers();
    });

    // Update grid when the checkbox is toggled
    $('#toggle-answers').on('change', toggleAnswers);

    // Update grid when a new word is added
    $('#add-word-button').on('click', function () {
        setTimeout(() => {
            generateCrosswordGrid('#crossword-grid');
            toggleAnswers();
        }, 100);
    });

    // Trigger grid generation and set the initial visibility when the page loads
    generateCrosswordGrid('#crossword-grid');
    toggleAnswers();
});
