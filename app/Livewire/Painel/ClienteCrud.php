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
    public $genero = '';
    public string $cpf = '';
    public string $endereco = '';
    public string $numero = '';
    public string $cep = '';
   public ?string $complemento = null;
    //public ?string $complemento = null;

   protected $rules = [
        'nome' => 'required|string|max:255',
        'email' => 'required|email',
        'telefone' => 'required|string|max:15',
        'data_nascimento' => 'required|date|before:today|max:10|after:1900-01-01',
        'genero' => 'nullable|string|max:50',
        'cpf' => 'nullable|string|max:14|unique:clientes,cpf',
        'cep' => 'required|regex:/^\d{5}-?\d{3}$/',
        'endereco' => 'required|string|max:80',
        'numero' => 'required|string|max:10',
        'complemento' => 'nullable|string|max:30',        
    ];
    public function salvar()
    {
        $this->validate();

        if ($this->cliente_id) {
            $cliente = Cliente::find($this->cliente_id);
            $cliente->update($this->only([
                                            'nome', 
                                            'email', 
                                            'telefone', 
                                            'data_nascimento',
                                            'genero',
                                            'cpf',
                                            'cep',
                                            'endereco',
                                            'numero',
                                            'complemento']));
            session()->flash('mensagem', 'Cliente atualizado com sucesso.');
        } else {
            Cliente::create($this->only([
                                            'nome', 
                                            'email', 
                                            'telefone', 
                                            'data_nascimento',
                                            'genero',
                                            'cpf',
                                            'cep',
                                            'endereco',
                                            'numero',
                                            'complemento']));
            session()->flash('mensagem', 'Cliente cadastrado com sucesso.');
        }

        $this->reset('nome',
                    'email',
                    'telefone',
                    'cpf',
                    'cep',
                    'endereco',
                    'numero',
                    'complemento',
                    'data_nascimento',
                    'genero',);
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
            // Campos que estavam faltando 👇
            $this->cpf = $cliente->cpf;
            $this->cep = $cliente->cep;
            $this->endereco = $cliente->endereco;
            $this->numero = $cliente->numero;
            $this->complemento = $cliente->complemento;
            $this->genero = $cliente->genero;
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
       $clientes = Cliente::orderBy('nome')->get();

        return view('livewire.painel.cliente-crud', [
            'clientes' => $clientes,
        ])->layout('layouts.app'); // ✅ Corrige o erro do layout

    }

        
}
