<div>
    <div class="max-w-2xl mx-auto px-4 py-6">
        
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center mb-4">
                <a href="{{ route('usuario.meus-agendamentos') }}" 
                   class="text-gray-500 hover:text-gray-700 mr-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">
                    @if($reagendando)
                        Reagendar Agendamento
                    @else
                        Novo Agendamento
                    @endif
                </h1>
            </div>
            
            @if($reagendando && $agendamentoOriginal)
                <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-blue-800 font-medium text-sm">Reagendando agendamento</p>
                            <p class="text-blue-700 text-xs">
                                Original: {{ $agendamentoOriginal->servico_nome }} - 
                                {{ \Carbon\Carbon::parse($agendamentoOriginal->data_agendamento)->format('d/m/Y') }} às 
                                {{ \Carbon\Carbon::parse($agendamentoOriginal->horario_agendamento)->format('H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Mensagens --}}
        @if($mensagemSucesso)
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                {{ $mensagemSucesso }}
            </div>
        @endif

        @if($mensagemErro)
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                {{ $mensagemErro }}
            </div>
        @endif

        {{-- Formulário --}}
        <form wire:submit.prevent="salvar" class="space-y-6">
            
            {{-- Selecionar Serviço --}}
            <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    1. Escolha o Serviço
                </h2>
                
                <div class="grid grid-cols-1 gap-3">
                    @foreach($servicos as $servico)
                        <label class="relative cursor-pointer">
                            <input type="radio" 
                                   wire:model.live="servico_id" 
                                   value="{{ $servico->id }}" 
                                   class="sr-only peer">
                            <div class="border-2 border-gray-200 rounded-lg p-4 peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-300 transition-colors">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ $servico->nome }}</h3>
                                        @if($servico->descricao)
                                            <p class="text-sm text-gray-600 mt-1">{{ $servico->descricao }}</p>
                                        @endif
                                        <div class="flex items-center mt-2 text-sm text-gray-500">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $servico->duracao_minutos ?? 30 }} minutos
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-gray-900">
                                            R$ {{ number_format($servico->preco, 2, ',', '.') }}
                                        </div>
                                        <div class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-blue-500 peer-checked:bg-blue-500 flex items-center justify-center">
                                            <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('servico_id') <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span> @enderror
            </div>

            {{-- Selecionar Data --}}
            @if($servico_id)
                <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        2. Escolha a Data
                    </h2>
                    
                    <input type="date" 
                           wire:model.live="data_agendamento"
                           min="{{ date('Y-m-d') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 text-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('data_agendamento') border-red-500 @enderror">
                    
                    @error('data_agendamento') 
                        <div class="text-red-500 text-sm mt-2 p-3 bg-red-50 rounded-lg">
                            ⚠️ {{ $message }}
                        </div>
                    @enderror
                    
                    @if($data_agendamento)
                        <div class="mt-3 p-3 bg-blue-50 rounded-lg">
                            <p class="text-blue-800 text-sm font-medium">
                                📅 Data selecionada: {{ \Carbon\Carbon::parse($data_agendamento)->format('d/m/Y') }} 
                                ({{ \Carbon\Carbon::parse($data_agendamento)->locale('pt_BR')->dayName }})
                            </p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Selecionar Horário --}}
            @if($data_agendamento && $servico_id)
                <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200" wire:loading.class="opacity-50">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        3. Escolha o Horário
                        <div wire:loading wire:target="data_agendamento" class="ml-2">
                            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
                        </div>
                    </h2>
                    
                    @if(empty($horariosDisponiveis))
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-gray-500">Nenhum horário disponível para esta data</p>
                            <p class="text-sm text-gray-400 mt-1">Tente selecionar outra data</p>
                        </div>
                    @else
                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
                            @foreach($horariosDisponiveis as $horario)
                                <label class="cursor-pointer">
                                    <input type="radio" 
                                           wire:model="horario_agendamento" 
                                           value="{{ $horario }}" 
                                           class="sr-only peer">
                                    <div class="border-2 border-gray-200 rounded-lg p-3 text-center peer-checked:border-blue-500 peer-checked:bg-blue-500 peer-checked:text-white hover:border-gray-300 transition-colors">
                                        <div class="font-medium">{{ $horario }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endif
                    
                    @error('horario_agendamento') 
                        <div class="text-red-500 text-sm mt-3 p-3 bg-red-50 rounded-lg">
                            ⚠️ {{ $message }}
                        </div>
                    @enderror
                </div>
            @endif

            {{-- Observações --}}
            @if($horario_agendamento)
                <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        4. Observações (Opcional)
                    </h2>
                    
                    <textarea wire:model="observacoes" 
                              rows="4" 
                              class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Alguma observação especial sobre o agendamento..."></textarea>
                    
                    @error('observacoes') <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span> @enderror
                </div>
            @endif

            {{-- Resumo e Confirmação --}}
            @if($horario_agendamento && $servicoSelecionado)
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-6 border border-blue-200">
                    <h2 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Resumo do Agendamento
                    </h2>
                    
                    <div class="space-y-2 text-blue-800">
                        <p><strong>Serviço:</strong> {{ $servicoSelecionado->nome }}</p>
                        <p><strong>Data:</strong> {{ \Carbon\Carbon::parse($data_agendamento)->format('d/m/Y') }} 
                           ({{ \Carbon\Carbon::parse($data_agendamento)->locale('pt_BR')->dayName }})</p>
                        <p><strong>Horário:</strong> {{ $horario_agendamento }}</p>
                        <p><strong>Duração:</strong> {{ $servicoSelecionado->duracao_minutos ?? 30 }} minutos</p>
                        <p><strong>Valor:</strong> R$ {{ number_format($servicoSelecionado->preco, 2, ',', '.') }}</p>
                        @if($observacoes)
                            <p><strong>Observações:</strong> {{ $observacoes }}</p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Botões de Ação --}}
            @if($horario_agendamento)
                <div class="flex space-x-4 pt-6">
                    <button type="button" 
                            wire:click="cancelar"
                            class="flex-1 bg-gray-600 text-white py-4 px-6 rounded-lg hover:bg-gray-700 transition-colors font-medium">
                        Cancelar
                    </button>
                    
                    <button type="submit" 
                            wire:loading.attr="disabled"
                            class="flex-1 bg-blue-600 text-white py-4 px-6 rounded-lg hover:bg-blue-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center">
                        <span wire:loading.remove>
                            @if($reagendando)
                                Confirmar Reagendamento
                            @else
                                Confirmar Agendamento
                            @endif
                        </span>
                        <span wire:loading class="flex items-center">
                            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                            Processando...
                        </span>
                    </button>
                </div>
            @endif

        </form>

    </div>
</div>