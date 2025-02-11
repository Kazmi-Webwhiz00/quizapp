<?php

add_action('wp_ajax_generate_crossword_pdf', 'generate_crossword_pdf_callback');
add_action('wp_ajax_nopriv_generate_crossword_pdf', 'generate_crossword_pdf_callback');

function generate_crossword_pdf_callback() {
    // Include TCPDF library (adjust the path as needed)
    include_once plugin_dir_path(__FILE__) . '/../../lib/tcpdf.php';

    // Validate and sanitize the crossword_id parameter
    if (!isset($_GET['crossword_id']) || !is_numeric($_GET['crossword_id'])) {
        wp_die(__('Invalid request.', 'wp-crossword-plugin'));
    }

    // Sanitize and cast crossword_id to integer
    $crossword_id = intval($_GET['crossword_id']);
    $crossword_title = get_the_title($crossword_id);
    $showkeys = intval($_GET['show_keys']) === 1;

    // Fetch crossword data from post meta using crossword_id as post ID
    $crossword_data = get_post_meta($crossword_id, '_crossword_grid_data', true);

    // Fetch the author ID of the quiz post
    $author_id = get_post_field('post_author', $crossword_id);

    // Fetch the author's display name
    $author_name = get_the_author_meta('display_name', $author_id);

    // Output or use the author's name
    if ($author_name) {
        $crossword_author = esc_html($author_name); // Sanitize output
    } else {
        $crossword_author = 'Unknown Author'; // Fallback in case author is not found
    }
    

    if (empty($crossword_data)) {
        wp_send_json_error('No crossword data found.');
        wp_die();
    }

    // Decode JSON data to work with it as an array
    $crossword_data_array = json_decode($crossword_data, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($crossword_data_array)) {
        wp_send_json_error('Invalid crossword data format.');
        wp_die();
    }

    // PDF setup
    $pdf = new TCPDF();
    $pdf->SetCreator('KazVerse');
    $pdf->SetAuthor('KazVerse');

    $pdf->SetTitle('Crossword Puzzle');

    // Set header and footer data
    $pdf->SetHeaderData('', 0, $crossword_title, $crossword_author);
    $pdf->setHeaderFont(['helvetica', '', 14]);
    $pdf->setFooterFont(['helvetica', '', 10]);
    $pdf->SetMargins(15, 27, 15);
    $pdf->SetHeaderMargin(12);
    $pdf->SetFooterMargin(10);
    $pdf->SetAutoPageBreak(TRUE, 25);
    $pdf->AddPage();

    // Add header image if provided
    $pdf_image_url = esc_url(get_option('wp_quiz_plugin_pdf_image_url'));
    if (!empty($pdf_image_url)) {
        $image_path = ABSPATH . str_replace(home_url('/'), '', $pdf_image_url);
        if (file_exists($image_path)) {
            $pdf->Image($image_path, 150, 10, 40, 15, '', '', '', false, 300, '', false, false, 1, false, false, false);
            $pdf->SetY($pdf->GetY() + 5);
        }
    }

    // Generate the crossword grid with padding and square cells
    $pdf->SetFont('helvetica', '', 12);
    $html = '<div style="padding: 20px;">
                <table cellpadding="0" cellspacing="0" style="border-collapse: separate; border-spacing: 0; border: 2px solid #ccc; border-radius: 10px; overflow: hidden;">';

    // Set a consistent size for width and height for square cells
    $cellSize = 20;

    foreach ($crossword_data_array['grid'] as $row) {
        $html .= '<tr>';
        foreach ($row as $cell) {
            $letter = $cell['letter'];
            $clueNumber = $cell['clueNumber'];
            if ($letter !== '') {
                $cellContent = '<div style="position: relative; font-size:12px; line-height: ' . $cellSize . 'px; text-align: center;">';
                
                // Clue number with padding
                if ($clueNumber !== '') {
                    $cellContent .= '<span style="position: absolute; top: 2px; left: 2px; font-size:8px; padding-right: 2px;">' . htmlspecialchars($clueNumber) . '</span>';
                }
                
                // Show letter if showkeys is enabled
                $cellContent .= $showkeys ? '<span>' . htmlspecialchars($letter) . '</span>' : '';
                $cellContent .= '</div>';

                $html .= '<td style="width: ' . $cellSize . 'px; height: ' . $cellSize . 'px; border: 1px solid #ccc; background-color: #d9eefa;">' . $cellContent . '</td>';
            } else {
                $html .= '<td style="width: ' . $cellSize . 'px; height: ' . $cellSize . 'px; background-color: white;"></td>';
            }
        }
        $html .= '</tr>';
    }
    $html .= '</table></div>';
    $pdf->writeHTML($html, true, false, false, false, '');

    // Add some vertical space before clues
    $pdf->Ln(10);

    // Function to render clues (both Across and Down)
    function render_clues($pdf, $clues, $title) {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, $title, 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 12);

        foreach ($clues as $clueData) {
            $clueNumber = htmlspecialchars($clueData['clueNumber']);
            $clueText = htmlspecialchars($clueData['clueText']);
            $clueImage = $clueData['clueImage'];
            $html = '<table cellpadding="4" cellspacing="0" style="width: 100%; margin-bottom: 10px;">';
            $html .= '<tr>';
            $html .= '<td style="width: 60%; background-color: #E8E8E8; padding: 8px;"><strong>' . $clueNumber . '.</strong> ' . $clueText . '</td>';

            if (!empty($clueImage)) {
                $imagePath = $clueImage;
                if (strpos($clueImage, home_url()) !== false) {
                    $imagePath = str_replace(home_url('/'), ABSPATH, $clueImage);
                } elseif (strpos($clueImage, '/') === 0) {
                    $imagePath = ABSPATH . ltrim($clueImage, '/');
                }

                $html .= '<td style="width: 40%; text-align: center; padding: 8px; background-color: #E8E8E8;">';
                $html .= '<img src="' . htmlspecialchars($imagePath) . '" style="width: 50px; height: 50px;" />';
                $html .= '</td>';
            } else {
                $html .= '<td style="width: 40%;"></td>';
            }

            $html .= '</tr></table>';
            $pdf->writeHTML($html, true, false, false, false, '');
        }
    }

    // Render Across clues
    if (!empty($crossword_data_array['clues']['across'])) {
        render_clues($pdf, $crossword_data_array['clues']['across'], 'Across');
    }

    // Add space between Across and Down clues
    $pdf->Ln(5);

    // Render Down clues
    if (!empty($crossword_data_array['clues']['down'])) {
        render_clues($pdf, $crossword_data_array['clues']['down'], 'Down');
    }

    // Define PDF filename based on showkeys
    $file_suffix = $showkeys ? _x('keys','crossword-pdf','wp-quiz-plugin') : _x('game','crossword-pdf','wp-quiz-plugin');
    $pdf_filename = $crossword_title . '_' . $file_suffix . '.pdf';
    $pdf_content = $pdf->Output($pdf_filename, 'S');

    // Output PDF with dynamic filename
    if (ob_get_length()) {
        ob_end_clean();
    }

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $pdf_filename . '"');
    header('Content-Length: ' . strlen($pdf_content));
    echo $pdf_content;

    wp_die();
}
