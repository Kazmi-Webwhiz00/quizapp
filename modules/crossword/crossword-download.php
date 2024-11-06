<?php

add_action('wp_ajax_generate_crossword_pdf', 'generate_crossword_pdf_callback');
add_action('wp_ajax_nopriv_generate_crossword_pdf', 'generate_crossword_pdf_callback');

function generate_crossword_pdf_callback() {
    // Include TCPDF library (adjust the path as needed)
    require_once("/home/gulzaib/Local Sites/quizapp/app/public/wp-content/plugins/Quiz/lib/tcpdf.php");

    // Get the crossword data from the AJAX request
    if (!isset($_POST['crossword_data'])) {
        wp_send_json_error('No crossword data received.');
        wp_die();
    }

    $crossword_data = json_decode(stripslashes($_POST['crossword_data']), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error('Invalid crossword data.');
        wp_die();
    }

    $pdf = new TCPDF();
    $pdf->SetCreator('Crossword Generator');
    $pdf->SetAuthor('Your Website');
    $pdf->SetTitle('Crossword Puzzle');
    $pdf->SetMargins(15, 15, 15);
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 12);

    // Generate the crossword grid
    $html = '<table cellpadding="4" cellspacing="0">';
    foreach ($crossword_data['grid'] as $row) {
        $html .= '<tr>';
        foreach ($row as $cell) {
            $letter = $cell['letter'];
            $clueNumber = $cell['clueNumber'];
            if ($letter !== '') {
                $cellContent = '';
                if ($clueNumber !== '') {
                    $cellContent .= '<div style="font-size:6px;">' . htmlspecialchars($clueNumber) . '</div>';
                }
                $cellContent .= '<div style="font-size:12px;">&nbsp;</div>';
                $html .= '<td border="1">' . $cellContent . '</td>';
            } else {
                $html .= '<td bgcolor="#F5F5DC"></td>';
            }
        }
        $html .= '</tr>';
    }
    $html .= '</table>';

    $pdf->writeHTML($html, true, false, false, false, '');

    // Add clues
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Across', 0, 1);

    $pdf->SetFont('helvetica', '', 12);
    foreach ($crossword_data['clues']['across'] as $clueData) {
        $clueNumber = htmlspecialchars($clueData['clueNumber']);
        $clueText = htmlspecialchars($clueData['clueText']);
        $clueImage = $clueData['clueImage'];

        // Set a larger cell with padding to fit the text and image side by side
        $pdf->SetFillColor(230, 230, 230); // Light grey background for the clue cell
        $pdf->MultiCell(140, 12, "$clueNumber. $clueText", 0, 'L', true, 0);

        if (!empty($clueImage)) {
            // Convert URL to absolute path or ensure it's accessible
            $imagePath = $clueImage;

            // If the image URL is relative, convert it to an absolute path
            if (strpos($clueImage, home_url()) !== false) {
                $imagePath = str_replace(home_url('/'), ABSPATH, $clueImage);
            } elseif (strpos($clueImage, '/') === 0) {
                $imagePath = ABSPATH . ltrim($clueImage, '/');
            }

            // Check if file exists and add the image next to the clue text
            if (file_exists($imagePath)) {
                $pdf->Image($imagePath, $pdf->GetX() - 35, $pdf->GetY() - 4, 25, 20, '', '', '', false, 300, '', false, false, 0, false, false, false);
            } else {
                $pdf->Image($clueImage, $pdf->GetX() - 35, $pdf->GetY() - 4, 25, 20, '', '', '', false, 300, '', false, false, 0, false, false, false);
            }
        }
        
        $pdf->Ln(14); // Add extra space after each row
    }

    // Down clues section
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Down', 0, 1);

    $pdf->SetFont('helvetica', '', 12);
    foreach ($crossword_data['clues']['down'] as $clueData) {
        $clueNumber = htmlspecialchars($clueData['clueNumber']);
        $clueText = htmlspecialchars($clueData['clueText']);
        $clueImage = $clueData['clueImage'];

        // Same styling for Down clues
        $pdf->SetFillColor(230, 230, 230); // Light grey background for the clue cell
        $pdf->MultiCell(140, 12, "$clueNumber. $clueText", 0, 'L', true, 0);

        if (!empty($clueImage)) {
            // Convert URL to absolute path or ensure it's accessible
            $imagePath = $clueImage;

            // If the image URL is relative, convert it to an absolute path
            if (strpos($clueImage, home_url()) !== false) {
                $imagePath = str_replace(home_url('/'), ABSPATH, $clueImage);
            } elseif (strpos($clueImage, '/') === 0) {
                $imagePath = ABSPATH . ltrim($clueImage, '/');
            }

            // Check if file exists and add the image next to the clue text
            if (file_exists($imagePath)) {
                $pdf->Image($imagePath, $pdf->GetX() - 35, $pdf->GetY() - 4, 25, 20, '', '', '', false, 300, '', false, false, 0, false, false, false);
            } else {
                $pdf->Image($clueImage, $pdf->GetX() - 35, $pdf->GetY() - 4, 25, 20, '', '', '', false, 300, '', false, false, 0, false, false, false);
            }
        }

        $pdf->Ln(14); // Add extra space after each row
    }

    // Output the PDF as a string
    $pdf_content = $pdf->Output('crossword.pdf', 'S');

    // Clean output buffer
    if (ob_get_length()) {
        ob_end_clean();
    }

    // Send the PDF back to the browser
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="crossword.pdf"');
    header('Content-Length: ' . strlen($pdf_content));

    echo $pdf_content;

    wp_die(); // Terminate AJAX handler
}
