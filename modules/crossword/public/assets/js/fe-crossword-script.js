jQuery(document).ready(function ($) {
    let words = [];
    let currentWord = null; // Keep track of the current word
    let gridData = null; // Move gridData to a higher scope

    function populateCrosswordFromData(container) {
        let crosswordData = $('#crossword-data').val();
        if (!crosswordData) {
            console.error('No crossword data found in #crossword-data');
            return;
        }

        let data;
        try {
            data = JSON.parse(crosswordData);
        } catch (e) {
            console.error('Invalid JSON data in #crossword-data');
            return;
        }

        gridData = data.grid; // Assign to the global gridData variable

        // Extract words from gridData
        words = extractWordsFromGrid(gridData, data);

        // Build a mapping from cell positions to words
        let cellToWordsMap = {};
        words.forEach(wordObj => {
            wordObj.cells.forEach(cell => {
                let key = cell.x + ',' + cell.y;
                if (!cellToWordsMap[key]) {
                    cellToWordsMap[key] = [];
                }
                cellToWordsMap[key].push(wordObj);
            });
        });

        const table = $('<table class="crossword-table"></table>');

        // Add CSS classes (no inline CSS)
        if (!$('#crossword-styles').length) {
            $('head').append(`
                <style id="crossword-styles">
                    .filled-cell {
                        background-color: ${cross_ajax_obj.filledCellColor};
                    }
                    .correct-cell {
                        background-color: ${cross_ajax_obj.correctedCellColor} !important;
                    }
                    .highlighted-cell input {
                        background-color: ${cross_ajax_obj.highlightColor} !important;
                    }
                    .highlighted-clue {
                        background-color: ${cross_ajax_obj.highlightColor};
                    }
                    #clues-container-fe ul li {
                        font-size: ${cross_ajax_obj.fontSize};
                        font-family: ${cross_ajax_obj.fontFamily};
                        color: ${cross_ajax_obj.fontColor};
                    }
                    .clue-image {
                        height: ${cross_ajax_obj.clueImageHeight} !important;
                        width: ${cross_ajax_obj.clueImageWidth} !important;
                    }
                </style>
            `);
        }

        for (let y = 0; y < gridData.length; y++) {
            const tableRow = $('<tr></tr>');
            for (let x = 0; x < gridData[y].length; x++) {
                const cell = gridData[y][x];
                const tableCell = $('<td></td>');
                if (cell && cell.letter) {
                    tableCell.addClass('filled-cell');
                    if (cell.clueNumber) {
                        tableCell.append(`<span class="clue-number">${cell.clueNumber}</span>`);
                    }
                    const input = $('<input type="text" maxlength="1" class="letter-input">');
                    input.data('x', x);
                    input.data('y', y);
                    input.on('keydown', handleNavigation); // Arrow key and Backspace handling
                    input.on('input', handleInput); // Overwrite existing letters
                    input.on('focus', handleFocus); // Highlight word and clue

                    // Store the words that this cell is part of
                    let key = x + ',' + y;
                    if (cellToWordsMap[key]) {
                        input.data('words', cellToWordsMap[key]);
                    }

                    tableCell.append(input);
                } else {
                    tableCell.addClass('empty-cell');
                }
                tableRow.append(tableCell);
            }
            table.append(tableRow);
        }

        $(container).empty().append(table);

        // Render clues
        renderClues(data.clues);

        // Attach validation logic
        $('#validate-crossword').on('click', function () {
            validateCrossword();
        });

        $('#check-words').on('change', function () {
            if ($(this).is(':checked')) {
                enableLiveValidation();
            } else {
                disableLiveValidation();
            }
        });
    }

    function extractWordsFromGrid(gridData, data) {
        let words = [];
        let gridHeight = gridData.length;
        let gridWidth = gridData[0].length;
        let clueNumberToClueTextMap = {};

        // Build a map of clue numbers to clue texts
        if (data.clues && data.clues.across) {
            data.clues.across.forEach(clueObj => {
                clueNumberToClueTextMap['A' + clueObj.clueNumber] = clueObj.clueText;
            });
        }
        if (data.clues && data.clues.down) {
            data.clues.down.forEach(clueObj => {
                clueNumberToClueTextMap['D' + clueObj.clueNumber] = clueObj.clueText;
            });
        }

        for (let y = 0; y < gridHeight; y++) {
            for (let x = 0; x < gridWidth; x++) {
                let cell = gridData[y][x];
                if (cell && cell.letter) {
                    // Check for ACROSS word starting here
                    let isStartOfAcross = (x === 0 || !gridData[y][x - 1] || !gridData[y][x - 1].letter) &&
                        (x + 1 < gridWidth && gridData[y][x + 1] && gridData[y][x + 1].letter);
                    if (isStartOfAcross) {
                        let wordObj = {
                            clueNumber: cell.clueNumber,
                            direction: 'across',
                            cells: [],
                            clueText: clueNumberToClueTextMap['A' + cell.clueNumber] || '',
                            x: x,
                            y: y
                        };
                        let i = x;
                        while (i < gridWidth && gridData[y][i] && gridData[y][i].letter) {
                            wordObj.cells.push({ x: i, y: y, letter: gridData[y][i].letter });
                            i++;
                        }
                        words.push(wordObj);
                    }

                    // Check for DOWN word starting here
                    let isStartOfDown = (y === 0 || !gridData[y - 1][x] || !gridData[y - 1][x].letter) &&
                        (y + 1 < gridHeight && gridData[y + 1][x] && gridData[y + 1][x].letter);
                    if (isStartOfDown) {
                        let wordObj = {
                            clueNumber: cell.clueNumber,
                            direction: 'down',
                            cells: [],
                            clueText: clueNumberToClueTextMap['D' + cell.clueNumber] || '',
                            x: x,
                            y: y
                        };
                        let i = y;
                        while (i < gridHeight && gridData[i][x] && gridData[i][x].letter) {
                            wordObj.cells.push({ x: x, y: i, letter: gridData[i][x].letter });
                            i++;
                        }
                        words.push(wordObj);
                    }
                }
            }
        }

        return words;
    }

    function handleInput(e) {
        const input = $(e.target);

        // Overwrite existing letter with the new input
        let value = input.val().slice(-1).toUpperCase();
        input.val(value);

        // Remove 'correct-cell' class when user changes input
        input.closest('td').removeClass('correct-cell');

        // Live validation
        if ($('#check-words').is(':checked')) {
            validateCell(input);
        }

        // Move to next cell in the current word
        if (currentWord) {
            // Find the index of the current cell in the word
            const x = input.data('x');
            const y = input.data('y');
            const currentIndex = currentWord.cells.findIndex(cell => cell.x === x && cell.y === y);
            if (currentIndex >= 0 && currentIndex < currentWord.cells.length - 1) {
                // Move to next cell
                const nextCell = currentWord.cells[currentIndex + 1];
                navigateToCell($('.crossword-table'), nextCell.x, nextCell.y);
            }
        }
    }

    function handleNavigation(e) {
        const input = $(e.target);
        const x = input.data('x');
        const y = input.data('y');
        const table = $('.crossword-table');

        // If a letter key is pressed, overwrite the existing value
        if (e.key.length === 1 && e.key.match(/^[a-zA-Z]$/)) {
            e.preventDefault(); // Prevent default input
            input.val(e.key.toUpperCase());

            // Remove 'correct-cell' class when user changes input
            input.closest('td').removeClass('correct-cell');

            // Live validation
            if ($('#check-words').is(':checked')) {
                validateCell(input);
            }

            // Move to next cell in the current word
            if (currentWord) {
                const currentIndex = currentWord.cells.findIndex(cell => cell.x === x && cell.y === y);
                if (currentIndex >= 0 && currentIndex < currentWord.cells.length - 1) {
                    const nextCell = currentWord.cells[currentIndex + 1];
                    navigateToCell(table, nextCell.x, nextCell.y);
                }
            }
        } else {
            switch (e.key) {
                case 'ArrowUp':
                    navigateToCell(table, x, y - 1, true);
                    break;
                case 'ArrowDown':
                    navigateToCell(table, x, y + 1, true);
                    break;
                case 'ArrowLeft':
                    navigateToCell(table, x - 1, y, true);
                    break;
                case 'ArrowRight':
                    navigateToCell(table, x + 1, y, true);
                    break;
                case 'Backspace':
                    // Clear the current cell
                    input.val('').closest('td').removeClass('correct-cell');
                    e.preventDefault(); // Prevent default backspace behavior

                    // Move to previous cell in the current word
                    if (currentWord) {
                        const currentIndex = currentWord.cells.findIndex(cell => cell.x === x && cell.y === y);
                        if (currentIndex > 0) {
                            const prevCell = currentWord.cells[currentIndex - 1];
                            navigateToCell(table, prevCell.x, prevCell.y);
                        }
                    }
                    break;
            }
        }
    }

    function handleFocus(e) {
        const input = $(e.target);

        // Remove previous highlights
        $('.letter-input').closest('td').removeClass('highlighted-cell');
        $('#clues-container-fe li').removeClass('highlighted-clue');

        const wordsAtCell = input.data('words');
        if (wordsAtCell && wordsAtCell.length > 0) {
            // If currentWord is among them, keep it
            if (currentWord && wordsAtCell.includes(currentWord)) {
                // Do nothing
            } else {
                currentWord = wordsAtCell[0];
            }

            // Highlight the cells of the current word
            currentWord.cells.forEach(cell => {
                let cellInput = $('.letter-input').filter(function () {
                    return $(this).data('x') === cell.x && $(this).data('y') === cell.y;
                });
                cellInput.closest('td').addClass('highlighted-cell');
            });

            // Highlight the clue
            let clueSelector = `#clues-container-fe li[data-clue-number="${currentWord.clueNumber}"][data-direction="${currentWord.direction}"]`;
            $(clueSelector).addClass('highlighted-clue');
        } else {
            currentWord = null;
        }
    }

    function navigateToCell(table, x, y, fromArrowKey = false) {
        const targetInput = table.find(`.letter-input`).filter(function () {
            return $(this).data('x') === x && $(this).data('y') === y;
        });

        if (targetInput.length) {
            targetInput.focus();

            // Update currentWord if navigated via arrow keys
            if (fromArrowKey) {
                const wordsAtCell = targetInput.data('words');
                if (wordsAtCell && wordsAtCell.length > 0) {
                    currentWord = wordsAtCell[0]; // You may implement logic to choose the word
                } else {
                    currentWord = null;
                }
            }
        }
    }

    function renderClues(cluesData) {
        const acrossClues = $('#across-clues').empty();
        const downClues = $('#down-clues').empty();

        if (cluesData && cluesData.across) {
            cluesData.across.forEach((clueObj) => {
                const clueItem = $('<li></li>');
                clueItem.attr('data-clue-number', clueObj.clueNumber);
                clueItem.attr('data-direction', 'across');
                clueItem.append(`<strong>${clueObj.clueNumber}.</strong> ${clueObj.clueText}`);
                if (clueObj.clueImage) {
                    clueItem.append(`<br><img src="${clueObj.clueImage}" alt="Clue image" class="clue-image">`);
                }
                clueItem.on('click', function() {
                    // Remove previous highlights
                    $('.letter-input').closest('td').removeClass('highlighted-cell');
                    $('#clues-container-fe li').removeClass('highlighted-clue');

                    // Highlight the clue
                    $(this).addClass('highlighted-clue');

                    // Find the word
                    const clueNumber = $(this).data('clue-number');
                    const direction = $(this).data('direction');

                    const wordObj = words.find(word => word.clueNumber === clueNumber && word.direction === direction);

                    if (wordObj) {
                        currentWord = wordObj; // Update currentWord

                        // Highlight the word
                        wordObj.cells.forEach(cell => {
                            let cellInput = $('.letter-input').filter(function () {
                                return $(this).data('x') === cell.x && $(this).data('y') === cell.y;
                            });
                            cellInput.closest('td').addClass('highlighted-cell');
                        });

                        // Focus on the first cell of the word
                        let firstCell = wordObj.cells[0];
                        navigateToCell($('.crossword-table'), firstCell.x, firstCell.y);
                    }
                });
                acrossClues.append(clueItem);
            });
        }

        if (cluesData && cluesData.down) {
            cluesData.down.forEach((clueObj) => {
                const clueItem = $('<li></li>');
                clueItem.attr('data-clue-number', clueObj.clueNumber);
                clueItem.attr('data-direction', 'down');
                clueItem.append(`<strong>${clueObj.clueNumber}.</strong> ${clueObj.clueText}`);
                if (clueObj.clueImage) {
                    clueItem.append(`<br><img src="${clueObj.clueImage}" alt="Clue image" class="clue-image">`);
                }
                clueItem.on('click', function() {
                    // Remove previous highlights
                    $('.letter-input').closest('td').removeClass('highlighted-cell');
                    $('#clues-container-fe li').removeClass('highlighted-clue');

                    // Highlight the clue
                    $(this).addClass('highlighted-clue');

                    // Find the word
                    const clueNumber = $(this).data('clue-number');
                    const direction = $(this).data('direction');

                    const wordObj = words.find(word => word.clueNumber === clueNumber && word.direction === direction);

                    if (wordObj) {
                        currentWord = wordObj; // Update currentWord

                        // Highlight the word
                        wordObj.cells.forEach(cell => {
                            let cellInput = $('.letter-input').filter(function () {
                                return $(this).data('x') === cell.x && $(this).data('y') === cell.y;
                            });
                            cellInput.closest('td').addClass('highlighted-cell');
                        });

                        // Focus on the first cell of the word
                        let firstCell = wordObj.cells[0];
                        navigateToCell($('.crossword-table'), firstCell.x, firstCell.y);
                    }
                });
                downClues.append(clueItem);
            });
        }
    }

    function validateCrossword() {
        const inputs = $('.letter-input');
        let allCorrect = true;

        // Reset styles
        $('.letter-input').closest('td').removeClass('correct-cell');

        inputs.each(function () {
            const input = $(this);
            const x = input.data('x');
            const y = input.data('y');
            const userAnswer = input.val().toUpperCase();
            const correctAnswer = gridData[y][x]?.letter?.toUpperCase() || '';

            if (userAnswer === correctAnswer) {
                input.closest('td').addClass('correct-cell');
            } else {
                allCorrect = false;
            }
        });

        if (allCorrect) {
            // SweetAlert2 for success
            Swal.fire({
                title: 'Congratulations!',
                text: 'You have correctly filled the crossword!',
                icon: 'success',
                iconColor:'#00796b',
                confirmButtonText: 'Awesome!',
                confirmButtonColor:'#00796b',
            });
            $('#kw-reset-crossword').show();
        } else {
            // SweetAlert2 for errors
            Swal.fire({
                title: 'Oops!',
                text: 'Some answers are incorrect. Keep trying!',
                icon: 'error',
                confirmButtonText: 'Try Again'
            });
        }
    }

    function validateCell(input) {
        const x = input.data('x');
        const y = input.data('y');
        const userAnswer = input.val().toUpperCase();
        const correctAnswer = gridData[y][x]?.letter?.toUpperCase() || '';

        if (userAnswer === correctAnswer && userAnswer !== '') {
            input.closest('td').addClass('correct-cell');
        } else {
            input.closest('td').removeClass('correct-cell');
        }
    }

    function enableLiveValidation() {
        $('.letter-input').each(function () {
            validateCell($(this));
        });
    }

    function disableLiveValidation() {
        $('.letter-input').closest('td').removeClass('correct-cell');
    }

    $('#kw-reset-crossword').on('click', function () {
        location.reload(); // Reload the current page
    });

    // Initialize the crossword grid
    populateCrosswordFromData('#crossword-grid');
});
