<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Painel Administrativo</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- ✅ TAILWIND CSS - CONDICIONAL -->
    @if(config('app.debug'))
        {{-- Desenvolvimento: CDN para prototipação rápida --}}
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            primary: {
                                50: '#eff6ff',
                                500: '#3b82f6',
                                600: '#2563eb',
                                700: '#1d4ed8',
                                900: '#1e3a8a',
                            }
                        }
                    }
                }
            }
        </script>
    @endif
    
    <!-- ✅ SEMPRE CARREGA: CSS compilado (sobrescreve CDN em produção) -->
    @vite(['resources/css/app.css'])
    
    <!-- ✅ AGENDAMENTOS CSS - CONDICIONAL -->
    @if(request()->is('painel/agendamentos*'))
        <style>
            /* Scrollbar personalizada */
            .scrollbar-hide {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }
            .scrollbar-hide::-webkit-scrollbar {
                display: none;
            }

            /* Status scroll containers */
            .status-scroll-container {
                position: relative;
            }
            
            .status-scroll-container::before,
            .status-scroll-container::after {
                content: '';
                position: absolute;
                top: 0;
                bottom: 0;
                width: 1rem;
                pointer-events: none;
                z-index: 10;
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            
            .status-scroll-container::before {
                left: 0;
                background: linear-gradient(to right, #ffffff, transparent);
            }
            
            .status-scroll-container::after {
                right: 0;
                background: linear-gradient(to left, #ffffff, transparent);
            }

            .status-scroll-container.can-scroll-left::before,
            .status-scroll-container.can-scroll-right::after {
                opacity: 1;
            }

            /* Status buttons com melhor performance */
            .status-button {
                transition: all 0.2s ease-in-out;
                transform: translateZ(0); /* Hardware acceleration */
            }

            .status-button:hover {
                transform: translateY(-1px);
            }

            .status-button-active {
                transform: translateY(-1px);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }

            /* Scroll suave para status */
            .status-scroll {
                scroll-behavior: smooth;
            }
        </style>
    @endif

    <!-- ✅ ALPINE.JS - SEM DUPLICAÇÃO -->
    @if(app()->environment('local') && config('app.debug'))
        {{-- DESENVOLVIMENTO: CDN para debug mais fácil --}}
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @else
        {{-- PRODUÇÃO: Via Vite (sem CDN) --}}
        @vite(['resources/js/app.js'])
    @endif
    
    <!-- ✅ LIVEWIRE STYLES -->
    @livewireStyles

    <!-- ✅ ESTILOS GLOBAIS -->
    <style>
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Loading animation */
        .loading-spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Mobile touch improvements */
        @media (max-width: 768px) {
            .touch-manipulation {
                touch-action: manipulation;
            }
        }

        /* Focus states melhorados */
        button:focus,
        a:focus,
        input:focus,
        select:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        /* Prevenção de flash */
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50" x-data="{ open: false }">
    <div class="min-h-screen">
        <!-- ✅ NAVIGATION (mantido exatamente como estava) -->
        <nav class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="/painel/agendamentos" class="text-xl font-bold text-gray-800 hover:text-gray-600 transition-colors">
                                {{ config('app.name', 'Sistema') }}
                            </a>
                        </div>

                        <!-- Navigation Links Desktop -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <x-nav-link href="/painel/agendamentos" :active="request()->is('painel/agendamentos*')" class="border-b-2 border-transparent hover:border-gray-300 transition-colors">
                                {{ __('Agendamentos') }}
                            </x-nav-link>
                            
                            @if(auth()->user()->tipo_usuario === 'super_admin' || auth()->user()->tipo_usuario === 'admin')
                                <x-nav-link href="/painel/clientes" :active="request()->is('painel/clientes*')">
                                    {{ __('Clientes') }}
                                </x-nav-link>
                                <x-nav-link href="/painel/servicos" :active="request()->is('painel/servicos*')">
                                    {{ __('Serviços') }}
                                </x-nav-link>
                                <x-nav-link href="/painel/usuarios" :active="request()->is('painel/usuarios*')">
                                    {{ __('Usuários') }}
                                </x-nav-link>
                                <x-nav-link href="/painel/configuracoes-agendamento" :active="request()->is('painel/configuracoes*')">
                                    {{ __('Configurações') }}
                                </x-nav-link>
                            @endif
                        </div>
                    </div>

                    <!-- ✅ USER MENU DESKTOP -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <div class="relative" x-data="{ userMenu: false }">
                            <button @click="userMenu = !userMenu" 
                                    @click.away="userMenu = false"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ml-1 text-xs text-gray-400">({{ Auth::user()->tipo_usuario }})</div>
                                <svg class="ml-2 h-4 w-4 transition-transform" :class="{ 'rotate-180': userMenu }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="userMenu" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 transform scale-100"
                                 x-transition:leave-end="opacity-0 transform scale-95"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                                <div class="py-1">
                                    <a href="/perfil" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                        <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        {{ __('Meu Perfil') }}
                                    </a>
                                    <a href="/meus-agendamentos" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                        <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        {{ __('Meus Agendamentos') }}
                                    </a>
                                    <hr class="my-1">
                                    <a href="/logout" 
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                       class="flex items-center px-4 py-2 text-sm text-red-700 hover:bg-red-50 transition-colors">
                                        <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        {{ __('Sair') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ HAMBURGER MOBILE -->
                    <div class="flex items-center sm:hidden">
                        <button @click="open = !open" 
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out touch-manipulation">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ✅ RESPONSIVE NAVIGATION MENU -->
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-1"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-1"
                 class="sm:hidden bg-white border-t border-gray-200">
                
                <!-- Navigation Links -->
                <div class="pt-2 pb-3 space-y-1">
                    <a href="/painel/agendamentos" 
                       class="block pl-3 pr-4 py-2 border-l-4 text-left text-base font-medium transition-colors touch-manipulation
                              {{ request()->is('painel/agendamentos*') ? 'border-blue-400 text-blue-700 bg-blue-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300' }}">
                        {{ __('Agendamentos') }}
                    </a>
                    
                    @if(auth()->user()->tipo_usuario === 'super_admin' || auth()->user()->tipo_usuario === 'admin')
                        <a href="/painel/clientes" 
                           class="block pl-3 pr-4 py-2 border-l-4 text-left text-base font-medium transition-colors touch-manipulation
                                  {{ request()->is('painel/clientes*') ? 'border-blue-400 text-blue-700 bg-blue-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300' }}">
                            {{ __('Clientes') }}
                        </a>
                        <a href="/painel/servicos" 
                           class="block pl-3 pr-4 py-2 border-l-4 text-left text-base font-medium transition-colors touch-manipulation
                                  {{ request()->is('painel/servicos*') ? 'border-blue-400 text-blue-700 bg-blue-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300' }}">
                            {{ __('Serviços') }}
                        </a>
                        <a href="/painel/usuarios" 
                           class="block pl-3 pr-4 py-2 border-l-4 text-left text-base font-medium transition-colors touch-manipulation
                                  {{ request()->is('painel/usuarios*') ? 'border-blue-400 text-blue-700 bg-blue-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300' }}">
                            {{ __('Usuários') }}
                        </a>
                        <a href="/painel/configuracoes-agendamento" 
                           class="block pl-3 pr-4 py-2 border-l-4 text-left text-base font-medium transition-colors touch-manipulation
                                  {{ request()->is('painel/configuracoes*') ? 'border-blue-400 text-blue-700 bg-blue-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300' }}">
                            {{ __('Configurações') }}
                        </a>
                    @endif
                </div>

                <!-- User Section Mobile -->
                <div class="pt-4 pb-1 border-t border-gray-200">
                    <div class="px-4">
                        <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                        <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                        <div class="font-medium text-xs text-gray-400 capitalize">{{ Auth::user()->tipo_usuario }}</div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <a href="/perfil" 
                           class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition-colors touch-manipulation">
                            {{ __('Meu Perfil') }}
                        </a>
                        <a href="/meus-agendamentos" 
                           class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition-colors touch-manipulation">
                            {{ __('Meus Agendamentos') }}
                        </a>
                        <a href="/logout" 
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                           class="block px-4 py-2 text-base font-medium text-red-600 hover:text-red-800 hover:bg-red-50 transition-colors touch-manipulation">
                            {{ __('Sair') }}
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- ✅ PAGE CONTENT -->
        <main class="flex-1">
            {{ $slot }}
        </main>

        <!-- ✅ FORM DE LOGOUT OCULTO -->
        <form id="logout-form" action="/logout" method="GET" style="display: none;">
            @csrf
        </form>
    </div>
    
    <!-- ✅ TOAST NOTIFICATIONS - CONDICIONAL -->
    @if(request()->is('painel/agendamentos*'))
        <x-toast-notifications />
    @endif
    
    <!-- ✅ LIVEWIRE SCRIPTS -->
    @livewireScripts

    <!-- ✅ SCRIPTS GLOBAIS E FUNCIONALIDADES -->
    <script>
        // Global toast function
        window.showToast = function(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('toast-' + type, { detail: message }));
        };

        // Utility functions
        window.utils = {
            loading: false,
            setLoading: function(state) {
                this.loading = state;
            }
        };

        // Inicialização
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Layout carregado - Alpine status:', !!window.Alpine);
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert-auto-hide');
                alerts.forEach(function(alert) {
                    if (alert) {
                        alert.style.transition = 'opacity 0.5s';
                        alert.style.opacity = '0';
                        setTimeout(() => alert.remove(), 500);
                    }
                });
            }, 5000);

            // ✅ FUNCIONALIDADES ESPECÍFICAS PARA AGENDAMENTOS
            if (window.location.pathname.includes('/agendamentos')) {
                initAgendamentosFeatures();
            }
        });

        // ✅ FUNCIONALIDADES DE AGENDAMENTOS (INLINE - SEM ARQUIVO EXTERNO)
        function initAgendamentosFeatures() {
            console.log('🎯 Inicializando funcionalidades de agendamentos...');
            
            // Scroll horizontal inteligente
            const scrollContainers = document.querySelectorAll('.status-scroll');
            scrollContainers.forEach(container => {
                if (!container) return;
                
                // Scroll horizontal com wheel do mouse
                container.addEventListener('wheel', function(e) {
                    if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) return;
                    e.preventDefault();
                    container.scrollLeft += e.deltaY > 0 ? 60 : -60;
                });
                
                // Indicadores de scroll
                updateScrollIndicators(container);
                container.addEventListener('scroll', () => updateScrollIndicators(container));
            });

            // Auto-scroll para status ativo
            setTimeout(() => {
                scrollContainers.forEach(container => {
                    const activeButton = container.querySelector('.status-button-active');
                    if (activeButton) {
                        activeButton.scrollIntoView({
                            behavior: 'smooth',
                            block: 'nearest',
                            inline: 'center'
                        });
                    }
                });
            }, 200);

            // Atalhos de teclado
            document.addEventListener('keydown', function(e) {
                if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;
                
                if (e.ctrlKey || e.metaKey) {
                    switch(e.code) {
                        case 'KeyF':
                            e.preventDefault();
                            const searchInput = document.querySelector('input[wire\\:model*="buscaUnificada"]');
                            if (searchInput) {
                                searchInput.focus();
                                searchInput.select();
                                showToast('🔍 Campo de busca focado', 'info');
                            }
                            break;
                        case 'KeyR':
                            e.preventDefault();
                            showToast('🔄 Recarregando...', 'info');
                            setTimeout(() => window.location.reload(), 300);
                            break;
                        case 'KeyC':
                            e.preventDefault();
                            if (window.Livewire) {
                                const component = window.Livewire.find(getLivewireComponentId());
                                if (component) {
                                    component.call('limparFiltros');
                                    showToast('🧹 Filtros limpos', 'success');
                                }
                            }
                            break;
                    }
                }
            });

            console.log('✅ Funcionalidades de agendamentos ativas!');
        }

        // Atualiza indicadores visuais de scroll
        function updateScrollIndicators(container) {
            const wrapper = container.closest('.status-scroll-container');
            if (!wrapper) return;
            
            const canScrollLeft = container.scrollLeft > 0;
            const canScrollRight = container.scrollLeft < (container.scrollWidth - container.clientWidth);
            
            wrapper.classList.toggle('can-scroll-left', canScrollLeft);
            wrapper.classList.toggle('can-scroll-right', canScrollRight);
        }

        // Obter ID do componente Livewire
        function getLivewireComponentId() {
            const component = document.querySelector('[wire\\:id]');
            return component ? component.getAttribute('wire:id') : null;
        }

        // Reaplica funcionalidades após atualizações do Livewire
        document.addEventListener('livewire:updated', function() {
            if (window.location.pathname.includes('/agendamentos')) {
                setTimeout(initAgendamentosFeatures, 100);
            }
        });
    </script>
</body>
</html>