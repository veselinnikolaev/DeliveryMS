<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Setting;
use App\Models\Notification;
use App\Models\User;
use Core;
use Core\View;
use Core\Controller;

class SettingsController extends Controller
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

    public function index(): void
    {
        $settingModel = new Setting();
        $notificationModel = new Notification();
        $userModel = new User();

        if (!empty($this->post('settings'))) {
            $updateData = [];
            $criticalChanges = [];
            $currentSettings = [];

            // Get current settings for comparison
            $existingSettings = $settingModel->getAll();
            foreach ($existingSettings as $setting) {
                $currentSettings[$setting['key']] = $setting['value'];
            }

            foreach ($this->post('settings') as $key => $value) {
                $updateData[] = [
                    'key' => $key,
                    'value' => $value
                ];

                // Track critical setting changes
                if (isset($currentSettings[$key]) && $currentSettings[$key] !== $value) {
                    switch ($key) {
                        case 'email_sending':
                            $criticalChanges[] = "Email sending has been " . $value;
                            break;
                        case 'tax_rate':
                            $criticalChanges[] = "Tax rate changed from {$currentSettings[$key]}% to {$value}%";
                            break;
                        case 'shipping_rate':
                            $criticalChanges[] = "Shipping rate changed from {$currentSettings[$key]}% to {$value}%";
                            break;
                        case 'currency_code':
                            $criticalChanges[] = "Currency changed from {$currentSettings[$key]} to {$value}";
                            break;
                    }
                }
            }

            if ($settingModel->updateBatch($updateData, 'key')) {
                // Notify all administrators about the changes
                if (!empty($criticalChanges)) {
                    $adminUsers = $userModel->getAll(['role' => 'admin']);

                    foreach ($adminUsers as $admin) {
                        // Create a detailed notification for critical changes
                        $notificationModel->save([
                            'user_id' => $admin['id'],
                            'message' => "Important system settings changed:\n" . implode("\n", $criticalChanges) .
                                       "\nChanged by: " . $_SESSION['user']['name'],
                            'link' => INSTALL_URL . "?controller=Settings&action=index",
                            'created_at' => time()
                        ]);
                    }
                }

                // Log the change in notifications for audit trail
                $notificationModel->save([
                    'user_id' => $_SESSION['user']['id'],
                    'message' => "System settings updated successfully",
                    'link' => INSTALL_URL . "?controller=Settings&action=index",
                    'created_at' => time()
                ]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Settings updated successfully!',
                    'changes' => $criticalChanges
                ]);
            } else {
                // Notify about failure
                $notificationModel->save([
                    'user_id' => $_SESSION['user']['id'],
                    'message' => "Failed to update system settings",
                    'link' => INSTALL_URL . "?controller=Settings&action=index",
                    'created_at' => time()
                ]);

                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to update settings.'
                ]);
            }
            $this->terminate();
        }

        $settings = $settingModel->getAll();
        $this->view($this->layout, ['settings' => $settings]);
    }
}
