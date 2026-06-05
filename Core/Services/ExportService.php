<?php

declare(strict_types=1);

namespace Core\Services;

use Shuchkin\SimpleXLSXGen;

class ExportService {

    /**
     * Export data to PDF format
     * 
     * @param array $data The data to export
     * @param string $title The title of the PDF document
     * @param string $filename The filename for the download
     * @return void
     */
    public static function exportToPDF(array $data, string $title, string $filename): void {
        if (ob_get_level()) {
            ob_end_clean();
        }

        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8');
        $pdf->SetCreator('DeliveryMS');
        $pdf->SetTitle($title);
        $pdf->SetHeaderData('', 0, $title, '');
        $pdf->setHeaderFont(Array('helvetica', '', 12));
        $pdf->setFooterFont(Array('helvetica', '', 10));
        $pdf->SetDefaultMonospacedFont('courier');
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);

        $pdf->AddPage();

        // Generate HTML table with dynamic headers
        $html = self::generateDynamicTable($data);
        $pdf->writeHTML($html, true, false, true, false, '');

        // Output PDF
        $pdf->Output($filename, 'D');
        exit;
    }

    /**
     * Export data to Excel format
     * 
     * @param array $data The data to export
     * @param string $filename The filename for the download
     * @return void
     */
    public static function exportToExcel(array $data, string $filename): void {
        $exportData = [];

        // First item in array determines headers
        if (!empty($data) && is_array($data[0])) {
            // Use keys from first item for headers, ensuring proper capitalization
            $headers = array_keys($data[0]);
            $capitalizedHeaders = array_map(function($header) {
                return ucwords(str_replace('_', ' ', $header));
            }, $headers);

            // Add headers as first row
            $exportData[] = $capitalizedHeaders;

            // Add data rows
            foreach ($data as $item) {
                $row = [];
                foreach ($headers as $header) {
                    $row[] = $item[$header] ?? '';
                }
                $exportData[] = $row;
            }
        } else {
            // Fallback for no data
            $exportData[] = ['No Data Available'];
            $exportData[] = ['No records found'];
        }

        // Create and send file
        SimpleXLSXGen::fromArray($exportData)->downloadAs($filename);
        exit;
    }

    /**
     * Export data to CSV format
     * 
     * @param array $data The data to export
     * @param string $filename The filename for the download
     * @return void
     */
    public static function exportToCSV(array $data, string $filename): void {
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Open output stream
        $output = fopen('php://output', 'w');

        // Determine headers dynamically from the first item
        if (!empty($data) && is_array($data[0])) {
            $headers = array_keys($data[0]);
            $capitalizedHeaders = array_map(function($header) {
                return ucwords(str_replace('_', ' ', $header));
            }, $headers);

            // Write headers
            fputcsv($output, $capitalizedHeaders);

            // Write data rows
            foreach ($data as $item) {
                $row = [];
                foreach ($headers as $header) {
                    $row[] = $item[$header] ?? '';
                }
                fputcsv($output, $row);
            }
        } else {
            // Fallback for empty data
            fputcsv($output, ['No data available']);
        }

        fclose($output);
        exit;
    }

    /**
     * Generate dynamic HTML table from data
     * 
     * @param array $data The data to convert to table
     * @return string HTML table
     */
    private static function generateDynamicTable(array $data): string {
        // Start HTML table
        $html = '<table>
<thead>
    <tr>';

        // If we have data, use keys as headers
        if (!empty($data) && is_array($data[0])) {
            $headers = array_keys($data[0]);

            // Add headers to table
            foreach ($headers as $header) {
                $displayHeader = ucwords(str_replace('_', ' ', $header));
                $html .= '<th>' . $displayHeader . '</th>';
            }

            $html .= '</tr>
</thead>
<tbody>';

            // Add data rows
            foreach ($data as $item) {
                $html .= '<tr>';
                foreach ($headers as $header) {
                    $html .= '<td>' . ($item[$header] ?? '') . '</td>';
                }
                $html .= '</tr>';
            }
        } else {
            // Fallback for no data
            $html .= '<th>No Data Available</th></tr></thead><tbody><tr><td>No records found</td></tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }
}
