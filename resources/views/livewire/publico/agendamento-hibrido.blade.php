<div class="bg-white rounded-lg shadow-lg p-6">
    {{-- INDICADOR DE PROGRESSO --}}
    <div class="mb-8">
    {{-- Versão Desktop (md e acima) --}}
    <div class="hidden md:flex items-center justify-center space-x-4">
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

    {{-- Versão Mobile (abaixo de md) --}}
        <div class="md:hidden">
            <div class="flex items-center justify-center space-x-2 mb-3">
                <div class="w-8 h-8 rounded-full {{ $etapaAtual >= 1 ? 'bg-blue-600 text-white' : 'bg-gray-300' }} flex items-center justify-center text-sm font-bold">
                    1
                </div>
                <div class="w-8 h-1 {{ $etapaAtual >= 2 ? 'bg-blue-600' : 'bg-gray-300' }} rounded"></div>
                <div class="w-8 h-8 rounded-full {{ $etapaAtual >= 2 ? 'bg-blue-600 text-white' : 'bg-gray-300' }} flex items-center justify-center text-sm font-bold">
                    2
                </div>
                <div class="w-8 h-1 {{ $etapaAtual >= 3 ? 'bg-green-600' : 'bg-gray-300' }} rounded"></div>
                <div class="w-8 h-8 rounded-full {{ $etapaAtual >= 3 ? 'bg-green-600 text-white' : 'bg-gray-300' }} flex items-center justify-center text-sm font-bold">
                    3
                </div>
            </div>
            <div class="flex justify-between text-xs text-center">
                <div class="flex-1">
                    <span class="{{ $etapaAtual >= 1 ? 'text-blue-600 font-medium' : 'text-gray-500' }}">Agendamento</span>
                </div>
                <div class="flex-1">
                    <span class="{{ $etapaAtual >= 2 ? 'text-blue-600 font-medium' : 'text-gray-500' }}">Identificação</span>
                </div>
                <div class="flex-1">
                    <span class="{{ $etapaAtual >= 3 ? 'text-green-600 font-medium' : 'text-gray-500' }}">Confirmação</span>
                </div>
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

                {{-- GRADE DE HORÁRIOS --}}
                @if($dataSelecionada)
                    <div wire:loading.class="opacity-50" wire:target="selecionarHorario">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-clock mr-2 text-blue-600"></i>
                            Escolha o Horário *
                        </label>
                        
                        <div class="bg-gray-50 p-4 rounded-lg border">
                            @if($carregandoHorarios)
                                {{-- Loading state --}}
                                <div class="text-center py-8">
                                    <i class="fas fa-spinner fa-spin text-2xl text-blue-600 mb-2"></i>
                                    <p class="text-gray-600">Carregando horários disponíveis...</p>
                                </div>
                            @elseif(empty($horariosDisponiveis))
                                {{-- Nenhum horário disponível --}}
                                <div class="text-center py-8">
                                    <i class="fas fa-exclamation-triangle text-2xl text-yellow-500 mb-2"></i>
                                    <p class="text-gray-600 font-medium">Nenhum horário disponível para esta data</p>
                                    <p class="text-sm text-gray-500">Escolha outra data no calendário</p>
                                </div>
                            @else
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
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Horário selecionado: <strong>{{ $horarioSelecionado }}</strong>
                                </p>
                            </div>
                        @endif
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
    {{-- ETAPA 2: LOGIN/CADASTRO UNIFICADO --}}
@elseif($etapaAtual == 2)
    <div>
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
            <i class="fas fa-user-plus mr-2 text-blue-600"></i>
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
                    <i class="fas fa-sign-in-alt text-3xl text-blue-600 mb-3"></i>
                    <h3 class="font-bold text-lg mb-2">Já tenho conta</h3>
                    <p class="text-gray-600 text-sm">Faça login com seu e-mail e senha</p>
                </button>

                <button wire:click="definirTipoLogin('cadastro')" 
                        class="p-6 border-2 border-gray-300 rounded-lg hover:border-green-500 hover:bg-green-50 transition text-center">
                    <i class="fas fa-user-plus text-3xl text-green-600 mb-3"></i>
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
                        <i class="fas fa-arrow-left mr-1"></i> Voltar
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
                    <i class="fas fa-sparkles mr-2"></i>Cadastro Inteligente
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
                    <input type="tel" wire:model="telefone" 
                           class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('telefone') border-red-500 @enderror"
                           placeholder="(11) 99999-9999">
                    @error('telefone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Senha *</label>
                    <input type="password" wire:model="senha" 
                           class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('senha') border-red-500 @enderror"
                           placeholder="Mínimo 6 caracteres">
                    <p class="text-xs text-gray-500 mt-1">Deve conter letras e números</p>
                    @error('senha')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar senha *</label>
                    <input type="password" wire:model="senhaConfirmacao" 
                           class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('senhaConfirmacao') border-red-500 @enderror"
                           placeholder="Digite a senha novamente">
                    @error('senhaConfirmacao')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-between items-center pt-4">
                    <button type="button" wire:click="$set('tipoLogin', '')" 
                            class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-arrow-left mr-1"></i> Voltar
                    </button>
                    
                    <button type="submit" 
                            class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            <i class="fas fa-check mr-2"></i>Criar Conta e Agendar
                        </span>
                        <span wire:loading>
                            <i class="fas fa-spinner fa-spin mr-2"></i>Processando...
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
                    <i class="fas fa-arrow-left mr-1"></i> Voltar para escolha de horário
                </button>
            </div>
        @endif
    </div>

    {{-- ETAPA 3: SUCESSO --}}
    {{-- ETAPA 3: SUCESSO --}}
@else
    <div class="text-center">
        <div class="mb-6">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                <i class="fas fa-check text-2xl text-green-600"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-3">
                🎉 Agendamento Confirmado!
            </h2>
            <p class="text-gray-600">
                {{ $mensagemSucesso }}
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
                <p><strong>Status:</strong> <span class="text-orange-600 font-medium">Aguardando confirmação</span></p>
                @if($agendamentoId)
                    <p><strong>Código:</strong> <span class="font-mono text-blue-600">#{{ str_pad($agendamentoId, 6, '0', STR_PAD_LEFT) }}</span></p>
                @endif
            </div>
        </div>

        {{-- Informações pós-agendamento --}}
        <div class="bg-blue-50 rounded-lg p-6 mb-6">
            <h4 class="font-semibold text-blue-800 mb-2">
                <i class="fas fa-user-check mr-2"></i>Sua conta foi criada!
            </h4>
            <p class="text-blue-700 text-sm mb-3">
                Você já está logado no sistema e pode acompanhar seus agendamentos.
            </p>
            <div class="text-xs text-blue-600 space-y-1">
                <p><strong>Login:</strong> {{ $email }}</p>
                <p><strong>Acesso:</strong> Use a mesma senha que criou</p>
            </div>
        </div>

        <div class="space-y-3">
            <a href="/agendamentos" class="block w-full bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition text-center">
                <i class="fas fa-list mr-2"></i>Ver Meus Agendamentos
            </a>
            <button wire:click="$set('etapaAtual', 1)" 
                    class="block w-full bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition">
                <i class="fas fa-plus mr-2"></i>Fazer Outro Agendamento
            </button>
            <a href="/" class="block w-full text-gray-600 px-6 py-2 text-center hover:text-gray-800 transition">
                Voltar ao Site
            </a>
        </div>
    </div>
@endif
</div>