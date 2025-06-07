<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Painel\ClienteCrud;


Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth'])->group(function () {
    Route::get('/painel/clientes', ClienteCrud::class)->name('clientes.index');
});

require __DIR__.'/auth.php';
