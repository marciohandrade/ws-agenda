<div class="space-y-6">
    {{-- ✅ FILTROS E BUSCA --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0 lg:space-x-4">
            
            {{-- BUSCA --}}
            <div class="flex-1 max-w-md">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="busca"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Buscar por serviço...">
                </div>
            </div>

            {{-- FILTROS --}}
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                
                {{-- FILTRO STATUS --}}
                <select wire:model.live="filtroStatus" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="todos">Todos os Status</option>
                    <option value="pendente">Pendente</option>
                    <option value="confirmado">Confirmado</option>
                    <option value="cancelado">Cancelado</option>
                    <option value="concluido">Concluído</option>
                </select>

                {{-- FILTRO DATA --}}
                <select wire:model.live="filtroData" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">Todas as Datas</option>
                    <option value="hoje">Hoje</option>
                    <option value="semana">Esta Semana</option>
                    <option value="futuros">Futuros</option>
                    <option value="passados">Passados</option>
                </select>

                {{-- BOTÃO LIMPAR --}}
                @if($busca || $filtroStatus !== 'todos' || $filtroData)
                    <button 
                        wire:click="limparFiltros"
                        class="px-3 py-2 text-gray-500 hover:text-gray-700 transition-colors">
                        <i class="fas fa-times mr-1"></i>
                        Limpar
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- ✅ LISTAGEM DE AGENDAMENTOS --}}
    @if($agendamentos->count() > 0)
        <div class="space-y-4">
            @foreach($agendamentos as $agendamento)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                    
                    {{-- DESKTOP VIEW --}}
                    <div class="hidden md:block p-6">
                        <div class="flex items-center justify-between">
                            
                            {{-- INFORMAÇÕES PRINCIPAIS --}}
                            <div class="flex items-center space-x-6">
                                
                                {{-- ÍCONE E STATUS --}}
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 rounded-full bg-{{ $agendamento->status_cor }}-100 flex items-center justify-center">
                                        <i class="{{ $agendamento->status_icone }} text-{{ $agendamento->status_cor }}-600"></i>
                                    </div>
                                </div>

                                {{-- DETALHES --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-3 mb-1">
                                        <h3 class="text-lg font-semibold text-gray-900 truncate">
                                            {{ $agendamento->servico_nome }}
                                        </h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $agendamento->status_cor }}-100 text-{{ $agendamento->status_cor }}-800">
                                            {{ $agendamento->status_texto }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                                        <div class="flex items-center">
                                            <i class="fas fa-calendar mr-1"></i>
                                            <span>{{ $agendamento->data_formatada }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="fas fa-clock mr-1"></i>
                                            <span>{{ $agendamento->horario_formatado }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="fas fa-tag mr-1"></i>
                                            <span>{{ $agendamento->servico_preco_formatado }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="fas fa-hashtag mr-1"></i>
                                            <span>{{ $agendamento->codigo }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- AÇÕES --}}
                            <div class="flex items-center space-x-2">
                                
                                {{-- VISUALIZAR --}}
                                <button 
                                    wire:click="visualizarDetalhes({{ $agendamento->id }})"
                                    class="px-3 py-1 text-blue-600 hover:text-blue-500 transition-colors">
                                    <i class="fas fa-eye mr-1"></i>
                                    Ver
                                </button>

                                {{-- CANCELAR (se possível) --}}
                                @if($agendamento->pode_cancelar)
                                    <button 
                                        wire:click="cancelarAgendamento({{ $agendamento->id }})"
                                        onclick="return confirm('Tem certeza que deseja cancelar este agendamento?')"
                                        class="px-3 py-1 text-red-600 hover:text-red-500 transition-colors">
                                        <i class="fas fa-times mr-1"></i>
                                        Cancelar
                                    </button>
                                @endif

                                {{-- REMARCAR --}}
                                <a href="/agendar" class="px-3 py-1 text-orange-600 hover:text-orange-500 transition-colors">
                                    <i class="fas fa-edit mr-1"></i>
                                    Novo
                                </a>
                            </div>
                        </div>

                        {{-- OBSERVAÇÕES (se existirem) --}}
                        @if($agendamento->observacoes)
                            <div class="mt-3 pt-3 border-t border-gray-200">
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-comment mr-1"></i>
                                    <strong>Observações:</strong> {{ $agendamento->observacoes }}
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- MOBILE VIEW --}}
                    <div class="md:hidden p-4">
                        <div class="flex items-start space-x-3">
                            
                            {{-- ÍCONE STATUS --}}
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-{{ $agendamento->status_cor }}-100 flex items-center justify-center">
                                    <i class="{{ $agendamento->status_icone }} text-{{ $agendamento->status_cor }}-600 text-sm"></i>
                                </div>
                            </div>

                            {{-- CONTEÚDO --}}
                            <div class="flex-1 min-w-0">
                                
                                {{-- TÍTULO E STATUS --}}
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="text-base font-semibold text-gray-900 truncate pr-2">
                                        {{ $agendamento->servico_nome }}
                                    </h3>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $agendamento->status_cor }}-100 text-{{ $agendamento->status_cor }}-800 flex-shrink-0">
                                        {{ $agendamento->status_texto }}
                                    </span>
                                </div>

                                {{-- INFORMAÇÕES --}}
                                <div class="space-y-1 text-sm text-gray-600 mb-3">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar w-4 mr-2"></i>
                                        <span>{{ $agendamento->data_formatada }} ({{ $agendamento->dia_semana_pt }})</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-clock w-4 mr-2"></i>
                                        <span>{{ $agendamento->horario_formatado }} - {{ $agendamento->servico_duracao_formatada }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-tag w-4 mr-2"></i>
                                        <span>{{ $agendamento->servico_preco_formatado }}</span>
                                    </div>
                                </div>

                                {{-- OBSERVAÇÕES MOBILE --}}
                                @if($agendamento->observacoes)
                                    <div class="mb-3 p-2 bg-gray-50 rounded text-xs text-gray-600">
                                        <i class="fas fa-comment mr-1"></i>
                                        {{ Str::limit($agendamento->observacoes, 80) }}
                                    </div>
                                @endif

                                {{-- AÇÕES MOBILE --}}
                                <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                                    <span class="text-xs text-gray-500">{{ $agendamento->codigo }}</span>
                                    
                                    <div class="flex items-center space-x-1">
                                        <button 
                                            wire:click="visualizarDetalhes({{ $agendamento->id }})"
                                            class="px-2 py-1 text-xs text-blue-600 hover:text-blue-500">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        @if($agendamento->pode_cancelar)
                                            <button 
                                                wire:click="cancelarAgendamento({{ $agendamento->id }})"
                                                onclick="return confirm('Cancelar agendamento?')"
                                                class="px-2 py-1 text-xs text-red-600 hover:text-red-500">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif

                                        <a href="/agendar" class="px-2 py-1 text-xs text-orange-600 hover:text-orange-500">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ✅ PAGINAÇÃO --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            {{ $agendamentos->links() }}
        </div>

    @else
        {{-- ✅ ESTADO VAZIO --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
            <div class="mb-4">
                <i class="fas fa-calendar-times text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    @if($busca || $filtroStatus !== 'todos' || $filtroData)
                        Nenhum agendamento encontrado
                    @else
                        Você ainda não tem agendamentos
                    @endif
                </h3>
                <p class="text-gray-500">
                    @if($busca || $filtroStatus !== 'todos' || $filtroData)
                        Tente ajustar os filtros ou fazer uma nova busca.
                    @else
                        Faça seu primeiro agendamento online de forma rápida e prática.
                    @endif
                </p>
            </div>
            
            <div class="space-y-3">
                @if($busca || $filtroStatus !== 'todos' || $filtroData)
                    <button 
                        wire:click="limparFiltros"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-filter mr-2"></i>
                        Limpar Filtros
                    </button>
                @endif
                
                <div>
                    <a href="/agendar" 
                       class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-plus mr-2"></i>
                        {{ $agendamentos->total() > 0 ? 'Novo Agendamento' : 'Fazer Primeiro Agendamento' }}
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- ✅ MODAL DE DETALHES --}}
    @if($mostrarModal && $agendamentoDetalhes)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            
            {{-- OVERLAY --}}
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="fecharModalDetalhes"></div>
                
                {{-- MODAL --}}
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    
                    {{-- HEADER --}}
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900" id="modal-title">
                                <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                                Detalhes do Agendamento
                            </h3>
                            <button 
                                wire:click="fecharModalDetalhes"
                                class="text-gray-400 hover:text-gray-600 transition-colors">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        {{-- CONTEÚDO --}}
                        <div class="space-y-4">
                            
                            {{-- STATUS --}}
                            <div class="flex items-center justify-between p-3 bg-{{ $agendamentoDetalhes->status_cor }}-50 rounded-lg">
                                <div class="flex items-center">
                                    <i class="{{ $agendamentoDetalhes->status_icone }} text-{{ $agendamentoDetalhes->status_cor }}-600 mr-2"></i>
                                    <span class="font-medium text-{{ $agendamentoDetalhes->status_cor }}-800">
                                        {{ $agendamentoDetalhes->status_texto }}
                                    </span>
                                </div>
                                <span class="text-sm text-{{ $agendamentoDetalhes->status_cor }}-600">
                                    {{ $agendamentoDetalhes->codigo }}
                                </span>
                            </div>

                            {{-- INFORMAÇÕES --}}
                            <div class="grid grid-cols-1 gap-3">
                                
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Serviço:</span>
                                    <span class="text-sm text-gray-900">{{ $agendamentoDetalhes->servico_nome }}</span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Data:</span>
                                    <span class="text-sm text-gray-900">{{ $agendamentoDetalhes->data_formatada }} ({{ $agendamentoDetalhes->dia_semana_pt }})</span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Horário:</span>
                                    <span class="text-sm text-gray-900">{{ $agendamentoDetalhes->horario_formatado }}</span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Duração:</span>
                                    <span class="text-sm text-gray-900">{{ $agendamentoDetalhes->servico_duracao_formatada }}</span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500">Valor:</span>
                                    <span class="text-sm text-gray-900 font-medium">{{ $agendamentoDetalhes->servico_preco_formatado }}</span>
                                </div>

                                @if($agendamentoDetalhes->observacoes)
                                    <div class="pt-2 border-t border-gray-200">
                                        <span class="text-sm font-medium text-gray-500">Observações:</span>
                                        <p class="text-sm text-gray-900 mt-1">{{ $agendamentoDetalhes->observacoes }}</p>
                                    </div>
                                @endif

                                <div class="pt-2 border-t border-gray-200 text-xs text-gray-500">
                                    <div class="flex justify-between">
                                        <span>Agendado em:</span>
                                        <span>{{ $agendamentoDetalhes->created_at_formatado }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- FOOTER --}}
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                            
                            @if($agendamentoDetalhes->pode_cancelar)
                                <button 
                                    wire:click="cancelarAgendamento({{ $agendamentoDetalhes->id }})"
                                    onclick="return confirm('Tem certeza que deseja cancelar este agendamento?')"
                                    class="w-full sm:w-auto px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition">
                                    <i class="fas fa-times mr-1"></i>
                                    Cancelar
                                </button>
                            @endif

                            <a href="/agendar" 
                               class="w-full sm:w-auto px-4 py-2 bg-orange-600 text-white text-sm rounded-lg hover:bg-orange-700 transition text-center">
                                <i class="fas fa-plus mr-1"></i>
                                Novo
                            </a>

                            <button 
                                wire:click="fecharModalDetalhes"
                                class="w-full sm:w-auto px-4 py-2 bg-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-400 transition">
                                Fechar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ✅ LOADING OVERLAY --}}
    <div wire:loading class="fixed inset-0 z-40 bg-gray-500 bg-opacity-75 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
            <i class="fas fa-spinner fa-spin text-blue-600"></i>
            <span class="text-gray-700">Carregando...</span>
        </div>
    </div>

    {{-- ✅ NOTIFICAÇÕES --}}
    @if (session()->has('success'))
        <div class="fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg">
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ session('error') }}
        </div>
    @endif
</div>