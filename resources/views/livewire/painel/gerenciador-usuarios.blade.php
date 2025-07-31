<div class="max-w-6xl mx-auto p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Painel Administrativo / Gerenciador de Usuários</h2>

    @if (session()->has('mensagem-sucesso'))
        <div 
            x-data="{ show: true }" 
            x-init="setTimeout(() => show = false, 3000)" 
            x-show="show"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-500"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            class="fixed bottom-6 right-6 z-[9999] w-auto max-w-sm bg-gray-800 text-white text-sm rounded-lg shadow-lg px-5 py-3"
        >
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('mensagem-sucesso') }}</span>
            </div>
        </div>
    @endif

    {{-- ESTATÍSTICAS --}}
    <div class="bg-gray-50 rounded-lg p-4 mb-6">
        <div class="text-center text-gray-700">
            <span class="font-medium">Total de usuários <span class="font-bold text-blue-600">{{ $estatisticas['total'] }}</span></span>
            <span class="mx-2 text-gray-400">|</span>
            <span class="font-medium">Colaboradores <span class="font-bold text-green-600">{{ $estatisticas['colaboradores'] }}</span></span>
            <span class="mx-2 text-gray-400">|</span>
            <span class="font-medium">Clientes <span class="font-bold text-gray-600">{{ $estatisticas['usuarios'] }}</span></span>
            <span class="mx-2 text-gray-400">|</span>
            <span class="font-medium">Super Usuário <span class="font-bold text-red-600">{{ $estatisticas['super_admins'] }}</span></span>
            <span class="mx-2 text-gray-400">|</span>
            <span class="font-medium">Administradores <span class="font-bold text-purple-600">{{ $estatisticas['admins'] }}</span></span>
        </div>
    </div>

    {{-- FORMULÁRIO --}}
    <form wire:submit.prevent="salvar" class="w-full space-y-6 mb-8">
    
        <!-- Linha 1: Nome -->
        <div class="flex flex-col">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo *</label>
            <input type="text" wire:change="nome" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Digite o nome completo">
            @error('nome') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Linha 2: Email e Telefone -->
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[220px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">E-mail *</label>
                <input type="email" wire:model.defer="email" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Digite o e-mail">
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex-1 min-w-[180px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Telefone *</label>
                <input type="text" wire:model.defer="telefone" x-mask="(99) 99999-9999" maxlength="15" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="(11) 99999-9999">
                @error('telefone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Linha 3: Tipo de Usuário com explicações -->
        <div class="flex flex-col">
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Usuário *</label>
            <select wire:model.defer="tipoUsuario" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Selecione o tipo</option>
                @foreach($this->tiposUsuarioDisponiveis as $valor => $label)
                    <option value="{{ $valor }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('tipoUsuario') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            
            {{-- Explicação dos tipos --}}
            <div class="mt-2 text-xs text-gray-600 bg-blue-50 p-3 rounded-md">
                <p class="font-medium mb-1">📋 Níveis de Acesso:</p>
                <ul class="space-y-1">
                    @if(auth()->user()->isSuperAdmin())
                        <li><strong>Administrador:</strong> Acesso total aos agendamentos e configurações, gerencia colaboradores e clientes</li>
                    @endif
                    <li><strong>Colaborador:</strong> Cria e gerencia agendamentos, sem acesso às configurações do sistema</li>
                    <li><strong>Cliente:</strong> Área restrita, vê e gerencia apenas seus próprios agendamentos</li>
                </ul>
            </div>
        </div>

        <!-- Linha 4: Senha e Confirmação (apenas ao criar ou se editarSenha = true) -->
        @if(!$usuarioId || $editarSenha)
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Senha *</label>
                <input type="password" wire:model.defer="senha" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Mínimo 6 caracteres">
                @error('senha') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex-1 min-w-[180px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Senha *</label>
                <input type="password" wire:model.defer="senhaConfirmacao" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Digite a senha novamente">
                @error('senhaConfirmacao') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>
        @endif

        <!-- Linha Final: Botões Centralizados -->
        <div class="w-full flex justify-center mt-6">
            @if ($usuarioId)
                <button type="button" wire:click="resetCampos"
                        class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-4">
                    Cancelar
                </button>
                @if(!$editarSenha)
                    <button type="button" wire:click="$set('editarSenha', true)"
                            class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 mr-4">
                        Alterar Senha
                    </button>
                @endif
            @endif

            <button type="submit" 
                    class="bg-gray-800 text-white px-6 py-2 rounded hover:bg-gray-900"
                    wire:loading.attr="disabled">
                <span wire:loading.remove>
                    {{ $usuarioId ? 'Atualizar' : 'Cadastrar' }}
                </span>
                <span wire:loading>
                    Salvando...
                </span>
            </button>
        </div>

    </form>

    {{-- FILTROS --}}
    <div class="bg-gray-50 rounded-lg p-4 mb-4">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar usuário</label>
                <input type="text" 
                       wire:model.live.debounce.300ms="busca"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Nome ou email...">
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                <select wire:model.live="filtroTipo" 
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos os tipos</option>
                    @if(auth()->user()->isSuperAdmin())
                        <option value="admin">Administrador</option>
                    @endif
                    <option value="colaborador">Colaborador</option>
                    <option value="usuario">Cliente</option>
                </select>
            </div>
            @if($busca || $filtroTipo)
                <div class="flex items-end">
                    <button wire:click="limparFiltros" 
                            class="bg-gray-500 text-white px-3 py-2 rounded text-sm hover:bg-gray-600">
                        Limpar
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- LISTA DE USUÁRIOS --}}
    <h3 class="text-xl font-bold mb-2">Lista de Usuários</h3>

    <!-- Mostra aviso em telas pequenas -->
    <p class="text-sm text-gray-500 mb-1 sm:hidden">Deslize para o lado →</p>

    <!-- Scroll horizontal somente no mobile -->
    <div class="overflow-x-auto w-full">
        <table class="w-full min-w-[700px] text-sm table-auto border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2">Nome</th>
                    <th class="p-2">Email</th>
                    <th class="p-2">Telefone</th>
                    <th class="p-2">Tipo</th>
                    <th class="p-2">Cadastro</th>
                    <th class="p-2">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($usuarios as $usuario)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-2">{{ $usuario->name }}</td>
                        <td class="p-2">{{ $usuario->email }}</td>
                        <td class="p-2">{{ $usuario->telefone }}</td>
                        <td class="p-2">
                            @switch($usuario->tipo_usuario)
                                @case('super_admin')
                                    <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">
                                        Super Admin
                                    </span>
                                    @break
                                @case('admin')
                                    <span class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded-full">
                                        Admin
                                    </span>
                                    @break
                                @case('colaborador')
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                                        Colaborador
                                    </span>
                                    @break
                                @default
                                    <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">
                                        Cliente
                                    </span>
                            @endswitch
                        </td>
                        <td class="p-2">{{ $usuario->created_at->format('d/m/Y') }}</td>
                        <td class="p-2">
                            <button wire:click="editar({{ $usuario->id }})" class="text-blue-600 hover:underline">Editar</button>
                                                   
                            @if($usuario->isDeletable())
                            <button wire:click="excluir({{ $usuario->id }})" 
                                    wire:confirm="Tem certeza que deseja excluir este usuário?"
                                    class="text-red-600 hover:bg-red-100 text-xs px-2 py-1 rounded transition-colors">
                                Excluir
                            </button>
                            @else
                                <span class="text-gray-400 ml-2" title="Não pode ser excluído">Protegido</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-gray-500 p-4">Nenhum usuário encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINAÇÃO --}}
    @if($usuarios->hasPages())
        <div class="mt-4">
            {{ $usuarios->links() }}
        </div>
    @endif

</div>