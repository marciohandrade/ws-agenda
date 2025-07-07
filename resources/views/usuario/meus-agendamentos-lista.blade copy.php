@extends('layouts.clinica')

@section('title', 'Meus Agendamentos - Debug')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- ✅ HEADER SIMPLES --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-900">
                Meus Agendamentos - Debug
            </h1>
            <p class="text-gray-600 mt-1">
                Usuário: <strong>{{ Auth::user()->name ?? 'Não logado' }}</strong>
            </p>
        </div>

        {{-- 🔍 DIAGNÓSTICO --}}
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <h3 class="font-medium text-yellow-800 mb-2">🔍 Diagnóstico:</h3>
            <div class="text-sm text-yellow-700 space-y-1">
                @try
                    <p>✅ User ID: {{ auth()->id() }}</p>
                    <p>✅ User Name: {{ auth()->user()->name }}</p>
                    
                    @php
                        $agendamentosCount = DB::table('agendamentos')->where('user_id', auth()->id())->count();
                    @endphp
                    <p>✅ Agendamentos no banco: {{ $agendamentosCount }}</p>
                    
                    <p>✅ Livewire component existe: {{ class_exists('App\Livewire\Usuario\MeusAgendamentos') ? 'SIM' : 'NÃO' }}</p>
                    
                    <p>✅ View do component existe: {{ view()->exists('livewire.usuario.meus-agendamentos') ? 'SIM' : 'NÃO' }}</p>
                    
                @catch (\Exception $e)
                    <p>❌ ERRO: {{ $e->getMessage() }}</p>
                @endtry
            </div>
        </div>

        {{-- ✅ ESTATÍSTICAS SIMPLES (SEM RELATIONSHIP) --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            
            @try
                @php
                    $userId = auth()->id();
                    $totalAgendamentos = DB::table('agendamentos')->where('user_id', $userId)->where('ativo', 1)->count();
                    $pendentes = DB::table('agendamentos')->where('user_id', $userId)->where('status', 'pendente')->where('ativo', 1)->count();
                    $confirmados = DB::table('agendamentos')->where('user_id', $userId)->where('status', 'confirmado')->where('ativo', 1)->count();
                    $proximo = DB::table('agendamentos')->where('user_id', $userId)->where('data_agendamento', '>=', now()->toDateString())->whereIn('status', ['pendente', 'confirmado'])->where('ativo', 1)->orderBy('data_agendamento')->first();
                @endphp

                {{-- TOTAL --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center">
                        <div class="p-2 rounded-md bg-blue-100">
                            <i class="fas fa-calendar text-blue-600"></i>
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
                            <i class="fas fa-clock text-yellow-600"></i>
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
                            <i class="fas fa-check-circle text-green-600"></i>
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
                            <i class="fas fa-star text-purple-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-500">Próximo</p>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $proximo ? \Carbon\Carbon::parse($proximo->data_agendamento)->format('d/m') : 'Nenhum' }}
                            </p>
                        </div>
                    </div>
                </div>

            @catch (\Exception $e)
                <div class="col-span-4 bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-red-800">❌ Erro nas estatísticas: {{ $e->getMessage() }}</p>
                </div>
            @endtry
        </div>

        {{-- 🧪 TESTE DO COMPONENTE LIVEWIRE --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">🧪 Teste do Componente:</h3>
            
            @try
                {{-- ✅ TENTAR CARREGAR O COMPONENTE --}}
                @livewire('usuario.meus-agendamentos')
                
            @catch (\Exception $e)
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <h4 class="font-medium text-red-800 mb-2">❌ Erro no Componente Livewire:</h4>
                    <p class="text-red-700 text-sm">{{ $e->getMessage() }}</p>
                    <p class="text-red-600 text-xs mt-2">Arquivo: {{ $e->getFile() }}:{{ $e->getLine() }}</p>
                </div>
            @endtry
        </div>

        {{-- ✅ FALLBACK - LISTAGEM SIMPLES --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">📋 Listagem Direta (Fallback):</h3>
            
            @try
                @php
                    $agendamentos = DB::table('agendamentos as a')
                        ->leftJoin('servicos as s', 'a.servico_id', '=', 's.id')
                        ->where('a.user_id', auth()->id())
                        ->where('a.ativo', 1)
                        ->select([
                            'a.id',
                            'a.data_agendamento',
                            'a.horario_agendamento',
                            'a.observacoes',
                            'a.status',
                            's.nome as servico_nome',
                            's.preco as servico_preco'
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
                                            {{ \Carbon\Carbon::parse($agendamento->data_agendamento)->format('d/m/Y') }} às 
                                            {{ \Carbon\Carbon::parse($agendamento->horario_agendamento)->format('H:i') }}
                                        </p>
                                        @if($agendamento->observacoes)
                                            <p class="text-xs text-gray-500 mt-1">{{ $agendamento->observacoes }}</p>
                                        @endif
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded-full
                                        {{ $agendamento->status === 'pendente' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $agendamento->status === 'confirmado' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $agendamento->status === 'cancelado' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $agendamento->status === 'concluido' ? 'bg-blue-100 text-blue-800' : '' }}">
                                        {{ ucfirst($agendamento->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-calendar-times text-4xl text-gray-400 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-600 mb-2">Nenhum agendamento encontrado</h3>
                        <p class="text-gray-500 mb-4">Execute o seeder para criar dados de teste</p>
                        <a href="/agendar" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Fazer Primeiro Agendamento
                        </a>
                    </div>
                @endif

            @catch (\Exception $e)
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-red-800">❌ Erro na listagem: {{ $e->getMessage() }}</p>
                </div>
            @endtry
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
    </div>
</div>
@endsection