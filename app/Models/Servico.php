<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servico extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'descricao',
        'duracao_minutos',
        'preco',
        'ativo'
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'preco' => 'decimal:2'
    ];

    /**
     * Relacionamento com agendamentos
     */
    public function agendamentos(): HasMany
    {
        return $this->hasMany(Agendamento::class);
    }

    /**
     * Scope para serviços ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

     /**
     * Scope para serviços ativos (alias)
     */
    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Accessor para preço formatado
     */
    public function getPrecoFormatadoAttribute(): string
    {
        return $this->preco ? 'R$ ' . number_format($this->preco, 2, ',', '.') : 'Não informado';
    }

    /**
     * Accessor para duração formatada
     */
    public function getDuracaoFormatadaAttribute(): string
    {
        $horas = floor($this->duracao_minutos / 60);
        $minutos = $this->duracao_minutos % 60;
        
        if ($horas > 0 && $minutos > 0) {
            return "{$horas}h {$minutos}min";
        } elseif ($horas > 0) {
            return "{$horas}h";
        } else {
            return "{$minutos}min";
        }
    }
}