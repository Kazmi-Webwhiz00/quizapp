jQuery(document).ready(function ($) {
    window.crossword = window.crossword || {};
    crossword.getGridData = function () {
        let gridData = [];
        $('.crossword-table tr').each(function (rowIndex) {
            let rowData = [];
            $(this).find('td').each(function (colIndex) {
                const cell = $(this);
                const letter = cell.find('.letter').text() || '';
                const clueNumber = cell.find('.clue-number').text() || '';
                rowData.push({
                    letter: letter,
                    clueNumber: clueNumber
                });
            });
            gridData.push(rowData);
        });
        return gridData;
    }

    crossword.getCluesData = function() {
        let acrossClues = [];
        $('#clues-container h3:contains("Across")').next('ul').find('li').each(function () {
            const clueNumber = $(this).find('strong').text().replace('.', '').trim();
            const clueText = $(this).contents().filter(function() {
                return this.nodeType === 3; // Node.TEXT_NODE
            }).text().trim();
            const clueImage = $(this).find('img').attr('src') || '';
            acrossClues.push({
                clueNumber: clueNumber,
                clueText: clueText,
                clueImage: clueImage
            });
        });

        let downClues = [];
        $('#clues-container h3:contains("Down")').next('ul').find('li').each(function () {
            const clueNumber = $(this).find('strong').text().replace('.', '').trim();
            const clueText = $(this).contents().filter(function() {
                return this.nodeType === 3; // Node.TEXT_NODE
            }).text().trim();
            const clueImage = $(this).find('img').attr('src') || '';
            downClues.push({
                clueNumber: clueNumber,
                clueText: clueText,
                clueImage: clueImage
            });
        });

        return {
            across: acrossClues,
            down: downClues
        };
    }

    // Function to update hidden fields
    crossword.updateHiddenFields = function() {
        const crosswordData = {
            grid: crossword.getGridData(),
            clues: crossword.getCluesData()
        };
        const crosswordDataJson = JSON.stringify(crosswordData);
        console.log(crosswordDataJson); // For debugging purposes
        $('#crossword-data').val(crosswordDataJson);
    }
});

