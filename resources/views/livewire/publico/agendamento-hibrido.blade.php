<div>
{{-- ✅ WRAPPER ÚNICO PARA LIVEWIRE (SEM ALTERAR DESIGN) --}}

{{-- ✅ EXIBIÇÃO DE AGENDAMENTOS EXISTENTES (SE USUÁRIO LOGADO) --}}
@if($usuarioLogado && $exibirAgendamentos)
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- HEADER --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            <i class="fas fa-calendar-check mr-3 text-blue-600"></i>
                            Meus Agendamentos
                        </h1>
                        <p class="text-gray-600 mt-1">
                            Bem-vindo de volta! Aqui estão seus agendamentos.
                        </p>
                    </div>
                    
                    <button 
                        wire:click="novoAgendamento"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-plus mr-2"></i>
                        Novo Agendamento
                    </button>
                </div>
            </div>

            {{-- LISTAGEM DE AGENDAMENTOS --}}
            @if(count($agendamentosUsuario) > 0)
                <div class="space-y-4">
                    @foreach($agendamentosUsuario as $agendamento)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="h-12 w-12 rounded-full bg-{{ $agendamento['status_cor'] }}-100 flex items-center justify-center">
                                        <i class="fas fa-calendar text-{{ $agendamento['status_cor'] }}-600"></i>
                                    </div>
                                    
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            {{ $agendamento['servico_nome'] }}
                                        </h3>
                                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                                            <span>{{ $agendamento['data_formatada'] }}</span>
                                            <span>{{ $agendamento['horario_formatado'] }}</span>
                                            <span>{{ $agendamento['servico_preco_formatado'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $agendamento['status_cor'] }}-100 text-{{ $agendamento['status_cor'] }}-800">
                                    {{ $agendamento['status_texto'] }}
                                </span>
                            </div>
                            
                            @if($agendamento['observacoes'])
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <p class="text-sm text-gray-600">
                                        <strong>Observações:</strong> {{ $agendamento['observacoes'] }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                    <i class="fas fa-calendar-times text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-600 mb-2">Nenhum agendamento encontrado</h3>
                    <p class="text-gray-500 mb-6">Você ainda não possui agendamentos registrados.</p>
                    
                    <button 
                        wire:click="novoAgendamento"
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-plus mr-2"></i>
                        Fazer Primeiro Agendamento
                    </button>
                </div>
            @endif
        </div>
    </div>

@else
{{-- ✅ FLUXO PRINCIPAL DE AGENDAMENTO (DESIGN ORIGINAL MANTIDO) --}}

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-green-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- ✅ MENSAGENS DE FEEDBACK --}}
        @if($mensagemSucesso)
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <span class="text-green-800">{{ $mensagemSucesso }}</span>
                </div>
            </div>
        @endif

        @if($mensagemErro)
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                    <span class="text-red-800">{{ $mensagemErro }}</span>
                </div>
            </div>
        @endif

        {{-- ✅ HEADER DINÂMICO --}}
        <div class="text-center mb-8">
            @if($usuarioLogado)
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Novo Agendamento</h1>
                        <p class="text-gray-600 mt-2">Olá 1, {{ auth()->user()->name }}! Agende sua consulta rapidamente.</p>
                    </div>
                    
                    <button 
                        wire:click="alternarExibicaoAgendamentos"
                        class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition">
                        <i class="fas fa-list mr-2"></i>
                        Ver Meus Agendamentos
                    </button>
                </div>
            @else
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Agendamento Online</h1>
                <p class="text-gray-600">Agende sua consulta de forma rápida e prática</p>
            @endif
        </div>

        {{-- ✅ INDICADORES DE ETAPA --}}
        <div class="flex items-center justify-center mb-8">
            <div class="flex items-center space-x-4">
                {{-- ETAPA 1 --}}
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $etapaAtual >= 1 ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600' }}">
                        @if($etapaAtual > 1)
                            <i class="fas fa-check text-sm"></i>
                        @else
                            1
                        @endif
                    </div>
                    <span class="ml-2 text-sm font-medium {{ $etapaAtual >= 1 ? 'text-blue-600' : 'text-gray-500' }}">
                        Agendamento
                    </span>
                </div>

                {{-- LINHA CONECTORA --}}
                <div class="w-16 h-1 {{ $etapaAtual > 1 ? 'bg-blue-600' : 'bg-gray-300' }}"></div>

                {{-- ETAPA 2 (SÓ APARECE SE USUÁRIO NÃO LOGADO) --}}
                @if(!$usuarioLogado)
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $etapaAtual >= 2 ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600' }}">
                            @if($etapaAtual > 2)
                                <i class="fas fa-check text-sm"></i>
                            @else
                                2
                            @endif
                        </div>
                        <span class="ml-2 text-sm font-medium {{ $etapaAtual >= 2 ? 'text-blue-600' : 'text-gray-500' }}">
                            Identificação
                        </span>
                    </div>

                    {{-- LINHA CONECTORA --}}
                    <div class="w-16 h-1 {{ $etapaAtual > 2 ? 'bg-blue-600' : 'bg-gray-300' }}"></div>
                @endif

                {{-- ETAPA FINAL --}}
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-8 h-8 rounded-full {{ ($usuarioLogado && $etapaAtual >= 3) || (!$usuarioLogado && $etapaAtual >= 3) ? 'bg-green-600 text-white' : 'bg-gray-300 text-gray-600' }}">
                        @if(($usuarioLogado && $etapaAtual >= 3) || (!$usuarioLogado && $etapaAtual >= 3))
                            <i class="fas fa-check text-sm"></i>
                        @else
                            {{ $usuarioLogado ? '2' : '3' }}
                        @endif
                    </div>
                    <span class="ml-2 text-sm font-medium {{ ($usuarioLogado && $etapaAtual >= 3) || (!$usuarioLogado && $etapaAtual >= 3) ? 'text-green-600' : 'text-gray-500' }}">
                        Confirmação
                    </span>
                </div>
            </div>
        </div>

        {{-- ✅ CONTEÚDO DAS ETAPAS --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            
            {{-- ETAPA 1: AGENDAMENTO --}}
            @if($etapaAtual == 1)
                <div class="p-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">
                        <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
                        Escolha o Serviço, Data e Horário
                    </h2>

                    {{-- SELEÇÃO DE SERVIÇO --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            <i class="fas fa-stethoscope mr-1"></i>
                            Selecione o Serviço
                        </label>
                        <select wire:model.live="servico_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Escolha um serviço...</option>
                            @foreach($servicos as $servico)
                                <option value="{{ $servico['id'] }}">{{ $servico['display_completo'] }}</option>
                            @endforeach
                        </select>
                        @error('servico_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- CALENDÁRIO E HORÁRIOS --}}
                    @if($servico_id)
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            
                            {{-- CALENDÁRIO --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    <i class="fas fa-calendar mr-1"></i>
                                    Selecione a Data
                                </label>
                                
                                <div class="bg-gray-50 rounded-lg p-4">
                                    {{-- NAVEGAÇÃO DO CALENDÁRIO --}}
                                    <div class="flex items-center justify-between mb-4">
                                        <button wire:click="mesAnterior" class="p-2 hover:bg-gray-200 rounded">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <h3 class="text-lg font-semibold">
                                            {{ $nomesMeses[$mesAtual] }} {{ $anoAtual }}
                                        </h3>
                                        <button wire:click="mesProximo" class="p-2 hover:bg-gray-200 rounded">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>

                                    {{-- DIAS DA SEMANA --}}
                                    <div class="grid grid-cols-7 gap-1 mb-2">
                                        @foreach(['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'] as $dia)
                                            <div class="text-center text-xs font-medium text-gray-500 py-2">{{ $dia }}</div>
                                        @endforeach
                                    </div>

                                    {{-- DIAS DO MÊS --}}
                                    <div class="grid grid-cols-7 gap-1">
                                        @foreach($this->dadosCalendario as $dia)
                                            <button 
                                                wire:click="selecionarData('{{ $dia['data'] }}')"
                                                class="aspect-square text-sm rounded transition-colors
                                                    {{ $dia['isOutroMes'] ? 'text-gray-300' : 'text-gray-700' }}
                                                    {{ $dia['isPassado'] ? 'opacity-50 cursor-not-allowed' : '' }}
                                                    {{ $dia['isDisponivel'] && !$dia['isPassado'] ? 'hover:bg-blue-100 cursor-pointer' : '' }}
                                                    {{ !$dia['isFuncionamento'] && !$dia['isOutroMes'] ? 'bg-gray-100 text-gray-400' : '' }}
                                                    {{ $dia['isSelecionado'] ? 'bg-blue-600 text-white' : '' }}
                                                    {{ $dia['isHoje'] ? 'ring-2 ring-blue-300' : '' }}"
                                                {{ $dia['isPassado'] || !$dia['isDisponivel'] ? 'disabled' : '' }}>
                                                {{ $dia['dia'] }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                @error('dataAgendamento') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            {{-- HORÁRIOS --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    <i class="fas fa-clock mr-1"></i>
                                    Selecione o Horário
                                    @if($dataSelecionada)
                                        <span class="text-blue-600">({{ \Carbon\Carbon::parse($dataSelecionada)->format('d/m/Y') }})</span>
                                    @endif
                                </label>

                                <div class="bg-gray-50 rounded-lg p-4 max-h-80 overflow-y-auto">
                                    @if($carregandoHorarios)
                                        <div class="text-center py-8">
                                            <i class="fas fa-spinner fa-spin text-2xl text-blue-600 mb-2"></i>
                                            <p class="text-gray-600">Carregando horários...</p>
                                        </div>
                                    @elseif($dataSelecionada && count($horariosDisponiveis) > 0)
                                        <div class="grid grid-cols-2 gap-2">
                                            @foreach($horariosDisponiveis as $horario)
                                                <button 
                                                    wire:click="selecionarHorario('{{ $horario['value'] }}')"
                                                    class="p-3 text-sm rounded-lg border transition-colors
                                                        {{ $horario['disponivel'] ? 'border-gray-300 hover:border-blue-500 hover:bg-blue-50' : 'border-red-200 bg-red-50 text-red-400 cursor-not-allowed' }}
                                                        {{ $horarioSelecionado === $horario['value'] ? 'bg-blue-600 text-white border-blue-600' : '' }}"
                                                    {{ !$horario['disponivel'] ? 'disabled' : '' }}>
                                                    {{ $horario['display'] }}
                                                    @if(!$horario['disponivel'])
                                                        <br><small>Ocupado</small>
                                                    @endif
                                                </button>
                                            @endforeach
                                        </div>
                                    @elseif($dataSelecionada)
                                        <div class="text-center py-8">
                                            <i class="fas fa-calendar-times text-2xl text-gray-400 mb-2"></i>
                                            <p class="text-gray-600">Nenhum horário disponível para esta data</p>
                                        </div>
                                    @else
                                        <div class="text-center py-8">
                                            <i class="fas fa-hand-pointer text-2xl text-gray-400 mb-2"></i>
                                            <p class="text-gray-600">Selecione uma data primeiro</p>
                                        </div>
                                    @endif
                                </div>
                                @error('horarioAgendamento') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- OBSERVAÇÕES --}}
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-comment mr-1"></i>
                                Observações (opcional)
                            </label>
                            <textarea 
                                wire:model="observacoes" 
                                rows="3" 
                                placeholder="Alguma informação adicional sobre sua consulta..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        {{-- RESUMO DO AGENDAMENTO --}}
                        @if($servicoSelecionado && $dataSelecionada && $horarioSelecionado)
                            <div class="mt-6 bg-blue-50 rounded-lg p-4">
                                <h3 class="font-medium text-blue-900 mb-2">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Resumo do Agendamento
                                </h3>
                                <div class="text-sm text-blue-800 space-y-1">
                                    <p><strong>Serviço:</strong> {{ $servicoSelecionado['nome'] }}</p>
                                    <p><strong>Data:</strong> {{ \Carbon\Carbon::parse($dataSelecionada)->format('d/m/Y') }}</p>
                                    <p><strong>Horário:</strong> {{ $horarioSelecionado }}</p>
                                    <p><strong>Duração:</strong> {{ $servicoSelecionado['duracao_formatada'] }}</p>
                                    <p><strong>Valor:</strong> {{ $servicoSelecionado['preco_formatado'] }}</p>
                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- BOTÃO PRÓXIMA ETAPA --}}
                    <div class="mt-8 flex justify-end">
                        <button 
                            wire:click="proximaEtapa" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium"
                            {{ !$servico_id || !$dataSelecionada || !$horarioSelecionado ? 'disabled' : '' }}>
                            <i class="fas fa-arrow-right mr-2"></i>
                            {{ $usuarioLogado ? 'Confirmar Agendamento' : 'Próxima Etapa' }}
                        </button>
                    </div>
                </div>

            {{-- ETAPA 2: IDENTIFICAÇÃO (SÓ PARA NÃO LOGADOS) --}}
            @elseif($etapaAtual == 2 && !$usuarioLogado)
                <div class="p-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">
                        <i class="fas fa-user mr-2 text-blue-600"></i>
                        Identificação
                    </h2>

                    {{-- ESCOLHA DO TIPO DE LOGIN --}}
                    @if(!$tipoLogin)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <button 
                                wire:click="definirTipoLogin('login')"
                                class="p-6 border-2 border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition text-center">
                                <i class="fas fa-sign-in-alt text-3xl text-blue-600 mb-3"></i>
                                <h3 class="font-semibold text-gray-900 mb-2">Já tenho conta</h3>
                                <p class="text-sm text-gray-600">Fazer login com email e senha</p>
                            </button>

                            <button 
                                wire:click="definirTipoLogin('cadastro')"
                                class="p-6 border-2 border-gray-300 rounded-lg hover:border-green-500 hover:bg-green-50 transition text-center">
                                <i class="fas fa-user-plus text-3xl text-green-600 mb-3"></i>
                                <h3 class="font-semibold text-gray-900 mb-2">Primeira vez</h3>
                                <p class="text-sm text-gray-600">Criar conta automaticamente</p>
                            </button>
                        </div>
                    @endif

                    {{-- FORMULÁRIO DE LOGIN --}}
                    @if($tipoLogin === 'login')
                        <div class="bg-blue-50 rounded-lg p-6">
                            <h3 class="font-medium text-blue-900 mb-4">Login</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                                    <input type="email" wire:model="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="seu@email.com">
                                    @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                                    <input type="password" wire:model="senha" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Sua senha">
                                    @error('senha') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <button wire:click="fazerLogin" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                    <i class="fas fa-sign-in-alt mr-2"></i>
                                    Fazer Login
                                </button>
                            </div>
                        </div>

                    {{-- FORMULÁRIO DE CADASTRO --}}
                    @elseif($tipoLogin === 'cadastro')
                        <div class="bg-green-50 rounded-lg p-6">
                            <h3 class="font-medium text-green-900 mb-4">Cadastro Rápido</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo</label>
                                    <input type="text" wire:model="nome" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Seu nome completo">
                                    @error('nome') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                                    <input type="tel" wire:model="telefone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="(11) 99999-9999">
                                    @error('telefone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                                    <input type="email" wire:model="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="seu@email.com">
                                    @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                                    <input type="password" wire:model="senha" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Crie uma senha">
                                    @error('senha') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Senha</label>
                                    <input type="password" wire:model="senhaConfirmacao" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Confirme sua senha">
                                    @error('senhaConfirmacao') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <button wire:click="fazerCadastroUnificado" class="w-full mt-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                <i class="fas fa-user-plus mr-2"></i>
                                Criar Conta e Agendar
                            </button>
                        </div>
                    @endif

                    {{-- BOTÕES DE NAVEGAÇÃO --}}
                    <div class="mt-8 flex justify-between">
                        <button wire:click="etapaAnterior" class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Voltar
                        </button>

                        @if($tipoLogin)
                            <button wire:click="definirTipoLogin('')" class="px-6 py-3 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition">
                                <i class="fas fa-undo mr-2"></i>
                                Trocar Opção
                            </button>
                        @endif
                    </div>
                </div>

            {{-- ETAPA 3: CONFIRMAÇÃO --}}
            @elseif($etapaAtual == 3)
                <div class="p-8 text-center">
                    <div class="mb-6">
                        <i class="fas fa-check-circle text-6xl text-green-600 mb-4"></i>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Agendamento Realizado!</h2>
                        <p class="text-gray-600">Seu agendamento foi registrado com sucesso.</p>
                    </div>

                    @if($agendamentoId)
                        <div class="bg-green-50 rounded-lg p-6 mb-6">
                            <h3 class="font-semibold text-green-900 mb-4">Detalhes do Agendamento</h3>
                            
                            <div class="text-left space-y-2 text-sm">
                                <p><strong>Número:</strong> #{{ str_pad($agendamentoId, 6, '0', STR_PAD_LEFT) }}</p>
                                @if($servicoSelecionado)
                                    <p><strong>Serviço:</strong> {{ $servicoSelecionado['nome'] }}</p>
                                    <p><strong>Valor:</strong> {{ $servicoSelecionado['preco_formatado'] }}</p>
                                @endif
                                <p><strong>Data:</strong> {{ \Carbon\Carbon::parse($dataAgendamento)->format('d/m/Y') }}</p>
                                <p><strong>Horário:</strong> {{ $horarioAgendamento }}</p>
                                @if($observacoes)
                                    <p><strong>Observações:</strong> {{ $observacoes }}</p>
                                @endif
                                <p><strong>Status:</strong> <span class="text-yellow-600">Aguardando Confirmação</span></p>
                            </div>
                        </div>
                    @endif

                    <div class="bg-blue-50 rounded-lg p-6 mb-6">
                        <h4 class="font-medium text-blue-900 mb-2">Próximos Passos</h4>
                        <ul class="text-sm text-blue-800 text-left space-y-1">
                            <li>• Você receberá um email de confirmação</li>
                            <li>• Nossa equipe irá confirmar seu agendamento</li>
                            <li>• Em caso de dúvidas, entre em contato conosco</li>
                        </ul>
                    </div>

                    <div class="space-y-3">
                        @if($usuarioLogado)
                            <button wire:click="alternarExibicaoAgendamentos" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition mr-3">
                                <i class="fas fa-list mr-2"></i>
                                Ver Meus Agendamentos
                            </button>
                        @endif
                        
                        <button wire:click="novoAgendamento" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            <i class="fas fa-plus mr-2"></i>
                            Novo Agendamento
                        </button>
                        
                        <div class="mt-4">
                            <a href="/" class="text-gray-600 hover:text-gray-800 transition">
                                <i class="fas fa-home mr-1"></i>
                                Voltar ao Site
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@endif

{{-- ✅ LOADING OVERLAY --}}
@if($carregando)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
            <i class="fas fa-spinner fa-spin text-blue-600"></i>
            <span class="text-gray-700">Processando...</span>
        </div>
    </div>
@endif

</div>