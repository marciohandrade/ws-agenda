<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Servico;
use Carbon\Carbon;

class AgendamentoPublico extends Component
{
    // Dados do cliente
    public $nome = '';
    public $email = '';
    public $telefone = '';
    public $data_nascimento = '';
    public $genero = '';
    public $cpf = '';
    public $cep = '';
    public $endereco = '';
    public $numero = '';
    public $complemento = '';

    // Dados do agendamento
    public $servico_id = '';
    public $data_agendamento = '';
    public $horario_agendamento = '';
    public $observacoes = '';

    // Controle
    public $etapa = 1; // 1: Dados pessoais, 2: Agendamento, 3: Confirmação
    public $servicos;
    public $horariosDisponiveis = [];
    public $agendamentoCriado = false;

    protected function rules()
    {
        $rules = [];
        
        if ($this->etapa == 1) {
            $rules = [
                'nome' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'telefone' => 'required|string|max:20',
                'data_nascimento' => 'nullable|date|before:today',
                'genero' => 'nullable|in:masculino,feminino,outro',
                'cpf' => 'nullable|string|max:14',
                'cep' => 'nullable|string|max:9',
                'endereco' => 'nullable|string|max:255',
                'numero' => 'nullable|string|max:10',
                'complemento' => 'nullable|string|max:100'
            ];
        } elseif ($this->etapa == 2) {
            $rules = [
                'servico_id' => 'required|exists:servicos,id',
                'data_agendamento' => 'required|date|after_or_equal:today',
                'horario_agendamento' => 'required',
                'observacoes' => 'nullable|string|max:1000'
            ];
        }
        
        return $rules;
    }

    protected $messages = [
        'nome.required' => 'O nome é obrigatório.',
        'email.required' => 'O e-mail é obrigatório.',
        'email.email' => 'Digite um e-mail válido.',
        'telefone.required' => 'O telefone é obrigatório.',
        'data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje.',
        'servico_id.required' => 'Selecione um serviço.',
        'data_agendamento.required' => 'A data é obrigatória.',
        'data_agendamento.after_or_equal' => 'A data não pode ser anterior a hoje.',
        'horario_agendamento.required' => 'Selecione um horário.'
    ];

    public function mount()
    {
        $this->servicos = Servico::where('ativo', true)->orderBy('nome')->get();
        $this->gerarHorariosDisponiveis();
    }

    public function render()
    {
        return view('livewire.agendamento-publico');
    }

    public function proximaEtapa()
    {
        $this->validate();
        
        if ($this->etapa < 3) {
            $this->etapa++;
        }
    }

    public function etapaAnterior()
    {
        if ($this->etapa > 1) {
            $this->etapa--;
        }
    }

    public function finalizarAgendamento()
    {
        $this->validate();

        // Verificar conflito de horário
        if ($this->verificarConflito()) {
            $this->addError('horario_agendamento', 'Este horário não está mais disponível. Selecione outro horário.');
            return;
        }

        // Verificar se o cliente já existe
        $cliente = Cliente::where('email', $this->email)->first();
        
        if (!$cliente) {
            // Criar novo cliente
            $cliente = Cliente::create([
                'nome' => $this->nome,
                'email' => $this->email,
                'telefone' => $this->telefone,
                'data_nascimento' => $this->data_nascimento,
                'genero' => $this->genero,
                'cpf' => $this->cpf,
                'cep' => $this->cep,
                'endereco' => $this->endereco,
                'numero' => $this->numero,
                'complemento' => $this->complemento
            ]);
            $clienteAutoCadastrado = true;
        } else {
            $clienteAutoCadastrado = false;
        }

        // Criar agendamento
        Agendamento::create([
            'cliente_id' => $cliente->id,
            'servico_id' => $this->servico_id,
            'data_agendamento' => $this->data_agendamento,
            'horario_agendamento' => $this->horario_agendamento,
            'status' => 'pendente',
            'observacoes' => $this->observacoes,
            'cliente_cadastrado_automaticamente' => $clienteAutoCadastrado
        ]);

        $this->agendamentoCriado = true;
        $this->etapa = 3;
    }

    public function novoAgendamento()
    {
        $this->reset();
        $this->mount();
    }

    public function updatedDataAgendamento()
    {
        $this->horario_agendamento = '';
        $this->gerarHorariosDisponiveis();
    }

    private function gerarHorariosDisponiveis()
    {
        $horarios = [];
        $inicio = Carbon::createFromTime(8, 0); // 08:00
        $fim = Carbon::createFromTime(18, 0);   // 18:00
        
        while ($inicio <= $fim) {
            $horarios[] = $inicio->format('H:i');
            $inicio->addMinutes(30);
        }
        
        // Se uma data foi selecionada, remover horários já agendados
        if ($this->data_agendamento) {
            // Verificar se é fim de semana
            $dataCarbon = Carbon::parse($this->data_agendamento);
            if ($dataCarbon->isWeekend()) {
                $this->horariosDisponiveis = [];
                return;
            }
            
            $agendados = Agendamento::where('data_agendamento', $this->data_agendamento)
                ->whereNotIn('status', ['cancelado'])
                ->pluck('horario_agendamento')
                ->map(function($horario) {
                    return Carbon::parse($horario)->format('H:i');
                })
                ->toArray();
                
            $horarios = array_diff($horarios, $agendados);
        }
        
        $this->horariosDisponiveis = array_values($horarios);
    }

    private function verificarConflito()
    {
        return Agendamento::where('data_agendamento', $this->data_agendamento)
            ->where('horario_agendamento', $this->horario_agendamento)
            ->whereNotIn('status', ['cancelado'])
            ->exists();
    }
}