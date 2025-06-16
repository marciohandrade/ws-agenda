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

    // Propriedades de controle
    public $mostrarModal = false;
    public $editando = false;
    public $agendamentoId = null;

    // Filtros
    public $filtroCliente = '';
    public $filtroData = '';
    public $filtroStatus = '';

    // Dados para selects
    public $clientes;
    public $servicos;
    public $horariosDisponiveis = [];

    protected $rules = [
        'cliente_id' => 'required|exists:clientes,id',
        'servico_id' => 'required|exists:servicos,id',
        'data_agendamento' => 'required|date|after_or_equal:today',
        'horario_agendamento' => 'required',
        'status' => 'required|in:pendente,confirmado,concluido,cancelado',
        'observacoes' => 'nullable|string|max:1000'
    ];

    protected $messages = [
        'cliente_id.required' => 'Selecione um cliente.',
        'cliente_id.exists' => 'Cliente inválido.',
        'servico_id.required' => 'Selecione um serviço.',
        'servico_id.exists' => 'Serviço inválido.',
        'data_agendamento.required' => 'A data é obrigatória.',
        'data_agendamento.after_or_equal' => 'A data não pode ser anterior a hoje.',
        'horario_agendamento.required' => 'O horário é obrigatório.',
        'status.required' => 'O status é obrigatório.',
        'observacoes.max' => 'As observações devem ter no máximo 1000 caracteres.'
    ];

    public function mount()
    {
        $this->carregarDados();
        $this->gerarHorariosDisponiveis();
    }

    public function render()
    {
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
            ->paginate(15);
            
        return view('livewire.painel.agendamentos', compact('agendamentos'))
            ->layout('layouts.app');
    }

    public function abrirModal()
    {
        $this->resetarFormulario();
        $this->mostrarModal = true;
    }

    public function fecharModal()
    {
        $this->mostrarModal = false;
        $this->resetarFormulario();
    }

    public function salvar()
    {
        $this->validate();

        // Verificar conflito de horário
        if ($this->verificarConflito()) {
            $this->addError('horario_agendamento', 'Já existe um agendamento neste horário.');
            return;
        }

        $dados = [
            'cliente_id' => $this->cliente_id,
            'servico_id' => $this->servico_id,
            'data_agendamento' => $this->data_agendamento,
            'horario_agendamento' => $this->horario_agendamento,
            'status' => $this->status,
            'observacoes' => $this->observacoes,
        ];

        if ($this->editando) {
            $agendamento = Agendamento::find($this->agendamentoId);
            $agendamento->update($dados);
            session()->flash('sucesso', 'Agendamento atualizado com sucesso!');
        } else {
            Agendamento::create($dados);
            session()->flash('sucesso', 'Agendamento criado com sucesso!');
        }

        $this->fecharModal();
    }

    public function editar($id)
    {
        $agendamento = Agendamento::find($id);
        
        $this->agendamentoId = $agendamento->id;
        $this->cliente_id = $agendamento->cliente_id;
        $this->servico_id = $agendamento->servico_id;
        $this->data_agendamento = $agendamento->data_agendamento->format('Y-m-d');
        $this->horario_agendamento = Carbon::parse($agendamento->horario_agendamento)->format('H:i');
        $this->status = $agendamento->status;
        $this->observacoes = $agendamento->observacoes;
        
        $this->editando = true;
        $this->mostrarModal = true;
    }

    public function alterarStatus($id, $novoStatus)
    {
        $agendamento = Agendamento::find($id);
        
        $dados = ['status' => $novoStatus];
        
        if ($novoStatus === 'cancelado') {
            $dados['data_cancelamento'] = now();
        }
        
        $agendamento->update($dados);
        
        session()->flash('sucesso', 'Status alterado para: ' . Agendamento::getStatusOptions()[$novoStatus]);
    }

    public function cancelar($id, $motivo = null)
    {
        $agendamento = Agendamento::find($id);
        
        $agendamento->update([
            'status' => 'cancelado',
            'data_cancelamento' => now(),
            'motivo_cancelamento' => $motivo
        ]);
        
        session()->flash('sucesso', 'Agendamento cancelado com sucesso!');
    }

    public function excluir($id)
    {
        $agendamento = Agendamento::find($id);
        $agendamento->delete();
        
        session()->flash('sucesso', 'Agendamento excluído com sucesso!');
    }

    public function updatedDataAgendamento()
    {
        $this->horario_agendamento = '';
        $this->gerarHorariosDisponiveis();
    }

    private function verificarConflito()
    {
        $query = Agendamento::where('data_agendamento', $this->data_agendamento)
            ->where('horario_agendamento', $this->horario_agendamento)
            ->whereNotIn('status', ['cancelado']);

        if ($this->editando) {
            $query->where('id', '!=', $this->agendamentoId);
        }

        return $query->exists();
    }

    private function gerarHorariosDisponiveis()
    {
        $horarios = [];
        $inicio = Carbon::createFromTime(8, 0); // 08:00
        $fim = Carbon::createFromTime(18, 0);   // 18:00
        
        while ($inicio <= $fim) {
            $horarios[] = $inicio->format('H:i');
            $inicio->addMinutes(30);
        }
        
        // Se uma data foi selecionada, remover horários já agendados
        if ($this->data_agendamento) {
            $agendados = Agendamento::where('data_agendamento', $this->data_agendamento)
                ->whereNotIn('status', ['cancelado'])
                ->when($this->editando, function($query) {
                    $query->where('id', '!=', $this->agendamentoId);
                })
                ->pluck('horario_agendamento')
                ->map(function($horario) {
                    return Carbon::parse($horario)->format('H:i');
                })
                ->toArray();
                
            $horarios = array_diff($horarios, $agendados);
        }
        
        $this->horariosDisponiveis = array_values($horarios);
    }

    private function carregarDados()
    {
        $this->clientes = Cliente::orderBy('nome')->get();
        $this->servicos = Servico::ativo()->orderBy('nome')->get();
    }

    private function resetarFormulario()
    {
        $this->cliente_id = '';
        $this->servico_id = '';
        $this->data_agendamento = '';
        $this->horario_agendamento = '';
        $this->status = 'pendente';
        $this->observacoes = '';
        $this->editando = false;
        $this->agendamentoId = null;
        $this->horariosDisponiveis = [];
        $this->resetErrorBag();
        $this->gerarHorariosDisponiveis();
    }

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

    public function limparFiltros()
    {
        $this->filtroCliente = '';
        $this->filtroData = '';
        $this->filtroStatus = '';
        $this->resetPage();
    }
}