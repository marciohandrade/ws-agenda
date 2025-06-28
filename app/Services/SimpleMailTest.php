<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class SimpleMailTest
{
    public function testMail(): array
    {
        // Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            // Server settings - exatamente como o exemplo oficial
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            $mail->isSMTP();                                            // Send using SMTP
            $mail->Host       = env('MAIL_HOST');                       // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = env('MAIL_USERNAME');                   // SMTP username
            $mail->Password   = env('MAIL_PASSWORD');                   // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable explicit TLS encryption
            $mail->Port       = 587;                                    // TCP port to connect to

            // Recipients
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $mail->addAddress('marcio.hol@hotmail.com', 'Marcio');      // Add a recipient

            // Content
            $mail->isHTML(true);                                        // Set email format to HTML
            $mail->Subject = 'Teste PHPMailer';
            $mail->Body    = 'Este é um <b>teste</b> do PHPMailer!';
            $mail->AltBody = 'Este é um teste do PHPMailer em texto simples.';

            $mail->send();
            return ['success' => true, 'message' => 'Email enviado com sucesso!'];
            
        } catch (Exception $e) {
            return [
                'success' => false, 
                'message' => "Erro: {$e->getMessage()}",
                'details' => $mail->ErrorInfo
            ];
        }
    }
}