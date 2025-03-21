/**
 * Word Search Timer Admin JavaScript
 */
jQuery(document).ready(function ($) {
  /**
   * Initialize the timer metabox functionality
   */
  function initTimerMetabox() {
    // Toggle timer fields visibility
    $("#wordsearch-timer-enabled").on("change", function () {
      if ($(this).is(":checked")) {
        $(".timer-fields").slideDown(200);
      } else {
        $(".timer-fields").slideUp(200);
      }
    });

    // Update timer preview
    function updateTimerPreview() {
      var hours = parseInt($("#wordsearch-timer-hours").val()) || 0;
      var minutes = parseInt($("#wordsearch-timer-minutes").val()) || 0;
      var seconds = parseInt($("#wordsearch-timer-seconds").val()) || 0;

      // Format with leading zeros
      var formattedHours = String(hours).padStart(2, "0");
      var formattedMinutes = String(minutes).padStart(2, "0");
      var formattedSeconds = String(seconds).padStart(2, "0");

      $("#timer-preview-value").text(
        formattedHours + ":" + formattedMinutes + ":" + formattedSeconds
      );
    }

    // Initialize preview on page load
    updateTimerPreview();

    // Update preview on input change
    $(
      "#wordsearch-timer-hours, #wordsearch-timer-minutes, #wordsearch-timer-seconds"
    ).on("input", function () {
      updateTimerPreview();
    });

    // Prevent invalid inputs
    $("#wordsearch-timer-minutes, #wordsearch-timer-seconds").on(
      "change",
      function () {
        var value = parseInt($(this).val()) || 0;
        if (value > 59) {
          $(this).val(59);
        } else if (value < 0) {
          $(this).val(0);
        }
        updateTimerPreview();
      }
    );

    $("#wordsearch-timer-hours").on("change", function () {
      var value = parseInt($(this).val()) || 0;
      if (value < 0) {
        $(this).val(0);
      }
      updateTimerPreview();
    });
  }

  // Initialize when the DOM is ready
  $(document).ready(function () {
    initTimerMetabox();
  });
});
