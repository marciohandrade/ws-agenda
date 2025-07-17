<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * 🛡️ REGRAS DE ACESSO POR PERFIL
     */
    private function getPermissionRules(): array
    {
        return [
            'super_admin' => ['*'], // Acesso total
            'admin' => [
                'painel/agendamentos',
                'painel/clientes', 
                'painel/servicos'
            ],
            'colaborador' => [
                'painel/agendamentos',
                'painel/clientes'
            ],
            'usuario' => [
                'meus-agendamentos',
                'perfil'
            ]
        ];
    }

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
        $currentPath = trim($request->path(), '/');

        // 🛡️ VERIFICAR PERMISSÕES ESPECÍFICAS
        if (!$this->hasPermissionToAccess($user->tipo_usuario, $currentPath)) {
            return $this->showAccessDeniedModal($user->tipo_usuario, $currentPath);
        }

        // ✅ Super admin tem acesso a tudo (depois da verificação acima)
        if ($user->tipo_usuario === 'super_admin') {
            return $next($request);
        }

        // Verificar se o usuário tem um dos roles permitidos pelo middleware
        if (!in_array($user->tipo_usuario, $roles)) {
            return $this->redirectBasedOnUserType($user->tipo_usuario, $request);
        }

        return $next($request);
    }

    /**
     * 🔒 Verificar se o usuário tem permissão para acessar a rota
     */
    private function hasPermissionToAccess(string $userType, string $path): bool
    {
        $rules = $this->getPermissionRules();
        
        // Se não existe regra para o tipo de usuário, negar acesso
        if (!isset($rules[$userType])) {
            return false;
        }

        $allowedPaths = $rules[$userType];

        // Super admin tem acesso total
        if (in_array('*', $allowedPaths)) {
            return true;
        }

        // Verificar se o path atual está na lista de permitidos
        foreach ($allowedPaths as $allowedPath) {
            // Verificação exata
            if ($path === $allowedPath) {
                return true;
            }
            
            // Verificação com wildcard (para sub-rotas)
            if (str_starts_with($path, $allowedPath . '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * 🚨 Mostrar modal de acesso negado
     */
    private function showAccessDeniedModal(string $userType, string $path): Response
    {
        $userTypeNames = [
            'super_admin' => 'Super Administrador',
            'admin' => 'Administrador', 
            'colaborador' => 'Colaborador',
            'usuario' => 'Cliente'
        ];

        $userName = $userTypeNames[$userType] ?? 'Usuário';
        $redirectUrl = $this->getDefaultRouteForUser($userType);

        // Retornar view com modal de acesso negado
        //return response()->view('errors.access-denied', [
        return response()->view('erros.access.denied', [
            'userName' => $userName,
            'userType' => $userType,
            'attemptedPath' => $path,
            'redirectUrl' => $redirectUrl,
            'allowedAreas' => $this->getAllowedAreasText($userType)
        ], 403);
    }

    /**
     * 📋 Obter texto das áreas permitidas para o usuário
     */
    private function getAllowedAreasText(string $userType): string
    {
        $rules = $this->getPermissionRules();
        $allowedPaths = $rules[$userType] ?? [];

        if (in_array('*', $allowedPaths)) {
            return 'Todas as áreas do sistema';
        }

        $areaNames = [
            'painel/agendamentos' => 'Agendamentos',
            'painel/clientes' => 'Clientes',
            'painel/servicos' => 'Serviços',
            'painel/usuarios' => 'Usuários',
            'painel/configuracoes-agendamento' => 'Configurações',
            'meus-agendamentos' => 'Meus Agendamentos',
            'perfil' => 'Meu Perfil'
        ];

        $allowedNames = [];
        foreach ($allowedPaths as $path) {
            if (isset($areaNames[$path])) {
                $allowedNames[] = $areaNames[$path];
            }
        }

        return implode(', ', $allowedNames);
    }

    /**
     * Redirecionar usuário baseado no seu tipo
     */
    private function redirectBasedOnUserType(string $userType, Request $request): Response
    {
        switch ($userType) {
            case 'super_admin':
                if (!$request->is('painel/*')) {
                    return redirect()->route('usuarios.index')
                        ->with('info', 'Você foi redirecionado para o painel administrativo.');
                }
                abort(403, 'Acesso negado.');
                
            case 'admin':
            case 'colaborador':
                if (!$request->is('painel/*')) {
                    return redirect()->route('agendamentos.index')
                        ->with('info', 'Você foi redirecionado para o painel administrativo.');
                }
                abort(403, 'Acesso negado.');
                
            case 'usuario':
                if ($request->is('painel/*')) {
                    return redirect()->route('meus-agendamentos')
                        ->with('info', 'Você foi redirecionado para sua área de cliente.');
                }
                abort(403, 'Acesso negado.');
                
            default:
                abort(403, 'Acesso negado. Tipo de usuário inválido.');
        }
    }

    /**
     * ✅ Obter rota padrão para o tipo de usuário
     */
    private function getDefaultRouteForUser(string $userType): string
    {
        switch ($userType) {
            case 'super_admin':
                return '/painel/usuarios';
                
            case 'admin':
            case 'colaborador':
                return '/painel/agendamentos';
                
            case 'usuario':
                return '/meus-agendamentos';
                
            default:
                return '/login';
        }
    }
}