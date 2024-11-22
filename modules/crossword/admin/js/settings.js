(function ($) {
    $(document).ready(function () {
        console.log('KW Crossword Admin Scripts Loaded.');

        // Example: Highlight the active tab
        $('.kw-crossword-tab').on('click', function (e) {
            e.preventDefault();
            $('.kw-crossword-tab').removeClass('kw-crossword-tab-active');
            $(this).addClass('kw-crossword-tab-active');
        });

        // Example: Using localized script variables
        console.log('AJAX URL:', kwCrosswordAdmin.ajaxUrl);
        console.log('Nonce:', kwCrosswordAdmin.nonce);
    });
})(jQuery);
