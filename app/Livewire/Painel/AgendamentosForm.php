<?php

namespace App\Livewire\Painel;

use Livewire\Component;
use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Servico;
use Carbon\Carbon;

class AgendamentosForm extends Component
{
    // ====== DADOS DO FORMULÁRIO ======
    public $cliente_id = '';
    public $servico_id = '';
    public $data_agendamento = '';
    public $horario_agendamento = '';
    public $status = 'pendente';
    public $observacoes = '';
    
    // ====== ESTADOS DO COMPONENTE ======
    public $agendamento = null; // Para edição
    public $editando = false;
    public $etapaAtual = 1; // 1=cliente, 2=servico, 3=data/hora, 4=final
    
    // ====== DADOS AUXILIARES ======
    public $clienteSelecionado = null;
    public $servicoSelecionado = null;
    public $horariosDisponiveis = [];
    
    // ====== VALIDAÇÃO PROGRESSIVA ======
    protected function rules()
    {
        $rules = [
            'status' => 'required|in:pendente,confirmado,concluido,cancelado',
            'observacoes' => 'nullable|string|max:500'
        ];

        // Validação por etapa
        if ($this->etapaAtual >= 1) {
            $rules['cliente_id'] = 'required|exists:clientes,id';
        }
        
        if ($this->etapaAtual >= 2) {
            $rules['servico_id'] = 'required|exists:servicos,id';
        }
        
        if ($this->etapaAtual >= 3) {
            $rules['data_agendamento'] = [
                'required',
                'date',
                'after_or_equal:today',
                function ($attribute, $value, $fail) {
                    $this->validarDiaFuncionamento($value, $fail);
                }
            ];
            $rules['horario_agendamento'] = [
                'required',
                function ($attribute, $value, $fail) {
                    $this->validarHorarioFuncionamento($value, $fail);
                }
            ];
        }

        return $rules;
    }

    protected $messages = [
        'cliente_id.required' => 'Selecione um cliente.',
        'servico_id.required' => 'Selecione um serviço.',
        'data_agendamento.required' => 'A data é obrigatória.',
        'data_agendamento.after_or_equal' => 'A data deve ser hoje ou uma data futura.',
        'horario_agendamento.required' => 'O horário é obrigatório.',
    ];

    // ====== LIFECYCLE ======
    public function mount($agendamento = null)
    {
        if ($agendamento) {
            $this->carregarAgendamento($agendamento);
        } else {
            $this->status = 'pendente';
            $this->etapaAtual = 1;
        }
    }

    public function carregarAgendamento($agendamentoId)
    {
        try {
            $this->agendamento = Agendamento::with(['cliente', 'servico'])->findOrFail($agendamentoId);
            $this->editando = true;
            $this->etapaAtual = 4; // Vai direto para a etapa final em edição
            
            // Carrega dados
            $this->cliente_id = $this->agendamento->cliente_id;
            $this->servico_id = $this->agendamento->servico_id;
            $this->data_agendamento = $this->agendamento->data_agendamento->format('Y-m-d');
            $this->horario_agendamento = Carbon::parse($this->agendamento->horario_agendamento)->format('H:i');
            $this->status = $this->agendamento->status;
            $this->observacoes = $this->agendamento->observacoes;
            
            // Carrega dados auxiliares
            $this->clienteSelecionado = $this->agendamento->cliente;
            $this->servicoSelecionado = $this->agendamento->servico;
            
        } catch (\Exception $e) {
            session()->flash('erro', 'Agendamento não encontrado.');
            return redirect()->route('painel.agendamentos.index');
        }
    }

    // ====== NAVEGAÇÃO ENTRE ETAPAS ======
    public function proximaEtapa()
    {
        // Valida etapa atual antes de prosseguir
        if ($this->etapaAtual == 1 && $this->cliente_id) {
            $this->clienteSelecionado = Cliente::find($this->cliente_id);
            $this->etapaAtual = 2;
        } elseif ($this->etapaAtual == 2 && $this->servico_id) {
            $this->servicoSelecionado = Servico::find($this->servico_id);
            $this->etapaAtual = 3;
        } elseif ($this->etapaAtual == 3 && $this->data_agendamento && $this->horario_agendamento) {
            $this->validate(); // Valida data e horário
            $this->etapaAtual = 4;
        }
    }

    public function etapaAnterior()
    {
        if ($this->etapaAtual > 1) {
            $this->etapaAtual--;
        }
    }

    public function irParaEtapa($etapa)
    {
        if ($etapa <= $this->etapaAtual || $this->editando) {
            $this->etapaAtual = $etapa;
        }
    }

