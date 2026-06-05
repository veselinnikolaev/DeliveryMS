<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\Notification;
use App\Models\CourierLocation;
use App\Models\Order;
use Core;
use Core\Services\ExportService;
use Core\View;
use Core\Controller;

class CourierController extends Controller {

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

    public function list($layout = 'admin') {
        $userModel = new User();

        $opts = array();
        $opts['role'] = 'courier';
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

        $couriers = $userModel->getAll($opts);

// Pass data to the view
        $this->view($layout, ['couriers' => $couriers]);
    }

    public function filter(): void {
        $this->list('ajax');
    }

    public function print(): void {
// Check if courierData is provided
        if ($this->post('courierData') !== null) {
// Decode the JSON data
            $couriers = json_decode($this->post('courierData'), true);

            if (!$couriers || empty($couriers)) {
                echo "No couriers to print";
                exit;
            }
        }

        $this->view('ajax', ['couriers' => $couriers]);
    }

    public function create(): void {
// Create an instance of the User model
        $userModel = new User();

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

    public function delete(): void {
        $userModel = new User();

        if (!empty($this->post('id'))) {
            $userModel->delete(\Core\Security::int($this->post('id')));
            if (\Core\Security::int($this->post('id')) == $_SESSION['user']['id']) {
                session_destroy();
            }
        }

        $couriers = $userModel->getAll(['role' => 'courier']);
        $this->view('ajax', ['couriers' => $couriers]);
    }

    public function bulkDelete(): void {
        $userModel = new User();

        if (!empty($this->post('ids')) && is_array($this->post('ids'))) {
            $userModel->deleteBy(['id' => $this->post('ids'), 'role' => 'courier']);
        }

        $couriers = $userModel->getAll(['role' => 'courier']);
        $this->view('ajax', ['couriers' => $couriers]);
    }

    public function edit(): void {
        $userModel = new User();

        $arr = $userModel->get(\Core\Security::int($this->get('id')));
        
        // Ensure the user is a courier
        if ($arr && $arr['role'] !== 'courier') {
            header("Location: " . INSTALL_URL . "?controller=Courier&action=list", true, 301);
            exit;
        }

// Check if the form has been submitted
        if (!empty($this->post('id'))) {
            $postData = $this->post();
            if ($userModel->update($postData)) {
// Redirect to the list of couriers on successful creation
                $notificationModel = new Notification();
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

    public function export(): void {
// Check if courierData is provided
        if ($this->post('courierData') !== null) {
// Decode the JSON data
            $couriers = json_decode($this->post('courierData'), true);

            if (!$couriers || empty($couriers)) {
                echo "No couriers to export";
                exit;
            }
        }

        $format = $this->post('format') !== null ? $this->post('format') : 'pdf';

// Export based on format
        switch ($format) {
            case 'pdf':
                ExportService::exportToPDF($couriers, 'Couriers Export', 'couriers_export.pdf');
            case 'excel':
                ExportService::exportToExcel($couriers, 'couriers_export.xlsx');
            case 'csv':
                ExportService::exportToCSV($couriers, 'couriers_export.csv');
            default:
                echo "Invalid export format";
                exit;
        }
    }

    public function updateLocation(): void {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'courier') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }

        $courierLocationModel = new CourierLocation();

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

    public function getLocation(): void {
        header('Content-Type: application/json');

        if (!empty($this->get('courier_id'))) {
            $courierLocationModel = new CourierLocation();
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

    public function startTracking(): void {
        if ($_SESSION['user']['role'] !== 'courier') {
            header("Location: " . INSTALL_URL, true, 301);
            exit;
        }

        $this->view($this->layout, [
            'active_orders' => $this->getActiveOrders($_SESSION['user']['id'])
        ]);
    }

    private function getActiveOrders($courierId): array {
        $orderModel = new Order();
        return $orderModel->getAll([
                    'courier_id' => $courierId,
                    'status' => 'shipped'
        ]);
    }
}
