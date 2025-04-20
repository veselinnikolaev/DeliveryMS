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
            if (!empty($_POST['name'])) {
                $opts["name LIKE '%" . $_POST['name'] . "%' AND 1 "] = "1";
            }
            if (!empty($_POST['phone'])) {
                $opts["phone_number LIKE '%" . $_POST['phone'] . "%' AND 1 "] = "1";
            }
            if (!empty($_POST['email'])) {
                $opts["email LIKE '%" . $_POST['email'] . "%' AND 1 "] = "1";
            }
            if (!empty($_POST['address'])) {
                $opts["address LIKE '%" . $_POST['address'] . "%' AND 1 "] = "1";
            }
            if (!empty($_POST['country'])) {
                $opts["country LIKE '%" . $_POST['country'] . "%' AND 1 "] = "1";
            }
            if (!empty($_POST['region'])) {
                $opts["region LIKE '%" . $_POST['region'] . "%' AND 1 "] = "1";
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
        if (isset($_POST['courierData'])) {
            // Decode the JSON data
            $couriers = json_decode($_POST['courierData'], true);

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
        if (!empty($_POST['send'])) {
            if ($userModel->existsBy(['email' => $_POST['email']])) {
                $error_message = "User with this email already exists.";
            } else if ($_POST['password'] !== $_POST['repeat_password']) {
                $error_message = "Passwords do not match.";
            } else {
                $_POST['password_hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $_POST['role'] = 'courier';

                if ($userModel->save($_POST)) {
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

        if (!empty($_POST['id'])) {
            $userModel->delete($_POST['id']);
            if ($_POST['id'] == $_SESSION['user']['id']) {
                session_destroy();
            }
        }

        $couriers = $userModel->getAll(['role' => 'courier']);
        $this->view('ajax', ['couriers' => $couriers]);
    }

    function bulkDelete() {
        $userModel = new \App\Models\User();

        if (!empty($_POST['ids']) && is_array($_POST['ids'])) {
            $userModel->deleteBy(['id' => $_POST['ids']]);
        }

        $couriers = $userModel->getAll(['role' => 'courier']);
        $this->view('ajax', ['couriers' => $couriers]);
    }

    function edit() {
        $userModel = new \App\Models\User();

        $arr = $userModel->get($_GET['id']);

        // Check if the form has been submitted
        if (!empty($_POST['id'])) {
            $id = $_POST['id'];
            // Save the data using the Courier model
            if ($userModel->update($_POST)) {
                // Redirect to the list of couriers on successful creation
                $notificationModel = new \App\Models\Notification();
                $adminName = $_SESSION['user']['name'];
                $notificationModel->save([
                    'user_id' => $id,
                    'message' => "Your profile has been edited by: $adminName",
                    'link' => INSTALL_URL . "?controller=User&action=profile&id=$id",
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
        if (isset($_POST['courierData'])) {
            // Decode the JSON data
            $couriers = json_decode($_POST['courierData'], true);

            if (!$couriers || empty($couriers)) {
                echo "No couriers to export";
                exit;
            }
        }

        $format = isset($_POST['format']) ? $_POST['format'] : 'pdf';

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

    public function getCourierLocation() {
        header('Content-Type: application/json');

        if (!isset($_POST['user_id']) || !isset($_POST['order_id'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }

        $user_id = intval($_POST['user_id']);
        $order_id = intval($_POST['order_id']);

        // Get order details
        $orderModel = new \App\Models\Order();
        $order = $orderModel->get($order_id);

        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }

        // Get courier's current location or geocode their address
        $courierLocation = $this->getCourierCurrentLocation($user_id, $order);

        $estimated_time = !empty($order['delivery_date']) ?
                date($this->settings['date_format'] . ' H:i', $order['delivery_date']) :
                null;

        echo json_encode([
            'success' => true,
            'latitude' => $courierLocation['latitude'],
            'longitude' => $courierLocation['longitude'],
            'timestamp' => time(),
            'estimated_time' => $estimated_time
        ]);
        exit;
    }

    private function getCourierCurrentLocation($user_id, $order) {
        // First check if we have a recent location in the database
        $locationModel = new \App\Models\CourierLocation();
        $existingLocations = $locationModel->getAll(['user_id' => $user_id], 'timestamp DESC');

        if (!empty($existingLocations)) {
            return [
                'latitude' => $existingLocations[0]['latitude'],
                'longitude' => $existingLocations[0]['longitude']
            ];
        }

        // If no location exists, use region-based coordinates
        return $this->getRegionCoordinates($order['region']);
    }

    private function geocodeAddress($address) {
        // Clean up the address
        $address = trim($address);
        $address = str_replace('boulevard', 'blvd', $address);
        $address = str_replace('number', '', $address);
        $address = preg_replace('/\s+/', ' ', $address);

        $url = 'https://nominatim.openstreetmap.org/search';
        $params = http_build_query([
            'q' => $address,
            'format' => 'json',
            'limit' => 1
        ]);

        error_log("Attempting to geocode: " . $address);

        $ch = curl_init($url . '?' . $params);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'DeliveryTrackingSystem/1.0',
            CURLOPT_TIMEOUT => 10
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("Geocoding error: " . $error);
            return $this->getRegionCoordinates('Varna');
        }

        $data = json_decode($response, true);
        error_log("Geocoding response: " . print_r($data, true));

        if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
            return [
                'latitude' => $data[0]['lat'],
                'longitude' => $data[0]['lon']
            ];
        }

        // If geocoding fails, return region coordinates
        return $this->getRegionCoordinates('Varna');
    }

    private function getRegionCoordinates($region) {
        $coordinates = [
            'Varna' => [
                'latitude' => 43.2141,
                'longitude' => 27.9147
            ],
            'Sofia' => [
                'latitude' => 42.6977,
                'longitude' => 23.3219
            ],
            'Plovdiv' => [
                'latitude' => 42.1429,
                'longitude' => 24.7537
            ],
            'Burgas' => [
                'latitude' => 42.5048,
                'longitude' => 27.4626
            ]
        ];

        return $coordinates[$region] ?? $coordinates['Varna'];
    }
}
