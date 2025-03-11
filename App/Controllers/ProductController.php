<?php

namespace App\Controllers;

use App\Models\Setting;
use Models;
use Core;
use Core\View;
use Core\Controller;

class ProductController extends Controller {

    var $layout = 'admin';
    var $settings;

    public function __construct() {
        if (empty($_SESSION['user'])) {
            header("Location: " . INSTALL_URL . "?controller=Auth&action=login", true, 301);
            exit;
        }
        if ($_SESSION['user']['role'] == 'user') {
            header("Location: " . INSTALL_URL, true, 301);
            exit;
        }
        $this->settings = $this->loadSettings();
    }

    function loadSettings() {
        $settingModel = new \App\Models\Setting();
        $settings = $settingModel->getAll();
        $app_settings = [];
        foreach ($settings as $setting) {
            $app_settings[$setting['key']] = $setting['value'];
        }
        return $app_settings;
    }

    function list($layout = 'admin') {
        $productModel = new \App\Models\Product();

        $opts = array();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['name'])) {
                $opts["name LIKE '%" . $_POST['name'] . "%' AND 1 "] = "1";
            }
            if (!empty($_POST['description'])) {
                $opts["description LIKE '%" . $_POST['description'] . "%' AND 1 "] = "1";
            }
            if (!empty($_POST['minPrice'])) {
                $opts["price >= " . $_POST['minPrice'] . " AND 1 "] = "1";
            }
            if (!empty($_POST['maxPrice'])) {
                $opts["price <= " . $_POST['maxPrice'] . " AND 1 "] = "1";
            }
            if (!empty($_POST['minStock'])) {
                $opts["stock >= " . $_POST['minStock'] . " AND 1 "] = "1";
            }
            if (!empty($_POST['maxStock'])) {
                $opts["stock <= " . $_POST['maxStock'] . " AND 1 "] = "1";
            }
        }

        $products = $productModel->getAll($opts);

        $this->view($layout, ['products' => $products, 'currency' => $this->settings['currency_code']]);
    }

    function filter() {
        $this->list('ajax');
    }

    function create() {
        // Create an instance of the Courier model
        $productModel = new \App\Models\Product();

        // Check if the form has been submitted
        if (!empty($_POST['send'])) {
            // Save the data using the Courier model
            if ($productModel->save($_POST)) {
                // Redirect to the list of couriers on successful creation
                header("Location: " . INSTALL_URL . "?controller=Product&action=list", true, 301);
                exit;
            } else {
                // If saving fails, set an error message
                $error_message = "Failed to create the product. Please try again.";
            }
        }

        // Pass any error messages to the view
        $arr = array();
        if (isset($error_message)) {
            $arr['error_message'] = $error_message;
        }
        $arr['currency'] = $this->settings['currency_code'];

        // Load the view and pass the data to it
        $this->view($this->layout, $arr);
    }

    function delete() {
        $productModel = new \App\Models\Product();

        if (!empty($_POST['id'])) {
            $productModel->delete($_POST['id']);
        }

        $products = $productModel->getAll();
        $this->view('ajax', ['products' => $products]);
    }

    function bulkDelete() {
        $productModel = new \App\Models\Product();

        if (!empty($_POST['ids']) && is_array($_POST['ids'])) {
            $inProductIds = implode(', ', $_POST['ids']);
            $productModel->deleteBy(["id IN ($inProductIds) AND 1 " => '1']);
        }

        $products = $productModel->getAll();
        $this->view('ajax', ['products' => $products]);
    }

    function edit() {
        $productModel = new \App\Models\Product();

        $arr = $productModel->get($_GET['id']);

        // Check if the form has been submitted
        if (!empty($_POST['id'])) {

            // Save the data using the Courier model
            if ($productModel->update($_POST)) {
                // Redirect to the list of couriers on successful creation
                header("Location: " . INSTALL_URL . "?controller=Product&action=list", true, 301);
                exit;
            } else {
                // If saving fails, set an error message
                $arr['error_message'] = "Failed to create the product. Please try again.";
            }
        }

        $arr['currency'] = $this->settings['currency_code'];
        // Load the view and pass the data to it
        $this->view($this->layout, $arr);
    }

    // In your Product.php controller

    function print() {
        $this->list('ajax');
    }

    function export() {
        $productModel = new \App\Models\Product();
        // Get filter and format parameters from POST
        $filters = [
            'name' => isset($_POST['name']) ? $_POST['name'] : '',
            'description' => isset($_POST['description']) ? $_POST['description'] : '',
            'price_min' => isset($_POST['price_min']) ? $_POST['price_min'] : null,
            'price_max' => isset($_POST['price_max']) ? $_POST['price_max'] : null,
            'stock_min' => isset($_POST['stock_min']) ? $_POST['stock_min'] : null,
            'stock_max' => isset($_POST['stock_max']) ? $_POST['stock_max'] : null
        ];

        $format = isset($_POST['format']) ? $_POST['format'] : 'pdf';

        // Get filtered products
        $products = $productModel->getAll($filters);

        // Export based on format
        switch ($format) {
            case 'pdf':
                $this->exportAsPDF($products);
                break;
            case 'excel':
                $this->exportAsExcel($products);
                break;
            case 'csv':
                $this->exportAsCSV($products);
                break;
            default:
                echo "Invalid export format";
                exit;
        }
    }

    private function exportAsPDF($products) {
        // Implement PDF export (using a library like FPDF, TCPDF, etc.)
        // Example with TCPDF:
        require_once('/App/Helpers/export/tcpdf/tcpdf.php');

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');
        $pdf->SetCreator('Your App');
        $pdf->SetTitle('Products Export');
        $pdf->SetHeaderData('', 0, 'Products List', '');
        $pdf->setHeaderFont(Array('helvetica', '', 12));
        $pdf->setFooterFont(Array('helvetica', '', 10));
        $pdf->SetDefaultMonospacedFont('courier');
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);

        $pdf->AddPage();

        // Add content
        $html = $this->generateProductTable($products);
        $pdf->writeHTML($html, true, false, true, false, '');

        // Output PDF
        $pdf->Output('products_export.pdf', 'D');
        exit;
    }

    private function exportAsExcel($products) {
        // Implement Excel export (using a library like PhpSpreadsheet)
        // Example with PhpSpreadsheet:
        require 'vendor/autoload.php'; // Include Composer autoloader

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add headers
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'Description');
        $sheet->setCellValue('D1', 'Price');
        $sheet->setCellValue('E1', 'Stock');

        // Add data
        $row = 2;
        foreach ($products as $product) {
            $sheet->setCellValue('A' . $row, $product['id']);
            $sheet->setCellValue('B' . $row, $product['name']);
            $sheet->setCellValue('C' . $row, $product['description']);
            $sheet->setCellValue('D' . $row, $product['price']);
            $sheet->setCellValue('E' . $row, $product['stock']);
            $row++;
        }

        // Create writer and output file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="products_export.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    private function exportAsCSV($products) {
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="products_export.csv"');

        // Open output stream
        $output = fopen('php://output', 'w');

        // Add headers
        fputcsv($output, ['ID', 'Name', 'Description', 'Price', 'Stock']);

        // Add data
        foreach ($products as $product) {
            fputcsv($output, [
                $product['id'],
                $product['name'],
                $product['description'],
                $product['price'],
                $product['stock']
            ]);
        }

        fclose($output);
        exit;
    }

    private function generateProductTable($products) {
        // Generate HTML table for PDF export
        $html = '<table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Stock</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($products as $product) {
            $html .= '<tr>
            <td>' . $product['id'] . '</td>
            <td>' . htmlspecialchars($product['name']) . '</td>
            <td>' . htmlspecialchars($product['description']) . '</td>
            <td>' . htmlspecialchars($product['price']) . '</td>
            <td>' . htmlspecialchars($product['stock']) . '</td>
        </tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }
}
