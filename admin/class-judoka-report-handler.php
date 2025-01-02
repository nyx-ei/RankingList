<?php

class Judoka_Report_Handler
{
    private $judoka_model;
    private $competition_model;
    private $db;

    public function __construct()
    {
        $this->judoka_model = new Judoka_Model();
        $this->competition_model = new Competition_Model();
        $this->db = Database_Access::getInstance();
    }

    public function generate_report($criteria)
    {
        if (is_string($criteria)) {
            $criteria = json_decode($criteria, true);
        }

        if (!is_array($criteria)) {
            $criteria = [
                'format' => 'pdf'
            ];
        }
        
        $query = $this->build_report_query($criteria);
        $results = $this->db->get_results($query);
        return $this->format_report_data($results, $criteria['format']);
    }

    private function build_report_query($criteria)
    {
        global $wpdb;
        $judokas_table = $wpdb->prefix . 'judokas';
        $competitions_table = $wpdb->prefix . 'competitions_judoka';

        $query = "SELECT j.*, 
                        COUNT(c.id) as total_competitions,
                        SUM(c.points) as total_points,
                        GROUP_CONCAT(DISTINCT c.medals) as medals
                 FROM {$judokas_table} j
                 LEFT JOIN {$competitions_table} c ON j.id = c.judoka_id
                 WHERE 1=1";

        if (!empty($criteria['club'])) {
            $query .= $wpdb->prepare(" AND j.club = %s", $criteria['club']);
        }
        if (!empty($criteria['category'])) {
            $query .= $wpdb->prepare(" AND j.category = %s", $criteria['category']);
        }
        if (!empty($criteria['weight_min'])) {
            $query .= $wpdb->prepare(" AND j.weight >= %f", $criteria['weight_min']);
        }
        if (!empty($criteria['weight_max'])) {
            $query .= $wpdb->prepare(" AND j.weight <= %f", $criteria['weight_max']);
        }
        if (!empty($criteria['period_start'])) {
            $query .= $wpdb->prepare(" AND c.date_competition >= %s", $criteria['period_start']);
        }
        if (!empty($criteria['period_end'])) {
            $query .= $wpdb->prepare(" AND c.date_competition <= %s", $criteria['period_end']);
        }

        $query .= " GROUP BY j.id";

        if (!empty($criteria['sort_by'])) {
            $query .= " ORDER BY " . esc_sql($criteria['sort_by']) . " " .
                (!empty($criteria['sort_order']) ? esc_sql($criteria['sort_order']) : 'ASC');
        }

        return $query;
    }

    private function format_report_data($data, $format)
    {
        switch ($format) {
            case 'pdf':
                return $this->generate_pdf_report($data);
            case 'excel':
                return $this->generate_excel_report($data);
            default:
                return $data;
        }
    }

    private function generate_pdf_report($data)
    {
        require_once(JUDOKA_PLUGIN_DIR . 'vendor/autoload.php');

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator('Judoka Management System');
        $pdf->SetTitle('Judoka Report');

        $pdf->AddPage();


        $pdf->SetFont('helvetica', '', 10);

        $html = $this->generate_report_html($data);


        $pdf->writeHTML($html, true, false, true, false, '');


        return $pdf->Output('judoka_report.pdf', 'S');
    }


    private function generate_excel_report($data)
    {
        require_once(JUDOKA_PLUGIN_DIR . 'vendor/autoload.php');

        $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['Name', 'Club', 'Category', 'Weight', 'Total Competitions', 'Total Points', 'Medals'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        $row = 2;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item->full_name);
            $sheet->setCellValue('B' . $row, $item->club);
            $sheet->setCellValue('C' . $row, $item->category);
            $sheet->setCellValue('D' . $row, $item->weight);
            $sheet->setCellValue('E' . $row, $item->total_competitions);
            $sheet->setCellValue('F' . $row, $item->total_points);
            $sheet->setCellValue('G' . $row, $item->medals);
            $row++;
        }

        $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }

    private function generate_report_html($data)
    {
        $html = '<table border="1" cellpadding="4">
                    <tr>
                        <th>Name</th>
                        <th>Club</th>
                        <th>Category</th>
                        <th>Weight</th>
                        <th>Total Competitions</th>
                        <th>Total Points</th>
                        <th>Medals</th>
                    </tr>';

        foreach ($data as $item) {
            $html .= '<tr>
                        <td>' . esc_html($item->full_name) . '</td>
                        <td>' . esc_html($item->club) . '</td>
                        <td>' . esc_html($item->category) . '</td>
                        <td>' . esc_html($item->weight) . '</td>
                        <td>' . esc_html($item->total_competitions) . '</td>
                        <td>' . esc_html($item->total_points) . '</td>
                        <td>' . esc_html($item->medals) . '</td>
                    </tr>';
        }

        $html .= '</table>';
        return $html;
    }

    public function share_report_email($email, $report_data, $format = 'pdf')
    {

        if (is_string($report_data)) {
            $report_data = json_decode($report_data, true);
        }

        $attachment = $this->generate_report($report_data);

        $to = sanitize_email($email);
        $subject = 'Judoka Report';
        $message = 'Please find attached the requested judoka report.';
        $headers = array('Content-Type: text/html; charset=UTF-8');

        $file_name = 'judoka_report.' . $format;
        $attachment_path = wp_upload_dir()['path'] . '/' . $file_name;
        file_put_contents($attachment_path, $attachment);

        $result = wp_mail($to, $subject, $message, $headers, array($attachment_path));
        unlink($attachment_path);

        return $result;
    }
}
