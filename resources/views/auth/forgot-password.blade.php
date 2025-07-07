@extends('layouts.clinica')

@section('title', 'Recuperar Senha - Clínica Vida Saudável')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-green-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        
        {{-- ✅ HEADER DA RECUPERAÇÃO --}}
        <div class="text-center">
            <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-full bg-orange-100 mb-4">
                <i class="fas fa-key text-2xl text-orange-600"></i>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">
                Esqueceu sua senha?
            </h2>
            <p class="text-gray-600">
                Não se preocupe! Digite seu e-mail e enviaremos um link para redefinir sua senha.
            </p>
        </div>

        {{-- ✅ MENSAGENS DE FEEDBACK --}}
        @if (session('status'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <span class="text-green-800">{{ session('status') }}</span>
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

        {{-- ✅ FORMULÁRIO DE RECUPERAÇÃO --}}
        <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-100">
            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf

                {{-- ✅ CAMPO EMAIL --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-orange-500"></i>
                        E-mail cadastrado
                    </label>
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        autocomplete="email" 
                        required 
                        value="{{ old('email') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors @error('email') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror"
                        placeholder="Digite seu e-mail cadastrado">
                    
                    @error('email')
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- ✅ INFORMAÇÕES SOBRE O PROCESSO --}}
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <h4 class="text-blue-800 font-medium mb-2 text-sm">
                        <i class="fas fa-info-circle mr-2"></i>
                        Como funciona:
                    </h4>
                    <ul class="text-blue-700 text-xs space-y-1">
                        <li>• Verificaremos se o e-mail está cadastrado</li>
                        <li>• Enviaremos um link seguro para seu e-mail</li>
                        <li>• O link expira em 60 minutos</li>
                        <li>• Você poderá criar uma nova senha</li>
                    </ul>
                </div>

                {{-- ✅ BOTÃO ENVIAR --}}
                <div>
                    <button 
                        type="submit" 
                        class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors font-medium">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Enviar Link de Recuperação
                    </button>
                </div>
            </form>
        </div>

        {{-- ✅ LINKS ÚTEIS --}}
        <div class="text-center space-y-4">
            {{-- ✅ VOLTAR PARA LOGIN --}}
            <div class="text-sm">
                <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-500 font-medium transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Voltar para o login
                </a>
            </div>

            {{-- ✅ DIVIDER --}}
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-xs">
                    <span class="px-2 bg-gray-50 text-gray-500">ou</span>
                </div>
            </div>

            {{-- ✅ AGENDAMENTO SEM CADASTRO --}}
            <div class="text-sm text-gray-600">
                Primeira vez aqui? 
                <a href="{{ route('agendar') }}" class="text-orange-600 hover:text-orange-500 font-medium transition-colors">
                    Faça seu agendamento online
                </a>
            </div>
        </div>

        {{-- ✅ AJUDA E CONTATO --}}
        <div class="bg-gray-50 rounded-lg p-6 text-center">
            <h3 class="text-sm font-medium text-gray-800 mb-3">
                <i class="fas fa-headset mr-2 text-blue-500"></i>
                Precisa de ajuda?
            </h3>
            
            <div class="space-y-2 text-sm text-gray-600">
                <p>Se você não lembra qual e-mail usou ou não receber o link:</p>
                
                <div class="flex flex-col sm:flex-row gap-2 justify-center mt-3">
                    <a href="https://wa.me/5511999999999?text=Olá!%20Estou%20com%20dificuldades%20para%20acessar%20minha%20conta." 
                       target="_blank"
                       class="inline-flex items-center px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-xs">
                        <i class="fab fa-whatsapp mr-1"></i>
                        WhatsApp
                    </a>
                    <a href="tel:1130000000" 
                       class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-xs">
                        <i class="fas fa-phone mr-1"></i>
                        (11) 3000-0000
                    </a>
                    <a href="mailto:suporte@clinicavida.com.br" 
                       class="inline-flex items-center px-3 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-xs">
                        <i class="fas fa-envelope mr-1"></i>
                        E-mail
                    </a>
                </div>
            </div>
        </div>

        {{-- ✅ DICAS DE SEGURANÇA --}}
        <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
            <h4 class="text-yellow-800 font-medium mb-2 text-sm">
                <i class="fas fa-shield-alt mr-2"></i>
                Dicas de Segurança
            </h4>
            <ul class="text-yellow-700 text-xs space-y-1">
                <li>• Verifique sempre o remetente do e-mail</li>
                <li>• O link expira em 1 hora por segurança</li>
                <li>• Nunca compartilhe o link de recuperação</li>
                <li>• Use uma senha forte na redefinição</li>
            </ul>
        </div>
    </div>
</div>

{{-- ✅ JAVASCRIPT PARA FUNCIONALIDADES AVANÇADAS --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const submitButton = form.querySelector('button[type="submit"]');
    const emailInput = document.getElementById('email');

    // ✅ FOCUS NO CAMPO EMAIL
    if (emailInput && !emailInput.value) {
        emailInput.focus();
    }

    // ✅ VALIDAÇÃO EM TEMPO REAL DO EMAIL
    emailInput.addEventListener('input', function() {
        const email = this.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && emailRegex.test(email)) {
            this.classList.remove('border-gray-300', 'border-red-500');
            this.classList.add('border-green-300');
        } else if (email) {
            this.classList.remove('border-gray-300', 'border-green-300');
            this.classList.add('border-red-500');
        } else {
            this.classList.remove('border-green-300', 'border-red-500');
            this.classList.add('border-gray-300');
        }
    });

    // ✅ ANIMAÇÃO DE LOADING NO SUBMIT
    form.addEventListener('submit', function() {
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
        
        // ✅ Reabilitar após timeout (fallback)
        setTimeout(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Enviar Link de Recuperação';
        }, 10000);
    });

    // ✅ AUTO-HIDE DE MENSAGENS APÓS 10 SEGUNDOS
    const messages = document.querySelectorAll('.bg-green-50, .bg-red-50');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.transition = 'opacity 0.5s ease-out';
            message.style.opacity = '0';
            setTimeout(() => {
                message.remove();
            }, 500);
        }, 10000);
    });

    // ✅ VERIFICAR SE EMAIL JÁ FOI ENVIADO (localStorage)
    const emailSent = localStorage.getItem('password_reset_email_sent');
    const emailSentTime = localStorage.getItem('password_reset_sent_time');
    
    if (emailSent && emailSentTime) {
        const timePassed = Date.now() - parseInt(emailSentTime);
        const minutesPassed = Math.floor(timePassed / 60000);
        
        if (minutesPassed < 5) {
            const warningDiv = document.createElement('div');
            warningDiv.className = 'bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4';
            warningDiv.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-clock text-yellow-500 mr-3"></i>
                    <span class="text-yellow-800 text-sm">
                        Você já solicitou um link há ${minutesPassed} minuto(s). 
                        Verifique sua caixa de entrada e spam.
                    </span>
                </div>
            `;
            
            form.parentNode.insertBefore(warningDiv, form);
        } else {
            // Limpar dados antigos
            localStorage.removeItem('password_reset_email_sent');
            localStorage.removeItem('password_reset_sent_time');
        }
    }

    // ✅ SALVAR NO LOCALSTORAGE QUANDO ENVIAR
    form.addEventListener('submit', function() {
        const email = emailInput.value.trim();
        if (email) {
            localStorage.setItem('password_reset_email_sent', email);
            localStorage.setItem('password_reset_sent_time', Date.now().toString());
        }
    });

    // ✅ DETECTAR DOMÍNIOS COMUNS E SUGERIR CORREÇÕES
    emailInput.addEventListener('blur', function() {
        const email = this.value.trim().toLowerCase();
        const commonDomains = {
            'gmail.com': ['gmai.com', 'gmail.co', 'gmial.com'],
            'hotmail.com': ['hotmai.com', 'hotmail.co'],
            'yahoo.com': ['yahoo.co', 'yaho.com'],
            'outlook.com': ['outlook.co', 'outlok.com']
        };

        for (const [correct, typos] of Object.entries(commonDomains)) {
            for (const typo of typos) {
                if (email.includes(typo)) {
                    const suggested = email.replace(typo, correct);
                    
                    const suggestionDiv = document.createElement('div');
                    suggestionDiv.className = 'mt-2 text-xs text-blue-600';
                    suggestionDiv.innerHTML = `
                        Você quis dizer: 
                        <button type="button" class="underline hover:text-blue-800" 
                                onclick="document.getElementById('email').value='${suggested}'; this.parentElement.remove();">
                            ${suggested}
                        </button>?
                    `;
                    
                    // Remover sugestão anterior se existir
                    const oldSuggestion = emailInput.parentNode.querySelector('.text-blue-600');
                    if (oldSuggestion) {
                        oldSuggestion.remove();
                    }
                    
                    emailInput.parentNode.appendChild(suggestionDiv);
                    return;
                }
            }
        }
    });
});
</script>
@endsection