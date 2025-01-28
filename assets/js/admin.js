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
});
