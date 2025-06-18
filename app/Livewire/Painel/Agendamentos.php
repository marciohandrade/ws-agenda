<?php

namespace App\Livewire\Painel;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Servico;
use Carbon\Carbon;

class Agendamentos extends Component
{
    use WithPagination;

    // Propriedades do formulário
    public $cliente_id = '';
    public $servico_id = '';
    public $data_agendamento = '';
    public $horario_agendamento = '';
    public $status = 'pendente';
    public $observacoes = '';

    // Propriedades de controle
    public $mostrarModal = false;
    public $editando = false;
    public $agendamentoId = null;

    // Filtros
    public $filtroCliente = '';
    public $filtroData = '';
    public $filtroStatus = '';

    // Dados para selects
    public $clientes;
    public $servicos;

    protected $rules = [
        'cliente_id' => 'required|exists:clientes,id',
        'servico_id' => 'required|exists:servicos,id',
        'data_agendamento' => 'required|date|after_or_equal:today',
        'horario_agendamento' => 'required|date_format:H:i',
        'status' => 'required|in:pendente,confirmado,concluido,cancelado',
        'observacoes' => 'nullable|string|max:1000'
    ];

    protected $messages = [
        'cliente_id.required' => 'Selecione um cliente.',
        'cliente_id.exists' => 'Cliente inválido.',
        'servico_id.required' => 'Selecione um serviço.',
        'servico_id.exists' => 'Serviço inválido.',
        'data_agendamento.required' => 'A data é obrigatória.',
        'data_agendamento.after_or_equal' => 'A data não pode ser anterior a hoje.',
        'horario_agendamento.required' => 'O horário é obrigatório.',
        'horario_agendamento.date_format' => 'O horário deve estar no formato HH:MM.',
        'status.required' => 'O status é obrigatório.',
        'observacoes.max' => 'As observações devem ter no máximo 1000 caracteres.'
    ];

    public function mount()
    {
        $this->carregarDados();
        $this->resetarFormulario(); // Garantir estado inicial limpo
    }

    /**
     * Método para iniciar novo agendamento
     */
    public function novoAgendamento()
    {
        $this->resetarFormulario();
    }

    public function render()
    {
        $agendamentos = Agendamento::with(['cliente', 'servico'])
            ->when($this->filtroCliente, function ($query) {
                $query->whereHas('cliente', function ($q) {
                    $q->where('nome', 'like', '%' . $this->filtroCliente . '%');
                });
            })
            ->when($this->filtroData, function ($query) {
                $query->whereDate('data_agendamento', $this->filtroData);
            })
            ->when($this->filtroStatus, function ($query) {
                $query->where('status', $this->filtroStatus);
            })
            ->orderBy('data_agendamento', 'desc')
            ->orderBy('horario_agendamento', 'desc')
            ->paginate(15);
            
        return view('livewire.painel.agendamentos', compact('agendamentos'))
            ->layout('layouts.app');
    }

    public function abrirModal()
    {
        $this->resetarFormulario();
        $this->mostrarModal = true;
    }

    public function fecharModal()
    {
        $this->mostrarModal = false;
        $this->resetarFormulario();
    }

    public function salvar()
    {
        $this->validate();

        // Verificar conflito de horário
        if ($this->verificarConflito()) {
            $servico = Servico::find($this->servico_id);
            $duracaoFormatada = $servico ? $servico->duracao_formatada : '';
            $this->addError('horario_agendamento', 
                "Já existe um agendamento para este serviço neste horário. " .
                "Serviço tem duração de {$duracaoFormatada}. Escolha outro horário.");
            return;
        }

        // Validar baseado nas configurações de agendamento
        if (!$this->validarAgendamentoCompleto()) {
            return;
        }

        $dados = [
            'cliente_id' => $this->cliente_id,
            'servico_id' => $this->servico_id,
            'data_agendamento' => $this->data_agendamento,
            'horario_agendamento' => $this->horario_agendamento,
            'status' => $this->status,
            'observacoes' => $this->observacoes,
        ];

        if ($this->editando && $this->agendamentoId) {
            // EDITAR agendamento existente
            $agendamento = Agendamento::find($this->agendamentoId);
            if ($agendamento) {
                $agendamento->update($dados);
                session()->flash('sucesso', 'Agendamento atualizado com sucesso!');
            }
        } else {
            // CRIAR novo agendamento
            Agendamento::create($dados);
            session()->flash('sucesso', 'Agendamento criado com sucesso!');
        }

        $this->resetarFormulario();
    }

