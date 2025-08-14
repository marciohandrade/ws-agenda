<?php

namespace App\Livewire\Painel;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Agendamento;
use App\Models\Cliente;
use Carbon\Carbon;

class AgendamentosLista extends Component
{
    use WithPagination;

    // ====== FILTROS OTIMIZADOS ======
    public $filtroCliente = '';
    public $filtroData = '';
    public $filtroStatus = '';
    public $filtroPeriodo = 'hoje'; // hoje, amanha, semana, mes, todos
    
    // ====== ESTADOS DA INTERFACE ======
    public $viewMode = 'cards'; // cards, table
    public $showFiltros = false;
    
    // ====== CONFIGURAÇÕES DE PERFORMANCE ======
    protected $paginationTheme = 'tailwind';
    protected $queryString = [
        'filtroCliente' => ['except' => ''],
        'filtroData' => ['except' => ''],
        'filtroStatus' => ['except' => ''],
        'filtroPeriodo' => ['except' => 'hoje'],
    ];

    // ====== COMPUTED PROPERTIES ======
    public function getAgendamentosProperty()
    {
        return Agendamento::with(['cliente', 'servico'])
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
            ->when($this->filtroPeriodo !== 'todos', function ($query) {
                switch ($this->filtroPeriodo) {
                    case 'hoje':
                        $query->whereDate('data_agendamento', today());
                        break;
                    case 'amanha':
                        $query->whereDate('data_agendamento', today()->addDay());
                        break;
                    case 'semana':
                        $query->whereBetween('data_agendamento', [
                            today(),
                            today()->addWeek()
                        ]);
                        break;
                    case 'mes':
                        $query->whereMonth('data_agendamento', now()->month)
                              ->whereYear('data_agendamento', now()->year);
                        break;
                }
            })
            ->orderBy('data_agendamento', 'asc')
            ->orderBy('horario_agendamento', 'asc')
            ->paginate(10);
    }

    public function getResumoProperty()
    {
        $hoje = today();
        
        return [
            'hoje' => Agendamento::whereDate('data_agendamento', $hoje)->count(),
            'pendentes' => Agendamento::where('status', 'pendente')->count(),
            'confirmados' => Agendamento::where('status', 'confirmado')
                ->whereDate('data_agendamento', '>=', $hoje)->count(),
            'total_mes' => Agendamento::whereMonth('data_agendamento', $hoje->month)
                ->whereYear('data_agendamento', $hoje->year)->count(),
        ];
    }

    // ====== AÇÕES RÁPIDAS ======
    public function alterarStatus($agendamentoId, $novoStatus)
    {
        try {
            $agendamento = Agendamento::findOrFail($agendamentoId);
            $agendamento->update(['status' => $novoStatus]);

            $statusTexto = [
                'confirmado' => 'confirmado',
                'concluido' => 'concluído',
                'cancelado' => 'cancelado'
            ];

            $this->dispatch('toast-sucesso', "Agendamento {$statusTexto[$novoStatus]} com sucesso!");
            
            // ✅ REFRESH OTIMIZADO - SÓ OS DADOS NECESSÁRIOS
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
        $this->reset(['filtroCliente', 'filtroData', 'filtroStatus']);
        $this->filtroPeriodo = 'hoje';
        $this->resetPage();
    }

    public function toggleFiltros()
    {
        $this->showFiltros = !$this->showFiltros;
    }

    public function alterarView($modo)
    {
        $this->viewMode = $modo;
    }

    public function setPeriodo($periodo)
    {
        $this->filtroPeriodo = $periodo;
        $this->filtroData = ''; // Limpa filtro de data específica
        $this->resetPage();
    }

    // ====== LIFECYCLE HOOKS ======
    public function mount()
    {
        // Detecta se é mobile para definir view padrão
        $this->viewMode = request()->header('User-Agent') && 
            preg_match('/Mobile|Android|iPhone/i', request()->header('User-Agent')) ? 'cards' : 'table';
    }

    public function updating($property)
    {
        // Reset página quando filtros mudam
        if (in_array($property, ['filtroCliente', 'filtroData', 'filtroStatus', 'filtroPeriodo'])) {
            $this->resetPage();
        }
    }

    // ====== RENDER ======
    public function render()
    {
        return view('livewire.painel.agendamentos-lista', [
            'agendamentos' => $this->agendamentos,
            'resumo' => $this->resumo,
            'clientes' => Cliente::orderBy('nome')->get(['id', 'nome']), // Só campos necessários
        ])->layout('layouts.painel');
    }
}