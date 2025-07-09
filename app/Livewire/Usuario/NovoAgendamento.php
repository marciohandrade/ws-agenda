<?php

namespace App\Livewire\Usuario;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NovoAgendamento extends Component
{
    public $etapaAtual = 1;
    public $servicos = [];
    public $mensagemErro = '';
    
    // Variáveis do calendário
    public $mesAtual;
    public $anoAtual;
    public $dataSelecionada = '';
    public $diasFuncionamento = [];
    
    // Variáveis do agendamento
    public $servico_id = '';
    public $dataAgendamento = '';

    public function mount()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user->isUsuario()) {
            if ($user->canAccessAdmin()) {
                return redirect()->route('agendamento.index');
            }
            abort(403, 'Acesso negado.');
        }
        
        $this->carregarServicos();
        $this->inicializarCalendario();
        $this->carregarDiasFuncionamento(); // ⚠️ TESTE: Carregamento do banco
    }

    // ⚠️ TESTE: Carregamento real de serviços do banco
    public function carregarServicos()
    {
        try {
            $this->mensagemErro = 'Tentando carregar serviços do banco...';
            
            $servicosDB = DB::table('servicos')
                ->where('ativo', 1)
                ->orderBy('nome')
                ->get();
            
            $this->mensagemErro = 'Encontrados ' . $servicosDB->count() . ' serviços no banco';
            
            if ($servicosDB->count() > 0) {
                $this->servicos = $servicosDB->map(function ($servico) {
                    $duracao = $servico->duracao_minutos ?? $servico->duracao ?? 30;
                    
                    return [
                        'id' => $servico->id,
                        'nome' => $servico->nome,
                        'descricao' => $servico->descricao ?? '',
                        'preco' => $servico->preco ?? 0,
                        'duracao' => $duracao,
                        'preco_formatado' => 'R$ ' . number_format($servico->preco ?? 0, 2, ',', '.'),
                        'duracao_formatada' => $duracao . ' min',
                        'display_completo' => $servico->nome . ' - R$ ' . number_format($servico->preco ?? 0, 2, ',', '.') . ' (' . $duracao . ' min)'
                    ];
                })->toArray();
            } else {
                $this->usarDadosExemplo();
            }
        } catch (\Exception $e) {
            $this->mensagemErro = 'ERRO no banco de serviços: ' . $e->getMessage();
            $this->usarDadosExemplo();
        }
    }

    // ⚠️ TESTE: Carregamento real dos dias de funcionamento
    public function carregarDiasFuncionamento()
    {
        try {
            $this->mensagemErro .= ' | Carregando dias de funcionamento...';
            
            $diasFuncionamento = DB::table('horarios_funcionamento')
                ->where('ativo', 1)
                ->pluck('dia_semana')
                ->unique()
                ->values()
                ->toArray();
            
            if (!empty($diasFuncionamento)) {
                $this->diasFuncionamento = $diasFuncionamento;
                $this->mensagemErro .= ' | Encontrados dias: ' . implode(',', $diasFuncionamento);
            } else {
                $this->diasFuncionamento = [1, 2, 3, 4, 5, 6]; // Fallback
                $this->mensagemErro .= ' | Usando dias padrão (sem dados no banco)';
            }
            
        } catch (\Exception $e) {
            $this->mensagemErro .= ' | ERRO dias funcionamento: ' . $e->getMessage();
            $this->diasFuncionamento = [1, 2, 3, 4, 5, 6]; // Fallback
        }
    }

    // ⚠️ TESTE: Verificação de dia disponível com banco
    public function isDiaDisponivel($data)
    {
        try {
            $dataCarbon = Carbon::parse($data);
            $diaSemana = $dataCarbon->dayOfWeek;
            
            // Verificação simples primeiro
            if (!in_array($diaSemana, $this->diasFuncionamento)) {
                return false;
            }
            
            // ⚠️ TESTE: Query no banco pode causar problema
            $horarioFuncionamento = DB::table('horarios_funcionamento')
                ->where('dia_semana', $diaSemana)
                ->where('ativo', 1)
                ->first();
            
            if (!$horarioFuncionamento) {
                return false;
            }
            
            // ⚠️ TESTE: Outra query no banco
            $bloqueado = DB::table('bloqueios_agendamento')
                ->where('ativo', 1)
                ->where(function ($query) use ($dataCarbon) {
                    $query->where('tipo', 'data_completa')
                        ->where(function ($subQuery) use ($dataCarbon) {
                            $subQuery->where('data_inicio', $dataCarbon->format('Y-m-d'));
                        });
                })
                ->exists();
            
            return !$bloqueado;
            
        } catch (\Exception $e) {
            // Em caso de erro, usa verificação simples
            $diaSemana = Carbon::parse($data)->dayOfWeek;
            return in_array($diaSemana, $this->diasFuncionamento);
        }
    }

    private function usarDadosExemplo()
    {
        $this->servicos = [
            [
                'id' => 1, 'nome' => 'Consulta Teste', 'descricao' => 'Teste',
                'preco' => 100.00, 'duracao' => 30, 'preco_formatado' => 'R$ 100,00',
                'duracao_formatada' => '30 min', 'display_completo' => 'Consulta Teste - R$ 100,00 (30 min)'
            ]
        ];
    }

    public function inicializarCalendario()
    {
        try {
            $hoje = now();
            $this->mesAtual = $hoje->month;
            $this->anoAtual = $hoje->year;
        } catch (\Exception $e) {
            $this->mensagemErro = 'Erro ao inicializar calendário: ' . $e->getMessage();
        }
    }

    public function getNomesMesesProperty()
    {
        return [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
    }

    public function getDadosCalendarioProperty()
    {
        try {
            $primeiroDiaDoMes = Carbon::createFromDate($this->anoAtual, $this->mesAtual, 1);
            $ultimoDiaDoMes = $primeiroDiaDoMes->copy()->endOfMonth();
            $hoje = now()->startOfDay();
            
            $inicioGrade = $primeiroDiaDoMes->copy()->startOfWeek(0);
            $fimGrade = $ultimoDiaDoMes->copy()->endOfWeek(6);
            
            $dias = [];
            $current = $inicioGrade->copy();
            
            while ($current <= $fimGrade) {
                $isOutroMes = $current->month != $this->mesAtual;
                $isPassado = $current < $hoje;
                $diaSemana = $current->dayOfWeek;
                
                $isFuncionamento = in_array($diaSemana, $this->diasFuncionamento);
                
                // ⚠️ TESTE: Chamada que pode causar muitas queries
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
                
                if (count($dias) > 50) {
                    $this->mensagemErro .= ' | Loop detectado no calendário - parado em ' . count($dias) . ' dias';
                    break;
                }
            }
            
            return $dias;
            
        } catch (\Exception $e) {
            $this->mensagemErro .= ' | Erro no grid do calendário: ' . $e->getMessage();
            return [];
        }
    }

    public function selecionarData($data)
    {
        try {
            $this->mensagemErro = 'Selecionando data: ' . $data;
            $this->dataSelecionada = $data;
            $this->dataAgendamento = $data;
            
            // ⚠️ TESTE CRÍTICO: Carregamento de horários
            if ($this->servico_id) {
                $this->mensagemErro .= ' | Tentando carregar horários...';
                $this->carregarHorarios($data);
            }
        } catch (\Exception $e) {
            $this->mensagemErro = 'ERRO em selecionarData: ' . $e->getMessage();
        }
    }

    // ⚠️ PRINCIPAL SUSPEITO: Carregamento de horários
    public function carregarHorarios($data)
    {
        try {
            $this->mensagemErro .= ' | Carregando horários para ' . $data;
            
            $dataCarbon = Carbon::parse($data);
            $diaSemana = $dataCarbon->dayOfWeek;
            
            // Query 1: Horário de funcionamento
            $horarioFuncionamento = DB::table('horarios_funcionamento')
                ->where('dia_semana', $diaSemana)
                ->where('ativo', 1)
                ->first();
            
            if (!$horarioFuncionamento) {
                $this->mensagemErro .= ' | Sem horário de funcionamento para este dia';
                return;
            }
            
            $this->mensagemErro .= ' | Horário funcionamento encontrado';
            
            // ⚠️ ESTE MÉTODO PODE CAUSAR LOOP INFINITO
            $intervalo = $this->getDuracaoServicoSelecionado();
            $this->mensagemErro .= ' | Intervalo: ' . $intervalo . ' min';
            
            $dataStr = $dataCarbon->format('Y-m-d');
            
            // Query 2: Agendamentos ocupados
            $agendamentosOcupados = DB::table('agendamentos')
                ->where('data_agendamento', $dataStr)
                ->where('servico_id', $this->servico_id)
                ->whereIn('status', ['pendente', 'confirmado'])
                ->where('ativo', 1)
                ->pluck('horario_agendamento')
                ->map(function($horario) {
                    return Carbon::parse($horario)->format('H:i');
                })
                ->toArray();
                
            $this->mensagemErro .= ' | Agendamentos ocupados: ' . count($agendamentosOcupados);
            
        } catch (\Exception $e) {
            $this->mensagemErro .= ' | ERRO carregarHorarios: ' . $e->getMessage();
        }
    }

    // ⚠️ MÉTODO SUSPEITO: Pode causar loop infinito
    public function getDuracaoServicoSelecionado()
    {
        try {
            if (!$this->servico_id) {
                return 30;
            }
            
            // ⚠️ ESTA QUERY PODE ESTAR EM LOOP
            $servico = DB::table('servicos')
                ->where('id', $this->servico_id)
                ->where('ativo', 1)
                ->first();
            
            if ($servico) {
                return (int) ($servico->duracao_minutos ?? $servico->duracao ?? 30);
            }
            
            return 30;
        } catch (\Exception $e) {
            return 30;
        }
    }

    // ⚠️ LISTENER SUSPEITO: Pode causar loop quando serviço muda
    public function updatedServicoId()
    {
        try {
            $this->mensagemErro = 'Serviço alterado para: ' . $this->servico_id;
            
            // ⚠️ SE HOUVER DATA SELECIONADA, PODE ENTRAR EM LOOP
            if ($this->dataSelecionada && $this->servico_id) {
                $this->mensagemErro .= ' | Recarregando horários...';
                $this->carregarHorarios($this->dataSelecionada);
            }
        } catch (\Exception $e) {
            $this->mensagemErro = 'ERRO em updatedServicoId: ' . $e->getMessage();
        }
    }

    public function mesAnterior()
    {
        if ($this->mesAtual == 1) {
            $this->mesAtual = 12;
            $this->anoAtual--;
        } else {
            $this->mesAtual--;
        }
    }

    public function mesProximo()
    {
        if ($this->mesAtual == 12) {
            $this->mesAtual = 1;
            $this->anoAtual++;
        } else {
            $this->mesAtual++;
        }
    }

    public function render()
    {
        return view('livewire.usuario.novo-agendamento');
    }
}