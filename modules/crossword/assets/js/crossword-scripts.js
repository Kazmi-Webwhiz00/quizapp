jQuery(document).ready(function ($) {
    const container = $('#crossword-words-clues-container');
    const addButton = $('#add-word-button');
    const clearButton = $('#clear-list-button');
    const template = $('#crossword-word-clue-template').html();

    // Function to render the HTML using the template
    function renderWordClue(index) {
        const newField = template
            .replace(/{{index}}/g, index)
            .replace(/{{number}}/g, index + 1);
        
        const newElement = $(newField);
        container.append(newElement);

        // Attach click event for the newly added remove button
        newElement.find('.remove-word').on('click', function () {
            $(this).closest('.crossword-word-clue').remove();
            updateIndices();
        });

        updateIndices(); // Update indices after adding a new row
    }

    // Add a new word and clue input pair
    addButton.on('click', function () {
        const index = container.children().length;
        renderWordClue(index);
    });

    // Remove a word and clue input pair
    container.on('click', '.remove-word', function () {
        $(this).closest('.crossword-word-clue').remove();
        updateIndices();
    });

    // Clear the entire list
    clearButton.on('click', function () {
        container.empty();
        updateIndices();
    });

    // Update indices when rows are added or removed
    function updateIndices() {
        container.children('.crossword-word-clue').each(function (index) {
            $(this).find('.word-number').text((index + 1) + '.');
            $(this).attr('data-index', index);
            $(this).find('input, file').each(function () {
                const name = $(this).attr('name');
                const newName = name.replace(/\d+/, index);
                $(this).attr('name', newName);
            });
        });
    }
});
