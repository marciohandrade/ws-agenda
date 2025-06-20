<?php

namespace App\Livewire\Publico;

use App\Models\User;
use App\Models\Cliente;
use App\Models\Agendamento;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class AgendamentoHibrido extends Component
{
    // ETAPAS DO FLUXO
    public $etapaAtual = 1; // 1: Dados do agendamento, 2: Login/Cadastro, 3: Sucesso
    
    // DADOS DO AGENDAMENTO
    public $especialidade = '';
    public $medico = '';
    public $dataAgendamento = '';
    public $horarioAgendamento = '';
    public $observacoes = '';
    
    // DADOS DO USUÁRIO (para login/cadastro)
    public $tipoLogin = ''; // 'login' ou 'cadastro'
    public $email = '';
    public $senha = '';
    public $nome = '';
    public $telefone = '';
    public $senhaConfirmacao = '';
    
    // ESTADOS
    public $carregando = false;
    public $mensagemErro = '';
    public $mensagemSucesso = '';
    
    // DADOS FICTÍCIOS (substituir por dados reais)
    public $especialidades = [
        'clinica-geral' => 'Clínica Geral',
        'pediatria' => 'Pediatria', 
        'ginecologia' => 'Ginecologia'
    ];
    
    public $medicos = [
        'clinica-geral' => ['Dra. Juliana Souza'],
        'pediatria' => ['Dra. Carla Mendes'],
        'ginecologia' => ['Dr. Marcos Lima']
    ];
    
    public $horariosDisponiveis = [
        '08:00', '08:30', '09:00', '09:30', '10:00', '10:30',
        '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'
    ];

    /**
     * Ir para próxima etapa
     */
    public function proximaEtapa()
    {
        if ($this->etapaAtual == 1) {
            // Validar dados do agendamento
            $this->validate([
                'especialidade' => 'required',
                'medico' => 'required',
                'dataAgendamento' => 'required|date|after:today',
                'horarioAgendamento' => 'required',
            ], [
                'especialidade.required' => 'Selecione uma especialidade',
                'medico.required' => 'Selecione um médico',
                'dataAgendamento.required' => 'Selecione uma data',
                'dataAgendamento.after' => 'A data deve ser futura',
                'horarioAgendamento.required' => 'Selecione um horário',
            ]);
            
            $this->etapaAtual = 2;
        }
    }
    
    /**
     * Voltar etapa anterior
     */
    public function etapaAnterior()
    {
        if ($this->etapaAtual > 1) {
            $this->etapaAtual--;
        }
    }
    
    /**
     * Definir tipo de login
     */
    public function definirTipoLogin($tipo)
    {
        $this->tipoLogin = $tipo;
        $this->mensagemErro = '';
    }
    
    /**
     * Fazer login
     */
    public function fazerLogin()
    {
        $this->validate([
            'email' => 'required|email',
            'senha' => 'required'
        ], [
            'email.required' => 'Digite seu e-mail',
            'email.email' => 'E-mail inválido',
            'senha.required' => 'Digite sua senha'
        ]);
        
        if (auth()->attempt(['email' => $this->email, 'password' => $this->senha])) {
            $this->finalizarAgendamento();
        } else {
            $this->addError('senha', 'E-mail ou senha incorretos');
        }
    }
    
    /**
     * Fazer cadastro e agendamento
     */
    public function fazerCadastro()
    {
        $this->carregando = true;
        $this->mensagemErro = '';
        
        try {
            // Validar dados
            $this->validate([
                'nome' => 'required|string|min:3|max:255',
                'email' => 'required|email|unique:users,email',
                'telefone' => 'required|string|unique:users,telefone',
                'senha' => ['required', Password::min(6)->letters()->numbers()],
                'senhaConfirmacao' => 'required|same:senha'
            ], [
                'nome.required' => 'Digite seu nome',
                'email.unique' => 'Este e-mail já está cadastrado',
                'telefone.unique' => 'Este telefone já está cadastrado',
                'senhaConfirmacao.same' => 'As senhas não coincidem'
            ]);
            
            DB::transaction(function () {
                // Criar usuário
                $user = User::create([
                    'name' => $this->nome,
                    'email' => $this->email,
                    'telefone' => preg_replace('/\D/', '', $this->telefone),
                    'password' => Hash::make($this->senha),
                    'tipo_usuario' => 'usuario',
                ]);
                
                // Criar cliente
                Cliente::create([
                    'user_id' => $user->id,
                    'nome' => $this->nome,
                    'email' => $this->email,
                    'telefone' => preg_replace('/\D/', '', $this->telefone),
                ]);
                
                // Fazer login automático
                auth()->login($user);
            });
            
            $this->finalizarAgendamento();
            
        } catch (\Exception $e) {
            $this->mensagemErro = 'Erro ao criar conta: ' . $e->getMessage();
        }
        
        $this->carregando = false;
    }
    
    /**
     * Finalizar agendamento
     */
    private function finalizarAgendamento()
    {
        try {
            // Buscar cliente do usuário logado
            $cliente = auth()->user()->cliente;
            
            if (!$cliente) {
                // Se não existir cliente, criar
                $cliente = Cliente::create([
                    'user_id' => auth()->id(),
                    'nome' => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'telefone' => auth()->user()->telefone,
                ]);
            }
            
            // Criar agendamento
            Agendamento::create([
                'cliente_id' => $cliente->id,
                'user_id' => auth()->id(),
                'servico_id' => 1, // Valor padrão (ajustar conforme necessário)
                'data_agendamento' => $this->dataAgendamento,
                'horario_agendamento' => $this->horarioAgendamento,
                'observacoes' => $this->observacoes . "\nEspecialidade: {$this->especialidade}\nMédico: {$this->medico}",
                'status' => 'pendente',
                'origem' => 'publico'
            ]);
            
            $this->etapaAtual = 3;
            $this->mensagemSucesso = 'Agendamento realizado com sucesso! Você receberá uma confirmação em breve.';
            
        } catch (\Exception $e) {
            $this->mensagemErro = 'Erro ao finalizar agendamento: ' . $e->getMessage();
        }
    }
    
    /**
     * Atualizar médicos quando especialidade muda
     */
    public function updatedEspecialidade()
    {
        $this->medico = '';
    }
    
    /**
     * Render do componente
     */
    public function render()
    {
        return view('livewire.publico.agendamento-hibrido');
    }
}