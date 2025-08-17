{{-- resources/views/livewire/painel/partials/agendamentos-table.blade.php --}}
{{-- VERSÃO CORRIGIDA - FUNCIONANDO --}}

<div class="overflow-hidden">
    {{-- Debug para verificar se dados chegam aqui --}}
    @if(config('app.debug'))
        <div class="mb-2 p-2 bg-green-50 text-xs text-green-700">
            🧪 DEBUG TABELA: {{ $agendamentos->count() }} agendamentos recebidos
        </div>
    @endif

    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Cliente / Serviço
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Data / Horário
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Contato
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Observações
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Ações
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($agendamentos as $agendamento)
                @php
                    $dataAgendamento = \Carbon\Carbon::parse($agendamento->data_agendamento);
                    $isHoje = $dataAgendamento->isToday();
                    $isAmanha = $dataAgendamento->isTomorrow();
                @endphp

                <tr class="hover:bg-gray-50 transition-colors duration-150 {{ $isHoje ? 'bg-blue-50' : '' }} {{ $isAmanha ? 'bg-green-50' : '' }}">
                    
                    {{-- Cliente / Serviço --}}
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8">
                                <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center">
                                    <span class="text-sm font-medium text-white">
                                        {{ $agendamento->cliente?->nome ? substr($agendamento->cliente->nome, 0, 1) : 'C' }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $agendamento->cliente?->nome ?? "Cliente não encontrado (ID: {$agendamento->cliente_id})" }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $agendamento->servico?->nome ?? "Serviço não encontrado (ID: {$agendamento->servico_id})" }}
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Data / Horário --}}
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">
                            {{ $dataAgendamento->format('d/m/Y') }}
                            @if($isHoje)
                                <span class="text-blue-600 font-medium">(Hoje)</span>
                            @elseif($isAmanha)
                                <span class="text-green-600 font-medium">(Amanhã)</span>
                            @endif
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($agendamento->horario_agendamento)->format('H:i') }}
                        </div>
                    </td>

                    {{-- Status --}}
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            // Status com fallback simples
                            $statusLabels = [
                                'pendente' => ['emoji' => '📋', 'label' => 'Pendente', 'classes' => 'bg-yellow-100 text-yellow-800 border-yellow-200'],
                                'confirmado' => ['emoji' => '✅', 'label' => 'Confirmado', 'classes' => 'bg-green-100 text-green-800 border-green-200'],
                                'concluido' => ['emoji' => '🏁', 'label' => 'Concluído', 'classes' => 'bg-blue-100 text-blue-800 border-blue-200'],
                                'cancelado' => ['emoji' => '❌', 'label' => 'Cancelado', 'classes' => 'bg-red-100 text-red-800 border-red-200'],
                            ];
                            
                            $statusInfo = $statusLabels[$agendamento->status] ?? ['emoji' => '📋', 'label' => ucfirst($agendamento->status), 'classes' => 'bg-gray-100 text-gray-800 border-gray-200'];
                        @endphp
                        
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $statusInfo['classes'] }}">
                            <span>{{ $statusInfo['emoji'] }}</span>
                            <span>{{ $statusInfo['label'] }}</span>
                        </span>
                    </td>

                    {{-- Contato --}}
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($agendamento->cliente?->telefone)
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <a href="tel:{{ $agendamento->cliente->telefone }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $agendamento->cliente->telefone }}
                                </a>
                            </div>
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>

                    {{-- Observações --}}
                    <td class="px-6 py-4 max-w-xs">
                        @if($agendamento->observacoes)
                            <div class="text-sm text-gray-600 truncate" title="{{ $agendamento->observacoes }}">
                                {{ Str::limit($agendamento->observacoes, 50) }}
                            </div>
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>

                    {{-- Ações --}}
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end gap-2">
                            
                            {{-- Ações Rápidas baseadas no status --}}
                            @if($agendamento->status === 'pendente')
                                <button 
                                    wire:click="alterarStatus({{ $agendamento->id }}, 'confirmado')"
                                    class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50 transition-colors"
                                    title="Confirmar agendamento"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </button>
                            @endif

                            @if($agendamento->status === 'confirmado')
                                <button 
                                    wire:click="alterarStatus({{ $agendamento->id }}, 'concluido')"
                                    class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50 transition-colors"
                                    title="Marcar como concluído"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </button>
                            @endif

                            {{-- Editar --}}
                            <button 
                                wire:click="editarAgendamento({{ $agendamento->id }})"
                                class="text-gray-600 hover:text-gray-900 p-1 rounded hover:bg-gray-50 transition-colors"
                                title="Editar agendamento"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>

                            {{-- Excluir --}}
                            <button 
                                wire:click="excluir({{ $agendamento->id }})"
                                wire:confirm="Tem certeza que deseja excluir este agendamento?"
                                class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50 transition-colors"
                                title="Excluir agendamento"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                {{-- Estado vazio dentro da tabela --}}
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum agendamento encontrado</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Os agendamentos aparecerão aqui quando houver dados que correspondam aos filtros aplicados.
                            </p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>