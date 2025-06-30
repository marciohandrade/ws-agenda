<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Listar usuários
     */
    public function index()
    {
        $currentUser = auth()->user();
        
        // Super admin vê todos usuários (exceto outros super admins)
        if ($currentUser->isSuperAdmin()) {
            $users = User::where('tipo_usuario', '!=', 'super_admin')
                        ->orWhere('id', $currentUser->id)
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);
        } else {
            // Admin vê apenas usuários comuns
            $users = User::where('tipo_usuario', 'usuario')
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);
        }

        return view('admin.usuarios.index', compact('users'));
    }

    /**
     * Mostrar formulário de criação
     */
    public function create()
    {
        $currentUser = auth()->user();
        
        // Tipos disponíveis baseado no usuário logado
        $tiposDisponiveis = [];
        
        if ($currentUser->isSuperAdmin()) {
            $tiposDisponiveis = [
                'admin' => 'Administrador',
                'colaborador' => 'Colaborador',
                'usuario' => 'Usuário'
            ];
        } else {
            $tiposDisponiveis = [
                'usuario' => 'Usuário'
            ];
        }

        return view('admin.usuarios.create', compact('tiposDisponiveis'));
    }

    /**
     * Criar usuário
     */
    public function store(Request $request)
    {
        $currentUser = auth()->user();
        
        // Validar permissões
        if (!$currentUser->isSuperAdmin() && !$currentUser->isAdmin()) {
            abort(403, 'Você não tem permissão para criar usuários.');
        }

        // Validar dados
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'telefone' => ['required', 'string', 'max:20', 'unique:users'],
            'tipo_usuario' => ['required', 'in:admin,colaborador,usuario'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Super admin pode criar qualquer tipo, admin só pode criar usuários
        if (!$currentUser->isSuperAdmin() && $request->tipo_usuario !== 'usuario') {
            abort(403, 'Você só pode criar usuários comuns.');
        }

        // Criar usuário
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'telefone' => $request->telefone,
            'tipo_usuario' => $request->tipo_usuario,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(), // Auto-verificar emails criados pelo admin
            'telefone_verified_at' => now(), // Auto-verificar telefones
        ]);

        return redirect()->route('admin.usuarios.index')
                        ->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Mostrar usuário
     */
    public function show(User $user)
    {
        // Verificar se pode ver este usuário
        $this->authorizeViewUser($user);
        
        return view('admin.usuarios.show', compact('user'));
    }

    /**
     * Editar usuário
     */
    public function edit(User $user)
    {
        // Verificar se pode editar este usuário
        $this->authorizeViewUser($user);
        
        $currentUser = auth()->user();
        $tiposDisponiveis = [];
        
        if ($currentUser->isSuperAdmin()) {
            $tiposDisponiveis = [
                'admin' => 'Administrador',
                'colaborador' => 'Colaborador',
                'usuario' => 'Usuário'
            ];
        } else {
            $tiposDisponiveis = [
                'usuario' => 'Usuário'
            ];
        }

        return view('admin.usuarios.edit', compact('user', 'tiposDisponiveis'));
    }

    /**
     * Atualizar usuário
     */
    public function update(Request $request, User $user)
    {
        // Verificar se pode editar este usuário
        $this->authorizeViewUser($user);
        
        $currentUser = auth()->user();

        // Validar dados
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'telefone' => ['required', 'string', 'max:20', 'unique:users,telefone,' . $user->id],
            'tipo_usuario' => ['required', 'in:admin,colaborador,usuario'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        // Super admin pode alterar qualquer tipo, admin só pode alterar para usuário
        if (!$currentUser->isSuperAdmin() && $request->tipo_usuario !== 'usuario') {
            abort(403, 'Você só pode alterar para usuário comum.');
        }

        // Atualizar dados
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'telefone' => $request->telefone,
            'tipo_usuario' => $request->tipo_usuario,
        ];

        // Atualizar senha se fornecida
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.usuarios.index')
                        ->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Excluir usuário
     */
    public function destroy(User $user)
    {
        // Verificar se pode excluir
        if (!$user->isDeletable()) {
            return back()->with('error', 'Este usuário não pode ser excluído.');
        }

        // Verificar permissões
        $this->authorizeViewUser($user);

        $user->delete();

        return redirect()->route('admin.usuarios.index')
                        ->with('success', 'Usuário excluído com sucesso!');
    }

    /**
     * Verificar se pode visualizar/editar usuário
     */
    private function authorizeViewUser(User $user)
    {
        $currentUser = auth()->user();

        // Super admin não pode ser editado por outros
        if ($user->isSuperAdmin() && !$currentUser->isSuperAdmin()) {
            abort(403, 'Você não tem permissão para visualizar este usuário.');
        }

        // Admin só pode ver usuários comuns
        if ($currentUser->isAdmin() && !$currentUser->isSuperAdmin() && $user->tipo_usuario !== 'usuario') {
            abort(403, 'Você só pode gerenciar usuários comuns.');
        }
    }
}