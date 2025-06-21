<?php

namespace App\Providers;

use App\Services\AgendamentoService;
use Illuminate\Support\ServiceProvider;

class AgendamentoServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AgendamentoService::class, function ($app) {
            return new AgendamentoService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configurar throttle personalizado
        $this->app['router']->aliasMiddleware('throttle:agendamento', function ($request) {
            return app(\App\Http\Middleware\AgendamentoRateLimit::class)->handle(
                $request,
                function ($request) {
                    return response('OK');
                }
            );
        });
    }
}

// ==========================================
// INSTRUÇÕES DE INSTALAÇÃO:
// ==========================================

/*
1. Registrar o Service Provider em config/app.php:

'providers' => [
    // ... outros providers
    App\Providers\AgendamentoServiceProvider::class,
],

2. Registrar o middleware em app/Http/Kernel.php:

protected $middlewareAliases = [
    // ... outros middlewares
    'agendamento' => \App\Http\Middleware\AgendamentoRateLimit::class,
];

3. Criar o diretório Services se não existir:
mkdir app/Services

4. Limpar cache de rotas e config:
php artisan route:clear
php artisan config:clear
php artisan cache:clear
*/