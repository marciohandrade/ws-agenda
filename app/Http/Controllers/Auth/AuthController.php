<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * ✅ VERSÃO ULTRA BÁSICA - SEM NADA QUE POSSA DAR ERRO
     */
    public function showLoginForm()
    {
        try {
            // Verificar se a view existe
            if (!view()->exists('auth.login')) {
                return response('View auth.login não encontrada', 404);
            }
            
            return view('auth.login');
            
        } catch (\Exception $e) {
            return response('Erro no showLoginForm: ' . $e->getMessage(), 500);
        }
    }

    /**
     * ✅ LOGIN ULTRA BÁSICO
     */
    public function login(Request $request)
    {
        try {
            // Validação mínima
            if (!$request->email || !$request->password) {
                return back()->with('error', 'Email e senha são obrigatórios');
            }

            // Tentativa de login
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                //return redirect('/meus-agendamentos')->with('success', 'Login realizado!');
                return redirect()->route('dashboard')->with('success', 'Login realizado!');
            }

            return back()->with('error', 'Credenciais inválidas');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Erro no login: ' . $e->getMessage());
        }
    }

    /**
     * ✅ LOGOUT ULTRA BÁSICO
     */
    public function logout(Request $request)
    {
        try {
            Auth::logout();
            return redirect('/login')->with('success', 'Logout realizado');
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Erro no logout');
        }
    }

    /**
     * ✅ PERFIL ULTRA BÁSICO
     */
    public function showProfile()
    {
        try {
            if (!Auth::check()) {
                return redirect('/login');
            }

            $user = Auth::user();
            
            // Verificar se a view existe
            if (!view()->exists('auth.profile')) {
                return response('View auth.profile não encontrada', 404);
            }
            
            return view('auth.profile', compact('user'));
            
        } catch (\Exception $e) {
            return response('Erro no perfil: ' . $e->getMessage(), 500);
        }
    }

    /**
     * ✅ ATUALIZAR PERFIL ULTRA BÁSICO
     */
    public function updateProfile(Request $request)
    {
        try {
            if (!Auth::check()) {
                return redirect('/login');
            }

            $user = Auth::user();
            $telefone = preg_replace('/\D/', '', $request->telefone);
            
            // Atualização básica sem validação complexa
            if ($request->name) {
                $user->name = $request->name;
            }
            if ($request->email) {
                $user->email = $request->email;
            }
            if ($request->telefone) {
                $user->telefone =  $telefone;
            }
            
            $user->save();
            
            return back()->with('success', 'Perfil atualizado!');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao atualizar: ' . $e->getMessage());
        }
    }

    /**
     * ✅ FORMATAÇÃO SIMPLES
     */
    public static function formatPhone($phone)
    {
        return $phone ?: '';
    }
}