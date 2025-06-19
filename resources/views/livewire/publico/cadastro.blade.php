<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
    @if($mostrarFormulario)
        {{-- FORMULÁRIO DE CADASTRO --}}
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
                Criar Conta
            </h2>

            {{-- Mensagem de erro geral --}}
            @if($mensagemErro)
                <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ $mensagemErro }}
                </div>
            @endif

            <form wire:submit="cadastrar" class="space-y-4">
                {{-- Nome Completo --}}
                <div>
                    <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">
                        Nome Completo *
                    </label>
                    <input 
                        type="text" 
                        id="nome"
                        wire:model.live="nome"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('nome') border-red-500 @enderror"
                        placeholder="Digite seu nome completo"
                        maxlength="255"
                    >
                    @error('nome')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- E-mail --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        E-mail *
                    </label>
                    <input 
                        type="email" 
                        id="email"
                        wire:model.live="email"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror"
                        placeholder="seu@email.com"
                        maxlength="255"
                    >
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Telefone com máscara --}}
                <div>
                    <label for="telefone" class="block text-sm font-medium text-gray-700 mb-1">
                        Telefone *
                    </label>
                    <input 
                        type="tel" 
                        id="telefone"
                        wire:model.live="telefone"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('telefone') border-red-500 @enderror"
                        placeholder="(11) 99999-9999"
                        maxlength="15"
                        x-data=""
                        x-on:input="formatarTelefone($event.target)"
                    >
                    @error('telefone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Senha --}}
                <div>
                    <label for="senha" class="block text-sm font-medium text-gray-700 mb-1">
                        Senha *
                    </label>
                    <input 
                        type="password" 
                        id="senha"
                        wire:model.live="senha"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('senha') border-red-500 @enderror"
                        placeholder="Mínimo 6 caracteres"
                    >
                    @error('senha')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirmação de Senha --}}
                <div>
                    <label for="senha_confirmacao" class="block text-sm font-medium text-gray-700 mb-1">
                        Confirmar Senha *
                    </label>
                    <input 
                        type="password" 
                        id="senha_confirmacao"
                        wire:model.live="senha_confirmacao"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('senha_confirmacao') border-red-500 @enderror"
                        placeholder="Digite a senha novamente"
                    >
                    @error('senha_confirmacao')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Botão Cadastrar --}}
                <button 
                    type="submit"
                    style="background-color: #000000; color: #ffffff; padding: 12px; width: 100%; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>Criar Conta</span>
                    <span wire:loading style="color: #ffffff;">Processando...</span>
                </button>
            </form>

            {{-- Link para Login --}}
            <div class="mt-4 text-center">
                <p class="text-sm text-gray-600">
                    Já tem uma conta? 
                    <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-500 font-medium">
                        Entrar
                    </a>
                </p>
            </div>
        </div>

    @elseif($mostrarSucesso)
        {{-- TELA DE SUCESSO --}}
        <div class="text-center">
            <div class="mb-4">
                <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            
            <h2 class="text-xl font-bold text-gray-900 mb-2">
                Conta Criada com Sucesso!
            </h2>
            
            <p class="text-gray-600 mb-6">
                {{ $mensagemSucesso }}
            </p>

            <div class="space-y-3">
                {{-- Botão principal para ir ao login --}}
                <button 
                    wire:click="irParaLogin"
                    style="background-color: #059669; color: #ffffff; padding: 12px; width: 100%; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;"
                >
                    Fazer Login Agora
                </button>
                
                {{-- Link menor para cadastrar outra conta (para admins) --}}
                <div class="mt-4">
                    <button 
                        wire:click="voltarFormulario"
                        style="background: none; border: none; color: #6b7280; text-decoration: underline; cursor: pointer; font-size: 0.875rem;"
                    >
                        Cadastrar outra conta
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- JavaScript para máscara de telefone --}}
<script>
function formatarTelefone(input) {
    // Remove tudo que não é número
    let value = input.value.replace(/\D/g, '');
    
    // Aplica a formatação conforme o usuário digita
    if (value.length <= 2) {
        value = value.replace(/^(\d{0,2})/, '($1');
    } else if (value.length <= 7) {
        value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
    } else if (value.length <= 11) {
        value = value.replace(/^(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
    } else {
        // Limita a 11 dígitos
        value = value.substr(0, 11);
        value = value.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    }
    
    // Atualiza o valor do input
    input.value = value;
    
    // Dispara evento para o Livewire
    input.dispatchEvent(new Event('input', { bubbles: true }));
}
</script>