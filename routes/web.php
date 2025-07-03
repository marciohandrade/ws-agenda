<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Painel\ClienteCrud;
use App\Livewire\Painel\Servicos;
use App\Livewire\Painel\Agendamentos;
use App\Livewire\Painel\ConfiguracoesAgendamento;
use App\Livewire\Publico\AgendamentoPublico;
use App\Livewire\Painel\DashboardAgendamentos;
use App\Livewire\Painel\GerenciadorUsuarios;
use App\Livewire\Painel\CriarUsuario;

/* Route::view('/', 'welcome'); */

Route::get('/', function () {
    return view('index');
});

Route::get('/register', function () {
    return redirect()->route('login')->with('info', 'O registro público foi desabilitado. Entre em contato com o administrador para criar uma conta.');
})->name('register.disabled');


Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

//============================================
// Rota para agendamento online (público)
Route::get('/agendar', function () {
    return view('agendamento');
})->name('agendar');
//============================================

/* Route::get('/cadastro', function () {
    return view('pages.cadastro-publico');
}); */

//============================================
// ROTAS PROTEGIDAS - PAINEL ADMINISTRATIVO
//============================================

// Rotas para Admin e Colaborador
/* Route::middleware(['auth', 'check.role:super_admin,admin,colaborador'])->prefix('painel')->group(function () {
    Route::get('/clientes', ClienteCrud::class)->name('clientes.index');
    Route::get('/servicos', Servicos::class)->name('servicos.index');
    Route::get('/agendamentos', Agendamentos::class)->name('agendamentos.index');
    Route::get('/configuracoes-agendamento', ConfiguracoesAgendamento::class)->name('configuracoes-agendamento.index');
    Route::get('/usuarios', GerenciadorUsuarios::class)->name('usuarios.index');
    Route::get('/usuarios/criar', CriarUsuario::class)->name('usuarios.criar');
 }); */

 Route::middleware(['auth', 'check.role:super_admin,admin,colaborador'])->prefix('painel')->group(function () {
    
    // Admin tem acesso a TUDO:
    Route::middleware(['check.role:super_admin,admin'])->group(function () {
        Route::get('/usuarios', GerenciadorUsuarios::class)->name('usuarios.index');
        Route::get('/configuracoes-agendamento', ConfiguracoesAgendamento::class)->name('configuracoes-agendamento.index');
        Route::get('/servicos', Servicos::class)->name('servicos.index');
    });
    
    // Agendamentos: Admin, Super Admin E Colaborador podem acessar
    Route::get('/agendamentos', Agendamentos::class)->name('agendamentos.index');
    Route::get('/clientes', ClienteCrud::class)->name('clientes.index');
});

 

// Seguindo o padrão do seu projeto

    /* Route::middleware(['auth', 'check.role:super_admin,admin,colaborador'])->prefix('painel')->group(function () {
        Route::get('/clientes', ClienteCrud::class)->name('clientes.index');
        Route::get('/servicos', Servicos::class)->name('servicos.index');
        Route::get('/agendamentos', \App\Livewire\Painel\Agendamentos::class)->name('agendamentos.index');
        Route::get('/configuracoes-agendamento', ConfiguracoesAgendamento::class)->name('configuracoes-agendamento.index');
        
        // Route::get('/dashboard-agendamentos', DashboardAgendamentos::class);
    }); */

    //Route::get('/configuracoes-agendamento', ConfiguracoesAgendamento::class)->name('configuracoes-agendamento.index');
    
    // Route::get('/dashboard-agendamentos', DashboardAgendamentos::class);


// Rotas exclusivas para SUPER ADMIN
Route::middleware(['auth', 'check.role:super_admin'])->prefix('admin')->group(function () {
    // Gestão de Usuários (criar interface depois)
    // Route::get('/usuarios', [UserController::class, 'index'])->name('admin.usuarios.index');
    // Route::get('/usuarios/criar', [UserController::class, 'create'])->name('admin.usuarios.create');
    // Route::post('/usuarios', [UserController::class, 'store'])->name('admin.usuarios.store');
    // Route::get('/usuarios/{user}/editar', [UserController::class, 'edit'])->name('admin.usuarios.edit');
    // Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('admin.usuarios.update');
    // Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->middleware('protect.super.admin')->name('admin.usuarios.destroy');
    
    // Configurações do Sistema
    // Route::get('/configuracoes', [SystemController::class, 'index'])->name('admin.configuracoes.index');
});

//============================================
// DASHBOARD - REDIRECIONAMENTO INTELIGENTE
//============================================
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
        case 'super_admin':
        case 'admin':
        case 'colaborador':
            return redirect()->route('agendamentos.index');
            
        case 'usuario':
            return redirect()->route('cliente.dashboard');
            
        default:
            abort(403, 'Tipo de usuário não reconhecido.');
    }
})->name('dashboard');

//============================================
// ROTAS DE CLIENTE (para usuários comuns)
//============================================
Route::middleware(['auth', 'check.role:usuario'])->prefix('cliente')->group(function () {
    // Route::get('/dashboard', [ClienteController::class, 'dashboard'])->name('cliente.dashboard');
    // Route::get('/agendamentos', [ClienteController::class, 'agendamentos'])->name('cliente.agendamentos');
    // Route::get('/perfil', [ClienteController::class, 'perfil'])->name('cliente.perfil');
});

require __DIR__.'/auth.php';