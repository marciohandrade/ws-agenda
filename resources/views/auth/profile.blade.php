@extends('layouts.clinica')

@section('title', 'Meu Perfil - Clínica Vida Saudável')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- ✅ HEADER DA PÁGINA COM PADRÃO VISUAL CONSISTENTE --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center mb-4 sm:mb-0">
                    <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                        <span class="text-xl text-blue-600">👤</span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Meu Perfil</h1>
                        <p class="text-gray-600">Gerencie suas informações pessoais</p>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                    <a href="{{ route('meus-agendamentos') }}" 
                       class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-center">
                        <span class="mr-2">📅</span>
                        Meus Agendamentos
                    </a>
                    <a href="/agendar" 
                       class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-center">
                        <span class="mr-2">✅</span>
                        Novo Agendamento
                    </a>
                    {{-- ✅ BOTÃO LOGOUT COM PADRÃO CONSISTENTE --}}
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm('Tem certeza que deseja sair da sua conta?')"
                                class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-center">
                            <span class="mr-2">🚪</span>
                            Sair
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ✅ MENSAGENS DE FEEDBACK --}}
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6" id="success-message">
                <div class="flex items-center">
                    <span class="text-green-500 mr-3">✅</span>
                    <span class="text-green-800">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6" id="error-message">
                <div class="flex items-center">
                    <span class="text-red-500 mr-3">❌</span>
                    <span class="text-red-800">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- ✅ INFORMAÇÕES ATUAIS COM LOGOUT SECUNDÁRIO --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <span class="mr-2 text-blue-500">ℹ️</span>
                        Informações Atuais
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Nome</label>
                            <p class="text-gray-900 font-medium">{{ $user->name }}</p>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-500">E-mail</label>
                            <p class="text-gray-900">{{ $user->email }}</p>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-500">Telefone</label>
                            <p class="text-gray-900">
                                {{ $user->telefone ? App\Http\Controllers\Auth\AuthController::formatPhone($user->telefone) : 'Não informado' }}
                            </p>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-500">Tipo de Conta</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $user->tipo_usuario === 'admin' ? 'bg-purple-100 text-purple-800' : 
                                   ($user->tipo_usuario === 'colaborador' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                                <span class="mr-1">{{ $user->tipo_usuario === 'admin' ? '🛡️' : ($user->tipo_usuario === 'colaborador' ? '👔' : '👤') }}</span>
                                {{ ucfirst($user->tipo_usuario) }}
                            </span>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-500">Membro desde</label>
                            <p class="text-gray-900">{{ $user->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>

                    {{-- ✅ ESTATÍSTICAS RÁPIDAS --}}
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h4 class="text-sm font-medium text-gray-500 mb-3">Atividade Recente</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Último acesso:</span>
                                <span class="text-gray-900">Agora</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Agendamentos:</span>
                                <a href="{{ route('meus-agendamentos') }}" class="text-blue-600 hover:text-blue-500">
                                    Ver todos
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- ✅ ÁREA DE AÇÕES RÁPIDAS COM PADRÃO CONSISTENTE --}}
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h4 class="text-sm font-medium text-gray-500 mb-3">Ações Rápidas</h4>
                        <div class="space-y-2">
                            <a href="/agendar" 
                               class="w-full flex items-center justify-center px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm">
                                <span class="mr-2">✅</span>Novo Agendamento
                            </a>
                            <a href="{{ route('meus-agendamentos') }}" 
                               class="w-full flex items-center justify-center px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm">
                                <span class="mr-2">📅</span>Ver Agendamentos
                            </a>
                            
                            {{-- ✅ LOGOUT SECUNDÁRIO COM PADRÃO CONSISTENTE --}}
                            <div class="pt-2 border-t border-gray-200">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" 
                                            onclick="return confirm('Tem certeza que deseja encerrar sua sessão?')"
                                            class="w-full flex items-center justify-center px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm">
                                        <span class="mr-2">🚪</span>Encerrar Sessão
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ✅ DICAS DE SEGURANÇA --}}
                <div class="bg-yellow-50 rounded-lg border border-yellow-200 p-4 mt-6">
                    <h4 class="text-yellow-800 font-medium mb-2 text-sm">
                        <span class="mr-2">🛡️</span>
                        Dicas de Segurança
                    </h4>
                    <ul class="text-yellow-700 text-xs space-y-1">
                        <li>• Use uma senha forte com letras e números</li>
                        <li>• Mantenha seus dados sempre atualizados</li>
                        <li>• Não compartilhe sua senha com terceiros</li>
                        <li>• Faça logout ao usar computadores públicos</li>
                        <li>• <strong>Sempre saia da conta</strong> quando terminar</li>
                    </ul>
                </div>
            </div>

            {{-- ✅ FORMULÁRIO DE EDIÇÃO (MANTIDO ORIGINAL) --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">
                        <span class="mr-2 text-green-500">✏️</span>
                        Editar Informações
                    </h3>

                    <form method="POST" action="{{ route('user.profile.update') }}" id="profile-form">
                        @csrf
                        @method('PATCH')

                        {{-- ✅ DADOS PESSOAIS --}}
                        <div class="space-y-6">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-4">Dados Pessoais</h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {{-- NOME --}}
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                            <span class="mr-1 text-gray-500">👤</span>
                                            Nome Completo
                                        </label>
                                        <input 
                                            type="text" 
                                            id="name" 
                                            name="name" 
                                            value="{{ old('name', $user->name) }}"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('name') border-red-500 @enderror"
                                            placeholder="Digite seu nome completo"
                                            required>
                                        @error('name')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>                              
                                    {{-- TELEFONE --}}
                                    <div>
                                        <label for="telefone" class="block text-sm font-medium text-gray-700 mb-2">
                                            <span class="mr-1 text-gray-500">📱</span>
                                            Telefone
                                        </label>
                                        <input 
                                            type="tel" 
                                            id="telefone" 
                                            name="telefone" 
                                            value="{{ old('telefone', $user->telefone) }}"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('telefone') border-red-500 @enderror"
                                            placeholder="(11) 99999-9999"
                                            maxlength="15"
                                            required>
                                        @error('telefone')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                {{-- EMAIL --}}
                                <div class="mt-4">
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                        <span class="mr-1 text-gray-500">📧</span>
                                        E-mail
                                    </label>
                                    <input 
                                        type="email" 
                                        id="email" 
                                        name="email" 
                                        value="{{ old('email', $user->email) }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('email') border-red-500 @enderror"
                                        placeholder="seu@email.com"
                                        required>
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- ✅ ALTERAÇÃO DE SENHA --}}
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-2">Alterar Senha</h4>
                                <p class="text-sm text-gray-600 mb-4">Deixe em branco se não quiser alterar a senha</p>
                                
                                <div class="space-y-4">
                                    {{-- SENHA ATUAL --}}
                                    <div>
                                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                                            <span class="mr-1 text-gray-500">🔑</span>
                                            Senha Atual
                                        </label>
                                        <div class="relative">
                                            <input 
                                                type="password" 
                                                id="current_password" 
                                                name="current_password" 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('current_password') border-red-500 @enderror"
                                                placeholder="Digite sua senha atual">
                                            <button type="button" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600" onclick="togglePassword('current_password')">
                                                <span id="current_password_icon">👁️</span>
                                            </button>
                                        </div>
                                        @error('current_password')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {{-- NOVA SENHA --}}
                                        <div>
                                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                                <span class="mr-1 text-gray-500">🔒</span>
                                                Nova Senha
                                            </label>
                                            <div class="relative">
                                                <input 
                                                    type="password" 
                                                    id="password" 
                                                    name="password" 
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('password') border-red-500 @enderror"
                                                    placeholder="Digite a nova senha"
                                                    minlength="6">
                                                <button type="button" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600" onclick="togglePassword('password')">
                                                    <span id="password_icon">👁️</span>
                                                </button>
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500">Mínimo 6 caracteres com letras e números</p>
                                            @error('password')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        {{-- CONFIRMAR SENHA --}}
                                        <div>
                                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                                <span class="mr-1 text-gray-500">🔒</span>
                                                Confirmar Nova Senha
                                            </label>
                                            <div class="relative">
                                                <input 
                                                    type="password" 
                                                    id="password_confirmation" 
                                                    name="password_confirmation" 
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('password_confirmation') border-red-500 @enderror"
                                                    placeholder="Confirme a nova senha">
                                                <button type="button" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600" onclick="togglePassword('password_confirmation')">
                                                    <span id="password_confirmation_icon">👁️</span>
                                                </button>
                                            </div>
                                            @error('password_confirmation')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ✅ BOTÕES DE AÇÃO --}}
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-3 sm:space-y-0">
                                <button 
                                    type="submit" 
                                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors font-medium"
                                    id="submit-btn">
                                    <span class="mr-2">💾</span>
                                    Salvar Alterações
                                </button>
                                
                                <div class="flex space-x-3">
                                    <button 
                                        type="button" 
                                        onclick="resetForm()" 
                                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                                        <span class="mr-1">↩️</span>
                                        Resetar
                                    </button>
                                    
                                    <a href="{{ route('meus-agendamentos') }}" 
                                       class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-center">
                                        <span class="mr-1">❌</span>
                                        Cancelar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ✅ INFORMAÇÕES ÚTEIS --}}
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <h4 class="font-medium text-blue-800 mb-2">
                    <span class="mr-2">🛡️</span>
                    Privacidade
                </h4>
                <p class="text-blue-700 text-sm">
                    Seus dados são protegidos e não serão compartilhados com terceiros.
                </p>
            </div>
            
            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                <h4 class="font-medium text-green-800 mb-2">
                    <span class="mr-2">🔄</span>
                    Sincronização
                </h4>
                <p class="text-green-700 text-sm">
                    Mudanças são aplicadas imediatamente em todos os dispositivos.
                </p>
            </div>
            
            <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                <h4 class="font-medium text-orange-800 mb-2">
                    <span class="mr-2">🎧</span>
                    Suporte
                </h4>
                <p class="text-orange-700 text-sm">
                    Precisa de ajuda? Entre em contato pelo WhatsApp.
                </p>
            </div>
        </div>
    </div>
