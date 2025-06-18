<?php

namespace App\Livewire\Publico;

use Livewire\Component;
use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Servico;
use Carbon\Carbon;

class AgendamentoPublico extends Component
{
    // Estados do formulário
    public $etapa = 1;
    public $agendamentoCriado = false;

    // Dados pessoais (Etapa 1)
    public $nome = '';
    public $email = '';
    public $telefone = '';
    public $data_nascimento = '';
    public $genero = '';
    public $cpf = '';
    public $cep = '';
    public $endereco = '';
    public $numero = '';
    public $complemento = '';

    // Dados do agendamento (Etapa 2)
    public $servico_id = '';
    public $data_agendamento = '';
    public $horario_agendamento = '';
    public $observacoes = '';

    // Dados para controle
    public $servicos;
    public $horariosDisponiveis = [];
    public $agendamentoSalvo = [];

    protected $rules = [
        // Etapa 1 - Dados pessoais
        'nome' => 'required|string|min:3|max:255',
        'email' => 'required|email|max:255',
        'telefone' => 'required|string|min:10|max:15',
        'data_nascimento' => 'nullable|date|before:today',
        'genero' => 'nullable|in:Masculino,Feminino,Não-binário,Prefere não informar',
        'cpf' => 'nullable|string|size:14',
        'cep' => 'nullable|string|size:9',
        'endereco' => 'nullable|string|max:255',
        'numero' => 'nullable|string|max:10',
        'complemento' => 'nullable|string|max:100',

        // Etapa 2 - Agendamento
        'servico_id' => 'required|exists:servicos,id',
        'data_agendamento' => 'required|date|after_or_equal:today',
        'horario_agendamento' => 'required',
        'observacoes' => 'nullable|string|max:1000'
    ];

    protected $messages = [
        // Etapa 1
        'nome.required' => 'O nome é obrigatório.',
        'nome.min' => 'O nome deve ter pelo menos 3 caracteres.',
        'email.required' => 'O e-mail é obrigatório.',
        'email.email' => 'Digite um e-mail válido.',
        'telefone.required' => 'O telefone é obrigatório.',
        'telefone.min' => 'O telefone deve ter pelo menos 10 dígitos.',
        'data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje.',
        'cpf.size' => 'O CPF deve estar no formato: 000.000.000-00',
        'cep.size' => 'O CEP deve estar no formato: 00000-000',

        // Etapa 2
        'servico_id.required' => 'Selecione um serviço.',
        'servico_id.exists' => 'Serviço inválido.',
        'data_agendamento.required' => 'A data é obrigatória.',
        'data_agendamento.after_or_equal' => 'A data não pode ser anterior a hoje.',
        'horario_agendamento.required' => 'O horário é obrigatório.',
        'observacoes.max' => 'As observações devem ter no máximo 1000 caracteres.'
    ];

    public function mount()
    {
        $this->carregarServicos();
        $this->gerarHorariosDisponiveis();
    }

    public function render()
    {
        return view('livewire.publico.agendamento-publico')
            ->layout('layouts.publico'); // Layout específico para área pública
    }

    /**
     * Avança para a próxima etapa após validar dados pessoais
     */
    public function proximaEtapa()
    {
        // Validar apenas campos da etapa 1
        $this->validate([
            'nome' => 'required|string|min:3|max:255',
            'email' => 'required|email|max:255',
            'telefone' => 'required|string|min:10|max:15',
            'data_nascimento' => 'nullable|date|before:today',
            'genero' => 'nullable|in:Masculino,Feminino,Não-binário,Prefere não informar',
            'cpf' => 'nullable|string|size:14',
            'cep' => 'nullable|string|size:9',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:10',
            'complemento' => 'nullable|string|max:100'
        ]);

        $this->etapa = 2;
    }

    /**
     * Volta para a etapa anterior
     */
    public function etapaAnterior()
    {
        $this->etapa = 1;
        $this->resetErrorBag();
    }

