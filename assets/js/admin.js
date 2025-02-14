(function ($) {
    $.fn.highlightPublishButton = function () {
        let publishButton = $("#publish");
        let submitDiv = $("#submitdiv"); // Publish box container

        if (publishButton.length && submitDiv.length) {
            // Avoid adding duplicate highlight class
            if (!publishButton.hasClass("publish-highlight")) {
                publishButton.addClass("publish-highlight");
            }

            // Avoid adding duplicate warning notice
            if ($(".publish-warning").length === 0) {
                let warningMessage = quizAdminData.message || "Warning: Please save!";

                // Create notice div with animated warning icon & close button
                let notice = $(`
                    <div class="publish-warning">
                        <span class="warning-icon">⚠️</span>
                        <span class="warning-text"><strong>${warningMessage}</strong></span>
                        <span class="close-notice">✖</span>
                    </div>
                `);

                // Append notice above #submitdiv
                submitDiv.before(notice);

                // Show the tooltip with a fade-in effect
                setTimeout(() => {
                    notice.css("opacity", "1");
                }, 800);

                // Remove notice when close button is clicked with a fade-out effect
                notice.find(".close-notice").on("click", function () {
                    notice.css("transform", "scale(0.9)").css("opacity", "0");
                    setTimeout(() => notice.remove(), 300);
                });

                // Remove only glow (keep border) when clicked
                publishButton.on("click", function () {
                    publishButton.removeClass("publish-highlight");
                    notice.fadeOut(300, function () {
                        $(this).remove();
                    });
                });
            }
        }
    };

    $.fn.showAdminPrompt = function (message, color) {
        // Default to yellow if no color is provided
        color = color || 'yellow';

        // Check if the notification already exists to avoid duplicates
        if ($('.kz-quiz-notice').length > 0) {
            console.log("Notification already exists. Skipping creation.");
            return this;
        }

        // Background and text color based on the chosen type
        const backgroundColor = color === 'yellow' ? '#fff3cd' : (color === 'green' ? '#d4edda' : '#ffffff');
        const borderColor = color === 'yellow' ? '#ffeeba' : (color === 'green' ? '#c3e6cb' : '#dddddd');
        const textColor = color === 'yellow' ? '#856404' : (color === 'green' ? '#155724' : '#333333');

        // Create the notification HTML
        const adminPrompt = $(`
            <div class="kz-quiz-notice" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 600px; background-color: ${backgroundColor}; color: ${textColor}; padding: 15px; border: 1px solid ${borderColor}; border-radius: 5px; z-index: 9999; text-align: left; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                <h4 style="margin: 0 0 10px 0; font-size: 18px; font-weight: bold; color: ${textColor};">Final Prompt Is:</h4>
                <p style="margin: 0; font-size: 16px;">${message}</p>
                <button class="kz-quiz-notice-dismiss" style="position: absolute; top: 10px; right: 10px; background: none; border: none; font-size: 18px; color: ${textColor}; font-weight: bold; cursor: pointer;">&times;</button>
            </div>
        `);

        // Append the notification to the body
        $('body').prepend(adminPrompt);

        // Add dismiss functionality
        adminPrompt.find('.kz-quiz-notice-dismiss').on('click', function () {
            $(this).closest('.kz-quiz-notice').remove();
        });

        // Automatically remove the notification after 1 minute
        setTimeout(() => {
            $('.kz-quiz-notice').fadeOut(300, function () {
                $(this).remove();
            });
        }, 60000); // 1 minute in milliseconds

        return this;
    };

    // Expose the functions globally if needed
    window.highlightPublishButton = $.fn.highlightPublishButton;
    window.showAdminPrompt = $.fn.showAdminPrompt;
})(jQuery);
