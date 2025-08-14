<!-- ✅ AGENDAMENTOS FORM - STEP BY STEP MOBILE OPTIMIZED -->
<div class="min-h-screen bg-gray-50">
    
    <!-- ====== HEADER MOBILE ====== -->
    <div class="bg-white shadow-sm border-b sticky top-0 z-40">
        <div class="max-w-2xl mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('painel.agendamentos.index') }}" 
                       class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">
                            {{ $editando ? 'Editar Agendamento' : 'Novo Agendamento' }}
                        </h1>
                        @if(!$editando)
                        <p class="text-xs text-gray-500">Etapa {{ $etapaAtual }} de 4</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Barra de Progresso (só para novos agendamentos) -->
            @if(!$editando)
            <div class="mt-3">
                <div class="bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                         style="width: {{ $progresso }}%"></div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- ====== FORMULÁRIO PRINCIPAL ====== -->
    <div class="max-w-2xl mx-auto px-4 py-6">
        <form wire:submit.prevent="salvar" class="space-y-6">
            
            <!-- ====== ETAPA 1: SELECIONAR CLIENTE ====== -->
            @if($etapaAtual == 1 || $editando)
            <div class="bg-white rounded-lg shadow-sm p-6 {{ $etapaAtual != 1 && !$editando ? 'opacity-50' : '' }}">
                <div class="flex items-center space-x-2 mb-4">
                    <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold">1</div>
                    <h2 class="text-lg font-semibold text-gray-900">Selecionar Cliente</h2>
                </div>

                @if($clienteSelecionado && $etapaAtual != 1)
                    <!-- Cliente já selecionado (modo collapsed) -->
                    <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                        <div>
                            <div class="font-medium text-green-900">{{ $clienteSelecionado->nome }}</div>
                            <div class="text-sm text-green-700">{{ $clienteSelecionado->telefone }}</div>
                        </div>
                        @if(!$editando)
                        <button type="button" wire:click="irParaEtapa(1)" 
                                class="text-green-600 hover:text-green-800 text-sm font-medium">
                            Alterar
                        </button>
                        @endif
                    </div>
                @else
                    <!-- Seleção de cliente -->
                    <div class="space-y-3">
                        <!-- Campo de busca -->
                        <div>
                            <input type="text" 
                                   wire:model.live.debounce.300ms="filtroCliente"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Buscar cliente por nome...">
                        </div>

                        <!-- Lista de clientes -->
                        <div class="max-h-64 overflow-y-auto space-y-2">
                            @forelse($clientes->filter(function($cliente) { 
                                return empty($this->filtroCliente) || 
                                       stripos($cliente->nome, $this->filtroCliente) !== false; 
                            }) as $cliente)
                            <button type="button" 
                                    wire:click="selecionarCliente({{ $cliente->id }})"
                                    class="w-full text-left p-3 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-colors
                                           {{ $cliente_id == $cliente->id ? 'bg-blue-50 border-blue-300' : '' }}">
                                <div class="font-medium text-gray-900">{{ $cliente->nome }}</div>
                                <div class="text-sm text-gray-500">{{ $cliente->telefone }}</div>
                            </button>
                            @empty
                            <div class="text-center py-4 text-gray-500">
                                <p class="text-sm">Nenhum cliente encontrado.</p>
                                <a href="{{ route('painel.clientes') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Cadastrar novo cliente
                                </a>
                            </div>
                            @endforelse
                        </div>
                        
                        @error('cliente_id') 
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </div>
            @endif

            <!-- ====== ETAPA 2: SELECIONAR SERVIÇO ====== -->
            @if($etapaAtual == 2 || $editando)
            <div class="bg-white rounded-lg shadow-sm p-6 {{ $etapaAtual != 2 && !$editando ? 'opacity-50' : '' }}">
                <div class="flex items-center space-x-2 mb-4">
                    <div class="w-8 h-8 {{ $etapaAtual >= 2 ? 'bg-blue-600' : 'bg-gray-300' }} text-white rounded-full flex items-center justify-center text-sm font-bold">2</div>
                    <h2 class="text-lg font-semibold text-gray-900">Selecionar Serviço</h2>
                </div>

                @if($servicoSelecionado && $etapaAtual != 2)
                    <!-- Serviço já selecionado -->
                    <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                        <div>
                            <div class="font-medium text-green-900">{{ $servicoSelecionado->nome }}</div>
                            <div class="text-sm text-green-700">R$ {{ number_format($servicoSelecionado->preco, 2, ',', '.') }}</div>
                        </div>
                        @if(!$editando)
                        <button type="button" wire:click="irParaEtapa(2)" 
                                class="text-green-600 hover:text-green-800 text-sm font-medium">
                            Alterar
                        </button>
                        @endif
                    </div>
                @else
                    <!-- Seleção de serviço -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($servicos as $servico)
                        <button type="button" 
                                wire:click="selecionarServico({{ $servico->id }})"
                                class="text-left p-4 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-colors
                                       {{ $servico_id == $servico->id ? 'bg-blue-50 border-blue-300' : '' }}">
                            <div class="font-medium text-gray-900">{{ $servico->nome }}</div>
                            <div class="text-sm text-gray-500 mt-1">R$ {{ number_format($servico->preco, 2, ',', '.') }}</div>
                            @if($servico->duracao)
                            <div class="text-xs text-gray-400 mt-1">{{ $servico->duracao }} min</div>
                            @endif
                        </button>
                        @endforeach
                    </div>
                    
                    @error('servico_id') 
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                @endif
            </div>
            @endif

            <!-- ====== ETAPA 3: DATA E HORÁRIO ====== -->
            @if($etapaAtual == 3 || $editando)
            <div class="bg-white rounded-lg shadow-sm p-6 {{ $etapaAtual != 3 && !$editando ? 'opacity-50' : '' }}">
                <div class="flex items-center space-x-2 mb-4">
                    <div class="w-8 h-8 {{ $etapaAtual >= 3 ? 'bg-blue-600' : 'bg-gray-300' }} text-white rounded-full flex items-center justify-center text-sm font-bold">3</div>
                    <h2 class="text-lg font-semibold text-gray-900">Data e Horário</h2>
                </div>

                <div class="space-y-4">
                    <!-- Seleção de Data -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data do Agendamento</label>
                        <input type="date" 
                               wire:model.live="data_agendamento"
                               min="{{ date('Y-m-d') }}"
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('data_agendamento') 
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Seleção de Horário -->
                    @if($data_agendamento)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Horário Disponível</label>
                        @if(count($horariosDisponiveis) > 0)
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                            @foreach($horariosDisponiveis as $horario)
                            <button type="button"
                                    wire:click="$set('horario_agendamento', '{{ $horario['horario'] }}')"
                                    @if(!$horario['disponivel']) disabled @endif
                                    class="p-2 text-sm font-medium rounded-lg transition-colors
                                           {{ !$horario['disponivel'] ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 
                                              ($horario_agendamento === $horario['horario'] ? 'bg-blue-600 text-white' : 'bg-gray-50 text-gray-700 hover:bg-gray-100') }}">
                                {{ $horario['horario'] }}
                            </button>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-4">
                            <p class="text-sm text-gray-500">Carregando horários disponíveis...</p>
                        </div>
                        @endif
                        
                        @error('horario_agendamento') 
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- ====== ETAPA 4: FINALIZAÇÃO ====== -->
            @if($etapaAtual == 4 || $editando)
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center space-x-2 mb-4">
                    <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold">4</div>
                    <h2 class="text-lg font-semibold text-gray-900">Finalizar Agendamento</h2>
                </div>

                <div class="space-y-4">
                    <!-- Resumo do Agendamento -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-3">Resumo do Agendamento</h3>
                        
                        @if($clienteSelecionado)
                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-sm text-gray-600">Cliente:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $clienteSelecionado->nome }}</span>
                        </div>
                        @endif
                        
                        @if($servicoSelecionado)
                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-sm text-gray-600">Serviço:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $servicoSelecionado->nome }}</span>
                        </div>
                        @endif
                        
                        @if($data_agendamento && $horario_agendamento)
                        <div class="flex justify-between py-2">
                            <span class="text-sm text-gray-600">Data & Hora:</span>
                            <span class="text-sm font-medium text-gray-900">
                                {{ \Carbon\Carbon::parse($data_agendamento)->format('d/m/Y') }} às {{ $horario_agendamento }}
                            </span>
                        </div>
                        @endif
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select wire:model="status" 
                                class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="pendente">Pendente</option>
                            <option value="confirmado">Confirmado</option>
                            <option value="concluido">Concluído</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                        @error('status') 
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Observações -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Observações (opcional)</label>
                        <textarea wire:model="observacoes" 
                                  rows="3"
                                  class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Observações sobre o agendamento..."></textarea>
                        @error('observacoes') 
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            @endif

            <!-- ====== BOTÕES DE NAVEGAÇÃO ====== -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between space-x-4">
                    @if(!$editando && $etapaAtual > 1)
                    <!-- Botão Voltar -->
                    <button type="button" 
                            wire:click="etapaAnterior"
                            class="flex-1 bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                        ← Voltar
                    </button>
                    @endif

                    @if(!$editando && $etapaAtual < 4)
                    <!-- Botão Próximo -->
                    <button type="button" 
                            wire:click="proximaEtapa"
                            class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                        Próximo →
                    </button>
                    @endif

                    @if($editando || $etapaAtual == 4)
                    <!-- Botões de Ação Final -->
                    <div class="flex space-x-3 w-full">
                        <button type="button" 
                                wire:click="cancelar"
                                class="flex-1 bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                            {{ $editando ? 'Atualizar' : 'Criar Agendamento' }}
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- ====== LOADING OVERLAY ====== -->
    <div wire:loading.flex class="fixed inset-0 bg-gray-900 bg-opacity-50 items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 text-center">
            <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-sm text-gray-600">Processando...</p>
        </div>
    </div>
</div>