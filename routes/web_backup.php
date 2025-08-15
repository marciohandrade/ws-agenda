<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Livewire\Painel\Agendamentos;
use App\Livewire\Painel\ClienteCrud;
use App\Livewire\Painel\Servicos;
use App\Livewire\Painel\ConfiguracoesAgendamento;
use App\Livewire\Painel\GerenciadorUsuarios;

/*
|--------------------------------------------------------------------------
| 🆘 ROTAS DE EMERGÊNCIA + DIAGNÓSTICO
|--------------------------------------------------------------------------
*/

// ✅ ROTA DE TESTE BÁSICO
Route::get('/test', function () {
    return 'Sistema funcionando! Data/Hora: ' . now();
});

// 🔍 ROTA DE DIAGNÓSTICO DETALHADO
Route::get('/diagnostic', function () {
    $output = '<h1>Diagnóstico do Sistema</h1>';
    
    try {
        // Teste Laravel
        $output .= '<p>✅ Laravel funcionando</p>';
        
        // Teste Banco
        $userCount = DB::table('users')->count();
        $output .= "<p>✅ Banco conectado - {$userCount} usuários</p>";
        
        // Teste View
        $loginViewExists = view()->exists('auth.login');
        $output .= '<p>' . ($loginViewExists ? '✅' : '❌') . ' View auth.login ' . ($loginViewExists ? 'existe' : 'NÃO EXISTE') . '</p>';
        
        // Teste Controller
        $controllerExists = class_exists('App\Http\Controllers\Auth\AuthController');
        $output .= '<p>' . ($controllerExists ? '✅' : '❌') . ' AuthController ' . ($controllerExists ? 'existe' : 'NÃO EXISTE') . '</p>';
        
        // Teste Auth
        $output .= '<p>✅ Auth provider: ' . config('auth.defaults.provider') . '</p>';
        
        // Teste Model
        $userModel = app()->make('App\Models\User');
        $output .= '<p>✅ Model User carregado</p>';
        
        // Info do sistema
        $output .= '<hr>';
        $output .= '<p><strong>PHP:</strong> ' . PHP_VERSION . '</p>';
        $output .= '<p><strong>Laravel:</strong> ' . app()->version() . '</p>';
        $output .= '<p><strong>Memory:</strong> ' . memory_get_usage(true) . ' bytes</p>';
        
    } catch (\Exception $e) {
        $output .= '<p>❌ ERRO: ' . $e->getMessage() . '</p>';
        $output .= '<p>Arquivo: ' . $e->getFile() . ':' . $e->getLine() . '</p>';
    }
    
    return $output;
});

// 🔍 TESTE DE LOGIN DIRETO (SEM CONTROLLER)
Route::get('/login-test', function () {
    return '
    <!DOCTYPE html>
    <html>
    <head><title>Login Teste</title></head>
    <body>
        <h1>Teste de Login Direto</h1>
        <form method="POST" action="/login-process">
            '.csrf_field().'
            <p>Email: <input type="email" name="email" value="ana.teste@clinica.local" required></p>
            <p>Senha: <input type="password" name="password" value="123456" required></p>
            <p><button type="submit">Login</button></p>
        </form>
        <p><a href="/diagnostic">Ver Diagnóstico</a></p>
    </body>
    </html>';
});

// 🔍 PROCESSAR LOGIN DIRETO
Route::post('/login-process', function (Illuminate\Http\Request $request) {
    try {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return 'LOGIN OK! Usuário: ' . auth()->user()->name;
        } else {
            return 'LOGIN FALHOU - credenciais incorretas';
        }
    } catch (\Exception $e) {
        return 'ERRO NO LOGIN: ' . $e->getMessage();
    }
});

/*
|--------------------------------------------------------------------------
| ✅ ROTAS ORIGINAIS
|--------------------------------------------------------------------------
*/

