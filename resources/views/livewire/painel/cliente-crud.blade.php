<div class="max-w-6xl mx-auto p-4 sm:p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Painel Administrativo / Cadastro de Cliente</h2>

    @if (session()->has('mensagem'))
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
                <span>{{ session('mensagem') }}</span>
            </div>
        </div>
    @endif

    <form wire:submit.prevent="salvar" class="w-full space-y-6">
    
        <!-- Linha 1: Nome (linha única) -->
        <div class="flex flex-col">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
            <input type="text" 
                   value="{{ $nome }}"
                   wire:change="$set('nome', $event.target.value)"
                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            @error('nome') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Linha 2: Email e Telefone -->
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[220px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" 
                       value="{{ $email }}"
                       wire:change="$set('email', $event.target.value)"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex-1 min-w-[180px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                <input type="text" 
                       value="{{ $telefone }}"
                       wire:change="$set('telefone', $event.target.value)"
                       onkeyup="mascaraTelefone(this)"
                       maxlength="15" 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('telefone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Linha 3: Data de Nascimento e Gênero -->
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Data de Nascimento</label>
                <input type="date" 
                       value="{{ $data_nascimento }}"
                       wire:change="$set('data_nascimento', $event.target.value)"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       max="{{ date('Y-m-d') }}" min="1900-01-01">
                @error('data_nascimento') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex-1 min-w-[180px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Gênero</label>
                <select wire:change="$set('genero', $event.target.value)" 
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="" {{ $genero == '' ? 'selected' : '' }}>Selecione</option>
                    <option value="Masculino" {{ $genero == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                    <option value="Feminino" {{ $genero == 'Feminino' ? 'selected' : '' }}>Feminino</option>
                    <option value="Não-binário" {{ $genero == 'Não-binário' ? 'selected' : '' }}>Não-binário</option>
                    <option value="Prefere não informar" {{ $genero == 'Prefere não informar' ? 'selected' : '' }}>Prefere não informar</option>
                </select>
                @error('genero') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Linha 4: CPF e CEP -->
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
                <input type="text" 
                        value="{{ $cpf }}"
                        wire:change="$set('cpf', $event.target.value)"
                        onkeyup="mascaraCpf(this)"
                        maxlength="14" 
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="000.000.000-00">
                @error('cpf') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex-1 min-w-[180px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                <input type="text" 
                       value="{{ $cep }}"
                       wire:change="$set('cep', $event.target.value)"
                        onkeyup="mascaraCep(this)"
                        maxlength="9" 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                       placeholder="00000-000">
                @error('cep') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Linha 5: Endereço -->
        <div class="flex flex-col">
            <label class="block text-sm font-medium text-gray-700 mb-1">Endereço</label>
            <input type="text" 
                   value="{{ $endereco }}"
                   wire:change="$set('endereco', $event.target.value)"
                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   maxlength="80" placeholder="Rua, Avenida...">
            @error('endereco') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Linha 6: Número e Complemento -->
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[120px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                <input type="text" 
                       value="{{ $numero }}"
                       wire:change="$set('numero', $event.target.value)"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       maxlength="10" placeholder="123">
                @error('numero') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex-1 min-w-[180px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                <input type="text" 
                       value="{{ $complemento }}"
                       wire:change="$set('complemento', $event.target.value)"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       maxlength="30" placeholder="Apto, Bloco, Fundos...">
                @error('complemento') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Linha Final: Botões Centralizados -->
        <div class="w-full flex justify-center mt-6 gap-8">
        
            <!-- ✅ BOTÃO CANCELAR - APARECE SEMPRE -->
           <button type="button" 
                wire:click="resetCampos"
                class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 transition-colors">
            Cancelar
        </button>

            <!-- ✅ BOTÃO PRINCIPAL -->
            <button type="submit" 
                    class="bg-gray-800 text-white px-6 py-2 rounded hover:bg-gray-900 transition-colors">
                {{ $cliente_id ? 'Atualizar' : 'Cadastrar' }}
            </button>
            
        </div>

    </form>

    <!-- ✅ FILTRO DE PESQUISA ADICIONADO -->
    <div class="w-full space-y-6 mt-8 mb-8 p-6 bg-gray-50 rounded-lg">
        <h3 class="text-xl font-bold">Filtros de Pesquisa</h3>
        
        <div class="flex flex-col">
            <label class="block text-sm font-medium text-gray-700 mb-1">Pesquisar clientes</label>
            <input type="text" 
                   wire:model.live="pesquisa" 
                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                   placeholder="Digite o nome, email ou telefone...">
        </div>
    </div>

    <h3 class="text-xl font-bold mb-2">Lista de Clientes</h3>

    <!-- Mostra aviso em telas pequenas -->
    <p class="text-sm text-gray-500 mb-1 sm:hidden">Deslize para o lado →</p>

    <!-- Scroll horizontal somente no mobile -->
    <div class="overflow-x-auto w-full">
        <table class="w-full min-w-[640px] text-sm table-auto border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2">Nome</th>
                    <th class="p-2">Email</th>
                    <th class="p-2">Telefone</th>
                    <th class="p-2">Nascimento</th>
                    <th class="p-2">Gênero</th>
                    <th class="p-2">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clientes as $cliente)
                    <tr class="border-t">
                        <td class="p-2">{{ $cliente->nome }}</td>
                        <td class="p-2">{{ $cliente->email }}</td>
                        <td class="p-2">{{ $cliente->telefone }}</td>
                        <td class="p-2">
                            {{ $cliente->data_nascimento ? \Carbon\Carbon::parse($cliente->data_nascimento)->format('d/m/Y') : '-' }}
                        </td>
                        <td class="p-2">{{ $cliente->genero ?? '-' }}</td>
                        <td class="p-2">
                            <button wire:click="editar({{ $cliente->id }})" class="text-blue-600 hover:underline">Editar</button>
                            <!-- <button wire:click="excluir({{ $cliente->id }})" class="text-red-600 hover:underline ml-2">Excluir</button> -->
                            <button wire:click="excluir({{ $cliente->id }})" 
                                    wire:confirm="Tem certeza que deseja excluir este cliente?"
                                    class="text-red-600 hover:bg-red-100 text-xs px-2 py-1 rounded transition-colors ml-2">
                                Excluir
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-8">
                            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            <h5 class="text-gray-600 text-lg mb-2">Nenhum cliente encontrado</h5>
                            @if($pesquisa)
                                <p class="text-gray-500">Tente ajustar sua pesquisa ou 
                                    <button wire:click="$set('pesquisa', '')" class="text-blue-600 hover:underline">limpe o filtro</button>
                                </p>
                            @else
                                <p class="text-gray-500">Adicione o primeiro cliente preenchendo o formulário acima</p>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- ✅ PAGINAÇÃO ADICIONADA -->
        @if($clientes->hasPages())
            <div class="mt-4 flex justify-center">
                {{ $clientes->links() }}
            </div>
        @endif
    </div>

</div>
<script>
// ✅ MÁSCARAS DE FORMATAÇÃO
function mascaraTelefone(input) {
    let valor = input.value.replace(/\D/g, '');
    if (valor.length <= 11) {
        valor = valor.replace(/(\d{2})(\d)/, '($1) $2');
        valor = valor.replace(/(\d{5})(\d)/, '$1-$2');
    }
    input.value = valor;
}

function mascaraCpf(input) {
    let valor = input.value.replace(/\D/g, '');
    if (valor.length <= 11) {
        valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
        valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
        valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    }
    input.value = valor;
}

function mascaraCep(input) {
    let valor = input.value.replace(/\D/g, '');
    if (valor.length <= 8) {
        valor = valor.replace(/(\d{5})(\d)/, '$1-$2');
    }
    input.value = valor;
}

// ✅ FUNÇÃO ÚNICA DE LIMPEZA
function limparFormulario() {
    // Limpar todos os inputs (exceto botões)
    document.querySelectorAll('input:not([type="submit"]):not([type="button"]):not([type="hidden"])').forEach(input => {
        input.value = '';
    });
    
    // Limpar selects
    document.querySelectorAll('select').forEach(select => {
        select.selectedIndex = 0;
    });
    
    // Limpar textareas
    document.querySelectorAll('textarea').forEach(textarea => {
        textarea.value = '';
    });
    
    console.log('Formulário limpo!');
}

// ✅ EVENT LISTENER ÚNICO
document.addEventListener('livewire:init', () => {
    // Escutar eventos de limpeza
    Livewire.on('cliente-salvo', () => {
        setTimeout(limparFormulario, 100);
    });
    
    Livewire.on('campos-resetados', () => {
        setTimeout(limparFormulario, 100);
    });
});
</script>