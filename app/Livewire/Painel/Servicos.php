<?php

namespace App\Livewire\Painel;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Servico;

class Servicos extends Component
{
    use WithPagination;

    public $nome = '';
    public $descricao = '';
    public $duracao_minutos = 60;
    public $preco = '';
    public $ativo = true;
    
    public $editando = false;
    public $servicoId = null;
    public $pesquisa = '';
    public $mostrarModal = false;

    protected $rules = [
        'nome' => 'required|string|max:255',
        'descricao' => 'nullable|string',
        'duracao_minutos' => 'required|integer|min:15|max:480',
        'preco' => 'nullable|string|regex:/^[\d{1,2}\.]*\d{1,3},\d{2}$/',
        'ativo' => 'boolean'
    ];

    protected $messages = [
        'nome.required' => 'O nome do serviço é obrigatório.',
        'nome.max' => 'O nome deve ter no máximo 255 caracteres.',
        'duracao_minutos.required' => 'A duração é obrigatória.',
        'duracao_minutos.min' => 'A duração mínima é de 15 minutos.',
        'duracao_minutos.max' => 'A duração máxima é de 8 horas (480 minutos).',
        'preco.regex' => 'O preço deve estar no formato correto (ex: 150,00 ou 1.500,50).',
    ];

    public function render()
    {
        $servicos = Servico::query()
            ->when($this->pesquisa, function ($query) {
                $query->where('nome', 'like', '%' . $this->pesquisa . '%')
                      ->orWhere('descricao', 'like', '%' . $this->pesquisa . '%');
            })
            ->orderBy('nome')
            ->paginate(10);

        return view('livewire.painel.servicos', compact('servicos'))
            ->layout('layouts.app'); // ✅ Mesmo padrão do ClienteCrud
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

        // Converter preço brasileiro para formato do banco
        $precoFormatado = null;
        if ($this->preco) {
            // Remove pontos e substitui vírgula por ponto
            $precoFormatado = str_replace(['.', ','], ['', '.'], $this->preco);
            $precoFormatado = (float) $precoFormatado;
        }

        $dados = [
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'duracao_minutos' => $this->duracao_minutos,
            'preco' => $precoFormatado,
            'ativo' => $this->ativo
        ];

        if ($this->editando) {
            $servico = Servico::find($this->servicoId);
            $servico->update($dados);
            session()->flash('sucesso', 'Serviço atualizado com sucesso!');
        } else {
            Servico::create($dados);
            session()->flash('sucesso', 'Serviço cadastrado com sucesso!');
        }

        $this->resetarFormulario();
    }

    public function editar($id)
    {
        $servico = Servico::find($id);
        
        $this->servicoId = $servico->id;
        $this->nome = $servico->nome;
        $this->descricao = $servico->descricao;
        $this->duracao_minutos = $servico->duracao_minutos;
        
        // Converter preço do banco para formato brasileiro
        if ($servico->preco) {
            $this->preco = number_format($servico->preco, 2, ',', '.');
        } else {
            $this->preco = '';
        }
        
        $this->ativo = $servico->ativo;
        $this->editando = true;
    }

    public function alternarStatus($id)
    {
        $servico = Servico::find($id);
        $servico->update(['ativo' => !$servico->ativo]);
        
        $status = $servico->ativo ? 'ativado' : 'desativado';
        session()->flash('sucesso', "Serviço {$status} com sucesso!");
    }

    public function excluir($id)
    {
        $servico = Servico::find($id);
        
        // Verificar se existem agendamentos
        if ($servico->agendamentos()->count() > 0) {
            session()->flash('erro', 'Não é possível excluir este serviço pois existem agendamentos vinculados.');
            return;
        }

        $servico->delete();
        session()->flash('sucesso', 'Serviço excluído com sucesso!');
    }

    public function resetarFormulario()
    {
        $this->nome = '';
        $this->descricao = '';
        $this->duracao_minutos = 60;
        $this->preco = '';
        $this->ativo = true;
        $this->editando = false;
        $this->servicoId = null;
        $this->resetErrorBag();
    }

    public function updatingPesquisa()
    {
        $this->resetPage();
    }
}