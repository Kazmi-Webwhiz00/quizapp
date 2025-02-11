jQuery(document).ready(function($) {
    // Handler for the regular PDF download
    $('#download-pdf-button').on('click', function(e) {
        e.preventDefault();

        $('#download-pdf-button').text(cross_ajax_obj.downloadingText).prop('disabled', true);
        var crossword_id = $(this).data('crossword-id'); // Get crossword ID from the button

        if (!crossword_id) {
            alert('Crossword ID is missing.');
            return;
        }

        $.ajax({
            url: cross_ajax_obj.ajax_url, // WordPress AJAX URL
            type: 'GET',
            data: {
                action: 'generate_crossword_pdf',
                crossword_id: crossword_id,
                show_keys: 0 // No answers included
            },
            xhrFields: {
                responseType: 'blob'
            },
            success: function(response, status, xhr) {
                if (xhr.status === 200) {
                    var blob = new Blob([response], { type: 'application/pdf' });
        
                    // Extract filename from Content-Disposition header
                    var contentDisposition = xhr.getResponseHeader('Content-Disposition');
                    var filename = "Krzyzówkawka.pdf"; // Default filename fallback
                    
                    if (contentDisposition) {
                        var matches = contentDisposition.match(/filename="(.+)"/);
                        if (matches && matches.length > 1) {
                            filename = matches[1];
                        }
                    }
        
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;
                    link.click();
                } else {
                    alert(crosswordPdfScriptVar.strings.errorMessage);
                }
            },
            error: function(xhr, status, error) {
                alert(crosswordPdfScriptVar.strings.errorMessage);
            },
            complete: function(){
                $('#download-pdf-button').text(cross_ajax_obj.pdfButtonText).prop('disabled', true);
            }
        });
        
    });


        // Handler for the regular PDF download
        $('#crossword-download-key').on('click', function(e) {
            e.preventDefault();
            $('#crossword-download-key').text(cross_ajax_obj.downloadingText).prop('disabled', true);
            var crossword_id = $(this).data('crossword-id'); // Get crossword ID from the button
    
            if (!crossword_id) {
                alert('Crossword ID is missing.');
                return;
            }
    
            $.ajax({
                url: cross_ajax_obj.ajax_url, // WordPress AJAX URL
                type: 'GET',
                data: {
                    action: 'generate_crossword_pdf',
                    crossword_id: crossword_id,
                    show_keys: 1 // No answers included
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response, status, xhr) {
                    if (xhr.status === 200) {
                        var blob = new Blob([response], { type: 'application/pdf' });
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "crossword.pdf";
                        link.click();
                    } else {
                        alert(crosswordPdfScriptVar.strings.errorMessage);
                    }
                },
                error: function(xhr, status, error) {
                    alert(crosswordPdfScriptVar.strings.errorMessage);
                },
                complete: function(){
                    $('#crossword-download-key').text(cross_ajax_obj.keyButtonText).prop('disabled', true);
                }
            });
        });
});
