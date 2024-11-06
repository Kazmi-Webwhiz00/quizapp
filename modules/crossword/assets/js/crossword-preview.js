jQuery(document).ready(function ($) {
    // Declare gridSize and grid at a higher scope so they are accessible in all functions
    let gridSize;
    let grid;

    function generateCrosswordGrid(container) {
        // Fetch words, clues, and clue images from the input fields
        let wordsData = [];
        $('.crossword-word-clue').each(function () {
            const index = $(this).data('index');
            const word = $(`input[name="crossword_words[${index}][word]"]`).val();
            const clue = $(`input[name="crossword_words[${index}][clue]"]`).val();
            const image = $(`input[name="crossword_words[${index}][image]"]`).val();

            if (word) {
                wordsData.push({
                    word: word.toUpperCase(),
                    clue: clue || '',
                    image: image || '',
                    clueNumber: null, // Initialize clueNumber
                });
            }
        });

        if (wordsData.length === 0) {
            $(container).empty().append('<p>Please add some words to generate the crossword.</p>');
            $('#clues-container').empty();
            return;
        }

        // Sort words by length in descending order
        wordsData.sort((a, b) => b.word.length - a.word.length);

        // Calculate dynamic grid size based on words
        const longestWordLength = wordsData[0].word.length;
        const totalLetters = wordsData.reduce((sum, wordObj) => sum + wordObj.word.length, 0);
        gridSize = Math.max(longestWordLength + 10, Math.ceil(Math.sqrt(totalLetters)) + 10);
        gridSize = Math.min(gridSize, 100); // Set a maximum grid size to prevent excessive size

        // Initialize grid
        grid = [];
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
                if (x < 0 || x + word.length > gridSize || y < 0 || y >= gridSize) return false;
                for (let i = 0; i < word.length; i++) {
                    const cell = grid[y][x + i];
                    if (cell && cell.letter !== word[i]) {
                        return false;
                    }
                    // Check adjacent cells for conflicts
                    if (cell === null) {
                        if (y > 0 && grid[y - 1][x + i] && grid[y - 1][x + i].letter) return false;
                        if (y < gridSize - 1 && grid[y + 1][x + i] && grid[y + 1][x + i].letter) return false;
                    }
                }
            } else if (direction === DOWN) {
                if (y < 0 || y + word.length > gridSize || x < 0 || x >= gridSize) return false;
                for (let i = 0; i < word.length; i++) {
                    const cell = grid[y + i][x];
                    if (cell && cell.letter !== word[i]) {
                        return false;
                    }
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
        function placeWord(wordObj, x, y, direction) {
            const { word } = wordObj;
            wordObj.x = x;
            wordObj.y = y;
            wordObj.direction = direction;
            placedWords.push(wordObj);

            if (direction === ACROSS) {
                for (let i = 0; i < word.length; i++) {
                    if (!grid[y][x + i]) {
                        grid[y][x + i] = { letter: word[i], across: wordObj, down: null, clueNumber: null };
                    } else {
                        grid[y][x + i].across = wordObj;
                    }
                }
            } else if (direction === DOWN) {
                for (let i = 0; i < word.length; i++) {
                    if (!grid[y + i][x]) {
                        grid[y + i][x] = { letter: word[i], across: null, down: wordObj, clueNumber: null };
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
                                    // Try placing word vertically
                                    direction = DOWN;
                                    x = placedWord.x + j;
                                    y = placedWord.y - i;
                                } else {
                                    // Try placing word horizontally
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
            if (index >= wordsData.length) {
                return true; // All words placed
            }

            const wordObj = wordsData[index];
            const positions = findPositions(wordObj.word);

            // Shuffle positions to add randomness
            positions.sort(() => Math.random() - 0.5);

            for (let pos of positions) {
                placeWord(wordObj, pos.x, pos.y, pos.direction);
                if (placeWords(index + 1)) {
                    return true;
                }
                removeWord(wordObj);
            }

            return false; // Unable to place word at any position
        }

        // Start placing words
        const success = placeWords(0);

        // If not all words could be placed, collect unplaced words
        let unplacedWords = [];
        if (!success) {
            unplacedWords = wordsData.slice(placedWords.length).map(w => w.word);
        }

        // If unplaced words exist, display a message
        if (unplacedWords.length > 0) {
            const unplacedList = unplacedWords.join(', ');
            const message = `Keep adding new words! The following words can't be included in the crossword puzzle because they do not have enough letters in common with other words: ${unplacedList}`;
            $('#error-message').text(message).show();
        } else {
            $('#error-message').hide();
        }

        // Assign clue numbers after all words have been placed
        assignClueNumbers();

        // Render the grid in the container
        const table = $('<table class="crossword-table"></table>');
        for (let y = 0; y < gridSize; y++) {
            const tableRow = $('<tr></tr>');
            for (let x = 0; x < gridSize; x++) {
                const cell = grid[y][x];
                const tableCell = $('<td></td>');
                if (cell) {
                    tableCell.addClass('filled-cell');
                    if (cell.clueNumber && !tableCell.find('.clue-number').length) {
                        tableCell.append(`<span class="clue-number">${cell.clueNumber}</span>`);
                    }
                    tableCell.append(`<span class="letter">${cell.letter}</span>`);
                } else {
                    tableCell.addClass('empty-cell');
                }
                tableRow.append(tableCell);
            }
            table.append(tableRow);
        }

        $(container).empty().append(table);

        // Display clues with optional images
        const acrossClues = $('<ul></ul>');
        const downClues = $('<ul></ul>');
        placedWords.forEach((wordObj) => {
            const clueItem = $('<li></li>');
            clueItem.append(`<strong>${wordObj.clueNumber}.</strong> ${wordObj.clue}`);
            if (wordObj.image) {
                clueItem.append(`<br><img src="${wordObj.image}" alt="Clue image" class="clue-image">`);
            }
            if (wordObj.direction === ACROSS) {
                acrossClues.append(clueItem);
            } else {
                downClues.append(clueItem);
            }
        });

        $('#clues-container').empty();
        $('#clues-container').append('<h3>Across</h3>');
        $('#clues-container').append(acrossClues);
        $('#clues-container').append('<h3>Down</h3>');
        $('#clues-container').append(downClues);

        // If you have a function to update hidden fields, make sure it's defined
        if (typeof crossword !== 'undefined' && typeof crossword.updateHiddenFields === 'function') {
            crossword.updateHiddenFields();
        }
    }

    // Function to assign clue numbers based on grid positions
    function assignClueNumbers() {
        let clueNumber = 1;
        for (let y = 0; y < gridSize; y++) {
            for (let x = 0; x < gridSize; x++) {
                const cell = grid[y][x];
                if (cell && cell.letter && !cell.clueNumber) {
                    let isStartOfWord = false;
                    // Check for across word
                    if (
                        cell.across &&
                        cell.across.x === x &&
                        cell.across.y === y &&
                        !cell.across.clueNumber &&
                        ((x === 0) || (grid[y][x - 1] === null))
                    ) {
                        cell.across.clueNumber = clueNumber;
                        isStartOfWord = true;
                    }
                    // Check for down word
                    if (
                        cell.down &&
                        cell.down.x === x &&
                        cell.down.y === y &&
                        !cell.down.clueNumber &&
                        ((y === 0) || (grid[y - 1][x] === null))
                    ) {
                        cell.down.clueNumber = clueNumber;
                        isStartOfWord = true;
                    }
                    if (isStartOfWord) {
                        cell.clueNumber = clueNumber;
                        clueNumber++;
                    }
                }
            }
        }
    }

    // Function to show/hide crossword answers based on the checkbox state
    function toggleAnswers() {
        const showAnswers = $('#toggle-answers').is(':checked');
        $('.letter').css('color', showAnswers ? '#000' : 'transparent');
    }

    // Update grid when inputs change
    $(document).on('input', '.crossword-word-clue input[name^="crossword_words"]', function () {
        generateCrosswordGrid('#crossword-grid');
        toggleAnswers();
    });

    // Update grid when the checkbox is toggled
    $('#toggle-answers').on('change', toggleAnswers);

    // Shuffle button functionality
    $('#shuffle-button').on('click', function () {
        generateCrosswordGrid('#crossword-grid');
        toggleAnswers();
    });

    // Initial grid generation
    crossword.populateCrosswordFromData('#crossword-grid');
    toggleAnswers();
});
