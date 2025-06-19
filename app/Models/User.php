<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
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
        'password',
        'telefone',           // ✅ ADICIONAR
        'tipo_usuario',       // ✅ ADICIONAR
        'telefone_verified_at',
        'sms_verification_token',
        'sms_token_expires_at',
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
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'telefone_verified_at' => 'datetime',
        'sms_token_expires_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relacionamento com Cliente
     */
    public function cliente()
    {
        return $this->hasOne(Cliente::class);
    }

    /**
     * Relacionamento com Agendamentos
     */
    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class);
    }

    /**
     * Verificar se é admin
     */
    public function isAdmin()
    {
        return $this->tipo_usuario === 'admin';
    }

    /**
     * Verificar se é colaborador
     */
    public function isColaborador()
    {
        return $this->tipo_usuario === 'colaborador';
    }

    /**
     * Verificar se é usuário comum
     */
    public function isUsuario()
    {
        return $this->tipo_usuario === 'usuario';
    }
}