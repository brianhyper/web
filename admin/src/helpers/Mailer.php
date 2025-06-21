<?php
// src/helpers/Mailer.php
namespace App\helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    public function send($toEmail, $toName, $subject, $body) {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'];
            $mail->Password   = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $_ENV['MAIL_PORT'];
            
            // Recipients
            $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
            $mail->addAddress($toEmail, $toName);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
    
    public function sendVerificationEmail($email, $name, $verificationLink) {
        $subject = "Verify Your Email Address";
        $message = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f7f9fc; }
                    .container { max-width: 600px; margin: 0 auto; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
                    .header { background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                    .header h1 { color: white; margin: 0; font-size: 24px; }
                    .content { padding: 30px; }
                    .button { display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); 
                             color: white !important; text-decoration: none; border-radius: 50px; font-weight: 600; font-size: 16px; 
                             margin: 20px 0; box-shadow: 0 4px 15px rgba(37, 117, 252, 0.3); }
                    .footer { text-align: center; color: #888; font-size: 12px; padding-top: 20px; }
                    .code { background: #f0f5ff; padding: 15px; border-radius: 5px; margin: 20px 0; font-family: monospace; word-break: break-all; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Email Verification</h1>
                    </div>
                    <div class='content'>
                        <h2>Hello, $name!</h2>
                        <p>Thank you for registering. Please verify your email address to activate your account:</p>
                        <p style='text-align: center;'>
                            <a href='$verificationLink' class='button'>Verify Email Address</a>
                        </p>
                        <p>Or copy and paste this link into your browser:</p>
                        <div class='code'>$verificationLink</div>
                        <p>This link will expire in 1 hour.</p>
                        <p>If you didn't create an account, you can safely ignore this email.</p>
                    </div>
                    <div class='footer'>
                        <p>&copy; " . date('Y') . " " . $_ENV['APP_NAME'] . ". All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        return $this->send($email, $name, $subject, $message);
    }
}