<div>
    <div class="max-w-6xl mx-auto px-4 py-6">
        
        {{-- Header com Ação Principal --}}
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Meus Agendamentos</h1>            
             <a href="#" 
               class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Novo Agendamento</span>
            </a>
        </div>

        {{-- Mensagens --}}
        @if($mensagemSucesso)
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                {{ $mensagemSucesso }}
            </div>
        @endif

        @if($mensagemErro)
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                {{ $mensagemErro }}
            </div>
        @endif

        {{-- Estatísticas em Cards --}}
        <!-- <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 text-center">
                <div class="text-2xl font-bold text-gray-700">{{ $estatisticas['total'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Total</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 text-center">
                <div class="text-2xl font-bold text-yellow-600">{{ $estatisticas['pendentes'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Pendentes</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $estatisticas['confirmados'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Confirmados</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 text-center">
                <div class="text-2xl font-bold text-green-600">{{ $estatisticas['concluidos'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Concluídos</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 text-center">
                <div class="text-2xl font-bold text-red-600">{{ $estatisticas['cancelados'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Cancelados</div>
            </div>
        </div> -->

        {{-- Filtros Compactos --}}
        <div class="bg-white rounded-lg p-4 shadow-sm mb-6" x-data="{ showFilters: false }">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900">Filtros</h2>
                <button @click="showFilters = !showFilters" 
                        class="text-blue-600 hover:text-blue-800 font-medium text-sm flex items-center">
                    <span x-text="showFilters ? 'Ocultar' : 'Mostrar'"></span>
                    <svg class="w-4 h-4 ml-1 transition-transform" :class="{ 'rotate-180': showFilters }" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
            
            <div x-show="showFilters" x-transition class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <select wire:model.live="filtroPeriodo" class="border rounded px-3 py-2 text-sm">
                    <option value="">Período</option>
                    <option value="hoje">Hoje</option>
                    <option value="semana">Esta semana</option>
                    <option value="mes">Este mês</option>
                    <option value="futuros">Futuros</option>
                    <option value="passados">Passados</option>
                </select>
                
                <select wire:model.live="filtroStatus" class="border rounded px-3 py-2 text-sm">
                    <option value="">Status</option>
                    <option value="pendente">Pendente</option>
                    <option value="confirmado">Confirmado</option>
                    <option value="concluido">Concluído</option>
                    <option value="cancelado">Cancelado</option>
                </select>
                
                <select wire:model.live="filtroServico" class="border rounded px-3 py-2 text-sm">
                    <option value="">Serviço</option>
                    @foreach($servicos as $servico)
                        <option value="{{ $servico->id }}">{{ $servico->nome }}</option>
                    @endforeach
                </select>
                
                <input type="date" wire:model.live="filtroData" class="border rounded px-3 py-2 text-sm">
            </div>
            
            @if($filtroStatus || $filtroData || $filtroServico || $filtroPeriodo)
                <div x-show="showFilters" class="mt-4 flex justify-center">
                    <button wire:click="limparFiltros" class="bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600">
                        Limpar Filtros
                    </button>
                </div>
            @endif
        </div>

        {{-- Lista de Agendamentos --}}
        <div class="space-y-4">
            @forelse($agendamentos as $agendamento)
                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                    {{-- Mobile Layout --}}
                    <div class="sm:hidden">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $agendamento->servico_nome }}</h3>
                                <p class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($agendamento->data_agendamento)->format('d/m/Y') }} às {{ \Carbon\Carbon::parse($agendamento->horario_agendamento)->format('H:i') }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full font-medium
                                @if($agendamento->status === 'confirmado') bg-blue-100 text-blue-800
                                @elseif($agendamento->status === 'pendente') bg-yellow-100 text-yellow-800
                                @elseif($agendamento->status === 'concluido') bg-green-100 text-green-800
                                @elseif($agendamento->status === 'cancelado') bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($agendamento->status) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-900">R$ {{ number_format($agendamento->servico_preco ?? 0, 2, ',', '.') }}</span>
                            <div class="flex space-x-2">
                                @if($this->podeSerCancelado($agendamento))
                                    <button wire:click="confirmarCancelamento({{ $agendamento->id }}, 'Cancelado pelo usuário')" 
                                            class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700"
                                            onclick="return confirm('Tem certeza que deseja cancelar?')">
                                        Cancelar
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Desktop Layout --}}
                    <div class="hidden sm:flex justify-between items-center">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">{{ $agendamento->servico_nome }}</h3>
                            <p class="text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($agendamento->data_agendamento)->format('d/m/Y') }} às 
                                {{ \Carbon\Carbon::parse($agendamento->horario_agendamento)->format('H:i') }}
                                • R$ {{ number_format($agendamento->servico_preco ?? 0, 2, ',', '.') }}
                            </p>
                            @if($agendamento->observacoes)
                                <p class="text-xs text-gray-500 mt-1">{{ Str::limit($agendamento->observacoes, 60) }}</p>
                            @endif
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <span class="px-3 py-1 text-sm rounded-full font-medium
                                @if($agendamento->status === 'confirmado') bg-blue-100 text-blue-800
                                @elseif($agendamento->status === 'pendente') bg-yellow-100 text-yellow-800
                                @elseif($agendamento->status === 'concluido') bg-green-100 text-green-800
                                @elseif($agendamento->status === 'cancelado') bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($agendamento->status) }}
                            </span>
                            
                            @if($this->podeSerCancelado($agendamento))
                                <button wire:click="confirmarCancelamento({{ $agendamento->id }}, 'Cancelado pelo usuário')" 
                                        class="bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700 transition-colors"
                                        onclick="return confirm('Tem certeza que deseja cancelar este agendamento?')">
                                    Cancelar
                                </button>
                            @endif
                            
                            @if($this->podeSerReagendado($agendamento))
                                <!-- <a href="{{ route('usuario.agendar') }}"  -->
                                 <a href="#"                     
                                   class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700 transition-colors">
                                    Reagendar
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg p-8 text-center shadow-sm">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum agendamento encontrado</h3>
                    <p class="text-gray-500 mb-6">
                        @if($filtroStatus || $filtroData || $filtroServico || $filtroPeriodo)
                            Tente ajustar os filtros ou limpar para ver todos os agendamentos.
                        @else
                            Você ainda não possui agendamentos. Que tal fazer o primeiro?
                        @endif
                    </p>
                    <a href="#"                     
                       class="inline-flex items-center bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Fazer Primeiro Agendamento
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Paginação --}}
        @if($agendamentos->hasPages())
            <div class="mt-6 flex justify-center">
                {{ $agendamentos->links() }}
            </div>
        @endif

    </div>

    {{-- Alpine.js CDN --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</div>