jQuery(document).ready(function ($) {
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

        let gridData = data.grid;
        const table = $('<table class="crossword-table"></table>');

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

        // Add a checkbox for live validation
        const checkContainer = $('<div class="check-container"></div>');
        const checkBox = $('<input type="checkbox" id="check-words" />');
        checkContainer.append(checkBox);
        checkContainer.append('<label for="check-words">Check Words</label>');
        const checkButton = $('<button id="validate-crossword">Check Crossword</button>');
        checkContainer.append(checkButton);
        $(container).append(checkContainer);

        // Attach validation logic
        $('#validate-crossword').on('click', function () {
            validateCrossword(data);
        });

        $('#check-words').on('change', function () {
            if ($(this).is(':checked')) {
                enableLiveValidation(data);
            } else {
                disableLiveValidation();
            }
        });
    }

    function renderClues(cluesData) {
        const acrossClues = $('#across-clues').empty();
        const downClues = $('#down-clues').empty();

        if (cluesData && cluesData.across) {
            cluesData.across.forEach((clueObj) => {
                const clueItem = $('<li></li>');
                clueItem.append(`<strong>${clueObj.clueNumber}.</strong> ${clueObj.clueText}`);
                if (clueObj.clueImage) {
                    clueItem.append(`<br><img src="${clueObj.clueImage}" alt="Clue image" class="clue-image">`);
                }
                acrossClues.append(clueItem);
            });
        }

        if (cluesData && cluesData.down) {
            cluesData.down.forEach((clueObj) => {
                const clueItem = $('<li></li>');
                clueItem.append(`<strong>${clueObj.clueNumber}.</strong> ${clueObj.clueText}`);
                if (clueObj.clueImage) {
                    clueItem.append(`<br><img src="${clueObj.clueImage}" alt="Clue image" class="clue-image">`);
                }
                downClues.append(clueItem);
            });
        }
    }

    function validateCrossword(data) {
        const gridData = data.grid;
        const inputs = $('.letter-input');
        let allCorrect = true;

        // Reset styles
        $('.crossword-table td').removeClass('correct-word');

        inputs.each(function () {
            const input = $(this);
            const x = input.data('x');
            const y = input.data('y');
            const userAnswer = input.val().toUpperCase();
            const correctAnswer = gridData[y][x]?.letter?.toUpperCase() || '';

            if (userAnswer === correctAnswer) {
                input.closest('td').addClass('correct-word');
            } else {
                allCorrect = false;
            }
        });

        if (allCorrect) {
            alert('Congratulations! You have correctly filled the crossword!');
        } else {
            alert('Some answers are incorrect. Keep trying!');
        }
    }

    function enableLiveValidation(data) {
        $('.letter-input').on('input', function () {
            const input = $(this);
            const x = input.data('x');
            const y = input.data('y');
            const userAnswer = input.val().toUpperCase();
            const correctAnswer = data.grid[y][x]?.letter?.toUpperCase() || '';

            if (userAnswer === correctAnswer) {
                input.closest('td').addClass('correct-word');
            } else {
                input.closest('td').removeClass('correct-word');
            }
        });
    }

    function disableLiveValidation() {
        $('.letter-input').off('input');
        $('.crossword-table td').removeClass('correct-word');
    }

    // Initialize the crossword grid
    populateCrosswordFromData('#crossword-grid');
});
