jQuery(document).ready(function ($) {

    function getGridData() {
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

    function getCluesData() {
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

    $('#download-pdf-button').on('click', function () {
        // Prepare the data to send
        const crosswordData = {
            grid: getGridData(), // Function to extract grid data
            clues: getCluesData() // Function to extract clues data
        };

        // Send data to server via AJAX POST request
        $.ajax({
            url: ajaxurl, // Ensure ajaxurl is defined
            type: 'POST',
            data: {
                action: 'generate_crossword_pdf',
                crossword_data: JSON.stringify(crosswordData)
            },
            xhrFields: {
                responseType: 'blob' // Expect a binary response
            },
            success: function (data) {
                // Create a link to download the PDF
                const blob = new Blob([data], { type: 'application/pdf' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'crossword.pdf';
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
            },
            error: function (xhr, status, error) {
                alert('Error generating PDF: ' + error);
            }
        });
    });
});
