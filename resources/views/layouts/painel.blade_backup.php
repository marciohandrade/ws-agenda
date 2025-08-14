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

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="/painel/agendamentos">
                                <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <x-nav-link href="/painel/agendamentos" :active="request()->is('painel/agendamentos*')">
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

                    <!-- Settings Dropdown -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ Auth::user()->name }}</div>
                                    <div class="ml-1 text-xs text-gray-400">({{ Auth::user()->tipo_usuario }})</div>

                                    <div class="ml-2">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link href="/perfil">
                                    {{ __('Meu Perfil') }}
                                </x-dropdown-link>

                                <x-dropdown-link href="/meus-agendamentos">
                                    {{ __('Meus Agendamentos') }}
                                </x-dropdown-link>

                                <div class="border-t border-gray-100"></div>

                                <!-- Authentication -->
                                <form method="GET" action="/logout">
                                    <x-dropdown-link href="/logout"
                                            onclick="event.preventDefault();
                                                        this.closest('form').submit();">
                                        {{ __('Sair') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <!-- Hamburger -->
                    <div class="-mr-2 flex items-center sm:hidden">
                        <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Responsive Navigation Menu -->
            <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
                <div class="pt-2 pb-3 space-y-1">
                    <x-responsive-nav-link href="/painel/agendamentos" :active="request()->is('painel/agendamentos*')">
                        {{ __('Agendamentos') }}
                    </x-responsive-nav-link>
                    
                    @if(auth()->user()->tipo_usuario === 'super_admin' || auth()->user()->tipo_usuario === 'admin')
                        <x-responsive-nav-link href="/painel/clientes" :active="request()->is('painel/clientes*')">
                            {{ __('Clientes') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link href="/painel/servicos" :active="request()->is('painel/servicos*')">
                            {{ __('Serviços') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link href="/painel/usuarios" :active="request()->is('painel/usuarios*')">
                            {{ __('Usuários') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link href="/painel/configuracoes-agendamento" :active="request()->is('painel/configuracoes*')">
                            {{ __('Configurações') }}
                        </x-responsive-nav-link>
                    @endif
                </div>

                <!-- Responsive Settings Options -->
                <div class="pt-4 pb-1 border-t border-gray-200">
                    <div class="px-4">
                        <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                        <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                        <div class="font-medium text-xs text-gray-400">{{ Auth::user()->tipo_usuario }}</div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <x-responsive-nav-link href="/perfil">
                            {{ __('Meu Perfil') }}
                        </x-responsive-nav-link>

                        <x-responsive-nav-link href="/meus-agendamentos">
                            {{ __('Meus Agendamentos') }}
                        </x-responsive-nav-link>

                        <!-- Authentication -->
                        <form method="GET" action="/logout">
                            <x-responsive-nav-link href="/logout"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Sair') }}
                            </x-responsive-nav-link>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
    
    <!-- Livewire Scripts -->
    @livewireScripts
</body>
</html>