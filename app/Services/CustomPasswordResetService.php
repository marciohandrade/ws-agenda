<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;

class CustomPasswordResetService
{
    private PHPMailer $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        $this->configurePHPMailer();
    }

    /**
     * Configurar PHPMailer
     */
    private function configurePHPMailer(): void
    {
        try {
            //Server settings - configuração para produção
            $this->mail->SMTPDebug = 0;                         //Desabilitar debug em produção
            $this->mail->isSMTP();                              //Send using SMTP
            $this->mail->Host       = env('MAIL_HOST');         //Set the SMTP server to send through
            $this->mail->SMTPAuth   = true;                     //Enable SMTP authentication
            $this->mail->Username   = env('MAIL_USERNAME');     //SMTP username
            $this->mail->Password   = env('MAIL_PASSWORD');     //SMTP password
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  //Enable explicit TLS encryption
            $this->mail->Port       = 587;                      //TCP port to connect to

            // Configurações SSL específicas para resolver problema de certificado
            $this->mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            //From
            $this->mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            
            //Content settings
            $this->mail->isHTML(true);                          //Set email format to HTML
            $this->mail->CharSet = 'UTF-8';

        } catch (Exception $e) {
            throw new Exception('Erro ao configurar PHPMailer: ' . $e->getMessage());
        }
    }

    /**
     * Enviar email de reset de senha
     */
    public function sendPasswordResetEmail(string $email): array
    {
        try {
            // Verificar se o usuário existe
            $user = User::where('email', $email)->first();
            if (!$user) {
                return ['success' => false, 'message' => 'Usuário não encontrado'];
            }

            // Gerar token único
            $token = Str::random(64);

            // Salvar token na base de dados
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                [
                    'email' => $email,
                    'token' => Hash::make($token),
                    'created_at' => Carbon::now()
                ]
            );

            // Gerar URL de reset usando helper route()
            $resetUrl = route('password.reset', [
                'token' => $token,
                'email' => $email
            ]);

            // Configurar email
            $this->mail->clearAddresses();
            $this->mail->addAddress($email, $user->name ?? '');
            $this->mail->Subject = 'Redefinição de Senha - ' . env('APP_NAME');
            
            // Template do email
            $this->mail->Body = $this->getEmailTemplate($user->name ?? 'Usuário', $resetUrl);
            $this->mail->AltBody = $this->getTextTemplate($user->name ?? 'Usuário', $resetUrl);

            // Enviar
            $result = $this->mail->send();

            if ($result) {
                return ['success' => true, 'message' => 'Email de reset enviado com sucesso!'];
            } else {
                return ['success' => false, 'message' => 'Falha ao enviar email'];
            }

        } catch (Exception $e) {
            return [
                'success' => false, 
                'message' => 'Erro: ' . $e->getMessage(),
                'details' => $this->mail->ErrorInfo
            ];
        }
    }

    /**
     * Template HTML do email
     */
    private function getEmailTemplate(string $userName, string $resetUrl): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Redefinição de Senha</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #f8f9fa; padding: 20px; text-align: center; border-radius: 5px; }
                .content { padding: 20px 0; }
                .button { 
                    display: inline-block; 
                    padding: 12px 24px; 
                    background-color: #007bff; 
                    color: white; 
                    text-decoration: none; 
                    border-radius: 5px; 
                    margin: 20px 0;
                }
                .footer { color: #666; font-size: 12px; text-align: center; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>" . env('APP_NAME') . "</h1>
                </div>
                <div class='content'>
                    <h2>Olá, {$userName}!</h2>
                    <p>Você está recebendo este email porque recebemos uma solicitação de redefinição de senha para sua conta.</p>
                    <p>Clique no botão abaixo para redefinir sua senha:</p>
                    <p style='text-align: center;'>
                        <a href='{$resetUrl}' class='button'>Redefinir Senha</a>
                    </p>
                    <p>Se você não solicitou esta redefinição, ignore este email. Sua senha permanecerá inalterada.</p>
                    <p><strong>Este link expira em 60 minutos.</strong></p>
                </div>
                <div class='footer'>
                    <p>Se você está tendo problemas para clicar no botão, copie e cole o link abaixo no seu navegador:</p>
                    <p>{$resetUrl}</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Template texto simples
     */
    private function getTextTemplate(string $userName, string $resetUrl): string
    {
        return "
Olá, {$userName}!

Você solicitou a redefinição da sua senha.

Para redefinir sua senha, acesse: {$resetUrl}

Se você não solicitou esta redefinição, ignore este email.

Este link expira em 60 minutos.

---
" . env('APP_NAME');
    }
}