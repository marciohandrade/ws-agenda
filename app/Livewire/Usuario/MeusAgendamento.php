<?php

namespace App\Livewire\Usuario;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MeusAgendamentos extends Component
{
    use WithPagination;

    // ✅ PROPRIEDADES BÁSICAS
    public $filtroStatus = 'todos';
    public $filtroData = '';
    public $busca = '';
    public $agendamentoDetalhes = null;
    public $mostrarModal = false;
    
    // ✅ PAGINAÇÃO SIMPLES
    protected $paginationTheme = 'tailwind';
    
    /**
     * ✅ RESET PAGINAÇÃO NOS FILTROS
     */
    public function updatingFiltroStatus() { $this->resetPage(); }
    public function updatingFiltroData() { $this->resetPage(); }
    public function updatingBusca() { $this->resetPage(); }

    /**
     * ✅ CARREGAR AGENDAMENTOS - VERSÃO OTIMIZADA
     */
    public function getAgendamentosProperty()
    {
        try {
            $userId = auth()->id();
            if (!$userId) {
                return collect()->paginate(10);
            }

            // ✅ QUERY BÁSICA E OTIMIZADA
            $query = DB::table('agendamentos as a')
                ->leftJoin('servicos as s', 'a.servico_id', '=', 's.id')
                ->where('a.user_id', $userId)
                ->where('a.ativo', 1)
                ->select([
                    'a.id',
                    'a.data_agendamento',
                    'a.horario_agendamento',
                    'a.observacoes',
                    'a.status',
                    'a.created_at',
                    's.nome as servico_nome',
                    's.preco as servico_preco',
                    's.duracao_minutos as servico_duracao'
                ]);

            // ✅ FILTROS SIMPLES
            if ($this->filtroStatus !== 'todos') {
                $query->where('a.status', $this->filtroStatus);
            }

            if ($this->filtroData) {
                $hoje = now();
                switch ($this->filtroData) {
                    case 'hoje':
                        $query->whereDate('a.data_agendamento', $hoje->toDateString());
                        break;
                    case 'semana':
                        $query->whereBetween('a.data_agendamento', [
                            $hoje->startOfWeek()->toDateString(),
                            $hoje->endOfWeek()->toDateString()
                        ]);
                        break;
                    case 'futuros':
                        $query->where('a.data_agendamento', '>=', $hoje->toDateString());
                        break;
                    case 'passados':
                        $query->where('a.data_agendamento', '<', $hoje->toDateString());
                        break;
                }
            }

            if ($this->busca) {
                $busca = '%' . $this->busca . '%';
                $query->where(function ($q) use ($busca) {
                    $q->where('s.nome', 'like', $busca)
                      ->orWhere('a.observacoes', 'like', $busca);
                });
            }

            // ✅ EXECUTAR QUERY
            $agendamentos = $query->orderBy('a.data_agendamento', 'desc')
                                  ->orderBy('a.horario_agendamento', 'desc')
                                  ->paginate(8);

            // ✅ PROCESSAR DADOS DE FORMA SIMPLES
            $agendamentos->getCollection()->transform(function ($agendamento) {
                return $this->processarAgendamento($agendamento);
            });

            return $agendamentos;

        } catch (\Exception $e) {
            // ✅ SEM LOGS QUE CAUSAM LOOP - APENAS RETORNO VAZIO
            return collect()->paginate(10);
        }
    }

    /**
     * ✅ PROCESSAR AGENDAMENTO - VERSÃO SIMPLES
     */
    private function processarAgendamento($agendamento)
    {
        $dataAgendamento = Carbon::parse($agendamento->data_agendamento);
        $horarioAgendamento = Carbon::parse($agendamento->horario_agendamento);
        $agora = now();

        return (object) [
            'id' => $agendamento->id,
            'codigo' => '#' . str_pad($agendamento->id, 6, '0', STR_PAD_LEFT),
            
            // ✅ DATAS FORMATADAS
            'data_agendamento' => $agendamento->data_agendamento,
            'horario_agendamento' => $agendamento->horario_agendamento,
            'data_formatada' => $dataAgendamento->format('d/m/Y'),
            'horario_formatado' => $horarioAgendamento->format('H:i'),
            'data_completa' => $dataAgendamento->format('d/m/Y') . ' às ' . $horarioAgendamento->format('H:i'),
            'dia_semana_pt' => $this->traduzirDiaSemana($dataAgendamento->format('l')),
            
            // ✅ STATUS
            'status' => $agendamento->status,
            'status_cor' => $this->getStatusCor($agendamento->status),
            'status_texto' => $this->getStatusTexto($agendamento->status),
            'status_icone' => $this->getStatusIcone($agendamento->status),
            
            // ✅ CLASSIFICAÇÕES TEMPORAIS
            'is_futuro' => $dataAgendamento->isFuture(),
            'is_hoje' => $dataAgendamento->isToday(),
            'is_passado' => $dataAgendamento->isPast(),
            
            // ✅ SERVIÇO
            'servico_nome' => $agendamento->servico_nome ?? 'Serviço não identificado',
            'servico_preco_formatado' => 'R$ ' . number_format($agendamento->servico_preco ?? 0, 2, ',', '.'),
            'servico_duracao_formatada' => ($agendamento->servico_duracao ?? 30) . ' min',
            
            // ✅ OBSERVAÇÕES
            'observacoes' => $agendamento->observacoes,
            'created_at_formatado' => Carbon::parse($agendamento->created_at)->format('d/m/Y H:i'),
            
            // ✅ AÇÕES
            'pode_cancelar' => $this->podeSerCancelado($agendamento),
        ];
    }

