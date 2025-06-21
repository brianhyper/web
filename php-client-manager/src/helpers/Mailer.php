<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    public static function send(
        string $to, 
        string $subject, 
        string $body, 
        string $altBody = '',
        array $attachments = []
    ): bool {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = getenv('SMTP_HOST');
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv('SMTP_USER');
            $mail->Password   = getenv('SMTP_PASS');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int)getenv('SMTP_PORT') ?: 587;
            $mail->CharSet    = 'UTF-8';

            // Recipients
            $mail->setFrom(getenv('SMTP_FROM'), getenv('APP_NAME'));
            $mail->addAddress($to);

            // Attachments
            foreach ($attachments as $attachment) {
                $mail->addAttachment(
                    $attachment['path'], 
                    $attachment['name'] ?? basename($attachment['path'])
                );
            }

            // Content
            $mail->isHTML(!empty($altBody));
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = $altBody ?: strip_tags($body);

            // Send in production
            if (getenv('APP_ENV') === 'production') {
                $mail->send();
                return true;
            }

            // Log in development
            return self::logEmail($to, $subject, $body, $attachments);
        } catch (Exception $e) {
            error_log("Mailer Error: {$mail->ErrorInfo}");
            Logger::error("Email sending failed", [
                'to' => $to,
                'error' => $mail->ErrorInfo
            ]);
            return false;
        }
    }

    private static function logEmail(
        string $to, 
        string $subject, 
        string $body,
        array $attachments
    ): bool {
        $logContent = "To: $to\nSubject: $subject\n\n$body\n";
        
        if (!empty($attachments)) {
            $logContent .= "\nAttachments:\n";
            foreach ($attachments as $att) {
                $logContent .= "- " . ($att['name'] ?? basename($att['path'])) . "\n";
            }
        }
        
        $logFile = STORAGE_PATH . '/logs/mail_' . date('Y-m-d') . '.log';
        file_put_contents($logFile, $logContent . "\n\n", FILE_APPEND);
        return true;
    }
}