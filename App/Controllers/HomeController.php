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
        // Check if user is logged in
        $isLoggedIn = !empty($_SESSION['user']);

        // Common data for all cases
        $data = [
            'currency' => $this->settings['currency_code'],
            'isLoggedIn' => $isLoggedIn
        ];

        // If not logged in, just render the view with minimal data
        if (!$isLoggedIn) {
            $this->view($this->layout, $data);
            return;
        }

        // User is logged in, proceed with role-specific data
        $userModel = new User();
        $orderModel = new Order();
        $productModel = new Product();
        $notificationModel = new Notification();

        $userRole = $_SESSION['user']['role'];
        $userId = $_SESSION['user']['id'];

        // Add user role to data
        $data['user_role'] = $userRole;

        // Role-specific data fetching
        switch ($userRole) {
            case 'admin':
            case 'root':
                // Admin Dashboard Statistics
                $data['total_orders'] = $orderModel->countAll();
                $data['pending_orders'] = $orderModel->countAll(['status' => 'pending']);
                $data['completed_orders'] = $orderModel->countAll(['status' => 'delivered']);
                $data['total_users'] = $userModel->countAll();
                $data['total_products'] = $productModel->countAll();

                // Recent orders for admin
                $data['recent_orders'] = $orderModel->getAll([], 'created_at DESC', 5);
                foreach ($data['recent_orders'] as &$order) {
                    $order['customer_name'] = $userModel->get($order['user_id'])['name'] ?? 'Unknown';
                    $order['formatted_total'] = \Utility::getDisplayableAmount($order['total_amount']);
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
                $data['completed_orders'] = $orderModel->countAll(['user_id' => $userId, 'status' => 'delivered']);

                // Recent orders for user
                $data['recent_orders'] = $orderModel->getAll(
                        ['user_id' => $userId],
                        'created_at DESC',
                        5
                );
                foreach ($data['recent_orders'] as &$order) {
                    $order['formatted_total'] = \Utility::getDisplayableAmount($order['total_amount']);
                    $order['status_text'] = \Utility::$order_status[$order['status']];
                }
                break;
        }

        // Common data for all roles when logged in
        $data['notifications'] = $notificationModel->getAll(
                ['user_id' => $userId, 'is_seen' => 0],
                'created_at DESC',
                5
        );

        $this->view($this->layout, $data);
    }

    public function getSalesData() {
        $orderModel = new Order();

        // Last 30 days sales data
        $salesData = [];
        $now = time();

        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days", $now));
            $start = strtotime($date . ' 00:00:00');
            $end = strtotime($date . ' 23:59:59');

            $sales = $orderModel->getAll([
                "created_at >= $start AND 1 " => 1,
                "created_at <= $end AND 1 " => 1,
                'status IN (\'delivered\', \'shipped\') AND 1 ' => 1
            ]);

            $total = 0;
            if (is_array($sales) && !empty($sales)) {
                foreach ($sales as $sale) {
                    $total += $sale['total_amount'];
                }
            }

            $salesData[] = [
                'date' => date('M j', $start),
                'total' => $total
            ];
        }

        return $salesData;
    }
}