    /**
     * Finaliza o agendamento criando cliente e agendamento
     */
    public function finalizarAgendamento()
    {
        // Validar campos da etapa 2
        $this->validate([
            'servico_id' => 'required|exists:servicos,id',
            'data_agendamento' => 'required|date|after_or_equal:today',
            'horario_agendamento' => 'required',
            'observacoes' => 'nullable|string|max:1000'
        ]);

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

        try {
            // 1. Verificar se cliente já existe pelo email
            $cliente = Cliente::where('email', $this->email)->first();

            if (!$cliente) {
                // 2. Criar novo cliente
                $cliente = Cliente::create([
                    'nome' => $this->nome,
                    'email' => $this->email,
                    'telefone' => $this->telefone,
                    'data_nascimento' => $this->data_nascimento ?: null,
                    'genero' => $this->genero ?: null,
                    'cpf' => $this->cpf ? str_replace(['.', '-'], '', $this->cpf) : null,
                    'cep' => $this->cep ? str_replace('-', '', $this->cep) : null,
                    'endereco' => $this->endereco ?: null,
                    'numero' => $this->numero ?: null,
                    'complemento' => $this->complemento ?: null,
                    'ativo' => true
                ]);
            }

            // 3. Criar agendamento
            $agendamento = Agendamento::create([
                'cliente_id' => $cliente->id,
                'servico_id' => $this->servico_id,
                'data_agendamento' => $this->data_agendamento,
                'horario_agendamento' => $this->horario_agendamento,
                'status' => 'pendente',
                'observacoes' => $this->observacoes,
                'cliente_cadastrado_automaticamente' => !Cliente::where('email', $this->email)->exists(),
                'ativo' => true
            ]);

            // 4. Salvar dados para exibição
            $this->agendamentoSalvo = [
                'servico_nome' => $agendamento->servico->nome,
                'data_formatada' => $agendamento->data_agendamento->format('d/m/Y'),
                'horario' => Carbon::parse($agendamento->horario_agendamento)->format('H:i'),
                'status' => 'Pendente para aprovação'
            ];

            $this->agendamentoCriado = true;

        } catch (\Exception $e) {
            session()->flash('erro', 'Erro ao criar agendamento. Tente novamente.');
        }
    }

