<?php

namespace App\Livewire\Painel;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Cliente;

class ClienteCrud extends Component
{
    use WithPagination;

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
    public $pesquisa = '';
    public $editandoId = null; 

    protected function rules()
    {
        return [
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:clientes,email,' . $this->cliente_id,
            'telefone' => 'required|string|max:15',
            'data_nascimento' => 'required|date|before:today|max:10|after:1900-01-01',
            'genero' => 'nullable|string|max:50',
            //'cpf' => 'required|string|max:14|unique:clientes,cpf,' . $this->cliente_id,
            'cpf' => [
                        'required',
                        'string',
                        'max:14',
                        'unique:clientes,cpf,' . $this->cliente_id,
                        function ($attribute, $value, $fail) {
                            if (!$this->validarCpf($value)) {
                                $fail('O CPF informado é inválido.');
                            }
                        },
                    ],
            'cep' => 'nullable|string|max:9',
            //'cep' => 'required|regex:/^\d{5}-?\d{3}$/',
            'endereco' => 'required|string|max:80',
            'numero' => 'required|string|max:10',
            'complemento' => 'nullable|string|max:30',        
        ];
    }

    protected $messages = [
        'nome.required' => 'O nome é obrigatório.',
        'email.required' => 'O email é obrigatório.',
        'email.email' => 'Digite um email válido.',
        'email.unique' => 'Este email já está cadastrado.',
        'telefone.required' => 'O telefone é obrigatório.',
        'data_nascimento.required' => 'A data de nascimento é obrigatória.',
        'data_nascimento.before' => 'A data deve ser anterior a hoje.',
        'data_nascimento.after' => 'Data inválida.',
        'cpf.required' => 'Este CPF já está cadastrado.',
        'cep.regex' => 'Digite um CEP válido (00000-000).',
        'endereco.required' => 'O endereço é obrigatório.',
        'numero.required' => 'O número é obrigatório.',
    ];

    public function updatingPesquisa()
    {
        $this->resetPage();
    }

    private function validarCpf($cpf)
    {
        // Remove formatação
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        // Verifica se tem 11 dígitos
        if (strlen($cpf) != 11) {
            return false;
        }
        
        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        
        // Calcula os dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        
        return true;
    }

    public function salvar()
    {
        $this->validate();

        try {
            // ✅ REMOVER FORMATAÇÃO DO CEP ANTES DE SALVAR
            $cepLimpo = preg_replace('/[^0-9]/', '', $this->cep); // Remove hífen
            
            $dados = $this->only([
                'nome', 'email', 'telefone', 'data_nascimento',
                'genero', 'cpf', 'endereco', 'numero', 'complemento'
            ]);
            
            // ✅ ADICIONAR CEP LIMPO
            $dados['cep'] = $cepLimpo;

            if ($this->cliente_id) {
                // ATUALIZAR
                $cliente = Cliente::find($this->cliente_id);
                $cliente->update($dados);
                session()->flash('mensagem', 'Cliente atualizado com sucesso.');
            } else {
                // CRIAR NOVO
                Cliente::create($dados);
                session()->flash('mensagem', 'Cliente cadastrado com sucesso.');
            }

            $this->resetCampos();

            $this->dispatch('cliente-salvo'); // para limpar os campos

        } catch (\Exception $e) {
            session()->flash('erro', 'Erro ao salvar cliente: ' . $e->getMessage());
        }
}
        
    public function editar($id)
    {
        $cliente = Cliente::find($id);
        
        if ($cliente) {
            $this->editandoId = $id;
            $this->pesquisa = '';
            
            $this->cliente_id = $cliente->id;
            $this->nome = $cliente->nome;
            $this->email = $cliente->email;
            
            // ✅ FORMATAR TELEFONE: 11912123434 → (11) 91212-3434
            if ($cliente->telefone && strlen($cliente->telefone) == 11) {
                $tel = $cliente->telefone;
                $this->telefone = '(' . substr($tel, 0, 2) . ') ' . substr($tel, 2, 5) . '-' . substr($tel, 7, 4);
            } else {
                $this->telefone = $cliente->telefone ?? '';
            }
            
            // ✅ FORMATAR CEP: 02845060 → 02845-060
            if ($cliente->cep && strlen($cliente->cep) == 8) {
                $this->cep = substr($cliente->cep, 0, 5) . '-' . substr($cliente->cep, 5, 3);
            } else {
                $this->cep = $cliente->cep ?? '';
            }
            
            $this->endereco = $cliente->endereco ?? '';
            $this->numero = $cliente->numero ?? '';
            $this->complemento = $cliente->complemento ?? '';
            $this->data_nascimento = $cliente->data_nascimento ? 
                $cliente->data_nascimento->format('Y-m-d') : '';
            $this->genero = $cliente->genero ?? '';
            $this->cpf = $cliente->cpf ?? '';
            
            // ✅ DD() DEPOIS DA ATRIBUIÇÃO
            /* dd([
                'telefone' => $this->telefone,
                'cpf' => $this->cpf,
                'telefone_length' => strlen($this->telefone),
                'cpf_length' => strlen($this->cpf),
            ]); */
            
            $this->resetErrorBag();
        }
    }

