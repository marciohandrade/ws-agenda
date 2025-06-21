<?php

namespace App\Livewire\Publico;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Carbon\Carbon;

class AgendamentoHibrido extends Component
{
    // ETAPAS DO FLUXO
    public $etapaAtual = 1;
    
    // DADOS DO AGENDAMENTO
    public $servico_id = '';
    public $dataAgendamento = '';
    public $horarioAgendamento = '';
    public $observacoes = '';
    
    // DADOS DO USUÁRIO UNIFICADO
    public $tipoLogin = '';
    public $email = '';
    public $senha = '';
    public $nome = '';
    public $telefone = '';
    public $senhaConfirmacao = '';
    
    // ESTADOS
    public $carregando = false;
    public $mensagemErro = '';
    public $mensagemSucesso = '';
    public $agendamentoId = null;
    
    // DADOS
    public $servicos = [];
    
    // CALENDÁRIO
    public $mesAtual;
    public $anoAtual;
    public $dataSelecionada = '';
    public $diasFuncionamento = [];
    public $carregandoCalendario = false;
    
    // HORÁRIOS
    public $horariosDisponiveis = [];
    public $carregandoHorarios = false;
    public $horarioSelecionado = '';

    /**
     * Inicializar componente
     */
    public function mount()
    {
        $this->carregarServicos();
        $this->inicializarCalendario();
    }

    /**
     * Inicializar calendário
     */
    public function inicializarCalendario()
    {
        $hoje = now();
        $this->mesAtual = $hoje->month;
        $this->anoAtual = $hoje->year;
        $this->carregarDiasFuncionamento();
    }

    /**
     * Carregar dias de funcionamento DIRETAMENTE do banco
     */
    public function carregarDiasFuncionamento()
    {
        try {
            $diasFuncionamento = DB::table('horarios_funcionamento')
                ->where('ativo', 1)
                ->pluck('dia_semana')
                ->unique()
                ->values()
                ->toArray();
            
            if (!empty($diasFuncionamento)) {
                $this->diasFuncionamento = $diasFuncionamento;
            } else {
                $this->diasFuncionamento = [1, 2, 3, 4, 5];
            }
            
        } catch (\Exception $e) {
            $this->mensagemErro = 'Aviso: Usando configuração padrão. Erro: ' . $e->getMessage();
            $this->diasFuncionamento = [1, 2, 3, 4, 5];
        }
    }

    /**
     * Verificar se um dia está disponível DIRETAMENTE
     */
    public function isDiaDisponivel($data)
    {
        try {
            $dataCarbon = Carbon::parse($data);
            $diaSemana = $dataCarbon->dayOfWeek;
            
            $horarioFuncionamento = DB::table('horarios_funcionamento')
                ->where('dia_semana', $diaSemana)
                ->where('ativo', 1)
                ->first();
            
            if (!$horarioFuncionamento) {
                return false;
            }
            
            $bloqueado = DB::table('bloqueios_agendamento')
                ->where('ativo', 1)
                ->where(function ($query) use ($dataCarbon) {
                    $query->where('tipo', 'data_completa')
                        ->where(function ($subQuery) use ($dataCarbon) {
                            $subQuery->where('data_inicio', $dataCarbon->format('Y-m-d'))
                                ->orWhere(function ($recurrentQuery) use ($dataCarbon) {
                                    $recurrentQuery->where('recorrente', 1)
                                        ->whereRaw('DATE_FORMAT(data_inicio, "%m-%d") = ?', [$dataCarbon->format('m-d')]);
                                });
                        });
                })
                ->exists();
            
            return !$bloqueado;
            
        } catch (\Exception $e) {
            $diaSemana = Carbon::parse($data)->dayOfWeek;
            return in_array($diaSemana, $this->diasFuncionamento);
        }
    }

    /**
     * Navegar para mês anterior
     */
    public function mesAnterior()
    {
        if ($this->mesAtual == 1) {
            $this->mesAtual = 12;
            $this->anoAtual--;
        } else {
            $this->mesAtual--;
        }
    }

    /**
     * Navegar para próximo mês
     */
    public function mesProximo()
    {
        if ($this->mesAtual == 12) {
            $this->mesAtual = 1;
            $this->anoAtual++;
        } else {
            $this->mesAtual++;
        }
    }

    /**
     * Selecionar data e carregar horários
     */
    public function selecionarData($data)
    {
        $this->dataSelecionada = $data;
        $this->dataAgendamento = $data;
        $this->horarioSelecionado = '';
        $this->horarioAgendamento = '';
        $this->carregarHorarios($data);
    }

