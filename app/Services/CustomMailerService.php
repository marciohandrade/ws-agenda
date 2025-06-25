<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class CustomMailerService
{
    private $mail;
    
    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        $this->configureSMTP();
    }
    
    /**
     * Configurar SMTP
     */
    private function configureSMTP()
    {
        try {
            // Configurações do servidor
            $this->mail->isSMTP();
            $this->mail->Host = env('MAIL_HOST', 'mail.webskill.com.br');
            $this->mail->SMTPAuth = true;
            $this->mail->Username = env('MAIL_USERNAME', 'contato@webskill.com.br');
            $this->mail->Password = env('MAIL_PASSWORD', '');
            $this->mail->SMTPSecure = env('MAIL_ENCRYPTION', PHPMailer::ENCRYPTION_STARTTLS);
            $this->mail->Port = env('MAIL_PORT', 587);
            
            // Configurações adicionais
            $this->mail->CharSet = 'UTF-8';
            $this->mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', env('APP_NAME')));
            
            // Debug apenas em desenvolvimento
            if (env('APP_DEBUG', false)) {
                $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
            }
            
        } catch (Exception $e) {
            throw new Exception("Erro na configuração SMTP: " . $e->getMessage());
        }
    }
    
    /**
     * Enviar email de recuperação de senha
     */
    public function sendPasswordReset($email, $token, $userName)
    {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($email, $userName);
            
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Recuperação de Senha - ' . env('APP_NAME');
            
            $resetUrl = url('password/reset/' . $token . '?email=' . urlencode($email));
            
            $this->mail->Body = $this->getPasswordResetTemplate($userName, $resetUrl);
            $this->mail->AltBody = "Olá {$userName}, clique no link para redefinir sua senha: {$resetUrl}";
            
            $this->mail->send();
            return true;
            
        } catch (Exception $e) {
            \Log::error('Erro ao enviar email de recuperação: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar email de confirmação de agendamento
     */
    public function sendAppointmentConfirmation($email, $userName, $appointmentData)
    {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($email, $userName);
            
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Agendamento Confirmado - ' . env('APP_NAME');
            
            $this->mail->Body = $this->getAppointmentTemplate($userName, $appointmentData);
            $this->mail->AltBody = "Olá {$userName}, seu agendamento foi confirmado para {$appointmentData['data']} às {$appointmentData['hora']}.";
            
            $this->mail->send();
            return true;
            
        } catch (Exception $e) {
            \Log::error('Erro ao enviar confirmação de agendamento: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Template de recuperação de senha
     */
    private function getPasswordResetTemplate($userName, $resetUrl)
    {
        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2c3e50;'>Recuperação de Senha</h2>
                <p>Olá <strong>{$userName}</strong>,</p>
                <p>Você solicitou a recuperação de senha para sua conta.</p>
                <p>Clique no botão abaixo para redefinir sua senha:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$resetUrl}' style='background-color: #3498db; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                        Redefinir Senha
                    </a>
                </div>
                <p><small>Se você não solicitou esta recuperação, ignore este email.</small></p>
                <p><small>Este link expira em 60 minutos.</small></p>
                <hr>
                <p><small>Atenciosamente,<br>Equipe " . env('APP_NAME') . "</small></p>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Template de confirmação de agendamento
     */
    private function getAppointmentTemplate($userName, $data)
    {
        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #27ae60;'>✅ Agendamento Confirmado</h2>
                <p>Olá <strong>{$userName}</strong>,</p>
                <p>Seu agendamento foi confirmado com sucesso!</p>
                
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                    <h3 style='margin-top: 0; color: #2c3e50;'>Detalhes do Agendamento:</h3>
                    <p><strong>Data:</strong> {$data['data']}</p>
                    <p><strong>Horário:</strong> {$data['hora']}</p>
                    <p><strong>Serviço:</strong> {$data['servico']}</p>
                </div>
                
                <p>Chegue com 10 minutos de antecedência.</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . url('/minha-conta/agendamentos') . "' style='background-color: #27ae60; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                        Ver Meus Agendamentos
                    </a>
                </div>
                
                <hr>
                <p><small>Atenciosamente,<br>Equipe " . env('APP_NAME') . "</small></p>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Teste de conexão SMTP
     */
    public function testConnection()
    {
        try {
            $this->mail->smtpConnect();
            $this->mail->smtpClose();
            return ['success' => true, 'message' => 'Conexão SMTP bem-sucedida!'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro na conexão: ' . $e->getMessage()];
        }
    }
}