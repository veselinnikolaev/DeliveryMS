<?php

namespace App\Helpers\mailer;

require 'App/Helpers/mailer/PHPMailer/src/Exception.php';
require 'App/Helpers/mailer/PHPMailer/src/PHPMailer.php';
require 'App/Helpers/mailer/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {

    private $phpmailer;
    private $host = '';
    private $port = '';
    private $username = '';
    private $password = '';

    public function __construct() {
        $this->host = MAIL_HOST;
        $this->port = MAIL_PORT;
        $this->username = MAIL_USERNAME;
        $this->password = MAIL_PASSWORD;

        $this->initMailer();
    }

    private function initMailer() {
        $this->phpmailer = new PHPMailer(true);

        try {
            $this->phpmailer->isSMTP();
            $this->phpmailer->Host = MAIL_HOST;
            $this->phpmailer->SMTPAuth = true;
            $this->phpmailer->Port = MAIL_PORT;
            $this->phpmailer->Username = MAIL_USERNAME;
            $this->phpmailer->Password = MAIL_PASSWORD;
            $this->phpmailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->phpmailer->CharSet = 'UTF-8';
        } catch (Exception $e) {
            die("Mailer Error: " . $this->phpmailer->ErrorInfo);
        }
    }

    public function sendMail($to, $subject, $body, $from = 'delivery@system.com') {
        try {
            $this->phpmailer->setFrom($from, 'DeliveryMS');
            $this->phpmailer->addAddress($to);
            $this->phpmailer->Subject = $subject;
            $this->phpmailer->Body = $body;
            $this->phpmailer->isHTML(true);

            return $this->phpmailer->send();
        } catch (Exception $e) {
            return "Mail Error: " . $this->phpmailer->ErrorInfo;
        }
    }
}
