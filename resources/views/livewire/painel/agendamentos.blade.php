<div class="max-w-6xl mx-auto p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Cadastro de Agendamentos</h2>

    @if (session()->has('sucesso'))
        <div 
            x-data="{ show: true }" 
            x-init="setTimeout(() => show = false, 3000)" 
            x-show="show"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-500"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            class="fixed bottom-6 right-6 z-[9999] w-auto max-w-sm bg-gray-800 text-white text-sm rounded-lg shadow-lg px-5 py-3"
        >
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('sucesso') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('erro'))
        <div 
            x-data="{ show: true }" 
            x-init="setTimeout(() => show = false, 3000)" 
            x-show="show"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-500"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            class="fixed bottom-6 right-6 z-[9999] w-auto max-w-sm bg-red-600 text-white text-sm rounded-lg shadow-lg px-5 py-3"
        >
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-200 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>{{ session('erro') }}</span>
            </div>
        </div>
    @endif

    <form wire:submit.prevent="salvar" class="w-full space-y-6">
    
        <!-- Linha 1: Cliente e Serviço -->
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[250px]">
                <label>Cliente *</label>
                <select wire:model="cliente_id" class="w-full border rounded p-2">
                    <option value="">Selecione um cliente</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}">
                            {{ $cliente->nome }} - {{ $cliente->telefone }}
                        </option>
                    @endforeach
                </select>
                @error('cliente_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex-1 min-w-[250px]">
                <label>Serviço *</label>
                <select wire:model="servico_id" class="w-full border rounded p-2">
                    <option value="">Selecione um serviço</option>
                    @foreach($servicos as $servico)
                        <option value="{{ $servico->id }}">
                            {{ $servico->nome }} ({{ $servico->duracao_formatada }})
                        </option>
                    @endforeach
                </select>
                @error('servico_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Linha 2: Data e Horário -->
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[180px]">
                <label>Data do Agendamento *</label>
                <input type="date" wire:model="data_agendamento" class="w-full border rounded p-2" 
                       min="{{ date('Y-m-d') }}">
                @error('data_agendamento') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex-1 min-w-[180px]">
                <label>Horário *</label>
                <select wire:model="horario_agendamento" class="w-full border rounded p-2">
                    <option value="">Selecione um horário</option>
                    @foreach($horariosDisponiveis as $horario)
                        <option value="{{ $horario }}">{{ $horario }}</option>
                    @endforeach
                </select>
                @error('horario_agendamento') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                @if(empty($horariosDisponiveis) && $data_agendamento)
                    <small class="text-orange-600 text-xs">
                        @if(\Carbon\Carbon::parse($data_agendamento)->isWeekend())
                            Não atendemos aos finais de semana. Selecione um dia útil.
                        @else
                            Nenhum horário disponível para esta data.
                        @endif
                    </small>
                @endif
            </div>

            <div class="flex-1 min-w-[180px]">
                <label>Status *</label>
                <select wire:model="status" class="w-full border rounded p-2">
                    @foreach(\App\Models\Agendamento::getStatusOptions() as $valor => $label)
                        <option value="{{ $valor }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Linha 3: Observações -->
        <div class="flex flex-col">
            <label>Observações</label>
            <textarea wire:model="observacoes" class="w-full border rounded p-2" rows="3" 
                      placeholder="Observações sobre o agendamento..."></textarea>
            @error('observacoes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Linha Final: Botões Centralizados -->
        <div class="w-full flex justify-center mt-6">
            @if ($editando)
                <button type="button" wire:click="resetarFormulario"
                        class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-4">
                    Cancelar
                </button>
            @endif

            <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded hover:bg-gray-900"
                    @if(empty($horariosDisponiveis) && $data_agendamento) disabled @endif>
                {{ $editando ? 'Atualizar' : 'Cadastrar' }}
            </button>
        </div>

    </form>

    <!-- Filtros de Pesquisa -->
    <div class="mt-8 mb-4 space-y-4">
        <h3 class="text-lg font-semibold">Filtros de Pesquisa</h3>
        
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <label>Pesquisar Cliente</label>
                <input type="text" wire:model.live="filtroCliente" class="w-full border rounded p-2" 
                       placeholder="Nome do cliente...">
            </div>
            
            <div class="flex-1 min-w-[150px]">
                <label>Data</label>
                <input type="date" wire:model.live="filtroData" class="w-full border rounded p-2">
            </div>
            
            <div class="flex-1 min-w-[150px]">
                <label>Status</label>
                <select wire:model.live="filtroStatus" class="w-full border rounded p-2">
                    <option value="">Todos os status</option>
                    @foreach(\App\Models\Agendamento::getStatusOptions() as $valor => $label)
                        <option value="{{ $valor }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-end">
                <button wire:click="limparFiltros" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Limpar
                </button>
            </div>
        </div>
    </div>

    <h3 class="text-xl font-bold mb-2">Lista de Agendamentos</h3>

    <!-- Mostra aviso em telas pequenas -->
    <p class="text-sm text-gray-500 mb-1 lg:hidden">Deslize para o lado →</p>

    <!-- Scroll horizontal somente no mobile/tablet -->
    <div class="overflow-x-auto w-full">
        @if($agendamentos->count() > 0)
            <table class="w-full min-w-[900px] text-sm table-auto border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2">Cliente</th>
                        <th class="p-2">Serviço</th>
                        <th class="p-2">Data & Hora</th>
                        <th class="p-2">Status</th>
                        <th class="p-2">Observações</th>
                        <th class="p-2">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agendamentos as $agendamento)
                        <tr class="border-t">
                            <td class="p-2">
                                <div class="font-medium">{{ $agendamento->cliente->nome }}</div>
                                <div class="text-xs text-gray-500">{{ $agendamento->cliente->telefone }}</div>
                                @if($agendamento->cliente_cadastrado_automaticamente)
                                    <span class="inline-block bg-yellow-100 text-yellow-800 text-xs px-1 py-0.5 rounded mt-1">
                                        Auto-cadastro
                                    </span>
                                @endif
                            </td>
                            <td class="p-2">
                                <div class="font-medium">{{ $agendamento->servico->nome }}</div>
                                <div class="text-xs text-gray-500">{{ $agendamento->servico->duracao_formatada }}</div>
                            </td>
                            <td class="p-2">
                                <div class="font-medium">{{ $agendamento->data_hora_formatada }}</div>
                            </td>
                            <td class="p-2">
                                @php
                                    $badgeColors = [
                                        'pendente' => 'bg-yellow-100 text-yellow-800',
                                        'confirmado' => 'bg-blue-100 text-blue-800',
                                        'concluido' => 'bg-green-100 text-green-800',
                                        'cancelado' => 'bg-red-100 text-red-800'
                                    ];
                                @endphp
                                <span class="inline-block {{ $badgeColors[$agendamento->status] ?? 'bg-gray-100 text-gray-800' }} text-xs px-2 py-1 rounded">
                                    {{ $agendamento->status_formatado }}
                                </span>
                                @if($agendamento->status === 'cancelado' && $agendamento->data_cancelamento)
                                    <div class="text-xs text-gray-500 mt-1">
                                        Cancelado em {{ $agendamento->data_cancelamento->format('d/m/Y H:i') }}
                                    </div>
                                @endif
                            </td>
                            <td class="p-2">
                                <div>{{ Str::limit($agendamento->observacoes, 30) }}</div>
                                @if($agendamento->motivo_cancelamento)
                                    <div class="text-xs text-red-600 mt-1">
                                        <strong>Motivo:</strong> {{ Str::limit($agendamento->motivo_cancelamento, 30) }}
                                    </div>
                                @endif
                            </td>
                            <td class="p-2">
                                <div class="flex flex-wrap gap-1">
                                    <!-- Ações de Status -->
                                    @if($agendamento->status === 'pendente')
                                        <button wire:click="alterarStatus({{ $agendamento->id }}, 'confirmado')"
                                                class="text-green-600 hover:underline text-xs px-1">
                                            Confirmar
                                        </button>
                                    @endif
                                    
                                    @if(in_array($agendamento->status, ['pendente', 'confirmado']))
                                        <button wire:click="alterarStatus({{ $agendamento->id }}, 'concluido')"
                                                class="text-blue-600 hover:underline text-xs px-1">
                                            Concluir
                                        </button>
                                    @endif
                                    
                                    @if($agendamento->podeSerCancelado())
                                        <button wire:click="cancelar({{ $agendamento->id }})"
                                                onclick="return confirm('Tem certeza que deseja cancelar este agendamento?')"
                                                class="text-orange-600 hover:underline text-xs px-1">
                                            Cancelar
                                        </button>
                                    @endif
                                    
                                    <!-- Ações de Gerenciamento -->
                                    @if($agendamento->podeSerEditado())
                                        <button wire:click="editar({{ $agendamento->id }})"
                                                class="text-blue-600 hover:underline text-xs px-1">
                                            Editar
                                        </button>
                                    @endif
                                    
                                    <button wire:click="excluir({{ $agendamento->id }})"
                                            onclick="return confirm('Tem certeza que deseja excluir este agendamento?')"
                                            class="text-red-600 hover:underline text-xs px-1">
                                        Excluir
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Paginação -->
            <div class="mt-4 flex justify-center">
                {{ $agendamentos->links() }}
            </div>
        @else
            <div class="text-center py-8">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h5 class="text-gray-600 text-lg mb-2">Nenhum agendamento encontrado</h5>
                @if($filtroCliente || $filtroData || $filtroStatus)
                    <p class="text-gray-500">Tente ajustar os filtros ou 
                        <button wire:click="limparFiltros" class="text-blue-600 hover:underline">limpe os filtros</button>
                    </p>
                @else
                    <p class="text-gray-500">Adicione o primeiro agendamento preenchendo o formulário acima</p>
                @endif
            </div>
        @endif
    </div>

</div> <!-- FECHA A DIV PRINCIPAL -->## ✅ **Adaptação Completa da View de Agendamentos Realizada!**
