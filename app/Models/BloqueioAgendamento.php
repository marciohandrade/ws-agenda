<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BloqueioAgendamento extends Model
{
    use HasFactory;

    protected $table = 'bloqueios_agendamento';

    protected $fillable = [
        'tipo',
        'data_inicio',
        'data_fim',
        'horario_inicio',
        'horario_fim',
        'motivo',
        'observacoes',
        'recorrente',
        'perfis_afetados',
        'ativo'
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'horario_inicio' => 'datetime:H:i',
        'horario_fim' => 'datetime:H:i',
        'recorrente' => 'boolean',
        'perfis_afetados' => 'array',
        'ativo' => 'boolean'
    ];

    // ✅ TIPOS CORRIGIDOS para corresponder à interface
    const TIPOS = [
        'dia_completo' => 'Dia Completo',
        'periodo' => 'Período',
        'horario_especifico' => 'Horário Específico'
    ];

    /**
     * Verificar se uma data/hora está bloqueada para um perfil
     */
    public static function estaBloqueado($data, $horario = null, $perfil = 'publico')
    {
        $query = self::where('ativo', true)
                     ->where(function($q) use ($perfil) {
                         $q->whereJsonContains('perfis_afetados', $perfil)
                           ->orWhereJsonContains('perfis_afetados', 'todos');
                     });

        // Verificar bloqueios por data
        $query->where(function($q) use ($data) {
            $q->where(function($subQ) use ($data) {
                // ✅ CORRIGIDO: dia_completo em vez de data_completa
                $subQ->where('tipo', 'dia_completo')
                     ->where('data_inicio', $data);
            })->orWhere(function($subQ) use ($data) {
                // Bloqueios de período
                $subQ->where('tipo', 'periodo')
                     ->where('data_inicio', '<=', $data)
                     ->where('data_fim', '>=', $data);
            })->orWhere(function($subQ) use ($data) {
                // Bloqueios recorrentes (mesmo dia/mês de anos diferentes)
                $subQ->where('recorrente', true)
                     ->whereRaw('DAY(data_inicio) = DAY(?)', [$data])
                     ->whereRaw('MONTH(data_inicio) = MONTH(?)', [$data]);
            });
        });

        $bloqueios = $query->get();

        foreach ($bloqueios as $bloqueio) {
            // ✅ CORRIGIDO: dia_completo
            if ($bloqueio->tipo === 'dia_completo') {
                return true;
            }

            // Se é bloqueio de período, está bloqueado
            if ($bloqueio->tipo === 'periodo') {
                return true;
            }

            // Se é bloqueio de horário específico, verificar horário
            if ($bloqueio->tipo === 'horario_especifico' && $horario) {
                $horarioCheck = Carbon::parse($horario);
                $inicio = Carbon::parse($bloqueio->horario_inicio);
                $fim = Carbon::parse($bloqueio->horario_fim);

                if ($horarioCheck >= $inicio && $horarioCheck <= $fim) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Scope para bloqueios ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para bloqueios por perfil
     */
    public function scopePorPerfil($query, $perfil)
    {
        return $query->where(function($q) use ($perfil) {
            $q->whereJsonContains('perfis_afetados', $perfil)
              ->orWhereJsonContains('perfis_afetados', 'todos');
        });
    }

    /**
     * Accessor para tipo formatado
     */
    public function getTipoFormatadoAttribute()
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }

    /**
     * Accessor para perfis formatados
     */
    public function getPerfisFormatadosAttribute()
    {
        if (!$this->perfis_afetados) {
            return 'Nenhum';
        }
        
        return collect($this->perfis_afetados)
            ->map(fn($perfil) => ConfiguracaoAgendamento::PERFIS[$perfil] ?? $perfil)
            ->implode(', ');
    }

    /**
     * ✅ ACCESSOR CORRIGIDO para período formatado
     */
    public function getPeriodoFormatadoAttribute()
    {
        if ($this->tipo === 'dia_completo') {
            return $this->data_inicio->format('d/m/Y');
        }

        if ($this->tipo === 'periodo') {
            return $this->data_inicio->format('d/m/Y') . ' até ' . 
                   ($this->data_fim ? $this->data_fim->format('d/m/Y') : 'indefinido');
        }

        if ($this->tipo === 'horario_especifico') {
            $horario = '';
            if ($this->horario_inicio && $this->horario_fim) {
                $horario = ' - ' . Carbon::parse($this->horario_inicio)->format('H:i') . ' às ' .
                          Carbon::parse($this->horario_fim)->format('H:i');
            }
            return $this->data_inicio->format('d/m/Y') . $horario;
        }

        return $this->data_inicio ? $this->data_inicio->format('d/m/Y') : '';
    }
}