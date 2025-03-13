<?php

namespace App\Controllers;

use Models;
use Core;
use Core\View;
use Core\Controller;

class UserController extends Controller {

    var $layout = 'admin';

    public function __construct() {
        if (empty($_SESSION['user'])) {
            header("Location: " . INSTALL_URL . "?controller=Auth&action=login", true, 301);
            exit;
        }
        if ($_SESSION['user']['role'] == 'user') {
            header("Location: " . INSTALL_URL, true, 301);
            exit;
        }
    }

    function list($layout = 'admin') {
        $userModel = new \App\Models\User();

        $opts = array();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['name'])) {
                $opts["name LIKE '%" . $_POST['name'] . "%' AND 1 "] = "1";
            }
            if (!empty($_POST['phone_number'])) {
                $opts["phone_number LIKE '%" . $_POST['phone_number'] . "%' AND 1 "] = "1";
            }
            if (!empty($_POST['email'])) {
                $opts["email LIKE '%" . $_POST['email'] . "%' AND 1 "] = "1";
            }
            if (!empty($_POST['role'])) {
                $opts["role LIKE '%" . $_POST['role'] . "%' AND 1 "] = "1";
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

        // Извличане на всички записи от таблицата gallery
        $users = $userModel->getAll($opts);

        // Прехвърляне на данни към изгледа
        $this->view($layout, ['users' => $users]);
    }

    function filter() {
        $this->list('ajax');
    }

    function print() {
        if (isset($_POST['userData'])) {
            // Decode the JSON data
            $users = json_decode($_POST['userData'], true);

            if (!$users || empty($users)) {
                echo "No users to print";
                exit;
            }
        }

        $this->view('ajax', ['users' => $users]);
    }

    public function changeRole() {
        $userModel = new \App\Models\User();

        if (!empty($_POST['id']) && !empty($_POST['role'])) {
            $role = $_POST['role'];

            if (in_array($role, ['user', 'admin'])) {
                $userModel->update($_POST);
            }
        }

        // Return refreshed user list
        $users = $userModel->getAll();
        $this->view('ajax', ['users' => $users]);
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
                $_POST['role'] = 'user';

                if ($userModel->save($_POST)) {
                    header("Location: " . INSTALL_URL . "?controller=User&action=list", true, 301);
                    exit;
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

    function delete() {
        $userModel = new \App\Models\User();

        if (!empty($_POST['id'])) {
            $userModel->delete($_POST['id']);
            if ($_POST['id'] == $_SESSION['user']['id']) {
                session_destroy();
            }
        }

        $users = $userModel->getAll();
        $this->view('ajax', ['users' => $users]);
    }

    function bulkDelete() {
        $userModel = new \App\Models\User();

        if (!empty($_POST['ids']) && is_array($_POST['ids'])) {
            $userIds = $_POST['ids'];

            $inUserIds = implode(', ', $userIds);
            $userModel->deleteBy(["id IN ($inUserIds) AND 1 " => '1']);
            if (in_array($_SESSION['user']['id'], $userIds)) {
                session_destroy();
            }
        }

        $users = $userModel->getAll();
        $this->view('ajax', ['users' => $users]);
    }

    function edit() {
        $userModel = new \App\Models\User();

        $id = isset($_POST['id']) ? $_POST['id'] : (isset($_GET['id']) ? $_GET['id'] : null);
        $arr = $userModel->get($id);

        // Check if the form has been submitted
        if (!empty($_POST['id'])) {
            if ($userModel->update($_POST)) {
                // Redirect to the list of users on successful creation
                header("Location: " . INSTALL_URL . "?controller=User&action=list", true, 301);
                exit;
            }

            // If saving fails, set an error message
            $arr['error_message'] = "Failed to create the courier. Please try again.";
        }

        // Load the view and pass the data to it
        $this->view($this->layout, $arr);
    }

    function profile() {
        $userModel = new \App\Models\User();

        $user = $userModel->get($_GET['id']);

        $this->view($this->layout, ['user' => $user]);
    }

    function uploadProfilePicture() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
            $user_id = $_POST['user_id']; // Get user ID
            $userModel = new \App\Models\User();
            $fileName = basename($_FILES["profile_picture"]["name"]);
            $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
            $allowedTypes = ["jpg", "jpeg", "png", "gif"];

            if (!in_array(strtolower($fileExt), $allowedTypes)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid file format!']);
                exit;
            }

            require 'App\Helpers\uploader\src\class.upload.php';

            $handle = new \Verot\Upload\Upload($_FILES['profile_picture']);
            if ($handle->uploaded) {
                $handle->file_new_name_body = 'profile_' . $user_id . '_' . uniqid();
                $handle->image_resize = true;
                $handle->image_x = 300;
                $handle->image_ratio_y = true;
                $upload_path = 'web/upload/';
                $handle->process($upload_path);

                if ($handle->processed) {
                    $photoPath = $upload_path . $handle->file_dst_name;
                    $handle->clean();

                    // ✅ Update user photo in database
                    $userModel->update(['id' => $user_id, 'photo_path' => $photoPath]);

                    echo json_encode([
                        'status' => 'success',
                        'photo_path' => $photoPath
                    ]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => $handle->error]);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'File upload failed.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
        }
    }

    function editPassword() {
        $userModel = new \App\Models\User();
        $id = isset($_POST['id']) ? $_POST['id'] : (isset($_GET['id']) ? $_GET['id'] : null);

        if (!empty($_POST['id'])) {
            $newPassword = $_POST['password'];
            $repeatNewPassword = $_POST['repeat_password'];

            if ($newPassword != $repeatNewPassword) {
                $errorMessage = 'Passwords do NOT match';
            }

            if (!isset($errorMessage)) {
                if ($userModel->update(['id' => $id, 'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT)])) {
                    header("Location: " . INSTALL_URL . "?controller=User&action=profile&id=$id", true, 301);
                    exit;
                }
                $errorMessage = 'Error updating password';
            }
        }

        $this->view($this->layout, ['id' => $id, 'error_message' => $errorMessage ?? null]);
    }

    function export() {
        // Check if userData is provided
        if (isset($_POST['userData'])) {
            // Decode the JSON data
            $users = json_decode($_POST['userData'], true);

            if (!$users || empty($users)) {
                echo "No users to export";
                exit;
            }
        }

        $format = isset($_POST['format']) ? $_POST['format'] : 'pdf';

        // Export based on format
        switch ($format) {
            case 'pdf':
                $this->exportAsPDF($users);
                break;
            case 'excel':
                $this->exportAsExcel($users);
                break;
            case 'csv':
                $this->exportAsCSV($users);
                break;
            default:
                echo "Invalid export format";
                exit;
        }
    }

    private function exportAsPDF($users) {
        if (ob_get_level()) {
            ob_end_clean();
        }
        require_once(__DIR__ . '/../Helpers/export/tcpdf/tcpdf.php');

        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8');
        $pdf->SetCreator('Your App');
        $pdf->SetTitle('Users Export');
        $pdf->SetHeaderData('', 0, 'Users List', '');
        $pdf->setHeaderFont(Array('helvetica', '', 12));
        $pdf->setFooterFont(Array('helvetica', '', 10));
        $pdf->SetDefaultMonospacedFont('courier');
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);

        $pdf->AddPage();

        // Generate HTML table with dynamic headers
        $html = $this->generateDynamicUserTable($users);
        $pdf->writeHTML($html, true, false, true, false, '');

        // Output PDF
        $pdf->Output('users_export.pdf', 'D');
        exit;
    }

    private function generateDynamicUserTable($users) {
        // Start HTML table
        $html = '<table border="1" cellpadding="5">
<thead>
    <tr>';

        // If we have users, use their keys as headers
        if (!empty($users) && is_array($users[0])) {
            $headers = array_keys($users[0]);

            // Add headers to table
            foreach ($headers as $header) {
                $displayHeader = ucwords(str_replace('_', ' ', $header));
                $html .= '<th>' . $displayHeader . '</th>';
            }

            $html .= '</tr>
    </thead>
    <tbody>';

            // Add user data
            foreach ($users as $user) {
                $html .= '<tr>';
                foreach ($user as $key => $value) {
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
            $html .= '<th>No Data Available</th></tr></thead><tbody><tr><td>No users found</td></tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }

    private function exportAsExcel($users) {
        // Include SimpleXLSXGen
        require(__DIR__ . '/../Helpers/export/simplexlsxgen/src/SimpleXLSXGen.php');

        // Prepare data
        $data = [];

        // First user in array determines headers
        if (!empty($users) && is_array($users[0])) {
            // Use keys from first user for headers, ensuring proper capitalization
            $headers = array_keys($users[0]);
            $headerRow = [];

            foreach ($headers as $header) {
                // Convert user_id to User ID, etc.
                $headerRow[] = ucwords(str_replace('_', ' ', $header));
            }

            $data[] = $headerRow;

            // Add users
            foreach ($users as $user) {
                $row = [];
                foreach ($user as $value) {
                    // Handle empty values
                    $row[] = (empty($value) && $value !== 0) ? 'N/A' : $value;
                }
                $data[] = $row;
            }
        } else {
            // Fallback for no data
            $data[] = ['No Data Available'];
            $data[] = ['No users found'];
        }

        // Create and send file
        \Shuchkin\SimpleXLSXGen::fromArray($data)->downloadAs('users_export.xlsx');
        exit;
    }

    private function exportAsCSV($users) {
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="users_export.csv"');

        // Open output stream
        $output = fopen('php://output', 'w');

        // Determine headers dynamically from the first user
        if (!empty($users) && is_array($users[0])) {
            $headers = array_keys($users[0]);
            // Convert keys to readable headers (e.g., user_id to User ID)
            $readableHeaders = array_map(function ($header) {
                return ucwords(str_replace('_', ' ', $header));
            }, $headers);

            // Add headers
            fputcsv($output, $readableHeaders);

            // Add data using the actual keys from the data
            foreach ($users as $user) {
                $row = [];
                foreach ($user as $value) {
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
}
