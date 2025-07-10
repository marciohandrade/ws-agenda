<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Clínica Vida Saudável')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @livewireStyles
    @yield('head')
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    @php
        $isLoginPage = request()->routeIs('login') || request()->is('login');
        $hideMenu = false; // NÃO esconder menu
        $hideFooter = $isLoginPage; // Só esconder footer na página de login
    @endphp

    {{-- 🎯 MENU SEMPRE VISÍVEL --}}
    @if(true)
        <header class="fixed top-0 w-full bg-white shadow z-50">
            <nav class="bg-white shadow-md fixed w-full top-0 left-0 z-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="/" class="text-xl font-bold text-blue-800">Clínica Vida</a>
                    </div>
                    <div class="-mr-2 flex items-center sm:hidden">
                        <!-- Botão do menu mobile -->
                        <button type="button" id="mobile-menu-button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-800 hover:text-white hover:bg-blue-600 focus:outline-none">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path class="inline" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        </button>
                    </div>
                    <!-- Menu Desktop -->
                    <div class="hidden sm:flex sm:items-center space-x-6">
                        <a href="/#inicio" class="menu-link px-3 py-2 rounded transition" data-section="inicio">Início</a>
                        <a href="/#sobre" class="menu-link px-3 py-2 rounded transition" data-section="sobre">Sobre</a>
                        <a href="/#especialidades" class="menu-link px-3 py-2 rounded transition" data-section="especialidades">Especialidades</a>
                        <a href="/#equipe" class="menu-link px-3 py-2 rounded transition" data-section="equipe">Equipe</a>                                                
                        <!-- Botão Agendar - sempre destacado -->
                            <a href="/agendar" class="menu-link px-4 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition font-medium {{ request()->is('agendar*') ? 'bg-blue-800' : '' }}">
                                <i class="fas fa-calendar-plus mr-2"></i>Agendar Online
                            </a>
                        <a href="/#contato" class="menu-link px-3 py-2 rounded transition" data-section="contato">Contato</a>
                        {{-- 🔥 DROPDOWN COM JAVASCRIPT --}}
                        @auth
                            <div class="relative">
                                <button id="user-dropdown-button" class="flex items-center px-3 py-2 rounded transition hover:bg-blue-50 text-blue-700 focus:outline-none">
                                    <i class="fas fa-user-circle mr-2"></i>
                                    <span>{{ Auth::user()->name }}</span>
                                    <i class="fas fa-chevron-down ml-2 text-xs transition-transform" id="dropdown-arrow"></i>
                                </button>
                                
                                <div id="user-dropdown-menu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border transform opacity-0 scale-95 transition-all duration-200 pointer-events-none z-50">
                                    <div class="py-2">
                                        <a href="/perfil" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 transition">
                                            <i class="fas fa-user mr-3"></i>Meu Perfil
                                        </a>
                                        <a href="/meus-agendamentos" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 transition">
                                            <i class="fas fa-calendar-check mr-3"></i>Meus Agendamentos
                                        </a>
                                        <a href="/agendar" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 transition">
                                            <i class="fas fa-plus mr-3"></i>Novo Agendamento
                                        </a>
                                        <div class="border-t border-gray-100 mt-2 pt-2">
                                            <form action="/logout" method="POST" class="inline w-full">
                                                @csrf
                                                <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                                                    <i class="fas fa-sign-out-alt mr-3"></i>Sair
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <a href="/login" class="menu-link px-3 py-2 rounded transition hover:bg-gray-100">
                                <i class="fas fa-sign-in-alt mr-1"></i>Entrar
                            </a>
                        @endauth
                    </div>
                    </div>
                </div>

                <!-- Menu Mobile -->
                <div id="mobile-menu" class="sm:hidden hidden px-4 pt-2 pb-4 space-y-1 bg-white shadow">
                <a href="/#inicio" class="menu-link block px-3 py-2 rounded transition" data-section="inicio">Início</a>
                <a href="/#sobre" class="menu-link block px-3 py-2 rounded transition" data-section="sobre">Sobre</a>
                <a href="/#especialidades" class="menu-link block px-3 py-2 rounded transition" data-section="especialidades">Especialidades</a>
                <a href="/#equipe" class="menu-link block px-3 py-2 rounded transition" data-section="equipe">Equipe</a>
                
                {{-- Menu Mobile baseado em autenticação --}}
                @auth
                    <div class="border-t border-gray-200 pt-3 mt-3">
                        <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            Minha Conta
                        </div>
                        <a href="/perfil" class="menu-link flex items-center px-3 py-2 rounded transition">
                            <i class="fas fa-user mr-3"></i>Meu Perfil
                        </a>
                        <a href="/meus-agendamentos" class="menu-link flex items-center px-3 py-2 rounded transition">
                            <i class="fas fa-calendar-check mr-3"></i>Meus Agendamentos
                        </a>
                        <a href="/agendar" class="menu-link flex items-center px-3 py-2 rounded transition">
                            <i class="fas fa-plus mr-3"></i>Novo Agendamento
                        </a>
                        
                        <div class="border-t border-gray-200 mt-3 pt-3">
                            <div class="px-3 py-1 text-xs text-gray-500">
                                Logado como: <strong>{{ Auth::user()->name }}</strong>
                            </div>
                            <form action="/logout" method="POST" class="mt-2">
                                @csrf
                                <button type="submit" class="w-full text-left px-3 py-2 text-red-600 hover:bg-red-50 rounded transition">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Sair
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="border-t border-gray-200 pt-3 mt-3">
                        <a href="/login" class="menu-link flex items-center px-3 py-2 rounded transition">
                            <i class="fas fa-sign-in-alt mr-3"></i>Entrar
                        </a>
                        
                        <!-- Botão Agendar Mobile -->
                        <a href="/agendar" class="menu-link block px-4 py-3 bg-blue-600 text-white rounded-lg transition font-medium text-center mt-2 {{ request()->is('agendar*') ? 'bg-blue-800' : '' }}">
                            <i class="fas fa-calendar-plus mr-2"></i>Agendar Online
                        </a>
                    </div>
                @endauth
                
                <a href="/#contato" class="menu-link block px-3 py-2 rounded transition" data-section="contato">Contato</a>
                </div>
            </nav>
        </header>
    @endif

    {{-- 🎯 MAIN SEMPRE COM PADDING (menu sempre visível) --}}
    <main class="pt-16">
        @yield('content')
    </main>

    {{-- 🎯 FOOTER CONDICIONAL - Só aparece se $hideFooter não for true --}}
    @if(!$hideFooter)
        <footer class="bg-blue-800 text-white py-6 text-center">
            <div class="mb-2">
                <a href="#" class="mx-2 text-white hover:text-gray-200"><i class="fab fa-whatsapp fa-lg"></i></a>
                <a href="#" class="mx-2 text-white hover:text-gray-200"><i class="fab fa-instagram fa-lg"></i></a>
                <a href="#" class="mx-2 text-white hover:text-gray-200"><i class="fab fa-facebook fa-lg"></i></a>
            </div>
            <p class="text-sm">&copy; 2025 Clínica Vida Saudável. Todos os direitos reservados.</p>
        </footer>
        
        <!-- ✅ BOTÃO WHATSAPP -->
        <a href="https://wa.me/5511999999999?text=Olá!%20Gostaria%20de%20agendar%20uma%20consulta%20com%20a%20Clínica%20Vida%20Saudável." 
        class="fixed bottom-4 right-4 flex items-center bg-green-500 text-white px-4 py-2 rounded-full shadow-lg hover:bg-green-600 transition z-50"
        target="_blank"
        aria-label="WhatsApp">
            <i class="fab fa-whatsapp fa-lg mr-2"></i>
            <span class="font-medium">Fale conosco</span>
        </a>
    @endif

    @livewireScripts
    
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        // 🔥 LÓGICA DO DROPDOWN DE USUÁRIO
        const dropdownButton = document.getElementById("user-dropdown-button");
        const dropdownMenu = document.getElementById("user-dropdown-menu");
        const dropdownArrow = document.getElementById("dropdown-arrow");
        
        if (dropdownButton && dropdownMenu) {
            let isOpen = false;
            
            function openDropdown() {
                isOpen = true;
                dropdownMenu.classList.remove("opacity-0", "scale-95", "pointer-events-none");
                dropdownMenu.classList.add("opacity-100", "scale-100");
                dropdownArrow.style.transform = "rotate(180deg)";
            }
            
            function closeDropdown() {
                isOpen = false;
                dropdownMenu.classList.remove("opacity-100", "scale-100");
                dropdownMenu.classList.add("opacity-0", "scale-95", "pointer-events-none");
                dropdownArrow.style.transform = "rotate(0deg)";
            }
            
            dropdownButton.addEventListener("click", function(e) {
                e.stopPropagation();
                if (isOpen) {
                    closeDropdown();
                } else {
                    openDropdown();
                }
            });
            
            document.addEventListener("click", function(e) {
                if (isOpen && !dropdownMenu.contains(e.target) && !dropdownButton.contains(e.target)) {
                    closeDropdown();
                }
            });
            
            document.addEventListener("keydown", function(e) {
                if (e.key === "Escape" && isOpen) {
                    closeDropdown();
                }
            });
        }

        // Resto do JavaScript do menu...
        const menuButton = document.getElementById("mobile-menu-button");
        const mobileMenu = document.getElementById("mobile-menu");

        if (menuButton && mobileMenu) {
            menuButton.addEventListener("click", () => {
                mobileMenu.classList.toggle("hidden");
            });
        }
    });
    </script>
    
    @yield('scripts')
</body>
</html>