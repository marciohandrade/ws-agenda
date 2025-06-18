<?php

namespace App\Livewire\Painel;

use Livewire\Component;
use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Servico;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardAgendamentos extends Component
{
    // Filtros
    public $filtro_periodo = 'hoje'; // hoje, semana, mes
    public $data_inicio;
    public $data_fim;

    // Propriedades para cache de dados
    public $estatisticas = [];
    public $agendamentosHoje;
    public $agendamentosAmanha;
    public $agendamentosPendentes;
    public $proximosAgendamentos;
    public $totalAgendamentosMes;

    protected $listeners = [
        'agendamentoAtualizado' => 'atualizarDados',
        'refreshDashboard' => 'atualizarDados'
    ];

    public function mount()
    {
        $this->filtro_periodo = 'hoje';
        $this->data_inicio = now()->startOfMonth()->format('Y-m-d');
        $this->data_fim = now()->endOfMonth()->format('Y-m-d');
        $this->carregarDados();
    }

    public function render()
    {
        return view('livewire.painel.dashboard-agendamentos')
            ->layout('layouts.app');
    }

    /**
     * Carrega todos os dados do dashboard
     */
    public function carregarDados()
    {
        // Agendamentos por período
        $this->agendamentosHoje = Agendamento::with(['cliente', 'servico'])
            ->whereDate('data_agendamento', today())
            ->orderBy('horario_agendamento')
            ->get();

        $this->agendamentosAmanha = Agendamento::with(['cliente', 'servico'])
            ->whereDate('data_agendamento', today()->addDay())
            ->orderBy('horario_agendamento')
            ->get();

        $this->agendamentosPendentes = Agendamento::with(['cliente', 'servico'])
            ->where('status', 'pendente')
            ->where('data_agendamento', '>=', today())
            ->orderBy('data_agendamento')
            ->orderBy('horario_agendamento')
            ->limit(10)
            ->get();

        $this->proximosAgendamentos = Agendamento::with(['cliente', 'servico'])
            ->whereBetween('data_agendamento', [today(), today()->addDays(7)])
            ->whereNotIn('status', ['cancelado'])
            ->orderBy('data_agendamento')
            ->orderBy('horario_agendamento')
            ->limit(15)
            ->get();

        // Total do mês atual
        $this->totalAgendamentosMes = Agendamento::whereMonth('data_agendamento', now()->month)
            ->whereYear('data_agendamento', now()->year)
            ->whereNotIn('status', ['cancelado'])
            ->count();

        // Estatísticas gerais
        $this->carregarEstatisticas();
    }

    /**
     * Carrega estatísticas detalhadas
     */
    private function carregarEstatisticas()
    {
        $inicioMes = now()->startOfMonth();
        $fimMes = now()->endOfMonth();

        $this->estatisticas = [
            // Básicas
            'total_clientes' => Cliente::where('ativo', true)->count(),
            'total_servicos_ativos' => Servico::where('ativo', true)->count(),
            'agendamentos_concluidos_mes' => Agendamento::where('status', 'concluido')
                ->whereBetween('data_agendamento', [$inicioMes, $fimMes])
                ->count(),

            // Receita (se serviços têm preço)
            'receita_estimada_mes' => $this->calcularReceitaEstimada($inicioMes, $fimMes),
            'receita_confirmada_mes' => $this->calcularReceitaConfirmada($inicioMes, $fimMes),

            // Taxa de conversão
            'taxa_conversao' => $this->calcularTaxaConversao(),

            // Clientes novos do mês
            'clientes_novos_mes' => Cliente::whereBetween('created_at', [$inicioMes, $fimMes])->count(),

            // Horários mais procurados
            'horarios_populares' => $this->obterHorariosPopulares(),

            // Próximo agendamento
            'proximo_agendamento' => $this->obterProximoAgendamento(),

            // Agendamentos por status
            'agendamentos_por_status' => $this->obterAgendamentosPorStatus(),

            // Serviços mais procurados
            'servicos_populares' => $this->obterServicosPopulares($inicioMes, $fimMes),

            // Crescimento mensal
            'crescimento_mensal' => $this->calcularCrescimentoMensal(),
        ];
    }

    /**
     * Calcula receita estimada do mês
     */
    private function calcularReceitaEstimada($inicio, $fim)
    {
        return Agendamento::join('servicos', 'agendamentos.servico_id', '=', 'servicos.id')
            ->whereBetween('agendamentos.data_agendamento', [$inicio, $fim])
            ->whereNotIn('agendamentos.status', ['cancelado'])
            ->whereNotNull('servicos.preco')
            ->sum('servicos.preco');
    }

    /**
     * Calcula receita confirmada (apenas agendamentos concluídos)
     */
    private function calcularReceitaConfirmada($inicio, $fim)
    {
        return Agendamento::join('servicos', 'agendamentos.servico_id', '=', 'servicos.id')
            ->whereBetween('agendamentos.data_agendamento', [$inicio, $fim])
            ->where('agendamentos.status', 'concluido')
            ->whereNotNull('servicos.preco')
            ->sum('servicos.preco');
    }

    /**
     * Calcula taxa de conversão (pendente → confirmado/concluído)
     */
    private function calcularTaxaConversao()
    {
        $totalAgendamentos = Agendamento::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $agendamentosConvertidos = Agendamento::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereIn('status', ['confirmado', 'concluido'])
            ->count();

        return $totalAgendamentos > 0 ? round(($agendamentosConvertidos / $totalAgendamentos) * 100, 1) : 0;
    }

    /**
     * Obtém horários mais procurados
     */
    private function obterHorariosPopulares()
    {
        return Agendamento::select(
                DB::raw('TIME_FORMAT(horario_agendamento, "%H:%i") as horario'),
                DB::raw('COUNT(*) as total')
            )
            ->whereMonth('data_agendamento', now()->month)
            ->whereYear('data_agendamento', now()->year)
            ->whereNotIn('status', ['cancelado'])
            ->groupBy('horario')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Obtém próximo agendamento
     */
    private function obterProximoAgendamento()
    {
        return Agendamento::with(['cliente', 'servico'])
            ->where('data_agendamento', '>=', now())
            ->whereIn('status', ['pendente', 'confirmado'])
            ->orderBy('data_agendamento')
            ->orderBy('horario_agendamento')
            ->first();
    }

    /**
     * Obtém agendamentos agrupados por status
     */
    private function obterAgendamentosPorStatus()
    {
        return Agendamento::select('status', DB::raw('COUNT(*) as total'))
            ->whereMonth('data_agendamento', now()->month)
            ->whereYear('data_agendamento', now()->year)
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    /**
     * Obtém serviços mais procurados
     */
    private function obterServicosPopulares($inicio, $fim)
    {
        return Agendamento::select('servicos.nome', DB::raw('COUNT(*) as total'))
            ->join('servicos', 'agendamentos.servico_id', '=', 'servicos.id')
            ->whereBetween('agendamentos.data_agendamento', [$inicio, $fim])
            ->whereNotIn('agendamentos.status', ['cancelado'])
            ->groupBy('servicos.id', 'servicos.nome')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Calcula crescimento mensal
     */
    private function calcularCrescimentoMensal()
    {
        $mesAtual = Agendamento::whereMonth('data_agendamento', now()->month)
            ->whereYear('data_agendamento', now()->year)
            ->whereNotIn('status', ['cancelado'])
            ->count();

        $mesAnterior = Agendamento::whereMonth('data_agendamento', now()->subMonth()->month)
            ->whereYear('data_agendamento', now()->subMonth()->year)
            ->whereNotIn('status', ['cancelado'])
            ->count();

        if ($mesAnterior > 0) {
            return round((($mesAtual - $mesAnterior) / $mesAnterior) * 100, 1);
        }

        return $mesAtual > 0 ? 100 : 0;
    }

    /**
     * Confirma um agendamento
     */
    public function confirmarAgendamento($agendamentoId)
    {
        $agendamento = Agendamento::find($agendamentoId);
        
        if ($agendamento && $agendamento->status === 'pendente') {
            $agendamento->update(['status' => 'confirmado']);
            
            session()->flash('sucesso', 'Agendamento confirmado com sucesso!');
            $this->atualizarDados();
            
            // Disparar evento para outros componentes
            $this->dispatch('agendamentoAtualizado');
        }
    }

    /**
     * Cancela um agendamento
     */
    public function cancelarAgendamento($agendamentoId, $motivo = 'Cancelado pelo administrador')
    {
        $agendamento = Agendamento::find($agendamentoId);
        
        if ($agendamento && $agendamento->podeSerCancelado()) {
            $agendamento->update([
                'status' => 'cancelado',
                'data_cancelamento' => now(),
                'motivo_cancelamento' => $motivo
            ]);
            
            session()->flash('sucesso', 'Agendamento cancelado com sucesso!');
            $this->atualizarDados();
            
            // Disparar evento para outros componentes
            $this->dispatch('agendamentoAtualizado');
        }
    }

    /**
     * Marca agendamento como concluído
     */
    public function concluirAgendamento($agendamentoId)
    {
        $agendamento = Agendamento::find($agendamentoId);
        
        if ($agendamento && in_array($agendamento->status, ['confirmado', 'pendente'])) {
            $agendamento->update(['status' => 'concluido']);
            
            session()->flash('sucesso', 'Agendamento marcado como concluído!');
            $this->atualizarDados();
            
            // Disparar evento para outros componentes
            $this->dispatch('agendamentoAtualizado');
        }
    }

    /**
     * Atualiza os dados do dashboard
     */
    public function atualizarDados()
    {
        $this->carregarDados();
    }

    /**
     * Aplica filtro por período
     */
    public function aplicarFiltro()
    {
        $this->carregarDados();
    }

    /**
     * Reseta filtros
     */
    public function resetarFiltros()
    {
        $this->filtro_periodo = 'hoje';
        $this->data_inicio = now()->startOfMonth()->format('Y-m-d');
        $this->data_fim = now()->endOfMonth()->format('Y-m-d');
        $this->carregarDados();
    }

    /**
     * Obtém dados para gráfico de agendamentos por dia
     */
    public function getDadosGraficoAgendamentos()
    {
        $dados = [];
        $inicio = now()->startOfMonth();
        $fim = now()->endOfMonth();

        for ($data = $inicio->copy(); $data <= $fim; $data->addDay()) {
            $total = Agendamento::whereDate('data_agendamento', $data)
                ->whereNotIn('status', ['cancelado'])
                ->count();
                
            $dados[] = [
                'data' => $data->format('d/m'),
                'total' => $total
            ];
        }

        return $dados;
    }

    /**
     * Exporta relatório em PDF (futura implementação)
     */
    public function exportarRelatorio()
    {
        // Implementar exportação de relatório
        session()->flash('info', 'Funcionalidade em desenvolvimento!');
    }
}