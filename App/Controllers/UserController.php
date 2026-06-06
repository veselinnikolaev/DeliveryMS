<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\Notification;
use Core\Security;
use Core\Services\ExportService;
use Core\Controller;
use RuntimeException;

class UserController extends Controller
{
    public string $layout = 'admin';

    public function __construct()
    {
        parent::__construct();
        if (empty($_SESSION['user'])) {
            $this->redirect(INSTALL_URL . "?controller=Auth&action=login");
        }
    }

    public function list($layout = 'admin'): void
    {
        if ($_SESSION['user']['role'] == 'user') {
            $this->redirect(INSTALL_URL);
        }

        $userModel = new User();

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
            if (!empty($this->post('roles')) && is_array($this->post('roles'))) {
                $roles = $this->post('roles');
                $opts['role'] = $roles;
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

        // Retrieve all records from the users table
        $users = $userModel->getAll($opts);

        // Pass data to the view
        $this->view($layout, ['users' => $users]);
    }

    public function filter(): void
    {
        if ($_SESSION['user']['role'] == 'user') {
            $this->redirect(INSTALL_URL);
        }

        $this->list('ajax');
    }

    public function print(): void
    {
        if ($_SESSION['user']['role'] == 'user') {
            $this->redirect(INSTALL_URL);
        }

        $users = [];

        $userData = $this->post('userData');
        if (isset($userData)) {
            // Decode the JSON data
            $users = json_decode($userData, true);

            if (empty($users)) {
                echo "No users to print";
                $this->terminate();
            }
        }

        $this->view('ajax', ['users' => $users]);
    }

    public function changeRole(): void
    {
        if ($_SESSION['user']['role'] == 'user') {
            $this->redirect(INSTALL_URL);
        }

        $userModel = new User();

        if (!empty($this->post('id')) && !empty($this->post('role'))) {
            $userId = Security::int($this->post('id'));
            $role = $this->post('role');

            if (in_array($role, ['user', 'admin', 'courier'])) {
                $postData = $this->post();
                $userModel->update($postData);

                $notificationModel = new Notification();

                // Notify the user whose role changed
                $notificationModel->save([
                    'user_id' => $userId,
                    'message' => "Your account role has been changed to: $role",
                    'link' => INSTALL_URL . "?controller=User&action=profile&id=$userId",
                    'created_at' => time()
                ]);

                // Fetch user data for notification
                $user = $userModel->get($userId);

                // Notify admins
                $this->notifyAdmins(
                    "User role changed: {$user['name']} is now a $role",
                    INSTALL_URL . "?controller=User&action=profile&id=$userId"
                );
            }
        }

        // Return refreshed user list
        $users = $userModel->getAll();
        $this->view('ajax', ['users' => $users]);
    }

    public function create(): void
    {
        if ($_SESSION['user']['role'] == 'user') {
            $this->redirect(INSTALL_URL);
        }

        // Create an instance of the User model
        $userModel = new User();

        // Check if the form has been submitted
        if (!empty($this->post('send'))) {
            if ($userModel->existsBy(['email' => $this->post('email')])) {
                $error_message = "User with this email already exists.";
            } elseif ($this->post('password') !== $this->post('repeat_password')) {
                $error_message = "Passwords do not match.";
            } else {
                $postData = $this->post();
                $postData['password_hash'] = password_hash($this->post('password'), PASSWORD_DEFAULT);
                $postData['role'] = 'user';

                if ($userModel->save($postData)) {
                    $this->redirect($_SESSION['previous_url']);
                } else {
                    $error_message = "Failed to register. Please try again.";
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
            $userId = Security::int($this->post('id'));
            $userModel->delete($userId);
            if ($userId == $_SESSION['user']['id']) {
                session_destroy();
            }
        }

        $users = $userModel->getAll();
        $this->view('ajax', ['users' => $users]);
    }

    public function bulkDelete(): void
    {
        if ($_SESSION['user']['role'] == 'user') {
            $this->redirect(INSTALL_URL);
        }

        $userModel = new User();

        if (!empty($this->post('ids')) && is_array($this->post('ids'))) {
            $userIds = $this->post('ids');

            $userModel->deleteBy(['id' => $userIds]);
            if (in_array($_SESSION['user']['id'], $userIds)) {
                session_destroy();
            }
        }

        $users = $userModel->getAll();
        $this->view('ajax', ['users' => $users]);
    }

    public function edit(): void
    {
        $userModel = new User();

        $id = $this->post('id') ?? $this->get('id') ?? null;
        $arr = $userModel->get($id);

        // Check if the form has been submitted
        if (!empty($this->post('id'))) {
            $postData = $this->post();
            if ($userModel->update($postData)) {
                // Redirect to the list of users on successful creation
                $this->redirect($_SESSION['previous_url']);
            }

            // If saving fails, set an error message
            $arr['error_message'] = "Failed to update the user. Please try again.";
        }

        // Load the view and pass the data to it
        $this->view($this->layout, $arr);
    }

    public function profile(): void
    {
        if ($_SESSION['user']['role'] == 'user' && $_SESSION['user']['id'] != $this->get('id')) {
            $this->redirect(INSTALL_URL);
        }

        $userModel = new User();

        $user = $userModel->get(Security::int($this->get('id')));

        $this->view($this->layout, ['user' => $user]);
    }

    public function uploadProfilePicture(): void
    {
        $this->setHeader('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['profile_picture'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
            $this->terminate();
        }

        $user_id = Security::int($this->post('user_id'));
        $file = $_FILES['profile_picture'];
        $fileExt = strtolower(pathinfo(basename($file['name']), PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($fileExt, $allowedTypes)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid file format!']);
            $this->terminate();
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['status' => 'error', 'message' => 'File upload failed.']);
            $this->terminate();
        }

        $newFileName = 'profile_' . $user_id . '_' . uniqid() . '.' . $fileExt;
        $destination = UPLOAD_PATH . $newFileName;

        // Resize using GD
        $src = match ($fileExt) {
            'jpg', 'jpeg' => imagecreatefromjpeg($file['tmp_name']),
            'png'         => imagecreatefrompng($file['tmp_name']),
            'gif'         => imagecreatefromgif($file['tmp_name']),
            default       => throw new RuntimeException("Unsupported image type"),
        };

        [$origW, $origH] = getimagesize($file['tmp_name']);
        $newW = 300;
        $newH = (int) ($origH * $newW / $origW);
        $dst = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        match ($fileExt) {
            'jpg', 'jpeg' => imagejpeg(imagecreatefromjpeg($file['tmp_name']), $destination, 90),
            'png'         => imagepng(imagecreatefrompng($file['tmp_name']), $destination),
            'gif'         => imagegif(imagecreatefromgif($file['tmp_name']), $destination),
            default       => throw new RuntimeException("Unsupported image type"),
        };

        imagedestroy($src);
        imagedestroy($dst);

        $userModel = new User();
        $userModel->update(['id' => $user_id, 'photo_path' => $destination]);
        $_SESSION['user']['photo_path'] = $destination;

        echo json_encode(['status' => 'success', 'photo_path' => $destination]);
    }
    public function editPassword(): void
    {
        $id = $this->post('id') ?? $this->get('id') ?? null;

        if ($_SESSION['user']['role'] == 'user' && $_SESSION['user']['id'] != $id) {
            $this->redirect(INSTALL_URL);
        }

        $userModel = new User();

        if (!empty($this->post('id'))) {
            $newPassword = $this->post('password');
            $repeatNewPassword = $this->post('repeat_password');

            if ($newPassword != $repeatNewPassword) {
                $errorMessage = 'Passwords do NOT match';
            }

            if (!isset($errorMessage)) {
                if ($userModel->update(['id' => $id, 'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT)])) {
                    $notificationModel = new Notification();
                    $notificationModel->save([
                        'user_id' => $id,
                        'message' => "Your password has been changed successfully",
                        'link' => INSTALL_URL . "?controller=User&action=profile&id=$id",
                        'created_at' => time()
                    ]);

                    // Notify admins if an admin changed someone else's password
                    if ($_SESSION['user']['role'] == 'admin' && $_SESSION['user']['id'] != $id) {
                        $user = $userModel->get($id);
                        $this->notifyAdmins(
                            "Password changed for user: {$user['name']} by admin: {$_SESSION['user']['name']}",
                            INSTALL_URL . "?controller=User&action=profile&id=$id"
                        );
                    }

                    $this->redirect(INSTALL_URL . "?controller=User&action=profile&id=$id");
                }
                $errorMessage = 'Error updating password';
            }
        }

        $this->view($this->layout, ['id' => $id, 'error_message' => $errorMessage ?? null]);
    }

    public function export(): void
    {
        $users = [];
        // Check if userData is provided
        $userData = $this->post('userData');
        if (isset($userData)) {
            // Decode the JSON data
            $users = json_decode($userData, true);

            if (empty($users)) {
                echo "No users to export";
                $this->terminate();
            }
        }

        $format = $this->post('format') ?? 'pdf';

        // Export based on format
        switch ($format) {
            case 'pdf':
                ExportService::exportToPDF($users, 'Users Export', 'users_export.pdf');
                break;
            case 'excel':
                ExportService::exportToExcel($users, 'users_export.xlsx');
                break;
            case 'csv':
                ExportService::exportToCSV($users, 'users_export.csv');
                break;
            default:
                echo "Invalid export format";
                $this->terminate();
        }
    }

    private function notifyAdmins($message, $link = null): void
    {
        $userModel = new User();
        $notificationModel = new Notification();

        $admins = $userModel->getAll(['role' => 'admin']);
        foreach ($admins as $admin) {
            $notificationModel->save([
                'user_id' => $admin['id'],
                'message' => $message,
                'link' => $link,
                'created_at' => time()
            ]);
        }
    }
}
