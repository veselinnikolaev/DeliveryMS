<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\Notification;
use App\Models\CourierLocation;
use App\Models\Order;
use Core;
use Core\Security;
use Core\Services\ExportService;
use Core\View;
use Core\Controller;

class CourierController extends Controller
{
    public string $layout = 'admin';

    public function __construct()
    {
        parent::__construct();
        if (empty($_SESSION['user'])) {
            $this->redirect(INSTALL_URL . "?controller=Auth&action=login");
        }
        if ($_SESSION['user']['role'] == 'user') {
            $this->redirect(INSTALL_URL);
        }
    }

    public function list($layout = 'admin')
    {
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

    public function filter(): void
    {
        $this->list('ajax');
    }

    public function print(): void
    {
        $couriers = [];
// Check if courierData is provided
        if ($this->post('courierData') !== null) {
// Decode the JSON data
            $couriers = json_decode($this->post('courierData'), true);

            if (empty($couriers)) {
                echo "No couriers to print";
                $this->terminate();
            }
        }

        $this->view('ajax', ['couriers' => $couriers]);
    }

    public function create(): void
    {
// Create an instance of the User model
        $userModel = new User();

// Check if the form has been submitted
        if (!empty($this->post('send'))) {
            if ($userModel->existsBy(['email' => $this->post('email')])) {
                $error_message = "Courier with this email already exists.";
            } elseif ($this->post('password') !== $this->post('repeat_password')) {
                $error_message = "Passwords do not match.";
            } else {
                $postData = $this->post();
                $postData['password_hash'] = password_hash($this->post('password'), PASSWORD_DEFAULT);
                $postData['role'] = 'courier';

                if ($userModel->save($postData)) {
                    $this->redirect($_SESSION['previous_url']);
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

    public function delete(): void
    {
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

    public function bulkDelete(): void
    {
        $userModel = new User();

        if (!empty($this->post('ids')) && is_array($this->post('ids'))) {
            $userModel->deleteBy(['id' => $this->post('ids'), 'role' => 'courier']);
        }

        $couriers = $userModel->getAll(['role' => 'courier']);
        $this->view('ajax', ['couriers' => $couriers]);
    }

    public function edit(): void
    {
        $userModel = new User();
        $arr = $userModel->get(Security::int($this->get('id')));

        // handle missing courier
        if (empty($arr)) {
            $this->redirect(INSTALL_URL . "?controller=Courier&action=list");
        }

        if ($arr['role'] !== 'courier') {
            $this->redirect(INSTALL_URL . "?controller=Courier&action=list");
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

                $this->redirect($_SESSION['previous_url']);
            } else {
// If saving fails, set an error message
                $arr['error_message'] = "Failed to create the courier. Please try again.";
            }
        }

// Load the view and pass the data to it
        $this->view($this->layout, $arr);
    }

    public function export(): void
    {
        $couriers = [];

        // Use $_POST directly for JSON data — Security::post() HTML-encodes quotes
        $rawData = $_POST['courierData'] ?? null;

        if ($rawData !== null) {
            $couriers = json_decode($rawData, true);

            if (!$couriers || empty($couriers)) {
                echo "No couriers to export";
                $this->terminate();
            }
        }

        $format = $_POST['format'] ?? 'pdf';

        switch ($format) {
            case 'pdf':
                ExportService::exportToPDF($couriers, 'Couriers Export', 'couriers_export.pdf');
                break;  // also add missing break statements
            case 'excel':
                ExportService::exportToExcel($couriers, 'couriers_export.xlsx');
                break;
            case 'csv':
                ExportService::exportToCSV($couriers, 'couriers_export.csv');
                break;
            default:
                echo "Invalid export format";
                $this->terminate();
        }
    }

    public function updateLocation(): void
    {
        $this->setHeader('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'courier') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            $this->terminate();
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
        $this->terminate();
    }

    public function getLocation(): void
    {
        $this->setHeader('Content-Type: application/json');

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
        $this->terminate();
    }

    public function startTracking(): void
    {
        if ($_SESSION['user']['role'] !== 'courier') {
            $this->redirect(INSTALL_URL);
        }

        $this->view($this->layout, [
            'active_orders' => $this->getActiveOrders($_SESSION['user']['id'])
        ]);
    }

    private function getActiveOrders($courierId): array
    {
        $orderModel = new Order();
        return $orderModel->getAll([
                    'courier_id' => $courierId,
                    'status' => 'shipped'
        ]);
    }
}
