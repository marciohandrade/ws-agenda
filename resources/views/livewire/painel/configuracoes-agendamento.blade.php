<div class="max-w-6xl mx-auto p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Painel Administrativo / Configurações de Agendamento</h2>

    <!-- Mensagem de Sucesso -->
    @if (session()->has('sucesso'))
        <div 
            x-data="{ show: true }" 
            x-init="setTimeout(() => show = false, 3000)" 
            x-show="show"
            x-transition
            class="fixed bottom-6 right-6 z-[9999] w-auto max-w-sm bg-gray-800 text-white text-sm rounded-lg shadow-lg px-5 py-3"
        >
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('sucesso') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('erro'))
        <div 
            x-data="{ show: true }" 
            x-init="setTimeout(() => show = false, 5000)" 
            x-show="show"
            x-transition
            class="fixed bottom-6 right-6 z-[9999] w-auto max-w-sm bg-red-600 text-white text-sm rounded-lg shadow-lg px-5 py-3"
        >
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-200 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>{{ session('erro') }}</span>
            </div>
        </div>
    @endif

    <!-- Abas Simplificadas -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
            <button wire:click="$set('aba_ativa', 'horarios')"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $aba_ativa === 'horarios' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                ⏰ Horários de Funcionamento
            </button>
            <button wire:click="$set('aba_ativa', 'regras')"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $aba_ativa === 'regras' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                ⚙️ Regras de Negócio
            </button>
            <button wire:click="$set('aba_ativa', 'bloqueios')"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $aba_ativa === 'bloqueios' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                🚫 Bloqueios e Feriados
            </button>
        </nav>
    </div>

    @if($aba_ativa === 'horarios')
        <!-- ✅ ABA 1: HORÁRIOS DE FUNCIONAMENTO -->
        <div class="space-y-6">
            
            <!-- Seleção de Perfil -->
            <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold mb-4">📋 Perfil de Configuração</h3>
                <div class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[250px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Perfil de Usuário</label>
                        <select wire:model.live="perfil_ativo" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @foreach(\App\Models\ConfiguracaoAgendamento::PERFIS as $valor => $label)
                                <option value="{{ $valor }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <small class="text-gray-600 text-xs">Cada perfil pode ter horários diferentes</small>
                    </div>
                </div>
            </div>

            <!-- CONFIGURAÇÃO ÚNICA: HORÁRIOS POR DIA -->
            <form wire:submit.prevent="salvarHorarios" class="space-y-4">
                
                <!-- Botões de Ação Rápida -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold mb-3">⚡ Configurações Rápidas</h4>
                    <p class="text-sm text-gray-600 mb-4">Aplicar modelos pré-definidos de horários para todos os dias</p>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="aplicarHorarioComercial" 
                                class="bg-gray-800 text-white px-6 py-2 rounded hover:bg-gray-900 text-sm"
                                title="Segunda a Sexta: 8h às 18h com almoço 12h-13h">
                            Horário Comercial
                        </button>
                        <button type="button" wire:click="aplicarComFinsDeSemana" 
                                class="bg-gray-800 text-white px-6 py-2 rounded hover:bg-gray-900 text-sm"
                                title="Seg-Sex: 8h-18h | Sábado: 8h-14h | Domingo: 8h-12h">
                            Incluir Fins de Semana
                        </button>
                        <button type="button" wire:click="aplicarSemFimDeSemana" 
                                class="bg-gray-800 text-white px-6 py-2 rounded hover:bg-gray-900 text-sm"
                                title="Apenas Segunda a Sexta: 8h às 18h">
                            Apenas Dias Úteis
                        </button>
                    </div>
                    <small class="text-xs text-gray-500 block mt-2">💡 Passe o mouse sobre os botões para ver detalhes</small>
                </div>

                <!-- Grade de Horários - Design Melhorado -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @php
                        $diasSemana = [
                            1 => ['nome' => 'Segunda-feira', 'cor' => 'green', 'icon' => '💼'],
                            2 => ['nome' => 'Terça-feira', 'cor' => 'green', 'icon' => '💼'], 
                            3 => ['nome' => 'Quarta-feira', 'cor' => 'green', 'icon' => '💼'],
                            4 => ['nome' => 'Quinta-feira', 'cor' => 'green', 'icon' => '💼'],
                            5 => ['nome' => 'Sexta-feira', 'cor' => 'green', 'icon' => '💼'],
                            6 => ['nome' => 'Sábado', 'cor' => 'blue', 'icon' => '🏖️'],
                            7 => ['nome' => 'Domingo', 'cor' => 'purple', 'icon' => '🏠']
                        ];
                    @endphp
                    
                    @foreach($diasSemana as $numero => $dia)
                        @php
                            $isAtivo = isset($horarios_especificos[$numero]['ativo']) && 
                                      ($horarios_especificos[$numero]['ativo'] === true || 
                                       $horarios_especificos[$numero]['ativo'] === 'true' ||
                                       $horarios_especificos[$numero]['ativo'] === 1 ||
                                       $horarios_especificos[$numero]['ativo'] === '1');
                        @endphp
                        
                        <div class="border-2 {{ $isAtivo ? 'border-'.$dia['cor'].'-200 bg-'.$dia['cor'].'-50' : 'border-gray-200 bg-gray-50' }} rounded-lg p-4 transition-colors">
                            
                            <!-- Header do Dia -->
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <span class="text-2xl mr-2">{{ $dia['icon'] }}</span>
                                    <h5 class="font-semibold text-lg">{{ $dia['nome'] }}</h5>
                                </div>
                                
                                <!-- Option Buttons Aberto/Fechado -->
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-700 mr-2">Status:</span>
                                    <div class="flex rounded-lg border border-gray-300 overflow-hidden">
                                        <!-- Botão Aberto -->
                                        <button type="button" 
                                                wire:click="$set('horarios_especificos.{{ $numero }}.ativo', true)"
                                                class="px-4 py-2 text-sm font-medium transition-all duration-200 {{ $isAtivo ? 'bg-green-500 text-white border-green-500' : 'bg-white text-green-600 hover:bg-green-50 border-gray-300' }}">
                                            🟢 Aberto
                                        </button>
                                        
                                        <!-- Botão Fechado -->
                                        <button type="button" 
                                                wire:click="$set('horarios_especificos.{{ $numero }}.ativo', false)"
                                                class="px-4 py-2 text-sm font-medium transition-all duration-200 border-l {{ !$isAtivo ? 'bg-red-500 text-white border-red-500' : 'bg-white text-red-600 hover:bg-red-50 border-gray-300' }}">
                                            🔴 Fechado
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            @if($isAtivo)
                                <!-- Configurações de Horário -->
                                <div class="space-y-4">
                                    
                                    <!-- Horário Principal -->
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">⏰ Abertura</label>
                                            <input type="time" 
                                                   wire:model="horarios_especificos.{{ $numero }}.horario_inicio" 
                                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            @error("horarios_especificos.{$numero}.horario_inicio") 
                                                <span class="text-red-500 text-xs">{{ $message }}</span> 
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">🔚 Fechamento</label>
                                            <input type="time" 
                                                   wire:model="horarios_especificos.{{ $numero }}.horario_fim" 
                                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            @error("horarios_especificos.{$numero}.horario_fim") 
                                                <span class="text-red-500 text-xs">{{ $message }}</span> 
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Horário de Almoço -->
                                    <div class="border-t pt-3">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm font-medium">🍽️ Pausa para almoço:</span>
                                            <div class="flex rounded-lg border border-gray-300 overflow-hidden">
                                                <!-- Botão Sim -->
                                                <button type="button" 
                                                        wire:click="$set('horarios_especificos.{{ $numero }}.tem_almoco', true)"
                                                        class="px-3 py-1 text-xs font-medium transition-all duration-200 {{ isset($horarios_especificos[$numero]['tem_almoco']) && $horarios_especificos[$numero]['tem_almoco'] ? 'bg-yellow-500 text-white' : 'bg-white text-yellow-600 hover:bg-yellow-50' }}">
                                                    ✓ Sim
                                                </button>
                                                
                                                <!-- Botão Não -->
                                                <button type="button" 
                                                        wire:click="$set('horarios_especificos.{{ $numero }}.tem_almoco', false)"
                                                        class="px-3 py-1 text-xs font-medium transition-all duration-200 border-l {{ !isset($horarios_especificos[$numero]['tem_almoco']) || !$horarios_especificos[$numero]['tem_almoco'] ? 'bg-gray-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                                                    ✕ Não
                                                </button>
                                            </div>
                                        </div>
                                        
                                        @if(isset($horarios_especificos[$numero]['tem_almoco']) && $horarios_especificos[$numero]['tem_almoco'])
                                            <div class="grid grid-cols-2 gap-3 bg-yellow-50 p-3 rounded">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">🍽️ Início almoço</label>
                                                    <input type="time" 
                                                           wire:model="horarios_especificos.{{ $numero }}.almoco_inicio" 
                                                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                    @error("horarios_especificos.{$numero}.almoco_inicio") 
                                                        <span class="text-red-500 text-xs">{{ $message }}</span> 
                                                    @enderror
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">🔚 Fim almoço</label>
                                                    <input type="time" 
                                                           wire:model="horarios_especificos.{{ $numero }}.almoco_fim" 
                                                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                    @error("horarios_especificos.{$numero}.almoco_fim") 
                                                        <span class="text-red-500 text-xs">{{ $message }}</span> 
                                                    @enderror
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Resumo -->
                                    <div class="bg-{{ $dia['cor'] }}-100 p-3 rounded text-sm border border-{{ $dia['cor'] }}-200">
                                        <strong>📋 Resumo:</strong><br>
                                        {{ $horarios_especificos[$numero]['horario_inicio'] ?? '--:--' }} às 
                                        {{ $horarios_especificos[$numero]['horario_fim'] ?? '--:--' }}
                                        @if(isset($horarios_especificos[$numero]['tem_almoco']) && $horarios_especificos[$numero]['tem_almoco'])
                                            <br><span class="text-yellow-700">🍽️ Almoço: {{ $horarios_especificos[$numero]['almoco_inicio'] ?? '--:--' }} às {{ $horarios_especificos[$numero]['almoco_fim'] ?? '--:--' }}</span>
                                        @endif
                                    </div>

                                    <!-- Ações Rápidas -->
                                    <div class="flex gap-1">
                                        @if($numero > 1)
                                            <button type="button" 
                                                    wire:click="copiarHorarioDia({{ $numero - 1 }}, {{ $numero }})"
                                                    class="text-xs px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded transition-colors">
                                                📋 Copiar anterior
                                            </button>
                                        @endif
                                        <button type="button" 
                                                wire:click="aplicarParaTodos({{ $numero }})"
                                                class="text-xs px-2 py-1 bg-{{ $dia['cor'] }}-100 hover:bg-{{ $dia['cor'] }}-200 rounded transition-colors">
                                            📤 Aplicar a todos
                                        </button>
                                    </div>
                                </div>
                                
                            @else
                                <!-- Estado Fechado -->
                                <div class="text-center py-6 text-gray-500">
                                    <div class="text-4xl mb-2">🔒</div>
                                    <p class="text-sm font-medium">Fechado</p>
                                    <p class="text-xs text-gray-400">Sem atendimento neste dia</p>
                                    @if($numero > 1)
                                        <button type="button" 
                                                wire:click="copiarHorarioDia({{ $numero - 1 }}, {{ $numero }})"
                                                class="text-xs px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded mt-2 transition-colors">
                                            📋 Copiar anterior
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                
                <!-- Botão Salvar -->
                <div class="text-center pt-6">
                    <button type="submit" 
                            class="bg-gray-800 text-white px-6 py-2 rounded hover:bg-gray-900">
                        Salvar Horários
                    </button>
                </div>
            </form>
        </div>

    @elseif($aba_ativa === 'regras')
        <!-- ✅ ABA 2: APENAS REGRAS DE NEGÓCIO -->
        <form wire:submit.prevent="salvarRegras" class="space-y-6">
            
            <!-- Seleção de Perfil -->
            <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold mb-4">📋 Perfil de Configuração</h3>
                <div class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[250px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Perfil de Usuário</label>
                        <select wire:model.live="perfil_ativo" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @foreach(\App\Models\ConfiguracaoAgendamento::PERFIS as $valor => $label)
                                <option value="{{ $valor }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Regras de Antecedência -->
            <div class="bg-purple-50 p-6 rounded-lg border border-purple-200">
                <h3 class="text-lg font-semibold mb-4">⏱️ Regras de Antecedência</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            🚀 Antecedência Mínima (horas)
                        </label>
                        <input type="number" 
                               wire:model="antecedencia_minima_horas" 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               min="1" max="72" step="1">
                        @error('antecedencia_minima_horas') 
                            <span class="text-red-500 text-sm">{{ $message }}</span> 
                        @enderror
                        <small class="text-gray-600 text-xs block mt-1">
                            ⚡ Tempo mínimo necessário para fazer um agendamento
                        </small>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            📅 Antecedência Máxima (dias)
                        </label>
                        <input type="number" 
                               wire:model="antecedencia_maxima_dias" 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               min="1" max="365" step="1">
                        @error('antecedencia_maxima_dias') 
                            <span class="text-red-500 text-sm">{{ $message }}</span> 
                        @enderror
                        <small class="text-gray-600 text-xs block mt-1">
                            📆 Máximo de dias no futuro que se pode agendar
                        </small>
                    </div>
                </div>

                <!-- Exemplos Visuais -->
                <div class="mt-4 p-4 bg-white rounded border border-purple-200">
                    <h5 class="font-medium mb-2">📋 Exemplos Práticos:</h5>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• <strong>Antecedência mínima 2h:</strong> Cliente deve agendar pelo menos 2 horas antes</li>
                        <li>• <strong>Antecedência máxima 30 dias:</strong> Cliente pode agendar até 30 dias no futuro</li>
                        <li>• <strong>Perfil público:</strong> Geralmente tem mais restrições que admin</li>
                    </ul>
                </div>
            </div>

            <!-- Status da Configuração -->
            <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                <h3 class="text-lg font-semibold mb-4">🔧 Status da Configuração</h3>
                
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm font-medium">
                            Configuração para este perfil:
                        </span>
                        <small class="text-gray-600 text-xs block mt-1">
                            ⚠️ Controle se esta configuração está ativa ou não
                        </small>
                    </div>
                    <div class="flex rounded-lg border border-gray-300 overflow-hidden">
                        <!-- Botão Ativa -->
                        <button type="button" 
                                wire:click="$set('configuracao_ativa', true)"
                                class="px-4 py-2 text-sm font-medium transition-all duration-200 {{ $configuracao_ativa ? 'bg-green-500 text-white' : 'bg-white text-green-600 hover:bg-green-50' }}">
                            ✅ Ativa
                        </button>
                        
                        <!-- Botão Inativa -->
                        <button type="button" 
                                wire:click="$set('configuracao_ativa', false)"
                                class="px-4 py-2 text-sm font-medium transition-all duration-200 border-l {{ !$configuracao_ativa ? 'bg-red-500 text-white' : 'bg-white text-red-600 hover:bg-red-50' }}">
                            ❌ Inativa
                        </button>
                    </div>
                </div>
            </div>

            <!-- Botão Salvar -->
            <div class="text-center pt-6">
                <button type="submit" 
                        class="bg-gray-800 text-white px-6 py-2 rounded hover:bg-gray-900">
                    Salvar Regras
                </button>
            </div>
        </form>

    @elseif($aba_ativa === 'bloqueios')
        <!-- ✅ ABA 3: BLOQUEIOS E FERIADOS -->
        <div class="space-y-6">
            
            <!-- Header com Botão -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">🚫 Bloqueios e Feriados</h3>
                    <p class="text-gray-600 text-sm">Configure datas e horários indisponíveis para agendamentos</p>
                </div>
                <button wire:click="abrirModalBloqueio" 
                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors flex items-center">
                    <i class="fas fa-plus mr-2"></i>Novo Bloqueio
                </button>
            </div>

            <!-- Lista de Bloqueios -->
            <div class="bg-white border rounded-lg overflow-hidden">
                @if($bloqueios && $bloqueios->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[800px] text-sm">
                            <thead class="bg-gray-100 border-b">
                                <tr>
                                    <th class="p-3 text-left font-semibold">Tipo</th>
                                    <th class="p-3 text-left font-semibold">Período</th>
                                    <th class="p-3 text-left font-semibold">Motivo</th>
                                    <th class="p-3 text-left font-semibold">Perfis Afetados</th>
                                    <th class="p-3 text-left font-semibold">Status</th>
                                    <th class="p-3 text-left font-semibold">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bloqueios as $bloqueio)
                                    <tr class="border-b hover:bg-gray-50 transition-colors">
                                        <td class="p-3">
                                            <div class="flex flex-col gap-1">
                                                <span class="inline-block bg-red-100 text-red-800 text-xs px-2 py-1 rounded">
                                                    {{ $bloqueio->tipo_formatado }}
                                                </span>
                                                @if($bloqueio->recorrente)
                                                    <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                                        🔄 Recorrente
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="p-3">{{ $bloqueio->periodo_formatado }}</td>
                                        <td class="p-3">
                                            <div class="max-w-xs">
                                                <p class="font-medium">{{ $bloqueio->motivo }}</p>
                                                @if($bloqueio->observacoes)
                                                    <p class="text-gray-500 text-xs">{{ $bloqueio->observacoes }}</p>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="p-3">{{ $bloqueio->perfis_formatados }}</td>
                                        <td class="p-3">
                                            @if($bloqueio->ativo)
                                                <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded font-medium">
                                                    ✅ Ativo
                                                </span>
                                            @else
                                                <span class="inline-block bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded font-medium">
                                                    ⏸️ Inativo
                                                </span>
                                            @endif
                                        </td>
                                        <td class="p-3">
                                            <div class="flex flex-wrap gap-1">
                                                <button wire:click="editarBloqueio({{ $bloqueio->id }})" 
                                                        class="text-blue-600 hover:text-blue-800 hover:underline text-xs px-1 transition-colors">
                                                    ✏️ Editar
                                                </button>
                                                
                                                <button wire:click="alternarStatusBloqueio({{ $bloqueio->id }})" 
                                                        class="text-{{ $bloqueio->ativo ? 'orange' : 'green' }}-600 hover:text-{{ $bloqueio->ativo ? 'orange' : 'green' }}-800 hover:underline text-xs px-1 transition-colors">
                                                    {{ $bloqueio->ativo ? '⏸️ Desativar' : '▶️ Ativar' }}
                                                </button>
                                                
                                                <button wire:click="excluirBloqueio({{ $bloqueio->id }})" 
                                                        onclick="return confirm('❌ Tem certeza que deseja excluir este bloqueio?')"
                                                        class="text-red-600 hover:text-red-800 hover:underline text-xs px-1 transition-colors">
                                                    🗑️ Excluir
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    {{-- Removido: paginação não é compatível com propriedades Livewire --}}
                    @if($bloqueios->count() > 10)
                        <div class="p-4 border-t bg-gray-50 text-center text-sm text-gray-600">
                            Mostrando {{ $bloqueios->count() }} bloqueios
                        </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">📅</div>
                        <h5 class="text-gray-600 text-lg mb-2 font-semibold">Nenhum bloqueio configurado</h5>
                        <p class="text-gray-500 mb-6">Configure bloqueios para feriados, férias ou manutenções</p>
                        <button wire:click="abrirModalBloqueio" 
                                class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>Criar Primeiro Bloqueio
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Modal de Bloqueio -->
        @if($mostrarModalBloqueio)
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click.self="fecharModalBloqueio">
                <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-800">
                            {{ $editandoBloqueio ? '✏️ Editar Bloqueio' : '🆕 Novo Bloqueio' }}
                        </h3>
                        <button wire:click="fecharModalBloqueio" class="text-gray-500 hover:text-gray-700 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="salvarBloqueio" class="space-y-4">
                        
                        <!-- Tipo de Bloqueio -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">🚫 Tipo de Bloqueio *</label>
                            <select wire:model="tipo_bloqueio" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione o tipo</option>
                                <option value="dia_completo">📅 Dia Completo</option>
                                <option value="periodo">📆 Período (múltiplos dias)</option>
                                <option value="horario_especifico">⏰ Horário Específico</option>
                            </select>
                            @error('tipo_bloqueio') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Datas -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">📅 Data de Início *</label>
                                <input type="date" wire:model="data_inicio_bloqueio" 
                                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                       min="{{ date('Y-m-d') }}">
                                @error('data_inicio_bloqueio') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            @if($tipo_bloqueio === 'periodo')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">📅 Data de Fim *</label>
                                    <input type="date" wire:model="data_fim_bloqueio" 
                                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    @error('data_fim_bloqueio') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            @endif
                        </div>

                        <!-- Horários (apenas para horário específico) -->
                        @if($tipo_bloqueio === 'horario_especifico')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">⏰ Horário de Início *</label>
                                    <input type="time" wire:model="horario_inicio_bloqueio" 
                                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    @error('horario_inicio_bloqueio') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">🔚 Horário de Fim *</label>
                                    <input type="time" wire:model="horario_fim_bloqueio" 
                                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    @error('horario_fim_bloqueio') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif

                        <!-- Motivo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">📝 Motivo *</label>
                            <input type="text" wire:model="motivo_bloqueio" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="Ex: Feriado Nacional, Férias, Manutenção...">
                            @error('motivo_bloqueio') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Observações -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">💬 Observações</label>
                            <textarea wire:model="observacoes_bloqueio" 
                                      class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                      rows="2" 
                                      placeholder="Informações adicionais..."></textarea>
                        </div>

                        <!-- Recorrente - OPTION BUTTONS -->
                        <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <div>
                                <label class="text-sm font-medium">🔄 Bloqueio recorrente (anual):</label>
                                <p class="text-xs text-gray-600">Para feriados que se repetem todo ano (ex: Natal, Ano Novo)</p>
                            </div>
                            <div class="flex rounded-lg border border-gray-300 overflow-hidden">
                                <!-- Botão Sim -->
                                <button type="button" 
                                        wire:click="$set('recorrente_bloqueio', true)"
                                        class="px-3 py-2 text-sm font-medium transition-all duration-200 {{ $recorrente_bloqueio ? 'bg-blue-500 text-white' : 'bg-white text-blue-600 hover:bg-blue-50' }}">
                                    🔄 Sim
                                </button>
                                
                                <!-- Botão Não -->
                                <button type="button" 
                                        wire:click="$set('recorrente_bloqueio', false)"
                                        class="px-3 py-2 text-sm font-medium transition-all duration-200 border-l {{ !$recorrente_bloqueio ? 'bg-gray-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                                    ❌ Não
                                </button>
                            </div>
                        </div>

                        <!-- Perfis Afetados - CARDS MODERNIZADOS -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">👥 Perfis Afetados *</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                @foreach(\App\Models\ConfiguracaoAgendamento::PERFIS as $valor => $label)
                                    @php
                                        $isSelected = in_array($valor, $perfis_afetados ?? []);
                                    @endphp
                                    <label class="relative flex items-center p-3 border-2 rounded-lg cursor-pointer transition-all duration-200 {{ $isSelected ? 'border-red-400 bg-red-50 shadow-md' : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50' }}">
                                        <input type="checkbox" wire:model="perfis_afetados" value="{{ $valor }}" 
                                               class="sr-only peer">
                                        
                                        <!-- Indicador visual personalizado -->
                                        <div class="flex-shrink-0 w-5 h-5 mr-3 rounded border-2 {{ $isSelected ? 'bg-red-500 border-red-500' : 'bg-white border-gray-300' }} flex items-center justify-center transition-all duration-200">
                                            @if($isSelected)
                                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                            @endif
                                        </div>
                                        
                                        <span class="text-sm font-medium {{ $isSelected ? 'text-red-800' : 'text-gray-700' }}">{{ $label }}</span>
                                        
                                        <!-- Badge selecionado -->
                                        @if($isSelected)
                                            <span class="ml-auto bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">✓</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                            @error('perfis_afetados') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Botões -->
                        <div class="flex justify-end gap-2 pt-4 border-t">
                            <button type="button" wire:click="fecharModalBloqueio" 
                                    class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                                Cancelar
                            </button>
                            <button type="submit" 
                                    class="bg-gray-800 text-white px-6 py-2 rounded hover:bg-gray-900">
                                {{ $editandoBloqueio ? 'Atualizar' : 'Cadastrar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endif

</div>