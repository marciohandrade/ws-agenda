{{-- resources/views/livewire/painel/partials/agendamento-card.blade.php --}}
{{-- VERSÃO FINAL CORRIGIDA - Relacionamentos otimizados + Debug --}}

<div class="bg-white border border-gray-200 rounded-lg p-4 mb-4 shadow-sm hover:shadow-md transition-shadow">
    
    {{-- Debug Info (remover depois) --}}
    @if(config('app.debug'))
        <div class="text-xs text-gray-400 mb-2 p-1 bg-gray-50 rounded">
            ID: {{ $agendamento->id }} | Cliente ID: {{ $agendamento->cliente_id }} | Status: {{ $agendamento->status }}
        </div>
    @endif
    
    {{-- Cabeçalho --}}
    <div class="mb-3">
        <h3 class="font-semibold text-gray-900 text-lg">
            {{ $agendamento->cliente?->nome ?? "Cliente não encontrado (ID: {$agendamento->cliente_id})" }}
        </h3>
        
        <p class="text-gray-600">
            {{ $agendamento->servico?->nome ?? "Serviço não encontrado (ID: {$agendamento->servico_id})" }}
        </p>
    </div>
    
    {{-- Informações Básicas --}}
    <div class="mb-3 space-y-1">
        <div class="flex items-center gap-2 text-sm text-gray-600">
            <span class="font-medium">📅 Data:</span>
            <span>{{ \Carbon\Carbon::parse($agendamento->data_agendamento)->format('d/m/Y') }}</span>
        </div>
        
        <div class="flex items-center gap-2 text-sm text-gray-600">
            <span class="font-medium">🕐 Horário:</span>
            <span>{{ \Carbon\Carbon::parse($agendamento->horario_agendamento)->format('H:i') }}</span>
        </div>
        
        {{-- Telefone - usando relacionamento --}}
        @if($agendamento->cliente?->telefone)
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <span class="font-medium">📱 Telefone:</span>
                <a href="tel:{{ $agendamento->cliente->telefone }}" class="text-blue-600 hover:text-blue-800">
                    {{ $agendamento->cliente->telefone }}
                </a>
            </div>
        @endif
    </div>
    
    {{-- Status - usando configuração dinâmica --}}
    <div class="mb-4">
        @php
            // Busca configuração do status ou usa padrão
            $statusConfig = $statusConfig ?? [];
            $statusInfo = $statusConfig['principais'][$agendamento->status] ?? 
                         $statusConfig['secundarios'][$agendamento->status] ?? null;
            
            if ($statusInfo) {
                $cores = $statusConfig['cores'][$statusInfo['cor']] ?? $statusConfig['cores']['gray'];
                $emoji = $statusInfo['emoji'];
                $label = $statusInfo['label'];
            } else {
                // Fallback para status não configurados
                $fallbacks = [
                    'pendente' => ['emoji' => '📋', 'label' => 'Pendente', 'classes' => 'bg-yellow-100 text-yellow-800'],
                    'confirmado' => ['emoji' => '✅', 'label' => 'Confirmado', 'classes' => 'bg-green-100 text-green-800'],
                    'concluido' => ['emoji' => '🏁', 'label' => 'Concluído', 'classes' => 'bg-blue-100 text-blue-800'],
                    'cancelado' => ['emoji' => '❌', 'label' => 'Cancelado', 'classes' => 'bg-red-100 text-red-800'],
                ];
                
                $fallback = $fallbacks[$agendamento->status] ?? ['emoji' => '📋', 'label' => ucfirst($agendamento->status), 'classes' => 'bg-gray-100 text-gray-800'];
                $emoji = $fallback['emoji'];
                $label = $fallback['label'];
                $cores = ['bg' => explode(' ', $fallback['classes'])[0], 'text' => explode(' ', $fallback['classes'])[1]];
            }
        @endphp
        
        <span class="inline-flex items-center gap-1 px-3 py-1 text-sm font-medium rounded-full {{ $cores['bg'] ?? 'bg-gray-100' }} {{ $cores['text'] ?? 'text-gray-800' }}">
            {{ $emoji }} {{ $label }}
        </span>
    </div>
    
    {{-- Observações --}}
    @if($agendamento->observacoes)
        <div class="mb-4 p-2 bg-gray-50 rounded text-sm text-gray-600">
            <strong>💬 Observações:</strong> {{ Str::limit($agendamento->observacoes, 100) }}
        </div>
    @endif
    
    {{-- Informações Extras --}}
    <div class="mb-3 text-xs text-gray-500">
        <div class="flex justify-between">
            <span>ID: {{ $agendamento->id }}</span>
            <span>{{ $agendamento->created_at->diffForHumans() }}</span>
        </div>
    </div>
    
    {{-- Ações Baseadas em Status --}}
    <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-100">
        
        @if($agendamento->status === 'pendente')
            <button 
                wire:click="alterarStatus({{ $agendamento->id }}, 'confirmado')" 
                class="px-3 py-1.5 text-sm bg-green-500 text-white rounded hover:bg-green-600 transition-colors">
                ✅ Confirmar
            </button>
            <button 
                wire:click="alterarStatus({{ $agendamento->id }}, 'cancelado')" 
                class="px-3 py-1.5 text-sm bg-red-500 text-white rounded hover:bg-red-600 transition-colors">
                ❌ Cancelar
            </button>
        @endif
        
        @if($agendamento->status === 'confirmado')
            <button 
                wire:click="alterarStatus({{ $agendamento->id }}, 'concluido')" 
                class="px-3 py-1.5 text-sm bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">
                🏁 Concluir
            </button>
            <button 
                wire:click="alterarStatus({{ $agendamento->id }}, 'cancelado')" 
                class="px-3 py-1.5 text-sm bg-red-500 text-white rounded hover:bg-red-600 transition-colors">
                ❌ Cancelar
            </button>
        @endif
        
        {{-- Ações sempre disponíveis --}}
        <button 
            wire:click="editarAgendamento({{ $agendamento->id }})" 
            class="px-3 py-1.5 text-sm bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors">
            ✏️ Editar
        </button>
        
        <button 
            wire:click="excluir({{ $agendamento->id }})" 
            wire:confirm="Tem certeza que deseja excluir este agendamento?"
            class="px-3 py-1.5 text-sm bg-gray-300 text-gray-700 rounded hover:bg-gray-400 transition-colors">
            🗑️ Excluir
        </button>
    </div>
    
    {{-- Rodapé --}}
    <div class="mt-3 pt-2 border-t border-gray-100 text-xs text-gray-400">
        Criado em {{ $agendamento->created_at->format('d/m/Y H:i') }}
    </div>
</div>