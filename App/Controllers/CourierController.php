<?php

namespace App\Controllers;

use Models;
use Core;
use Core\View;
use Core\Controller;

class CourierController extends Controller {

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
        $userModel = new \App\Models\User();

        $opts = array();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($this->post('name'))) {
                $opts['name LIKE'] = '%' . $this->post('name') . '%';
            }
            if (!empty($this->post('phone'))) {
                $opts['phone_number LIKE'] = '%' . $this->post('phone') . '%';
            }
            if (!empty($this->post('email'))) {
                $opts['email LIKE'] = '%' . $this->post('email') . '%';
            }
            if (!empty($this->post('address'))) {
                $opts['address LIKE'] = '%' . $this->post('address') . '%';
            }
            if (!empty($this->post('country'))) {
                $opts['country LIKE'] = '%' . $this->post('country') . '%';
            }
            if (!empty($this->post('region'))) {
                $opts['region LIKE'] = '%' . $this->post('region') . '%';
            }
        }

// Извличане на всички записи на куриери от таблицата users
        $opts['role'] = 'courier';
        $couriers = $userModel->getAll($opts);

// Прехвърляне на данни към изгледа
        $this->view($layout, ['couriers' => $couriers]);
    }

    function filter() {
        $this->list('ajax');
    }

    function print() {
// Check if courierData is provided
        if (isset($this->post('courierData'))) {
// Decode the JSON data
            $couriers = json_decode($this->post('courierData'), true);

            if (!$couriers || empty($couriers)) {
                echo "No couriers to print";
                exit;
            }
        }

        $this->view('ajax', ['couriers' => $couriers]);
    }

    function create() {
// Create an instance of the User model
        $userModel = new \App\Models\User();

// Check if the form has been submitted
        if (!empty($this->post('send'))) {
            if ($userModel->existsBy(['email' => $this->post('email')])) {
                $error_message = "Courier with this email already exists.";
            } else if ($this->post('password') !== $this->post('repeat_password')) {
                $error_message = "Passwords do not match.";
            } else {
                $postData = $this->post();
                $postData['password_hash'] = password_hash($this->post('password'), PASSWORD_DEFAULT);
                $postData['role'] = 'courier';

                if ($userModel->save($postData)) {
                    header("Location: " . $_SESSION['previous_url'], true, 301);
                    exit;
                } else {
                    $error_message = "Failed to save courier. Please try again.";
                }
            }
        }

// Pass any error messages to the view
        $arr = array();
        if (isset($error_message)) {
            $arr['error_message'] = $error_message;
        }

// Load the view and pass the data to it
        $this->view($this->layout, $arr);
    }

    function delete() {
        $userModel = new \App\Models\User();

        if (!empty($this->post('id'))) {
            $userModel->delete(\Core\Security::int($this->post('id')));
            if (\Core\Security::int($this->post('id')) == $_SESSION['user']['id']) {
                session_destroy();
            }
        }

        $couriers = $userModel->getAll(['role' => 'courier']);
        $this->view('ajax', ['couriers' => $couriers]);
    }

    function bulkDelete() {
        $userModel = new \App\Models\User();

        if (!empty($this->post('ids')) && is_array($this->post('ids'))) {
            $userModel->deleteBy(['id' => $this->post('ids')]);
        }

        $couriers = $userModel->getAll(['role' => 'courier']);
        $this->view('ajax', ['couriers' => $couriers]);
    }

    function edit() {
        $userModel = new \App\Models\User();

        $arr = $userModel->get(\Core\Security::int($this->get('id')));

// Check if the form has been submitted
        if (!empty($this->post('id'))) {
            $postData = $this->post();
            if ($userModel->update($postData)) {
// Redirect to the list of couriers on successful creation
                $notificationModel = new \App\Models\Notification();
                $adminName = $_SESSION['user']['name'];
                $notificationModel->save([
                    'user_id' => \Core\Security::int($this->post('id')),
                    'message' => "Your profile has been edited by: $adminName",
                    'link' => INSTALL_URL . "?controller=User&action=profile&id=" . \Core\Security::int($this->post('id')),
                    'created_at' => time()
                ]);

                header("Location: " . $_SESSION['previous_url'], true, 301);
                exit;
            } else {
// If saving fails, set an error message
                $arr['error_message'] = "Failed to create the courier. Please try again.";
            }
        }

// Load the view and pass the data to it
        $this->view($this->layout, $arr);
    }

    function export() {
// Check if courierData is provided
        if (isset($this->post('courierData'))) {
// Decode the JSON data
            $couriers = json_decode($this->post('courierData'), true);

            if (!$couriers || empty($couriers)) {
                echo "No couriers to export";
                exit;
            }
        }

        $format = isset($this->post('format')) ? $this->post('format') : 'pdf';

// Export based on format
        switch ($format) {
            case 'pdf':
                $this->exportAsPDF($couriers);
                break;
            case 'excel':
                $this->exportAsExcel($couriers);
                break;
            case 'csv':
                $this->exportAsCSV($couriers);
                break;
            default:
                echo "Invalid export format";
                exit;
        }
    }

    private function exportAsPDF($couriers) {
        if (ob_get_level()) {
            ob_end_clean();
        }
        require_once(__DIR__ . '/../Helpers/export/tcpdf/tcpdf.php');

        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8');
        $pdf->SetCreator('Your App');
        $pdf->SetTitle('Couriers Export');
        $pdf->SetHeaderData('', 0, 'Couriers List', '');
        $pdf->setHeaderFont(Array('helvetica', '', 12));
        $pdf->setFooterFont(Array('helvetica', '', 10));
        $pdf->SetDefaultMonospacedFont('courier');
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);

        $pdf->AddPage();

