jQuery(document).ready(function ($) {
  $("#selected_school_wordsearch").on("change", function () {
    var selectedSchool = $(this).val();
    $("#class_select_container, #subject_select_container_wordsearch").hide();

    if (selectedSchool) {
      $.ajax({
        url: wordsearch_ajax_obj.ajax_url,
        type: "POST",
        data: {
          action: "fetch_wordsearch_classes",
          parent_id: selectedSchool,
        },
        success: function (response) {
          $("#selected_class_wordsearch").html(response);
          if ($("#selected_class_wordsearch option").length > 1) {
            $("#class_select_container").show();
          }
          $("#selected_subject_wordsearch").html(
            '<option value=""><?php _e("----------", "wp-quiz-plugin"); ?></option>'
          );
        },
      });
    }
  });

  $("#selected_class_wordsearch").on("change", function () {
    var selectedClass = $(this).val();
    $("#subject_select_container_wordsearch").hide();

    if (selectedClass) {
      $.ajax({
        url: wordsearch_ajax_obj.ajax_url,
        type: "POST",
        data: {
          action: "fetch_wordsearch_subjects",
          parent_id: selectedClass,
        },
        success: function (response) {
          $("#selected_subject_wordsearch").html(response);
          if ($("#selected_subject_wordsearch option").length > 1) {
            $("#subject_select_container_wordsearch").show();
          }
        },
      });
    }
  });
});
