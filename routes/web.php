<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Livewire\Painel\ClienteCrud;
use App\Livewire\Painel\Servicos;
use App\Livewire\Painel\Agendamentos;
use App\Livewire\Painel\ConfiguracoesAgendamento;
use App\Livewire\Publico\AgendamentoPublico;
use App\Livewire\Painel\DashboardAgendamentos;
use App\Livewire\Painel\GerenciadorUsuarios;
use App\Livewire\Painel\CriarUsuario;
use App\Livewire\Usuario\MeusAgendamentos;
use App\Livewire\Usuario\NovoAgendamento;

/*
|--------------------------------------------------------------------------
| ✅ ROTAS PÚBLICAS - MANTIDAS ORIGINAIS (FUNCIONANDO)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('index');
})->name('home');

// ✅ AGENDAMENTO HÍBRIDO - MANTIDO FUNCIONANDO
Route::get('/agendar', function () {
    return view('agendamento');
})->name('agendar');

// Registro público desabilitado
Route::get('/register', function () {
    return redirect()->route('login')->with('info', 'O registro público foi desabilitado. Entre em contato com o administrador para criar uma conta.');
})->name('register.disabled');

/*
|--------------------------------------------------------------------------
| ✅ ROTAS DE AUTENTICAÇÃO - MANTIDAS DO BACKUP (FUNCIONAM)
|--------------------------------------------------------------------------
*/

// Para usuários NÃO logados
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    // ✅ RECUPERAÇÃO DE SENHA
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');
    
    Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Para usuários logados
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| ✅ DASHBOARD INTELIGENTE - SIMPLIFICADO E FUNCIONAL
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->get('/dashboard', function () {
    $user = auth()->user();

    // ✅ Log de acesso ao dashboard
    \Log::info('Acesso ao dashboard', [
        'user_id' => $user->id,
        'tipo_usuario' => $user->tipo_usuario,
        'email' => $user->email
    ]);
    
    // ✅ Redirecionamento inteligente baseado no tipo de usuário
    switch ($user->tipo_usuario) {
        case 'super_admin':
        case 'admin':
        case 'colaborador':
            return redirect()->route('painel.agendamentos.index')
                ->with('success', 'Bem-vindo ao painel administrativo, ' . $user->name . '!');
            
        case 'usuario':
            return redirect()->route('meus-agendamentos')
                ->with('success', 'Bem-vindo de volta, ' . $user->name . '!');
            
        default:
            \Log::warning('Tipo de usuário desconhecido tentando acessar dashboard', [
                'user_id' => $user->id,
                'tipo_usuario' => $user->tipo_usuario
            ]);
            
            return redirect()->route('login')
                ->with('error', 'Tipo de usuário não reconhecido. Entre em contato com o suporte.');
    }
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| ✅ ROTAS DE USUÁRIO TIPO 'USUARIO' - MANTIDAS FUNCIONAIS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'check.role:usuario'])->group(function () {
    
    // ✅ PERFIL DO USUÁRIO
    Route::get('/perfil', [AuthController::class, 'showProfile'])->name('perfil');
    Route::patch('/perfil', [AuthController::class, 'updateProfile'])->name('user.profile.update');
    Route::put('/perfil', [AuthController::class, 'updateProfile']); // Fallback para PUT
    
    // ✅ LISTAGEM DE AGENDAMENTOS - ROTA CORRETA
    Route::get('/meus-agendamentos', function () {
        return view('usuario.meus-agendamentos-lista');
    })->name('meus-agendamentos');
    
    // ✅ NOVO AGENDAMENTO para usuários logados
    Route::get('/usuario/agendar', NovoAgendamento::class)->name('usuario.novo-agendamento');
    
    // ✅ AÇÕES DE AGENDAMENTO
    Route::post('/agendamento/cancelar/{id}', [AuthController::class, 'cancelarAgendamento'])->name('agendamento.cancelar');
    Route::get('/agendamento/detalhes/{id}', [AuthController::class, 'detalhesAgendamento'])->name('agendamento.detalhes');
});

