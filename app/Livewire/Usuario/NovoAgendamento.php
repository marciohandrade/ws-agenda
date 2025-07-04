<?php

namespace App\Livewire\Usuario;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NovoAgendamento extends Component
{
    // Propriedades do formulário
    public $servico_id = '';
    public $data_agendamento = '';
    public $horario_agendamento = '';
    public $observacoes = '';
    
    // Estados
    public $mensagemSucesso = '';
    public $mensagemErro = '';
    public $carregando = false;
    
    // Dados
    public $servicos = [];
    public $horariosDisponiveis = [];
    public $servicoSelecionado = null;

    // Reagendamento (se vier de reagendar)
    public $reagendandoId = null;
    public $agendamentoOriginal = null;

    protected function rules()
    {
        return [
            'servico_id' => 'required|exists:servicos,id',
            'data_agendamento' => [
                'required',
                'date',
                'after_or_equal:today',
                function ($attribute, $value, $fail) {
                    $this->validarDiaFuncionamento($value, $fail);
                }
            ],
            'horario_agendamento' => [
                'required',
                function ($attribute, $value, $fail) {
                    $this->validarHorarioDisponivel($value, $fail);
                }
            ],
            'observacoes' => 'nullable|string|max:500'
        ];
    }

    protected $messages = [
        'servico_id.required' => 'Selecione um serviço.',
        'servico_id.exists' => 'Serviço selecionado não existe.',
        'data_agendamento.required' => 'A data é obrigatória.',
        'data_agendamento.after_or_equal' => 'A data deve ser hoje ou uma data futura.',
        'horario_agendamento.required' => 'O horário é obrigatório.',
        'observacoes.max' => 'As observações não podem ter mais de 500 caracteres.'
    ];

    public function mount()
    {
        // Verificar autenticação
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Verificar se é usuário comum
        if (!$user->isUsuario()) {
            if ($user->canAccessAdmin()) {
                return redirect()->route('painel.agendamentos.index');
            }
            abort(403, 'Acesso negado.');
        }

        // Se está reagendando
        if (request()->has('reagendar')) {
            $this->reagendandoId = request()->get('reagendar');
            $this->carregarAgendamentoParaReagendar();
        }
        
        $this->carregarServicos();
    }

