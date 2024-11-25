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

                // Set the filled-cell background color from the localized variable
                $('head').append(`
                    <style>
                        .filled-cell {
                            background-color: ${cross_ajax_obj.filledCellColor};
                        }
                        .correct-word {
                        background-color: ${cross_ajax_obj.correctedCellColor};
                        };
                    </style>
                `);

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
                    input.on('input', handleInput); // Prevent spaces
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

    function handleInput(e) {
        const input = $(e.target);

        // Remove spaces if entered
        const value = input.val();
        if (value.includes(' ')) {
            input.val(value.replace(/ /g, ''));
        }
    }

    function handleNavigation(e) {
        const input = $(e.target);
        const x = input.data('x');
        const y = input.data('y');
        const table = $('.crossword-table');

        switch (e.key) {
            case 'ArrowUp':
                navigateToCell(table, x, y - 1);
                break;
            case 'ArrowDown':
                navigateToCell(table, x, y + 1);
                break;
            case 'ArrowLeft':
                navigateToCell(table, x - 1, y);
                break;
            case 'ArrowRight':
                navigateToCell(table, x + 1, y);
                break;
            case 'Backspace':
                // Clear the current cell
                input.val('').closest('td').removeClass('correct-word');
                e.preventDefault(); // Prevent default backspace behavior
                break;
        }
    }

    function navigateToCell(table, x, y) {
        const targetInput = table.find(`.letter-input`).filter(function () {
            return $(this).data('x') === x && $(this).data('y') === y;
        });

        if (targetInput.length) {
            targetInput.focus();
        }
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

    $('#kw-reset-crossword').on('click', function () {
        location.reload(); // Reload the current page
    });

    // Initialize the crossword grid
    populateCrosswordFromData('#crossword-grid');
});
