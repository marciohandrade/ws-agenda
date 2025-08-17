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
    public $buscaUnificada = '';
    public $filtroCliente = '';
    public $filtroData = '';
    public $filtroStatus = '';
    public $filtroPeriodo = 'todos'; // ✅ CORRIGIDO: padrão é "todos"
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
        'filtroPeriodo' => ['except' => 'todos'], // ✅ CORRIGIDO
        'filtroServico' => ['except' => ''],
        'showStatusSecundarios' => ['except' => false],
    ];

    // ====== COMPUTED PROPERTIES ======
    public function getAgendamentosProperty()
    {
        try {
            $query = Agendamento::with(['cliente:id,nome,telefone', 'servico:id,nome'])
                ->select(['id', 'cliente_id', 'servico_id', 'data_agendamento', 'horario_agendamento', 'status', 'observacoes', 'created_at']);

            // Busca inteligente (prioridade máxima)
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

            // Paginação
            $isMobile = $this->detectarMobile();
            $itensPorPagina = $this->getItensPorPagina($isMobile);
            
            return $query->paginate($itensPorPagina);
            
        } catch (\Exception $e) {
            // Em caso de erro, retorna paginação vazia
            return new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]), 0, 10, 1, ['path' => request()->url()]
            );
        }
    }

    private function getItensPorPagina($isMobile)
    {
        $config = config('agendamentos.performance.pagination', [
            'mobile' => 8,
            'tablet' => 12,
            'desktop' => 15
        ]);
        
        if ($isMobile) {
            return $config['mobile'] ?? 8;
        }
        
        $userAgent = request()->header('User-Agent', '');
        $isTablet = $userAgent && preg_match('/iPad|Tablet/i', $userAgent);
        
        return $isTablet ? ($config['tablet'] ?? 12) : ($config['desktop'] ?? 15);
    }

    private function detectarMobile()
    {
        $userAgent = request()->header('User-Agent', '');
        return $userAgent && preg_match('/Mobile|Android|iPhone|iPad/i', $userAgent);
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

    public function getStatusConfigProperty()
    {
        try {
            $config = config('agendamentos.status');
            
            if (!$config || !isset($config['principais']) || !isset($config['secundarios'])) {
                return $this->getDefaultStatusConfig();
            }
            
            return $config;
        } catch (\Exception $e) {
            return $this->getDefaultStatusConfig();
        }
    }

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
        
        // Detecta status
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
        return $query
            ->when($this->filtroCliente, function ($q) {
                $q->whereHas('cliente', function ($clienteQ) {
                    $clienteQ->where('nome', 'like', '%' . $this->filtroCliente . '%');
                });
            })
            ->when($this->filtroData, function ($q) {
                $q->whereDate('data_agendamento', $this->filtroData);
            })
            ->when($this->filtroStatus, function ($q) {
                // ✅ FILTRO CRÍTICO PARA OS CLICKS
                $q->where('status', $this->filtroStatus);
            })
            ->when($this->filtroServico, function ($q) {
                $q->where('servico_id', $this->filtroServico);
            });
    }

    private function aplicarFiltrosPeriodo($query)
    {
        switch ($this->filtroPeriodo) {
            case 'hoje':
                return $query->whereDate('data_agendamento', today());
            case 'amanha':
                return $query->whereDate('data_agendamento', today()->addDay());
            case 'semana':
                return $query->whereBetween('data_agendamento', [today(), today()->addWeek()]);
            case 'mes':
                return $query->whereMonth('data_agendamento', now()->month)
                            ->whereYear('data_agendamento', now()->year);
            case 'todos': // ✅ CASO ADICIONADO
                return $query;
            default:
                return $query;
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
            
            $agendamento->update(['status' => $novoStatus]);

            $statusConfig = $this->statusConfig;
            $statusTexto = $statusConfig['principais'][$novoStatus]['label'] ?? 
                          $statusConfig['secundarios'][$novoStatus]['label'] ?? 
                          ucfirst($novoStatus);

            $this->dispatch('toast-sucesso', "Agendamento alterado para '{$statusTexto}' com sucesso!");
            $this->resetPage();
            
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
            
        } catch (\Exception $e) {
            $this->dispatch('toast-erro', 'Erro ao excluir agendamento.');
        }
    }

    // ====== FILTROS E NAVEGAÇÃO ======
    public function limparFiltros()
    {
        $this->reset(['buscaUnificada', 'filtroCliente', 'filtroData', 'filtroStatus', 'filtroServico', 
                     'filtroDataInicio', 'filtroDataFim', 'filtroHorarioInicio', 'filtroHorarioFim']);
        $this->filtroPeriodo = 'todos'; // ✅ CORRIGIDO
        $this->filtroOrdenacao = 'data_asc';
        $this->resetPage();
        $this->dispatch('toast-info', 'Filtros limpos');
    }

    public function toggleFiltros()
    {
        $this->showFiltros = !$this->showFiltros;
    }

    public function toggleFiltrosAvancados()
    {
        $this->showFiltrosAvancados = !$this->showFiltrosAvancados;
    }

    public function toggleStatusSecundarios()
    {
        $this->showStatusSecundarios = !$this->showStatusSecundarios;
    }

    public function alterarView($modo)
    {
        $this->viewMode = $modo;
    }

    public function setPeriodo($periodo)
    {
        $this->filtroPeriodo = $periodo;
        $this->filtroData = '';
        $this->resetPage();
    }

    public function setFiltroRapido($tipo, $valor)
    {
        if ($tipo === 'status') {
            $this->filtroStatus = $valor;
        }
        $this->resetPage();
    }

    // ✅ MÉTODO PRINCIPAL PARA DEFINIR STATUS - LIMPO
    public function setStatus($status)
    {
        // Toggle on/off
        if ($this->filtroStatus === $status) {
            $this->filtroStatus = '';
            $this->dispatch('toast-info', 'Filtro removido');
        } else {
            $this->filtroStatus = $status;
            $this->dispatch('toast-info', 'Filtrando por: ' . $status);
        }
        
        $this->resetPage();
        $this->dispatch('$refresh');
    }

    // 🧪 MÉTODOS DE DEBUG SIMPLIFICADOS
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
            $resultados['total'] = Agendamento::count();
            $resultados['confirmado'] = Agendamento::where('status', 'confirmado')->count();
            $resultados['pendente'] = Agendamento::where('status', 'pendente')->count();
            $resultados['cancelado'] = Agendamento::where('status', 'cancelado')->count();
            $resultados['hoje'] = Agendamento::whereDate('data_agendamento', today())->count();
            $resultados['query_atual'] = $this->agendamentos->count();
            $resultados['filtro_atual'] = $this->filtroStatus ?: 'nenhum';
            
        } catch (\Exception $e) {
            $resultados['erro'] = $e->getMessage();
        }
        
        $message = "Total: {$resultados['total']} | Confirmados: {$resultados['confirmado']} | Atual: {$resultados['query_atual']} | Filtro: {$resultados['filtro_atual']}";
        $this->dispatch('toast-info', $message);
        
        return $resultados;
    }

    public function editarAgendamento($agendamentoId)
    {
        try {
            $agendamento = Agendamento::findOrFail($agendamentoId);
            
            $this->dispatch('abrir-modal-edicao', [
                'agendamento' => $agendamento->toArray(),
                'cliente' => $agendamento->cliente->toArray(),
                'servico' => $agendamento->servico->toArray()
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('toast-erro', 'Erro ao carregar dados do agendamento.');
        }
    }

    public function validarTransicao($statusAtual, $novoStatus)
    {
        $config = $this->statusConfig;
        $comportamento = $config['comportamento'];
        
        if ($comportamento['permitir_transicoes_livres']) {
            return true;
        }
        
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
        $this->viewMode = $this->detectarMobile() ? 'cards' : 'table';
    }

    public function updating($property)
    {
        if (in_array($property, ['buscaUnificada', 'filtroCliente', 'filtroData', 'filtroStatus', 'filtroPeriodo', 
                                'filtroServico', 'filtroDataInicio', 'filtroDataFim', 'filtroHorarioInicio', 'filtroHorarioFim'])) {
            $this->resetPage();
        }
    }

    // ====== RENDER OTIMIZADO ======
    public function render()
    {
        $contadores = $this->getContadoresStatus();
        $statusPrincipais = $this->montarStatusPrincipais($contadores);
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
            'statusConfig' => $this->statusConfig,
        ])->layout('layouts.painel');
    }

    // ====== MÉTODOS AUXILIARES ======
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
        
        uasort($statusPrincipais, fn($a, $b) => $a['prioridade'] <=> $b['prioridade']);
        
        return $statusPrincipais;
    }

    private function montarStatusSecundarios($contadores)
    {
        $statusSecundarios = [];
        $config = $this->statusConfig;
        
        foreach ($config['secundarios'] as $status => $info) {
            $count = $contadores[$status] ?? 0;
            
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
        
        uasort($statusSecundarios, fn($a, $b) => $a['prioridade'] <=> $b['prioridade']);
        
        return $statusSecundarios;
    }

    private function getContadoresStatus()
    {
        try {
            return Agendamento::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}