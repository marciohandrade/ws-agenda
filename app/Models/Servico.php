<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servico extends Model
{
    use HasFactory;

    /**
     * A tabela associada ao modelo.
     */
    protected $table = 'servicos';

    /**
     * Os atributos que podem ser atribuídos em massa.
     */
    protected $fillable = [
        'nome',
        'descricao',
        'preco',
        'duracao',
        'ativo',
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     */
    protected $casts = [
        'preco' => 'decimal:2',
        'duracao' => 'integer',
        'ativo' => 'boolean',
    ];

    /**
     * Scope para buscar apenas serviços ativos.
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Accessor para formatar o preço.
     */
    public function getPrecoFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->preco, 2, ',', '.');
    }

    /**
     * Accessor para formatar a duração.
     */
    public function getDuracaoFormatadaAttribute()
    {
        return $this->duracao . ' min';
    }

    /**
     * Accessor para exibição completa do serviço.
     */
    public function getDisplayCompletoAttribute()
    {
        return $this->nome . ' - ' . $this->preco_formatado . ' (' . $this->duracao_formatada . ')';
    }
}