<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Contracts\Auth\UserProvider;
use App\Services\PHPMailerPasswordResetService;

class PHPMailerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar o serviço personalizado de PHPMailer
        $this->app->singleton(PHPMailerPasswordResetService::class, function ($app) {
            return new PHPMailerPasswordResetService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Sobrescrever o password broker padrão
        Password::extend('phpmailer', function ($app, $config) {
            return new class(
                $app['auth.password.tokens'],
                $app['auth']->createUserProvider($config['provider'] ?? null)
            ) extends PasswordBroker {
                
                public function __construct(TokenRepositoryInterface $tokens, UserProvider $users)
                {
                    parent::__construct($tokens, $users);
                }
                
                protected function sendPasswordResetNotification($user, $token)
                {
                    // Usar nosso serviço PHPMailer
                    $service = app(PHPMailerPasswordResetService::class);
                    $result = $service->sendResetLink($user, $token);
                    
                    if (!$result) {
                        throw new \Exception('Falha ao enviar email de reset');
                    }
                }
            };
        });
    }
}