    /**
     * ✅ VERIFICAR SE PODE CANCELAR - SIMPLES
     */
    private function podeSerCancelado($agendamento)
    {
        $dataAgendamento = Carbon::parse($agendamento->data_agendamento);
        return in_array($agendamento->status, ['pendente', 'confirmado']) &&
               $dataAgendamento->isFuture() &&
               $dataAgendamento->diffInHours(now()) >= 24;
    }

    /**
     * ✅ TRADUZIR DIA DA SEMANA
     */
    private function traduzirDiaSemana($diaSemana)
    {
        $dias = [
            'Monday' => 'Segunda-feira',
            'Tuesday' => 'Terça-feira', 
            'Wednesday' => 'Quarta-feira',
            'Thursday' => 'Quinta-feira',
            'Friday' => 'Sexta-feira',
            'Saturday' => 'Sábado',
            'Sunday' => 'Domingo'
        ];
        return $dias[$diaSemana] ?? $diaSemana;
    }

    /**
     * ✅ STATUS HELPERS
     */
    private function getStatusCor($status)
    {
        return match($status) {
            'pendente' => 'yellow',
            'confirmado' => 'green',
            'cancelado' => 'red',
            'concluido' => 'blue',
            default => 'gray'
        };
    }

    private function getStatusTexto($status)
    {
        return match($status) {
            'pendente' => 'Aguardando Confirmação',
            'confirmado' => 'Confirmado',
            'cancelado' => 'Cancelado',
            'concluido' => 'Concluído',
            default => 'Desconhecido'
        };
    }

    private function getStatusIcone($status)
    {
        return match($status) {
            'pendente' => 'fas fa-clock',
            'confirmado' => 'fas fa-check-circle',
            'cancelado' => 'fas fa-times-circle',
            'concluido' => 'fas fa-flag-checkered',
            default => 'fas fa-question-circle'
        };
    }

    /**
     * ✅ VISUALIZAR DETALHES
     */
    public function visualizarDetalhes($agendamentoId)
    {
        try {
            $agendamento = DB::table('agendamentos as a')
                ->leftJoin('servicos as s', 'a.servico_id', '=', 's.id')
                ->where('a.id', $agendamentoId)
                ->where('a.user_id', auth()->id())
                ->select([
                    'a.*',
                    's.nome as servico_nome',
                    's.preco as servico_preco',
                    's.duracao_minutos as servico_duracao'
                ])
                ->first();

            if ($agendamento) {
                $this->agendamentoDetalhes = $this->processarAgendamento($agendamento);
                $this->mostrarModal = true;
            }

        } catch (\Exception $e) {
            // ✅ SEM LOGS - APENAS FALHA SILENCIOSA
        }
    }

    /**
     * ✅ FECHAR MODAL
     */
    public function fecharModalDetalhes()
    {
        $this->mostrarModal = false;
        $this->agendamentoDetalhes = null;
    }

    /**
     * ✅ CANCELAR AGENDAMENTO
     */
    public function cancelarAgendamento($agendamentoId)
    {
        try {
            $agendamento = DB::table('agendamentos')
                ->where('id', $agendamentoId)
                ->where('user_id', auth()->id())
                ->first();

            if ($agendamento && $this->podeSerCancelado($agendamento)) {
                DB::table('agendamentos')
                    ->where('id', $agendamentoId)
                    ->update([
                        'status' => 'cancelado',
                        'updated_at' => now()
                    ]);

                $this->fecharModalDetalhes();
                session()->flash('success', 'Agendamento cancelado com sucesso!');
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao cancelar agendamento.');
        }
    }

    /**
     * ✅ LIMPAR FILTROS
     */
    public function limparFiltros()
    {
        $this->filtroStatus = 'todos';
        $this->filtroData = '';
        $this->busca = '';
        $this->resetPage();
    }

    /**
     * ✅ RENDER
     */
    public function render()
    {
        return view('livewire.usuario.meus-agendamentos', [
            'agendamentos' => $this->agendamentos
        ]);
    }
}