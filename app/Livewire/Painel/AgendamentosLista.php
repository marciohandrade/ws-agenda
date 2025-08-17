<?php

namespace App\Livewire\Painel;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Servico;
use Carbon\Carbon;

class AgendamentosLista extends Component
{
    use WithPagination;

    // ====== FILTROS PRINCIPAIS ======
    public $buscaUnificada = ''; // 🔍 Busca inteligente principal
    public $filtroCliente = '';
    public $filtroData = '';
    public $filtroStatus = '';
    public $filtroPeriodo = 'todos'; // 🔧 MUDANÇA: padrão agora é "todos" em vez de "hoje"
    public $filtroServico = '';
    
    // ====== FILTROS AVANÇADOS ======
    public $filtroDataInicio = '';
    public $filtroDataFim = '';
    public $filtroHorarioInicio = '';
    public $filtroHorarioFim = '';
    public $filtroOrdenacao = 'data_asc';
    
    // ====== ESTADOS DA INTERFACE ======
    public $viewMode = 'cards';
    public $showFiltros = false;
    public $showFiltrosAvancados = false;
    public $showStatusSecundarios = false;
    
    // ====== CONFIGURAÇÕES ======
    protected $paginationTheme = 'tailwind';
    protected $queryString = [
        'buscaUnificada' => ['except' => ''],
        'filtroCliente' => ['except' => ''],
        'filtroData' => ['except' => ''],
        'filtroStatus' => ['except' => ''],
        'filtroPeriodo' => ['except' => 'todos'], // 🔧 MUDANÇA: padrão agora é "todos"
        'filtroServico' => ['except' => ''],
        'showStatusSecundarios' => ['except' => false],
    ];

