@extends('layouts.clinica')

@section('title', 'Meu Perfil - Clínica Vida Saudável')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        {{-- Header da página --}}
        <div class="mb-8">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                <i class="fas fa-user-circle text-blue-600 mr-2"></i>
                {{ __('Meu Perfil') }}
            </h2>
            <p class="text-gray-600 mt-2">
                Gerencie suas informações pessoais e configurações de conta
            </p>
        </div>

        <div class="space-y-6">
            {{-- Atualizar Informações do Perfil --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-user text-blue-600 mr-2"></i>
                            Informações do Perfil
                        </h3>
                        <p class="text-sm text-gray-600">
                            Atualize suas informações pessoais e endereço de email
                        </p>
                    </div>
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>

            {{-- Atualizar Senha --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-lock text-blue-600 mr-2"></i>
                            Atualizar Senha
                        </h3>
                        <p class="text-sm text-gray-600">
                            Mantenha sua conta segura usando uma senha forte
                        </p>
                    </div>
                    <livewire:profile.update-password-form />
                </div>
            </div>

            {{-- Excluir Conta --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                            Excluir Conta
                        </h3>
                        <p class="text-sm text-gray-600">
                            Exclua permanentemente sua conta e todos os dados associados
                        </p>
                    </div>
                    <livewire:profile.delete-user-form />
                </div>
            </div>
        </div>

        {{-- Navegação --}}
        <div class="mt-8 flex justify-center space-x-4">
            <a href="/meus-agendamentos" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-calendar-check mr-2"></i>
                Meus Agendamentos
            </a>
            <a href="/agendar" 
               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-plus mr-2"></i>
                Novo Agendamento
            </a>
        </div>
    </div>
</div>
@endsection