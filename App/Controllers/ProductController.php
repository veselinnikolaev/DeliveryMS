<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Setting;
use App\Models\Product;
use Core;
use Core\Services\ExportService;
use Core\View;
use Core\Controller;

class ProductController extends Controller {

    public string $layout = 'admin';

    public function __construct() {
        parent::__construct();
        if (empty($_SESSION['user'])) {
            header("Location: " . INSTALL_URL . "?controller=Auth&action=login", true, 301);
            exit;
        }
        if ($_SESSION['user']['role'] == 'user') {
            header("Location: " . INSTALL_URL, true, 301);
            exit;
        }
    }

    public function list($layout = 'admin'): void {
        $productModel = new Product();

        $opts = array();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($this->post('name'))) {
                $opts['name LIKE'] = '%' . $this->post('name') . '%';
            }
            if (!empty($this->post('description'))) {
                $opts['description LIKE'] = '%' . $this->post('description') . '%';
            }
            if (!empty($this->post('minPrice'))) {
                $opts['price >='] = \Core\Security::float($this->post('minPrice'));
            }
            if (!empty($this->post('maxPrice'))) {
                $opts['price <='] = \Core\Security::float($this->post('maxPrice'));
            }
            if (!empty($this->post('minStock'))) {
                $opts['stock >='] = \Core\Security::int($this->post('minStock'));
            }
            if (!empty($this->post('maxStock'))) {
                $opts['stock <='] = \Core\Security::int($this->post('maxStock'));
            }
        }

        $products = $productModel->getAll($opts);

        $this->view($layout, ['products' => $products, 'currency' => $this->settings['currency_code']]);
    }

    public function filter(): void {
        $this->list('ajax');
    }

    public function create(): void {
        // Create an instance of the Product model
        $productModel = new Product();

        // Check if the form has been submitted
        if (!empty($this->post('send'))) {
            // Save the data using the Product model
            $postData = $this->post();
            if ($productModel->save($postData)) {
                // Redirect to the list of couriers on successful creation
                header("Location: " . $_SESSION['previous_url'], true, 301);
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

    public function delete(): void {
        $productModel = new Product();

        if (!empty($this->post('id'))) {
            $productModel->delete(\Core\Security::int($this->post('id')));
        }

        $products = $productModel->getAll();
        $this->view('ajax', ['products' => $products]);
    }

    public function bulkDelete(): void {
        $productModel = new Product();

        if (!empty($this->post('ids')) && is_array($this->post('ids'))) {
            $productModel->deleteBy(['id' => $this->post('ids')]);
        }

        $products = $productModel->getAll();
        $this->view('ajax', ['products' => $products]);
    }

    public function edit(): void {
        $productModel = new Product();

        $arr = $productModel->get(\Core\Security::int($this->get('id')));

        // Check if the form has been submitted
        if (!empty($this->post('id'))) {

            // Save the data using the Product model
            $postData = $this->post();
            if ($productModel->update($postData)) {
                // Redirect to the list of couriers on successful creation
                header("Location: " . $_SESSION['previous_url'], true, 301);
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

    public function print(): void {
        if ($this->post('productData') !== null) {
            // Decode the JSON data
            $products = json_decode($this->post('productData'), true);

            if (!$products || empty($products)) {
                echo "No products to export";
                exit;
            }
        }

        $this->view('ajax', ['products' => $products]);
    }

    public function export(): void {
        // Check if productData is provided
        if ($this->post('productData') !== null) {
            // Decode the JSON data
            $products = json_decode($this->post('productData'), true);

            if (!$products || empty($products)) {
                echo "No products to export";
                exit;
            }
        }

        $format = $this->post('format') !== null ? $this->post('format') : 'pdf';

        // Export based on format
        switch ($format) {
            case 'pdf':
                ExportService::exportToPDF($products, 'Products Export', 'products_export.pdf');
            case 'excel':
                ExportService::exportToExcel($products, 'products_export.xlsx');
            case 'csv':
                ExportService::exportToCSV($products, 'products_export.csv');
            default:
                echo "Invalid export format";
                exit;
        }
    }
}
