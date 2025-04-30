(function ($) {
  // Reorder logic
  function reorderSideBoxes() {
    var postType = KW_MetaBox_Order.postType;

    // Base panels always first:
    var order = [
      "submitdiv", // Publish
      "postimagediv", // Featured Image
      "my_custom_info_box_id", // My custom image
    ];

    // If we're on wordsearch, stick the timer right after Featured Image:
    if (postType === "wordsearch") {
      // indexOf('postimagediv') === 1, so splice at 2
      order.splice(2, 0, "wordsearch-timer");
    }

    var typePrefix = postType === "quizzes" ? "quiz" : postType;
    // Then the two that vary by post type:
    order.push(typePrefix + "_seo_text_meta_box");
    order.push(typePrefix + "_visibility_meta_box");

    var container = $("#side-sortables");
    // Move each box in turn to the bottom of #side-sortables,
    // which ends up rendering them in the order we listed.
    console.log("::order", typePrefix);
    order.forEach(function (id) {
      var box = $("#" + id);
      if (box.length) {
        container.append(box);
      }
    });
  }

  // Run once on DOM ready
  $(document).ready(reorderSideBoxes);

  // Also re-run after any AJAX call that might re-render meta-boxes
  $(document).ajaxComplete(function () {
    reorderSideBoxes();
  });
})(jQuery);
