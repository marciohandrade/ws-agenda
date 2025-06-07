<?php

namespace App\Livewire\Painel;

use Livewire\Component;
use App\Models\Cliente;


class ClienteCrud extends Component
{
    public $cliente_id;
    public $nome = '';
    public $email = '';
    public $telefone = '';
    public $data_nascimento = '';

   protected $rules = [
        'nome' => 'required|string|max:255',
        'email' => 'required|email',
        'telefone' => 'required|string|max:15',
        'data_nascimento' => 'required|date|before:today|max:10|after:1900-01-01',
    ];
    public function salvar()
    {
        $this->validate();

        if ($this->cliente_id) {
            $cliente = Cliente::find($this->cliente_id);
            $cliente->update($this->only(['nome', 'email', 'telefone', 'data_nascimento']));
            session()->flash('mensagem', 'Cliente atualizado com sucesso.');
        } else {
            Cliente::create($this->only(['nome', 'email', 'telefone', 'data_nascimento']));
            session()->flash('mensagem', 'Cliente cadastrado com sucesso.');
        }

        $this->resetCampos();
    }


    public function editar($id)
    {
        $cliente = Cliente::find($id);
        if ($cliente) {
            $this->cliente_id = $cliente->id;
            $this->nome = $cliente->nome;
            $this->email = $cliente->email;
            $this->telefone = $cliente->telefone;
            $this->data_nascimento = $cliente->data_nascimento;
        }
    }

    public function excluir($id)
    {
        Cliente::findOrFail($id)->delete();
        session()->flash('mensagem', 'Cliente excluído com sucesso.');
    }


    public function resetCampos()
    {
        $this->reset(['cliente_id', 'nome', 'email', 'telefone', 'data_nascimento']);
    }

    public function render()
    {
       /*  return view('livewire.painel.cliente-crud', [
            'clientes' => Cliente::latest()->get()
        ]); */

       /*  return view('livewire.painel.cliente-crud')
         ->layout('layouts.app');
        } */

       $clientes = Cliente::orderBy('nome')->get();

        return view('livewire.painel.cliente-crud', [
            'clientes' => $clientes,
        ])->layout('layouts.app'); // ✅ Corrige o erro do layout

    }

        
}