    // ====== COMPUTED PROPERTIES ======
    public function getAgendamentosProperty()
    {
        try {
            // 🔧 QUERY PRINCIPAL 
            $query = Agendamento::with(['cliente:id,nome,telefone', 'servico:id,nome'])
                ->select(['id', 'cliente_id', 'servico_id', 'data_agendamento', 'horario_agendamento', 'status', 'observacoes', 'created_at']);

            // 🧠 BUSCA INTELIGENTE (prioridade máxima)
            if ($this->buscaUnificada) {
                $query = $this->aplicarBuscaInteligente($query, $this->buscaUnificada);
            } else {
                // Filtros individuais apenas se não há busca unificada
                $query = $this->aplicarFiltrosIndividuais($query);
            }

            // Filtros de período sempre aplicados
            $query = $this->aplicarFiltrosPeriodo($query);
            
            // Filtros avançados
            $query = $this->aplicarFiltrosAvancados($query);

            // Ordenação
            $query = $this->aplicarOrdenacao($query);

            // 📱 PAGINAÇÃO MOBILE-FIRST
            $isMobile = $this->detectarMobile();
            $itensPorPagina = $this->getItensPorPagina($isMobile);
            
            $resultado = $query->paginate($itensPorPagina);
            
            return $resultado;
            
        } catch (\Exception $e) {
            \Log::error('Erro na query de agendamentos: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Em caso de erro, retorna paginação vazia
            return new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]), 0, 10, 1, ['path' => request()->url()]
            );
        }
    }

    // 🆕 COMPUTED PROPERTY PARA VERIFICAR SE DEVE MOSTRAR BOTÕES DE ALTERNÂNCIA
    public function getMostrarBotoesViewProperty()
    {
        return !$this->detectarMobile();
    }

    // 🆕 COMPUTED PROPERTY PARA VERIFICAR SE É MOBILE
    public function getIsMobileProperty()
    {
        return $this->detectarMobile();
    }

    // 🆕 MÉTODO OTIMIZADO PARA ITENS POR PÁGINA
    private function getItensPorPagina($isMobile)
    {
        try {
            $config = config('agendamentos.performance.pagination', [
                'mobile' => 8,
                'tablet' => 12,
                'desktop' => 15
            ]);
            
            if ($isMobile) {
                return $config['mobile'] ?? 8;
            }
            
            // Detecta tablet vs desktop baseado na largura da tela
            $userAgent = request()->header('User-Agent', '');
            $isTablet = $userAgent && preg_match('/iPad|Tablet/i', $userAgent);
            
            return $isTablet ? ($config['tablet'] ?? 12) : ($config['desktop'] ?? 15);
        } catch (\Exception $e) {
            // Fallback seguro
            return $isMobile ? 8 : 12;
        }
    }

    private function detectarMobile()
    {
        try {
            $userAgent = request()->header('User-Agent', '');
            return $userAgent && preg_match('/Mobile|Android|iPhone|iPad/i', $userAgent);
        } catch (\Exception $e) {
            return false; // Fallback para desktop
        }
    }

    public function getResumoProperty()
    {
        $hoje = today();
        
        return [
            'hoje' => Agendamento::whereDate('data_agendamento', $hoje)->count(),
            'total_mes' => Agendamento::whereMonth('data_agendamento', $hoje->month)
                ->whereYear('data_agendamento', $hoje->year)->count(),
        ];
    }

    // 🆕 COMPUTED PROPERTY PARA CONTADORES DE PERÍODO
    public function getContadoresPeriodoProperty()
    {
        try {
            $hoje = today();
            $amanha = today()->addDay();
            $fimSemana = today()->addWeek();
            
            return [
                'hoje' => Agendamento::whereDate('data_agendamento', $hoje)->count(),
                'amanha' => Agendamento::whereDate('data_agendamento', $amanha)->count(),
                'semana' => Agendamento::whereBetween('data_agendamento', [$hoje, $fimSemana])->count(),
                'mes' => Agendamento::whereMonth('data_agendamento', $hoje->month)
                    ->whereYear('data_agendamento', $hoje->year)->count(),
                'todos' => Agendamento::count()
            ];
        } catch (\Exception $e) {
            \Log::error('Erro ao calcular contadores de período: ' . $e->getMessage());
            return [
                'hoje' => 0,
                'amanha' => 0,
                'semana' => 0,
                'mes' => 0,
                'todos' => 0
            ];
        }
    }

    // 🆕 MÉTODO PARA OBTER CONFIGURAÇÕES DE STATUS
    public function getStatusConfigProperty()
    {
        try {
            $config = config('agendamentos.status');
            
            // Validação básica da configuração
            if (!$config || !isset($config['principais']) || !isset($config['secundarios'])) {
                return $this->getDefaultStatusConfig();
            }
            
            return $config;
        } catch (\Exception $e) {
            \Log::warning('Erro ao carregar config de status: ' . $e->getMessage());
            return $this->getDefaultStatusConfig();
        }
    }

    // 🆕 CONFIGURAÇÃO PADRÃO CASO O ARQUIVO CONFIG TENHA PROBLEMAS
    private function getDefaultStatusConfig()
    {
        return [
            'principais' => [
                'pendente' => [
                    'label' => 'Pendentes',
                    'emoji' => '📋',
                    'cor' => 'yellow',
                    'forma' => 'circle',
                    'prioridade' => 1,
                    'descricao' => 'Aguardando confirmação',
                    'acoes' => ['confirmar', 'cancelar', 'editar'],
                    'transicoes_permitidas' => ['confirmado', 'cancelado']
                ],
                'confirmado' => [
                    'label' => 'Confirmados',
                    'emoji' => '✅',
                    'cor' => 'green',
                    'forma' => 'circle',
                    'prioridade' => 2,
                    'descricao' => 'Confirmado pelo cliente',
                    'acoes' => ['concluir', 'cancelar', 'editar'],
                    'transicoes_permitidas' => ['concluido', 'cancelado']
                ],
                'concluido' => [
                    'label' => 'Concluídos',
                    'emoji' => '🏁',
                    'cor' => 'blue',
                    'forma' => 'circle',
                    'prioridade' => 3,
                    'descricao' => 'Atendimento realizado',
                    'acoes' => ['ver_detalhes'],
                    'transicoes_permitidas' => []
                ],
                'cancelado' => [
                    'label' => 'Cancelados',
                    'emoji' => '❌',
                    'cor' => 'red',
                    'forma' => 'circle',
                    'prioridade' => 4,
                    'descricao' => 'Cancelado',
                    'acoes' => ['ver_detalhes'],
                    'transicoes_permitidas' => []
                ]
            ],
            'secundarios' => [],
            'cores' => [
                'yellow' => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-800', 'border' => 'border-yellow-200', 'hover' => 'hover:bg-yellow-100', 'ring' => 'ring-yellow-500'],
                'green' => ['bg' => 'bg-green-50', 'text' => 'text-green-800', 'border' => 'border-green-200', 'hover' => 'hover:bg-green-100', 'ring' => 'ring-green-500'],
                'blue' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-800', 'border' => 'border-blue-200', 'hover' => 'hover:bg-blue-100', 'ring' => 'ring-blue-500'],
                'red' => ['bg' => 'bg-red-50', 'text' => 'text-red-800', 'border' => 'border-red-200', 'hover' => 'hover:bg-red-100', 'ring' => 'ring-red-500'],
                'gray' => ['bg' => 'bg-gray-50', 'text' => 'text-gray-800', 'border' => 'border-gray-200', 'hover' => 'hover:bg-gray-100', 'ring' => 'ring-gray-500']
            ],
            'formas' => [
                'circle' => '●',
                'diamond' => '◆',
                'triangle' => '▲',
                'square' => '■'
            ],
            'comportamento' => [
                'mostrar_contadores' => true,
                'validar_transicoes' => false,
                'log_mudancas_status' => false,
                'permitir_transicoes_livres' => true
            ]
        ];
    }

    // ====== MÉTODOS DE BUSCA E FILTROS ======
    private function aplicarBuscaInteligente($query, $busca)
    {
        $busca = trim(strtolower($busca));
        
        // Detecta telefone
        if (preg_match('/^\d{2,}/', $busca)) {
            return $query->whereHas('cliente', function ($q) use ($busca) {
                $q->where('telefone', 'like', '%' . $busca . '%');
            });
        }
        
        // Detecta status (verifica tanto principais quanto secundários)
        $todosStatus = array_merge(
            array_keys($this->statusConfig['principais']),
            array_keys($this->statusConfig['secundarios'])
        );
        
        if (in_array($busca, $todosStatus)) {
            return $query->where('status', $busca);
        }
        
        // Detecta data (DD/MM)
        if (preg_match('/^\d{1,2}\/\d{1,2}/', $busca)) {
            try {
                $data = Carbon::createFromFormat('d/m/Y', $busca . '/' . date('Y'));
                return $query->whereDate('data_agendamento', $data);
            } catch (\Exception $e) {
                // Continua para busca textual
            }
        }
        
        // Busca textual geral
        return $query->where(function ($q) use ($busca) {
            $q->whereHas('cliente', function ($clienteQuery) use ($busca) {
                $clienteQuery->where('nome', 'like', '%' . $busca . '%');
            })
            ->orWhereHas('servico', function ($servicoQuery) use ($busca) {
                $servicoQuery->where('nome', 'like', '%' . $busca . '%');
            })
            ->orWhere('observacoes', 'like', '%' . $busca . '%');
        });
    }

    private function aplicarFiltrosIndividuais($query)
    {
        $query = $query->when($this->filtroCliente, function ($q) {
            $q->whereHas('cliente', function ($clienteQ) {
                $clienteQ->where('nome', 'like', '%' . $this->filtroCliente . '%');
            });
        })
        ->when($this->filtroData, function ($q) {
            $q->whereDate('data_agendamento', $this->filtroData);
        })
        ->when($this->filtroStatus, function ($q) {
            // ✅ ESTE É O FILTRO CRUCIAL PARA OS CLICKS
            $q->where('status', $this->filtroStatus);
        })
        ->when($this->filtroServico, function ($q) {
            $q->where('servico_id', $this->filtroServico);
        });

        return $query;
    }

    private function aplicarFiltrosPeriodo($query)
    {
        switch ($this->filtroPeriodo) {
            case 'hoje':
                return $query->whereDate('data_agendamento', today());
            case 'amanha':
                $amanha = today()->addDay();
                return $query->whereDate('data_agendamento', $amanha);
            case 'semana':
                return $query->whereBetween('data_agendamento', [today(), today()->addWeek()]);
            case 'mes':
                return $query->whereMonth('data_agendamento', now()->month)
                            ->whereYear('data_agendamento', now()->year);
            case 'todos': // 🔧 NOVO CASO
                return $query;
            default:
                return $query; // 'todos'
        }
    }

    private function aplicarFiltrosAvancados($query)
    {
        return $query
            ->when($this->filtroDataInicio, function ($q) {
                $q->whereDate('data_agendamento', '>=', $this->filtroDataInicio);
            })
            ->when($this->filtroDataFim, function ($q) {
                $q->whereDate('data_agendamento', '<=', $this->filtroDataFim);
            })
            ->when($this->filtroHorarioInicio, function ($q) {
                $q->whereTime('horario_agendamento', '>=', $this->filtroHorarioInicio);
            })
            ->when($this->filtroHorarioFim, function ($q) {
                $q->whereTime('horario_agendamento', '<=', $this->filtroHorarioFim);
            });
    }

    private function aplicarOrdenacao($query)
    {
        switch ($this->filtroOrdenacao) {
            case 'data_desc':
                return $query->orderBy('data_agendamento', 'desc')->orderBy('horario_agendamento', 'desc');
            case 'cliente':
                return $query->join('clientes', 'agendamentos.cliente_id', '=', 'clientes.id')
                            ->orderBy('clientes.nome', 'asc')
                            ->select('agendamentos.*');
            case 'status':
                // 🆕 Ordenação inteligente baseada na prioridade configurada
                $statusPrincipais = array_keys($this->statusConfig['principais']);
                $statusSecundarios = array_keys($this->statusConfig['secundarios']);
                $todosStatus = array_merge($statusPrincipais, $statusSecundarios);
                
                return $query->orderByRaw("FIELD(status, '" . implode("','", $todosStatus) . "')");
            default: // 'data_asc'
                return $query->orderBy('data_agendamento', 'asc')->orderBy('horario_agendamento', 'asc');
        }
    }

    // ====== AÇÕES RÁPIDAS ======
    public function alterarStatus($agendamentoId, $novoStatus)
    {
        try {
            $agendamento = Agendamento::findOrFail($agendamentoId);
            
            // 🆕 Valida transição de status se configurado
            if (config('agendamentos.status.comportamento.validar_transicoes', false)) {
                if (!$this->validarTransicao($agendamento->status, $novoStatus)) {
                    $this->dispatch('toast-erro', 'Transição de status não permitida.');
                    return;
                }
            }
            
            $statusAnterior = $agendamento->status;
            $agendamento->update(['status' => $novoStatus]);

            // 🆕 Log da mudança se habilitado
            if (config('agendamentos.status.comportamento.log_mudancas_status', false)) {
                $this->logMudancaStatus($agendamento, $statusAnterior, $novoStatus);
            }

            // 🆕 Obtém texto do status da configuração
            $statusConfig = $this->statusConfig;
            $statusTexto = $statusConfig['principais'][$novoStatus]['label'] ?? 
                          $statusConfig['secundarios'][$novoStatus]['label'] ?? 
                          ucfirst($novoStatus);

            $this->dispatch('toast-sucesso', "Agendamento alterado para '{$statusTexto}' com sucesso!");
            $this->resetPage();
            
            // 🆕 Limpa cache de contadores se habilitado
            if (config('agendamentos.performance.cache_contadores.enabled', false)) {
                $this->limparCacheContadores();
            }
            
        } catch (\Exception $e) {
            $this->dispatch('toast-erro', 'Erro ao alterar status do agendamento.');
        }
    }

    public function excluir($agendamentoId)
    {
        try {
            $agendamento = Agendamento::findOrFail($agendamentoId);
            $agendamento->delete();

            $this->dispatch('toast-sucesso', 'Agendamento excluído com sucesso!');
            $this->resetPage();
            
            // 🆕 Limpa cache de contadores
            if (config('agendamentos.performance.cache_contadores.enabled', false)) {
                $this->limparCacheContadores();
            }
            
        } catch (\Exception $e) {
            $this->dispatch('toast-erro', 'Erro ao excluir agendamento.');
        }
    }

    // ====== FILTROS E NAVEGAÇÃO ======
    public function limparFiltros()
    {
        $this->reset(['buscaUnificada', 'filtroCliente', 'filtroData', 'filtroStatus', 'filtroServico', 
                     'filtroDataInicio', 'filtroDataFim', 'filtroHorarioInicio', 'filtroHorarioFim']);
        $this->filtroPeriodo = 'todos'; // 🔧 MUDANÇA: limpar para "todos" em vez de "hoje"
        $this->filtroOrdenacao = 'data_asc';
        $this->resetPage();
        $this->dispatch('toast-info', 'Todos os filtros foram limpos');
    }

    public function toggleFiltros()
    {
        $this->showFiltros = !$this->showFiltros;
    }

    public function toggleFiltrosAvancados()
    {
        $this->showFiltrosAvancados = !$this->showFiltrosAvancados;
    }

    // 🆕 TOGGLE PARA STATUS SECUNDÁRIOS
    public function toggleStatusSecundarios()
    {
        $this->showStatusSecundarios = !$this->showStatusSecundarios;
    }

    // 🔧 MÉTODO ATUALIZADO PARA ALTERAÇÃO DE VIEW (só funciona se não for mobile)
    public function alterarView($modo)
    {
        // Se for mobile, força cards sempre
        if ($this->detectarMobile()) {
            $this->viewMode = 'cards';
        } else {
            $this->viewMode = $modo;
        }
    }

    public function setPeriodo($periodo)
    {
        $this->filtroPeriodo = $periodo;
        $this->filtroData = ''; // Limpa filtro de data específica
        $this->resetPage();
        
        // 🆕 Dispatch para melhor UX
        $periodoLabels = [
            'todos' => 'Todos os agendamentos',
            'hoje' => 'Agendamentos de hoje',
            'amanha' => 'Agendamentos de amanhã', 
            'semana' => 'Agendamentos desta semana',
            'mes' => 'Agendamentos deste mês'
        ];
        
        $label = $periodoLabels[$periodo] ?? 'Período selecionado';
        $this->dispatch('toast-info', $label);
    }

    // 🆕 MÉTODO ESPECÍFICO PARA FILTROS RÁPIDOS DE PERÍODO
    public function filtrarPorPeriodo($periodo)
    {
        // Se já está no período selecionado, vai para "todos"
        if ($this->filtroPeriodo === $periodo) {
            $this->setPeriodo('todos');
        } else {
            $this->setPeriodo($periodo);
        }
    }

    public function setFiltroRapido($tipo, $valor)
    {
        if ($tipo === 'status') {
            $this->filtroStatus = $valor;
        }
        $this->resetPage();
    }

    // ✅ MÉTODO PRINCIPAL PARA DEFINIR STATUS
    public function setStatus($status)
    {
        // Lógica simples: toggle on/off
        if ($this->filtroStatus === $status) {
            // Se já está filtrando este status, remove o filtro
            $this->filtroStatus = '';
            $this->dispatch('toast-info', 'Filtro removido');
        } else {
            // Se não está filtrando ou está filtrando outro, define este status
            $this->filtroStatus = $status;
            $this->dispatch('toast-info', 'Filtrando por: ' . $status);
        }
        
        // Reset página para ir para primeira página
        $this->resetPage();
        
        // Força atualização da interface
        $this->dispatch('$refresh');
    }

    // 🧪 MÉTODOS DE DEBUG INTEGRADOS
    public function filtrarPorStatus($status)
    {
        $this->filtroStatus = $status;
        $this->resetPage();
        
        $this->dispatch('toast-info', 'Forçando filtro para: ' . $status);
    }

    public function limparFiltroStatus()
    {
        $this->filtroStatus = '';
        $this->resetPage();
        
        $this->dispatch('toast-info', 'Filtro de status removido');
    }

    public function testarQuery()
    {
        $resultados = [];
        
        try {
            // Teste 1: Sem filtros
            $resultados['sem_filtros'] = Agendamento::count();
            
            // Teste 2: Com filtro confirmado
            $resultados['confirmado'] = Agendamento::where('status', 'confirmado')->count();
            
            // Teste 3: Com filtro pendente  
            $resultados['pendente'] = Agendamento::where('status', 'pendente')->count();
            
            // Teste 4: Com filtro cancelado
            $resultados['cancelado'] = Agendamento::where('status', 'cancelado')->count();
            
            // Teste 5: Hoje + Status
            $resultados['hoje_confirmado'] = Agendamento::whereDate('data_agendamento', today())
                ->where('status', 'confirmado')->count();
                
            // Teste 6: Todos os status únicos
            $resultados['status_unicos'] = Agendamento::distinct('status')->pluck('status')->toArray();
            
            // Teste 7: Query atual completa
            $queryAtual = $this->agendamentos;
            $resultados['query_atual'] = $queryAtual->count();
            $resultados['filtro_status_atual'] = $this->filtroStatus;
            
        } catch (\Exception $e) {
            $resultados['erro'] = $e->getMessage();
        }
        
        $message = "Confirmados: {$resultados['confirmado']} | Atual: {$resultados['query_atual']} | Filtro: {$resultados['filtro_status_atual']}";
        $this->dispatch('toast-info', $message);
        
        return $resultados;
    }

    public function debugStatus()
    {
        $debug = [
            'filtroStatus_atual' => $this->filtroStatus,
            'all_properties' => [
                'buscaUnificada' => $this->buscaUnificada,
                'filtroCliente' => $this->filtroCliente,
                'filtroData' => $this->filtroData,
                'filtroStatus' => $this->filtroStatus,
                'filtroPeriodo' => $this->filtroPeriodo,
                'filtroServico' => $this->filtroServico,
            ],
            'query_tests' => [],
            'contadores' => null
        ];
        
        try {
            // Query step by step
            $queryBase = Agendamento::with(['cliente:id,nome,telefone', 'servico:id,nome'])
                ->select(['id', 'cliente_id', 'servico_id', 'data_agendamento', 'horario_agendamento', 'status', 'observacoes', 'created_at']);
            
            $debug['query_tests']['base'] = $queryBase->count();
            
            // Com filtro de status
            $queryStatus = clone $queryBase;
            if ($this->filtroStatus) {
                $queryStatus->where('status', $this->filtroStatus);
            }
            $debug['query_tests']['com_status'] = $queryStatus->count();
            $debug['query_tests']['sql_status'] = $queryStatus->toSql();
            
            // Com filtro de período
            $queryPeriodo = clone $queryBase;
            if ($this->filtroPeriodo === 'hoje') {
                $queryPeriodo->whereDate('data_agendamento', today());
            }
            $debug['query_tests']['com_periodo'] = $queryPeriodo->count();
            
            // Combinado
            $queryCombinado = clone $queryBase;
            if ($this->filtroStatus) {
                $queryCombinado->where('status', $this->filtroStatus);
            }
            if ($this->filtroPeriodo === 'hoje') {
                $queryCombinado->whereDate('data_agendamento', today());
            }
            $debug['query_tests']['combinado'] = $queryCombinado->count();
            $debug['query_tests']['sql_combinado'] = $queryCombinado->toSql();
            
            $debug['contadores'] = $this->getContadoresStatus();
            
        } catch (\Exception $e) {
            $debug['erro'] = $e->getMessage();
        }
        
        $this->dispatch('toast-info', 'Debug executado - verifique logs');
        
        dd($debug);
    }

    // 🆕 MÉTODO PARA EDITAR AGENDAMENTO
    public function editarAgendamento($agendamentoId)
    {
        try {
            $agendamento = Agendamento::findOrFail($agendamentoId);
            
            // Redireciona para página de edição ou abre modal
            $this->dispatch('abrir-modal-edicao', [
                'agendamento' => $agendamento->toArray(),
                'cliente' => $agendamento->cliente->toArray(),
                'servico' => $agendamento->servico->toArray()
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('toast-erro', 'Erro ao carregar dados do agendamento.');
        }
    }

    // 🆕 MÉTODO PARA REAGENDAR AGENDAMENTO
    public function reagendarAgendamento($agendamentoId)
    {
        try {
            $agendamento = Agendamento::findOrFail($agendamentoId);
            
            // Altera status para reagendado e abre modal de reagendamento
            $agendamento->update(['status' => 'reagendado']);
            
            $this->dispatch('abrir-modal-reagendamento', [
                'agendamento' => $agendamento->toArray(),
                'cliente' => $agendamento->cliente->toArray(),
                'servico' => $agendamento->servico->toArray()
            ]);
            
            $this->dispatch('toast-info', 'Agendamento marcado para reagendamento.');
            $this->resetPage();
            
            // Limpa cache se habilitado
            if (config('agendamentos.performance.cache_contadores.enabled', false)) {
                $this->limparCacheContadores();
            }
            
        } catch (\Exception $e) {
            $this->dispatch('toast-erro', 'Erro ao reagendar agendamento.');
        }
    }

    // 🆕 MÉTODO PARA VALIDAR TRANSIÇÕES DE STATUS
    public function validarTransicao($statusAtual, $novoStatus)
    {
        $config = $this->statusConfig;
        $comportamento = $config['comportamento'];
        
        // Se transições livres estão permitidas, permite qualquer mudança
        if ($comportamento['permitir_transicoes_livres']) {
            return true;
        }
        
        // Verifica se a transição está configurada
        $statusInfo = $config['principais'][$statusAtual] ?? $config['secundarios'][$statusAtual] ?? null;
        
        if (!$statusInfo) {
            return false;
        }
        
        $transicoesPermitidas = $statusInfo['transicoes_permitidas'] ?? [];
        
        return in_array($novoStatus, $transicoesPermitidas);
    }

    // ====== LIFECYCLE HOOKS ======
    public function mount()
    {
        // 🔧 MELHORIA: Detecta tipo de device e define view mode apropriado
        $isMobile = $this->detectarMobile();
        
        if ($isMobile) {
            // Mobile sempre cards
            $this->viewMode = 'cards';
        } else {
            // Desktop/Tablet pode usar table como padrão ou cards baseado na preferência
            $this->viewMode = 'table';
        }
    }

    public function updating($property)
    {
        // Reset página quando filtros mudam
        if (in_array($property, ['buscaUnificada', 'filtroCliente', 'filtroData', 'filtroStatus', 'filtroPeriodo', 
                                'filtroServico', 'filtroDataInicio', 'filtroDataFim', 'filtroHorarioInicio', 'filtroHorarioFim'])) {
            $this->resetPage();
        }
    }

    // ====== RENDER OTIMIZADO ======
    public function render()
    {
        // 📊 CONTADORES COM CACHE INTELIGENTE
        $contadores = $this->getContadoresComCache();

        // 🆕 MONTA STATUS PRINCIPAIS USANDO CONFIGURAÇÃO
        $statusPrincipais = $this->montarStatusPrincipais($contadores);
        
        // 🆕 MONTA STATUS SECUNDÁRIOS USANDO CONFIGURAÇÃO  
        $statusSecundarios = $this->montarStatusSecundarios($contadores);
        
        $totalSecundarios = array_sum(array_column($statusSecundarios, 'count'));

        return view('livewire.painel.agendamentos-lista', [
            'agendamentos' => $this->agendamentos,
            'resumo' => $this->resumo,
            'clientes' => Cliente::orderBy('nome')->get(['id', 'nome']),
            'servicos' => Servico::orderBy('nome')->get(['id', 'nome']),
            'statusPrincipais' => $statusPrincipais,
            'statusSecundarios' => $statusSecundarios,
            'totalSecundarios' => $totalSecundarios,
            'statusConfig' => $this->statusConfig, // 🆕 Passa configuração para view
            'contadoresPeriodo' => $this->contadoresPeriodo, // 🆕 Passa contadores de período
        ])->layout('layouts.painel');
    }

    // 🆕 MÉTODOS AUXILIARES PARA MONTAGEM DE STATUS
    private function montarStatusPrincipais($contadores)
    {
        $statusPrincipais = [];
        $config = $this->statusConfig;
        
        foreach ($config['principais'] as $status => $info) {
            $cores = $config['cores'][$info['cor']] ?? $config['cores']['gray'];
            
            $statusPrincipais[$status] = [
                'label' => $info['label'],
                'emoji' => $info['emoji'],
                'count' => $contadores[$status] ?? 0,
                'classes' => $cores,
                'forma' => $config['formas'][$info['forma']] ?? $config['formas']['circle'],
                'descricao' => $info['descricao'] ?? '',
                'prioridade' => $info['prioridade'] ?? 999
            ];
        }
        
        // Ordena por prioridade
        uasort($statusPrincipais, fn($a, $b) => $a['prioridade'] <=> $b['prioridade']);
        
        return $statusPrincipais;
    }

    private function montarStatusSecundarios($contadores)
    {
        $statusSecundarios = [];
        $config = $this->statusConfig;
        
        foreach ($config['secundarios'] as $status => $info) {
            $count = $contadores[$status] ?? 0;
            
            // Só inclui se tiver agendamentos (comportamento configurável)
            if ($count > 0 || !$config['comportamento']['mostrar_contadores']) {
                $cores = $config['cores'][$info['cor']] ?? $config['cores']['gray'];
                
                $statusSecundarios[$status] = [
                    'label' => $info['label'],
                    'emoji' => $info['emoji'],
                    'count' => $count,
                    'classes' => $cores,
                    'forma' => $config['formas'][$info['forma']] ?? $config['formas']['circle'],
                    'descricao' => $info['descricao'] ?? '',
                    'prioridade' => $info['prioridade'] ?? 999
                ];
            }
        }
        
        // Ordena por prioridade
        uasort($statusSecundarios, fn($a, $b) => $a['prioridade'] <=> $b['prioridade']);
        
        return $statusSecundarios;
    }

    // 📊 MÉTODO OTIMIZADO PARA CONTADORES COM CACHE
    private function getContadoresComCache()
    {
        $cacheConfig = config('agendamentos.performance.cache_contadores', ['enabled' => false]);
        
        if (!$cacheConfig['enabled']) {
            return $this->getContadoresStatus();
        }
        
        $cacheKey = ($cacheConfig['key_prefix'] ?? 'agendamentos_contadores_') . md5(serialize([
            $this->buscaUnificada, 
            $this->filtroCliente, 
            $this->filtroData, 
            $this->filtroPeriodo,
            $this->filtroServico
        ]));
        
        return cache()->remember($cacheKey, $cacheConfig['duration'] ?? 300, function () {
            return $this->getContadoresStatus();
        });
    }

    private function getContadoresStatus()
    {
        try {
            return Agendamento::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar contadores de status: ' . $e->getMessage());
            return [];
        }
    }

    // 🆕 MÉTODO PARA LIMPAR CACHE
    private function limparCacheContadores()
    {
        $cacheConfig = config('agendamentos.performance.cache_contadores', ['enabled' => false]);
        
        if (!$cacheConfig['enabled']) {
            return;
        }
        
        // Limpa todos os caches relacionados aos contadores
        cache()->flush(); // Simplificado - em produção, usar padrão mais específico
    }

    // 🆕 MÉTODO PARA LOG DE MUDANÇAS DE STATUS
    private function logMudancaStatus($agendamento, $statusAnterior, $novoStatus)
    {
        if (!config('agendamentos.auditoria.enabled', false)) {
            return;
        }

        try {
            // Implementar sistema de auditoria
            // Pode usar um model de Log ou sistema de auditoria existente
            \Log::info('Mudança de status do agendamento', [
                'agendamento_id' => $agendamento->id,
                'cliente' => $agendamento->cliente->nome,
                'status_anterior' => $statusAnterior,
                'novo_status' => $novoStatus,
                'usuario_id' => auth()->id(),
                'data_alteracao' => now(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        } catch (\Exception $e) {
            // Log do erro mas não interrompe o fluxo
            \Log::error('Erro ao registrar log de mudança de status', [
                'error' => $e->getMessage(),
                'agendamento_id' => $agendamento->id ?? null
            ]);
        }
    }
}