    /**
     * Carregar horários disponíveis para uma data
     */
    public function carregarHorarios($data)
    {
        $this->carregandoHorarios = true;
        $this->horariosDisponiveis = [];
        
        try {
            $dataCarbon = Carbon::parse($data);
            $diaSemana = $dataCarbon->dayOfWeek;
            
            $horarioFuncionamento = DB::table('horarios_funcionamento')
                ->where('dia_semana', $diaSemana)
                ->where('ativo', 1)
                ->first();
            
            if (!$horarioFuncionamento) {
                $this->carregandoHorarios = false;
                return;
            }
            
            $horarios = [];
            $dataStr = $dataCarbon->format('Y-m-d');
            
            $horaInicio = substr($horarioFuncionamento->horario_inicio, 0, 8);
            $horaFim = substr($horarioFuncionamento->horario_fim, 0, 8);
            
            $inicio = Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . $horaInicio);
            $fim = Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . $horaFim);
            
            $current = $inicio->copy();
            $intervalo = 30;
            
            $agendamentosOcupados = DB::table('agendamentos')
                ->where('data_agendamento', $dataStr)
                ->whereIn('status', ['pendente', 'confirmado'])
                ->where('ativo', 1)
                ->pluck('horario_agendamento')
                ->map(function($horario) {
                    return Carbon::parse($horario)->format('H:i');
                })
                ->toArray();
            
            while ($current < $fim) {
                if (isset($horarioFuncionamento->tem_almoco) && $horarioFuncionamento->tem_almoco) {
                    $almocoInicio = Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . substr($horarioFuncionamento->almoco_inicio, 0, 8));
                    $almocoFim = Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . substr($horarioFuncionamento->almoco_fim, 0, 8));
                    
                    if ($current >= $almocoInicio && $current < $almocoFim) {
                        $current->addMinutes($intervalo);
                        continue;
                    }
                }
                
                $horarioFormatado = $current->format('H:i');
                $temAgendamento = in_array($horarioFormatado, $agendamentosOcupados);
                
                $horarios[] = [
                    'value' => $horarioFormatado,
                    'display' => $horarioFormatado,
                    'disponivel' => !$temAgendamento,
                    'ocupado' => $temAgendamento
                ];
                
                $current->addMinutes($intervalo);
            }
            
            $this->horariosDisponiveis = $horarios;
            
        } catch (\Exception $e) {
            $this->mensagemErro = 'Erro ao carregar horários: ' . $e->getMessage();
            $this->horariosDisponiveis = [
                ['value' => '08:00', 'display' => '08:00', 'disponivel' => true, 'ocupado' => false],
                ['value' => '09:00', 'display' => '09:00', 'disponivel' => false, 'ocupado' => true],
                ['value' => '10:00', 'display' => '10:00', 'disponivel' => true, 'ocupado' => false],
                ['value' => '14:00', 'display' => '14:00', 'disponivel' => true, 'ocupado' => false],
            ];
        }
        
        $this->carregandoHorarios = false;
    }

    /**
     * Selecionar horário
     */
    public function selecionarHorario($horario)
    {
        $this->horarioSelecionado = $horario;
        $this->horarioAgendamento = $horario;
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
     * Fazer login existente
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
        
        if (Auth::attempt(['email' => $this->email, 'password' => $this->senha])) {
            $this->finalizarAgendamento();
        } else {
            $this->addError('senha', 'E-mail ou senha incorretos');
        }
    }

    /**
     * Cadastro unificado: Usuário + Cliente + Agendamento
     */
    /**
 * Cadastro unificado: Usuário + Cliente + Agendamento
 */
