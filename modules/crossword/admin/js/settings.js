jQuery(document).ready(function ($) {
    $('.kw-color-picker').wpColorPicker();
    const tabs = $('.kw-crossword-nav-tab');
    const panes = $('.kw-crossword-tab-pane');

    // Function to activate a tab
    function activateTab(tabKey) {
        tabs.removeClass('kw-crossword-nav-tab-active');
        panes.hide();

        $(`.kw-crossword-nav-tab[data-tab="${tabKey}"]`).addClass('kw-crossword-nav-tab-active');
        $(`#kw-crossword-${tabKey}`).show();
    }

    // Add click event listeners to tabs
    tabs.on('click', function (event) {
        event.preventDefault();
        const tabKey = $(this).data('tab');
        activateTab(tabKey);

        // Update the URL hash without reloading the page
        history.pushState(null, '', `#${tabKey}`);
    });

        $('.kw-reset-button').on('click', function () {
            console.log("test");
            const $parentSection = $(this).closest('.kw-settings-section');
    
            // Reset all input fields with 'data-default'
            $parentSection.find('input[data-default], textarea[data-default]').each(function () {
                const defaultValue = $(this).data('default');
                $(this).val(defaultValue);
            });
        });
    

    // Initialize the active tab based on the URL hash
    const activeTab = window.location.hash.substring(1) || 'general';
    activateTab(activeTab);



        // Default value for the crossword prompt
        const defaultPrompt = "Generate a crossword puzzle prompt.";

        // Reset button click handler
        $("#kw-reset-default-prompt").on("click", function () {
            $("#kw_crossword_prompt_main").val(defaultPrompt);
        });
});