// ✅ ROTA HOME
Route::get('/', function () {
    return view('index');
})->name('home');

// ✅ AGENDAMENTO PÚBLICO
Route::get('/agendar', function () {
    return view('agendamento');
})->name('agendar');

// 🚀 NOVA: AGENDAMENTO PÚBLICO OTIMIZADO (FUTURA MIGRAÇÃO)
// Route::get('/agendar-servico', App\Livewire\Publico\AgendamentoPublico::class)->name('agendar.publico');

/*
|--------------------------------------------------------------------------
| ✅ AUTENTICAÇÃO
|--------------------------------------------------------------------------
*/

// Login - COM TRATAMENTO DE ERRO
Route::get('/login', function () {
    return view('auth.login');
})->name('login');


Route::post('/login', function (Illuminate\Http\Request $request) {
    try {
        return app(AuthController::class)->login($request);
    } catch (\Exception $e) {
        return back()->with('error', 'Erro no login: ' . $e->getMessage());
    }
});

Route::get('/logout', function () {
    if (auth()->check()) {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/login')->with('success', 'Logout realizado com sucesso!');
    }
    return redirect('/login');
})->name('logout.get');


// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| ✅ ÁREA DO USUÁRIO
|--------------------------------------------------------------------------
*/

// Perfil
Route::get('/perfil', function () {
    try {
        if (!auth()->check()) {
            return redirect('/login');
        }
        return app(AuthController::class)->showProfile();
    } catch (\Exception $e) {
        return 'ERRO NO PERFIL: ' . $e->getMessage();
    }
})->name('perfil');

Route::patch('/perfil', [AuthController::class, 'updateProfile'])->name('user.profile.update');

// Agendamentos
Route::get('/meus-agendamentos', function () {
    if (!auth()->check()) {
        return redirect('/login');
    }
    
    try {
        if (!view()->exists('usuario.meus-agendamentos-lista')) {
            return 'View meus-agendamentos-lista não encontrada';
        }
        return view('usuario.meus-agendamentos-lista');
    } catch (\Exception $e) {
        return 'ERRO NOS AGENDAMENTOS: ' . $e->getMessage();
    }
})->name('meus-agendamentos');

// 🚀 NOVA: AGENDAMENTOS DO CLIENTE OTIMIZADOS (FUTURA MIGRAÇÃO)
// Route::get('/meus-agendamentos', App\Livewire\Cliente\MeusAgendamentos::class)->name('cliente.agendamentos');
// Route::get('/novo-agendamento', App\Livewire\Cliente\NovoAgendamento::class)->name('cliente.agendar');

Route::post('/agendamento/cancelar/{id}', function($id) {
    if (!auth()->check()) {
        return redirect('/login');
    }
    
    try {
        // Buscar o agendamento
        $agendamento = \DB::table('agendamentos')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->first();
        
        if (!$agendamento) {
            return back()->with('error', 'Agendamento não encontrado.');
        }
        
        // Verificar se pode cancelar
        $dataAgendamento = \Carbon\Carbon::parse($agendamento->data_agendamento);
        $podeCarcelar = in_array($agendamento->status, ['pendente', 'confirmado']) && 
                       $dataAgendamento->isFuture();
        
        if (!$podeCarcelar) {
            return back()->with('error', 'Este agendamento não pode ser cancelado.');
        }
        
        // Cancelar
        \DB::table('agendamentos')
            ->where('id', $id)
            ->update([
                'status' => 'cancelado',
                'updated_at' => now()
            ]);
        
        return back()->with('success', 'Agendamento cancelado com sucesso!');
        
    } catch (\Exception $e) {
        return back()->with('error', 'Erro ao cancelar agendamento.');
    }
})->name('agendamento.cancelar');

