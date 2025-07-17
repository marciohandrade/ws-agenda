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

        // ✅ Log para debug (pode remover em produção)
        Log::info('CheckRole Middleware', [
            'user_id' => $user->id,
            'user_type' => $user->tipo_usuario,
            'required_roles' => $roles,
            'route' => $request->route()->getName(),
            'url' => $request->url()
        ]);

        // ✅ CORRIGIDO: Super admin tem acesso a tudo (sem redirect forçado)
        if ($user->tipo_usuario === 'super_admin') {
            return $next($request);
        }

        // Verificar se o usuário tem um dos roles permitidos
        if (!in_array($user->tipo_usuario, $roles)) {
            // ✅ Redirecionar baseado no tipo de usuário
            return $this->redirectBasedOnUserType($user->tipo_usuario, $request);
        }

        return $next($request);
    }

    /**
     * Redirecionar usuário baseado no seu tipo
     */
    private function redirectBasedOnUserType(string $userType, Request $request): Response
    {
        // ✅ Log do redirecionamento
        Log::info('Redirecionamento por tipo de usuário', [
            'user_type' => $userType,
            'current_route' => $request->route()->getName(),
            'current_url' => $request->url()
        ]);

        switch ($userType) {
            case 'super_admin':
                // ✅ Super admin vai para gestão de usuários
                if (!$request->routeIs('usuarios.index') && !$request->is('painel/*')) {
                    return redirect()->route('usuarios.index')
                        ->with('info', 'Você foi redirecionado para o painel administrativo.');
                }
                
                // Se já está no painel mas não tem permissão específica
                abort(403, 'Acesso negado. Você não tem permissão para acessar esta área específica.');
                
            case 'admin':
            case 'colaborador':
                // ✅ Admin/Colaborador vão para agendamentos
                if (!$request->is('painel/*')) {
                    return redirect()->route('agendamentos.index')
                        ->with('info', 'Você foi redirecionado para o painel administrativo.');
                }
                
                // Se já está numa rota do painel mas não tem permissão específica
                abort(403, 'Acesso negado. Você não tem permissão para acessar esta área específica.');
                
            case 'usuario':
                // ✅ Usuário vai para seus agendamentos
                if ($request->is('painel/*')) {
                    return redirect()->route('meus-agendamentos')
                        ->with('info', 'Você foi redirecionado para sua área de cliente.');
                }
                
                // Se está tentando acessar área administrativa
                abort(403, 'Acesso negado. Esta é uma área restrita para administradores.');
                
            default:
                // ✅ Tipo de usuário desconhecido
                Log::warning('Tipo de usuário desconhecido tentando acesso', [
                    'user_type' => $userType,
                    'user_id' => auth()->id(),
                    'route' => $request->route()->getName()
                ]);
                
                abort(403, 'Acesso negado. Tipo de usuário inválido.');
        }
    }

    /**
     * ✅ FUNÇÃO CORRIGIDA: Verificar se o usuário pode acessar uma rota específica
     */
    private function canAccessRoute(string $userType, string $routeName): bool
    {
        // ✅ ROTAS ADMINISTRATIVAS - Names corretos
        $adminRoutes = [
            'agendamentos.index',
            'clientes.index', 
            'servicos.index',
            'configuracoes-agendamento.index',
            'usuarios.index'
        ];

        // ✅ ROTAS DE CLIENTE - Names corretos
        $clientRoutes = [
            'meus-agendamentos',
            'perfil'
        ];

        // Verificar permissões
        switch ($userType) {
            case 'super_admin':
                return true; // Super admin acessa tudo
                
            case 'admin':
            case 'colaborador':
                return in_array($routeName, $adminRoutes) || 
                       in_array($routeName, $clientRoutes); // Podem acessar ambas as áreas
                       
            case 'usuario':
                return in_array($routeName, $clientRoutes); // Só área do cliente
                
            default:
                return false;
        }
    }

    /**
     * ✅ FUNÇÃO CORRIGIDA: Obter rota padrão para o tipo de usuário
     */
    private function getDefaultRouteForUser(string $userType): string
    {
        switch ($userType) {
            case 'super_admin':
                return 'usuarios.index'; // ✅ Super admin → Usuários
                
            case 'admin':
            case 'colaborador':
                return 'agendamentos.index'; // ✅ Admin/Colaborador → Agendamentos
                
            case 'usuario':
                return 'meus-agendamentos'; // ✅ Cliente → Seus agendamentos
                
            default:
                return 'login';
        }
    }
}