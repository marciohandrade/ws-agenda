<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Painel\ClienteCrud;
use App\Livewire\Painel\Servicos;
use App\Livewire\Painel\Agendamentos;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth'])->group(function () {
    Route::get('/painel/clientes', ClienteCrud::class)->name('clientes.index');

    // Gestão de Serviços
   Route::get('/painel/servicos', Servicos::class)->name('servicos.index');
    
    // Gestão de Agendamentos
    Route::get('/agendamentos', Agendamentos::class)->name('agendamentos.index');
});

require __DIR__.'/auth.php';
