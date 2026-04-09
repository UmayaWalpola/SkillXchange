<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../libraries/phpmailer/src/Exception.php';
require_once __DIR__ . '/../libraries/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../libraries/phpmailer/src/SMTP.php';

// Load local credentials if present, else fall back to template
$mailConfig = __DIR__ . '/../config/mail.local.php';
if (file_exists($mailConfig)) {
    require_once $mailConfig;
} else {
    require_once __DIR__ . '/../config/mail.php';
}

class Mailer {

    private function buildMailer(): PHPMailer {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->CharSet = 'UTF-8';
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->setFrom(MAIL_FROM, MAIL_NAME);
        $mail->isHTML(true);
        return $mail;
    }

    private function template(string $title, string $bodyContent): string {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 30px; border: 1px solid #e0e0e0; border-radius: 10px; background: #ffffff;'>
            <div style='border-bottom: 3px solid #4A90D9; padding-bottom: 15px; margin-bottom: 25px;'>
                <h2 style='color: #4A90D9; margin: 0;'>SkillXchange</h2>
            </div>
            <h3 style='color: #333;'>{$title}</h3>
            {$bodyContent}
            <hr style='margin-top: 30px; border: none; border-top: 1px solid #eee;'>
            <p style='color: #aaa; font-size: 12px; margin-top: 10px;'>This is an automated message from SkillXchange. Please do not reply to this email.</p>
        </div>";
    }

    // Called when manager ADDS a new user
    public function sendNewUserCredentials(string $name, string $email, string $password, string $role): bool {
        try {
            $mail = $this->buildMailer();
            $mail->addAddress($email, $name);
            $mail->Subject = 'SkillXchange – Your Account Has Been Created';

            $roleLabel = ucfirst($role);
            $loginUrl  = URLROOT . '/login';

            $body = $this->template('Welcome to SkillXchange!', "
                <p>Hi <strong>{$name}</strong>,</p>
                <p>Your <strong>{$roleLabel}</strong> account has been created on SkillXchange. Here are your login credentials:</p>
                <table style='width:100%; border-collapse: collapse; margin: 20px 0; background: #f9f9f9; border-radius: 6px;'>
                    <tr>
                        <td style='padding: 12px 16px; font-weight: bold; color: #555; width: 40%;'>Email</td>
                        <td style='padding: 12px 16px; color: #222;'>{$email}</td>
                    </tr>
                    <tr style='background:#f0f4ff;'>
                        <td style='padding: 12px 16px; font-weight: bold; color: #555;'>Password</td>
                        <td style='padding: 12px 16px; color: #222;'>{$password}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px 16px; font-weight: bold; color: #555;'>Role</td>
                        <td style='padding: 12px 16px; color: #222;'>{$roleLabel}</td>
                    </tr>
                </table>
                <p style='color: #e74c3c;'>⚠️ Please change your password immediately after logging in.</p>
                <p><a href='{$loginUrl}' style='display:inline-block; padding: 10px 20px; background: #4A90D9; color: #fff; text-decoration: none; border-radius: 5px;'>Login to SkillXchange</a></p>
            ");

            $mail->Body    = $body;
            $mail->AltBody = "Hi {$name}, your {$roleLabel} account has been created.\nEmail: {$email}\nPassword: {$password}\nLogin: {$loginUrl}\n\nPlease change your password after logging in.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Mailer::sendNewUserCredentials failed: ' . $e->getMessage());
            return false;
        }
    }

    // Called when manager EDITS a user
    public function sendUpdatedCredentials(string $name, string $email, string $role, string $password = ''): bool {
        try {
            $mail = $this->buildMailer();
            $mail->addAddress($email, $name);
            $mail->Subject = 'SkillXchange – Your Account Details Have Been Updated';

            $roleLabel   = ucfirst($role);
            $loginUrl    = URLROOT . '/login';
            $passwordRow = '';

            if (!empty($password)) {
                $passwordRow = "
                    <tr style='background:#fff3cd;'>
                        <td style='padding: 12px 16px; font-weight: bold; color: #555;'>New Password</td>
                        <td style='padding: 12px 16px; color: #222;'>{$password}</td>
                    </tr>";
            }

            $body = $this->template('Your Account Has Been Updated', "
                <p>Hi <strong>{$name}</strong>,</p>
                <p>Your SkillXchange account details have been updated by a manager. Here are your current details:</p>
                <table style='width:100%; border-collapse: collapse; margin: 20px 0; background: #f9f9f9; border-radius: 6px;'>
                    <tr>
                        <td style='padding: 12px 16px; font-weight: bold; color: #555; width: 40%;'>Name</td>
                        <td style='padding: 12px 16px; color: #222;'>{$name}</td>
                    </tr>
                    <tr style='background:#f0f4ff;'>
                        <td style='padding: 12px 16px; font-weight: bold; color: #555;'>Email</td>
                        <td style='padding: 12px 16px; color: #222;'>{$email}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px 16px; font-weight: bold; color: #555;'>Role</td>
                        <td style='padding: 12px 16px; color: #222;'>{$roleLabel}</td>
                    </tr>
                    {$passwordRow}
                </table>
                " . (!empty($password) ? "<p style='color: #e74c3c;'>⚠️ Your password has been changed. Please log in with your new password.</p>" : "") . "
                <p>If you did not expect these changes, please contact your administrator immediately.</p>
                <p><a href='{$loginUrl}' style='display:inline-block; padding: 10px 20px; background: #4A90D9; color: #fff; text-decoration: none; border-radius: 5px;'>Login to SkillXchange</a></p>
            ");

            $mail->Body    = $body;
            $mail->AltBody = "Hi {$name}, your account has been updated.\nEmail: {$email}\nRole: {$roleLabel}" . (!empty($password) ? "\nNew Password: {$password}" : '') . "\nLogin: {$loginUrl}";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Mailer::sendUpdatedCredentials failed: ' . $e->getMessage());
            return false;
        }
    }
}