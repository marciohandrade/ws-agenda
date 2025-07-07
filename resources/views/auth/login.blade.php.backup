@extends('layouts.clinica')

@section('title', 'Login - Clínica Vida Saudável')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-green-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        {{-- ✅ HEADER DO LOGIN --}}
        <div class="text-center">
            <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-full bg-blue-100 mb-4">
                <i class="fas fa-user-md text-2xl text-blue-600"></i>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">
                Bem-vindo de volta!
            </h2>
            <p class="text-gray-600">
                Acesse sua conta para continuar
            </p>
        </div>

        {{-- ✅ MENSAGENS DE FEEDBACK --}}
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <span class="text-green-800">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                    <span class="text-red-800">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if (session('info'))
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-blue-500 mr-3"></i>
                    <span class="text-blue-800">{{ session('info') }}</span>
                </div>
            </div>
        @endif

        {{-- ✅ FORMULÁRIO DE LOGIN --}}
        <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-100">
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                {{-- ✅ CAMPO EMAIL --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-blue-500"></i>
                        E-mail
                    </label>
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        autocomplete="email" 
                        required 
                        value="{{ old('email') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('email') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror"
                        placeholder="seu@email.com">
                    
                    @error('email')
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- ✅ CAMPO SENHA --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-blue-500"></i>
                        Senha
                    </label>
                    <div class="relative">
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            autocomplete="current-password" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('password') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror"
                            placeholder="Digite sua senha">
                        
                        {{-- ✅ BOTÃO MOSTRAR/OCULTAR SENHA --}}
                        <button 
                            type="button" 
                            id="togglePassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    
                    @error('password')
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- ✅ CHECKBOX LEMBRAR-ME --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            id="remember" 
                            name="remember" 
                            type="checkbox" 
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">
                            Lembrar-me
                        </label>
                    </div>

                    {{-- ✅ LINK ESQUECI SENHA --}}
                    <div class="text-sm">
                        <a href="{{ route('password.request') }}" class="text-blue-600 hover:text-blue-500 transition-colors">
                            Esqueceu a senha?
                        </a>
                    </div>
                </div>

                {{-- ✅ BOTÃO ENTRAR --}}
                <div>
                    <button 
                        type="submit" 
                        class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors font-medium">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Entrar
                    </button>
                </div>
            </form>
        </div>

        {{-- ✅ INFORMAÇÕES ADICIONAIS --}}
        <div class="text-center space-y-4">
            {{-- ✅ LINK PARA AGENDAMENTO PÚBLICO --}}
            <div class="text-sm text-gray-600">
                Primeira vez aqui? 
                <a href="{{ route('agendar') }}" class="text-blue-600 hover:text-blue-500 font-medium transition-colors">
                    Faça seu agendamento online
                </a>
            </div>

            {{-- ✅ INFORMAÇÕES DE CONTATO --}}
            <div class="border-t border-gray-200 pt-4">
                <p class="text-xs text-gray-500 mb-2">Precisa de ajuda?</p>
                <div class="flex justify-center space-x-4 text-sm">
                    <a href="#" class="text-green-600 hover:text-green-500 transition-colors">
                        <i class="fab fa-whatsapp mr-1"></i>
                        WhatsApp
                    </a>
                    <a href="mailto:contato@clinicavida.com.br" class="text-blue-600 hover:text-blue-500 transition-colors">
                        <i class="fas fa-envelope mr-1"></i>
                        E-mail
                    </a>
                </div>
            </div>
        </div>

        {{-- ✅ INFORMAÇÕES SOBRE TIPOS DE ACESSO --}}
        <div class="bg-gray-50 rounded-lg p-4 text-center">
            <h3 class="text-sm font-medium text-gray-800 mb-2">
                <i class="fas fa-info-circle mr-1 text-blue-500"></i>
                Tipos de Acesso
            </h3>
            <div class="grid grid-cols-2 gap-2 text-xs text-gray-600">
                <div class="flex items-center justify-center p-2 bg-white rounded border">
                    <i class="fas fa-user-tie mr-1 text-purple-500"></i>
                    <span>Administradores</span>
                </div>
                <div class="flex items-center justify-center p-2 bg-white rounded border">
                    <i class="fas fa-user mr-1 text-blue-500"></i>
                    <span>Clientes</span>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">
                O sistema redirecionará você automaticamente para a área adequada
            </p>
        </div>
    </div>
</div>

{{-- ✅ JAVASCRIPT PARA FUNCIONALIDADES AVANÇADAS --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ✅ FUNCIONALIDADE MOSTRAR/OCULTAR SENHA
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (togglePassword && passwordInput && eyeIcon) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Alternar ícone
            if (type === 'password') {
                eyeIcon.className = 'fas fa-eye';
            } else {
                eyeIcon.className = 'fas fa-eye-slash';
            }
        });
    }

    // ✅ FOCUS NO PRIMEIRO CAMPO COM ERRO
    const firstError = document.querySelector('.border-red-500');
    if (firstError) {
        firstError.focus();
    } else {
        // Se não há erros, focus no email
        const emailInput = document.getElementById('email');
        if (emailInput && !emailInput.value) {
            emailInput.focus();
        }
    }

    // ✅ ANIMAÇÃO DE LOADING NO BOTÃO DE SUBMIT
    const form = document.querySelector('form');
    const submitButton = form.querySelector('button[type="submit"]');
    
    if (form && submitButton) {
        form.addEventListener('submit', function() {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Entrando...';
            
            // Reabilitar botão após 10 segundos (fallback)
            setTimeout(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fas fa-sign-in-alt mr-2"></i>Entrar';
            }, 10000);
        });
    }

    // ✅ FEEDBACK VISUAL NOS CAMPOS
    const inputs = document.querySelectorAll('input[type="email"], input[type="password"]');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim() !== '') {
                this.classList.add('border-green-300');
                this.classList.remove('border-gray-300');
            } else {
                this.classList.remove('border-green-300');
                this.classList.add('border-gray-300');
            }
        });

        input.addEventListener('focus', function() {
            this.classList.remove('border-green-300', 'border-red-500');
        });
    });

    // ✅ AUTO-HIDE DE MENSAGENS APÓS 5 SEGUNDOS
    const messages = document.querySelectorAll('.bg-green-50, .bg-red-50, .bg-blue-50');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.transition = 'opacity 0.5s ease-out';
            message.style.opacity = '0';
            setTimeout(() => {
                message.remove();
            }, 500);
        }, 5000);
    });
});

// ✅ DETECTAR CAPS LOCK
document.addEventListener('keydown', function(event) {
    const passwordInput = document.getElementById('password');
    if (passwordInput && passwordInput === document.activeElement) {
        if (event.getModifierState && event.getModifierState('CapsLock')) {
            showCapsLockWarning();
        } else {
            hideCapsLockWarning();
        }
    }
});

function showCapsLockWarning() {
    let warning = document.getElementById('capsLockWarning');
    if (!warning) {
        warning = document.createElement('div');
        warning.id = 'capsLockWarning';
        warning.className = 'mt-1 text-xs text-orange-600 flex items-center';
        warning.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i>Caps Lock está ativado';
        
        const passwordInput = document.getElementById('password');
        passwordInput.parentNode.appendChild(warning);
    }
}

function hideCapsLockWarning() {
    const warning = document.getElementById('capsLockWarning');
    if (warning) {
        warning.remove();
    }
}
</script>
@endsection