</div>

{{-- ✅ JAVASCRIPT PARA FUNCIONALIDADES AVANÇADAS --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ✅ MÁSCARA DE TELEFONE
    const telefoneInput = document.getElementById('telefone');
    if (telefoneInput) {
        telefoneInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            
            if (value.length <= 11) {
                if (value.length <= 10) {
                    // Telefone fixo: (11) 9999-9999
                    value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
                } else {
                    // Celular: (11) 99999-9999
                    value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                }
            }
            
            this.value = value;
        });
    }

    // ✅ ANIMAÇÃO DE LOADING NO SUBMIT
    const form = document.getElementById('profile-form');
    const submitBtn = document.getElementById('submit-btn');
    
    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="mr-2">⏳</span>Salvando...';
            
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span class="mr-2">💾</span>Salvar Alterações';
            }, 10000);
        });
    }

    // ✅ VALIDAÇÃO EM TEMPO REAL
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('border-red-500')) {
                validateField(this);
            }
        });
    });

    // ✅ AUTO-HIDE DE MENSAGENS
    const messages = document.querySelectorAll('#success-message, #error-message');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.transition = 'opacity 0.5s ease-out';
            message.style.opacity = '0';
            setTimeout(() => {
                message.remove();
            }, 500);
        }, 5000);
    });

    // ✅ CONFIRMAÇÃO DE SENHAS
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    
    if (passwordInput && confirmInput) {
        [passwordInput, confirmInput].forEach(input => {
            input.addEventListener('input', function() {
                if (passwordInput.value && confirmInput.value) {
                    if (passwordInput.value === confirmInput.value) {
                        confirmInput.classList.remove('border-red-500');
                        confirmInput.classList.add('border-green-300');
                    } else {
                        confirmInput.classList.remove('border-green-300');
                        confirmInput.classList.add('border-red-500');
                    }
                }
            });
        });
    }
});

