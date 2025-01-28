jQuery(document).ready(function ($) {
    // Add new checkbox
    $('#kw-add-checkbox').click(function () {
        $('#kw-checkbox-container').append(`
            <div class="kw-checkbox-item">
                <input type="text" name="wp_quiz_plugin_prompt_checkboxes[]" placeholder="Enter checkbox value">
                <button type="button" class="kw-remove-checkbox">remove</button>
            </div>
        `);
    });

    // Remove a checkbox
    $(document).on('click', '.kw-remove-checkbox', function () {
        $(this).closest('.kw-checkbox-item').remove();
    });

    // Before form submission, discard empty inputs
    $('form').on('submit', function () {
        $('#kw-checkbox-container .kw-checkbox-item input[type="text"]').each(function () {
            if ($(this).val().trim() === '') {
                $(this).closest('.kw-checkbox-item').remove(); // Remove empty input fields
            }
        });
    });

    const defaultPrompt = `
    Generate a list of quiz questions and answers based on the following prompt: {userPrompt}. The questions should be designed for learners aged {learnerAge}, so adjust the level of difficulty accordingly. 
    
    The quiz will be published in the following categories: {selectedCategories}. Use this information to align the questions and answers with the target audience and subject matter.
    
    Focus on the following areas: {selectedCheckboxes}. Ensure that the questions encourage critical thinking, are engaging, and are relevant to the specified topics.
    
    {previousQuestionsContext}
    
    Use the following structure for the questions and answers: {questionTemplate}.
        `.trim();
    
        // Reset to default functionality
        $('#kw-reset-default-prompt').on('click', function () {
            $('#wp_quiz_plugin_custom_prompt_template').val(defaultPrompt);
        });
});
