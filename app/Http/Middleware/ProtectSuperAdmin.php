<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class ProtectSuperAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar se está tentando modificar/excluir um super admin
        $userId = $request->route('user') ?? $request->route('id');
        
        if ($userId) {
            $targetUser = User::find($userId);
            
            if ($targetUser && $targetUser->isSuperAdmin()) {
                $currentUser = auth()->user();
                
                // Apenas super admin pode modificar super admin
                if (!$currentUser || !$currentUser->isSuperAdmin()) {
                    abort(403, 'Você não tem permissão para modificar este usuário.');
                }
                
                // Super admin não pode excluir a si mesmo
                if ($request->isMethod('delete') && $currentUser->id === $targetUser->id) {
                    abort(403, 'Você não pode excluir sua própria conta de super administrador.');
                }
            }
        }

        return $next($request);
    }
}