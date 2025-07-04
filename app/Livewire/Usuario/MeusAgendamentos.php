<?php

namespace App\Livewire\Usuario;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MeusAgendamentos extends Component
{
    use WithPagination;

    // FILTROS
    public $filtroStatus = '';
    public $filtroData = '';
    public $filtroServico = '';
    public $filtroPeriodo = '';
    
    // ESTADOS
    public $mensagemSucesso = '';
    public $mensagemErro = '';
    
    // DADOS
    public $servicos = [];
    public $estatisticas = [];

    public function mount()
    {
        // Verificar autenticação
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Verificar se é usuário comum
        if (!$user->isUsuario()) {
            // Se não é usuário comum, redirecionar para área admin
            if ($user->canAccessAdmin()) {
                return redirect()->route('painel.agendamentos.index');
            }
            abort(403, 'Acesso negado.');
        }
        
        $this->carregarDados();
    }

    private function carregarDados()
    {
        $this->carregarServicos();
        $this->calcularEstatisticas();
    }

    public function getAgendamentosProperty()
    {
        $userId = Auth::id();
        
        $query = DB::table('agendamentos')
            ->join('servicos', 'agendamentos.servico_id', '=', 'servicos.id')
            ->select([
                'agendamentos.*',
                'servicos.nome as servico_nome',
                'servicos.preco as servico_preco',
                'servicos.duracao_minutos as servico_duracao',
            ])
            ->where('agendamentos.user_id', $userId)
            ->where('agendamentos.ativo', 1);

        // Aplicar filtros
        if ($this->filtroStatus) {
            $query->where('agendamentos.status', $this->filtroStatus);
        }
        
        if ($this->filtroData) {
            $query->whereDate('agendamentos.data_agendamento', $this->filtroData);
        }
        
        if ($this->filtroServico) {
            $query->where('agendamentos.servico_id', $this->filtroServico);
        }
        
        if ($this->filtroPeriodo) {
            switch ($this->filtroPeriodo) {
                case 'hoje':
                    $query->whereDate('agendamentos.data_agendamento', today());
                    break;
                case 'semana':
                    $query->whereBetween('agendamentos.data_agendamento', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ]);
                    break;
                case 'mes':
                    $query->whereMonth('agendamentos.data_agendamento', now()->month)
                          ->whereYear('agendamentos.data_agendamento', now()->year);
                    break;
            }
        }

        return $query->orderBy('agendamentos.data_agendamento', 'desc')
                    ->orderBy('agendamentos.horario_agendamento', 'desc')
                    ->paginate(8);
    }

    public function carregarServicos()
    {
        try {
            $this->servicos = DB::table('servicos')
                ->where('ativo', 1)
                ->orderBy('nome')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            $this->servicos = [];
        }
    }

    public function calcularEstatisticas()
    {
        try {
            $userId = Auth::id();
            
            $this->estatisticas = [
                'total' => DB::table('agendamentos')
                    ->where('user_id', $userId)
                    ->where('ativo', 1)
                    ->count(),
                    
                'pendentes' => DB::table('agendamentos')
                    ->where('user_id', $userId)
                    ->where('status', 'pendente')
                    ->where('ativo', 1)
                    ->count(),
                    
                'confirmados' => DB::table('agendamentos')
                    ->where('user_id', $userId)
                    ->where('status', 'confirmado')
                    ->where('ativo', 1)
                    ->count(),
                    
                'concluidos' => DB::table('agendamentos')
                    ->where('user_id', $userId)
                    ->where('status', 'concluido')
                    ->where('ativo', 1)
                    ->count(),
                    
                'cancelados' => DB::table('agendamentos')
                    ->where('user_id', $userId)
                    ->where('status', 'cancelado')
                    ->where('ativo', 1)
                    ->count(),
            ];
        } catch (\Exception $e) {
            $this->estatisticas = [
                'total' => 0, 'pendentes' => 0, 'confirmados' => 0, 
                'concluidos' => 0, 'cancelados' => 0
            ];
        }
    }

    public function confirmarCancelamento($agendamentoId, $motivo)
    {
        try {
            // Buscar agendamento
            $agendamento = DB::table('agendamentos')
                ->where('id', $agendamentoId)
                ->where('user_id', Auth::id())
                ->where('ativo', 1)
                ->first();
                
            if (!$agendamento) {
                $this->mensagemErro = 'Agendamento não encontrado.';
                return;
            }
            
            if (!$this->podeSerCancelado($agendamento)) {
                $this->mensagemErro = 'Este agendamento não pode mais ser cancelado.';
                return;
            }
            
            // Atualizar agendamento
            DB::table('agendamentos')
                ->where('id', $agendamento->id)
                ->update([
                    'status' => 'cancelado',
                    'observacoes_cancelamento' => $motivo,
                    'cancelado_por' => 'usuario',
                    'cancelado_em' => now(),
                    'updated_at' => now()
                ]);
            
            $this->calcularEstatisticas();
            $this->mensagemSucesso = 'Agendamento cancelado com sucesso!';
            
        } catch (\Exception $e) {
            $this->mensagemErro = 'Erro ao cancelar agendamento.';
        }
    }

    public function podeSerCancelado($agendamento)
    {
        if (!in_array($agendamento->status, ['pendente', 'confirmado'])) {
            return false;
        }
        
        $dataHoraAgendamento = Carbon::parse($agendamento->data_agendamento . ' ' . $agendamento->horario_agendamento);
        $horasRestantes = $dataHoraAgendamento->diffInHours(now(), false);
        
        return $horasRestantes >= 2;
    }

    public function podeSerReagendado($agendamento)
    {
        return $this->podeSerCancelado($agendamento);
    }

    public function limparFiltros()
    {
        $this->reset(['filtroStatus', 'filtroData', 'filtroServico', 'filtroPeriodo']);
        $this->resetPage();
    }

    public function updatingFiltroStatus() { $this->resetPage(); }
    public function updatingFiltroData() { $this->resetPage(); }
    public function updatingFiltroServico() { $this->resetPage(); }
    public function updatingFiltroPeriodo() { $this->resetPage(); }

    public function render()
    {
        return view('livewire.usuario.meus-agendamentos', [
            'agendamentos' => $this->agendamentos,
            'servicos' => $this->servicos,
            'estatisticas' => $this->estatisticas
        ])->layout('layouts.cliente'); // ✅ MUDANÇA: usar layout específico para clientes
    }
}