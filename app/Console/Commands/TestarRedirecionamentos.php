<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class TestarRedirecionamentos extends Command
{
    protected $signature = 'test:redirects';
    protected $description = 'Testa os redirecionamentos por tipo de usuário';

    public function handle()
    {
        $this->info('🧪 Testando sistema de redirecionamentos...');
        
        // Buscar usuários de diferentes tipos
        $usuarios = [
            'super_admin' => User::where('tipo_usuario', 'super_admin')->first(),
            'admin' => User::where('tipo_usuario', 'admin')->first(),
            'colaborador' => User::where('tipo_usuario', 'colaborador')->first(),
            'usuario' => User::where('tipo_usuario', 'usuario')->first(),
        ];

        foreach ($usuarios as $tipo => $user) {
            if ($user) {
                $this->line("\n📋 Testando usuário: {$user->name} (ID: {$user->id})");
                $this->line("   Tipo: {$tipo}");
                
                // Testar métodos do modelo User
                $this->testUserMethods($user);
                
                // Testar rotas esperadas
                $this->testExpectedRoutes($tipo);
            } else {
                $this->warn("⚠️  Nenhum usuário encontrado do tipo: {$tipo}");
            }
        }

        $this->info("\n✅ Teste de redirecionamentos concluído!");
        
        // Mostrar estrutura de rotas
        $this->showRouteStructure();
    }

    private function testUserMethods($user)
    {
        $methods = [
            'isSuperAdmin' => $user->isSuperAdmin(),
            'isAdmin' => $user->isAdmin(),
            'isColaborador' => $user->isColaborador(),
            'isUsuario' => $user->isUsuario(),
            'canAccessAdmin' => $user->canAccessAdmin(),
            'isDeletable' => $user->isDeletable(),
        ];

        foreach ($methods as $method => $result) {
            $status = $result ? '✅' : '❌';
            $this->line("   {$status} {$method}(): " . ($result ? 'true' : 'false'));
        }
    }

    private function testExpectedRoutes($tipo)
    {
        $expectedRoutes = $this->getExpectedRoutes($tipo);
        
        $this->line("   🎯 Rotas esperadas:");
        foreach ($expectedRoutes as $route) {
            if (\Route::has($route)) {
                $this->line("      ✅ {$route}");
            } else {
                $this->line("      ❌ {$route} (não existe)");
            }
        }
    }

    private function getExpectedRoutes($tipo)
    {
        switch ($tipo) {
            case 'super_admin':
            case 'admin':
            case 'colaborador':
                return [
                    'painel.agendamentos.index',
                    'painel.clientes.index',
                    'painel.servicos.index',
                    'painel.usuarios.index',
                    'painel.meus-agendamentos',
                    'usuario.meus-agendamentos', // Podem acessar ambas
                ];
                
            case 'usuario':
                return [
                    'usuario.meus-agendamentos',
                    //'usuario.agendar',
                    //'usuario.perfil',
                ];
                
            default:
                return [];
        }
    }

    private function showRouteStructure()
    {
        $this->info("\n📁 Estrutura de Rotas Atual:");
        
        $routes = [
            '🔑 Área Administrativa (Painel)' => [
                'painel.agendamentos.index' => '/painel/agendamentos',
                'painel.clientes.index' => '/painel/clientes',
                'painel.servicos.index' => '/painel/servicos',
                'painel.usuarios.index' => '/painel/usuarios',
                'painel.meus-agendamentos' => '/painel/meus-agendamentos',
            ],
            '👤 Área do Cliente' => [
                'usuario.meus-agendamentos' => '/meus-agendamentos',
                //'usuario.agendar' => '/agendar',
                //'usuario.perfil' => '/perfil',
            ],
            '🔄 Redirecionamentos' => [
                'dashboard' => '/dashboard',
                'agendamentos.index' => '/agendamentos (fallback)',
            ],
        ];

        foreach ($routes as $section => $sectionRoutes) {
            $this->line("\n{$section}:");
            foreach ($sectionRoutes as $name => $path) {
                $exists = \Route::has($name) ? '✅' : '❌';
                $this->line("   {$exists} {$name} → {$path}");
            }
        }
    }
}