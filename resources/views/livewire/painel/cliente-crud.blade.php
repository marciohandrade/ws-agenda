<div class="max-w-4xl mx-auto p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Cadastro de Cliente</h2>

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





    <form wire:submit.prevent="salvar" class="grid grid-cols-2 gap-4 mb-6">
        <div>
            <label>Nome</label>
            <input type="text" wire:model="nome" class="w-full border rounded p-2">
            @error('nome') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div>
            <label>Email</label>
            <input type="email" wire:model="email" class="w-full border rounded p-2">
            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div x-data @input="$el.value = $el.value
            .replace(/\D/g, '')
            .replace(/^(\d{2})(\d)/, '($1) $2')
            .replace(/(\d{5})(\d)/, '$1-$2')
            .replace(/(-\d{4})\d+?$/, '$1')">
            <label>Telefone</label>
             <input type="text" wire:model="telefone" 
                    x-mask="(99) 99999-9999" 
                    maxlength="15"
                    class="w-full border rounded p-2">
            @error('telefone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div>
            <label>Data de Nascimento</label>
            <input
                    type="date"
                    wire:model="data_nascimento"
                    class="w-full border rounded p-2"
                    max="{{ date('Y-m-d') }}"
                    min="1900-01-01"
                    @input="if ($el.value.length > 10) $el.value = $el.value.slice(0, 10)"
                >
             @error('data_nascimento') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="col-span-2 text-right mt-4 flex justify-end gap-2">
            @if ($cliente_id)
                <button type="button" wire:click="resetCampos"
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Cancelar
                </button>
            @endif
            <!-- <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                {{ $cliente_id ? 'Atualizar' : 'Cadastrar' }}
            </button> -->
            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-900">
                {{ $cliente_id ? 'Atualizar' : 'Cadastrar' }}
            </button>
            <div wire:loading wire:target="salvar" class="text-sm text-gray-500 self-center">
                Salvando...
            </div>
        </div>
    </form>

    <h3 class="text-xl font-bold mb-2">Lista de Clientes</h3>
    <table class="w-full text-sm table-auto border">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2">Nome</th>
                <th class="p-2">Email</th>
                <th class="p-2">Telefone</th>
                <th class="p-2">Nascimento</th>
                <th class="p-2">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($clientes as $cliente)
            <tr>
                <td>{{ $cliente->nome }}</td>
                <td>{{ $cliente->email }}</td>
                <td>{{ $cliente->telefone }}</td>
                <td>{{ \Carbon\Carbon::parse($cliente->data_nascimento)->format('d/m/Y') }}</td>
                <td>
                    <button wire:click="editar({{ $cliente->id }})">Editar</button>
                    <button wire:click="excluir({{ $cliente->id }})">Excluir</button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-gray-500">Nenhum cliente cadastrado.</td>
            </tr>
        @endforelse
        </tbody>
    </table> <!-- FECHA A TABELA -->
</div> <!-- FECHA A DIV PRINCIPAL -->

