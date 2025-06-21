<?php

namespace App\Livewire\Publico;

use App\Models\Agendamento;
use App\Models\Servico;
use App\Models\BloqueioAgendamento;
use App\Models\HorarioFuncionamento;
use App\Models\ConfiguracaoAgendamento;
use Livewire\Component;
use Carbon\Carbon;

class AgendamentoSimples extends Component
{
    // Dados do agendamento
    public $servico_id = '';
    public $data_agendamento = '';
    public $horario_agendamento = '';
    
    // Dados básicos do cliente
    public $nome = '';
    public $email = '';
    public $telefone = '';
    public $observacoes = '';

    // Dados para interface
    public $servicosDisponiveis = [];
    public $datasDisponiveis = [];
    public $horariosDisponiveis = [];
    public $servicoSelecionado = null;
    
    // Estados
    public $loading = false;
    public $agendamentoCriado = false;
    public $agendamentoId = null;

    protected $rules = [
        'servico_id' => 'required|exists:servicos,id',
        'data_agendamento' => 'required|date|after:today',
        'horario_agendamento' => 'required',
        'nome' => 'required|string|min:2|max:255',
        'email' => 'required|email|max:255',
        'telefone' => 'required|string|min:10|max:20',
        'observacoes' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'servico_id.required' => 'Selecione um serviço',
        'data_agendamento.required' => 'Selecione uma data',
        'data_agendamento.after' => 'A data deve ser futura',
        'horario_agendamento.required' => 'Selecione um horário',
        'nome.required' => 'Informe seu nome',
        'nome.min' => 'Nome deve ter pelo menos 2 caracteres',
        'email.required' => 'Informe seu email',
        'email.email' => 'Email deve ser válido',
        'telefone.required' => 'Informe seu telefone',
        'telefone.min' => 'Telefone deve ter pelo menos 10 dígitos',
    ];

    public function mount()
    {
        $this->carregarServicos();
    }

    public function carregarServicos()
    {
        $this->servicosDisponiveis = Servico::where('ativo', 1)
            ->orderBy('nome')
            ->get()
            ->map(function ($servico) {
                return [
                    'id' => $servico->id,
                    'nome' => $servico->nome,
                    'preco' => number_format($servico->preco, 2, ',', '.'),
                    'duracao' => $servico->duracao_minutos,
                    'display' => $servico->nome . ' - R$ ' . number_format($servico->preco, 2, ',', '.') . ' (' . $servico->duracao_minutos . 'min)'
                ];
            });
    }

    public function updatedServicoId()
    {
        if ($this->servico_id) {
            $this->servicoSelecionado = collect($this->servicosDisponiveis)->firstWhere('id', $this->servico_id);
            $this->carregarDatasDisponiveis();
            // Limpar seleções posteriores
            $this->data_agendamento = '';
            $this->horario_agendamento = '';
            $this->datasDisponiveis = [];
            $this->horariosDisponiveis = [];
        }
    }

    public function updatedDataAgendamento()
    {
        if ($this->data_agendamento) {
            $this->carregarHorariosDisponiveis();
            // Limpar horário anterior
            $this->horario_agendamento = '';
        }
    }

    public function carregarDatasDisponiveis()
    {
        $configuracao = ConfiguracaoAgendamento::where('perfil', 'cliente_cadastrado')
            ->where('ativo', 1)
            ->first();

        if (!$configuracao) {
            $this->datasDisponiveis = [];
            return;
        }

        $dataInicio = Carbon::now()->addHours($configuracao->antecedencia_minima_horas ?? 2);
        $dataFim = Carbon::now()->addDays($configuracao->antecedencia_maxima_dias ?? 60);
        
        $this->datasDisponiveis = [];
        $current = $dataInicio->copy()->startOfDay();

        while ($current <= $dataFim && count($this->datasDisponiveis) < 30) { // Limitar a 30 datas
            if ($this->isDiaDisponivel($current)) {
                $this->datasDisponiveis[] = [
                    'value' => $current->format('Y-m-d'),
                    'display' => $current->format('d/m/Y') . ' - ' . $this->getDiaSemanaPortugues($current->dayOfWeek)
                ];
            }
            $current->addDay();
        }
    }

    public function carregarHorariosDisponiveis()
    {
        if (!$this->data_agendamento) {
            $this->horariosDisponiveis = [];
            return;
        }

        $data = Carbon::parse($this->data_agendamento);
        $diaSemana = $data->dayOfWeek;

        // Buscar horário de funcionamento
        $horarioFuncionamento = HorarioFuncionamento::where('dia_semana', $diaSemana)
            ->where('ativo', 1)
            ->first();

        if (!$horarioFuncionamento) {
            $this->horariosDisponiveis = [];
            return;
        }

        // Gerar horários
        $horarios = $this->gerarHorarios($horarioFuncionamento, $data);
        
        // Remover horários ocupados
        $this->horariosDisponiveis = $this->filtrarHorariosDisponiveis($horarios, $data);
    }

