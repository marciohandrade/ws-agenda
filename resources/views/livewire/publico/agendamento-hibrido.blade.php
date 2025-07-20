<div class="bg-white rounded-lg shadow-lg p-6">
    @php
        // ✅ LÓGICA HÍBRIDA: Detecta se usuário está logado
        $usuarioLogado = auth()->check();
        $totalEtapas = $usuarioLogado ? 2 : 3;
        
        // Ajusta etapa para usuários logados (pula identificação)
        $etapaExibida = $etapaAtual;
        if ($usuarioLogado && $etapaAtual == 3) {
            $etapaExibida = 2; // Para exibição, etapa 3 vira etapa 2
        }
    @endphp

    {{-- INDICADOR DE PROGRESSO HÍBRIDO --}}
    <div class="mb-8">
        {{-- Versão Desktop (md e acima) --}}
        <div class="hidden md:flex items-center justify-center space-x-4">
            {{-- ETAPA 1: AGENDAMENTO --}}
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full {{ $etapaAtual >= 1 ? 'bg-blue-600 text-white' : 'bg-gray-300' }} flex items-center justify-center text-sm font-bold">
                    1
                </div>
                <span class="ml-2 text-sm {{ $etapaAtual >= 1 ? 'text-blue-600 font-medium' : 'text-gray-500' }}">Agendamento</span>
            </div>

            @if(!$usuarioLogado)
                {{-- ETAPA 2: IDENTIFICAÇÃO (apenas para não logados) --}}
                <div class="w-16 h-1 {{ $etapaAtual >= 2 ? 'bg-blue-600' : 'bg-gray-300' }} rounded"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full {{ $etapaAtual >= 2 ? 'bg-blue-600 text-white' : 'bg-gray-300' }} flex items-center justify-center text-sm font-bold">
                        2
                    </div>
                    <span class="ml-2 text-sm {{ $etapaAtual >= 2 ? 'text-blue-600 font-medium' : 'text-gray-500' }}">Identificação</span>
                </div>
            @endif

            {{-- ETAPA FINAL: CONFIRMAÇÃO --}}
            <div class="w-16 h-1 {{ ($usuarioLogado && $etapaAtual >= 2) || (!$usuarioLogado && $etapaAtual >= 3) ? 'bg-green-600' : 'bg-gray-300' }} rounded"></div>
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full {{ ($usuarioLogado && $etapaAtual >= 2) || (!$usuarioLogado && $etapaAtual >= 3) ? 'bg-green-600 text-white' : 'bg-gray-300' }} flex items-center justify-center text-sm font-bold">
                    {{ $usuarioLogado ? '2' : '3' }}
                </div>
                <span class="ml-2 text-sm {{ ($usuarioLogado && $etapaAtual >= 2) || (!$usuarioLogado && $etapaAtual >= 3) ? 'text-green-600 font-medium' : 'text-gray-500' }}">Confirmação</span>
            </div>
        </div>

        {{-- Versão Mobile (abaixo de md) --}}
        <div class="md:hidden">
            <div class="flex items-center justify-center space-x-2 mb-3">
                <div class="w-8 h-8 rounded-full {{ $etapaAtual >= 1 ? 'bg-blue-600 text-white' : 'bg-gray-300' }} flex items-center justify-center text-sm font-bold">
                    1
                </div>
                
                @if(!$usuarioLogado)
                    <div class="w-8 h-1 {{ $etapaAtual >= 2 ? 'bg-blue-600' : 'bg-gray-300' }} rounded"></div>
                    <div class="w-8 h-8 rounded-full {{ $etapaAtual >= 2 ? 'bg-blue-600 text-white' : 'bg-gray-300' }} flex items-center justify-center text-sm font-bold">
                        2
                    </div>
                @endif
                
                <div class="w-8 h-1 {{ ($usuarioLogado && $etapaAtual >= 2) || (!$usuarioLogado && $etapaAtual >= 3) ? 'bg-green-600' : 'bg-gray-300' }} rounded"></div>
                <div class="w-8 h-8 rounded-full {{ ($usuarioLogado && $etapaAtual >= 2) || (!$usuarioLogado && $etapaAtual >= 3) ? 'bg-green-600 text-white' : 'bg-gray-300' }} flex items-center justify-center text-sm font-bold">
                    {{ $usuarioLogado ? '2' : '3' }}
                </div>
            </div>
            <div class="flex justify-between text-xs text-center">
                <div class="flex-1">
                    <span class="{{ $etapaAtual >= 1 ? 'text-blue-600 font-medium' : 'text-gray-500' }}">Agendamento</span>
                </div>
                @if(!$usuarioLogado)
                    <div class="flex-1">
                        <span class="{{ $etapaAtual >= 2 ? 'text-blue-600 font-medium' : 'text-gray-500' }}">Identificação</span>
                    </div>
                @endif
                <div class="flex-1">
                    <span class="{{ ($usuarioLogado && $etapaAtual >= 2) || (!$usuarioLogado && $etapaAtual >= 3) ? 'text-green-600 font-medium' : 'text-gray-500' }}">Confirmação</span>
                </div>
            </div>
        </div>
    </div>

    {{-- HEADER DINÂMICO --}}
    @if($usuarioLogado)
        <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <div class="flex items-center">
                <span class="text-2xl mr-3">👋</span>
                <div>
                    <h3 class="font-semibold text-blue-800">Olá, {{ auth()->user()->name }}!</h3>
                    <p class="text-blue-700 text-sm">Processo rápido: apenas {{ $totalEtapas }} etapas para seu agendamento.</p>
                </div>
            </div>
            <div class="mt-2 text-right">
                <a href="/meus-agendamentos" class="text-xs text-blue-600 hover:text-blue-800 underline">
                    📋 Ver meus agendamentos
                </a>
            </div>
        </div>
    @else
        <div class="mb-6 p-4 bg-green-50 rounded-lg border border-green-200">
            <div class="flex items-center">
                <span class="text-2xl mr-3">✨</span>
                <div>
                    <h3 class="font-semibold text-green-800">Bem-vindo!</h3>
                    <p class="text-green-700 text-sm">Processo completo em {{ $totalEtapas }} etapas. Criaremos sua conta automaticamente.</p>
                </div>
            </div>
        </div>
    @endif

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
                <span class="text-2xl mr-2">📅</span>
                Escolha seu agendamento
            </h2>

            <form wire:submit="proximaEtapa" class="space-y-6">
                {{-- ✅ SERVIÇO COM LOADING MELHORADO --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <span class="text-blue-600 mr-2">🏥</span>
                        Serviço *
                    </label>
                    
                    @if(isset($servicos) && is_array($servicos) && count($servicos) > 0)
                        <select wire:model.live="servico_id" 
                                wire:change="forcarRecargaHorarios"
                                class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('servico_id') border-red-500 @enderror">
                            <option value="">Selecione um serviço...</option>
                            @foreach($servicos as $servico)
                                <option value="{{ $servico['id'] }}">{{ $servico['display_completo'] }}</option>
                            @endforeach
                        </select>
                        
                        {{-- ✅ LOADING INDICATOR QUANDO SERVIÇO MUDA --}}
                        <div wire:loading wire:target="servico_id,forcarRecargaHorarios" class="mt-2">
                            <div class="flex items-center text-blue-600 text-sm">
                                <span class="mr-2">🔄</span>
                                Atualizando horários disponíveis...
                            </div>
                        </div>
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
                            <div class="mt-2 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <p class="text-sm text-blue-700">
                                    <span class="mr-1">ℹ️</span>
                                    {{ $servicoSelecionado['descricao'] }}
                                </p>
                                <p class="text-xs text-blue-600 mt-1">
                                    ⏱️ Duração: {{ $servicoSelecionado['duracao_formatada'] }} | 
                                    💰 Valor: {{ $servicoSelecionado['preco_formatado'] }}
                                </p>
                            </div>
                        @endif
                    @endif
                </div>

                {{-- CALENDÁRIO PERSONALIZADO - SEMPRE VISÍVEL --}}
                <div wire:loading.class="opacity-50" wire:target="mesAnterior,mesProximo,selecionarData">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <span class="text-blue-600 mr-2">📅</span>
                        Escolha a Data *
                    </label>
                    
                    {{-- Navegação do Calendário --}}
                    <div class="bg-gray-50 p-4 rounded-lg border">
                        <div class="flex justify-between items-center mb-4">
                            <button type="button" wire:click="mesAnterior" 
                                    class="p-2 hover:bg-gray-200 rounded-lg transition-colors">
                                <span class="text-gray-600">◀️</span>
                            </button>
                            <h3 class="text-lg font-semibold text-gray-800">
                                {{ $this->nomesMeses[$mesAtual] }} {{ $anoAtual }}
                            </h3>
                            <button type="button" wire:click="mesProximo" 
                                    class="p-2 hover:bg-gray-200 rounded-lg transition-colors">
                                <span class="text-gray-600">▶️</span>
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
                                <span class="mr-1">✅</span>
                                Data selecionada: <strong>{{ \Carbon\Carbon::parse($dataSelecionada)->format('d/m/Y') }}</strong>
                            </p>
                        </div>
                    @endif
                </div>

                {{-- ✅ GRADE DE HORÁRIOS COM MELHORIAS --}}
                @if($dataSelecionada)
                    <div wire:loading.class="opacity-50" wire:target="selecionarHorario,carregarHorarios,recarregarHorarios">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <span class="text-blue-600 mr-2">🕐</span>
                            Escolha o Horário *
                            @if($servico_id && isset($servicos))
                                @php
                                    $servicoSelecionado = collect($servicos)->firstWhere('id', $servico_id);
                                @endphp
                                @if($servicoSelecionado)
                                    <span class="text-xs text-gray-500 ml-1">
                                        (Intervalos de {{ $servicoSelecionado['duracao_formatada'] }})
                                    </span>
                                @endif
                            @endif
                        </label>
                        
                        <div class="bg-gray-50 p-4 rounded-lg border">
                            @if($carregandoHorarios)
                                {{-- Loading state --}}
                                <div class="text-center py-8">
                                    <div class="text-2xl mb-2">⏳</div>
                                    <p class="text-gray-600">Carregando horários disponíveis...</p>
                                    <p class="text-xs text-gray-500 mt-1">Calculando intervalos baseados no serviço selecionado</p>
                                </div>
                            @elseif(!$servico_id)
                                {{-- Nenhum serviço selecionado --}}
                                <div class="text-center py-8">
                                    <div class="text-2xl mb-2">⚠️</div>
                                    <p class="text-gray-600 font-medium">Selecione um serviço primeiro</p>
                                    <p class="text-sm text-gray-500">Os horários serão calculados baseados na duração do serviço</p>
                                </div>
                            @elseif(empty($horariosDisponiveis))
                                {{-- Nenhum horário disponível --}}
                                <div class="text-center py-8">
                                    <div class="text-2xl mb-2">😔</div>
                                    <p class="text-gray-600 font-medium">Nenhum horário disponível para esta data</p>
                                    <p class="text-sm text-gray-500">Escolha outra data no calendário</p>
                                </div>
                            @else
                                {{-- ✅ INFORMAÇÃO DO INTERVALO ATUAL --}}
                                @if($servico_id && isset($servicos))
                                    @php
                                        $servicoSelecionado = collect($servicos)->firstWhere('id', $servico_id);
                                    @endphp
                                    @if($servicoSelecionado)
                                        <div class="mb-3 p-2 bg-blue-50 rounded text-xs text-blue-700 text-center">
                                            <span class="mr-1">ℹ️</span>
                                            Horários organizados com intervalos de {{ $servicoSelecionado['duracao_formatada'] }} 
                                            para o serviço "{{ $servicoSelecionado['nome'] }}"
                                        </div>
                                    @endif
                                @endif
                                
                                {{-- Grade de horários --}}
                                <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
                                    @foreach($horariosDisponiveis as $horario)
                                        @if($horario['disponivel'])
                                            {{-- Horário disponível --}}
                                            <button type="button" 
                                                    wire:click="selecionarHorario('{{ $horario['value'] }}')"
                                                    class="p-3 rounded-lg border text-sm font-medium transition-all duration-200
                                                           {{ $horarioSelecionado === $horario['value'] 
                                                              ? 'bg-blue-600 text-white border-blue-600 shadow-md' 
                                                              : 'bg-white text-gray-700 border-gray-200 hover:bg-blue-50 hover:border-blue-300' }}">
                                                {{ $horario['display'] }}
                                            </button>
                                        @else
                                            {{-- Horário ocupado --}}
                                            <div class="p-3 rounded-lg border text-sm font-medium bg-red-50 text-red-400 border-red-200 cursor-not-allowed">
                                                {{ $horario['display'] }}
                                                <div class="text-xs">Ocupado</div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                
                                {{-- Legenda dos horários --}}
                                <div class="mt-4 flex flex-wrap justify-center gap-4 text-xs">
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
                                        <span class="text-gray-600">Ocupado</span>
                                    </div>
                                </div>
                                
                                {{-- ✅ BOTÃO PARA FORÇAR RECARGA --}}
                                @if($servico_id)
                                    <div class="mt-4 text-center">
                                        <button type="button" 
                                                wire:click="forcarRecargaHorarios"
                                                class="text-xs text-blue-600 hover:text-blue-800 underline">
                                            <span class="mr-1">🔄</span>Atualizar horários
                                        </button>
                                    </div>
                                @endif
                            @endif
                        </div>
                        
                        {{-- Campo hidden para o horário selecionado --}}
                        <input type="hidden" wire:model="horarioAgendamento">
                        
                        @error('horarioAgendamento')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        
                        {{-- Feedback do horário selecionado --}}
                        @if($horarioSelecionado)
                            <div class="mt-2 p-3 bg-green-50 rounded-lg border border-green-200">
                                <p class="text-sm text-green-700">
                                    <span class="mr-1">✅</span>
                                    Horário selecionado: <strong>{{ $horarioSelecionado }}</strong>
                                </p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Observações --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <span class="text-blue-600 mr-2">💬</span>
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
                        <span wire:loading.remove>
                            @if($usuarioLogado)
                                Finalizar Agendamento <span class="ml-2">✅</span>
                            @else
                                Continuar <span class="ml-2">➡️</span>
                            @endif
                        </span>
                        <span wire:loading>
                            <span class="mr-2">⏳</span>Processando...
                        </span>
                    </button>
                </div>
            </form>
        </div>

    {{-- ETAPA 2: LOGIN/CADASTRO (APENAS PARA USUÁRIOS NÃO LOGADOS) --}}
    @elseif($etapaAtual == 2 && !$usuarioLogado)
        <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
                <span class="text-2xl mr-2">👤</span>
                Finalize seu agendamento
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

            {{-- Mensagem de erro --}}
            @if($mensagemErro)
                <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ $mensagemErro }}
                </div>
            @endif

            {{-- Escolha do tipo: login ou cadastro --}}
            @if(!$tipoLogin)
                <div class="grid md:grid-cols-2 gap-4 mb-6">
                    <button wire:click="definirTipoLogin('login')" 
                            class="p-6 border-2 border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition text-center">
                        <span class="text-3xl mb-3 block">🔐</span>
                        <h3 class="font-bold text-lg mb-2">Já tenho conta</h3>
                        <p class="text-gray-600 text-sm">Faça login com seu e-mail e senha</p>
                    </button>

                    <button wire:click="definirTipoLogin('cadastro')" 
                            class="p-6 border-2 border-gray-300 rounded-lg hover:border-green-500 hover:bg-green-50 transition text-center">
                        <span class="text-3xl mb-3 block">✨</span>
                        <h3 class="font-bold text-lg mb-2">Primeira vez</h3>
                        <p class="text-gray-600 text-sm">Crie sua conta e agende em uma só etapa</p>
                    </button>
                </div>

            {{-- FORMULÁRIO DE LOGIN --}}
            @elseif($tipoLogin === 'login')
                <form wire:submit="fazerLogin" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                        <input type="email" wire:model="email" 
                               class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                        <input type="password" wire:model="senha" 
                               class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('senha') border-red-500 @enderror">
                        @error('senha')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-between items-center pt-4">
                        <button type="button" wire:click="$set('tipoLogin', '')" 
                                class="text-gray-600 hover:text-gray-800">
                            <span class="mr-1">◀️</span> Voltar
                        </button>
                        
                        <button type="submit" 
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Entrar e Agendar
                        </button>
                    </div>
                </form>

            {{-- FORMULÁRIO DE CADASTRO UNIFICADO --}}
            @elseif($tipoLogin === 'cadastro')
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <h4 class="font-semibold text-green-800 mb-2">
                        <span class="mr-2">✨</span>Cadastro Inteligente
                    </h4>
                    <p class="text-sm text-green-700">
                        Vamos criar sua conta e finalizar o agendamento em uma única etapa. 
                        Você já ficará logado no sistema para acompanhar seus agendamentos!
                    </p>
                </div>

                <form wire:submit="fazerCadastroUnificado" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome completo *</label>
                        <input type="text" wire:model="nome" 
                               class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nome') border-red-500 @enderror"
                               placeholder="Seu nome completo">
                        @error('nome')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">E-mail *</label>
                        <input type="email" wire:model="email" 
                               class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror"
                               placeholder="seu@email.com">
                        <p class="text-xs text-gray-500 mt-1">Este será seu login para acessar o sistema</p>
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefone *</label>
                        <input type="tel" 
                            wire:model="telefone"
                            x-data="{ 
                                formatPhone(value) {
                                    // Remove tudo que não é número
                                    let numbers = value.replace(/\D/g, '');
                                    
                                    // Formatação progressiva
                                    if (numbers.length === 0) return '';
                                    if (numbers.length === 1) return `(${numbers}`;
                                    if (numbers.length === 2) return `(${numbers}) `;
                                    if (numbers.length <= 6) return `(${numbers.slice(0,2)}) ${numbers.slice(2)}`;
                                    if (numbers.length <= 10) return `(${numbers.slice(0,2)}) ${numbers.slice(2,6)}-${numbers.slice(6)}`;
                                    // Para celular (11 dígitos)
                                    return `(${numbers.slice(0,2)}) ${numbers.slice(2,7)}-${numbers.slice(7,11)}`;
                                }
                            }"
                            x-on:input="$event.target.value = formatPhone($event.target.value)"
                            class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('telefone') border-red-500 @enderror"
                            placeholder="(11) 99999-9999"
                            maxlength="15">
                        @error('telefone')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Senha *</label>
                        <div class="relative">
                            <input type="password" 
                                id="senha" 
                                wire:model="senha" 
                                oninput="validarSenhas()"
                                class="w-full px-3 py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('senha') border-red-500 @enderror"
                                placeholder="Mínimo 6 caracteres">
                            
                            {{-- BOTÃO TOGGLE SENHA --}}
                            <button type="button" 
                                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700 focus:outline-none transition-colors"
                                    onclick="togglePassword('senha', 'eyeIconSenha')">
                                <span id="eyeIconSenha" class="text-lg" title="Mostrar senha">👁️</span>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Deve conter letras e números</p>
                        @error('senha')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar senha *</label>
                        <div class="relative">
                            <input type="password" 
                                id="senhaConfirmacao" 
                                wire:model="senhaConfirmacao" 
                                oninput="validarSenhas()"
                                class="w-full px-3 py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('senhaConfirmacao') border-red-500 @enderror"
                                placeholder="Digite a senha novamente">
                            
                            {{-- BOTÃO TOGGLE CONFIRMAÇÃO --}}
                            <button type="button" 
                                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700 focus:outline-none transition-colors"
                                    onclick="togglePassword('senhaConfirmacao', 'eyeIconConfirmacao')">
                                <span id="eyeIconConfirmacao" class="text-lg" title="Mostrar senha">👁️</span>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Deve conter letras e números</p>
                        
                        {{-- FEEDBACK DE VALIDAÇÃO EM TEMPO REAL --}}
                        <div id="senhaValidacao" class="mt-2 text-xs hidden">
                            <div id="senhaMatch" class="hidden">
                                <p class="text-green-600 flex items-center">
                                    <span class="mr-1">✅</span>
                                    As senhas coincidem
                                </p>
                            </div>
                            <div id="senhaDiferente" class="hidden">
                                <p class="text-red-600 flex items-center">
                                    <span class="mr-1">❌</span>
                                    As senhas não coincidem
                                </p>
                            </div>
                            <div id="senhaVazia" class="hidden">
                                <p class="text-gray-500 flex items-center">
                                    <span class="mr-1">⏳</span>
                                    Digite a confirmação da senha
                                </p>
                            </div>
                        </div>
                        
                        @error('senhaConfirmacao')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-between items-center pt-4">
                        <button type="button" wire:click="$set('tipoLogin', '')" 
                                class="text-gray-600 hover:text-gray-800">
                            <span class="mr-1">◀️</span> Voltar
                        </button>
                        
                        <button type="submit" 
                                class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove">
                                <span class="mr-2">✅</span>Criar Conta e Agendar
                            </span>
                            <span wire:loading>
                                <span class="mr-2">⏳</span>Processando...
                            </span>
                        </button>
                    </div>
                </form>
            @endif

            {{-- Botão voltar para etapa anterior --}}
            @if(!$tipoLogin)
                <div class="flex justify-center pt-6">
                    <button wire:click="etapaAnterior" 
                            class="text-gray-600 hover:text-gray-800">
                        <span class="mr-1">◀️</span> Voltar para escolha de horário
                    </button>
                </div>
            @endif
        </div>

    {{-- ETAPA FINAL: SUCESSO/CONFIRMAÇÃO --}}
    @else
        <div class="text-center">
            <div class="mb-6">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                    <span class="text-2xl">✅</span>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-3">
                    🎉 Agendamento Confirmado!
                </h2>
                <p class="text-gray-600">
                    {{ $mensagemSucesso ?? 'Seu agendamento foi criado com sucesso!' }}
                </p>
            </div>

            {{-- Resumo final --}}
            <div class="bg-green-50 rounded-lg p-6 mb-6">
                <h3 class="font-semibold text-green-800 mb-4">Detalhes do seu agendamento:</h3>
                <div class="space-y-2 text-sm">
                    @if($this->servicoSelecionado)
                        <p><strong>Serviço:</strong> {{ $this->servicoSelecionado['nome'] }}</p>
                        <p><strong>Preço:</strong> {{ $this->servicoSelecionado['preco_formatado'] }}</p>
                        <p><strong>Duração:</strong> {{ $this->servicoSelecionado['duracao_formatada'] }}</p>
                    @endif
                    <p><strong>Data:</strong> {{ \Carbon\Carbon::parse($dataAgendamento)->format('d/m/Y') }}</p>
                    <p><strong>Horário:</strong> {{ $horarioAgendamento }}</p>
                    <p><strong>Cliente:</strong> {{ $usuarioLogado ? auth()->user()->name : ($nome ?? 'Cadastrado com sucesso') }}</p>
                    <p><strong>Status:</strong> <span class="text-orange-600 font-medium">Aguardando confirmação</span></p>
                    @if($agendamentoId)
                        <p><strong>Código:</strong> <span class="font-mono text-blue-600">#{{ str_pad($agendamentoId, 6, '0', STR_PAD_LEFT) }}</span></p>
                    @endif
                </div>
            </div>

            {{-- Informações para novos usuários --}}
            @if(!$usuarioLogado && isset($nome))
                <div class="bg-blue-50 rounded-lg p-6 mb-6">
                    <h4 class="font-semibold text-blue-800 mb-2">
                        <span class="mr-2">👤</span>Sua conta foi criada!
                    </h4>
                    <p class="text-blue-700 text-sm mb-3">
                        Você já está logado no sistema e pode acompanhar seus agendamentos.
                    </p>
                    <div class="text-xs text-blue-600 space-y-1">
                        <p><strong>Login:</strong> {{ $email }}</p>
                        <p><strong>Acesso:</strong> Use a mesma senha que criou</p>
                    </div>
                </div>
            @endif

            {{-- Informações importantes --}}
            <div class="bg-yellow-50 rounded-lg p-6 mb-6">
                <h4 class="font-semibold text-yellow-800 mb-2">
                    <span class="mr-2">📋</span>Próximos passos
                </h4>
                <div class="text-yellow-700 text-sm space-y-1">
                    <p>• Você receberá uma confirmação por e-mail</p>
                    <p>• Fique atento ao telefone para confirmação</p>
                    <p>• Você pode alterar ou cancelar até 24h antes</p>
                </div>
            </div>

            <div class="space-y-3">
                <a href="/meus-agendamentos" class="block w-full bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition text-center">
                    <span class="mr-2">📋</span>Ver Meus Agendamentos
                </a>
                <button wire:click="$set('etapaAtual', 1)" 
                        class="block w-full bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition">
                    <span class="mr-2">➕</span>Fazer Outro Agendamento
                </button>
                <a href="/" class="block w-full text-gray-600 px-6 py-2 text-center hover:text-gray-800 transition">
                    Voltar ao Site
                </a>
            </div>
        </div>
    @endif

    {{-- ✅ JAVASCRIPT PARA EVENTS LISTENERS --}}
    <script>
    document.addEventListener('livewire:initialized', () => {
        // Listener para quando o serviço é alterado
        Livewire.on('servico-alterado', () => {
            console.log('Serviço alterado - horários serão recarregados');
        });

        // Listener para quando os horários são recarregados
        Livewire.on('horarios-recarregados', () => {
            console.log('Horários recarregados com sucesso');
        });
    });

    function togglePassword(inputId, iconId) {
    const passwordInput = document.getElementById(inputId);
    const eyeIcon = document.getElementById(iconId);
    
    if (passwordInput.type === 'password') {
        // Mostrar senha
        passwordInput.type = 'text';
        eyeIcon.textContent = '🙈'; // Olho fechado
        eyeIcon.title = 'Ocultar senha';
    } else {
        // Ocultar senha
        passwordInput.type = 'password';
        eyeIcon.textContent = '👁️'; // Olho aberto
        eyeIcon.title = 'Mostrar senha';
    }
}

