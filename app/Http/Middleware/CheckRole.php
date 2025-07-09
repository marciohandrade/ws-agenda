<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Verificar se o usuário está autenticado
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // ✅ Log para debug (pode remover em produção)
        Log::info('CheckRole Middleware', [
            'user_id' => $user->id,
            'user_type' => $user->tipo_usuario,
            'required_roles' => $roles,
            'route' => $request->route()->getName(),
            'url' => $request->url()
        ]);

        // ✅ CORREÇÃO: Super admin tem acesso a tudo
        if ($user->tipo_usuario === 'super_admin') {
            return $next($request);
        }

        // ✅ CORREÇÃO: Verificar se o usuário tem um dos roles permitidos
        if (in_array($user->tipo_usuario, $roles)) {
            return $next($request); // ✅ PERMITE O ACESSO
        }

        // ✅ CORREÇÃO: Se não tem permissão, redirecionar adequadamente
        return $this->redirectBasedOnUserType($user->tipo_usuario, $request);
    }

    /**
     * ✅ CORREÇÃO COMPLETA: Redirecionar usuário baseado no seu tipo
     */
    private function redirectBasedOnUserType(string $userType, Request $request): Response
    {
        Log::info('Redirecionamento por falta de permissão', [
            'user_type' => $userType,
            'current_route' => $request->route()->getName(),
            'current_url' => $request->url()
        ]);

        switch ($userType) {
            case 'super_admin':
            case 'admin':
            case 'colaborador':
                // ✅ Se é admin/colaborador mas não tem essa permissão específica
                // Redirecionar para área administrativa padrão
                return redirect()->route('painel.agendamentos.index')
                    ->with('warning', 'Você não tem permissão para essa área específica. Redirecionado para agendamentos.');
                break; // ✅ CORREÇÃO: Adicionar break

            case 'usuario':
                // ✅ CORREÇÃO: Usar a rota correta que existe no web.php
                return redirect()->route('meus-agendamentos')
                    ->with('warning', 'Esta é uma área restrita para administradores.');
                break; // ✅ CORREÇÃO: Adicionar break

            default:
                // ✅ Tipo de usuário desconhecido
                Log::warning('Tipo de usuário desconhecido tentando acesso', [
                    'user_type' => $userType,
                    'user_id' => auth()->id(),
                    'route' => $request->route()->getName()
                ]);
                
                return redirect()->route('login')
                    ->with('error', 'Tipo de usuário inválido. Faça login novamente.');
                break; // ✅ CORREÇÃO: Adicionar break
        }
    }
}