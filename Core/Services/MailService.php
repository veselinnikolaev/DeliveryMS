<?php

declare(strict_types=1);

namespace Core\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use RuntimeException;

class MailService
{
    private PHPMailer $phpmailer;
    private string $host;
    private string $port;
    private string $username;
    private string $password;

    public function __construct()
    {
        $this->host = MAIL_HOST;
        $this->port = MAIL_PORT;
        $this->username = MAIL_USERNAME;
        $this->password = MAIL_PASSWORD;
    }

    /**
     * Check mail server connection
     *
     * @param string $host SMTP host
     * @param string $port SMTP port
     * @param string $username SMTP username
     * @param string $password SMTP password
     * @return array Connection status and message
     */
    public function checkConnection(string $host, string $port, string $username, string $password): array
    {
        $this->phpmailer = new PHPMailer(true);

        try {
            $this->phpmailer->isSMTP();
            $this->phpmailer->Host = $host;
            $this->phpmailer->SMTPAuth = true;
            $this->phpmailer->Port = $port;
            $this->phpmailer->Username = $username;
            $this->phpmailer->Password = $password;
            $this->phpmailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->phpmailer->CharSet = 'UTF-8';

            $this->phpmailer->setFrom('test@test.com', 'Testing Connection');
            $this->phpmailer->addAddress('test@test.com');
            $this->phpmailer->Subject = 'Testing Connection';
            $this->phpmailer->Body = 'Testing Connection';
            $this->phpmailer->isHTML(false);

            $this->phpmailer->send();

            return [
                'status' => true,
                'message' => 'Connection successful!'
            ];
        } catch (Exception) {
            return [
                'status' => false,
                'message' => 'Connection failed.'
            ];
        }
    }

    /**
     * Send email
     *
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param string $from Sender email address
     * @return bool|string True on success, error message on failure
     */
    public function sendMail(string $to, string $subject, string $body, string $from = 'delivery@system.com'): bool|string
    {
        $this->connect();

        try {
            $this->phpmailer->setFrom($from, 'DeliveryMS');
            $this->phpmailer->addAddress($to);
            $this->phpmailer->Subject = $subject;
            $this->phpmailer->Body = $body;
            $this->phpmailer->isHTML(true);

            return $this->phpmailer->send();
        } catch (Exception) {
            return "Mail Error: " . $this->phpmailer->ErrorInfo;
        }
    }

    /**
     * Connect to SMTP server using configured credentials
     *
     * @return void
     * @throws RuntimeException If connection fails
     */
    private function connect(): void
    {
        $this->phpmailer = new PHPMailer(true);

        try {
            $this->phpmailer->isSMTP();
            $this->phpmailer->Host = $this->host;
            $this->phpmailer->SMTPAuth = true;
            $this->phpmailer->Port = $this->port;
            $this->phpmailer->Username = $this->username;
            $this->phpmailer->Password = $this->password;
            $this->phpmailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->phpmailer->CharSet = 'UTF-8';
        } catch (Exception $e) {
            error_log("Mailer connection error: " . $e->getMessage());
            throw new RuntimeException("Mailer Error: " . $this->phpmailer->ErrorInfo, 0, $e);
        }
    }
}