    public function editar($id)
    {
        $agendamento = Agendamento::find($id);
        
        if (!$agendamento) {
            session()->flash('erro', 'Agendamento não encontrado.');
            return;
        }
        
        $this->agendamentoId = $agendamento->id;
        $this->cliente_id = $agendamento->cliente_id;
        $this->servico_id = $agendamento->servico_id;
        $this->data_agendamento = $agendamento->data_agendamento->format('Y-m-d');
        $this->horario_agendamento = Carbon::parse($agendamento->horario_agendamento)->format('H:i');
        $this->status = $agendamento->status;
        $this->observacoes = $agendamento->observacoes;
        
        $this->editando = true;
        
        // Limpar erros
        $this->resetErrorBag();
    }

    public function alterarStatus($id, $novoStatus)
    {
        $agendamento = Agendamento::find($id);
        
        $dados = ['status' => $novoStatus];
        
        if ($novoStatus === 'cancelado') {
            $dados['data_cancelamento'] = now();
        }
        
        $agendamento->update($dados);
        
        session()->flash('sucesso', 'Status alterado para: ' . Agendamento::getStatusOptions()[$novoStatus]);
    }

    public function cancelar($id, $motivo = null)
    {
        $agendamento = Agendamento::find($id);
        
        $agendamento->update([
            'status' => 'cancelado',
            'data_cancelamento' => now(),
            'motivo_cancelamento' => $motivo
        ]);
        
        session()->flash('sucesso', 'Agendamento cancelado com sucesso!');
    }

    public function excluir($id)
    {
        $agendamento = Agendamento::find($id);
        $agendamento->delete();
        
        session()->flash('sucesso', 'Agendamento excluído com sucesso!');
    }