    // ✅ ADICIONAR ESTE MÉTODO NO SEU COMPONENTE ClienteCrud.php
    public function cancelarEdicao()
    {
        // Limpar todos os campos
        $this->reset([
            'cliente_id', 
            'nome', 
            'email', 
            'telefone', 
            'data_nascimento',
            'genero', 
            'cpf', 
            'cep', 
            'endereco', 
            'numero',
            'complemento',
            'editandoId'  // ✅ Importante limpar este também
        ]);
        
        $this->resetErrorBag();
        $this->resetValidation();
        
        // ✅ FORÇAR REFRESH DA PÁGINA (igual ao que você quer)
        return redirect()->to(request()->header('Referer'));
    }

    // ✅ ADICIONAR ESTE MÉTODO NO SEU COMPONENTE ClienteCrud.php
    // (COLOQUE DEPOIS DO MÉTODO salvar() ou resetCampos())

    /**
     * Cancela ação atual (cadastro ou edição)
     */
    public function cancelar()
    {
        $estaEditando = !empty($this->cliente_id);
        
        // Limpar todos os campos
        $this->reset([
            'cliente_id', 
            'nome', 
            'email', 
            'telefone', 
            'data_nascimento',
            'genero', 
            'cpf', 
            'cep', 
            'endereco', 
            'numero',
            'complemento',
            'editandoId'
        ]);
        
        $this->resetErrorBag();
        $this->resetValidation();
        
        if ($estaEditando) {
            // ✅ EDIÇÃO: Refresh da página (como você quer)
            return redirect()->to(request()->header('Referer'));
        } else {
            // ✅ CADASTRO: Apenas limpar campos (mais suave)
            $this->dispatch('campos-resetados');
            session()->flash('mensagem', 'Formulário limpo com sucesso.');
        }
    }

    public function excluir($id)
    {
        try {
            $cliente = Cliente::findOrFail($id);

            //dd('o valor do id do cliente eh >> ' . $id);
            
            // Verificar se o cliente tem agendamentos
            if ($cliente->agendamentos()->count() > 0) {
                session()->flash('erro', 'Não é possível excluir este cliente pois existem agendamentos vinculados.');
                return;
            }

            $nomeCliente = $cliente->nome; // Salvar nome antes de excluir
            $cliente->delete();
            
            session()->flash('mensagem', "Cliente '{$nomeCliente}' excluído com sucesso.");

             // ✅ FORÇAR RECARREGAMENTO COMPLETO
             $this->dispatch('campos-resetados');

            //return redirect()->to(request()->header('Referer'));
            
        } catch (\Exception $e) {
            session()->flash('erro', 'Erro ao excluir cliente: ' . $e->getMessage());
        }
    }

    public function resetCampos()
    {
        //dd('Reset chamado!');
        
        $this->reset([
            'cliente_id', 
            'nome', 
            'email', 
            'telefone', 
            'data_nascimento',
            'genero', 
            'cpf', 
            'cep', 
            'endereco', 
            'numero',
            'complemento'
        ]);
        
        $this->resetErrorBag();
        $this->resetValidation();

        $this->dispatch('campos-resetados');
    }

    public function render()
    {
        $clientes = Cliente::query()
            ->when($this->pesquisa, function ($query) {
                $query->where('nome', 'like', '%' . $this->pesquisa . '%')
                      ->orWhere('email', 'like', '%' . $this->pesquisa . '%')
                      ->orWhere('telefone', 'like', '%' . $this->pesquisa . '%');
            })
            ->orderBy('nome')
            ->paginate(10);

        return view('livewire.painel.cliente-crud', [
            'clientes' => $clientes,
        ])->layout('layouts.painel');
    }
}