    private function gerarHorarios($horarioFuncionamento, $data)
    {
        $horarios = [];
        $dataStr = $data->format('Y-m-d');
        
        $horaInicio = substr($horarioFuncionamento->horario_inicio, 0, 8);
        $horaFim = substr($horarioFuncionamento->horario_fim, 0, 8);
        
        $inicio = Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . $horaInicio);
        $fim = Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . $horaFim);
        
        $current = $inicio->copy();
        $intervalo = 30; // 30 minutos

        while ($current < $fim) {
            // Verificar horário de almoço
            if ($horarioFuncionamento->tem_almoco) {
                $almocoInicio = Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . substr($horarioFuncionamento->almoco_inicio, 0, 8));
                $almocoFim = Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . substr($horarioFuncionamento->almoco_fim, 0, 8));

                if ($current >= $almocoInicio && $current < $almocoFim) {
                    $current->addMinutes($intervalo);
                    continue;
                }
            }

            $horarios[] = [
                'value' => $current->format('H:i'),
                'display' => $current->format('H:i')
            ];

            $current->addMinutes($intervalo);
        }

        return $horarios;
    }

    private function filtrarHorariosDisponiveis($horarios, $data)
    {
        $dataStr = $data->format('Y-m-d');
        
        // Buscar agendamentos existentes
        $agendamentosExistentes = Agendamento::where('data_agendamento', $dataStr)
            ->whereIn('status', ['pendente', 'confirmado'])
            ->where('ativo', 1)
            ->get(['horario_agendamento']);

        $horariosOcupados = $agendamentosExistentes->pluck('horario_agendamento')
            ->map(function($horario) {
                return Carbon::parse($horario)->format('H:i');
            })
            ->toArray();

        // Filtrar horários disponíveis
        return array_filter($horarios, function($horario) use ($horariosOcupados) {
            return !in_array($horario['value'], $horariosOcupados);
        });
    }

    private function isDiaDisponivel($data)
    {
        $diaSemana = $data->dayOfWeek;
        
        // Verificar horário de funcionamento
        $horarioFuncionamento = HorarioFuncionamento::where('dia_semana', $diaSemana)
            ->where('ativo', 1)
            ->exists();

        if (!$horarioFuncionamento) {
            return false;
        }

        // Verificar bloqueios
        return !$this->isDiaBloqueado($data);
    }

    private function isDiaBloqueado($data)
    {
        $bloqueios = BloqueioAgendamento::where('ativo', 1)
            ->where(function ($query) use ($data) {
                $query->where('tipo', 'data_completa')
                    ->where(function ($subQuery) use ($data) {
                        $subQuery->where('data_inicio', $data->format('Y-m-d'))
                            ->orWhere(function ($recurrentQuery) use ($data) {
                                $recurrentQuery->where('recorrente', 1)
                                    ->whereRaw('DATE_FORMAT(data_inicio, "%m-%d") = ?', [$data->format('m-d')]);
                            });
                    });
            })
            ->where(function ($query) {
                $query->whereRaw('JSON_CONTAINS(perfis_afetados, ?)', ['"cliente_cadastrado"'])
                    ->orWhereRaw('JSON_CONTAINS(perfis_afetados, ?)', ['"publico"']);
            })
            ->exists();

        return $bloqueios;
    }

    public function confirmarAgendamento()
    {
        $this->loading = true;

        try {
            $this->validate();

            // Verificar disponibilidade final
            if ($this->verificarConflito()) {
                session()->flash('error', 'Horário não está mais disponível. Selecione outro horário.');
                $this->carregarHorariosDisponiveis();
                $this->loading = false;
                return;
            }

            // Criar agendamento
            $agendamento = Agendamento::create([
                'servico_id' => $this->servico_id,
                'data_agendamento' => $this->data_agendamento,
                'horario_agendamento' => $this->horario_agendamento . ':00',
                'cliente_nome' => $this->nome,
                'cliente_email' => $this->email,
                'cliente_telefone' => $this->telefone,
                'observacoes' => $this->observacoes,
                'status' => 'pendente',
                'origem' => 'publico',
                'cliente_cadastrado_automaticamente' => false,
                'ativo' => true,
            ]);

            $this->agendamentoId = $agendamento->id;
            $this->agendamentoCriado = true;

            session()->flash('success', 'Agendamento realizado com sucesso!');

        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao realizar agendamento. Tente novamente.');
        } finally {
            $this->loading = false;
        }
    }

    private function verificarConflito()
    {
        return Agendamento::where('data_agendamento', $this->data_agendamento)
            ->whereTime('horario_agendamento', $this->horario_agendamento . ':00')
            ->whereIn('status', ['pendente', 'confirmado'])
            ->where('ativo', 1)
            ->exists();
    }

    public function novoAgendamento()
    {
        $this->reset();
        $this->carregarServicos();
    }

    private function getDiaSemanaPortugues($dayOfWeek)
    {
        $dias = [
            0 => 'Domingo',
            1 => 'Segunda',
            2 => 'Terça', 
            3 => 'Quarta',
            4 => 'Quinta',
            5 => 'Sexta',
            6 => 'Sábado'
        ];

        return $dias[$dayOfWeek] ?? '';
    }

    public function render()
    {
        return view('livewire.publico.agendamento-simples');
    }
}