// Detalhes do agendamento
Route::get('/agendamento/detalhes/{id}', function($id) {
    if (!auth()->check()) {
        return redirect('/login');
    }
    
    try {
        $agendamento = \DB::table('agendamentos as a')
            ->leftJoin('servicos as s', 'a.servico_id', '=', 's.id')
            ->where('a.id', $id)
            ->where('a.user_id', auth()->id())
            ->select([
                'a.*',
                's.nome as servico_nome',
                's.preco as servico_preco',
                's.duracao_minutos as servico_duracao'
            ])
            ->first();
            
        if (!$agendamento) {
            return back()->with('error', 'Agendamento não encontrado.');
        }
        
        return response()->json($agendamento);
        
    } catch (\Exception $e) {
        return back()->with('error', 'Erro ao buscar detalhes.');
    }
})->name('agendamento.detalhes');

/*
|--------------------------------------------------------------------------
| ✅ DASHBOARD
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    if (!auth()->check()) {
        return redirect('/login');
    }
    
    $user = auth()->user();
    
    switch ($user->tipo_usuario) {
        case 'super_admin':
            return redirect('/painel/usuarios'); // ✅ SUPER ADMIN → USUÁRIOS
            
        case 'admin':
        case 'colaborador':
            return redirect('/painel/agendamentos'); // ✅ ADMIN/COLABORADOR → AGENDAMENTOS
            
        case 'usuario':
            return redirect('/meus-agendamentos');
            
        default:
            return redirect('/login');
    }
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| ✅ PAINEL ADMINISTRATIVO
|--------------------------------------------------------------------------
*/

// Painel - Rotas administrativas
Route::middleware(['auth', 'check.role:super_admin,admin,colaborador'])->prefix('painel')->group(function () {
    
    // 🎯 AGENDAMENTOS - ARQUITETURA SEPARADA MOBILE-FIRST
    Route::prefix('agendamentos')->name('agendamentos.')->group(function () {
        
        // Listagem principal (com filtros e ações rápidas)
        Route::get('/', App\Livewire\Painel\AgendamentosLista::class)->name('index');
        
        // Formulário de novo agendamento
        Route::get('/novo', App\Livewire\Painel\AgendamentosForm::class)->name('novo');
        
        // Formulário de edição
        Route::get('/{agendamento}/editar', App\Livewire\Painel\AgendamentosForm::class)->name('editar');
            
        // 🚀 AÇÕES RÁPIDAS AJAX (para performance mobile)
        Route::post('/{agendamento}/status', [App\Http\Controllers\AgendamentoController::class, 'alterarStatus'])
            ->name('alterar-status');
            
        Route::delete('/{agendamento}', [App\Http\Controllers\AgendamentoController::class, 'excluir'])
            ->name('excluir');
            
        Route::get('/horarios-disponiveis', [App\Http\Controllers\AgendamentoController::class, 'horariosDisponiveis'])
            ->name('horarios-disponiveis');
            
        Route::get('/estatisticas', [App\Http\Controllers\AgendamentoController::class, 'estatisticas'])
            ->name('estatisticas');
    });
    
    // 🔄 ROTA ANTIGA (COMPATIBILIDADE TEMPORÁRIA)
    // Route::get('/agendamentos', Agendamentos::class)->name('agendamentos.old');
    
    // ✅ OUTRAS ROTAS DO PAINEL
    Route::get('/clientes', ClienteCrud::class)->name('clientes.index');
    Route::get('/servicos', Servicos::class)->name('servicos.index');
    Route::get('/configuracoes-agendamento', ConfiguracoesAgendamento::class)->name('configuracoes-agendamento.index');
    
    // Rota de usuários - apenas para admin e super_admin
    Route::middleware(['check.role:super_admin,admin'])->group(function () {
        Route::get('/usuarios', GerenciadorUsuarios::class)->name('usuarios.index');
    });
});

/*
|--------------------------------------------------------------------------
| ✅ FALLBACK
|--------------------------------------------------------------------------
*/

Route::fallback(function () {
    return response()->json(['error' => 'Route not found'], 404);
});