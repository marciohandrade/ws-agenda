<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Agende seu serviço de forma rápida e fácil">
    <title>{{ $title ?? 'Agendamento Online' }}</title>
    
    <!-- TailwindCSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    
    <!-- Header -->
    <!-- <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Nome da Empresa</h1>
                    <p class="text-sm text-gray-600">Agendamento Online</p>
                </div>
                <nav class="hidden md:flex space-x-4">
                    <a href="/" class="text-gray-600 hover:text-gray-900">Início</a>
                    <a href="/servicos" class="text-gray-600 hover:text-gray-900">Serviços</a>
                    <a href="/contato" class="text-gray-600 hover:text-gray-900">Contato</a>
                </nav>
            </div>
        </div>
    </header> -->

    <!-- Main Content -->
    <main class="flex-1 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{ $slot }}
        </div>
    </main>

    <!-- Footer - LARGURA TOTAL -->
    <!-- <footer class="w-full bg-white border-t mt-16">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center text-gray-600">
                <p>&copy; {{ date('Y') }} Nome da Empresa 1. Todos os direitos reservados.</p>
                <p class="mt-2 text-sm">
                    Dúvidas? Entre em contato: 
                    <a href="tel:+5511999999999" class="text-blue-600 hover:text-blue-800">(11) 99999-9999</a> | 
                    <a href="mailto:contato@empresa.com" class="text-blue-600 hover:text-blue-800">contato@empresa.com</a>
                </p>
            </div>
        </div>
    </footer> -->
    <footer class="bg-blue-800 text-white py-6 text-center">
        <div class="mb-2">
            <a href="#" class="mx-2 text-white hover:text-gray-200"><i class="fab fa-whatsapp fa-lg"></i></a>
            <a href="#" class="mx-2 text-white hover:text-gray-200"><i class="fab fa-instagram fa-lg"></i></a>
            <a href="#" class="mx-2 text-white hover:text-gray-200"><i class="fab fa-facebook fa-lg"></i></a>
        </div>
        <p class="text-sm">&copy; 2025 Clínica Vida Saudável. Todos os direitos reservados.</p>
    </footer>

    @livewireScripts
</body>
</html>