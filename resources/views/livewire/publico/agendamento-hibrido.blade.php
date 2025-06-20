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

    {{-- ETAPA 1: DADOS DO AGENDAMENTO --}}
    @if($etapaAtual == 1)
        <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
                Escolha sua consulta
            </h2>

            <form wire:submit="proximaEtapa" class="space-y-6">
                {{-- Especialidade --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Especialidade *
                    </label>
                    <select wire:model.live="especialidade" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('especialidade') border-red-500 @enderror">
                        <option value="">Selecione uma especialidade</option>
                        @foreach($especialidades as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                    @error('especialidade')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Médico --}}
                @if($especialidade)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Médico *
                        </label>
                        <select wire:model="medico" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('medico') border-red-500 @enderror">
                            <option value="">Selecione um médico</option>
                            @foreach($medicos[$especialidade] ?? [] as $med)
                                <option value="{{ $med }}">{{ $med }}</option>
                            @endforeach
                        </select>
                        @error('medico')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                {{-- Data --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Data da consulta *
                    </label>
                    <input type="date" wire:model="dataAgendamento" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('dataAgendamento') border-red-500 @enderror"
                           min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                    @error('dataAgendamento')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Horário --}}
                @if($dataAgendamento)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Horário *
                        </label>
                        <div class="grid grid-cols-3 md:grid-cols-4 gap-2">
                            @foreach($horariosDisponiveis as $horario)
                                <button type="button" 
                                        wire:click="$set('horarioAgendamento', '{{ $horario }}')"
                                        class="px-3 py-2 border rounded-md text-sm transition {{ $horarioAgendamento === $horario ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-blue-50' }}">
                                    {{ $horario }}
                                </button>
                            @endforeach
                        </div>
                        @error('horarioAgendamento')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                {{-- Observações --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Observações (opcional)
                    </label>
                    <textarea wire:model="observacoes" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Descreva sintomas ou informações importantes..."></textarea>
                </div>

                {{-- Botão continuar --}}
                <div class="flex justify-end">
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition font-medium">
                        Continuar <i class="fas fa-arrow-right ml-2"></i>
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
                <h3 class="font-semibold text-blue-800 mb-2">Resumo da consulta:</h3>
                <div class="text-sm space-y-1">
                    <p><strong>Especialidade:</strong> {{ $especialidades[$especialidade] }}</p>
                    <p><strong>Médico:</strong> {{ $medico }}</p>
                    <p><strong>Data:</strong> {{ date('d/m/Y', strtotime($dataAgendamento)) }}</p>
                    <p><strong>Horário:</strong> {{ $horarioAgendamento }}</p>
                </div>
            </div>

            {{-- Mensagem de erro geral --}}
            @if($mensagemErro)
                <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ $mensagemErro }}
                </div>
            @endif

            {{-- Escolha do tipo de login --}}
            @if(!$tipoLogin)
                <div class="grid md:grid-cols-2 gap-4">
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
                        <p class="text-gray-600 text-sm">Crie sua conta em alguns segundos</p>
                    </button>
                </div>

            {{-- FORMULÁRIO DE LOGIN --}}
            @elseif($tipoLogin === 'login')
                <form wire:submit="fazerLogin" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                        <input type="email" wire:model="email" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                        <input type="password" wire:model="senha" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('senha') border-red-500 @enderror">
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
                                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                            Entrar e Agendar
                        </button>
                    </div>
                </form>

            {{-- FORMULÁRIO DE CADASTRO --}}
            @elseif($tipoLogin === 'cadastro')
                <form wire:submit="fazerCadastro" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome completo</label>
                        <input type="text" wire:model="nome" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nome') border-red-500 @enderror">
                        @error('nome')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                        <input type="email" wire:model="email" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                        <input type="tel" wire:model="telefone" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('telefone') border-red-500 @enderror"
                               placeholder="(11) 99999-9999">
                        @error('telefone')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                        <input type="password" wire:model="senha" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('senha') border-red-500 @enderror">
                        @error('senha')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar senha</label>
                        <input type="password" wire:model="senhaConfirmacao" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('senhaConfirmacao') border-red-500 @enderror">
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
                                class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove>Criar Conta e Agendar</span>
                            <span wire:loading>Processando...</span>
                        </button>
                    </div>
                </form>
            @endif

            {{-- Botão voltar para etapa anterior --}}
            @if(!$tipoLogin)
                <div class="flex justify-between items-center pt-6">
                    <button wire:click="etapaAnterior" 
                            class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-arrow-left mr-1"></i> Voltar
                    </button>
                </div>
            @endif
        </div>

    {{-- ETAPA 3: SUCESSO --}}
    @elseif($etapaAtual == 3)
        <div class="text-center">
            <div class="mb-6">
                <i class="fas fa-check-circle text-6xl text-green-500 mb-4"></i>
                <h2 class="text-2xl font-bold text-gray-800 mb-3">
                    Agendamento Realizado!
                </h2>
                <p class="text-gray-600">
                    {{ $mensagemSucesso }}
                </p>
            </div>

            {{-- Resumo final --}}
            <div class="bg-green-50 rounded-lg p-6 mb-6">
                <h3 class="font-semibold text-green-800 mb-4">Detalhes da sua consulta:</h3>
                <div class="space-y-2 text-sm">
                    <p><strong>Especialidade:</strong> {{ $especialidades[$especialidade] }}</p>
                    <p><strong>Médico:</strong> {{ $medico }}</p>
                    <p><strong>Data:</strong> {{ date('d/m/Y', strtotime($dataAgendamento)) }}</p>
                    <p><strong>Horário:</strong> {{ $horarioAgendamento }}</p>
                    <p><strong>Status:</strong> <span class="text-orange-600 font-medium">Aguardando confirmação</span></p>
                </div>
            </div>

            <div class="space-y-3">
                <a href="/" class="block w-full bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition text-center">
                    Voltar ao Site
                </a>
                <button wire:click="$set('etapaAtual', 1)" 
                        class="block w-full bg-gray-200 text-gray-700 px-6 py-3 rounded-md hover:bg-gray-300 transition">
                    Fazer Outro Agendamento
                </button>
            </div>
        </div>
    @endif
</div>