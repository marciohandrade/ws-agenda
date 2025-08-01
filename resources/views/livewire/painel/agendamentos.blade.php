<div class="max-w-7xl mx-auto p-4 sm:p-6 bg-white rounded shadow">
    <h2 class="text-xl sm:text-2xl font-bold mb-6 text-gray-800">Painel Administrativo / Cadastro de Agendamentos</h2>

    <!-- ✅ TOAST DE SUCESSO - IGUAL AO SISTEMA DE CLIENTES -->
    @if (session()->has('sucesso'))
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
                <span>{{ session('sucesso') }}</span>
            </div>
        </div>
    @endif

    <!-- ✅ TOAST DE ERRO -->
    @if (session()->has('erro'))
        <div 
            x-data="{ show: true }" 
            x-init="setTimeout(() => show = false, 4000)" 
            x-show="show"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-500"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            class="fixed bottom-6 right-6 z-[9999] w-auto max-w-sm bg-red-600 text-white text-sm rounded-lg shadow-lg px-5 py-3"
        >
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-200 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>{{ session('erro') }}</span>
            </div>
        </div>
    @endif

    <!-- Formulário de Cadastro/Edição -->
    <form wire:submit.prevent="salvar" class="w-full space-y-6 sm:space-y-8 mb-8 p-6 bg-gray-50 rounded-lg">
        
        <!-- Linha 1: Cliente e Serviço - Responsive -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
                <select wire:model="cliente_id" 
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Selecione um cliente</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}">
                            {{ $cliente->nome }} - {{ $cliente->telefone }}
                        </option>
                    @endforeach
                </select>
                @error('cliente_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Serviço *</label>
                <select wire:model="servico_id" 
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Selecione um serviço</option>
                    @foreach($servicos as $servico)
                        <option value="{{ $servico->id }}">
                            {{ $servico->nome }} - R$ {{ number_format($servico->preco, 2, ',', '.') }}
                        </option>
                    @endforeach
                </select>
                @error('servico_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Linha 2: Data, Horário e Status - Responsive Stack -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Data do Agendamento *</label>
                <input type="date" 
                       wire:model="data_agendamento"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('data_agendamento') border-red-500 @enderror"
                       min="{{ date('Y-m-d') }}">
                @error('data_agendamento') 
                    <span class="text-red-500 text-xs mt-1 block font-medium bg-red-50 p-2 rounded">
                        ⚠️ {{ $message }}
                    </span> 
                @enderror
            </div>

            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Horário *</label>
                <input type="time" 
                       wire:model="horario_agendamento"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('horario_agendamento') border-red-500 @enderror">
                @error('horario_agendamento') 
                    <span class="text-red-500 text-xs mt-1 block font-medium bg-red-50 p-2 rounded">
                        ⚠️ {{ $message }}
                    </span> 
                @enderror
            </div>

            <div class="w-full sm:col-span-2 lg:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                <select wire:model="status" 
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="pendente">Pendente</option>
                    <option value="confirmado">Confirmado</option>
                    <option value="concluido">Concluído</option>
                    <option value="cancelado">Cancelado</option>
                </select>
                @error('status') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Linha 3: Observações - Full Width -->
        <div class="w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
            <textarea wire:model="observacoes" 
                      class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                      rows="3" 
                      placeholder="Observações sobre o agendamento..."></textarea>
            @error('observacoes') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- ✅ BOTÕES PADRONIZADOS - IGUAL AO SISTEMA DE CLIENTES -->
        <div class="w-full flex justify-center mt-6 gap-8">

            <!-- BOTÃO CANCELAR - APARECE SEMPRE -->
            <button type="button" 
                    wire:click="cancelarFormulario"
                    class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 transition-colors">
                Cancelar
            </button>

            <!-- BOTÃO PRINCIPAL -->
            <button type="submit" 
                    class="bg-gray-800 text-white px-6 py-2 rounded hover:bg-gray-900 transition-colors">
                @if(isset($editando) && $editando)
                    Atualizar
                @else
                    Cadastrar
                @endif
            </button>
            
        </div>
    </form>

    <!-- Filtros de Pesquisa -->
    <div class="w-full space-y-6 mb-8 p-6 bg-gray-50 rounded-lg">
        <h3 class="text-xl font-bold">Filtros de Pesquisa</h3>
        
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[220px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pesquisar Cliente</label>
                <input type="text" 
                       wire:model.live="filtroCliente"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                       placeholder="Nome do cliente...">
            </div>

            <div class="flex-1 min-w-[180px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Data</label>
                <input type="date" 
                       wire:model.live="filtroData"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <div class="flex flex-col">
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select wire:model.live="filtroStatus" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Todos os status</option>
                <option value="pendente">Pendente</option>
                <option value="confirmado">Confirmado</option>
                <option value="concluido">Concluído</option>
                <option value="cancelado">Cancelado</option>
            </select>
        </div>

        <div class="flex justify-center items-center gap-3 pt-4">
            <button wire:click="limparFiltros" 
                    class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 transition-colors text-sm font-medium min-w-[120px]">
                Limpar Filtros
            </button>
        </div>
    </div>

    <!-- Lista de Agendamentos -->
    <div class="space-y-4">
        <h3 class="text-xl font-bold text-gray-800">Lista de Agendamentos</h3>
        <p class="text-sm text-gray-500 lg:hidden">Deslize para o lado →</p>

        <div class="overflow-x-auto w-full">
            <table class="w-full min-w-[900px] text-sm table-auto border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-4 text-left font-medium text-gray-700">Cliente</th>
                        <th class="p-4 text-left font-medium text-gray-700">Serviço</th>
                        <th class="p-4 text-left font-medium text-gray-700">Data & Hora</th>
                        <th class="p-4 text-left font-medium text-gray-700">Status</th>
                        <th class="p-4 text-left font-medium text-gray-700">Observações</th>
                        <th class="p-4 text-left font-medium text-gray-700">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agendamentos as $agendamento)
                        <tr class="border-t border-gray-200 hover:bg-gray-50">
                            <td class="p-4">
                                <div class="font-medium text-gray-900">{{ $agendamento->cliente->nome ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $agendamento->cliente->telefone ?? '' }}</div>
                            </td>
                            <td class="p-4">
                                <div class="font-medium text-gray-900">{{ $agendamento->servico->nome ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $agendamento->servico->duracao_formatada ?? '' }}</div>
                            </td>
                            <td class="p-4">
                                <div class="font-medium text-gray-900">{{ $agendamento->data_agendamento->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($agendamento->horario_agendamento)->format('H:i') }}</div>
                            </td>
                            <td class="p-4">
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full
                                    @if($agendamento->status === 'confirmado') bg-green-100 text-green-800
                                    @elseif($agendamento->status === 'pendente') bg-yellow-100 text-yellow-800
                                    @elseif($agendamento->status === 'concluido') bg-blue-100 text-blue-800
                                    @elseif($agendamento->status === 'cancelado') bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($agendamento->status) }}
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="text-gray-700 text-xs">{{ $agendamento->observacoes ?? '-' }}</div>
                            </td>
                            <td class="p-4">
                                <div class="flex flex-wrap gap-1">
                                    @if($agendamento->status === 'pendente')
                                        <button wire:click="alterarStatus({{ $agendamento->id }}, 'confirmado')" 
                                                class="text-green-600 hover:bg-green-100 text-xs px-2 py-1 rounded transition-colors">
                                            Confirmar
                                        </button>
                                    @endif

                                    @if($agendamento->status === 'confirmado')
                                        <button wire:click="alterarStatus({{ $agendamento->id }}, 'concluido')" 
                                                class="text-blue-600 hover:bg-blue-100 text-xs px-2 py-1 rounded transition-colors">
                                            Concluir
                                        </button>
                                    @endif

                                    @if(!in_array($agendamento->status, ['cancelado', 'concluido']))
                                        <button wire:click="cancelar({{ $agendamento->id }})" 
                                                class="text-red-600 hover:bg-red-100 text-xs px-2 py-1 rounded transition-colors">
                                            Cancelar
                                        </button>
                                    @endif

                                    <button wire:click="editar({{ $agendamento->id }})" 
                                            class="text-indigo-600 hover:bg-indigo-100 text-xs px-2 py-1 rounded transition-colors">
                                        Editar
                                    </button>
                                    
                                    <button wire:click="excluir({{ $agendamento->id }})" 
                                            wire:confirm="Tem certeza que deseja excluir este agendamento?"
                                            class="text-red-600 hover:bg-red-100 text-xs px-2 py-1 rounded transition-colors">
                                        Excluir
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                Nenhum agendamento encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        @if($agendamentos->hasPages())
            <div class="mt-6">
                {{ $agendamentos->links() }}
            </div>
        @endif
    </div>
</div>