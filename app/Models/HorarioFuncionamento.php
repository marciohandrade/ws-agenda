<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class HorarioFuncionamento extends Model
{
    use HasFactory;

    protected $table = 'horarios_funcionamento';

    protected $fillable = [
        'configuracao_agendamento_id',
        'dia_semana',
        'horario_inicio',
        'horario_fim',
        'tem_almoco',
        'almoco_inicio',
        'almoco_fim',
        'ativo'
    ];

    protected $casts = [
        'horario_inicio' => 'datetime:H:i',
        'horario_fim' => 'datetime:H:i',
        'almoco_inicio' => 'datetime:H:i',
        'almoco_fim' => 'datetime:H:i',
        'tem_almoco' => 'boolean',
        'ativo' => 'boolean'
    ];

    const DIAS_SEMANA = [
        1 => 'Segunda-feira',
        2 => 'Terça-feira',
        3 => 'Quarta-feira',
        4 => 'Quinta-feira',
        5 => 'Sexta-feira',
        6 => 'Sábado',
        7 => 'Domingo'
    ];

    /**
     * Relacionamento com configuração
     */
    public function configuracao(): BelongsTo
    {
        return $this->belongsTo(ConfiguracaoAgendamento::class, 'configuracao_agendamento_id');
    }

    /**
     * Gerar horários disponíveis para este dia específico
     */
    public function gerarHorarios($intervalo_minutos = 30)
    {
        if (!$this->ativo) {
            return [];
        }

        $horarios = [];
        $inicio = Carbon::parse($this->horario_inicio);
        $fim = Carbon::parse($this->horario_fim);
        $almoco_inicio = $this->tem_almoco ? Carbon::parse($this->almoco_inicio) : null;
        $almoco_fim = $this->tem_almoco ? Carbon::parse($this->almoco_fim) : null;

        while ($inicio < $fim) {
            // Pular horário de almoço
            if ($almoco_inicio && $almoco_fim && 
                $inicio >= $almoco_inicio && $inicio < $almoco_fim) {
                $inicio->addMinutes($intervalo_minutos);
                continue;
            }

            $horarios[] = $inicio->format('H:i');
            $inicio->addMinutes($intervalo_minutos);
        }

        return $horarios;
    }

    /**
     * Accessor para nome do dia
     */
    public function getDiaFormatadoAttribute()
    {
        return self::DIAS_SEMANA[$this->dia_semana] ?? 'Desconhecido';
    }

    /**
     * Accessor para horário formatado
     */
    public function getHorarioFormatadoAttribute()
    {
        $horario = $this->horario_inicio->format('H:i') . ' às ' . $this->horario_fim->format('H:i');
        
        if ($this->tem_almoco) {
            $horario .= ' (Almoço: ' . $this->almoco_inicio->format('H:i') . '-' . $this->almoco_fim->format('H:i') . ')';
        }
        
        return $horario;
    }

    /**
     * Scope para dias ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para um dia específico
     */
    public function scopeDia($query, $diaSemana)
    {
        return $query->where('dia_semana', $diaSemana);
    }
}