    private function verificarConflito()
    {
        if (!$this->data_agendamento || !$this->horario_agendamento || !$this->servico_id) {
            return false;
        }

        // Buscar informações do serviço selecionado
        $servico = Servico::find($this->servico_id);
        if (!$servico || !$servico->duracao_minutos) {
            $this->addError('servico_id', 'Serviço inválido ou sem duração definida.');
            return false;
        }

        // ✅ CORREÇÃO: Validação e limpeza dos dados de data/hora
        try {
            $dataLimpa = Carbon::parse($this->data_agendamento)->format('Y-m-d');
            $horarioLimpo = Carbon::createFromFormat('H:i', $this->horario_agendamento)->format('H:i');
            
            $horarioInicio = Carbon::createFromFormat('Y-m-d H:i', $dataLimpa . ' ' . $horarioLimpo);
            $horarioFim = $horarioInicio->copy()->addMinutes($servico->duracao_minutos);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao processar data/hora do agendamento', [
                'data_agendamento' => $this->data_agendamento,
                'horario_agendamento' => $this->horario_agendamento,
                'erro' => $e->getMessage()
            ]);
            $this->addError('horario_agendamento', 'Formato de data ou horário inválido.');
            return false;
        }

        // Buscar agendamentos do MESMO SERVIÇO na mesma data
        $query = Agendamento::where('data_agendamento', $dataLimpa)
            ->where('servico_id', $this->servico_id) // MESMO SERVIÇO
            ->whereNotIn('status', ['cancelado'])
            ->select('id', 'data_agendamento', 'horario_agendamento'); // ✅ Otimização de performance

        // Se estiver editando, excluir o próprio agendamento
        if ($this->editando && $this->agendamentoId) {
            $query->where('id', '!=', $this->agendamentoId);
        }

        $agendamentosExistentes = $query->get();

        // Verificar sobreposição de horários para o MESMO SERVIÇO
        foreach ($agendamentosExistentes as $agendamentoExistente) {
            try {
                $dataExistente = Carbon::parse($agendamentoExistente->data_agendamento)->format('Y-m-d');
                $horarioExistente = Carbon::parse($agendamentoExistente->horario_agendamento)->format('H:i');
                
                $horarioExistenteInicio = Carbon::createFromFormat('Y-m-d H:i', $dataExistente . ' ' . $horarioExistente);
                $horarioExistenteFim = $horarioExistenteInicio->copy()->addMinutes($servico->duracao_minutos);

                // Verificar se há sobreposição
                if ($this->horariosSobrepoe($horarioInicio, $horarioFim, $horarioExistenteInicio, $horarioExistenteFim)) {
                    // ✅ Log para debugging
                    \Log::info('Conflito de agendamento detectado', [
                        'servico_id' => $this->servico_id,
                        'data' => $dataLimpa,
                        'horario_novo' => $this->horario_agendamento,
                        'agendamento_conflitante' => $agendamentoExistente->id
                    ]);
                    return true;
                }
            } catch (\Exception $e) {
                \Log::error('Erro ao processar agendamento existente', [
                    'agendamento_id' => $agendamentoExistente->id,
                    'erro' => $e->getMessage()
                ]);
                continue; // Pular este agendamento se houver erro
            }
        }

        return false;
    }

    /**
     * Verifica se dois períodos de tempo se sobrepõem
     */
    private function horariosSobrepoe($inicio1, $fim1, $inicio2, $fim2)
    {
        // Dois períodos se sobrepõem se:
        // O início de um está antes do fim do outro E
        // O fim de um está depois do início do outro
        return $inicio1 < $fim2 && $fim1 > $inicio2;
    }

    /**
     * Valida agendamento baseado nas configurações
     */
    private function validarAgendamentoCompleto()
    {
        // Buscar configuração para o perfil administrativo
        $config = \App\Models\ConfiguracaoAgendamento::porPerfil('admin');
        
        if (!$config) {
            $this->addError('data_agendamento', 'Configurações de agendamento não encontradas.');
            return false;
        }

        // 🚨 1. PRIMEIRO: Validar se não é bloqueio/feriado
        if (!$this->validarBloqueios()) {
            return false;
        }

        // 2. Validar dia da semana
        if (!$this->validarDiaSemana($config)) {
            return false;
        }

        // 3. Validar horário de funcionamento
        if (!$this->validarHorarioConfigurado($config)) {
            return false;
        }

        // 4. Validar antecedência
        if (!$this->validarAntecedencia($config)) {
            return false;
        }

        return true;
    }

    /**
     * ✅ NOVA VALIDAÇÃO: Verifica se data/horário não está bloqueado
     */
    private function validarBloqueios()
    {
        if (!$this->data_agendamento || !$this->horario_agendamento) {
            return true;
        }

        try {
            $dataLimpa = Carbon::parse($this->data_agendamento)->format('Y-m-d');
            $horarioLimpo = Carbon::createFromFormat('H:i', $this->horario_agendamento)->format('H:i');
            $dataHoraAgendamento = Carbon::createFromFormat('Y-m-d H:i', $dataLimpa . ' ' . $horarioLimpo);
        } catch (\Exception $e) {
            return true; // Se erro no parsing, deixa outras validações pegarem
        }

        // Buscar bloqueios ativos que afetam o perfil 'admin'
        $bloqueios = \App\Models\BloqueioAgendamento::where('ativo', true)
            ->where(function($query) {
                $query->whereJsonContains('perfis_afetados', 'admin')
                      ->orWhereJsonContains('perfis_afetados', 'todos');
            })
            ->get();

        foreach ($bloqueios as $bloqueio) {
            if ($this->dataHorarioEstaEmBloqueio($dataHoraAgendamento, $bloqueio)) {
                $this->addError('data_agendamento', $this->obterMensagemBloqueio($bloqueio));
                return false;
            }
        }

        return true;
    }

    /**
     * Verifica se uma data/hora específica está em um bloqueio
     */
    private function dataHorarioEstaEmBloqueio($dataHoraAgendamento, $bloqueio)
    {
        $dataAgendamento = $dataHoraAgendamento->format('Y-m-d');
        $horaAgendamento = $dataHoraAgendamento->format('H:i');

        // Bloqueios recorrentes (feriados anuais)
        if ($bloqueio->recorrente) {
            $dataInicioAnoAtual = Carbon::parse($bloqueio->data_inicio)->year($dataHoraAgendamento->year)->format('Y-m-d');
            
            if ($bloqueio->tipo === 'dia_completo') {
                return $dataAgendamento === $dataInicioAnoAtual;
            }
            
            if ($bloqueio->tipo === 'periodo' && $bloqueio->data_fim) {
                $dataFimAnoAtual = Carbon::parse($bloqueio->data_fim)->year($dataHoraAgendamento->year)->format('Y-m-d');
                return $dataAgendamento >= $dataInicioAnoAtual && $dataAgendamento <= $dataFimAnoAtual;
            }
            
            if ($bloqueio->tipo === 'horario_especifico') {
                return $dataAgendamento === $dataInicioAnoAtual && 
                       $horaAgendamento >= $bloqueio->horario_inicio && 
                       $horaAgendamento < $bloqueio->horario_fim;
            }
        }

        // Bloqueios não recorrentes (específicos do ano)
        if ($bloqueio->tipo === 'dia_completo') {
            return $dataAgendamento === $bloqueio->data_inicio;
        }

        if ($bloqueio->tipo === 'periodo' && $bloqueio->data_fim) {
            return $dataAgendamento >= $bloqueio->data_inicio && 
                   $dataAgendamento <= $bloqueio->data_fim;
        }

        if ($bloqueio->tipo === 'horario_especifico') {
            return $dataAgendamento === $bloqueio->data_inicio && 
                   $horaAgendamento >= $bloqueio->horario_inicio && 
                   $horaAgendamento < $bloqueio->horario_fim;
        }

        return false;
    }

    /**
     * Retorna mensagem amigável sobre o bloqueio
     */
    private function obterMensagemBloqueio($bloqueio)
    {
        $motivo = $bloqueio->motivo;
        
        switch ($bloqueio->tipo) {
            case 'dia_completo':
                return "Data indisponível: {$motivo}.";
                
            case 'periodo':
                $inicio = Carbon::parse($bloqueio->data_inicio)->format('d/m');
                $fim = Carbon::parse($bloqueio->data_fim)->format('d/m');
                return "Período indisponível ({$inicio} a {$fim}): {$motivo}.";
                
            case 'horario_especifico':
                return "Horário indisponível ({$bloqueio->horario_inicio} às {$bloqueio->horario_fim}): {$motivo}.";
                
            default:
                return "Data/horário indisponível: {$motivo}.";
        }
    }

    /**
     * Valida se o dia da semana está ativo - USA APENAS HORARIOS_FUNCIONAMENTO
     */
    private function validarDiaSemana($config)
    {
        if (!$this->data_agendamento) {
            return true;
        }

        $dataCarbon = Carbon::parse($this->data_agendamento);
        $diaSemana = $dataCarbon->dayOfWeek === 0 ? 7 : $dataCarbon->dayOfWeek;

        // ✅ BUSCAR HORÁRIO ESPECÍFICO DO DIA
        $horarioDia = $config->horarioDia($diaSemana);
        
        if (!$horarioDia) {
            $nomeDia = \App\Models\ConfiguracaoAgendamento::DIAS_SEMANA[$diaSemana] ?? 'este dia';
            $this->addError('data_agendamento', "Não atendemos em {$nomeDia}. Escolha outro dia.");
            return false;
        }

        return true;
    }

    /**
     * Valida se o horário está dentro do funcionamento configurado POR DIA
     */
    private function validarHorarioConfigurado($config)
    {
        if (!$this->horario_agendamento || !$this->data_agendamento) {
            return true;
        }

        $dataCarbon = Carbon::parse($this->data_agendamento);
        $diaSemana = $dataCarbon->dayOfWeek === 0 ? 7 : $dataCarbon->dayOfWeek;

        // ✅ BUSCAR APENAS HORÁRIO ESPECÍFICO DO DIA
        $horarioDia = $config->horarioDia($diaSemana);
        
        if (!$horarioDia) {
            $nomeDia = \App\Models\ConfiguracaoAgendamento::DIAS_SEMANA[$diaSemana] ?? 'este dia';
            $this->addError('data_agendamento', "Não atendemos em {$nomeDia}.");
            return false;
        }

        $horarioSolicitado = Carbon::createFromFormat('H:i', $this->horario_agendamento);
        $inicio = Carbon::parse($horarioDia->horario_inicio);
        $fim = Carbon::parse($horarioDia->horario_fim);

        // Validar se está dentro do horário de funcionamento do dia específico
        if ($horarioSolicitado < $inicio || $horarioSolicitado >= $fim) {
            $nomeDia = \App\Models\ConfiguracaoAgendamento::DIAS_SEMANA[$diaSemana];
            $this->addError('horario_agendamento', 
                "Em {$nomeDia} atendemos das {$inicio->format('H:i')} às {$fim->format('H:i')}.");
            return false;
        }

        // Validar horário de almoço específico do dia
        if ($horarioDia->tem_almoco && $horarioDia->almoco_inicio && $horarioDia->almoco_fim) {
            $almocoInicio = Carbon::parse($horarioDia->almoco_inicio);
            $almocoFim = Carbon::parse($horarioDia->almoco_fim);
            
            if ($horarioSolicitado >= $almocoInicio && $horarioSolicitado < $almocoFim) {
                $nomeDia = \App\Models\ConfiguracaoAgendamento::DIAS_SEMANA[$diaSemana];
                $this->addError('horario_agendamento', 
                    "Em {$nomeDia} temos pausa para almoço das {$almocoInicio->format('H:i')} às {$almocoFim->format('H:i')}.");
                return false;
            }
        }

        return true;
    }

    /**
     * Valida antecedência mínima e máxima
     */
    private function validarAntecedencia($config)
    {
        if (!$this->data_agendamento || !$this->horario_agendamento) {
            return true;
        }

        try {
            $dataLimpa = Carbon::parse($this->data_agendamento)->format('Y-m-d');
            $horarioLimpo = Carbon::createFromFormat('H:i', $this->horario_agendamento)->format('H:i');
            $dataHoraAgendamento = Carbon::createFromFormat('Y-m-d H:i', $dataLimpa . ' ' . $horarioLimpo);
        } catch (\Exception $e) {
            $this->addError('data_agendamento', 'Formato de data ou horário inválido.');
            return false;
        }

        $agora = now();

        // 1. Verificar se a data/hora é no futuro
        if ($dataHoraAgendamento <= $agora) {
            $this->addError('data_agendamento', 'Data e horário devem ser no futuro.');
            return false;
        }

        // 2. Validar antecedência mínima (horas no futuro)
        if ($config->antecedencia_minima_horas > 0) {
            $horasRestantes = $agora->diffInHours($dataHoraAgendamento, false);
            
            if ($horasRestantes < $config->antecedencia_minima_horas) {
                $this->addError('data_agendamento', 
                    "Agendamento deve ser feito com pelo menos {$config->antecedencia_minima_horas} horas de antecedência.");
                return false;
            }
        }

        // 3. Validar antecedência máxima (dias no futuro)
        if ($config->antecedencia_maxima_dias > 0) {
            $diasRestantes = $agora->diffInDays($dataHoraAgendamento, false);
            
            if ($diasRestantes > $config->antecedencia_maxima_dias) {
                $this->addError('data_agendamento', 
                    "Agendamento não pode ser feito com mais de {$config->antecedencia_maxima_dias} dias de antecedência.");
                return false;
            }
        }

        return true;
    }

    private function carregarDados()
    {
        $this->clientes = Cliente::orderBy('nome')->get();
        $this->servicos = Servico::where('ativo', true)->orderBy('nome')->get();
    }

    private function resetarFormulario()
    {
        $this->cliente_id = '';
        $this->servico_id = '';
        $this->data_agendamento = '';
        $this->horario_agendamento = '';
        $this->status = 'pendente';
        $this->observacoes = '';
        
        // IMPORTANTE: Resetar estado de edição
        $this->editando = false;
        $this->agendamentoId = null;
        
        $this->resetErrorBag();
    }

    public function updatingFiltroCliente()
    {
        $this->resetPage();
    }

    public function updatingFiltroData()
    {
        $this->resetPage();
    }

    public function updatingFiltroStatus()
    {
        $this->resetPage();
    }

    public function limparFiltros()
    {
        $this->filtroCliente = '';
        $this->filtroData = '';
        $this->filtroStatus = '';
        $this->resetPage();
    }
}