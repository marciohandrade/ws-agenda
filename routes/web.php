<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Painel\ClienteCrud;
use App\Livewire\Painel\Servicos;
use App\Livewire\Painel\Agendamentos;
use App\Livewire\Painel\ConfiguracoesAgendamento;
use App\Livewire\Publico\AgendamentoPublico;
use App\Livewire\Painel\DashboardAgendamentos;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
->name('profile');

Route::get('/', function () {
    return view('index');
});
//============================================
// Rota para agendamento online
Route::get('/agendar', function () {
    return view('agendamento');
})->name('agendar');
//============================================

Route::get('/cadastro', function () {
    return view('pages.cadastro-publico');
});

Route::get('/agendar', function () {
    return view('agendamento');
})->name('agendar');

Route::get('/agendamento', AgendamentoPublico::class)
    ->name('agendamento.publico');

// Rota alternativa (para compatibilidade)
Route::get('/agendar', AgendamentoPublico::class)
    ->name('agendar');

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

require __DIR__.'/auth.php';
