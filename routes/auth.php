<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    // Mantém apenas login para visitantes
    Volt::route('login', 'pages.auth.login')
        ->name('login');

    // Esqueci senha para visitantes
    Volt::route('esqueci-senha', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('redefinir-senha/{token}', 'pages.auth.reset-password')
        ->name('password.reset');

    // REGISTRO PÚBLICO REMOVIDO
    // Volt::route('register', 'pages.auth.register')->name('register');
});

Route::middleware('auth')->group(function () {
    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');

    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('logout', 'pages.auth.logout')
        ->name('logout');
});

// REGISTRO PROTEGIDO - só para admin e super admin
Route::middleware(['auth', 'check.role:super_admin,admin'])->group(function () {
    Volt::route('admin/registro', 'pages.auth.register')
        ->name('admin.register');
});