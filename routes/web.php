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
use App\Livewire\Painel\AgendamentosLista;
use App\Livewire\Usuario\MeusAgendamentos;
use App\Livewire\Usuario\NovoAgendamento;


/*
|--------------------------------------------------------------------------
| ✅ ROTAS PÚBLICAS - MANTIDAS ORIGINAIS
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('index');
})->name('home');

// Rota para agendamento online (público) - MANTIDA ORIGINAL
Route::get('/agendar', function () {
    return view('agendamento');
})->name('agendar');

// Registro público desabilitado
Route::get('/register', function () {
    return redirect()->route('login')->with('info', 'O registro público foi desabilitado. Entre em contato com o administrador para criar uma conta.');
})->name('register.disabled');

/*
|--------------------------------------------------------------------------
| ✅ ROTAS DE AUTENTICAÇÃO - FASE 1 COMPLETA
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
| ✅ ROTAS DE USUÁRIO TIPO 'USUARIO' - FASE 2 COMPLETA
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'check.role:usuario'])->group(function () {
    
    // ✅ PERFIL DO USUÁRIO - IMPLEMENTADO NA FASE 2
    Route::get('/perfil', [AuthController::class, 'showProfile'])->name('perfil');
    Route::patch('/perfil', [AuthController::class, 'updateProfile'])->name('user.profile.update');
    Route::put('/perfil', [AuthController::class, 'updateProfile']); // Fallback para PUT
    
    // ✅ LISTAGEM DE AGENDAMENTOS - IMPLEMENTADO NA FASE 2
    Route::get('/meus-agendamentos', function () {
        return view('usuario.meus-agendamentos-lista');
    })->name('meus-agendamentos');
    
    // ✅ NOVO AGENDAMENTO (componente Livewire existente) - Rota específica para usuários logados
    Route::get('/usuario/agendar', NovoAgendamento::class)->name('usuario.novo-agendamento');
    
    // ✅ AÇÕES DE AGENDAMENTO
    Route::post('/agendamento/cancelar/{id}', [AuthController::class, 'cancelarAgendamento'])->name('agendamento.cancelar');
    Route::get('/agendamento/detalhes/{id}', [AuthController::class, 'detalhesAgendamento'])->name('agendamento.detalhes');
});

/*
|--------------------------------------------------------------------------
| ✅ DASHBOARD INTELIGENTE - OTIMIZADO
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->get('/dashboard', function () {
    $user = auth()->user();

    // Headers específicos para Edge e cache
    $userAgent = request()->userAgent();
    if (str_contains($userAgent, 'Edge') || 
        str_contains($userAgent, 'Edg/') ||
        str_contains($userAgent, 'Trident')) {
        
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
    
    // ✅ Log de acesso ao dashboard
    \Log::info('Acesso ao dashboard', [
        'user_id' => $user->id,
        'tipo_usuario' => $user->tipo_usuario,
        'email' => $user->email,
        'ip' => request()->ip()
    ]);
    
    // ✅ Redirecionamento inteligente baseado no tipo de usuário
    switch ($user->tipo_usuario) {
        case 'super_admin':
        case 'admin':
        case 'colaborador':
            return redirect()->route('agendamentos.index')
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
| ✅ ROTAS ADMINISTRATIVAS - MANTIDAS ORIGINAIS + MELHORIAS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'check.role:super_admin,admin,colaborador'])->prefix('painel')->name('painel.')->group(function () {
    
    // ✅ ROTAS EXCLUSIVAS PARA ADMIN E SUPER_ADMIN
    Route::middleware(['check.role:super_admin,admin'])->group(function () {
        Route::get('/usuarios', GerenciadorUsuarios::class)->name('usuarios.index');
        Route::get('/usuarios/criar', CriarUsuario::class)->name('usuarios.criar');
        Route::get('/configuracoes-agendamento', ConfiguracoesAgendamento::class)->name('configuracoes-agendamento.index');
        Route::get('/servicos', Servicos::class)->name('servicos.index');
    });
    
    // ✅ AGENDAMENTOS: Acesso para ADMIN, SUPER_ADMIN E COLABORADOR
    //Route::get('/agendamentos', Agendamentos::class)->name('agendamentos.index');
    Route::get('/agendamentos', AgendamentosLista::class)->name('agendamentos.index');
    Route::get('/clientes', ClienteCrud::class)->name('clientes.index');
    
    // ✅ DASHBOARD ADMINISTRATIVO (opcional)
    Route::get('/dashboard', DashboardAgendamentos::class)->name('dashboard');
});

// ✅ ROTAS EXCLUSIVAS PARA SUPER ADMIN
Route::middleware(['auth', 'check.role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    // ✅ FUTURAS FUNCIONALIDADES DE SUPER ADMIN
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
| ✅ ROTAS DE API (FUTURO)
|--------------------------------------------------------------------------
*/

Route::prefix('api')->middleware(['auth'])->name('api.')->group(function () {
    // ✅ API para agendamentos (mobile app futuro)
    Route::get('/agendamentos', function () {
        $user = auth()->user();
        
        if ($user->tipo_usuario === 'usuario') {
            // Retornar agendamentos do usuário
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }
        
        return response()->json(['error' => 'Unauthorized'], 403);
    })->name('agendamentos');
    
    // ✅ API para perfil
    Route::get('/perfil', function () {
        return response()->json([
            'success' => true,
            'data' => auth()->user()->only(['id', 'name', 'email', 'telefone', 'tipo_usuario'])
        ]);
    })->name('perfil');
});

/*
|--------------------------------------------------------------------------
| ✅ ROTAS DE WEBHOOK (FUTURO - INTEGRAÇÕES)
|--------------------------------------------------------------------------
*/

Route::prefix('webhook')->name('webhook.')->group(function () {
    // ✅ Webhook para WhatsApp
    Route::post('/whatsapp', function () {
        return response()->json(['status' => 'ok']);
    })->name('whatsapp');
    
    // ✅ Webhook para email
    Route::post('/email', function () {
        return response()->json(['status' => 'ok']);
    })->name('email');
});

/*
|--------------------------------------------------------------------------
| ✅ ROTAS DE MANUTENÇÃO E SAÚDE DO SISTEMA
|--------------------------------------------------------------------------
*/

Route::get('/health', function () {
    try {
        // ✅ Verificar banco de dados
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
| ✅ FALLBACK E ROTAS DE ERRO
|--------------------------------------------------------------------------
*/

// ✅ Fallback para rotas não encontradas
Route::fallback(function () {
    if (request()->expectsJson()) {
        return response()->json([
            'error' => 'Route not found',
            'message' => 'The requested route does not exist.'
        ], 404);
    }
    
    return view('errors.404');
});

Route::get('/teste-alpine', function () {
    return view('teste-alpine');
})->middleware('auth')->name('teste.alpine');

/*
|--------------------------------------------------------------------------
| ✅ INCLUI ROTAS DE AUTENTICAÇÃO PADRÃO DO LARAVEL (SE NECESSÁRIO)
|--------------------------------------------------------------------------
*/

// Comentado pois estamos usando AuthController customizado
// require __DIR__.'/auth.php';