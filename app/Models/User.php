<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'telefone',
        'tipo_usuario',
        'password',
        'email_verified_at',
        'telefone_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'sms_verification_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'telefone_verified_at' => 'datetime',
            'sms_token_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Verificar se é super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->tipo_usuario === 'super_admin';
    }

    /**
     * Verificar se o usuário é admin
     */
    public function isAdmin(): bool
    {
        return $this->tipo_usuario === 'admin';
    }

    /**
     * Verificar se o usuário é colaborador
     */
    public function isColaborador(): bool
    {
        return $this->tipo_usuario === 'colaborador';
    }

    /**
     * Verificar se o usuário é usuário comum
     */
    public function isUsuario(): bool
    {
        return $this->tipo_usuario === 'usuario';
    }

    /**
     * Verificar se o usuário pode acessar o painel administrativo
     */
    public function canAccessAdmin(): bool
    {
        return in_array($this->tipo_usuario, ['super_admin', 'admin', 'colaborador']);
    }

    /**
     * Verificar se o usuário pode ser deletado
     * (Prevenção para não deletar o último admin ou super admin)
     */
    public function isDeletable(): bool
    {
        // Super admin nunca pode ser deletado
        if ($this->isSuperAdmin()) {
            return false;
        }

        // Se não for admin, pode ser deletado
        if (!$this->isAdmin()) {
            return true;
        }

        // Se for admin, só pode ser deletado se existir outro admin
        $adminCount = static::where('tipo_usuario', 'admin')->count();
        return $adminCount > 1;
    }

    /**
     * Boot method para proteções automáticas
     */
    protected static function boot()
    {
        parent::boot();

        // Proteção contra exclusão do último admin ou super admin
        static::deleting(function ($user) {
            if (!$user->isDeletable()) {
                if ($user->isSuperAdmin()) {
                    throw new \Exception('Super administrador não pode ser excluído.');
                } else {
                    throw new \Exception('Não é possível excluir o último administrador do sistema.');
                }
            }
        });
    }

    /**
     * Scope para buscar apenas super admins
     */
    public function scopeSuperAdmins($query)
    {
        return $query->where('tipo_usuario', 'super_admin');
    }

    /**
     * Scope para buscar apenas admins
     */
    public function scopeAdmins($query)
    {
        return $query->where('tipo_usuario', 'admin');
    }

    /**
     * Scope para buscar apenas colaboradores
     */
    public function scopeColaboradores($query)
    {
        return $query->where('tipo_usuario', 'colaborador');
    }

    /**
     * Scope para buscar apenas usuários comuns
     */
    public function scopeUsuarios($query)
    {
        return $query->where('tipo_usuario', 'usuario');
    }

    /**
     * Scope para buscar usuários que podem acessar admin
     */
    public function scopeAdminAccess($query)
    {
        return $query->whereIn('tipo_usuario', ['super_admin', 'admin', 'colaborador']);
    }

    /**
     * Scope para buscar apenas usuários que podem ser gerenciados
     */
    public function scopeManageable($query)
    {
        $currentUser = auth()->user();
        
        if ($currentUser && $currentUser->isSuperAdmin()) {
            // Super admin vê todos, exceto outros super admins
            return $query->where('tipo_usuario', '!=', 'super_admin')
                        ->orWhere('id', $currentUser->id);
        }

        // Outros usuários veem apenas usuários comuns
        return $query->where('tipo_usuario', 'usuario');
    }
}