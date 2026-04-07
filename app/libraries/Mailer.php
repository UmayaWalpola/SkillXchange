<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/phpmailer/src/Exception.php';
require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/src/SMTP.php';
class Mailer {

    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);

        // If SMTP credentials are not configured, don't attempt to send.
        // This avoids confusing timeouts/errors in local setups.
        if (!defined('MAIL_HOST') || !defined('MAIL_PORT') || !defined('MAIL_USERNAME') || !defined('MAIL_PASSWORD')
            || empty(MAIL_HOST) || empty(MAIL_PORT) || empty(MAIL_USERNAME) || empty(MAIL_PASSWORD)) {
            $this->mail = null;
            return;
        }

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
        if ($this->mail === null) {
            error_log('Mailer Error: Mail is not configured (MAIL_* constants missing/empty)');
            return false;
        }
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

    public function sendUserUpdatedNotification($name, $email, $role, $newPassword = '') {
        if ($this->mail === null) {
            error_log('Mailer Error: Mail is not configured (MAIL_* constants missing/empty)');
            return false;
        }

        try {
            $this->mail->addAddress($email, $name);
            $this->mail->Subject = 'Your SkillXchange Account Was Updated';

            $roleFormatted = ucwords(str_replace('_', ' ', $role));

            $passwordSection = '';
            $passwordAlt = '';
            if (!empty($newPassword)) {
                $passwordSection = "<p style='margin:0 0 10px 0;'><strong>New Password:</strong> {$newPassword}</p>";
                $passwordAlt = "\nNew Password: {$newPassword}";
            }

            $this->mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #658396;'>Account Updated</h2>
                    <p>Hi <strong>{$name}</strong>,</p>
                    <p>Your SkillXchange account details were updated by a manager.</p>
                    <div style='background: #d5eaf6; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <p style='margin:0 0 10px 0;'><strong>Email:</strong> {$email}</p>
                        {$passwordSection}
                        <p style='margin:0;'><strong>Role:</strong> {$roleFormatted}</p>
                    </div>
                    <p>You can login at: <a href='" . URLROOT . "/auth/signin' style='color:#658396;'>" . URLROOT . "/auth/signin</a></p>
                    <br>
                    <p>Regards,<br><strong>SkillXchange Team</strong></p>
                </div>
            ";

            $this->mail->AltBody = "Hi {$name}, your account details were updated.\n\nEmail: {$email}{$passwordAlt}\nRole: {$roleFormatted}\n\nLogin at: " . URLROOT . "/auth/signin";

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Mailer Error: ' . $this->mail->ErrorInfo);
            return false;
        }
    }
}