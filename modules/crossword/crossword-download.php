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

  // Add some vertical space before clues
  $pdf->Ln(10);


  // Function to render clues (both Across and Down)
  function render_clues($pdf, $clues, $title) {
      // Add section title
      $pdf->SetFont('helvetica', 'B', 14);
      $pdf->Cell(0, 10, $title, 0, 1, 'L');

      // Set font for clues
      $pdf->SetFont('helvetica', '', 12);

      foreach ($clues as $clueData) {
          $clueNumber = htmlspecialchars($clueData['clueNumber']);
          $clueText = htmlspecialchars($clueData['clueText']);
          $clueImage = $clueData['clueImage'];

          // Start a new table row for each clue
          $html = '<table cellpadding="4" cellspacing="0" style="width: 100%; margin-bottom: 10px;">';
          $html .= '<tr>';

          // Clue text cell with increased padding and background color
          $html .= '<td style="width: 60%; background-color: #E8E8E8; padding: 8px;">';
          $html .= "<strong>{$clueNumber}.</strong> {$clueText}";
          $html .= '</td>';

          // Clue image cell with padding if an image exists
          if (!empty($clueImage)) {
              // Convert URL to absolute path or ensure it's accessible
              $imagePath = $clueImage;

              // If the image URL is relative, convert it to an absolute path
              if (strpos($clueImage, home_url()) !== false) {
                  $imagePath = str_replace(home_url('/'), ABSPATH, $clueImage);
              } elseif (strpos($clueImage, '/') === 0) {
                  $imagePath = ABSPATH . ltrim($clueImage, '/');
              }

              // Check if file exists
              if (file_exists($imagePath)) {
                  $imageSrc = $imagePath;
              } else {
                  $imageSrc = $clueImage; // Assume it's a URL
              }

              // Add image with padding
              $html .= '<td style="width: 40%; text-align: center; padding: 8px;  background-color: #E8E8E8;">';
              $html .= '<img src="' . htmlspecialchars($imageSrc) . '" style="width: 50px; height: 50px;" />';
              $html .= '</td>';
          } else {
              // If no image, add an empty cell for alignment
              $html .= '<td style="width: 40%;"></td>';
          }

          $html .= '</tr>';
          $html .= '</table>';

          // Write the clues HTML to the PDF
          $pdf->writeHTML($html, true, false, false, false, '');
      }
  }

  // Render Across clues
  if (!empty($crossword_data['clues']['across'])) {
      render_clues($pdf, $crossword_data['clues']['across'], 'Across');
  }

  // Add some vertical space between Across and Down clues
  $pdf->Ln(5);

  // Render Down clues
  if (!empty($crossword_data['clues']['down'])) {
      render_clues($pdf, $crossword_data['clues']['down'], 'Down');
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