    // ====== AÇÕES AUXILIARES ======
    public function selecionarCliente($clienteId)
    {
        $this->cliente_id = $clienteId;
        $this->clienteSelecionado = Cliente::find($clienteId);
        $this->proximaEtapa();
    }

    public function selecionarServico($servicoId)
    {
        $this->servico_id = $servicoId;
        $this->servicoSelecionado = Servico::find($servicoId);
        $this->proximaEtapa();
    }

    public function updatedDataAgendamento()
    {
        if ($this->data_agendamento) {
            $this->carregarHorariosDisponiveis();
        }
    }

    private function carregarHorariosDisponiveis()
    {
        // Gera horários disponíveis baseado na configuração do sistema
        $horarios = [];
        $inicio = Carbon::createFromFormat('H:i', '08:00');
        $fim = Carbon::createFromFormat('H:i', '18:00');
        $intervalo = 30; // minutos
        
        while ($inicio < $fim) {
            $horarioStr = $inicio->format('H:i');
            
            // Verifica se horário está ocupado (exceto o próprio agendamento em edição)
            $ocupado = Agendamento::where('data_agendamento', $this->data_agendamento)
                ->where('horario_agendamento', $horarioStr)
                ->when($this->editando, function($query) {
                    $query->where('id', '!=', $this->agendamento->id);
                })
                ->exists();
                
            $horarios[] = [
                'horario' => $horarioStr,
                'disponivel' => !$ocupado
            ];
            
            $inicio->addMinutes($intervalo);
        }
        
        $this->horariosDisponiveis = $horarios;
    }

    // ====== SALVAR ======
    public function salvar()
    {
        $this->validate();

        try {
            $dados = [
                'cliente_id' => $this->cliente_id,
                'servico_id' => $this->servico_id,
                'data_agendamento' => $this->data_agendamento,
                'horario_agendamento' => $this->horario_agendamento,
                'status' => $this->status,
                'observacoes' => $this->observacoes
            ];

            if ($this->editando) {
                $this->agendamento->update($dados);
                session()->flash('sucesso', 'Agendamento atualizado com sucesso!');
            } else {
                Agendamento::create($dados);
                session()->flash('sucesso', 'Agendamento criado com sucesso!');
            }

            return redirect()->route('painel.agendamentos.index');

        } catch (\Exception $e) {
            session()->flash('erro', 'Erro ao salvar agendamento: ' . $e->getMessage());
        }
    }

    public function cancelar()
    {
        return redirect()->route('painel.agendamentos.index');
    }

    // ====== VALIDAÇÕES PERSONALIZADAS ======
    private function getDiasFuncionamento()
    {
        return [1, 2, 3, 4, 5, 6]; // Segunda a Sábado
    }

    private function getHorarioFuncionamento()
    {
        return ['inicio' => '08:00', 'fim' => '18:00'];
    }

    private function validarDiaFuncionamento($data, $fail)
    {
        try {
            $dataCarbon = Carbon::parse($data);
            $diasFuncionamento = $this->getDiasFuncionamento();
            
            if (!in_array($dataCarbon->dayOfWeek, $diasFuncionamento)) {
                $fail('Não funcionamos neste dia da semana.');
            }
        } catch (\Exception $e) {
            $fail('Data inválida.');
        }
    }

    private function validarHorarioFuncionamento($horario, $fail)
    {
        try {
            $horarioFuncionamento = $this->getHorarioFuncionamento();
            $horarioSelecionado = Carbon::createFromFormat('H:i', $horario);
            $horarioInicio = Carbon::createFromFormat('H:i', $horarioFuncionamento['inicio']);
            $horarioFim = Carbon::createFromFormat('H:i', $horarioFuncionamento['fim']);
            
            if ($horarioSelecionado->lt($horarioInicio) || $horarioSelecionado->gt($horarioFim)) {
                $fail("Horário fora do funcionamento (08:00 às 18:00).");
            }
        } catch (\Exception $e) {
            $fail('Horário inválido.');
        }
    }

    // ====== COMPUTED PROPERTIES ======
    public function getClientesProperty()
    {
        return Cliente::orderBy('nome')->get(['id', 'nome', 'telefone']);
    }

    public function getServicosProperty()
    {
        return Servico::orderBy('nome')->get(['id', 'nome', 'preco', 'duracao']);
    }

    public function getProgressoProperty()
    {
        return ($this->etapaAtual / 4) * 100;
    }

    // ====== RENDER ======
    public function render()
    {
        return view('livewire.painel.agendamentos-form', [
            'clientes' => $this->clientes,
            'servicos' => $this->servicos,
            'progresso' => $this->progresso
        ])->layout('layouts.painel');
    }
}