    /**
     * Valida agendamento baseado nas configurações (versão pública)
     */
    private function validarAgendamentoCompleto()
    {
        // Buscar configuração para o perfil público
        $config = \App\Models\ConfiguracaoAgendamento::porPerfil('publico');
        
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
     * ✅ NOVA VALIDAÇÃO: Verifica se data/horário não está bloqueado (VERSÃO PÚBLICA)
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

        // Buscar bloqueios ativos que afetam o perfil 'publico'
        $bloqueios = \App\Models\BloqueioAgendamento::where('ativo', true)
            ->where(function($query) {
                $query->whereJsonContains('perfis_afetados', 'publico')
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

        // ✅ CORREÇÃO: Validação e limpeza dos dados de data/hora
        try {
            $dataLimpa = Carbon::parse($this->data_agendamento)->format('Y-m-d');
            $horarioLimpo = Carbon::createFromFormat('H:i', $this->horario_agendamento)->format('H:i');
            $dataHoraAgendamento = Carbon::createFromFormat('Y-m-d H:i', $dataLimpa . ' ' . $horarioLimpo);
        } catch (\Exception $e) {
            \Log::error('Erro ao processar data/hora do agendamento público', [
                'data_agendamento' => $this->data_agendamento,
                'horario_agendamento' => $this->horario_agendamento,
                'erro' => $e->getMessage()
            ]);
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

    /**
     * Reinicia o formulário para novo agendamento
     */
    public function novoAgendamento()
    {
        $this->reset();
        $this->carregarServicos();
        $this->gerarHorariosDisponiveis();
    }

    /**
     * Atualiza horários quando data é alterada
     */
    public function updatedDataAgendamento()
    {
        $this->horario_agendamento = '';
        $this->gerarHorariosDisponiveis();
    }

    /**
     * Carrega serviços ativos do banco
     */
    private function carregarServicos()
    {
        $this->servicos = Servico::ativos()
            ->orderBy('nome')
            ->get();
    }

    /**
     * Gera horários disponíveis baseado na configuração do dia específico
     */
    private function gerarHorariosDisponiveis()
    {
        if (!$this->data_agendamento) {
            $this->horariosDisponiveis = [];
            return;
        }

        $dataCarbon = Carbon::parse($this->data_agendamento);
        $diaSemana = $dataCarbon->dayOfWeek === 0 ? 7 : $dataCarbon->dayOfWeek;

        // ✅ BUSCAR CONFIGURAÇÃO PARA O DIA ESPECÍFICO
        $config = \App\Models\ConfiguracaoAgendamento::porPerfil('publico');
        
        if (!$config) {
            $this->horariosDisponiveis = [];
            return;
        }

        $horarioDia = $config->horarioDia($diaSemana);
        
        // Se não tem configuração para este dia ou dia está inativo
        if (!$horarioDia) {
            $this->horariosDisponiveis = [];
            return;
        }

        // ✅ GERAR HORÁRIOS BASEADO NA CONFIGURAÇÃO DO DIA
        $horarios = [];
        $inicio = Carbon::parse($horarioDia->horario_inicio);
        $fim = Carbon::parse($horarioDia->horario_fim);
        
        // Usar intervalo configurável (padrão 30min, mas pode ser configurado)
        $intervaloMinutos = 30; // TODO: Mover para configuração se necessário

        $horarioAtual = $inicio->copy();
        while ($horarioAtual < $fim) {
            $horarioStr = $horarioAtual->format('H:i');
            
            // ✅ VERIFICAR SE NÃO ESTÁ NO HORÁRIO DE ALMOÇO
            $estaNoAlmoco = false;
            if ($horarioDia->tem_almoco && $horarioDia->almoco_inicio && $horarioDia->almoco_fim) {
                $almocoInicio = Carbon::parse($horarioDia->almoco_inicio);
                $almocoFim = Carbon::parse($horarioDia->almoco_fim);
                
                if ($horarioAtual >= $almocoInicio && $horarioAtual < $almocoFim) {
                    $estaNoAlmoco = true;
                }
            }
            
            if (!$estaNoAlmoco) {
                $horarios[] = $horarioStr;
            }
            
            $horarioAtual->addMinutes($intervaloMinutos);
        }

        // ✅ REMOVER HORÁRIOS JÁ AGENDADOS (considerar duração do serviço)
        $this->removerHorariosOcupados($horarios);
    }

    /**
     * Remove horários que já estão ocupados, considerando duração dos serviços
     */
    private function removerHorariosOcupados(&$horarios)
    {
        $agendamentosExistentes = Agendamento::where('data_agendamento', $this->data_agendamento)
            ->whereNotIn('status', ['cancelado'])
            ->with('servico:id,duracao_minutos')
            ->get(['horario_agendamento', 'servico_id']);

        $horariosOcupados = [];

        foreach ($agendamentosExistentes as $agendamento) {
            if ($agendamento->servico && $agendamento->servico->duracao_minutos) {
                $inicioAgendamento = Carbon::parse($this->data_agendamento . ' ' . $agendamento->horario_agendamento);
                $fimAgendamento = $inicioAgendamento->copy()->addMinutes($agendamento->servico->duracao_minutos);

                // Marcar todos os horários ocupados pela duração do serviço
                foreach ($horarios as $horario) {
                    $horarioTeste = Carbon::parse($this->data_agendamento . ' ' . $horario);
                    
                    // Se o horário testado conflita com um agendamento existente
                    if ($horarioTeste >= $inicioAgendamento && $horarioTeste < $fimAgendamento) {
                        $horariosOcupados[] = $horario;
                    }
                }
            }
        }

        // Remover horários ocupados
        $this->horariosDisponiveis = array_diff($horarios, $horariosOcupados);
        $this->horariosDisponiveis = array_values($this->horariosDisponiveis); // Reindexar array
    }

    /**
     * Verifica se há conflito de horário para o mesmo serviço
     */
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
            \Log::error('Erro ao processar data/hora do agendamento público', [
                'data_agendamento' => $this->data_agendamento,
                'horario_agendamento' => $this->horario_agendamento,
                'erro' => $e->getMessage()
            ]);
            $this->addError('horario_agendamento', 'Formato de data ou horário inválido.');
            return false;
        }

        // Buscar agendamentos do MESMO SERVIÇO na mesma data
        $agendamentosExistentes = Agendamento::where('data_agendamento', $dataLimpa)
            ->where('servico_id', $this->servico_id) // MESMO SERVIÇO
            ->whereNotIn('status', ['cancelado'])
            ->select('id', 'data_agendamento', 'horario_agendamento') // ✅ Otimização de performance
            ->get();

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
                    \Log::info('Conflito de agendamento público detectado', [
                        'servico_id' => $this->servico_id,
                        'data' => $dataLimpa,
                        'horario_novo' => $this->horario_agendamento,
                        'agendamento_conflitante' => $agendamentoExistente->id
                    ]);
                    return true;
                }
            } catch (\Exception $e) {
                \Log::error('Erro ao processar agendamento existente no público', [
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
     * Verifica se a data é fim de semana
     */
    public function isWeekend($dateString)
    {
        if (!$dateString) return false;
        
        $date = Carbon::parse($dateString);
        return $date->isWeekend();
    }
}