<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'data_nascimento',
        'genero',
        //'cpf',
        'cep',
        'endereco',
        'numero',
        'complemento'
    ];

     protected $casts = [
        'data_nascimento' => 'date'
    ];

    /**
     * Relacionamento com agendamentos
     */
    public function agendamentos(): HasMany
    {
        return $this->hasMany(Agendamento::class);
    }

    /**
     * Agendamentos futuros do cliente
     */
    public function agendamentosFuturos(): HasMany
    {
        return $this->hasMany(Agendamento::class)
            ->where('data_agendamento', '>=', today())
            ->whereNotIn('status', ['cancelado']);
    }

    /**
     * Histórico de agendamentos do cliente
     */
    public function historicoAgendamentos(): HasMany
    {
        return $this->hasMany(Agendamento::class)
            ->where('data_agendamento', '<', today())
            ->orWhere('status', 'concluido');
    }

    /**
     * Accessor para nome formatado
     */
    public function getNomeCompletoAttribute(): string
    {
        return $this->nome;
    }

    /**
     * Accessor para telefone formatado
     */
    public function getTelefoneFormatadoAttribute(): string
    {
        $telefone = preg_replace('/\D/', '', $this->telefone);
        
        if (strlen($telefone) == 11) {
            return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7);
        } elseif (strlen($telefone) == 10) {
            return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 4) . '-' . substr($telefone, 6);
        }
        
        return $this->telefone;
    }

    /**
     * Accessor para CPF formatado
     */
    public function getCpfFormatadoAttribute(): string
    {
        if (!$this->cpf) return 'Não informado';
        
        $cpf = preg_replace('/\D/', '', $this->cpf);
        
        if (strlen($cpf) == 11) {
            return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9);
        }
        
        return $this->cpf;
    }

    /**
     * Accessor para endereço completo
     */
    public function getEnderecoCompletoAttribute(): string
    {
        $endereco = $this->endereco;
        
        if ($this->numero) {
            $endereco .= ', ' . $this->numero;
        }
        
        if ($this->complemento) {
            $endereco .= ' - ' . $this->complemento;
        }
        
        if ($this->cep) {
            $endereco .= ' - CEP: ' . $this->cep;
        }
        
        return $endereco ?: 'Endereço não informado';
    }
}
