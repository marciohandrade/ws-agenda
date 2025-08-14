<!-- ✅ AGENDAMENTOS LISTA - MOBILE FIRST DESIGN -->
<div class="min-h-screen bg-gray-50" x-data="{ showFiltros: @entangle('showFiltros') }">
    
    <!-- ====== HEADER MOBILE OTIMIZADO ====== -->
    <div class="bg-white shadow-sm border-b sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-lg sm:text-xl font-bold text-gray-900">Agendamentos</h1>
                    <p class="text-xs text-gray-500 mt-1">{{ $resumo['hoje'] }} hoje • {{ $resumo['pendentes'] }} pendentes</p>
                </div>
                
                <div class="flex items-center space-x-2">
                    <!-- Botão Filtros Mobile -->
                    <button @click="showFiltros = !showFiltros" 
                            class="lg:hidden p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                    </button>
                    
                    <!-- Botão Novo -->
                    <a href="{{ route('painel.agendamentos.novo') }}" 
                       class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors flex items-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span class="hidden sm:inline">Novo</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ====== FILTROS RÁPIDOS - SEMPRE VISÍVEIS ====== -->
    <div class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <!-- Períodos Rápidos -->
            <div class="flex space-x-2 overflow-x-auto pb-2 scrollbar-hide">
                @php
                    $periodos = [
                        'hoje' => 'Hoje',
                        'amanha' => 'Amanhã', 
                        'semana' => 'Esta Semana',
                        'mes' => 'Este Mês',
                        'todos' => 'Todos'
                    ];
                @endphp
                
                @foreach($periodos as $valor => $label)
                <button wire:click="setPeriodo('{{ $valor }}')" 
                        class="flex-shrink-0 px-3 py-1.5 text-xs font-medium rounded-full transition-colors
                               {{ $filtroPeriodo === $valor 
                                  ? 'bg-blue-100 text-blue-800 border border-blue-200' 
                                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ $label }}
                    @if($valor === 'hoje' && $resumo['hoje'] > 0)
                        <span class="ml-1 bg-blue-600 text-white text-xs rounded-full px-1.5">{{ $resumo['hoje'] }}</span>
                    @endif
                </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- ====== FILTROS AVANÇADOS - COLAPSÁVEL ====== -->
    <div x-show="showFiltros" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-1"
         class="bg-white border-b shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Filtro Cliente -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Cliente</label>
                    <input type="text" wire:model.live.debounce.300ms="filtroCliente"
                           class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Nome do cliente...">
                </div>
                
                <!-- Filtro Data -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Data Específica</label>
                    <input type="date" wire:model.live="filtroData"
                           class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <!-- Filtro Status -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                    <select wire:model.live="filtroStatus"
                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos</option>
                        <option value="pendente">Pendente</option>
                        <option value="confirmado">Confirmado</option>
                        <option value="concluido">Concluído</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
            </div>
            
            <!-- Botão Limpar -->
            @if($filtroCliente || $filtroData || $filtroStatus)
            <div class="mt-3 flex justify-center">
                <button wire:click="limparFiltros" 
                        class="text-xs text-gray-600 hover:text-gray-800 px-3 py-1 hover:bg-gray-100 rounded transition-colors">
                    Limpar filtros
                </button>
            </div>
            @endif
        </div>
    </div>

    <!-- ====== CONTEÚDO PRINCIPAL ====== -->
    <div class="max-w-7xl mx-auto px-4 py-6">
        
        <!-- ====== RESUMO ESTATÍSTICAS (MOBILE) ====== -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-3 rounded-lg shadow-sm text-center">
                <div class="text-lg font-bold text-blue-600">{{ $resumo['hoje'] }}</div>
                <div class="text-xs text-gray-500">Hoje</div>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm text-center">
                <div class="text-lg font-bold text-yellow-600">{{ $resumo['pendentes'] }}</div>
                <div class="text-xs text-gray-500">Pendentes</div>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm text-center">
                <div class="text-lg font-bold text-green-600">{{ $resumo['confirmados'] }}</div>
                <div class="text-xs text-gray-500">Confirmados</div>
            </div>
            <div class="bg-white p-3 rounded-lg shadow-sm text-center">
                <div class="text-lg font-bold text-gray-600">{{ $resumo['total_mes'] }}</div>
                <div class="text-xs text-gray-500">Este Mês</div>
            </div>
        </div>

        <!-- ====== TOGGLE VIEW (Desktop) ====== -->
        <div class="hidden lg:flex justify-between items-center mb-4">
            <div class="text-sm text-gray-600">
                {{ $agendamentos->total() }} agendamento(s) encontrado(s)
            </div>
            <div class="flex bg-gray-100 rounded-lg p-1">
                <button wire:click="alterarView('cards')" 
                        class="px-3 py-1 text-xs font-medium rounded transition-colors
                               {{ $viewMode === 'cards' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600' }}">
                    Cards
                </button>
                <button wire:click="alterarView('table')" 
                        class="px-3 py-1 text-xs font-medium rounded transition-colors
                               {{ $viewMode === 'table' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600' }}">
                    Tabela
                </button>
            </div>
        </div>

        <!-- ====== LISTA DE AGENDAMENTOS - CARDS (Mobile First) ====== -->
        @if($viewMode === 'cards' || request()->header('User-Agent') && preg_match('/Mobile|Android|iPhone/i', request()->header('User-Agent')))
        <div class="space-y-3">
            @forelse($agendamentos as $agendamento)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <!-- Header do Card -->
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 text-sm">{{ $agendamento->cliente->nome ?? 'N/A' }}</h3>
                        <p class="text-xs text-gray-500">{{ $agendamento->cliente->telefone ?? '' }}</p>
                    </div>
                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full
                        {{ $agendamento->status === 'confirmado' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $agendamento->status === 'pendente' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $agendamento->status === 'concluido' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $agendamento->status === 'cancelado' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ ucfirst($agendamento->status) }}
                    </span>
                </div>

                <!-- Informações do Agendamento -->
                <div class="grid grid-cols-2 gap-3 mb-3 text-sm">
                    <div>
                        <div class="text-xs text-gray-500">Serviço</div>
                        <div class="font-medium text-gray-900">{{ $agendamento->servico->nome ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Data & Hora</div>
                        <div class="font-medium text-gray-900">
                            {{ $agendamento->data_agendamento->format('d/m') }} - 
                            {{ \Carbon\Carbon::parse($agendamento->horario_agendamento)->format('H:i') }}
                        </div>
                    </div>
                </div>

                @if($agendamento->observacoes)
                <div class="mb-3">
                    <div class="text-xs text-gray-500 mb-1">Observações</div>
                    <div class="text-sm text-gray-700 bg-gray-50 p-2 rounded">{{ $agendamento->observacoes }}</div>
                </div>
                @endif

                <!-- Ações -->
                <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-100">
                    @if($agendamento->status === 'pendente')
                        <button wire:click="alterarStatus({{ $agendamento->id }}, 'confirmado')" 
                                class="flex-1 sm:flex-none bg-green-50 text-green-700 text-xs px-3 py-1.5 rounded font-medium hover:bg-green-100 transition-colors">
                            Confirmar
                        </button>
                    @endif

                    @if($agendamento->status === 'confirmado')
                        <button wire:click="alterarStatus({{ $agendamento->id }}, 'concluido')" 
                                class="flex-1 sm:flex-none bg-blue-50 text-blue-700 text-xs px-3 py-1.5 rounded font-medium hover:bg-blue-100 transition-colors">
                            Concluir
                        </button>
                    @endif

                    <a href="{{ route('painel.agendamentos.editar', $agendamento->id) }}" 
                       class="flex-1 sm:flex-none bg-gray-50 text-gray-700 text-xs px-3 py-1.5 rounded font-medium hover:bg-gray-100 transition-colors text-center">
                        Editar
                    </a>

                    @if(!in_array($agendamento->status, ['cancelado', 'concluido']))
                        <button wire:click="alterarStatus({{ $agendamento->id }}, 'cancelado')" 
                                wire:confirm="Cancelar este agendamento?"
                                class="bg-red-50 text-red-700 text-xs px-3 py-1.5 rounded font-medium hover:bg-red-100 transition-colors">
                            Cancelar
                        </button>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum agendamento</h3>
                <p class="mt-1 text-sm text-gray-500">Nenhum agendamento encontrado com os filtros aplicados.</p>
            </div>
            @endforelse
        </div>
        @endif

        <!-- ====== TABELA (Desktop) ====== -->
        @if($viewMode === 'table' && !(request()->header('User-Agent') && preg_match('/Mobile|Android|iPhone/i', request()->header('User-Agent'))))
        <div class="hidden lg:block bg-white rounded-lg shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium text-gray-700">Cliente</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-700">Serviço</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-700">Data & Hora</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-700">Status</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-700">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($agendamentos as $agendamento)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $agendamento->cliente->nome ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $agendamento->cliente->telefone ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $agendamento->servico->nome ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $agendamento->data_agendamento->format('d/m/Y') }}</div>
                            <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($agendamento->horario_agendamento)->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full
                                {{ $agendamento->status === 'confirmado' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $agendamento->status === 'pendente' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $agendamento->status === 'concluido' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $agendamento->status === 'cancelado' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ ucfirst($agendamento->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <a href="{{ route('painel.agendamentos.editar', $agendamento->id) }}" 
                                   class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                    Editar
                                </a>
                                @if($agendamento->status === 'pendente')
                                    <button wire:click="alterarStatus({{ $agendamento->id }}, 'confirmado')" 
                                            class="text-green-600 hover:text-green-800 text-xs font-medium">
                                        Confirmar
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            Nenhum agendamento encontrado.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif

        <!-- ====== PAGINAÇÃO ====== -->
        @if($agendamentos->hasPages())
        <div class="mt-6">
            {{ $agendamentos->links() }}
        </div>
        @endif
    </div>

    <!-- ====== TOAST NOTIFICATIONS ====== -->
    <div x-data="{ 
        toasts: [],
        addToast(message, type = 'success') {
            const id = Date.now();
            this.toasts.push({ id, message, type });
            setTimeout(() => this.removeToast(id), 3000);
        },
        removeToast(id) {
            this.toasts = this.toasts.filter(toast => toast.id !== id);
        }
    }"
    @toast-sucesso.window="addToast($event.detail, 'success')"
    @toast-erro.window="addToast($event.detail, 'error')"
    class="fixed bottom-4 right-4 z-50 space-y-2">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="true"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-x-full"
                 x-transition:enter-end="opacity-100 transform translate-x-0"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 transform translate-x-0"
                 x-transition:leave-end="opacity-0 transform translate-x-full"
                 :class="toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'"
                 class="text-white px-4 py-3 rounded-lg shadow-lg text-sm font-medium max-w-sm">
                <span x-text="toast.message"></span>
            </div>
        </template>
    </div>
</div>

<style>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>