<?php

namespace App\Livewire\Painel;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class CriarUsuario extends Component
{
    public $nome = '';
    public $email = '';
    public $telefone = '';
    public $tipoUsuario = 'usuario';
    public $senha = '';
    public $senhaConfirmacao = '';
    
    public $carregando = false;

    public function mount()
    {
        if (!Auth::user()->canAccessAdmin()) {
            abort(403, 'Acesso negado.');
        }
    }

    public function getTiposUsuarioDisponiveisProperty()
    {
        $usuarioAtual = Auth::user();
        
        $tipos = [
            'usuario' => 'Cliente/Usuário',
            'colaborador' => 'Colaborador'
        ];

        // Super admin pode criar admins
        if ($usuarioAtual->isSuperAdmin()) {
            $tipos['admin'] = 'Administrador';
        }

        return $tipos;
    }

    public function salvar()
    {
        $this->carregando = true;

        $this->validate([
            'nome' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:users,email',
            'telefone' => 'required|string|min:10|max:20|unique:users,telefone',
            'tipoUsuario' => 'required|in:usuario,colaborador,admin',
            'senha' => ['required', Password::min(6)->letters()->numbers()],
            'senhaConfirmacao' => 'required|same:senha'
        ], [
            'nome.required' => 'O nome é obrigatório',
            'nome.min' => 'Nome deve ter pelo menos 3 caracteres',
            'email.required' => 'O e-mail é obrigatório',
            'email.email' => 'E-mail deve ter um formato válido',
            'email.unique' => 'Este e-mail já está em uso',
            'telefone.required' => 'O telefone é obrigatório',
            'telefone.min' => 'Telefone deve ter pelo menos 10 dígitos',
            'telefone.unique' => 'Este telefone já está em uso',
            'tipoUsuario.required' => 'Selecione um tipo de usuário',
            'senha.required' => 'A senha é obrigatória',
            'senhaConfirmacao.same' => 'As senhas não conferem'
        ]);

        // Verificar se o usuário atual pode criar este tipo
        if ($this->tipoUsuario === 'admin' && !Auth::user()->isSuperAdmin()) {
            $this->addError('tipoUsuario', 'Você não tem permissão para criar administradores.');
            $this->carregando = false;
            return;
        }

        try {
            User::create([
                'name' => $this->nome,
                'email' => $this->email,
                'telefone' => preg_replace('/\D/', '', $this->telefone),
                'tipo_usuario' => $this->tipoUsuario,
                'password' => Hash::make($this->senha),
            ]);

            session()->flash('mensagem-sucesso', 'Usuário criado com sucesso!');
            return $this->redirect('/usuarios', navigate: true);

        } catch (\Exception $e) {
            $this->addError('geral', 'Erro ao criar usuário: ' . $e->getMessage());
        }

        $this->carregando = false;
    }

    public function voltar()
    {
        return $this->redirect('/painel/usuarios', navigate: true);
    }

    public function render()
    {
        return view('livewire.painel.criar-usuario');
    }
}