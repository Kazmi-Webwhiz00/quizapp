jQuery(document).ready(function ($) {
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

    // Initialize the active tab based on the URL hash
    const activeTab = window.location.hash.substring(1) || 'general';
    activateTab(activeTab);
});
