{{-- resources/views/livewire/painel/agendamentos-lista.blade.php --}}
<div class="space-y-6 p-4 lg:p-6">
    
    {{-- 🔍 CABEÇALHO COM BUSCA PRINCIPAL --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 lg:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            
            {{-- Título e Filtros de Período Compactos --}}
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-2xl font-bold text-gray-900">📅 Agendamentos</h1>
                    
                    {{-- Filtros de Período Compactos - Desktop --}}
                    <div class="hidden lg:flex items-center gap-2">
                        <button 
                            wire:click="filtrarPorPeriodo('todos')"
                            class="px-2 py-1 text-xs font-medium rounded-full transition-colors {{ $filtroPeriodo === 'todos' ? 'bg-green-100 text-green-800' : 'bg-gray-50 text-gray-600 hover:bg-green-50' }}"
                        >
                            📋 Todos: {{ $this->contadoresPeriodo['todos'] }}
                        </button>
                        <button 
                            wire:click="filtrarPorPeriodo('hoje')"
                            class="px-2 py-1 text-xs font-medium rounded-full transition-colors {{ $filtroPeriodo === 'hoje' ? 'bg-blue-100 text-blue-800' : 'bg-gray-50 text-gray-600 hover:bg-blue-50' }}"
                        >
                            📅 Hoje: {{ $this->contadoresPeriodo['hoje'] }}
                        </button>
                        <button 
                            wire:click="filtrarPorPeriodo('amanha')"
                            class="px-2 py-1 text-xs font-medium rounded-full transition-colors {{ $filtroPeriodo === 'amanha' ? 'bg-purple-100 text-purple-800' : 'bg-gray-50 text-gray-600 hover:bg-purple-50' }}"
                        >
                            🌅 Amanhã: {{ $this->contadoresPeriodo['amanha'] }}
                        </button>
                        <button 
                            wire:click="filtrarPorPeriodo('semana')"
                            class="px-2 py-1 text-xs font-medium rounded-full transition-colors {{ $filtroPeriodo === 'semana' ? 'bg-orange-100 text-orange-800' : 'bg-gray-50 text-gray-600 hover:bg-orange-50' }}"
                        >
                            📆 Semana: {{ $this->contadoresPeriodo['semana'] }}
                        </button>
                        <button 
                            wire:click="filtrarPorPeriodo('mes')"
                            class="px-2 py-1 text-xs font-medium rounded-full transition-colors {{ $filtroPeriodo === 'mes' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-50 text-gray-600 hover:bg-indigo-50' }}"
                        >
                            🗓️ Mês: {{ $this->contadoresPeriodo['mes'] }}
                        </button>
                    </div>
                </div>
                
                {{-- Filtros de Período Compactos - Mobile --}}
                <div class="flex lg:hidden items-center gap-1 text-xs overflow-x-auto pb-1 filtros-periodo-mobile">
                    <button 
                        wire:click="filtrarPorPeriodo('todos')"
                        class="px-2 py-1 text-xs font-medium rounded-full transition-colors whitespace-nowrap {{ $filtroPeriodo === 'todos' ? 'bg-green-100 text-green-800' : 'bg-gray-50 text-gray-600' }}"
                    >
                        📋 {{ $this->contadoresPeriodo['todos'] }}
                    </button>
                    <button 
                        wire:click="filtrarPorPeriodo('hoje')"
                        class="px-2 py-1 text-xs font-medium rounded-full transition-colors whitespace-nowrap {{ $filtroPeriodo === 'hoje' ? 'bg-blue-100 text-blue-800' : 'bg-gray-50 text-gray-600' }}"
                    >
                        📅 {{ $this->contadoresPeriodo['hoje'] }}
                    </button>
                    <button 
                        wire:click="filtrarPorPeriodo('amanha')"
                        class="px-2 py-1 text-xs font-medium rounded-full transition-colors whitespace-nowrap {{ $filtroPeriodo === 'amanha' ? 'bg-purple-100 text-purple-800' : 'bg-gray-50 text-gray-600' }}"
                    >
                        🌅 {{ $this->contadoresPeriodo['amanha'] }}
                    </button>
                    <button 
                        wire:click="filtrarPorPeriodo('semana')"
                        class="px-2 py-1 text-xs font-medium rounded-full transition-colors whitespace-nowrap {{ $filtroPeriodo === 'semana' ? 'bg-orange-100 text-orange-800' : 'bg-gray-50 text-gray-600' }}"
                    >
                        📆 {{ $this->contadoresPeriodo['semana'] }}
                    </button>
                    <button 
                        wire:click="filtrarPorPeriodo('mes')"
                        class="px-2 py-1 text-xs font-medium rounded-full transition-colors whitespace-nowrap {{ $filtroPeriodo === 'mes' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-50 text-gray-600' }}"
                    >
                        🗓️ {{ $this->contadoresPeriodo['mes'] }}
                    </button>
                </div>
            </div>

            {{-- Busca Unificada --}}
            <div class="flex-1 lg:max-w-md">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="buscaUnificada"
                        placeholder="🔍 Buscar por cliente, telefone, status ou data..."
                        class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-lg 
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                               text-sm placeholder-gray-500 bg-gray-50 focus:bg-white transition-colors"
                    >
                    @if($buscaUnificada)
                        <button 
                            wire:click="$set('buscaUnificada', '')"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Botões de Ação --}}
            <div class="flex items-center gap-2">
                <button 
                    wire:click="toggleFiltros"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                    </svg>
                    <span class="hidden sm:inline">Filtros</span>
                </button>

                {{-- 🔧 BOTÕES DE ALTERNÂNCIA - AGORA COM LÓGICA RESPONSIVA --}}
                @if($this->mostrarBotoesView)
                    <div class="hidden md:flex items-center bg-gray-100 rounded-lg p-1">
                        <button 
                            wire:click="alterarView('cards')"
                            class="p-2 rounded-md text-sm font-medium transition-colors {{ $viewMode === 'cards' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                            </svg>
                        </button>
                        <button 
                            wire:click="alterarView('table')"
                            class="p-2 rounded-md text-sm font-medium transition-colors {{ $viewMode === 'table' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- 🚀 FILTROS DE STATUS --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        
        {{-- Status Principais - USANDO DADOS REAIS --}}
        <div class="p-4 border-b border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                    <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                    Filtros por Status
                </h3>
                @if($filtroStatus)
                    <button 
                        wire:click="setStatus('')"
                        class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Limpar
                    </button>
                @endif
            </div>
            
            {{-- Container com Scroll Horizontal --}}
            <div class="relative overflow-hidden">
                {{-- Gradient Sombras nas Bordas --}}
                <div class="absolute left-0 top-0 bottom-0 w-8 bg-gradient-to-r from-white to-transparent z-10 pointer-events-none"></div>
                <div class="absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-l from-white to-transparent z-10 pointer-events-none"></div>
                
                {{-- Scroll Container com STATUS REAIS --}}
                <div class="flex gap-3 overflow-x-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 pb-2" 
                     style="scrollbar-width: thin; scroll-behavior: smooth;">
                    
                    {{-- Status Principais REAIS (vindos do Component) --}}
                    @foreach($statusPrincipais as $status => $config)
                        <button 
                            wire:click="setStatus('{{ $status }}')"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="flex items-center gap-2 px-4 py-3 rounded-lg border transition-all duration-200 whitespace-nowrap flex-shrink-0 min-w-fit
                                   {{ $filtroStatus === $status 
                                      ? $config['classes']['bg'] . ' ' . $config['classes']['text'] . ' ' . $config['classes']['border'] . ' ring-2 ring-opacity-20 shadow-md' 
                                      : 'bg-gray-50 text-gray-700 border-gray-200 hover:' . $config['classes']['bg'] . ' hover:' . $config['classes']['text'] . ' hover:border-opacity-50 hover:shadow-sm' 
                                   }}"
                            title="Filtrar por {{ $config['label'] }}"
                        >
                            <span class="text-lg">{{ $config['emoji'] }}</span>
                            <span class="font-medium text-sm">{{ $config['label'] }}</span>
                            @if($config['count'] > 0)
                                <span class="bg-white bg-opacity-80 text-xs font-bold px-2 py-1 rounded-full min-w-[24px] text-center shadow-sm">
                                    {{ $config['count'] }}
                                </span>
                            @endif
                        </button>
                    @endforeach

                    {{-- Status Secundários (se existirem e showStatusSecundarios estiver ativo) --}}
                    @if($showStatusSecundarios && count($statusSecundarios) > 0)
                        {{-- Separador --}}
                        <div class="flex-shrink-0 w-px bg-gray-300 my-2"></div>
                        
                        @foreach($statusSecundarios as $status => $config)
                            <button 
                                wire:click="setStatus('{{ $status }}')"
                                class="flex items-center gap-2 px-4 py-3 rounded-lg border transition-all duration-200 whitespace-nowrap flex-shrink-0 min-w-fit
                                       {{ $filtroStatus === $status 
                                          ? $config['classes']['bg'] . ' ' . $config['classes']['text'] . ' ' . $config['classes']['border'] . ' ring-2 ring-opacity-20 shadow-md' 
                                          : 'bg-gray-50 text-gray-700 border-gray-200 hover:' . $config['classes']['bg'] . ' hover:' . $config['classes']['text'] . ' hover:border-opacity-50 hover:shadow-sm' 
                                       }}"
                                title="Filtrar por {{ $config['label'] }}"
                            >
                                <span class="text-lg">{{ $config['emoji'] }}</span>
                                <span class="font-medium text-sm">{{ $config['label'] }}</span>
                                @if($config['count'] > 0)
                                    <span class="bg-white bg-opacity-80 text-xs font-bold px-2 py-1 rounded-full min-w-[24px] text-center shadow-sm">
                                        {{ $config['count'] }}
                                    </span>
                                @endif
                            </button>
                        @endforeach
                    @endif

                    {{-- Botão para mostrar status secundários (se existirem) --}}
                    @if(count($statusSecundarios) > 0)
                        <button 
                            wire:click="toggleStatusSecundarios"
                            class="flex items-center gap-2 px-3 py-3 rounded-lg border-2 border-dashed border-gray-300 text-gray-500 hover:border-gray-400 hover:text-gray-700 transition-all duration-200 whitespace-nowrap flex-shrink-0"
                            title="{{ $showStatusSecundarios ? 'Ocultar' : 'Mostrar' }} status secundários"
                        >
                            @if($showStatusSecundarios)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                                <span class="text-sm">Ocultar</span>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span class="text-sm">Mais ({{ $totalSecundarios }})</span>
                            @endif
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Filtros Avançados (quando expandidos) --}}
        @if($showFiltros)
            <div class="p-4 bg-gray-50 border-t border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    
                    {{-- Filtro de Cliente --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                        <select wire:model.live="filtroCliente" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <option value="">Todos os clientes</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}">{{ $cliente->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filtro de Serviço --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Serviço</label>
                        <select wire:model.live="filtroServico" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <option value="">Todos os serviços</option>
                            @foreach($servicos as $servico)
                                <option value="{{ $servico->id }}">{{ $servico->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filtro de Data --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data específica</label>
                        <input type="date" wire:model.live="filtroData" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    </div>

                    {{-- Filtro de Período --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Período</label>
                        <select wire:model.live="filtroPeriodo" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <option value="todos">Todos</option>
                            <option value="hoje">Hoje</option>
                            <option value="amanha">Amanhã</option>
                            <option value="semana">Esta semana</option>
                            <option value="mes">Este mês</option>
                        </select>
                    </div>
                </div>

                {{-- Botões de Ação dos Filtros --}}
                <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200">
                    <button 
                        wire:click="limparFiltros"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Limpar Filtros
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- 📊 LISTA DE AGENDAMENTOS --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        
        {{-- Cabeçalho da Lista --}}
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <h3 class="font-semibold text-gray-900">Agendamentos</h3>
                <span class="text-sm text-gray-500">({{ $agendamentos->total() }} {{ $agendamentos->total() === 1 ? 'resultado' : 'resultados' }})</span>
                
                {{-- Indicadores de filtros ativos --}}
                @if($filtroStatus)
                    <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                        Status: {{ $statusPrincipais[$filtroStatus]['label'] ?? ucfirst($filtroStatus) }}
                        <button wire:click="setStatus('')" class="ml-1 hover:text-blue-900">✕</button>
                    </span>
                @endif
                
                @if($filtroPeriodo !== 'todos')
                    <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                        Período: {{ ucfirst($filtroPeriodo) }}
                        <button wire:click="setPeriodo('todos')" class="ml-1 hover:text-green-900">✕</button>
                    </span>
                @endif
                
                {{-- 📱 Indicador de modo mobile no debug --}}
                @if(config('app.debug') && $this->isMobile)
                    <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                        📱 Mobile Mode
                    </span>
                @endif
            </div>
            
            <div class="flex items-center gap-2">
                <select wire:model.live="filtroOrdenacao" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="data_asc">Data ↑</option>
                    <option value="data_desc">Data ↓</option>
                    <option value="cliente">Cliente A-Z</option>
                    <option value="status">Status</option>
                </select>
            </div>
        </div>

        {{-- Conteúdo da Lista --}}
        <div class="p-4">
            @if($agendamentos->count() > 0)
                {{-- 🔧 VISUALIZAÇÃO INTELIGENTE - Mobile sempre cards, Desktop pode escolher --}}
                @if($viewMode === 'cards' || $this->isMobile)
                    {{-- Visualização em Cards --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($agendamentos as $agendamento)
                            @include('livewire.painel.partials.agendamento-card', ['agendamento' => $agendamento, 'statusConfig' => $statusConfig])
                        @endforeach
                    </div>
                @else
                    {{-- Visualização em Tabela (só para Desktop/Tablet) --}}
                    <div class="overflow-x-auto">
                        @include('livewire.painel.partials.agendamentos-table', ['agendamentos' => $agendamentos, 'statusConfig' => $statusConfig])
                    </div>
                @endif

                {{-- Paginação --}}
                <div class="mt-6">
                    {{ $agendamentos->links() }}
                </div>
            @else
                {{-- Estado Vazio --}}
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3a4 4 0 118 0v4m-4 8a3 3 0 100-6 3 3 0 000 6z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum agendamento encontrado</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if($buscaUnificada || $filtroCliente || $filtroData || $filtroStatus || $filtroServico || $filtroPeriodo !== 'todos')
                            Tente ajustar os filtros para encontrar o que procura.
                        @else
                            Que tal criar o primeiro agendamento?
                        @endif
                    </p>
                    @if($buscaUnificada || $filtroCliente || $filtroData || $filtroStatus || $filtroServico || $filtroPeriodo !== 'todos')
                        <div class="mt-6">
                            <button 
                                wire:click="limparFiltros"
                                class="inline-flex items-center gap-2 px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-blue-600 bg-blue-50 hover:bg-blue-100 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Limpar Filtros
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

{{-- CSS customizado para scroll horizontal --}}
<style>
    /* Scrollbar customizada */
    .scrollbar-thin {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #f7fafc;
    }
    
    .scrollbar-thin::-webkit-scrollbar {
        height: 6px;
    }
    
    .scrollbar-thin::-webkit-scrollbar-track {
        background: #f7fafc;
        border-radius: 3px;
    }
    
    .scrollbar-thin::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 3px;
    }
    
    .scrollbar-thin::-webkit-scrollbar-thumb:hover {
        background: #a0aec0;
    }
    
    /* Smooth scroll behavior */
    .scrollbar-thin {
        scroll-behavior: smooth;
    }
    
    /* Hide scrollbar for mobile */
    @media (max-width: 768px) {
        .scrollbar-thin::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-thin {
            scrollbar-width: none;
        }
    }
    
    /* 🆕 Scroll horizontal para filtros de período mobile */
    .filtros-periodo-mobile {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    
    .filtros-periodo-mobile::-webkit-scrollbar {
        display: none;
    }
</style>