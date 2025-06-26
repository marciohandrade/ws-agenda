<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Painel\ClienteCrud;
use App\Livewire\Painel\Servicos;
use App\Livewire\Painel\Agendamentos;
use App\Livewire\Painel\ConfiguracoesAgendamento;
use App\Livewire\Publico\AgendamentoPublico;
use App\Livewire\Painel\DashboardAgendamentos;

/* Route::view('/', 'welcome'); */

Route::get('/', function () {
    return view('index');
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
->name('profile');


//============================================
// Rota para agendamento online
Route::get('/agendar', function () {
    return view('agendamento');
})->name('agendar');
//============================================

/* Route::get('/cadastro', function () {
    return view('pages.cadastro-publico');
}); */

Route::middleware(['auth'])->group(function () {
    Route::get('/painel/clientes', ClienteCrud::class)
    ->name('clientes.index');
    // Gestão de Serviços
    Route::get('/painel/servicos', Servicos::class)
    ->name('servicos.index');    
    // Gestão de Agendamentos
    Route::get('painel/agendamentos', Agendamentos::class)
    ->name('agendamentos.index');
    // configurações e agendamento
     Route::get('/painel/configuracoes-agendamento', ConfiguracoesAgendamento::class)
    ->name('configuracoes-agendamento.index');

    /* Route::get('/painel/dashboard-agendamentos', DashboardAgendamentos::class); */
    
});

// Rota dashboard - redireciona baseado no tipo de usuário
Route::middleware(['auth'])->get('/dashboard', function () {
    $user = auth()->user();

    // 🔧 Headers específicos para Edge
    if (str_contains(request()->userAgent(), 'Edge') || 
        str_contains(request()->userAgent(), 'Edg/') ||
        str_contains(request()->userAgent(), 'Trident')) {
        
        // Força no-cache para Edge
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
    
    switch ($user->tipo_usuario) {
        case 'admin':
        case 'colaborador':
            return redirect()->route('painel.agendamentos.index');
            
        case 'usuario':
            return redirect()->route('cliente.dashboard');
            
        default:
            abort(403, 'Tipo de usuário não reconhecido.');
    }
})->name('dashboard');

require __DIR__.'/auth.php';
