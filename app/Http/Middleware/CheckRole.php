<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Verificar se o usuário está autenticado
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Verificar se o usuário tem um dos roles permitidos
        if (!in_array($user->tipo_usuario, $roles)) {
            // Redirecionar baseado no tipo de usuário
            return $this->redirectBasedOnUserType($user->tipo_usuario);
        }

        return $next($request);
    }

    /**
     * Redirecionar usuário baseado no seu tipo
     */
    private function redirectBasedOnUserType(string $userType): Response
    {
        switch ($userType) {
            case 'admin':
            case 'colaborador':
                // Admin/colaborador já têm acesso ao painel
                return redirect()->route('agendamentos.index');
                
            case 'usuario':
            default:
                // Usuário comum vai para área do cliente (implementar depois)
                abort(403, 'Acesso negado. Você não tem permissão para acessar esta área.');
        }
    }
}