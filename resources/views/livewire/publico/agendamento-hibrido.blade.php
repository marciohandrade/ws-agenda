<div class="bg-white rounded-lg shadow-lg p-6">
    {{-- INDICADOR DE PROGRESSO --}}
    <div class="mb-8">
        <div class="flex items-center justify-center space-x-4">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full {{ $etapaAtual >= 1 ? 'bg-blue-600 text-white' : 'bg-gray-300' }} flex items-center justify-center text-sm font-bold">
                    1
                </div>
                <span class="ml-2 text-sm {{ $etapaAtual >= 1 ? 'text-blue-600 font-medium' : 'text-gray-500' }}">Agendamento</span>
            </div>
            <div class="w-16 h-1 {{ $etapaAtual >= 2 ? 'bg-blue-600' : 'bg-gray-300' }} rounded"></div>
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full {{ $etapaAtual >= 2 ? 'bg-blue-600 text-white' : 'bg-gray-300' }} flex items-center justify-center text-sm font-bold">
                    2
                </div>
                <span class="ml-2 text-sm {{ $etapaAtual >= 2 ? 'text-blue-600 font-medium' : 'text-gray-500' }}">Identificação</span>
            </div>
            <div class="w-16 h-1 {{ $etapaAtual >= 3 ? 'bg-green-600' : 'bg-gray-300' }} rounded"></div>
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full {{ $etapaAtual >= 3 ? 'bg-green-600 text-white' : 'bg-gray-300' }} flex items-center justify-center text-sm font-bold">
                    3
                </div>
                <span class="ml-2 text-sm {{ $etapaAtual >= 3 ? 'text-green-600 font-medium' : 'text-gray-500' }}">Confirmação</span>
            </div>
        </div>
    </div>

    {{-- ERRO GERAL --}}
    @if($mensagemErro)
        <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ $mensagemErro }}
        </div>
    @endif

    {{-- ETAPA 1: DADOS DO AGENDAMENTO --}}
    @if($etapaAtual == 1)
        <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
                <i class="fas fa-calendar-plus mr-2 text-blue-600"></i>
                Escolha seu agendamento
            </h2>

            <form wire:submit="proximaEtapa" class="space-y-6">
                {{-- Serviço --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-medical mr-2 text-blue-600"></i>
                        Serviço *
                    </label>
                    
                    @if(isset($servicos) && is_array($servicos) && count($servicos) > 0)
                        <select wire:model="servico_id" class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('servico_id') border-red-500 @enderror">
                            <option value="">Selecione um serviço...</option>
                            @foreach($servicos as $servico)
                                <option value="{{ $servico['id'] }}">{{ $servico['display_completo'] }}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="w-full px-3 py-3 border border-red-300 rounded-lg bg-red-50">
                            <p class="text-red-600 text-sm">❌ Nenhum serviço disponível.</p>
                        </div>
                    @endif
                    
                    @error('servico_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    
                    {{-- Descrição do serviço selecionado --}}
                    @if($servico_id && isset($servicos))
                        @php
                            $servicoSelecionado = collect($servicos)->firstWhere('id', $servico_id);
                        @endphp
                        @if($servicoSelecionado && !empty($servicoSelecionado['descricao']))
                            <div class="mt-2 p-3 bg-blue-50 rounded-lg">
                                <p class="text-sm text-blue-700">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    {{ $servicoSelecionado['descricao'] }}
                                </p>
                            </div>
                        @endif
                    @endif
                </div>

                {{-- CALENDÁRIO PERSONALIZADO - SEMPRE VISÍVEL --}}
                <div wire:loading.class="opacity-50" wire:target="mesAnterior,mesProximo,selecionarData">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar mr-2 text-blue-600"></i>
                        Escolha a Data *
                    </label>
                    
                    {{-- Navegação do Calendário --}}
                    <div class="bg-gray-50 p-4 rounded-lg border">
                        <div class="flex justify-between items-center mb-4">
                            <button type="button" wire:click="mesAnterior" 
                                    class="p-2 hover:bg-gray-200 rounded-lg transition-colors">
                                <i class="fas fa-chevron-left text-gray-600"></i>
                            </button>
                            <h3 class="text-lg font-semibold text-gray-800">
                                {{ $this->nomesMeses[$mesAtual] }} {{ $anoAtual }}
                            </h3>
                            <button type="button" wire:click="mesProximo" 
                                    class="p-2 hover:bg-gray-200 rounded-lg transition-colors">
                                <i class="fas fa-chevron-right text-gray-600"></i>
                            </button>
                        </div>
                        
                        {{-- Grid do Calendário --}}
                        <div class="grid grid-cols-7 gap-1 text-center text-sm">
                            {{-- Cabeçalho dos dias da semana --}}
                            <div class="p-2 font-semibold text-gray-600">Dom</div>
                            <div class="p-2 font-semibold text-gray-600">Seg</div>
                            <div class="p-2 font-semibold text-gray-600">Ter</div>
                            <div class="p-2 font-semibold text-gray-600">Qua</div>
                            <div class="p-2 font-semibold text-gray-600">Qui</div>
                            <div class="p-2 font-semibold text-gray-600">Sex</div>
                            <div class="p-2 font-semibold text-gray-600">Sáb</div>
                            
                            {{-- Dias do calendário --}}
                            @foreach($this->dadosCalendario as $dia)
                                <div class="relative">
                                    @if($dia['isOutroMes'])
                                        {{-- Dia de outro mês --}}
                                        <div class="p-3 text-gray-300 cursor-default min-h-[2.5rem] flex items-center justify-center">
                                            {{ $dia['dia'] }}
                                        </div>
                                    @elseif($dia['isPassado'])
                                        {{-- Dia passado --}}
                                        <div class="p-3 bg-gray-100 text-gray-400 cursor-not-allowed min-h-[2.5rem] flex items-center justify-center rounded">
                                            {{ $dia['dia'] }}
                                        </div>
                                    @elseif(!$dia['isFuncionamento'])
                                        {{-- Dia sem funcionamento --}}
                                        <div class="p-3 bg-red-50 text-red-400 cursor-not-allowed min-h-[2.5rem] flex items-center justify-center rounded border border-red-200">
                                            {{ $dia['dia'] }}
                                            <span class="absolute top-0 right-0 text-xs">🚫</span>
                                        </div>
                                    @elseif($dia['isDisponivel'])
                                        {{-- Dia disponível --}}
                                        <button type="button" 
                                                wire:click="selecionarData('{{ $dia['data'] }}')"
                                                class="w-full p-3 min-h-[2.5rem] flex items-center justify-center rounded transition-all duration-200 
                                                       {{ $dia['isSelecionado'] 
                                                          ? 'bg-blue-600 text-white border-blue-600' 
                                                          : 'bg-white border border-gray-200 hover:bg-blue-50 hover:border-blue-300 text-gray-800' }}
                                                       {{ $dia['isHoje'] ? 'ring-2 ring-blue-200' : '' }}">
                                            {{ $dia['dia'] }}
                                            @if($dia['isHoje'])
                                                <span class="absolute top-0 right-0 text-xs">📍</span>
                                            @endif
                                        </button>
                                    @else
                                        {{-- Dia indisponível (bloqueado) --}}
                                        <div class="p-3 bg-yellow-50 text-yellow-600 cursor-not-allowed min-h-[2.5rem] flex items-center justify-center rounded border border-yellow-200">
                                            {{ $dia['dia'] }}
                                            <span class="absolute top-0 right-0 text-xs">⚠️</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        
                        {{-- Legenda --}}
                        <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-blue-600 rounded mr-1"></div>
                                <span class="text-gray-600">Selecionado</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-white border rounded mr-1"></div>
                                <span class="text-gray-600">Disponível</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-red-50 border border-red-200 rounded mr-1"></div>
                                <span class="text-gray-600">Fechado</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-yellow-50 border border-yellow-200 rounded mr-1"></div>
                                <span class="text-gray-600">Bloqueado</span>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Campo hidden para a data selecionada --}}
                    <input type="hidden" wire:model="dataAgendamento">
                    
                    @error('dataAgendamento')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    
                    {{-- Feedback da data selecionada --}}
                    @if($dataSelecionada)
                        <div class="mt-2 p-3 bg-green-50 rounded-lg border border-green-200">
                            <p class="text-sm text-green-700">
                                <i class="fas fa-check-circle mr-1"></i>
                                Data selecionada: <strong>{{ \Carbon\Carbon::parse($dataSelecionada)->format('d/m/Y') }}</strong>
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Horário (campo manual por enquanto) --}}
                @if($dataSelecionada)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-clock mr-2 text-blue-600"></i>
                            Horário *
                        </label>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <p class="text-yellow-700 text-sm mb-2">
                                <i class="fas fa-construction mr-1"></i>
                                <strong>Temporário:</strong> Digite o horário manualmente. A grade de horários disponíveis será implementada em breve.
                            </p>
                            <input type="time" wire:model="horarioAgendamento" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Ex: 14:30">
                        </div>
                        @error('horarioAgendamento')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                {{-- Observações --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-comment mr-2 text-blue-600"></i>
                        Observações (opcional)
                    </label>
                    <textarea wire:model="observacoes" rows="3" 
                              class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Descreva informações importantes para o atendimento..."></textarea>
                </div>

                {{-- Botão continuar --}}
                <div class="flex justify-end">
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium disabled:opacity-50"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove>Continuar <i class="fas fa-arrow-right ml-2"></i></span>
                        <span wire:loading>
                            <i class="fas fa-spinner fa-spin mr-2"></i>Carregando...
                        </span>
                    </button>
                </div>
            </form>
        </div>

    {{-- ETAPA 2: LOGIN/CADASTRO --}}
    @elseif($etapaAtual == 2)
        <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
                Para finalizar, identifique-se
            </h2>

            {{-- Resumo do agendamento --}}
            <div class="bg-blue-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-blue-800 mb-2">Resumo do agendamento:</h3>
                <div class="text-sm space-y-1">
                    @if($servico_id && isset($servicos))
                        @php
                            $servicoSelecionado = collect($servicos)->firstWhere('id', $servico_id);
                        @endphp
                        @if($servicoSelecionado)
                            <p><strong>Serviço:</strong> {{ $servicoSelecionado['nome'] }}</p>
                            <p><strong>Preço:</strong> {{ $servicoSelecionado['preco_formatado'] }}</p>
                            <p><strong>Duração:</strong> {{ $servicoSelecionado['duracao_formatada'] }}</p>
                        @endif
                    @endif
                    <p><strong>Data:</strong> {{ $dataAgendamento ? \Carbon\Carbon::parse($dataAgendamento)->format('d/m/Y') : 'Não informada' }}</p>
                    <p><strong>Horário:</strong> {{ $horarioAgendamento ?: 'Não informado' }}</p>
                </div>
            </div>

            {{-- Área de login/cadastro simplificada para teste --}}
            <div class="text-center">
                <p class="text-gray-600 mb-4">Funcionalidade de login/cadastro em desenvolvimento...</p>
                <button wire:click="etapaAnterior" 
                        class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar
                </button>
            </div>
        </div>

    {{-- ETAPA 3: SUCESSO --}}
    @else
        <div class="text-center">
            <i class="fas fa-check-circle text-6xl text-green-500 mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-800 mb-3">Agendamento Realizado!</h2>
            <p class="text-gray-600 mb-4">{{ $mensagemSucesso }}</p>
            <button wire:click="$set('etapaAtual', 1)" 
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Fazer Outro Agendamento
            </button>
        </div>
    @endif
</div>