    private function carregarAgendamentoParaReagendar()
    {
        try {
            $this->agendamentoOriginal = DB::table('agendamentos')
                ->join('servicos', 'agendamentos.servico_id', '=', 'servicos.id')
                ->select('agendamentos.*', 'servicos.nome as servico_nome')
                ->where('agendamentos.id', $this->reagendandoId)
                ->where('agendamentos.user_id', Auth::id())
                ->where('agendamentos.ativo', 1)
                ->first();

            if ($this->agendamentoOriginal) {
                $this->servico_id = $this->agendamentoOriginal->servico_id;
                $this->observacoes = $this->agendamentoOriginal->observacoes;
                $this->atualizarServicoSelecionado();
            }
        } catch (\Exception $e) {
            $this->mensagemErro = 'Erro ao carregar agendamento para reagendar.';
        }
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
            $this->mensagemErro = 'Erro ao carregar serviços.';
        }
    }

    public function updatedServicoId()
    {
        $this->atualizarServicoSelecionado();
        $this->resetarHorarios();
    }

    public function updatedDataAgendamento()
    {
        $this->resetarHorarios();
        if ($this->data_agendamento && $this->servico_id) {
            $this->carregarHorariosDisponiveis();
        }
    }

    private function atualizarServicoSelecionado()
    {
        if ($this->servico_id) {
            $this->servicoSelecionado = collect($this->servicos)
                ->firstWhere('id', $this->servico_id);
        } else {
            $this->servicoSelecionado = null;
        }
    }

    private function resetarHorarios()
    {
        $this->horario_agendamento = '';
        $this->horariosDisponiveis = [];
    }

    public function carregarHorariosDisponiveis()
    {
        if (!$this->data_agendamento || !$this->servico_id) {
            return;
        }

        try {
            $data = $this->data_agendamento;
            $servicoId = $this->servico_id;
            
            // Horários de funcionamento (podem vir de configuração)
            $inicioFuncionamento = '08:00';
            $fimFuncionamento = '18:00';
            $intervaloMinutos = 30; // Intervalos de 30 minutos
            
            // Gerar todos os horários possíveis
            $horarios = [];
            $inicio = Carbon::createFromFormat('H:i', $inicioFuncionamento);
            $fim = Carbon::createFromFormat('H:i', $fimFuncionamento);
            
            while ($inicio->lt($fim)) {
                $horarios[] = $inicio->format('H:i');
                $inicio->addMinutes($intervaloMinutos);
            }
            
            // Buscar horários já ocupados
            $horariosOcupados = DB::table('agendamentos')
                ->where('data_agendamento', $data)
                ->where('servico_id', $servicoId)
                ->whereIn('status', ['pendente', 'confirmado'])
                ->where('ativo', 1)
                // Se está reagendando, excluir o agendamento original
                ->when($this->reagendandoId, function($query) {
                    $query->where('id', '!=', $this->reagendandoId);
                })
                ->pluck('horario_agendamento')
                ->map(function($horario) {
                    return Carbon::parse($horario)->format('H:i');
                })
                ->toArray();
            
            // Filtrar horários disponíveis
            $this->horariosDisponiveis = array_diff($horarios, $horariosOcupados);
            
        } catch (\Exception $e) {
            $this->horariosDisponiveis = [];
            $this->mensagemErro = 'Erro ao carregar horários disponíveis.';
        }
    }

    public function salvar()
    {
        $this->carregando = true;
        $this->validate();

        try {
            if ($this->reagendandoId) {
                // Reagendar agendamento existente
                $this->reagendarAgendamento();
            } else {
                // Criar novo agendamento
                $this->criarNovoAgendamento();
            }
            
        } catch (\Exception $e) {
            $this->mensagemErro = 'Erro ao processar agendamento: ' . $e->getMessage();
        } finally {
            $this->carregando = false;
        }
    }

    private function criarNovoAgendamento()
    {
        DB::transaction(function () {
            $agendamentoId = DB::table('agendamentos')->insertGetId([
                'user_id' => Auth::id(),
                'servico_id' => $this->servico_id,
                'data_agendamento' => $this->data_agendamento,
                'horario_agendamento' => $this->horario_agendamento,
                'status' => 'pendente',
                'observacoes' => $this->observacoes,
                'ativo' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Log de auditoria
            $this->criarLogAuditoria($agendamentoId, 'criacao');
        });

        session()->flash('sucesso', 'Agendamento criado com sucesso! Aguarde a confirmação.');
        return redirect()->route('usuario.meus-agendamentos');
    }

    private function reagendarAgendamento()
    {
        DB::transaction(function () {
            DB::table('agendamentos')
                ->where('id', $this->reagendandoId)
                ->update([
                    'servico_id' => $this->servico_id,
                    'data_agendamento' => $this->data_agendamento,
                    'horario_agendamento' => $this->horario_agendamento,
                    'status' => 'pendente', // Volta para pendente
                    'observacoes' => $this->observacoes,
                    'updated_at' => now()
                ]);

            // Log de auditoria
            $this->criarLogAuditoria($this->reagendandoId, 'reagendamento');
        });

        session()->flash('sucesso', 'Agendamento reagendado com sucesso! Aguarde a confirmação.');
        return redirect()->route('usuario.meus-agendamentos');
    }

    private function criarLogAuditoria($agendamentoId, $acao)
    {
        try {
            DB::table('logs_agendamento')->insert([
                'agendamento_id' => $agendamentoId,
                'user_id' => Auth::id(),
                'acao' => $acao,
                'descricao' => "Agendamento {$acao} pelo usuário",
                'dados_antes' => $this->reagendandoId ? json_encode([
                    'data_anterior' => $this->agendamentoOriginal->data_agendamento ?? null,
                    'horario_anterior' => $this->agendamentoOriginal->horario_agendamento ?? null,
                ]) : null,
                'dados_depois' => json_encode([
                    'servico_id' => $this->servico_id,
                    'data_agendamento' => $this->data_agendamento,
                    'horario_agendamento' => $this->horario_agendamento,
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            // Log falhou, mas não deve quebrar o fluxo
        }
    }

    public function cancelar()
    {
        return redirect()->route('usuario.meus-agendamentos');
    }

    // Validações personalizadas
    private function validarDiaFuncionamento($data, $fail)
    {
        try {
            $dataCarbon = Carbon::parse($data);
            // 1 = Segunda, 2 = Terça, ..., 6 = Sábado, 0 = Domingo
            $diasFuncionamento = [1, 2, 3, 4, 5, 6]; // Segunda a Sábado
            
            if (!in_array($dataCarbon->dayOfWeek, $diasFuncionamento)) {
                $fail('Não funcionamos neste dia. Funcionamos de segunda à sábado.');
            }
        } catch (\Exception $e) {
            $fail('Data inválida.');
        }
    }

    private function validarHorarioDisponivel($horario, $fail)
    {
        if (!in_array($horario, $this->horariosDisponiveis)) {
            $fail('Horário não disponível. Selecione um horário disponível.');
        }
    }

    public function render()
    {
        return view('livewire.usuario.novo-agendamento', [
            'servicos' => $this->servicos,
            'servicoSelecionado' => $this->servicoSelecionado,
            'horariosDisponiveis' => $this->horariosDisponiveis,
            'reagendando' => (bool) $this->reagendandoId,
            'agendamentoOriginal' => $this->agendamentoOriginal
        ])->layout('layouts.cliente');
    }
}