<?php

namespace App\Livewire\Painel;

use Livewire\Component;
use App\Models\ConfiguracaoAgendamento;
use App\Models\HorarioFuncionamento;

class HorariosEspecificos extends Component
{
    public $configuracao_id;
    public $perfil_ativo = 'publico';
    public $horarios_dias = [];
    public $modo_avancado = false;

    protected $rules = [
        'horarios_dias.*.horario_inicio' => 'required_if:horarios_dias.*.ativo,true',
        'horarios_dias.*.horario_fim' => 'required_if:horarios_dias.*.ativo,true|after:horarios_dias.*.horario_inicio',
        'horarios_dias.*.almoco_inicio' => 'required_if:horarios_dias.*.tem_almoco,true',
        'horarios_dias.*.almoco_fim' => 'required_if:horarios_dias.*.tem_almoco,true|after:horarios_dias.*.almoco_inicio',
    ];

    public function mount($perfil = 'publico')
    {
        $this->perfil_ativo = $perfil;
        $this->carregarHorarios();
    }

    public function render()
    {
        return view('livewire.painel.horarios-especificos');
    }

    public function carregarHorarios()
    {
        $config = ConfiguracaoAgendamento::where('perfil', $this->perfil_ativo)->first();
        
        if (!$config) {
            $this->inicializarHorariosPadrao();
            return;
        }

        $this->configuracao_id = $config->id;
        
        // Carregar horários específicos ou usar padrão
        for ($dia = 1; $dia <= 7; $dia++) {
            $horarioEspecifico = $config->horariosFuncionamento()
                                       ->where('dia_semana', $dia)
                                       ->first();
            
            if ($horarioEspecifico) {
                $this->horarios_dias[$dia] = [
                    'id' => $horarioEspecifico->id,
                    'ativo' => $horarioEspecifico->ativo,
                    'horario_inicio' => $horarioEspecifico->horario_inicio->format('H:i'),
                    'horario_fim' => $horarioEspecifico->horario_fim->format('H:i'),
                    'tem_almoco' => $horarioEspecifico->tem_almoco,
                    'almoco_inicio' => $horarioEspecifico->almoco_inicio ? $horarioEspecifico->almoco_inicio->format('H:i') : '12:00',
                    'almoco_fim' => $horarioEspecifico->almoco_fim ? $horarioEspecifico->almoco_fim->format('H:i') : '13:00',
                ];
                $this->modo_avancado = true;
            } else {
                // Usar configuração geral
                $this->horarios_dias[$dia] = [
                    'id' => null,
                    'ativo' => in_array((string)$dia, $config->dias_funcionamento),
                    'horario_inicio' => $config->horario_inicio->format('H:i'),
                    'horario_fim' => $config->horario_fim->format('H:i'),
                    'tem_almoco' => $config->tem_horario_almoco,
                    'almoco_inicio' => $config->almoco_inicio->format('H:i'),
                    'almoco_fim' => $config->almoco_fim->format('H:i'),
                ];
            }
        }
    }

    public function inicializarHorariosPadrao()
    {
        $padroes = [
            'publico' => ['08:00', '18:00'],
            'cliente_cadastrado' => ['08:00', '19:00'],
            'admin' => ['07:00', '20:00']
        ];

        $horario = $padroes[$this->perfil_ativo] ?? $padroes['publico'];

        for ($dia = 1; $dia <= 7; $dia++) {
            $this->horarios_dias[$dia] = [
                'id' => null,
                'ativo' => $dia <= 5, // Seg-Sex por padrão
                'horario_inicio' => $horario[0],
                'horario_fim' => $horario[1],
                'tem_almoco' => true,
                'almoco_inicio' => '12:00',
                'almoco_fim' => '13:00',
            ];
        }
    }

    public function ativarModoAvancado()
    {
        $this->modo_avancado = true;
    }

    public function desativarModoAvancado()
    {
        $this->modo_avancado = false;
        
        // Limpar horários específicos
        if ($this->configuracao_id) {
            HorarioFuncionamento::where('configuracao_agendamento_id', $this->configuracao_id)->delete();
        }
        
        // Recarregar horários gerais
        $this->carregarHorarios();
        
        session()->flash('sucesso', 'Modo avançado desativado. Usando horários gerais.');
    }

    public function salvarHorariosEspecificos()
    {
        $this->validate();

        if (!$this->configuracao_id) {
            session()->flash('erro', 'Configure primeiro as configurações gerais.');
            return;
        }

        // Limpar horários existentes
        HorarioFuncionamento::where('configuracao_agendamento_id', $this->configuracao_id)->delete();

        // Salvar novos horários
        foreach ($this->horarios_dias as $dia => $horario) {
            if ($horario['ativo']) {
                HorarioFuncionamento::create([
                    'configuracao_agendamento_id' => $this->configuracao_id,
                    'dia_semana' => $dia,
                    'horario_inicio' => $horario['horario_inicio'],
                    'horario_fim' => $horario['horario_fim'],
                    'tem_almoco' => $horario['tem_almoco'],
                    'almoco_inicio' => $horario['tem_almoco'] ? $horario['almoco_inicio'] : null,
                    'almoco_fim' => $horario['tem_almoco'] ? $horario['almoco_fim'] : null,
                    'ativo' => true
                ]);
            }
        }

        session()->flash('sucesso', 'Horários específicos salvos com sucesso!');
    }

    public function copiarHorario($diaOrigem, $diaDestino)
    {
        $this->horarios_dias[$diaDestino] = [
            'id' => null,
            'ativo' => $this->horarios_dias[$diaOrigem]['ativo'],
            'horario_inicio' => $this->horarios_dias[$diaOrigem]['horario_inicio'],
            'horario_fim' => $this->horarios_dias[$diaOrigem]['horario_fim'],
            'tem_almoco' => $this->horarios_dias[$diaOrigem]['tem_almoco'],
            'almoco_inicio' => $this->horarios_dias[$diaOrigem]['almoco_inicio'],
            'almoco_fim' => $this->horarios_dias[$diaOrigem]['almoco_fim'],
        ];
    }

    public function aplicarTodosDias($dia)
    {
        $horarioBase = $this->horarios_dias[$dia];
        
        for ($i = 1; $i <= 7; $i++) {
            if ($i !== $dia) {
                $this->horarios_dias[$i] = [
                    'id' => null,
                    'ativo' => $horarioBase['ativo'],
                    'horario_inicio' => $horarioBase['horario_inicio'],
                    'horario_fim' => $horarioBase['horario_fim'],
                    'tem_almoco' => $horarioBase['tem_almoco'],
                    'almoco_inicio' => $horarioBase['almoco_inicio'],
                    'almoco_fim' => $horarioBase['almoco_fim'],
                ];
            }
        }
    }

    public function getDiaNome($dia)
    {
        return HorarioFuncionamento::DIAS_SEMANA[$dia];
    }
}