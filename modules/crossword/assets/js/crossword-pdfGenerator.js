jQuery(document).ready(function($) {
    $('#download-pdf-button').on('click', function(e) {
        e.preventDefault();

        // Get crossword_id from data attribute on the button (or replace with appropriate selector)
        var crossword_id = $(this).data('crossword-id'); // Ensure you set data-crossword-id on the button in HTML

        if (!crossword_id) {
            alert('Crossword ID is missing.');
            return;
        }

        $.ajax({
            url: cross_ajax_obj.ajax_url, // WordPress AJAX URL
            type: 'GET',
            data: {
                action: 'generate_crossword_pdf',
                crossword_id: crossword_id
            },
            xhrFields: {
                responseType: 'blob' // Handle binary data
            },
            success: function(response, status, xhr) {
                if (xhr.status === 200) {
                    var blob = new Blob([response], { type: 'application/pdf' });
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "crossword.pdf";
                    link.click();
                } else {
                    console.log("hi", response);
                    alert('An error occurred while generating the PDF 1.');
                }
            },
            error: function(xhr, status, error) {
                alert('An error occurred while generating the PDF. 2', error);
            }
        });
    });
});
