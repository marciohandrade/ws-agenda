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

        <!-- ✅ INDICADOR DE EDIÇÃO -->
        @if($editando)
            <div class="bg-yellow-100 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>✏️ Editando:</strong> {{ $nome }}
                            <button wire:click="resetarFormulario" class="underline ml-4 hover:text-yellow-900">❌ Cancelar</button>
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- ✅ NOME DO SERVIÇO -->
        <div class="flex flex-col">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Serviço</label>
            <input type="text" 
                value="{{ $nome }}" 
                wire:change="$set('nome', $event.target.value)"
                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                placeholder="Ex: Consulta, Retorno, Exame...">
            @error('nome') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- ✅ DURAÇÃO, PREÇO E STATUS -->
        <div class="flex flex-wrap gap-4">
            <!-- DURAÇÃO -->
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Duração (minutos)</label>
                <input type="number" 
                    value="{{ $duracao_minutos }}"
                    wire:change="$set('duracao_minutos', $event.target.value)"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    min="15" max="480" step="15" placeholder="60">
                @error('duracao_minutos') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                <small class="text-gray-500 text-xs">Mín: 15 min, Máx: 8h</small>
            </div>

            <!-- PREÇO -->
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Preço (R$)</label>
                <input type="text" 
                    value="{{ $preco }}" 
                    wire:change="$set('preco', $event.target.value)"
                    wire:key="campo-preco-{{ $editando ? $servicoId : 'novo' }}"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="Ex: 150,00" 
                    maxlength="12">
                @error('preco') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                <small class="text-gray-500 text-xs">Formato: 150,00 ou 1.500,50</small>
            </div>

            <!-- STATUS -->
            <div class="flex-1 min-w-[120px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select wire:change="$set('ativo', $event.target.value)"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="1" {{ $ativo == 1 ? 'selected' : '' }}>Ativo</option>
                    <option value="0" {{ $ativo == 0 ? 'selected' : '' }}>Inativo</option>
                </select>
                @error('ativo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- ✅ DESCRIÇÃO -->
        <div class="flex flex-col">
            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
            <textarea wire:change="$set('descricao', $event.target.value)"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    rows="3" 
                    placeholder="Descreva brevemente o serviço oferecido...">{{ $descricao }}</textarea>
            @error('descricao') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- ✅ BOTÕES -->
        <div class="w-full flex justify-center mt-6">
            @if ($editando)
                <button type="button" wire:click="resetarFormulario"
                        class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-4">
                    ❌ Cancelar Edição
                </button>
            @endif

            <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded hover:bg-gray-900">
                {{ $editando ? '✅ Atualizar Serviço' : '➕ Cadastrar Serviço' }}
            </button>
        </div>

    </form>
        <!-- BOTÃO DE TESTE TEMPORÁRIO -->
        <!-- <div class="bg-red-100 p-4 mt-4 border border-red-300 rounded">
            <h4 class="font-bold mb-2">🔍 DEBUG - REMOVER DEPOIS</h4>
            <p><strong>Nome atual:</strong> "{{ $nome }}"</p>
            <p><strong>Preço atual:</strong> "{{ $preco }}"</p>
            <p><strong>Duração atual:</strong> "{{ $duracao_minutos }}"</p>
            <p><strong>Editando:</strong> {{ $editando ? 'SIM' : 'NÃO' }}</p>
            <p><strong>Serviço ID:</strong> {{ $servicoId ?? 'NULL' }}</p>
            
            <button wire:click="editar(16)" class="bg-blue-500 text-white px-4 py-2 rounded mt-2">
                🧪 TESTE: Editar ID 16
            </button>
            
            <button wire:click="$set('nome', 'TESTE MANUAL')" class="bg-green-500 text-white px-4 py-2 rounded mt-2 ml-2">
                🧪 TESTE: Definir nome manualmente
            </button>
        </div> -->

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
                                    @if($servico->duracao_minutos)
                                        {{ $servico->duracao_minutos }} min
                                    @else
                                        Não definida
                                    @endif
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
<script>
/* document.addEventListener('livewire:init', () => {
    Livewire.on('servico-salvo', () => {
        setTimeout(() => {
            location.reload();
        }, 500);
    });
}); */

document.addEventListener('livewire:init', () => {
    // ✅ LISTA DE EVENTOS QUE FAZEM RELOAD
    const eventosReload = ['servico-salvo', 'servico-excluido'];
    
    eventosReload.forEach(evento => {
        Livewire.on(evento, () => {
            setTimeout(() => {
                location.reload();
            }, 500);
        });
    });
});

// Adicione junto com o outro script que já está funcionando
document.addEventListener('livewire:init', () => {
    // Seu script existente do servico-salvo...
    
    // ✅ NOVO: Confirmação de exclusão
    Livewire.on('confirmar-exclusao', (dados) => {
        const nome = dados[0].nome; // Livewire passa como array
        const id = dados[0].id;
        
        const confirma = confirm(`Tem certeza que deseja excluir o serviço "${nome}"?`);
        if (confirma) {
            Livewire.find('{{ $this->getId() }}').call('confirmarExclusao', id);
        }
    });
});
</script>