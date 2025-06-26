@extends('layouts.clinica')

@section('title', 'Agendar Consulta - Clínica Vida Saudável')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-white py-8">
    <div class="container mx-auto px-4">
        
        <!-- Header da página -->
        <div class="text-center mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-blue-800 mb-3">
                <i class="fas fa-calendar-plus mr-3"></i>Agendamento Online
            </h1>
            <p class="text-gray-600 text-lg">
                Escolha o Serviço | Especialidade, data e horário. É rápido e fácil!
            </p>
        </div>

        <!-- Componente Livewire de Agendamento -->
        <div class="max-w-4xl mx-auto">
            <livewire:publico.agendamento-hibrido />
        </div>

        <!-- Benefícios do agendamento online -->
        <div class="max-w-4xl mx-auto mt-12">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold text-blue-800 mb-4 text-center">
                    Por que agendar online?
                </h3>
                <div class="grid md:grid-cols-3 gap-6 text-center">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-clock text-3xl text-blue-600 mb-2"></i>
                        <h4 class="font-semibold mb-1">Economia de Tempo</h4>
                        <p class="text-sm text-gray-600">Agende em minutos, sem precisar ligar</p>
                    </div>
                    <div class="flex flex-col items-center">
                        <i class="fas fa-calendar-check text-3xl text-blue-600 mb-2"></i>
                        <h4 class="font-semibold mb-1">Disponibilidade Real</h4>
                        <p class="text-sm text-gray-600">Veja horários disponíveis em tempo real</p>
                    </div>
                    <div class="flex flex-col items-center">
                        <i class="fas fa-mobile-alt text-3xl text-blue-600 mb-2"></i>
                        <h4 class="font-semibold mb-1">Confirmação Imediata</h4>
                        <p class="text-sm text-gray-600">Receba confirmação por SMS</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection