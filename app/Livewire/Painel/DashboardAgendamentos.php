<?php

namespace App\Livewire\Painel;

use Livewire\Component;
use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Servico;
use Carbon\Carbon;

class DashboardAgendamentos extends Component
{
    public $agendamentosHoje;
    public $agendamentosAmanha;
    public $totalAgendamentosMes;
    public $agendamentosPendentes;
    public $proximosAgendamentos;

    public function mount()
    {
        $this->carregarDados();
    }

    public function render()
    {
        return view('livewire.painel.dashboard-agendamentos');
    }

    public function confirmarAgendamento($id)
    {
        $agendamento = Agendamento::find($id);
        $agendamento->update(['status' => 'confirmado']);
        
        session()->flash('sucesso', 'Agendamento confirmado com sucesso!');
        $this->carregarDados();
    }

    public function cancelarAgendamento($id)
    {
        $agendamento = Agendamento::find($id);
        $agendamento->update([
            'status' => 'cancelado',
            'data_cancelamento' => now()
        ]);
        
        session()->flash('sucesso', 'Agendamento cancelado com sucesso!');
        $this->carregarDados();
    }

    private function carregarDados()
    {
        $hoje = today();
        $amanha = $hoje->copy()->addDay();
        $inicioMes = $hoje->copy()->startOfMonth();
        $fimMes = $hoje->copy()->endOfMonth();

        // Agendamentos de hoje
        $this->agendamentosHoje = Agendamento::with(['cliente', 'servico'])
            ->whereDate('data_agendamento', $hoje)
            ->whereNotIn('status', ['cancelado'])
            ->orderBy('horario_agendamento')
            ->get();

        // Agendamentos de amanhã
        $this->agendamentosAmanha = Agendamento::with(['cliente', 'servico'])
            ->whereDate('data_agendamento', $amanha)
            ->whereNotIn('status', ['cancelado'])
            ->orderBy('horario_agendamento')
            ->get();

        // Total de agendamentos do mês
        $this->totalAgendamentosMes = Agendamento::whereBetween('data_agendamento', [$inicioMes, $fimMes])
            ->whereNotIn('status', ['cancelado'])
            ->count();

        // Agendamentos pendentes
        $this->agendamentosPendentes = Agendamento::with(['cliente', 'servico'])
            ->where('status', 'pendente')
            ->where('data_agendamento', '>=', $hoje)
            ->orderBy('data_agendamento')
            ->orderBy('horario_agendamento')
            ->take(5)
            ->get();

        // Próximos agendamentos (próximos 7 dias)
        $this->proximosAgendamentos = Agendamento::with(['cliente', 'servico'])
            ->whereBetween('data_agendamento', [$hoje, $hoje->copy()->addDays(7)])
            ->whereIn('status', ['confirmado', 'pendente'])
            ->orderBy('data_agendamento')
            ->orderBy('horario_agendamento')
            ->take(10)
            ->get();
    }

    public function getEstatisticasProperty()
    {
        $hoje = today();
        $inicioMes = $hoje->copy()->startOfMonth();
        $mesAnterior = $hoje->copy()->subMonth();
        $inicioMesAnterior = $mesAnterior->copy()->startOfMonth();
        $fimMesAnterior = $mesAnterior->copy()->endOfMonth();

        return [
            'total_clientes' => Cliente::count(),
            'agendamentos_mes_atual' => Agendamento::whereBetween('data_agendamento', [$inicioMes, $hoje])
                ->whereNotIn('status', ['cancelado'])
                ->count(),
            'agendamentos_mes_anterior' => Agendamento::whereBetween('data_agendamento', [$inicioMesAnterior, $fimMesAnterior])
                ->whereNotIn('status', ['cancelado'])
                ->count(),
            'agendamentos_concluidos_mes' => Agendamento::whereBetween('data_agendamento', [$inicioMes, $hoje])
                ->where('status', 'concluido')
                ->count(),
            'total_servicos_ativos' => Servico::where('ativo', true)->count(),
        ];
    }
}