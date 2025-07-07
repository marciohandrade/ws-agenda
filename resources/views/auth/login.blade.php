<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Clínica Vida</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-md p-6">
            
            {{-- TÍTULO --}}
            <h1 class="text-2xl font-bold text-center mb-6">Login</h1>

            {{-- MENSAGENS --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            {{-- FORMULÁRIO --}}
            <form method="POST" action="/login">
                @csrf
                
                {{-- EMAIL --}}
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-mail</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="seu@email.com"
                        required>
                </div>

                {{-- SENHA --}}
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Senha</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Sua senha"
                        required>
                </div>

                {{-- LEMBRAR --}}
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="rounded">
                        <span class="ml-2 text-sm text-gray-600">Lembrar-me</span>
                    </label>
                </div>

                {{-- BOTÃO --}}
                <button 
                    type="submit" 
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Entrar
                </button>
            </form>

            {{-- LINKS --}}
            <div class="mt-6 text-center space-y-2">
                <a href="/agendar" class="text-blue-600 hover:text-blue-500 text-sm">
                    Fazer agendamento sem cadastro
                </a>
                <br>
                <a href="/" class="text-gray-600 hover:text-gray-500 text-sm">
                    Voltar ao site
                </a>
            </div>

            {{-- USUÁRIOS DE TESTE --}}
            <div class="mt-6 p-4 bg-yellow-50 rounded-md">
                <h3 class="text-sm font-medium text-yellow-800 mb-2">Usuários de Teste:</h3>
                <div class="text-xs text-yellow-700 space-y-1">
                    <p><strong>ana.teste@clinica.local</strong> / 123456</p>
                    <p><strong>carlos.teste@clinica.local</strong> / 123456</p>
                    <p><strong>admin@clinica.com</strong> / 123456</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>