// ✅ FUNÇÃO PARA MOSTRAR/OCULTAR SENHA
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.textContent = '🙈';
    } else {
        field.type = 'password';
        icon.textContent = '👁️';
    }
}

// ✅ VALIDAÇÃO DE CAMPO
function validateField(field) {
    const value = field.value.trim();
    
    switch (field.type) {
        case 'email':
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (value && emailRegex.test(value)) {
                field.classList.remove('border-red-500');
                field.classList.add('border-green-300');
            } else if (value) {
                field.classList.remove('border-green-300');
                field.classList.add('border-red-500');
            }
            break;
            
        case 'tel':
            const phoneRegex = /^\(\d{2}\) \d{4,5}-\d{4}$/;
            if (value && phoneRegex.test(value)) {
                field.classList.remove('border-red-500');
                field.classList.add('border-green-300');
            } else if (value) {
                field.classList.remove('border-green-300');
                field.classList.add('border-red-500');
            }
            break;
            
        default:
            if (value.length >= 3) {
                field.classList.remove('border-red-500');
                field.classList.add('border-green-300');
            } else if (value) {
                field.classList.remove('border-green-300');
                field.classList.add('border-red-500');
            }
    }
}

// ✅ RESETAR FORMULÁRIO
function resetForm() {
    if (confirm('Tem certeza que deseja desfazer todas as alterações?')) {
        document.getElementById('profile-form').reset();
        
        // Limpar classes de validação
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.classList.remove('border-red-500', 'border-green-300');
            input.classList.add('border-gray-300');
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const telefoneInput = document.getElementById('telefone');
    
    if (telefoneInput) {
        // Aplicar máscara no valor inicial (se houver)
        if (telefoneInput.value) {
            telefoneInput.value = aplicarMascaraTelefone(telefoneInput.value);
        }
        
        // Aplicar máscara conforme digita
        telefoneInput.addEventListener('input', function(e) {
            e.target.value = aplicarMascaraTelefone(e.target.value);
        });
        
        // Permitir apenas teclas válidas
        telefoneInput.addEventListener('keypress', function(e) {
            // Permite: números, backspace, delete, tab, enter, setas
            const allowedKeys = [8, 9, 13, 46, 37, 38, 39, 40];
            const isNumber = (e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 96 && e.keyCode <= 105);
            
            if (!isNumber && !allowedKeys.includes(e.keyCode)) {
                e.preventDefault();
            }
        });
    }
    
    function aplicarMascaraTelefone(value) {
        // Remove tudo que não é número
        value = value.replace(/\D/g, '');
        
        // Limita a 11 dígitos
        value = value.substring(0, 11);
        
        // Aplica a máscara progressivamente
        if (value.length >= 11) {
            // Celular: (XX) 9XXXX-XXXX
            return value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        } else if (value.length >= 10) {
            // Fixo: (XX) XXXX-XXXX
            return value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
        } else if (value.length >= 6) {
            // Parcial: (XX) XXXX
            return value.replace(/(\d{2})(\d{4})/, '($1) $2');
        } else if (value.length >= 2) {
            // Só DDD: (XX
            return value.replace(/(\d{2})/, '($1) ');
        } else if (value.length >= 1) {
            // Começando: (X
            return value.replace(/(\d{1})/, '($1');
        }
        
        return value;
    }
});
</script>
@endsection