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
        // Create an instance of the Product model
        $productModel = new \App\Models\Product();

        // Check if the form has been submitted
        if (!empty($_POST['send'])) {
            // Save the data using the Product model
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

            // Save the data using the Product model
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
        if (isset($_POST['productData'])) {
            // Decode the JSON data
            $products = json_decode($_POST['productData'], true);

            if (!$products || empty($products)) {
                echo "No products to export";
                exit;
            }
        }

        $this->view('ajax', ['products' => $products]);
    }

    function export() {
        // Check if productData is provided
        if (isset($_POST['productData'])) {
            // Decode the JSON data
            $products = json_decode($_POST['productData'], true);

            if (!$products || empty($products)) {
                echo "No products to export";
                exit;
            }
        }

        $format = isset($_POST['format']) ? $_POST['format'] : 'pdf';

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
        if (ob_get_level()) {
            ob_end_clean();
        }
        require_once(__DIR__ . '/../Helpers/export/tcpdf/tcpdf.php');

        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8');
        $pdf->SetCreator('Your App');
        $pdf->SetTitle('Products Export');
        $pdf->SetHeaderData('', 0, 'Products List', '');
        $pdf->setHeaderFont(Array('helvetica', '', 12));
        $pdf->setFooterFont(Array('helvetica', '', 10));
        $pdf->SetDefaultMonospacedFont('courier');
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);

        $pdf->AddPage();

        // Generate HTML table with dynamic headers
        $html = $this->generateDynamicProductTable($products);
        $pdf->writeHTML($html, true, false, true, false, '');

        // Output PDF
        $pdf->Output('products_export.pdf', 'D');
        exit;
    }

    private function generateDynamicProductTable($products) {
        // Start HTML table
        $html = '<table border="1" cellpadding="5">
    <thead>
        <tr>';

        // If we have products, use their keys as headers
        if (!empty($products) && is_array($products[0])) {
            $headers = array_keys($products[0]);

            // Add headers to table
            foreach ($headers as $header) {
                $displayHeader = ucwords(str_replace('_', ' ', $header));
                $html .= '<th>' . $displayHeader . '</th>';
            }

            $html .= '</tr>
        </thead>
        <tbody>';

            // Add product data
            foreach ($products as $product) {
                $html .= '<tr>';
                foreach ($product as $value) {
                    $html .= '<td>' . htmlspecialchars($value) . '</td>';
                }
                $html .= '</tr>';
            }
        } else {
            // Fallback for no data
            $html .= '<th>No Data Available</th></tr></thead><tbody><tr><td>No products found</td></tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }

    private function exportAsExcel($products) {
        // Include SimpleXLSXGen
        require(__DIR__ . '/../Helpers/export/simplexlsxgen/src/SimpleXLSXGen.php');

        // Prepare data
        $data = [];

        // First product in array determines headers
        if (!empty($products) && is_array($products[0])) {
            // Use keys from first product for headers, ensuring proper capitalization
            $headers = array_keys($products[0]);
            $headerRow = [];

            foreach ($headers as $header) {
                // Convert product_id to Product ID, etc.
                $headerRow[] = ucwords(str_replace('_', ' ', $header));
            }

            $data[] = $headerRow;

            // Add products
            foreach ($products as $product) {
                $data[] = array_values($product);
            }
        } else {
            // Fallback for no data
            $data[] = ['No Data Available'];
            $data[] = ['No products found'];
        }

        // Create and send file
        \Shuchkin\SimpleXLSXGen::fromArray($data)->downloadAs('products_export.xlsx');
        exit;
    }

    private function exportAsCSV($products) {
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="products_export.csv"');

        // Open output stream
        $output = fopen('php://output', 'w');

        // Determine headers dynamically from the first product
        if (!empty($products) && is_array($products[0])) {
            $headers = array_keys($products[0]);
            // Convert keys to readable headers (e.g., product_id to Product ID)
            $readableHeaders = array_map(function ($header) {
                return ucwords(str_replace('_', ' ', $header));
            }, $headers);

            // Add headers
            fputcsv($output, $readableHeaders);

            // Add data using the actual keys from the data
            foreach ($products as $product) {
                fputcsv($output, array_values($product));
            }
        } else {
            // Fallback for empty data
            fputcsv($output, ['No data available']);
        }

        fclose($output);
        exit;
    }
}
