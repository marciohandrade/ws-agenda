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

    <form wire:submit.prevent="salvar" class="w-full space-y-6">
    
        <!-- Linha 1: Nome (linha única) -->
        <div class="flex flex-col">
            <label>Nome</label>
            <input type="text" wire:model="nome" class="w-full border rounded p-2">
            @error('nome') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Linha 2: Email e Telefone -->
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[220px]">
                <label>Email</label>
                <input type="email" wire:model="email" class="w-full border rounded p-2">
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex-1 min-w-[180px]">
                <label>Telefone</label>
                <input type="text" wire:model="telefone" x-mask="(99) 99999-9999" maxlength="15" class="w-full border rounded p-2">
                @error('telefone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Linha 3: Data de Nascimento e Gênero -->
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[180px]">
                <label>Data de Nascimento</label>
                <input type="date" wire:model="data_nascimento"
                    class="w-full border rounded p-2"
                    max="{{ date('Y-m-d') }}" min="1900-01-01">
                @error('data_nascimento') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex-1 min-w-[180px]">
                <label>Gênero</label>
                <select wire:model="genero" class="w-full border rounded p-2">
                    <option value="">Selecione</option>
                    <option value="Masculino">Masculino</option>
                    <option value="Feminino">Feminino</option>
                    <option value="Não-binário">Não-binário</option>
                    <option value="Prefere não informar">Prefere não informar</option>
                </select>
                @error('genero') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Linha 4: CPF e CEP -->
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[180px]">
                <label>CPF</label>
                <input type="text" x-data x-mask="999.999.999-99" wire:model.defer="cpf"
                    class="w-full border rounded p-2" placeholder="000.000.000-00">
                @error('cpf') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex-1 min-w-[180px]">
                <label>CEP</label>
                <input type="text" x-data x-mask="99999-999" wire:model.defer="cep"
                    class="w-full border rounded p-2" placeholder="00000-000">
                @error('cep') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Linha 5: Endereço -->
        <div class="flex flex-col">
            <label>Endereço</label>
            <input type="text" wire:model.defer="endereco"
                class="w-full border rounded p-2"
                maxlength="80" placeholder="Rua, Avenida...">
            @error('endereco') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Linha 6: Número e Complemento -->
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[120px]">
                <label>Número</label>
                <input type="text" wire:model.defer="numero"
                    class="w-full border rounded p-2"
                    maxlength="10" placeholder="123">
                @error('numero') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex-1 min-w-[180px]">
                <label>Complemento</label>
                <input type="text" wire:model.defer="complemento"
                    class="w-full border rounded p-2"
                    maxlength="30" placeholder="Apto, Bloco, Fundos...">
                @error('complemento') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Linha Final: Botões Centralizados -->
        <div class="w-full flex justify-center mt-6">
            @if ($cliente_id)
                <button type="button" wire:click="resetCampos"
                        class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-4">
                    Cancelar
                </button>
            @endif

            <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded hover:bg-gray-900">
                {{ $cliente_id ? 'Atualizar' : 'Cadastrar' }}
            </button>
        </div>

    </form>



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
                        <td class="p-2">{{ \Carbon\Carbon::parse($cliente->data_nascimento)->format('d/m/Y') }}</td>
                        <td class="p-2">{{ $cliente->genero }}</td>
                        <td class="p-2">
                            <button wire:click="editar({{ $cliente->id }})" class="text-blue-600 hover:underline">Editar</button>
                            <button wire:click="excluir({{ $cliente->id }})" class="text-red-600 hover:underline ml-2">Excluir</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-gray-500 p-4">Nenhum cliente cadastrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>



 <!-- FECHA A TABELA -->
</div> <!-- FECHA A DIV PRINCIPAL -->

