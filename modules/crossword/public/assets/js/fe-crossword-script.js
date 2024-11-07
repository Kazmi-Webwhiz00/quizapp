jQuery(document).ready(function ($) {
    function  populateCrosswordFromData (container) {
    // Read data from the hidden field
    let crosswordData = $('#crossword-data').val();
    if (!crosswordData) {
        console.error('No crossword data found in #crossword-data');
        return;
    }

    // Parse the JSON data
    let data;
    try {
        data = JSON.parse(crosswordData);
    } catch (e) {
        console.error('Invalid JSON data in #crossword-data');
        return;
    }

    let gridData = data.grid;
    let cluesData = data.clues;

    // Render the grid
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
                tableCell.append(`<span class="letter">${cell.letter}</span>`);
            } else {
                tableCell.addClass('empty-cell');
            }
            tableRow.append(tableCell);
        }
        table.append(tableRow);
    }

    // Append the grid to the container
    $(container).empty().append(table);

    // Render the clues
    const acrossClues = $('<ul></ul>');
    const downClues = $('<ul></ul>');

    cluesData.across.forEach((clueObj) => {
        const clueItem = $('<li></li>');
        clueItem.append(`<strong>${clueObj.clueNumber}.</strong> ${clueObj.clueText}`);
        if (clueObj.clueImage) {
            clueItem.append(`<br><img src="${clueObj.clueImage}" alt="Clue image" class="clue-image">`);
        }
        acrossClues.append(clueItem);
    });

    cluesData.down.forEach((clueObj) => {
        const clueItem = $('<li></li>');
        clueItem.append(`<strong>${clueObj.clueNumber}.</strong> ${clueObj.clueText}`);
        if (clueObj.clueImage) {
            clueItem.append(`<br><img src="${clueObj.clueImage}" alt="Clue image" class="clue-image">`);
        }
        downClues.append(clueItem);
    });

    // Append the clues to the clues container
    $('#clues-container').empty();
    $('#clues-container').append('<h3>Across</h3>');
    $('#clues-container').append(acrossClues);
    $('#clues-container').append('<h3>Down</h3>');
    $('#clues-container').append(downClues);

    // Apply the show/hide answers functionality
    
}

populateCrosswordFromData('#crossword-grid');
});