public function fazerCadastroUnificado()
{
        $this->carregando = true;
        $this->mensagemErro = '';
        
        try {
            // Validações
            $this->validate([
                'nome' => 'required|string|min:3|max:255',
                'email' => 'required|email|unique:users,email',
                'telefone' => 'required|string|min:10|max:20',
                'senha' => ['required', Password::min(6)->letters()->numbers()],
                'senhaConfirmacao' => 'required|same:senha'
            ], [
                'nome.required' => 'Digite seu nome completo',
                'nome.min' => 'Nome deve ter pelo menos 3 caracteres',
                'email.required' => 'Digite seu e-mail',
                'email.email' => 'E-mail inválido',
                'email.unique' => 'Este e-mail já está cadastrado',
                'telefone.required' => 'Digite seu telefone',
                'telefone.min' => 'Telefone deve ter pelo menos 10 dígitos',
                'senha.required' => 'Digite uma senha',
                'senhaConfirmacao.same' => 'As senhas não coincidem'
            ]);
            
            // Verificar conflito de horário antes de salvar
            $conflito = DB::table('agendamentos')
                ->where('data_agendamento', $this->dataAgendamento)
                ->whereTime('horario_agendamento', $this->horarioAgendamento . ':00')
                ->whereIn('status', ['pendente', 'confirmado'])
                ->where('ativo', 1)
                ->exists();
            
            if ($conflito) {
                $this->mensagemErro = 'Este horário não está mais disponível. Selecione outro horário.';
                $this->carregando = false;
                $this->carregarHorarios($this->dataAgendamento); // Recarregar horários
                return;
            }
            
            DB::transaction(function () {
                // 1. Criar usuário
                $userId = DB::table('users')->insertGetId([
                    'name' => $this->nome,
                    'email' => $this->email,
                    'password' => Hash::make($this->senha),
                    'telefone' => preg_replace('/\D/', '', $this->telefone),
                    'tipo_usuario' => 'usuario',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // 2. Criar cliente (SEM coluna 'ativo')
                $clienteId = DB::table('clientes')->insertGetId([
                    'user_id' => $userId,
                    'nome' => $this->nome,
                    'email' => $this->email,
                    'telefone' => preg_replace('/\D/', '', $this->telefone),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // 3. Criar agendamento
                $this->agendamentoId = DB::table('agendamentos')->insertGetId([
                    'cliente_id' => $clienteId,
                    'user_id' => $userId,
                    'servico_id' => $this->servico_id,
                    'data_agendamento' => $this->dataAgendamento,
                    'horario_agendamento' => $this->horarioAgendamento . ':00',
                    'cliente_nome' => $this->nome,
                    'cliente_email' => $this->email,
                    'cliente_telefone' => preg_replace('/\D/', '', $this->telefone),
                    'observacoes' => $this->observacoes,
                    'status' => 'pendente',
                    'origem' => 'publico',
                    'cliente_cadastrado_automaticamente' => true,
                    'ativo' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // 4. Login automático
                Auth::loginUsingId($userId);
            });
            
            $this->etapaAtual = 3;
            $this->mensagemSucesso = 'Agendamento realizado com sucesso! Sua conta foi criada e você já está logado no sistema.';
            
        } catch (\Exception $e) {
            $this->mensagemErro = 'Erro ao processar agendamento: ' . $e->getMessage();
        }
        
        $this->carregando = false;
}

    /**
     * Finalizar agendamento (para login existente)
     */
    /**
 * Finalizar agendamento (para login existente)
 */
    private function finalizarAgendamento()
    {
        try {
            // Verificar conflito
            $conflito = DB::table('agendamentos')
                ->where('data_agendamento', $this->dataAgendamento)
                ->whereTime('horario_agendamento', $this->horarioAgendamento . ':00')
                ->whereIn('status', ['pendente', 'confirmado'])
                ->where('ativo', 1)
                ->exists();
            
            if ($conflito) {
                $this->mensagemErro = 'Este horário não está mais disponível. Selecione outro horário.';
                $this->carregarHorarios($this->dataAgendamento);
                return;
            }
            
            // Buscar ou criar cliente
            $cliente = DB::table('clientes')->where('user_id', Auth::id())->first();
            
            if (!$cliente) {
                $clienteId = DB::table('clientes')->insertGetId([
                    'user_id' => Auth::id(),
                    'nome' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'telefone' => Auth::user()->telefone ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $clienteId = $cliente->id;
            }
            
            // Criar agendamento
            $this->agendamentoId = DB::table('agendamentos')->insertGetId([
                'cliente_id' => $clienteId,
                'user_id' => Auth::id(),
                'servico_id' => $this->servico_id,
                'data_agendamento' => $this->dataAgendamento,
                'horario_agendamento' => $this->horarioAgendamento . ':00',
                'cliente_nome' => Auth::user()->name,
                'cliente_email' => Auth::user()->email,
                'cliente_telefone' => Auth::user()->telefone ?? '',
                'observacoes' => $this->observacoes,
                'status' => 'pendente',
                'origem' => 'publico',
                'cliente_cadastrado_automaticamente' => false,
                'ativo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->etapaAtual = 3;
            $this->mensagemSucesso = 'Agendamento realizado com sucesso!';
            
        } catch (\Exception $e) {
            $this->mensagemErro = 'Erro ao finalizar agendamento: ' . $e->getMessage();
        }
    }

    /**
     * Próxima etapa
     */
    public function proximaEtapa()
    {
        if ($this->etapaAtual == 1) {
            $this->validate([
                'servico_id' => 'required',
                'dataAgendamento' => 'required|date|after:today',
                'horarioAgendamento' => 'required',
            ], [
                'servico_id.required' => 'Selecione um serviço',
                'dataAgendamento.required' => 'Selecione uma data no calendário',
                'dataAgendamento.after' => 'A data deve ser futura',
                'horarioAgendamento.required' => 'Selecione um horário disponível',
            ]);
            
            $this->etapaAtual = 2;
        }
    }
    
    /**
     * Etapa anterior
     */
    public function etapaAnterior()
    {
        if ($this->etapaAtual > 1) {
            $this->etapaAtual--;
        }
    }

    /**
     * Obter dados do calendário
     */
    public function getDadosCalendarioProperty()
    {
        $primeiroDiaDoMes = Carbon::createFromDate($this->anoAtual, $this->mesAtual, 1);
        $ultimoDiaDoMes = $primeiroDiaDoMes->copy()->endOfMonth();
        $hoje = now()->startOfDay();
        
        $inicioGrade = $primeiroDiaDoMes->copy()->startOfWeek(0);
        $fimGrade = $ultimoDiaDoMes->copy()->endOfWeek(6);
        
        $dias = [];
        $current = $inicioGrade->copy();
        
        while ($current <= $fimGrade) {
            $isOutroMes = $current->month != $this->mesAtual;
            $isPassado = $current < $hoje;
            $diaSemana = $current->dayOfWeek;
            
            $isFuncionamento = in_array($diaSemana, $this->diasFuncionamento);
            
            $isDisponivel = false;
            if (!$isOutroMes && !$isPassado && $isFuncionamento) {
                $isDisponivel = $this->isDiaDisponivel($current);
            }
            
            $dias[] = [
                'data' => $current->format('Y-m-d'),
                'dia' => $current->day,
                'isOutroMes' => $isOutroMes,
                'isPassado' => $isPassado,
                'isFuncionamento' => $isFuncionamento,
                'isDisponivel' => $isDisponivel,
                'isSelecionado' => $this->dataSelecionada === $current->format('Y-m-d'),
                'isHoje' => $current->isSameDay($hoje)
            ];
            
            $current->addDay();
        }
        
        return $dias;
    }

    /**
     * Obter nome do mês atual
     */
    public function getNomesMesesProperty()
    {
        return [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
    }

    /**
     * Carregar serviços
     */
    public function carregarServicos()
    {
        try {
            $servicosDB = DB::table('servicos')
                ->where('ativo', 1)
                ->orderBy('nome')
                ->get();
            
            if ($servicosDB->count() > 0) {
                $this->servicos = $servicosDB->map(function ($servico) {
                    return [
                        'id' => $servico->id,
                        'nome' => $servico->nome,
                        'descricao' => $servico->descricao ?? '',
                        'preco' => $servico->preco ?? 0,
                        'duracao' => $servico->duracao ?? 30,
                        'preco_formatado' => 'R$ ' . number_format($servico->preco ?? 0, 2, ',', '.'),
                        'duracao_formatada' => ($servico->duracao ?? 30) . ' min',
                        'display_completo' => $servico->nome . ' - R$ ' . number_format($servico->preco ?? 0, 2, ',', '.') . ' (' . ($servico->duracao ?? 30) . ' min)'
                    ];
                })->toArray();
            } else {
                $this->usarDadosExemplo();
            }
        } catch (\Exception $e) {
            $this->mensagemErro = 'Aviso: Usando dados de exemplo. Erro: ' . $e->getMessage();
            $this->usarDadosExemplo();
        }
    }
    
    /**
     * Usar dados de exemplo
     */
    private function usarDadosExemplo()
    {
        $this->servicos = [
            [
                'id' => 1, 'nome' => 'Consulta Médica', 'descricao' => 'Consulta médica geral',
                'preco' => 100.00, 'duracao' => 30, 'preco_formatado' => 'R$ 100,00',
                'duracao_formatada' => '30 min', 'display_completo' => 'Consulta Médica - R$ 100,00 (30 min)'
            ],
            [
                'id' => 2, 'nome' => 'Exame de Sangue', 'descricao' => 'Coleta de sangue para exames laboratoriais',
                'preco' => 80.00, 'duracao' => 15, 'preco_formatado' => 'R$ 80,00',
                'duracao_formatada' => '15 min', 'display_completo' => 'Exame de Sangue - R$ 80,00 (15 min)'
            ]
        ];
    }
    
    /**
     * Obter serviço selecionado
     */
    public function getServicoSelecionadoProperty()
    {
        if (!$this->servico_id || empty($this->servicos)) {
            return null;
        }
        
        return collect($this->servicos)->firstWhere('id', (int)$this->servico_id);
    }

    /**
     * Render
     */
    public function render()
    {
        return view('livewire.publico.agendamento-hibrido', [
            'servicos' => $this->servicos
        ]);
    }
}