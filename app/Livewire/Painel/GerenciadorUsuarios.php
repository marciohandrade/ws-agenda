<?php

namespace App\Livewire\Painel;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class GerenciadorUsuarios extends Component
{
    use WithPagination;

    // Campos do formulário
    public $nome = '';
    public $email = '';
    public $telefone = '';
    public $tipoUsuario = 'usuario';
    public $senha = '';
    public $senhaConfirmacao = '';
    
    // Estados
    public $usuarioId = null;
    public $editarSenha = false;
    
    // Filtros
    public $busca = '';
    public $filtroTipo = '';
    public $itensPorPagina = 10;

    public function updatedBusca()
    {
        $this->resetPage();
    }

    public function updatedFiltroTipo()
    {
        $this->resetPage();
    }

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
            'usuario' => 'Cliente - Área restrita, vê apenas seus agendamentos',
            'colaborador' => 'Colaborador - Gerencia agendamentos, sem configurações do sistema'
        ];

        // Super admin pode criar admins
        if ($usuarioAtual->isSuperAdmin()) {
            $tipos['admin'] = 'Administrador - Acesso total aos agendamentos e configurações';
        }

        return $tipos;
    }

    public function salvar()
    {
        // Definir tipos permitidos dinamicamente
        $tiposPermitidos = ['usuario', 'colaborador', 'admin'];
        
        // Se for super admin editando a si mesmo, permitir super_admin
        if ($this->usuarioId && Auth::user()->isSuperAdmin() && $this->tipoUsuario === 'super_admin') {
            $tiposPermitidos[] = 'super_admin';
        }

        $rules = [
            'nome' => 'required|string|min:3|max:255',
            'telefone' => 'required|string|min:10|max:20',
            'tipoUsuario' => 'required|in:' . implode(',', $tiposPermitidos),
        ];

        $messages = [
            'nome.required' => 'O nome é obrigatório',
            'nome.min' => 'Nome deve ter pelo menos 3 caracteres',
            'telefone.required' => 'O telefone é obrigatório',
            'telefone.min' => 'Telefone deve ter pelo menos 10 dígitos',
            'tipoUsuario.required' => 'Selecione um tipo de usuário',
        ];

        // Validação de email (único apenas se for novo ou se mudou)
        if (!$this->usuarioId) {
            $rules['email'] = 'required|email|unique:users,email';
            $rules['telefone'] .= '|unique:users,telefone';
        } else {
            $rules['email'] = 'required|email|unique:users,email,' . $this->usuarioId;
            $rules['telefone'] .= '|unique:users,telefone,' . $this->usuarioId;
        }

        // Validação de senha (obrigatória ao criar ou ao editar senha)
        if (!$this->usuarioId || $this->editarSenha) {
            $rules['senha'] = ['required', Password::min(6)->letters()->numbers()];
            $rules['senhaConfirmacao'] = 'required|same:senha';
            $messages['senha.required'] = 'A senha é obrigatória';
            $messages['senhaConfirmacao.same'] = 'As senhas não conferem';
        }

        $this->validate($rules, $messages);

        // Verificar se o usuário atual pode criar/editar este tipo
        if ($this->tipoUsuario === 'admin' && !Auth::user()->isSuperAdmin()) {
            $this->addError('tipoUsuario', 'Apenas Super Administradores podem criar outros Administradores.');
            return;
        }

        try {
            $dados = [
                'name' => $this->nome,
                'email' => $this->email,
                'telefone' => preg_replace('/\D/', '', $this->telefone),
                'tipo_usuario' => $this->tipoUsuario,
            ];

            // Adicionar senha se necessário
            if (!$this->usuarioId || $this->editarSenha) {
                $dados['password'] = Hash::make($this->senha);
            }

            if ($this->usuarioId) {
                // ✅ ATUALIZAR USUÁRIO EXISTENTE
                User::find($this->usuarioId)->update($dados);
                $tipoNome = $this->getTipoNomeAmigavel($this->tipoUsuario);
                session()->flash('mensagem-sucesso', "{$tipoNome} '{$this->nome}' atualizado com sucesso!");
            } else {
                // ✅ CRIAR NOVO USUÁRIO
                User::create($dados);
                $tipoNome = $this->getTipoNomeAmigavel($this->tipoUsuario);
                session()->flash('mensagem-sucesso', "Novo {$tipoNome} '{$this->nome}' criado com sucesso!");
            }

            $this->resetCampos();

        } catch (\Exception $e) {
            $this->addError('geral', 'Erro ao salvar usuário: ' . $e->getMessage());
        }
    }

    private function getTipoNomeAmigavel($tipo)
    {
        return match($tipo) {
            'super_admin' => 'Super Administrador', // ✅ ADICIONAR ESTE CASO
            'admin' => 'Administrador',
            'colaborador' => 'Colaborador',
            'usuario' => 'Cliente',
            default => 'Usuário'
        };
    }

    public function editar($usuarioId)
    {
        $usuario = User::find($usuarioId);
        
        if (!$usuario || !$this->podeGerenciarUsuario($usuario)) {
            session()->flash('mensagem-erro', 'Usuário não encontrado ou sem permissão para editá-lo.');
            return;
        }

        $this->usuarioId = $usuario->id;
        $this->nome = $usuario->name;
        $this->email = $usuario->email;
        $this->telefone = $usuario->telefone;
        $this->tipoUsuario = $usuario->tipo_usuario;
        $this->editarSenha = false;
        $this->senha = '';
        $this->senhaConfirmacao = '';

        // Scroll suave para o formulário
        $this->dispatch('scroll-to-top');
    }

    public function excluir($usuarioId)
    {
        try {
            $usuario = User::find($usuarioId);
            
            if (!$usuario || !$this->podeGerenciarUsuario($usuario)) {
                session()->flash('mensagem-erro', 'Usuário não encontrado ou sem permissão para excluí-lo.');
                return;
            }

            if (!$usuario->isDeletable()) {
                session()->flash('mensagem-erro', 'Este usuário não pode ser excluído (é o último admin ou super admin).');
                return;
            }

            $nomeUsuario = $usuario->name;
            $tipoUsuario = $this->getTipoNomeAmigavel($usuario->tipo_usuario);
            $usuario->delete();

            session()->flash('mensagem-sucesso', "{$tipoUsuario} '{$nomeUsuario}' excluído com sucesso.");
            
        } catch (\Exception $e) {
            session()->flash('mensagem-erro', 'Erro ao excluir usuário: ' . $e->getMessage());
        }
    }

    public function resetCampos()
    {
        $this->usuarioId = null;
        $this->nome = '';
        $this->email = '';
        $this->telefone = '';
        $this->tipoUsuario = 'usuario';
        $this->senha = '';
        $this->senhaConfirmacao = '';
        $this->editarSenha = false;
        $this->resetErrorBag();
    }

    private function podeGerenciarUsuario($usuario)
    {
        $usuarioAtual = Auth::user();

        // Super admin pode gerenciar todos, exceto outros super admins (exceto ele mesmo)
        if ($usuarioAtual->isSuperAdmin()) {
            return !$usuario->isSuperAdmin() || $usuario->id === $usuarioAtual->id;
        }

        // Admin tem ACESSO TOTAL aos agendamentos E pode gerenciar colaboradores e clientes
        if ($usuarioAtual->isAdmin()) {
            return $usuario->isColaborador() || $usuario->isUsuario();
        }

        // Colaboradores não podem gerenciar outros usuários
        return false;
    }

    public function getUsuariosProperty()
    {
        $query = User::query();
        
        // Aplicar filtros de permissão
        $usuarioAtual = Auth::user();
        if ($usuarioAtual->isSuperAdmin()) {
            // Super admin vê todos exceto outros super admins
            $query->where('tipo_usuario', '!=', 'super_admin')
                  ->orWhere('id', $usuarioAtual->id);
        } elseif ($usuarioAtual->isAdmin()) {
            // Admin vê colaboradores e clientes
            $query->whereIn('tipo_usuario', ['colaborador', 'usuario']);
        } else {
            // Outros não podem acessar (já bloqueado no mount)
            $query->where('id', 0); // Resultado vazio
        }

        // Aplicar busca
        if (!empty($this->busca)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->busca . '%')
                  ->orWhere('email', 'like', '%' . $this->busca . '%');
            });
        }

        // Aplicar filtro por tipo
        if (!empty($this->filtroTipo)) {
            $query->where('tipo_usuario', $this->filtroTipo);
        }

        return $query->orderBy('created_at', 'desc')->paginate($this->itensPorPagina);
    }

    public function getEstatisticasProperty()
    {
        $usuarioAtual = Auth::user();
        
        if ($usuarioAtual->isSuperAdmin()) {
            return [
                'total' => User::where('tipo_usuario', '!=', 'super_admin')->count() + 
                          ($usuarioAtual->isSuperAdmin() ? 1 : 0),
                'super_admins' => User::where('tipo_usuario', 'super_admin')->count(),
                'admins' => User::where('tipo_usuario', 'admin')->count(),
                'colaboradores' => User::where('tipo_usuario', 'colaborador')->count(),
                'usuarios' => User::where('tipo_usuario', 'usuario')->count(),
            ];
        } else {
            // Admin vê apenas colaboradores e clientes
            return [
                'total' => User::whereIn('tipo_usuario', ['colaborador', 'usuario'])->count(),
                'super_admins' => 0,
                'admins' => 0,
                'colaboradores' => User::where('tipo_usuario', 'colaborador')->count(),
                'usuarios' => User::where('tipo_usuario', 'usuario')->count(),
            ];
        }
    }

    public function limparFiltros()
    {
        $this->busca = '';
        $this->filtroTipo = '';
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.painel.gerenciador-usuarios', [
            'usuarios' => $this->usuarios,
            'estatisticas' => $this->estatisticas
        ])->layout('layouts.painel');
    }
}