jQuery(document).ready(function ($) {
  // Handler for the regular PDF download
  $("#download-pdf-button-fe").on("click", function (e) {
    e.preventDefault();
    $("#download-pdf-button-fe")
      .text(cross_ajax_download_obj.downloadingText)
      .prop("disabled", true);
    var crossword_id = $(this).data("crossword-id"); // Get crossword ID from the button

    if (!crossword_id) {
      alert("Crossword ID is missing.");
      return;
    }

    $.ajax({
      url: cross_ajax_download_obj.ajax_url, // WordPress AJAX URL
      type: "GET",
      data: {
        action: "generate_crossword_pdf",
        crossword_id: crossword_id,
        show_keys: 0, // No answers included
      },
      xhrFields: {
        responseType: "blob",
      },
      success: function (response, status, xhr) {
        if (xhr.status === 200) {
          var blob = new Blob([response], { type: "application/pdf" });
          var link = document.createElement("a");
          link.href = window.URL.createObjectURL(blob);
          link.download = "crossword.pdf";
          link.click();
        } else {
          alert(cross_ajax_download_obj.strings.errorMessage);
        }
      },
      error: function (xhr, status, error) {
        alert(cross_ajax_download_obj.strings.errorMessage);
      },
      complete: function () {
        $("#download-pdf-button-fe")
          .text(cross_ajax_download_obj.pdfButtonText)
          .prop("disabled", true);
      },
    });
  });
});
