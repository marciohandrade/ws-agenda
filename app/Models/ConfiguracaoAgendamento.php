<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracaoAgendamento extends Model
{
    use HasFactory;

    protected $table = 'configuracoes_agendamento';

    protected $fillable = [
        'perfil',
        'antecedencia_minima_horas',
        'antecedencia_maxima_dias',
        'ativo'
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'antecedencia_minima_horas' => 'integer',
        'antecedencia_maxima_dias' => 'integer'
    ];

    // ✅ CONSTANTES MANTIDAS
    public const DIAS_SEMANA = [
        1 => 'Segunda-feira',
        2 => 'Terça-feira', 
        3 => 'Quarta-feira',
        4 => 'Quinta-feira',
        5 => 'Sexta-feira',
        6 => 'Sábado',
        7 => 'Domingo'
    ];

    // ✅ PERFIS DE CONFIGURAÇÃO
    public const PERFIS = [
        'publico' => 'Público (não cadastrado)',
        'cliente_cadastrado' => 'Cliente Cadastrado',
        'admin' => 'Administrador'
    ];

    // ✅ RELACIONAMENTO - ÚNICA FONTE DE VERDADE PARA HORÁRIOS
    public function horariosFuncionamento()
    {
        return $this->hasMany(HorarioFuncionamento::class);
    }

    // ✅ SCOPE PARA BUSCAR POR PERFIL
    public static function porPerfil($perfil)
    {
        return static::where('perfil', $perfil)->where('ativo', true)->first();
    }

    // ✅ MÉTODO REFATORADO - USA APENAS HORARIOS_FUNCIONAMENTO
    public function diaAtivo($diaSemana)
    {
        $horarioDia = $this->horariosFuncionamento()
            ->where('dia_semana', $diaSemana)
            ->first();
        
        return $horarioDia ? $horarioDia->ativo : false;
    }

    // ✅ MÉTODO PARA BUSCAR HORÁRIO ESPECÍFICO DO DIA
    public function horarioDia($diaSemana)
    {
        return $this->horariosFuncionamento()
            ->where('dia_semana', $diaSemana)
            ->where('ativo', true)
            ->first();
    }

    // ✅ MÉTODO PARA VERIFICAR SE HORÁRIO É VÁLIDO PARA O DIA
    public function horarioValidoParaDia($diaSemana, $horario)
    {
        $horarioDia = $this->horarioDia($diaSemana);
        
        if (!$horarioDia) {
            return false;
        }

        $horarioSolicitado = \Carbon\Carbon::createFromFormat('H:i', $horario);
        $inicio = \Carbon\Carbon::parse($horarioDia->horario_inicio);
        $fim = \Carbon\Carbon::parse($horarioDia->horario_fim);

        // Verificar se está dentro do horário de funcionamento
        if ($horarioSolicitado < $inicio || $horarioSolicitado >= $fim) {
            return false;
        }

        // Verificar horário de almoço
        if ($horarioDia->tem_almoco) {
            $almocoInicio = \Carbon\Carbon::parse($horarioDia->almoco_inicio);
            $almocoFim = \Carbon\Carbon::parse($horarioDia->almoco_fim);
            
            if ($horarioSolicitado >= $almocoInicio && $horarioSolicitado < $almocoFim) {
                return false;
            }
        }

        return true;
    }

    // ✅ MÉTODO PARA OBTER TODOS OS HORÁRIOS ATIVOS
    public function diasAtivos()
    {
        return $this->horariosFuncionamento()
            ->where('ativo', true)
            ->orderBy('dia_semana')
            ->get();
    }

    // ✅ MÉTODO PARA GARANTIR QUE SEMPRE EXISTAM 7 HORÁRIOS (seg-dom)
    public function garantirHorariosTodosDias()
    {
        for ($dia = 1; $dia <= 7; $dia++) {
            $horarioExistente = $this->horariosFuncionamento()
                ->where('dia_semana', $dia)
                ->first();

            if (!$horarioExistente) {
                // Criar horário padrão para o dia
                $this->horariosFuncionamento()->create([
                    'dia_semana' => $dia,
                    'horario_inicio' => '08:00',
                    'horario_fim' => '18:00',
                    'tem_almoco' => true,
                    'almoco_inicio' => '12:00',
                    'almoco_fim' => '13:00',
                    'ativo' => in_array($dia, [1, 2, 3, 4, 5]) // Seg-Sex por padrão
                ]);
            }
        }
    }
}