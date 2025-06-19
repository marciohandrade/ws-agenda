<?php

namespace App\Livewire\Publico;

use App\Models\User;
use App\Models\Cliente;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class Cadastro extends Component
{
    // Propriedades do formulário
    public $nome = '';
    public $email = '';
    public $telefone = '';
    public $senha = '';
    public $senha_confirmacao = '';
    
    // Estados do componente
    public $mostrarFormulario = true;
    public $mostrarSucesso = false;
    public $carregando = false;
    
    // Mensagens
    public $mensagemSucesso = '';
    public $mensagemErro = '';

    /**
     * Regras de validação
     */
    protected function rules()
    {
        return [
            'nome' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'telefone' => ['required', 'string', 'regex:/^(\(\d{2}\)\s?)?\d{4,5}-?\d{4}$/', 'unique:users,telefone'],
            'senha' => ['required', 'string', Password::min(6)->letters()->numbers()],
            'senha_confirmacao' => ['required', 'string', 'same:senha'],
        ];
    }

    /**
     * Mensagens customizadas de validação
     */
    protected function messages()
    {
        return [
            'nome.required' => 'O nome é obrigatório.',
            'nome.min' => 'O nome deve ter pelo menos 3 caracteres.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Digite um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado no sistema.',
            'telefone.required' => 'O telefone é obrigatório.',
            'telefone.regex' => 'Digite um telefone válido (ex: (11) 99999-9999).',
            'telefone.unique' => 'Este telefone já está cadastrado no sistema.',
            'senha.required' => 'A senha é obrigatória.',
            'senha.min' => 'A senha deve ter pelo menos 6 caracteres.',
            'senha_confirmacao.required' => 'Confirme sua senha.',
            'senha_confirmacao.same' => 'As senhas não coincidem.',
        ];
    }

    /**
     * Validação em tempo real
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    /**
     * Formatar telefone automaticamente
     */
    public function updatedTelefone()
    {
        $this->telefone = $this->formatarTelefone($this->telefone);
    }

    /**
     * Processar cadastro
     */
    public function cadastrar()
    {
        $this->carregando = true;
        $this->mensagemErro = '';

        try {
            // Validar dados
            $this->validate();

            // Transação para garantir consistência
            DB::transaction(function () {
                // Criar usuário
                $user = User::create([
                    'name' => $this->nome,
                    'email' => $this->email,
                    'telefone' => $this->limparTelefone($this->telefone),
                    'password' => Hash::make($this->senha),
                    'tipo_usuario' => 'usuario',
                ]);

                // Criar cliente vinculado
                Cliente::create([
                    'user_id' => $user->id,
                    'nome' => $this->nome,
                    'email' => $this->email,
                    'telefone' => $this->limparTelefone($this->telefone),
                ]);

                // Enviar SMS de verificação (implementar depois)
                $this->enviarSmsVerificacao($user);
            });

            // Sucesso
            $this->mostrarSucesso();

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Erros de validação são tratados automaticamente
            throw $e;
            
        } catch (\Illuminate\Database\QueryException $e) {
            // Tratar erros específicos de banco de dados
            if ($e->errorInfo[1] == 1062) { // Duplicate entry
                if (str_contains($e->getMessage(), 'users_email_unique')) {
                    $this->addError('email', 'Este e-mail já está cadastrado no sistema.');
                } elseif (str_contains($e->getMessage(), 'users_telefone_unique')) {
                    $this->addError('telefone', 'Este telefone já está cadastrado no sistema.');
                } elseif (str_contains($e->getMessage(), 'clientes_email_unique')) {
                    $this->addError('email', 'Este e-mail já está cadastrado no sistema.');
                } elseif (str_contains($e->getMessage(), 'clientes_telefone_unique')) {
                    $this->addError('telefone', 'Este telefone já está cadastrado no sistema.');
                } else {
                    $this->mensagemErro = 'Dados já cadastrados no sistema. Verifique e-mail e telefone.';
                }
            } else {
                $this->mensagemErro = 'Erro no banco de dados. Tente novamente.';
                \Log::error('Erro de banco no cadastro: ' . $e->getMessage());
            }
            
        } catch (\Exception $e) {
            $this->mensagemErro = 'Erro interno. Tente novamente em alguns minutos.';
            \Log::error('Erro no cadastro público: ' . $e->getMessage());
        }

        $this->carregando = false;
    }

    /**
     * Mostrar tela de sucesso
     */
    private function mostrarSucesso()
    {
        $this->mostrarFormulario = false;
        $this->mostrarSucesso = true;
        $this->mensagemSucesso = 'Cadastro realizado com sucesso! Verifique seu telefone para ativar a conta.';
    }

    /**
     * Enviar SMS de verificação (placeholder)
     */
    private function enviarSmsVerificacao($user)
    {
        // TODO: Implementar envio de SMS
        // Por enquanto apenas simula
        \Log::info("SMS enviado para: {$user->telefone}");
    }

    /**
     * Formatar telefone para exibição
     */
    private function formatarTelefone($telefone)
    {
        $numeros = preg_replace('/\D/', '', $telefone);
        
        if (strlen($numeros) == 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $numeros);
        } elseif (strlen($numeros) == 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $numeros);
        }
        
        return $telefone;
    }

    /**
     * Limpar telefone para salvar no banco
     */
    private function limparTelefone($telefone)
    {
        return preg_replace('/\D/', '', $telefone);
    }

    /**
     * Voltar ao formulário
     */
    public function voltarFormulario()
    {
        $this->mostrarFormulario = true;
        $this->mostrarSucesso = false;
        $this->reset(['nome', 'email', 'telefone', 'senha', 'senha_confirmacao']);
    }

    /**
     * Ir para login
     */
    public function irParaLogin()
    {
        return redirect()->route('login');
    }

    /**
     * Render do componente
     */
    public function render()
    {
        return view('livewire.publico.cadastro');
    }
}