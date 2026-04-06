<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$basePath = str_replace('/app', '', APPROOT);
require_once $basePath . '/libraries/phpmailer/src/Exception.php';
require_once $basePath . '/libraries/phpmailer/src/PHPMailer.php';
require_once $basePath . '/libraries/phpmailer/src/SMTP.php';
class Mailer {

    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);

        // SMTP configuration
        $this->mail->isSMTP();
        $this->mail->Host       = MAIL_HOST;
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = MAIL_USERNAME;
        $this->mail->Password   = MAIL_PASSWORD;
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port       = MAIL_PORT;

        // Sender
        $this->mail->setFrom(MAIL_FROM, MAIL_NAME);
        $this->mail->isHTML(true);
    }

    public function sendNewUserCredentials($name, $email, $password, $role) {
        try {
            $this->mail->addAddress($email, $name);
            $this->mail->Subject = 'Your SkillXchange Account Details';

            $roleFormatted = ucwords(str_replace('_', ' ', $role));

            $this->mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #658396;'>Welcome to SkillXchange!</h2>
                    <p>Hi <strong>{$name}</strong>,</p>
                    <p>Your account has been created. Here are your login details:</p>
                    <div style='background: #d5eaf6; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <p style='margin:0 0 10px 0;'><strong>Email:</strong> {$email}</p>
                        <p style='margin:0 0 10px 0;'><strong>Password:</strong> {$password}</p>
                        <p style='margin:0;'><strong>Role:</strong> {$roleFormatted}</p>
                    </div>
                    <p>You can login at: <a href='" . URLROOT . "/auth/signin' style='color:#658396;'>" . URLROOT . "/auth/signin</a></p>
                    <p>Please change your password after logging in.</p>
                    <br>
                    <p>Regards,<br><strong>SkillXchange Team</strong></p>
                </div>
            ";

            $this->mail->AltBody = "Hi {$name}, your account has been created.\n\nEmail: {$email}\nPassword: {$password}\nRole: {$roleFormatted}\n\nLogin at: " . URLROOT . "/auth/signin";

            $this->mail->send();
            return true;

        } catch (Exception $e) {
            error_log('Mailer Error: ' . $this->mail->ErrorInfo);
            return false;
        }
    }
}