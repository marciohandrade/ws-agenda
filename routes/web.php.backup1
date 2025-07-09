<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

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
        case 'admin':
        case 'colaborador':
            return redirect('/painel/agendamentos');
            
        case 'usuario':
            return redirect('/meus-agendamentos');
            
        default:
            return redirect('/login');
    }
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| ✅ FALLBACK
|--------------------------------------------------------------------------
*/

Route::fallback(function () {
    return response()->json(['error' => 'Route not found'], 404);
});