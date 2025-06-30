<div class="max-w-6xl mx-auto p-4 sm:p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Painel Administrativo / Cadastro de Serviços</h2>

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
    
        <!-- Linha 1: Nome do Serviço (linha única) -->
        <div class="flex flex-col">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Serviço</label>
            <input type="text" wire:model="nome" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ex: Consulta, Retorno, Exame...">
            @error('nome') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Linha 2: Duração, Preço e Status -->
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Duração (minutos)</label>
                <input type="number" wire:model="duracao_minutos" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                       min="15" max="480" step="15" placeholder="60">
                @error('duracao_minutos') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                <small class="text-gray-500 text-xs">Mín: 15 min, Máx: 8h</small>
            </div>

            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Preço (R$)</label>
                <input type="text" wire:model="preco" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                       placeholder="0,00" maxlength="12"
                       x-data="{ 
                           formatMoney(event) {
                               let value = event.target.value.replace(/\D/g, '');
                               if (value === '') {
                                   event.target.value = '';
                                   return;
                               }
                               value = (value / 100).toFixed(2) + '';
                               value = value.replace('.', ',');
                               value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
                               event.target.value = 'R$ ' + value;
                               $wire.set('preco', value.replace('R$ ', ''));
                           }
                       }"
                       x-on:input="formatMoney($event)"
                       x-on:keypress="if(!/[0-9]/.test($event.key) && $event.key !== 'Backspace' && $event.key !== 'Delete' && $event.key !== 'Tab') $event.preventDefault()">
                @error('preco') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                <small class="text-gray-500 text-xs">Opcional - Ex: R$ 150,00</small>
            </div>

            <div class="flex-1 min-w-[120px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select wire:model="ativo" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="1">Ativo</option>
                    <option value="0">Inativo</option>
                </select>
                @error('ativo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Linha 4: Descrição -->
        <div class="flex flex-col">
            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
            <textarea wire:model="descricao" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="3" 
                      placeholder="Descreva brevemente o serviço oferecido..."></textarea>
            @error('descricao') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Linha Final: Botões Centralizados -->
        <div class="w-full flex justify-center mt-6">
            @if ($editando)
                <button type="button" wire:click="resetarFormulario"
                        class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-4">
                    Cancelar
                </button>
            @endif

            <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded hover:bg-gray-900">
                {{ $editando ? 'Atualizar' : 'Cadastrar' }}
            </button>
        </div>

    </form>

    <!-- Filtro de Pesquisa - PADRONIZADO -->
    <div class="w-full space-y-6 mt-8 mb-8 p-6 bg-gray-50 rounded-lg">
        <h3 class="text-xl font-bold">Filtros de Pesquisa</h3>
        
        <div class="flex flex-col">
            <label class="block text-sm font-medium text-gray-700 mb-1">Pesquisar serviços</label>
            <input type="text" wire:model.live="pesquisa" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                   placeholder="Digite o nome ou descrição do serviço...">
        </div>
    </div>

    <h3 class="text-xl font-bold mb-2">Lista de Serviços</h3>

    <!-- Mostra aviso em telas pequenas -->
    <p class="text-sm text-gray-500 mb-1 sm:hidden">Deslize para o lado →</p>

    <!-- Scroll horizontal somente no mobile -->
    <div class="overflow-x-auto w-full">
        @if($servicos->count() > 0)
            <table class="w-full min-w-[640px] text-sm table-auto border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2">Nome</th>
                        <th class="p-2">Descrição</th>
                        <th class="p-2">Duração</th>
                        <th class="p-2">Preço</th>
                        <th class="p-2">Status</th>
                        <th class="p-2">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($servicos as $servico)
                        <tr class="border-t">
                            <td class="p-2 font-medium">{{ $servico->nome }}</td>
                            <td class="p-2">{{ Str::limit($servico->descricao, 50) }}</td>
                            <td class="p-2">
                                <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                    {{ $servico->duracao_formatada }}
                                </span>
                            </td>
                            <td class="p-2">{{ $servico->preco_formatado }}</td>
                            <td class="p-2">
                                @if($servico->ativo)
                                    <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Ativo</span>
                                @else
                                    <span class="inline-block bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded">Inativo</span>
                                @endif
                            </td>
                            <td class="p-2">
                                <button wire:click="editar({{ $servico->id }})" class="text-blue-600 hover:underline">Editar</button>
                                
                                <button wire:click="alternarStatus({{ $servico->id }})" 
                                        class="text-{{ $servico->ativo ? 'orange' : 'green' }}-600 hover:underline ml-2">
                                    {{ $servico->ativo ? 'Desativar' : 'Ativar' }}
                                </button>
                                
                                <button wire:click="excluir({{ $servico->id }})" 
                                        onclick="return confirm('Tem certeza que deseja excluir este serviço?')"
                                        class="text-red-600 hover:underline ml-2">
                                    Excluir
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Paginação -->
            <div class="mt-4 flex justify-center">
                {{ $servicos->links() }}
            </div>
        @else
            <div class="text-center py-8">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0h3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <h5 class="text-gray-600 text-lg mb-2">Nenhum serviço encontrado</h5>
                @if($pesquisa)
                    <p class="text-gray-500">Tente ajustar sua pesquisa ou 
                        <button wire:click="$set('pesquisa', '')" class="text-blue-600 hover:underline">limpe o filtro</button>
                    </p>
                @else
                    <p class="text-gray-500">Adicione o primeiro serviço preenchendo o formulário acima</p>
                @endif
            </div>
        @endif
    </div>

</div> <!-- FECHA A DIV PRINCIPAL -->