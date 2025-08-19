<?php
namespace Utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Core\Services\LoggerFactory;

class MailHelper
{
    public static function send($to, $subject, $htmlBody)
    {
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';

        try {
            // Config SMTP via .env
            $mail->isSMTP();
            $mail->Host = $_ENV['MAIL_HOST'] ?? 'localhost';
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USERNAME'] ?? '';
            $mail->Password = $_ENV['MAIL_PASSWORD'] ?? '';
            $mail->Port = $_ENV['MAIL_PORT'] ?? 587;

            if ($mail->Port == 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $fromEmail = $_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@localhost';
            $fromName = $_ENV['MAIL_FROM_NAME'] ?? 'App';
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;

            // Debugging options (só em dev/debug)
            $appEnv = $_ENV['APP_ENV'] ?? 'production';
            $appDebug = $_ENV['APP_DEBUG'] ?? 'false';
            if ($appEnv === 'development' || $appDebug === 'true') {
                $mail->SMTPDebug = 2;
                $mail->Debugoutput = function ($str, $level) {
                    error_log("SMTP Debug [Level $level]: $str");
                };
            }

            // ENVIO REAL
            $mail->send();
            return true;
        } catch (Exception $e) {
            $logger = LoggerFactory::create();
            $logger->error('PHPMailer Error', [
                'to' => $to,
                'subject' => $subject,
                'mail_error' => $mail->ErrorInfo,
                'exception' => $e->getMessage()
            ]);

            error_log('Mailer Error: ' . $mail->ErrorInfo);
            return false;
        }
    }
}
