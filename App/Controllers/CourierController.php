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
        // Return JSON response
        header('Content-Type: application/json');

        // Validate request
        if (!isset($_POST['user_id']) || !isset($_POST['order_id'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }

        $user_id = intval($_POST['user_id']);
        $order_id = intval($_POST['order_id']);

        // Security check - ensure the user has permission to view this order
        if (!$this->isUserAuthorized($order_id)) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        // Get latest location
        $locationModel = new \App\Models\CourierLocation();
        $location = $locationModel->getAll(['user_id' => $user_id], 'timestamp DESC')[0];

        if ($location) {
            // Get estimated delivery time
            $orderModel = new \App\Models\Order();
            $order = $orderModel->get($order_id);
            $estimated_time = !empty($order['delivery_date']) ? date($this->settings['date_format'] . ' H:i', $order['delivery_date']) : null;

            echo json_encode([
                'success' => true,
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
                'timestamp' => $location['timestamp'],
                'estimated_time' => $estimated_time
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No location data available']);
        }
        exit;
    }

    /**
     * API endpoint for couriers to update their location
     */
    public function updateLocation() {
        // Return JSON response
        header('Content-Type: application/json');

        // Check if the request is POST and user is authenticated as courier
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'courier') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        // Get POST data (supports both form data and JSON)
        $latitude = isset($_POST['latitude']) ? $_POST['latitude'] : null;
        $longitude = isset($_POST['longitude']) ? $_POST['longitude'] : null;

        // If not form data, try to parse JSON input
        if ($latitude === null || $longitude === null) {
            $input = json_decode(file_get_contents('php://input'), true);
            $latitude = isset($input['latitude']) ? $input['latitude'] : null;
            $longitude = isset($input['longitude']) ? $input['longitude'] : null;
        }

        // Validate input
        if ($latitude === null || $longitude === null) {
            echo json_encode(['success' => false, 'message' => 'Missing latitude or longitude']);
            exit;
        }

        $user_id = $_SESSION['user']['id'];

        // Store the location update
        $locationModel = new \App\Models\CourierLocation();
        $result = $locationModel->save(['user_id' => $user_id, 'latitude' => $latitude, 'longitude' => $longitude, 'timestamp' => time()]);

        // Update estimated delivery times for active orders if successful
        if ($result) {
            $this->updateEstimatedDeliveryTimes($user_id);
        }

        echo json_encode(['success' => (bool) $result]);
        exit;
    }

    /**
     * Check if user is authorized to view an order
     */
    private function isUserAuthorized($order_id) {
        // Admin can view all orders
        if ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['role'] === 'root') {
            return true;
        }

        $orderModel = new \App\Models\Order();
        $order = $orderModel->get($order_id);

        // User can only view their own orders
        if ($_SESSION['user']['role'] === 'user') {
            return $order && $order['user_id'] == $_SESSION['user']['id'];
        }

        // Courier can only view orders assigned to them
        if ($_SESSION['user']['role'] === 'courier') {
            return $order && $order['courier_id'] == $_SESSION['user']['id'];
        }

        return false;
    }

    /**
     * Update estimated delivery times for orders assigned to this courier
     */
    private function updateEstimatedDeliveryTimes($user_id) {
        $orderModel = new \App\Models\Order();

        // Get active orders for this courier
        $activeOrders = $orderModel->getAll([
            'courier_id' => $user_id,
            'status  => \'shipped\''
        ]);

        if (empty($activeOrders)) {
            return;
        }

        // Get courier's current location
        $locationModel = new \App\Models\CourierLocation();
        $courier_location = $locationModel->getAll(['user_id' => $user_id], 'timestamp DESC')[0];

        if (!$courier_location) {
            return;
        }

        // For each order, calculate estimated time based on distance
        foreach ($activeOrders as $order) {
            // Get destination coordinates (assuming you've stored them or need to geocode)
            $destination = $this->getOrderDestinationCoordinates($order['id']);

            if ($destination) {
                // Calculate estimated time based on distance
                $estimated_time = $this->calculateEstimatedTime(
                        $courier_location['latitude'],
                        $courier_location['longitude'],
                        $destination['latitude'],
                        $destination['longitude']
                );

                // Update the order
                $orderModel->update($order['id'], [
                    'delivery_date' => $estimated_time
                ]);
            }
        }
    }

    /**
     * Get order destination coordinates using address caching
     */
    private function getOrderDestinationCoordinates($order_id) {
        // Get the order
        $orderModel = new \App\Models\Order();
        $order = $orderModel->get($order_id);

        if (!$order) {
            return null;
        }

        // Create an address string from order data
        $address = $order['address'] . ', ' . $order['region'] . ', ' . $order['country'];
        $address_hash = md5($address);

        // First, check if we have this address in our address coordinates cache
        // We'll create a simple model for this
        $coordinateModel = new \App\Models\AddressCoordinate();
        $cached_coordinates = $coordinateModel->getAll(['address_hash' => $address_hash]);

        if (!empty($cached_coordinates)) {
            return [
                'latitude' => $cached_coordinates[0]['latitude'],
                'longitude' => $cached_coordinates[0]['longitude']
            ];
        }

        // If not in cache, use OpenStreetMap for geocoding
        $coordinates = $this->geocodeAddressWithOpenStreetMap($address);

        // If geocoding successful, store coordinates for future use
        if ($coordinates) {
            $coordinateModel->save([
                'address_hash' => $address_hash,
                'address' => $address,
                'latitude' => $coordinates['latitude'],
                'longitude' => $coordinates['longitude'],
                'created_at' => time()
            ]);
        }

        return $coordinates;
    }

    /**
     * Geocode an address using OpenStreetMap's Nominatim API
     */
    private function geocodeAddressWithOpenStreetMap($address) {
        // Use OpenStreetMap Nominatim API (free)
        $url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' . urlencode($address);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Required by Nominatim usage policy
        curl_setopt($ch, CURLOPT_USERAGENT, 'DeliveryTrackingSystem/1.0');
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            // Log error
            error_log("Geocoding error: " . $error);
            return $this->getFallbackCoordinates($address);
        }

        $data = json_decode($response, true);

        if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
            return [
                'latitude' => $data[0]['lat'],
                'longitude' => $data[0]['lon']
            ];
        }

        // If geocoding fails, use fallback method
        return $this->getFallbackCoordinates($address);
    }

    /**
     * Provide fallback coordinates based on region/country
     */
    private function getFallbackCoordinates($address) {
        // Extract region and country from address
        $parts = explode(',', $address);
        $country = trim(end($parts));
        $region = (count($parts) > 1) ? trim($parts[count($parts) - 2]) : '';

        // Default coordinates map (add more as needed based on your common delivery regions)
        $regionCoordinates = [
            'Sofia' => ['latitude' => 42.6977, 'longitude' => 23.3219],
            'Bulgaria' => ['latitude' => 42.7339, 'longitude' => 25.4858],
                // Add more common regions as needed
        ];

        // Try to match by region first, then by country
        if (!empty($region) && isset($regionCoordinates[$region])) {
            return $regionCoordinates[$region];
        } elseif (!empty($country) && isset($regionCoordinates[$country])) {
            return $regionCoordinates[$country];
        }

        // Default coordinates (center of map or your warehouse location)
        return ['latitude' => 42.6977, 'longitude' => 23.3219]; // Default to Sofia if nothing else matches
    }

    /**
     * Calculate estimated delivery time based on distance
     */
    private function calculateEstimatedTime($lat1, $lon1, $lat2, $lon2) {
        // Calculate distance between points using Haversine formula
        $earth_radius = 6371; // in km

        $lat1_rad = deg2rad($lat1);
        $lon1_rad = deg2rad($lon1);
        $lat2_rad = deg2rad($lat2);
        $lon2_rad = deg2rad($lon2);

        $dlat = $lat2_rad - $lat1_rad;
        $dlon = $lon2_rad - $lon1_rad;

        $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1_rad) * cos($lat2_rad) * sin($dlon / 2) * sin($dlon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earth_radius * $c; // Distance in km
        // Adjust speed based on distance and region
        // You can customize this based on your knowledge of local traffic conditions
        $speed = ($distance > 50) ? 40 : 30; // km/h
        // Additional time for densely populated areas (this is just a simple example)
        $time_hours = $distance / $speed;
        $time_seconds = $time_hours * 3600;

        // Add buffer for traffic, stops, etc. (15%)
        $time_with_buffer = $time_seconds * 1.15;

        // Add current time to get estimated delivery time
        return time() + $time_with_buffer;
    }
}
