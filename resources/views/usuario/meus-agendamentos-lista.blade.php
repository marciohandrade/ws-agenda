@extends('layouts.clinica')

@section('title', 'Meus Agendamentos')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- ✅ HEADER SIMPLES --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-900">
                Meus Agendamentos
            </h1>
            <p class="text-gray-600 mt-1">
                Usuário: <strong>{{ Auth::user()->name ?? 'Não logado' }}</strong>
            </p>
        </div>

        {{-- 🔍 DIAGNÓSTICO CORRIGIDO (SEM @try/@catch) --}}
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <h3 class="font-medium text-green-800 mb-2">✅ Diagnóstico (Corrigido):</h3>
            <div class="text-sm text-green-700 space-y-1">
                @php
                    $userId = auth()->id();
                    $userName = auth()->user()->name;
                    $agendamentosCount = \DB::table('agendamentos')->where('user_id', $userId)->count();
                    $componentExists = class_exists('App\Livewire\Usuario\MeusAgendamentos');
                    $viewExists = view()->exists('livewire.usuario.meus-agendamentos');
                @endphp
                
                <p>✅ User ID: {{ $userId }}</p>
                <p>✅ User Name: {{ $userName }}</p>
                <p>✅ Agendamentos no banco: {{ $agendamentosCount }}</p>
                <p>✅ Livewire component existe: {{ $componentExists ? 'SIM' : 'NÃO' }}</p>
                <p>✅ View do component existe: {{ $viewExists ? 'SIM' : 'NÃO' }}</p>
            </div>
        </div>

        {{-- ✅ ESTATÍSTICAS CORRIGIDAS (SEM @try/@catch) --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            
            @php
                $totalAgendamentos = \DB::table('agendamentos')->where('user_id', $userId)->where('ativo', 1)->count();
                $pendentes = \DB::table('agendamentos')->where('user_id', $userId)->where('status', 'pendente')->where('ativo', 1)->count();
                $confirmados = \DB::table('agendamentos')->where('user_id', $userId)->where('status', 'confirmado')->where('ativo', 1)->count();
                $proximo = \DB::table('agendamentos')->where('user_id', $userId)->where('data_agendamento', '>=', now()->toDateString())->whereIn('status', ['pendente', 'confirmado'])->where('ativo', 1)->orderBy('data_agendamento')->first();
            @endphp

            {{-- TOTAL --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="p-2 rounded-md bg-blue-100">
                        <span class="text-blue-600 text-xl">📅</span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">Total</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $totalAgendamentos }}</p>
                    </div>
                </div>
            </div>

            {{-- PENDENTES --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="p-2 rounded-md bg-yellow-100">
                        <span class="text-yellow-600 text-xl">⏰</span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">Pendentes</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $pendentes }}</p>
                    </div>
                </div>
            </div>

            {{-- CONFIRMADOS --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="p-2 rounded-md bg-green-100">
                        <span class="text-green-600 text-xl">✅</span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">Confirmados</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $confirmados }}</p>
                    </div>
                </div>
            </div>

            {{-- PRÓXIMO --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="p-2 rounded-md bg-purple-100">
                        <span class="text-purple-600 text-xl">⭐</span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">Próximo</p>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $proximo ? \Carbon\Carbon::parse($proximo->data_agendamento)->format('d/m') : 'Nenhum' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 🧪 TESTE DO COMPONENTE LIVEWIRE CORRIGIDO --}}
        <!-- <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">🧪 Componente Livewire:</h3>
            
            @if($componentExists && $viewExists)
                {{-- ✅ CARREGAR O COMPONENTE DIRETAMENTE (SEM @try/@catch) --}}
                @livewire('usuario.meus-agendamentos')
            @else
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                    <h4 class="font-medium text-orange-800 mb-2">⚠️ Componente Livewire não disponível:</h4>
                    <p class="text-orange-700 text-sm">Component: {{ $componentExists ? 'OK' : 'Missing' }} | View: {{ $viewExists ? 'OK' : 'Missing' }}</p>
                    <p class="text-orange-600 text-xs mt-2">Usando fallback da listagem direta abaixo.</p>
                </div>
            @endif
        </div> -->

        {{-- ✅ FALLBACK - LISTAGEM CORRIGIDA --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">📋 Listagem Direta:</h3>
            
            @php
                // ✅ CONSULTA CORRIGIDA (campo duracao_minutos + sem @try/@catch)
                $agendamentos = \DB::table('agendamentos as a')
                    ->leftJoin('servicos as s', 'a.servico_id', '=', 's.id')
                    ->where('a.user_id', $userId)
                    ->where('a.ativo', 1)
                    ->select([
                        'a.id',
                        'a.data_agendamento',
                        'a.horario_agendamento',
                        'a.observacoes',
                        'a.status',
                        's.nome as servico_nome',
                        's.preco as servico_preco',
                        's.duracao_minutos as servico_duracao'  // ✅ CAMPO CORRIGIDO!
                    ])
                    ->orderBy('a.data_agendamento', 'desc')
                    ->limit(5)
                    ->get();
            @endphp

            @if($agendamentos->count() > 0)
                <div class="space-y-3">
                    @foreach($agendamentos as $agendamento)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ $agendamento->servico_nome ?? 'Serviço' }}</h4>
                                    <p class="text-sm text-gray-600">
                                        <span class="mr-2">📅</span>
                                        {{ \Carbon\Carbon::parse($agendamento->data_agendamento)->format('d/m/Y') }} às 
                                        <span class="mr-1">🕒</span>
                                        {{ \Carbon\Carbon::parse($agendamento->horario_agendamento)->format('H:i') }}
                                        @if($agendamento->servico_duracao)
                                            <span class="ml-2 text-gray-500">({{ $agendamento->servico_duracao }} min)</span>
                                        @endif
                                    </p>
                                    @if($agendamento->observacoes)
                                        <p class="text-xs text-gray-500 mt-1">
                                            <span class="mr-1">💬</span>{{ $agendamento->observacoes }}
                                        </p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    @if($agendamento->servico_preco)
                                        <p class="text-sm font-medium text-gray-900 mb-1">
                                            R$ {{ number_format($agendamento->servico_preco, 2, ',', '.') }}
                                        </p>
                                    @endif
                                    <span class="px-2 py-1 text-xs rounded-full
                                        {{ $agendamento->status === 'pendente' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $agendamento->status === 'confirmado' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $agendamento->status === 'cancelado' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $agendamento->status === 'concluido' ? 'bg-blue-100 text-blue-800' : '' }}">
                                        {{ ucfirst($agendamento->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <div class="text-6xl text-gray-400 mb-4">📅</div>
                    <h3 class="text-lg font-medium text-gray-600 mb-2">Nenhum agendamento encontrado</h3>
                    <p class="text-gray-500 mb-4">Execute o seeder para criar dados de teste</p>
                    <a href="/agendar" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <span class="mr-2">➕</span>Fazer Primeiro Agendamento
                    </a>
                </div>
            @endif
        </div>

        {{-- ✅ AÇÕES --}}
        <div class="mt-6 text-center space-x-4">
            <a href="/perfil" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                👤 Meu Perfil
            </a>
            <a href="/agendar" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                📅 Novo Agendamento
            </a>
        </div>

        {{-- 🎯 STATUS CORREÇÃO --}}
        <div class="mt-6 text-center">
            <div class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-lg">
                <span class="mr-2">✅</span>
                <span class="text-sm font-medium">Versão corrigida: sem @try/@catch + campo duracao_minutos!</span>
            </div>
        </div>
    </div>
</div>
@endsection