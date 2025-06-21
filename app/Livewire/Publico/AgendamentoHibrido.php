<?php

namespace App\Livewire\Publico;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Carbon\Carbon;

class AgendamentoHibrido extends Component
{
    // ETAPAS DO FLUXO
    public $etapaAtual = 1;
    
    // DADOS DO AGENDAMENTO
    public $servico_id = '';
    public $dataAgendamento = '';
    public $horarioAgendamento = '';
    public $observacoes = '';
    
    // DADOS DO USUÁRIO
    public $tipoLogin = '';
    public $email = '';
    public $senha = '';
    public $nome = '';
    public $telefone = '';
    public $senhaConfirmacao = '';
    
    // ESTADOS
    public $carregando = false;
    public $mensagemErro = '';
    public $mensagemSucesso = '';
    
    // DADOS
    public $servicos = [];
    
    // CALENDÁRIO
    public $mesAtual;
    public $anoAtual;
    public $dataSelecionada = '';
    public $diasFuncionamento = [];
    public $carregandoCalendario = false;

    /**
     * Inicializar componente
     */
    public function mount()
    {
        $this->carregarServicos();
        $this->inicializarCalendario();
    }

    /**
     * Inicializar calendário
     */
    public function inicializarCalendario()
    {
        $hoje = now();
        $this->mesAtual = $hoje->month;
        $this->anoAtual = $hoje->year;
        $this->carregarDiasFuncionamento();
    }

    /**
     * Carregar dias de funcionamento DIRETAMENTE do banco
     */
    public function carregarDiasFuncionamento()
    {
        try {
            // Buscar diretamente do banco ao invés de fazer HTTP
            $diasFuncionamento = DB::table('horarios_funcionamento')
                ->where('ativo', 1)
                ->pluck('dia_semana')
                ->unique()
                ->values()
                ->toArray();
            
            if (!empty($diasFuncionamento)) {
                $this->diasFuncionamento = $diasFuncionamento;
            } else {
                // Fallback: segunda a sexta (1-5)
                $this->diasFuncionamento = [1, 2, 3, 4, 5];
            }
            
        } catch (\Exception $e) {
            $this->mensagemErro = 'Aviso: Usando configuração padrão. Erro: ' . $e->getMessage();
            // Fallback: segunda a sexta
            $this->diasFuncionamento = [1, 2, 3, 4, 5];
        }
    }

    /**
     * Verificar se um dia está disponível DIRETAMENTE
     */
    public function isDiaDisponivel($data)
    {
        try {
            $dataCarbon = Carbon::parse($data);
            $diaSemana = $dataCarbon->dayOfWeek;
            
            // PRIMEIRO: Verificar se estabelecimento está aberto neste dia
            $horarioFuncionamento = DB::table('horarios_funcionamento')
                ->where('dia_semana', $diaSemana)
                ->where('ativo', 1)
                ->first();
            
            if (!$horarioFuncionamento) {
                return false; // Fechado neste dia
            }
            
            // SEGUNDO: Verificar bloqueios
            $bloqueado = DB::table('bloqueios_agendamento')
                ->where('ativo', 1)
                ->where(function ($query) use ($dataCarbon) {
                    $query->where('tipo', 'data_completa')
                        ->where(function ($subQuery) use ($dataCarbon) {
                            $subQuery->where('data_inicio', $dataCarbon->format('Y-m-d'))
                                ->orWhere(function ($recurrentQuery) use ($dataCarbon) {
                                    $recurrentQuery->where('recorrente', 1)
                                        ->whereRaw('DATE_FORMAT(data_inicio, "%m-%d") = ?', [$dataCarbon->format('m-d')]);
                                });
                        });
                })
                ->exists();
            
            return !$bloqueado; // Disponível se não estiver bloqueado
            
        } catch (\Exception $e) {
            // Em caso de erro, usar lógica simples
            $diaSemana = Carbon::parse($data)->dayOfWeek;
            return in_array($diaSemana, $this->diasFuncionamento);
        }
    }

    /**
     * Navegar para mês anterior
     */
    public function mesAnterior()
    {
        if ($this->mesAtual == 1) {
            $this->mesAtual = 12;
            $this->anoAtual--;
        } else {
            $this->mesAtual--;
        }
    }

    /**
     * Navegar para próximo mês
     */
    public function mesProximo()
    {
        if ($this->mesAtual == 12) {
            $this->mesAtual = 1;
            $this->anoAtual++;
        } else {
            $this->mesAtual++;
        }
    }

    /**
     * Selecionar data
     */
    public function selecionarData($data)
    {
        $this->dataSelecionada = $data;
        $this->dataAgendamento = $data;
    }

