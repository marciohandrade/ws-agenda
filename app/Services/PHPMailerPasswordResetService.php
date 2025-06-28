<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Passwords\PasswordBroker;
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
        // Substituir o método de envio de email do Password Broker
        Password::extend('phpmailer', function ($app, $config) {
            return new PasswordBroker(
                $app['auth.password.tokens'],
                $app['auth']->createUserProvider($config['provider'] ?? null),
                function ($user, $token) {
                    // Usar nosso serviço PHPMailer personalizado
                    $service = app(PHPMailerPasswordResetService::class);
                    return $service->sendResetLink($user, $token);
                }
            );
        });
    }
}