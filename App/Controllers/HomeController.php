<?php

namespace App\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Notification;
use Core\Controller;

class HomeController extends Controller {

    var $layout = 'admin';
    var $settings;

    public function __construct() {
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

    public function index() {
        if (empty($_SESSION['user'])) {
            header("Location: " . INSTALL_URL . "?controller=Auth&action=login");
            exit;
        }

        $userModel = new User();
        $orderModel = new Order();
        $productModel = new Product();
        $notificationModel = new Notification();

        $userRole = $_SESSION['user']['role'];
        $userId = $_SESSION['user']['id'];

        // Common data for all roles
        $data = [
            'currency' => $this->settings['currency_code'],
            'user_role' => $userRole
        ];

        // Role-specific data fetching
        switch ($userRole) {
            case 'admin':
            case 'root':
                // Admin Dashboard Statistics
                $data['total_orders'] = $orderModel->countAll();
                $data['pending_orders'] = $orderModel->countAll(['status' => 'pending']);
                $data['completed_orders'] = $orderModel->countAll(['status' => 'completed']);
                $data['total_users'] = $userModel->countAll();
                $data['total_products'] = $productModel->countAll();

                // Recent orders for admin
                $data['recent_orders'] = $orderModel->getAll([], 'created_at DESC', 5);
                foreach ($data['recent_orders'] as &$order) {
                    $order['customer_name'] = $userModel->get($order['user_id'])['name'] ?? 'Unknown';
                    $order['formatted_total'] = $this->settings['currency_code'] . number_format($order['total_amount'], 2);
                }

                // Sales data for charts
                $data['sales_data'] = $this->getSalesData();
                break;

            case 'courier':
                // Courier Dashboard Statistics
                $data['assigned_orders'] = $orderModel->countAll(['courier_id' => $userId]);
                $data['delivered_orders'] = $orderModel->countAll(['courier_id' => $userId, 'status' => 'delivered']);
                $data['pending_deliveries'] = $orderModel->countAll(['courier_id' => $userId, 'status' => 'shipped']);

                // Recent deliveries for courier
                $data['recent_deliveries'] = $orderModel->getAll(
                        ['courier_id' => $userId],
                        'delivery_date DESC',
                        5
                );
                foreach ($data['recent_deliveries'] as &$order) {
                    $order['customer_name'] = $userModel->get($order['user_id'])['name'] ?? 'Unknown';
                    $order['address_short'] = substr($order['address'], 0, 30) . '...';
                }
                break;

            case 'user':
                // User Dashboard Statistics
                $data['my_orders'] = $orderModel->countAll(['user_id' => $userId]);
                $data['pending_orders'] = $orderModel->countAll(['user_id' => $userId, 'status' => 'pending']);
                $data['completed_orders'] = $orderModel->countAll(['user_id' => $userId, 'status' => 'completed']);

                // Recent orders for user
                $data['recent_orders'] = $orderModel->getAll(
                        ['user_id' => $userId],
                        'created_at DESC',
                        5
                );
                foreach ($data['recent_orders'] as &$order) {
                    $order['formatted_total'] = $this->settings['currency_code'] . number_format($order['total_amount'], 2);
                    $order['status_text'] = \Utility::statuses[$order['status']];
                }
                break;
        }

        // Common data for all roles
        $data['notifications'] = $notificationModel->getAll(
                ['user_id' => $userId, 'is_seen' => 0],
                'created_at DESC',
                5
        );

        $this->view($this->layout, $data);
    }

    private function getSalesData() {
        $orderModel = new Order();

        // Last 30 days sales data
        $salesData = [];
        $now = time();

        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days", $now));
            $start = strtotime($date . ' 00:00:00');
            $end = strtotime($date . ' 23:59:59');

            $sales = $orderModel->getAll([
                'created_at >= ' => $start,
                'created_at <= ' => $end,
                'status' => 'completed'
            ]);

            $total = 0;
            foreach ($sales as $sale) {
                $total += $sale['total_amount'];
            }

            $salesData[] = [
                'date' => date('M j', $start),
                'total' => $total
            ];
        }

        return $salesData;
    }
}