    /**
     * Obter dados do calendário
     */
    public function getDadosCalendarioProperty()
    {
        $primeiroDiaDoMes = Carbon::createFromDate($this->anoAtual, $this->mesAtual, 1);
        $ultimoDiaDoMes = $primeiroDiaDoMes->copy()->endOfMonth();
        $hoje = now()->startOfDay();
        
        // Início da grade (domingo da primeira semana)
        $inicioGrade = $primeiroDiaDoMes->copy()->startOfWeek(0); // 0 = domingo
        
        // Fim da grade (sábado da última semana)
        $fimGrade = $ultimoDiaDoMes->copy()->endOfWeek(6); // 6 = sábado
        
        $dias = [];
        $current = $inicioGrade->copy();
        
        while ($current <= $fimGrade) {
            $isOutroMes = $current->month != $this->mesAtual;
            $isPassado = $current < $hoje;
            $diaSemana = $current->dayOfWeek;
            
            // Verificar se está nos dias de funcionamento
            $isFuncionamento = in_array($diaSemana, $this->diasFuncionamento);
            
            // Verificar disponibilidade (apenas para dias não passados e do mês atual)
            $isDisponivel = false;
            if (!$isOutroMes && !$isPassado && $isFuncionamento) {
                $isDisponivel = $this->isDiaDisponivel($current);
            }
            
            $dias[] = [
                'data' => $current->format('Y-m-d'),
                'dia' => $current->day,
                'isOutroMes' => $isOutroMes,
                'isPassado' => $isPassado,
                'isFuncionamento' => $isFuncionamento,
                'isDisponivel' => $isDisponivel,
                'isSelecionado' => $this->dataSelecionada === $current->format('Y-m-d'),
                'isHoje' => $current->isSameDay($hoje)
            ];
            
            $current->addDay();
        }
        
        return $dias;
    }

    /**
     * Obter nome do mês atual
     */
    public function getNomesMesesProperty()
    {
        return [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
    }

    /**
     * Carregar serviços
     */
    public function carregarServicos()
    {
        try {
            // Buscar da base de dados
            $servicosDB = DB::table('servicos')
                ->where('ativo', 1)
                ->orderBy('nome')
                ->get();
            
            if ($servicosDB->count() > 0) {
                $this->servicos = $servicosDB->map(function ($servico) {
                    return [
                        'id' => $servico->id,
                        'nome' => $servico->nome,
                        'descricao' => $servico->descricao ?? '',
                        'preco' => $servico->preco ?? 0,
                        'duracao' => $servico->duracao ?? 30,
                        'preco_formatado' => 'R$ ' . number_format($servico->preco ?? 0, 2, ',', '.'),
                        'duracao_formatada' => ($servico->duracao ?? 30) . ' min',
                        'display_completo' => $servico->nome . ' - R$ ' . number_format($servico->preco ?? 0, 2, ',', '.') . ' (' . ($servico->duracao ?? 30) . ' min)'
                    ];
                })->toArray();
            } else {
                // Se não houver serviços no banco, usar dados de exemplo
                $this->usarDadosExemplo();
            }
            
        } catch (\Exception $e) {
            $this->mensagemErro = 'Aviso: Usando dados de exemplo. Erro: ' . $e->getMessage();
            $this->usarDadosExemplo();
        }
    }
    
    /**
     * Usar dados de exemplo
     */
    private function usarDadosExemplo()
    {
        $this->servicos = [
            [
                'id' => 1,
                'nome' => 'Consulta Médica',
                'descricao' => 'Consulta médica geral',
                'preco' => 100.00,
                'duracao' => 30,
                'preco_formatado' => 'R$ 100,00',
                'duracao_formatada' => '30 min',
                'display_completo' => 'Consulta Médica - R$ 100,00 (30 min)'
            ],
            [
                'id' => 2,
                'nome' => 'Exame de Sangue',
                'descricao' => 'Coleta de sangue para exames laboratoriais',
                'preco' => 80.00,
                'duracao' => 15,
                'preco_formatado' => 'R$ 80,00',
                'duracao_formatada' => '15 min',
                'display_completo' => 'Exame de Sangue - R$ 80,00 (15 min)'
            ],
            [
                'id' => 3,
                'nome' => 'Consulta Especializada',
                'descricao' => 'Consulta com médico especialista',
                'preco' => 200.00,
                'duracao' => 45,
                'preco_formatado' => 'R$ 200,00',
                'duracao_formatada' => '45 min',
                'display_completo' => 'Consulta Especializada - R$ 200,00 (45 min)'
            ]
        ];
    }

    /**
     * Próxima etapa
     */
    public function proximaEtapa()
    {
        if ($this->etapaAtual == 1) {
            $this->validate([
                'servico_id' => 'required',
                'dataAgendamento' => 'required|date|after:today',
                'horarioAgendamento' => 'required',
            ], [
                'servico_id.required' => 'Selecione um serviço',
                'dataAgendamento.required' => 'Selecione uma data no calendário',
                'dataAgendamento.after' => 'A data deve ser futura',
                'horarioAgendamento.required' => 'Selecione um horário',
            ]);
            
            $this->etapaAtual = 2;
        }
    }
    
    /**
     * Etapa anterior
     */
    public function etapaAnterior()
    {
        if ($this->etapaAtual > 1) {
            $this->etapaAtual--;
        }
    }
    
    /**
     * Obter serviço selecionado
     */
    public function getServicoSelecionadoProperty()
    {
        if (!$this->servico_id || empty($this->servicos)) {
            return null;
        }
        
        return collect($this->servicos)->firstWhere('id', (int)$this->servico_id);
    }

    /**
     * Render
     */
    public function render()
    {
        return view('livewire.publico.agendamento-hibrido', [
            'servicos' => $this->servicos
        ]);
    }
}