<?php

namespace App\Livewire\Painel;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Servico;
use Carbon\Carbon;

class Agendamentos extends Component
{
    use WithPagination;

    // Propriedades do formulário
    public $cliente_id = '';
    public $servico_id = '';
    public $data_agendamento = '';
    public $horario_agendamento = '';
    public $status = 'pendente';
    public $observacoes = '';
    
    // Estados do componente
    public $editando = false;
    public $agendamento_id = null;
    
    // Filtros
    public $filtroCliente = '';
    public $filtroData = '';
    public $filtroStatus = '';

    // Regras de validação
    protected function rules()
    {
        return [
            'cliente_id' => 'required|exists:clientes,id',
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
                    $this->validarHorarioFuncionamento($value, $fail);
                }
            ],
            'status' => 'required|in:pendente,confirmado,concluido,cancelado',
            'observacoes' => 'nullable|string|max:500'
        ];
    }

    // Mensagens de validação personalizadas
    protected $messages = [
        'cliente_id.required' => 'Selecione um cliente.',
        'cliente_id.exists' => 'Cliente selecionado não existe.',
        'servico_id.required' => 'Selecione um serviço.',
        'servico_id.exists' => 'Serviço selecionado não existe.',
        'data_agendamento.required' => 'A data é obrigatória.',
        'data_agendamento.after_or_equal' => 'A data deve ser hoje ou uma data futura.',
        'horario_agendamento.required' => 'O horário é obrigatório.',
        'status.required' => 'O status é obrigatório.',
        'observacoes.max' => 'As observações não podem ter mais de 500 caracteres.'
    ];

    public function mount()
    {
        // Inicializa com valores padrão
        $this->status = 'pendente';
    }

    public function render()
    {
        // Busca agendamentos com filtros aplicados
        $agendamentos = Agendamento::with(['cliente', 'servico'])
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
            ->orderBy('data_agendamento', 'desc')
            ->orderBy('horario_agendamento', 'desc')
            ->paginate(10);

        // Busca clientes e serviços para os selects
        $clientes = Cliente::orderBy('nome')->get();
        $servicos = Servico::orderBy('nome')->get();

        return view('livewire.painel.agendamentos', [
            'agendamentos' => $agendamentos,
            'clientes' => $clientes,
            'servicos' => $servicos
            ])->layout('layouts.painel');
        //])->layout('layouts.app');
    }

    public function salvar()
    {
        $this->validate();

        try {
            if ($this->editando) {
                // Atualiza agendamento existente
                $agendamento = Agendamento::findOrFail($this->agendamento_id);
                $agendamento->update([
                    'cliente_id' => $this->cliente_id,
                    'servico_id' => $this->servico_id,
                    'data_agendamento' => $this->data_agendamento,
                    'horario_agendamento' => $this->horario_agendamento,
                    'status' => $this->status,
                    'observacoes' => $this->observacoes
                ]);

                session()->flash('sucesso', 'Agendamento atualizado com sucesso!');
            } else {
                // Cria novo agendamento
                Agendamento::create([
                    'cliente_id' => $this->cliente_id,
                    'servico_id' => $this->servico_id,
                    'data_agendamento' => $this->data_agendamento,
                    'horario_agendamento' => $this->horario_agendamento,
                    'status' => $this->status,
                    'observacoes' => $this->observacoes
                ]);

                session()->flash('sucesso', 'Agendamento criado com sucesso!');
            }

            // Limpa o formulário após salvar
            $this->resetarFormulario();
            $this->dispatch('$refresh');
            $this->resetPage();

        } catch (\Exception $e) {
            session()->flash('erro', 'Erro ao salvar agendamento: ' . $e->getMessage());
        }
    }

    public function editar($agendamento_id)
    {
        
        //dd("Método editar chamado com ID: " . $agendamento_id);

        try {
            $agendamento = Agendamento::findOrFail($agendamento_id);
            
            // Carrega os dados no formulário
            $this->agendamento_id = $agendamento->id;
            $this->cliente_id = $agendamento->cliente_id;
            $this->servico_id = $agendamento->servico_id;
            $this->data_agendamento = $agendamento->data_agendamento->format('Y-m-d');
            $this->horario_agendamento = Carbon::parse($agendamento->horario_agendamento)->format('H:i');
            $this->status = $agendamento->status;
            $this->observacoes = $agendamento->observacoes;
            
            // Ativa modo de edição
            $this->editando = true;

            // Limpa erros de validação
            $this->resetErrorBag();

        } catch (\Exception $e) {
            session()->flash('erro', 'Erro ao carregar agendamento para edição.');
        }
    }

    public function resetarFormulario()
    {
        // Limpa todos os campos do formulário
        $this->reset([
            'cliente_id',
            'servico_id',
            'data_agendamento',
            'horario_agendamento',
            'status',
            'observacoes',
            'agendamento_id'
        ]);

        // Reseta estados
        $this->editando = false;
        $this->status = 'pendente';

        // Limpa erros de validação
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function alterarStatus($agendamento_id, $novo_status)
    {
        try {
            $agendamento = Agendamento::findOrFail($agendamento_id);
            $agendamento->update(['status' => $novo_status]);

            $status_texto = [
                'confirmado' => 'confirmado',
                'concluido' => 'concluído',
                'cancelado' => 'cancelado'
            ];

            session()->flash('sucesso', "Agendamento {$status_texto[$novo_status]} com sucesso!");

        } catch (\Exception $e) {
            session()->flash('erro', 'Erro ao alterar status do agendamento.');
        }
    }

    public function cancelar($agendamento_id)
    {
        $this->alterarStatus($agendamento_id, 'cancelado');
    }

    public function excluir($agendamento_id)
    {
        try {
            $agendamento = Agendamento::findOrFail($agendamento_id);
            $agendamento->delete();

            session()->flash('sucesso', 'Agendamento excluído com sucesso!');

            // Se estava editando o agendamento excluído, limpa o formulário
            if ($this->editando && $this->agendamento_id == $agendamento_id) {
                $this->resetarFormulario();
            }

        } catch (\Exception $e) {
            session()->flash('erro', 'Erro ao excluir agendamento.');
        }
    }

    public function limparFiltros()
    {
        $this->reset(['filtroCliente', 'filtroData', 'filtroStatus']);
        $this->resetPage();
    }

    // Método para atualizar a paginação quando os filtros mudam
    public function updatingFiltroCliente()
    {
        $this->resetPage();
    }

    public function updatingFiltroData()
    {
        $this->resetPage();
    }

    public function updatingFiltroStatus()
    {
        $this->resetPage();
    }

    // Configurações de funcionamento
    private function getDiasFuncionamento()
    {
        // 1 = Segunda, 2 = Terça, 3 = Quarta, 4 = Quinta, 5 = Sexta, 6 = Sábado, 0 = Domingo
        return [1, 2, 3, 4, 5, 6]; // Segunda a Sábado (remova dias que não funcionam)
    }

    private function getHorarioFuncionamento()
    {
        return [
            'inicio' => '08:00',
            'fim' => '18:00'
        ];
    }

    // Validações personalizadas
    private function validarDiaFuncionamento($data, $fail)
    {
        try {
            $dataCarbon = Carbon::parse($data);
            $diasFuncionamento = $this->getDiasFuncionamento();
            
            if (!in_array($dataCarbon->dayOfWeek, $diasFuncionamento)) {
                $diasTexto = [
                    0 => 'Domingo',
                    1 => 'Segunda-feira', 
                    2 => 'Terça-feira',
                    3 => 'Quarta-feira',
                    4 => 'Quinta-feira',
                    5 => 'Sexta-feira',
                    6 => 'Sábado'
                ];
                
                $diasFuncionamentoTexto = array_map(function($dia) use ($diasTexto) {
                    return $diasTexto[$dia];
                }, $diasFuncionamento);
                
                $fail('Não funcionamos em ' . $diasTexto[$dataCarbon->dayOfWeek] . '. Funcionamos: ' . implode(', ', $diasFuncionamentoTexto) . '.');
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
                $fail("Horário fora do funcionamento. Funcionamos das {$horarioFuncionamento['inicio']} às {$horarioFuncionamento['fim']}.");
            }
        } catch (\Exception $e) {
            $fail('Horário inválido.');
        }
    }
}