// Função para validar senhas em tempo real
function validarSenhas() {
    const senha = document.getElementById('senha').value;
    const senhaConfirmacao = document.getElementById('senhaConfirmacao').value;
    
    const validacaoDiv = document.getElementById('senhaValidacao');
    const matchDiv = document.getElementById('senhaMatch');
    const differentDiv = document.getElementById('senhaDiferente');
    const emptyDiv = document.getElementById('senhaVazia');
    
    // Esconder todos os estados primeiro
    matchDiv.classList.add('hidden');
    differentDiv.classList.add('hidden');
    emptyDiv.classList.add('hidden');
    
    // Se senha principal estiver vazia, não mostrar nada
    if (!senha) {
        validacaoDiv.classList.add('hidden');
        return;
    }
    
    // Mostrar div de validação
    validacaoDiv.classList.remove('hidden');
    
    // Se confirmação estiver vazia
    if (!senhaConfirmacao) {
        emptyDiv.classList.remove('hidden');
        return;
    }
    
    // Comparar senhas
    if (senha === senhaConfirmacao) {
        matchDiv.classList.remove('hidden');
        
        // Adicionar borda verde nos campos
        document.getElementById('senha').classList.remove('border-red-500');
        document.getElementById('senhaConfirmacao').classList.remove('border-red-500');
        document.getElementById('senha').classList.add('border-green-500');
        document.getElementById('senhaConfirmacao').classList.add('border-green-500');
    } else {
        differentDiv.classList.remove('hidden');
        
        // Adicionar borda vermelha nos campos
        document.getElementById('senha').classList.remove('border-green-500');
        document.getElementById('senhaConfirmacao').classList.remove('border-green-500');
        document.getElementById('senha').classList.add('border-red-500');
        document.getElementById('senhaConfirmacao').classList.add('border-red-500');
    }
}

// Configurar títulos iniciais quando a página carrega
document.addEventListener('DOMContentLoaded', function() {
    const eyeIconSenha = document.getElementById('eyeIconSenha');
    const eyeIconConfirmacao = document.getElementById('eyeIconConfirmacao');
    
    if (eyeIconSenha) {
        eyeIconSenha.title = 'Mostrar senha';
    }
    if (eyeIconConfirmacao) {
        eyeIconConfirmacao.title = 'Mostrar senha';
    }
});

// Limpar bordas coloridas quando o usuário focar nos campos
document.addEventListener('DOMContentLoaded', function() {
    const senhaInput = document.getElementById('senha');
    const confirmacaoInput = document.getElementById('senhaConfirmacao');
    
    if (senhaInput) {
        senhaInput.addEventListener('focus', function() {
            this.classList.remove('border-green-500', 'border-red-500');
        });
    }
    
    if (confirmacaoInput) {
        confirmacaoInput.addEventListener('focus', function() {
            this.classList.remove('border-green-500', 'border-red-500');
        });
    }
});
    </script>
</div>