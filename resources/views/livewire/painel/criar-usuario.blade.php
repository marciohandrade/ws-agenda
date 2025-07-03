<div class="space-y-6">
    {{-- CABEÇALHO --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">
                    <i class="fas fa-user-plus mr-2 text-blue-600"></i>
                    Criar Novo Usuário
                </h1>
                <p class="text-gray-600">Preencha os dados do novo usuário</p>
            </div>
            <button wire:click="voltar" 
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar
            </button>
        </div>
    </div>

    {{-- ERRO GERAL --}}
    @error('geral')
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ $message }}
            </div>
        </div>
    @enderror

    {{-- FORMULÁRIO --}}
    <div class="bg-white rounded-lg shadow p-6">
        <form wire:submit="salvar" class="space-y-6">
            
            {{-- NOME --}}
            <div>
                <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">
                    Nome Completo *
                </label>
                <input type="text" 
                       id="nome"
                       wire:model="nome"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nome') border-red-500 @enderror"
                       placeholder="Digite o nome completo">
                @error('nome')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- EMAIL --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                    E-mail *
                </label>
                <input type="email" 
                       id="email"
                       wire:model="email"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                       placeholder="Digite o e-mail">
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- TELEFONE --}}
            <div>
                <label for="telefone" class="block text-sm font-medium text-gray-700 mb-1">
                    Telefone *
                </label>
                <input type="tel" 
                       id="telefone"
                       wire:model="telefone"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('telefone') border-red-500 @enderror"
                       placeholder="(11) 99999-9999">
                @error('telefone')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- TIPO DE USUÁRIO --}}
            <div>
                <label for="tipoUsuario" class="block text-sm font-medium text-gray-700 mb-1">
                    Tipo de Usuário *
                </label>
                <select id="tipoUsuario"
                        wire:model="tipoUsuario"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tipoUsuario') border-red-500 @enderror">
                    @foreach($this->tiposUsuarioDisponiveis as $valor => $label)
                        <option value="{{ $valor }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('tipoUsuario')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- SENHA --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="senha" class="block text-sm font-medium text-gray-700 mb-1">
                        Senha *
                    </label>
                    <input type="password" 
                           id="senha"
                           wire:model="senha"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('senha') border-red-500 @enderror"
                           placeholder="Mínimo 6 caracteres">
                    @error('senha')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="senhaConfirmacao" class="block text-sm font-medium text-gray-700 mb-1">
                        Confirmar Senha *
                    </label>
                    <input type="password" 
                           id="senhaConfirmacao"
                           wire:model="senhaConfirmacao"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('senhaConfirmacao') border-red-500 @enderror"
                           placeholder="Digite a senha novamente">
                    @error('senhaConfirmacao')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- OBSERVAÇÃO --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 mt-0.5 mr-2"></i>
                    <div class="text-sm text-blue-700">
                        <p class="font-medium mb-1">Informações importantes:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>A senha deve conter letras e números</li>
                            <li>O e-mail será usado para login no sistema</li>
                            <li>O telefone deve ser único para cada usuário</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- BOTÕES --}}
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <button type="button" 
                        wire:click="voltar"
                        class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
                        wire:loading.attr="disabled">
                    <span wire:loading.remove>
                        <i class="fas fa-save mr-2"></i>
                        Criar Usuário
                    </span>
                    <span wire:loading>
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Salvando...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>