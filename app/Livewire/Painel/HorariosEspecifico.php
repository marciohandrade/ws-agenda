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

    /**
 * ✅ CORRIGIDO: Carregar horários com conflito POR SERVIÇO
 */
    public function carregarHorarios($data)
    {
        $this->carregandoHorarios = true;
        $this->horariosDisponiveis = [];
        
        try {
            $dataCarbon = Carbon::parse($data);
            $diaSemana = $dataCarbon->dayOfWeek;
            
            $horarioFuncionamento = DB::table('horarios_funcionamento')
                ->where('dia_semana', $diaSemana)
                ->where('ativo', 1)
                ->first();
            
            if (!$horarioFuncionamento) {
                $this->carregandoHorarios = false;
                return;
            }
            
            // ✅ USAR DURAÇÃO DO SERVIÇO SELECIONADO COMO INTERVALO
            $intervalo = $this->getDuracaoServicoSelecionado();
            
            $horarios = [];
            $dataStr = $dataCarbon->format('Y-m-d');
            
            $horaInicio = substr($horarioFuncionamento->horario_inicio, 0, 8);
            $horaFim = substr($horarioFuncionamento->horario_fim, 0, 8);
            
            $inicio = Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . $horaInicio);
            $fim = Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . $horaFim);
            
            $current = $inicio->copy();
            
            // ✅ BUSCAR APENAS AGENDAMENTOS DO SERVIÇO ESPECÍFICO
            $agendamentosOcupados = [];
            if ($this->servico_id) {
                $agendamentosOcupados = DB::table('agendamentos')
                    ->where('data_agendamento', $dataStr)
                    ->where('servico_id', $this->servico_id) // ✅ FILTRO POR SERVIÇO
                    ->whereIn('status', ['pendente', 'confirmado'])
                    ->where('ativo', 1)
                    ->pluck('horario_agendamento')
                    ->map(function($horario) {
                        return Carbon::parse($horario)->format('H:i');
                    })
                    ->toArray();
            }
            
            while ($current < $fim) {
                // Verificar horário de almoço
                if (isset($horarioFuncionamento->tem_almoco) && $horarioFuncionamento->tem_almoco) {
                    $almocoInicio = Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . substr($horarioFuncionamento->almoco_inicio, 0, 8));
                    $almocoFim = Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . substr($horarioFuncionamento->almoco_fim, 0, 8));
                    
                    if ($current >= $almocoInicio && $current < $almocoFim) {
                        $current->addMinutes($intervalo);
                        continue;
                    }
                }
                
                $horarioFormatado = $current->format('H:i');
                $temAgendamento = in_array($horarioFormatado, $agendamentosOcupados);
                
                // Validação adicional: Verificar se há tempo suficiente para o serviço
                $horarioFimServico = $current->copy()->addMinutes($intervalo);
                $servicoCabeFinal = $horarioFimServico <= $fim;
                
                // Se passa do almoço, verificar se cabe antes do almoço
                if (isset($horarioFuncionamento->tem_almoco) && $horarioFuncionamento->tem_almoco) {
                    $almocoInicio = Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . substr($horarioFuncionamento->almoco_inicio, 0, 8));
                    if ($current < $almocoInicio && $horarioFimServico > $almocoInicio) {
                        $servicoCabeFinal = false;
                    }
                }
                
                if ($servicoCabeFinal) {
                    $horarios[] = [
                        'value' => $horarioFormatado,
                        'display' => $horarioFormatado,
                        'disponivel' => !$temAgendamento,
                        'ocupado' => $temAgendamento,
                        'servico_id' => $this->servico_id // ✅ DEBUG: Incluir ID do serviço
                    ];
                }
                
                $current->addMinutes($intervalo);
            }
            
            $this->horariosDisponiveis = $horarios;
            
        } catch (\Exception $e) {
            $this->mensagemErro = 'Erro ao carregar horários: ' . $e->getMessage();
            $this->horariosDisponiveis = [];
        }
        
        $this->carregandoHorarios = false;
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