/*
|--------------------------------------------------------------------------
| ✅ ROTAS ADMINISTRATIVAS - CORRIGIDAS COM MIDDLEWARE FUNCIONAL
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('painel')->name('painel.')->group(function () {
    
    // ✅ ROTAS EXCLUSIVAS PARA ADMIN E SUPER_ADMIN
    Route::middleware(['check.role:super_admin,admin'])->group(function () {
        Route::get('/usuarios', GerenciadorUsuarios::class)->name('usuarios.index');
        Route::get('/usuarios/criar', CriarUsuario::class)->name('usuarios.criar');
        Route::get('/configuracoes-agendamento', ConfiguracoesAgendamento::class)->name('configuracoes-agendamento.index');
        Route::get('/servicos', Servicos::class)->name('servicos.index');
    });
    
    // ✅ AGENDAMENTOS: Acesso para ADMIN, SUPER_ADMIN E COLABORADOR
    Route::middleware(['check.role:super_admin,admin,colaborador'])->group(function () {        
        Route::get('/agendamentos', Agendamentos::class)->name('agendamentos.index');
       
        /* Route::get('/agendamentos', function () {
            return view('livewire-page', ['component' => App\Livewire\Painel\Agendamentos::class]);
        })->name('agendamentos.index'); */
       
        Route::get('/clientes', ClienteCrud::class)->name('clientes.index');
       
        Route::get('/dashboard', DashboardAgendamentos::class)->name('dashboard');
    });
});

/*
|--------------------------------------------------------------------------
| ✅ ROTAS EXCLUSIVAS PARA SUPER ADMIN - MANTIDAS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'check.role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/sistema', function () {
        return view('admin.sistema');
    })->name('sistema');
    
    Route::get('/logs', function () {
        return view('admin.logs');
    })->name('logs');
    
    Route::get('/backup', function () {
        return view('admin.backup');
    })->name('backup');
});

/*
|--------------------------------------------------------------------------
| ✅ ROTAS DE API - MANTIDAS
|--------------------------------------------------------------------------
*/

Route::prefix('api')->middleware(['auth'])->name('api.')->group(function () {
    Route::get('/agendamentos', function () {
        $user = auth()->user();
        
        if ($user->tipo_usuario === 'usuario') {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }
        
        return response()->json(['error' => 'Unauthorized'], 403);
    })->name('agendamentos');
    
    Route::get('/perfil', function () {
        return response()->json([
            'success' => true,
            'data' => auth()->user()->only(['id', 'name', 'email', 'telefone', 'tipo_usuario'])
        ]);
    })->name('perfil');
});

/*
|--------------------------------------------------------------------------
| ✅ ROTAS DE WEBHOOK - MANTIDAS
|--------------------------------------------------------------------------
*/

Route::prefix('webhook')->name('webhook.')->group(function () {
    Route::post('/whatsapp', function () {
        return response()->json(['status' => 'ok']);
    })->name('whatsapp');
    
    Route::post('/email', function () {
        return response()->json(['status' => 'ok']);
    })->name('email');
});

/*
|--------------------------------------------------------------------------
| ✅ ROTAS DE SAÚDE DO SISTEMA - MANTIDAS
|--------------------------------------------------------------------------
*/

Route::get('/health', function () {
    try {
        \DB::connection()->getPdo();
        
        return response()->json([
            'status' => 'healthy',
            'database' => 'connected',
            'timestamp' => now()->toISOString(),
            'version' => config('app.version', '1.0.0')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'database' => 'disconnected',
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString()
        ], 500);
    }
})->name('health');

Route::get('/version', function () {
    return response()->json([
        'app_name' => config('app.name'),
        'version' => config('app.version', '1.0.0'),
        'environment' => config('app.env'),
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
        'timestamp' => now()->toISOString()
    ]);
})->name('version');

/*
|--------------------------------------------------------------------------
| ✅ FALLBACK - MANTIDO
|--------------------------------------------------------------------------
*/

Route::fallback(function () {
    if (request()->expectsJson()) {
        return response()->json([
            'error' => 'Route not found',
            'message' => 'The requested route does not exist.'
        ], 404);
    }
    
    return view('errors.404');
});

// Rota de debug - TEMPORÁRIA
Route::get('/debug-agendamentos', function () {
    try {
        $component = new App\Livewire\Painel\Agendamentos();
        $result = $component->render();
        return "✅ Component funciona no contexto web! Hora: " . now();
    } catch (Exception $e) {
        return "❌ ERRO WEB: " . $e->getMessage() . "<br>Linha: " . $e->getLine() . "<br>Arquivo: " . $e->getFile();
    }
})->middleware(['auth', 'check.role:super_admin,admin,colaborador']);


// ROTA TEMPORÁRIA PARA TESTE DO LIVEWIRE
Route::get('/teste-livewire', \App\Livewire\Painel\Teste::class)
    ->middleware(['auth', 'check.role:super_admin,admin,colaborador'])
    ->name('teste.livewire');


require __DIR__.'/auth.php';

