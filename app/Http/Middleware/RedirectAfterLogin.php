<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectAfterLogin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Verificar se o usuário acabou de fazer login e está sendo redirecionado
        if (auth()->check() && $request->is('dashboard')) {
            $user = auth()->user();
            
            // Redirecionar baseado no tipo de usuário
            switch ($user->tipo_usuario) {
                case 'admin':
                case 'colaborador':
                    return redirect()->route('painel.agendamentos.index');
                    
                case 'usuario':
                    // Quando implementarmos a área do cliente
                    return redirect()->route('cliente.dashboard');
                    
                default:
                    // Fallback para a rota padrão
                    break;
            }
        }

        return $response;
    }
}