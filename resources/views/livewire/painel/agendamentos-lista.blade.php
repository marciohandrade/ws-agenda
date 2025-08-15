<!-- ✅ VIEW TEMPORÁRIA SIMPLES - SEM ROTAS PROBLEMÁTICAS -->
<div class="min-h-screen bg-gray-50">
    
    <!-- ====== HEADER ====== -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <h1 class="text-xl font-bold text-gray-900">Agendamentos</h1>
            <p class="text-sm text-gray-500">{{ $resumo['hoje'] }} hoje • {{ $resumo['pendentes'] }} pendentes</p>
        </div>
    </div>

    <!-- ====== FILTROS RÁPIDOS ====== -->
    <div class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="flex space-x-2 overflow-x-auto">
                @php
                    $periodos = [
                        'hoje' => 'Hoje',
                        'amanha' => 'Amanhã', 
                        'semana' => 'Esta Semana',
                        'mes' => 'Este Mês',
                        'todos' => 'Todos'
                    ];
                @endphp
                
                @foreach($periodos as $valor => $label)
                <button wire:click="setPeriodo('{{ $valor }}')" 
                        class="px-3 py-1.5 text-xs font-medium rounded-full transition-colors
                               {{ $filtroPeriodo === $valor 
                                  ? 'bg-blue-100 text-blue-800' 
                                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- ====== LISTA DE AGENDAMENTOS ====== -->
    <div class="max-w-7xl mx-auto px-4 py-6">
        
        <!-- Cards Mobile First -->
        <div class="space-y-4">
            @forelse($agendamentos as $agendamento)
            <div class="bg-white rounded-lg shadow-sm border p-4">
                <!-- Header do Card -->
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $agendamento->cliente->nome ?? 'N/A' }}</h3>
                        <p class="text-sm text-gray-500">{{ $agendamento->cliente->telefone ?? '' }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                        {{ $agendamento->status === 'confirmado' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $agendamento->status === 'pendente' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $agendamento->status === 'concluido' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $agendamento->status === 'cancelado' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ ucfirst($agendamento->status) }}
                    </span>
                </div>

                <!-- Informações -->
                <div class="grid grid-cols-2 gap-4 mb-3 text-sm">
                    <div>
                        <span class="text-gray-500">Serviço:</span>
                        <div class="font-medium">{{ $agendamento->servico->nome ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <span class="text-gray-500">Data & Hora:</span>
                        <div class="font-medium">
                            {{ $agendamento->data_agendamento->format('d/m/Y') }} - 
                            {{ \Carbon\Carbon::parse($agendamento->horario_agendamento)->format('H:i') }}
                        </div>
                    </div>
                </div>

                @if($agendamento->observacoes)
                <div class="mb-3">
                    <span class="text-gray-500 text-sm">Observações:</span>
                    <div class="text-sm bg-gray-50 p-2 rounded mt-1">{{ $agendamento->observacoes }}</div>
                </div>
                @endif

                <!-- Ações Simples -->
                <div class="flex flex-wrap gap-2 pt-3 border-t">
                    @if($agendamento->status === 'pendente')
                        <button wire:click="alterarStatus({{ $agendamento->id }}, 'confirmado')" 
                                class="bg-green-50 text-green-700 text-xs px-3 py-1.5 rounded hover:bg-green-100">
                            Confirmar
                        </button>
                    @endif

                    @if($agendamento->status === 'confirmado')
                        <button wire:click="alterarStatus({{ $agendamento->id }}, 'concluido')" 
                                class="bg-blue-50 text-blue-700 text-xs px-3 py-1.5 rounded hover:bg-blue-100">
                            Concluir
                        </button>
                    @endif

                    @if(!in_array($agendamento->status, ['cancelado', 'concluido']))
                        <button wire:click="alterarStatus({{ $agendamento->id }}, 'cancelado')" 
                                wire:confirm="Cancelar este agendamento?"
                                class="bg-red-50 text-red-700 text-xs px-3 py-1.5 rounded hover:bg-red-100">
                            Cancelar
                        </button>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-12 bg-white rounded-lg">
                <h3 class="text-gray-500">Nenhum agendamento encontrado</h3>
                <p class="text-sm text-gray-400 mt-2">Tente alterar os filtros ou período</p>
            </div>
            @endforelse
        </div>

        <!-- Paginação -->
        @if($agendamentos->hasPages())
        <div class="mt-6">
            {{ $agendamentos->links() }}
        </div>
        @endif
    </div>
</div>