// Generate HTML table with dynamic headers
        $html = $this->generateDynamicCourierTable($couriers);
        $pdf->writeHTML($html, true, false, true, false, '');

// Output PDF
        $pdf->Output('couriers_export.pdf', 'D');
        exit;
    }

    private function generateDynamicCourierTable($couriers) {
// Start HTML table
        $html = '<table border="1" cellpadding="5">
<thead>
    <tr>';

// If we have couriers, use their keys as headers
        if (!empty($couriers) && is_array($couriers[0])) {
            $headers = array_keys($couriers[0]);

// Add headers to table
            foreach ($headers as $header) {
                $displayHeader = ucwords(str_replace('_', ' ', $header));
                $html .= '<th>' . $displayHeader . '</th>';
            }

            $html .= '</tr>
    </thead>
    <tbody>';

// Add courier data
            foreach ($couriers as $courier) {
                $html .= '<tr>';
                foreach ($courier as $key => $value) {
// Handle empty values
                    if (empty($value) && $value !== 0) {
                        $value = 'N/A';
                    }
// Sanitize output
                    $html .= '<td>' . htmlspecialchars($value) . '</td>';
                }
                $html .= '</tr>';
            }
        } else {
// Fallback for no data
            $html .= '<th>No Data Available</th></tr></thead><tbody><tr><td>No couriers found</td></tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }

    private function exportAsExcel($couriers) {
// Include SimpleXLSXGen
        require(__DIR__ . '/../Helpers/export/simplexlsxgen/src/SimpleXLSXGen.php');

// Prepare data
        $data = [];

// First courier in array determines headers
        if (!empty($couriers) && is_array($couriers[0])) {
// Use keys from first courier for headers, ensuring proper capitalization
            $headers = array_keys($couriers[0]);
            $headerRow = [];

            foreach ($headers as $header) {
// Convert courier_id to Courier ID, etc.
                $headerRow[] = ucwords(str_replace('_', ' ', $header));
            }

            $data[] = $headerRow;

// Add couriers
            foreach ($couriers as $courier) {
                $row = [];
                foreach ($courier as $value) {
// Handle empty values
                    $row[] = (empty($value) && $value !== 0) ? 'N/A' : $value;
                }
                $data[] = $row;
            }
        } else {
// Fallback for no data
            $data[] = ['No Data Available'];
            $data[] = ['No couriers found'];
        }

// Create and send file
        \Shuchkin\SimpleXLSXGen::fromArray($data)->downloadAs('couriers_export.xlsx');
        exit;
    }

    private function exportAsCSV($couriers) {
// Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="couriers_export.csv"');

// Open output stream
        $output = fopen('php://output', 'w');

// Determine headers dynamically from the first courier
        if (!empty($couriers) && is_array($couriers[0])) {
            $headers = array_keys($couriers[0]);
// Convert keys to readable headers (e.g., courier_id to Courier ID)
            $readableHeaders = array_map(function ($header) {
                return ucwords(str_replace('_', ' ', $header));
            }, $headers);

// Add headers
            fputcsv($output, $readableHeaders);

// Add data using the actual keys from the data
            foreach ($couriers as $courier) {
                $row = [];
                foreach ($courier as $value) {
// Handle empty values
                    $row[] = (empty($value) && $value !== 0) ? 'N/A' : $value;
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

    public function updateLocation() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'courier') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }

        $courierLocationModel = new \App\Models\CourierLocation();

        $locationData = [
            'user_id' => $_SESSION['user']['id'],
            'latitude' => \Core\Security::float($this->post('latitude')),
            'longitude' => \Core\Security::float($this->post('longitude')),
            'timestamp' => time()
        ];

        $courierLocation = $courierLocationModel->getFirstBy(['user_id' => $_SESSION['user']['id']]);
        $status = false;
        if (empty($courierLocation)) {
            $status = $courierLocationModel->save($locationData);
        } else {
            $status = $courierLocationModel->update(['id' => $courierLocation['id']] + $locationData);
        }

        if ($status) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update location']);
        }
        exit;
    }

    function getLocation() {
        header('Content-Type: application/json');

        if (!empty($this->get('courier_id'))) {
            $courierLocationModel = new \App\Models\CourierLocation();
            $location = $courierLocationModel->getLatestLocation(\Core\Security::int($this->get('courier_id')));

            if ($location) {
                echo json_encode([
                    'status' => 'success',
                    'data' => [
                        'latitude' => $location['latitude'],
                        'longitude' => $location['longitude'],
                        'timestamp' => $location['timestamp']
                    ]
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Location not found'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Courier ID not provided'
            ]);
        }
        exit;
    }

    public function startTracking() {
        if ($_SESSION['user']['role'] !== 'courier') {
            header("Location: " . INSTALL_URL, true, 301);
            exit;
        }

        $this->view($this->layout, [
            'active_orders' => $this->getActiveOrders($_SESSION['user']['id'])
        ]);
    }

    private function getActiveOrders($courierId) {
        $orderModel = new \App\Models\Order();
        return $orderModel->getAll([
                    'courier_id' => $courierId,
                    'status' => 'shipped'
        ]);
    }
}
