<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Agendamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'servico_id',
        'data_agendamento',
        'horario_agendamento',
        'status',
        'observacoes',
        'cliente_cadastrado_automaticamente',
        'data_cancelamento',
        'motivo_cancelamento',
        'ativo'
    ];

    protected $casts = [
        'data_agendamento' => 'date',
        'horario_agendamento' => 'datetime:H:i',
        'cliente_cadastrado_automaticamente' => 'boolean',
        'data_cancelamento' => 'datetime'
    ];

    const STATUS_PENDENTE = 'pendente';
    const STATUS_CONFIRMADO = 'confirmado';
    const STATUS_CONCLUIDO = 'concluido';
    const STATUS_CANCELADO = 'cancelado';

    /**
     * Relacionamento com cliente
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Relacionamento com serviço
     */
    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }

    /**
     * Lista de status disponíveis
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_PENDENTE => 'Pendente',
            self::STATUS_CONFIRMADO => 'Confirmado',
            self::STATUS_CONCLUIDO => 'Concluído',
            self::STATUS_CANCELADO => 'Cancelado'
        ];
    }

    /**
     * Accessor para status formatado
     */
    public function getStatusFormatadoAttribute(): string
    {
        return self::getStatusOptions()[$this->status] ?? 'Desconhecido';
    }

    /**
     * Accessor para data e hora formatadas
     */
    public function getDataHoraFormatadaAttribute(): string
    {
        return $this->data_agendamento->format('d/m/Y') . ' às ' . 
               Carbon::parse($this->horario_agendamento)->format('H:i');
    }

    /**
     * Accessor para cor do status
     */
    public function getCorStatusAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDENTE => 'warning',
            self::STATUS_CONFIRMADO => 'info',
            self::STATUS_CONCLUIDO => 'success',
            self::STATUS_CANCELADO => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Scope para agendamentos de hoje
     */
    public function scopeHoje($query)
    {
        return $query->whereDate('data_agendamento', today());
    }

    /**
     * Scope para agendamentos futuros
     */
    public function scopeFuturos($query)
    {
        return $query->where('data_agendamento', '>=', today());
    }

    /**
     * Scope para filtrar por status
     */
    public function scopeComStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para filtrar por período
     */
    public function scopeEntreDatas($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data_agendamento', [$dataInicio, $dataFim]);
    }

    /**
     * Verifica se o agendamento pode ser editado
     */
    public function podeSerEditado(): bool
    {
        return in_array($this->status, [self::STATUS_PENDENTE, self::STATUS_CONFIRMADO]) &&
               $this->data_agendamento >= today();
    }

    /**
     * Verifica se o agendamento pode ser cancelado
     */
    public function podeSerCancelado(): bool
    {
        return in_array($this->status, [self::STATUS_PENDENTE, self::STATUS_CONFIRMADO]);
    }
}