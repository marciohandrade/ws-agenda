@extends('layouts.clinica')

@section('title', 'Entrar - Login')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- ✅ HEADER DA PÁGINA --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                    <span class="text-2xl">🔐</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">
                    Entrar na sua conta
                </h1>
                <p class="text-gray-600 text-sm">
                    Acesse sua conta para gerenciar seus agendamentos
                </p>
            </div>
        </div>

        {{-- ✅ FORMULÁRIO DE LOGIN --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            @if($errors->any())
                <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                    <ul class="text-sm space-y-1">
                        @foreach($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('status'))
                <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                {{-- EMAIL --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <span class="text-blue-600 mr-2">📧</span>
                        E-mail
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autocomplete="email" 
                           autofocus
                           class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror"
                           placeholder="seu@email.com">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- SENHA --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <span class="text-blue-600 mr-2">🔒</span>
                        Senha
                    </label>
                    <div class="relative">
                        <input type="password" 
                            id="password" 
                            name="password" 
                            required 
                            autocomplete="current-password"
                            class="w-full px-3 py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror"
                            placeholder="Sua senha">
                        
                        {{-- BOTÃO TOGGLE SENHA --}}
                        <button type="button" 
                                id="togglePassword"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700 focus:outline-none transition-colors"
                                onclick="togglePasswordVisibility()">
                            <span id="eyeIcon" class="text-lg">👁️</span>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- LEMBRAR-ME --}}
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="remember" 
                           name="remember" 
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                           {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember" class="ml-2 block text-sm text-gray-700">
                        Lembrar-me
                    </label>
                </div>

                {{-- BOTÃO LOGIN --}}
                <div>
                    <button type="submit" 
                            class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                        <span class="mr-2">🚀</span>Entrar
                    </button>
                </div>
            </form>
        </div>

        {{-- ✅ LINKS ÚTEIS --}}
        <div class="mt-6 space-y-4">
            {{-- ESQUECI A SENHA --}}
            @if (Route::has('password.request'))
                <div class="text-center">
                    <a href="{{ route('password.request') }}" 
                       class="text-sm text-blue-600 hover:text-blue-800 underline">
                        <span class="mr-1">🔑</span>Esqueci minha senha
                    </a>
                </div>
            @endif

            {{-- SEPARADOR --}}
            <div class="flex items-center">
                <div class="flex-1 border-t border-gray-300"></div>
                <div class="px-4 text-sm text-gray-500">ou</div>
                <div class="flex-1 border-t border-gray-300"></div>
            </div>

            {{-- CRIAR CONTA --}}
            <div class="bg-gray-50 rounded-lg p-4 text-center">
                <p class="text-sm text-gray-600 mb-3">Ainda não tem uma conta?</p>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" 
                       class="inline-block px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium">
                        <span class="mr-2">✨</span>Criar nova conta
                    </a>
                @else
                    <a href="/agendar" 
                       class="inline-block px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium">
                        <span class="mr-2">📅</span>Agendar sem cadastro
                    </a>
                @endif
            </div>

            {{-- VOLTAR --}}
            <div class="text-center">
                <a href="/" class="text-sm text-gray-600 hover:text-gray-800">
                    <span class="mr-1">🏠</span>Voltar ao site
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
{{-- JAVASCRIPT PARA TOGGLE DA SENHA --}}
<script>
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if (passwordInput.type === 'password') {
        // Mostrar senha
        passwordInput.type = 'text';
        eyeIcon.textContent = '🙈'; // Olho fechado
        eyeIcon.title = 'Ocultar senha';
    } else {
        // Ocultar senha
        passwordInput.type = 'password';
        eyeIcon.textContent = '👁️'; // Olho aberto
        eyeIcon.title = 'Mostrar senha';
    }
}

// Adicionar título inicial
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('eyeIcon').title = 'Mostrar senha';
});
</script>