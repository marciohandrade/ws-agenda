<?php

namespace App\Livewire\Publico;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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
     * ✅ MÉTODO HELPER: Obter duração do serviço selecionado
     */
    public function getDuracaoServicoSelecionado()
    {
        if (!$this->servico_id) {
            return 30; // Padrão
        }
        
        try {
            $servico = DB::table('servicos')
                ->where('id', $this->servico_id)
                ->where('ativo', 1)
                ->first();
            
            if ($servico) {
                // Verifica ambos os nomes de campo para compatibilidade
                return (int) ($servico->duracao_minutos ?? $servico->duracao ?? 30);
            }
        } catch (\Exception $e) {
            // Em caso de erro, retorna padrão
        }
        
        return 30;
    }

    /**
     * ✅ CORRIGIDO: Listener aprimorado para mudança de serviço
     */
    public function updatedServicoId()
    {
        // Limpar seleções de data/horário quando trocar serviço
        $this->horarioSelecionado = '';
        $this->horarioAgendamento = '';
        $this->horariosDisponiveis = [];
        
        // ✅ FORÇA LIMPEZA DA MENSAGEM DE ERRO
        $this->mensagemErro = '';
        
        // ✅ Se já tinha uma data selecionada, recarregar horários com novo intervalo
        if ($this->dataSelecionada && $this->servico_id) {
            // ✅ FORÇA REFRESH DOS HORÁRIOS COM NOVO SERVIÇO
            $this->recarregarHorarios();
        }
        
        // ✅ DISPATCH DE EVENTO PARA NOTIFICAR MUDANÇA NO FRONTEND
        $this->dispatch('servico-alterado');
    }

    /**
     * ✅ NOVO: Método específico para recarregar horários forçadamente
     */
    public function recarregarHorarios()
    {
        if ($this->dataSelecionada) {
            $this->carregandoHorarios = true;
            
            // ✅ PEQUENO DELAY PARA GARANTIR QUE O FRONTEND VEJA O LOADING
            $this->carregarHorarios($this->dataSelecionada);
            
            // ✅ DISPATCH PARA NOTIFICAR QUE OS HORÁRIOS FORAM RECARREGADOS
            $this->dispatch('horarios-recarregados');
        }
    }

    /**
     * ✅ MÉTODO PÚBLICO: Forçar recarga de horários (pode ser chamado do frontend)
     */
    public function forcarRecargaHorarios()
    {
        if ($this->dataSelecionada && $this->servico_id) {
            $this->recarregarHorarios();
        }
    }

    /**
     * ✅ CORRIGIDO: Carregar horários com intervalo dinâmico baseado no serviço
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
            
            // ✅ USAR DURAÇÃO DO SERVIÇO SELECIONADO COMO INTERVALO
            $intervalo = $this->getDuracaoServicoSelecionado();
            
            // ✅ LOG PARA DEBUG (remover em produção)
            \Log::info("Carregando horários para serviço {$this->servico_id} com intervalo de {$intervalo} minutos");
            
            $horarios = [];
            $dataStr = $dataCarbon->format('Y-m-d');
            
            $horaInicio = substr($horarioFuncionamento->horario_inicio, 0, 8);
            $horaFim = substr($horarioFuncionamento->horario_fim, 0, 8);
            
            $inicio = Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . $horaInicio);
            $fim = Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . $horaFim);
            
            $current = $inicio->copy();
            
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
                // ✅ VERIFICA HORÁRIO DE ALMOÇO
                if (isset($horarioFuncionamento->tem_almoco) && $horarioFuncionamento->tem_almoco) {
                    $almocoInicio = Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . substr($horarioFuncionamento->almoco_inicio, 0, 8));
                    $almocoFim = Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . substr($horarioFuncionamento->almoco_fim, 0, 8));
                    
                    if ($current >= $almocoInicio && $current < $almocoFim) {
                        $current->addMinutes($intervalo); // ✅ USA INTERVALO DINÂMICO
                        continue;
                    }
                }
                
                $horarioFormatado = $current->format('H:i');
                $temAgendamento = in_array($horarioFormatado, $agendamentosOcupados);
                
                // ✅ VALIDAÇÃO ADICIONAL: Verificar se há tempo suficiente para o serviço
                $horarioFimServico = $current->copy()->addMinutes($intervalo);
                $servicoCabeFinal = $horarioFimServico <= $fim;
                
                // ✅ Se passa do almoço, verificar se cabe antes do almoço
                if (isset($horarioFuncionamento->tem_almoco) && $horarioFuncionamento->tem_almoco) {
                    $almocoInicio = Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . substr($horarioFuncionamento->almoco_inicio, 0, 8));
                    if ($current < $almocoInicio && $horarioFimServico > $almocoInicio) {
                        $servicoCabeFinal = false; // Não cabe antes do almoço
                    }
                }
                
                if ($servicoCabeFinal) {
                    $horarios[] = [
                        'value' => $horarioFormatado,
                        'display' => $horarioFormatado,
                        'disponivel' => !$temAgendamento,
                        'ocupado' => $temAgendamento,
                        'intervalo' => $intervalo // ✅ ADICIONA INFO DO INTERVALO PARA DEBUG
                    ];
                }
                
                $current->addMinutes($intervalo); // ✅ USA INTERVALO DINÂMICO
            }
            
            $this->horariosDisponiveis = $horarios;
            
        } catch (\Exception $e) {
            $this->mensagemErro = 'Erro ao carregar horários: ' . $e->getMessage();
            
            // ✅ FALLBACK COM HORÁRIOS DE EXEMPLO
            $this->horariosDisponiveis = [
                ['value' => '08:00', 'display' => '08:00', 'disponivel' => true, 'ocupado' => false, 'intervalo' => 30],
                ['value' => '09:00', 'display' => '09:00', 'disponivel' => false, 'ocupado' => true, 'intervalo' => 30],
                ['value' => '10:00', 'display' => '10:00', 'disponivel' => true, 'ocupado' => false, 'intervalo' => 30],
                ['value' => '14:00', 'display' => '14:00', 'disponivel' => true, 'ocupado' => false, 'intervalo' => 30],
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
     * ✅ NOVO: Enviar email de confirmação do agendamento
     */
    private function enviarEmailConfirmacao($dadosUsuario, $dadosAgendamento)
    {
        try {
            // Buscar dados do serviço para o email
            $servico = DB::table('servicos')
                ->where('id', $this->servico_id)
                ->first();
            
            $nomeServico = $servico ? $servico->nome : 'Serviço';
            $precoServico = $servico ? 'R$ ' . number_format($servico->preco ?? 0, 2, ',', '.') : '';
            $duracaoServico = $servico ? ($servico->duracao_minutos ?? $servico->duracao ?? 30) . ' minutos' : '';
            
            // Formatação de data e hora para o email
            $dataFormatada = Carbon::parse($this->dataAgendamento)->format('d/m/Y');
            $horarioFormatado = $this->horarioAgendamento;
            
            // Dados para o template do email
            $dadosEmail = [
                'nomeCliente' => $dadosUsuario['nome'],
                'emailCliente' => $dadosUsuario['email'],
                'nomeServico' => $nomeServico,
                'precoServico' => $precoServico,
                'duracaoServico' => $duracaoServico,
                'dataAgendamento' => $dataFormatada,
                'horarioAgendamento' => $horarioFormatado,
                'observacoes' => $this->observacoes ?: 'Nenhuma observação',
                'agendamentoId' => $this->agendamentoId,
                'status' => 'Pendente'
            ];
            
            // Enviar email usando template simples
            Mail::send([], [], function ($message) use ($dadosEmail) {
                $message->to($dadosEmail['emailCliente'], $dadosEmail['nomeCliente'])
                    ->subject('Confirmação de Agendamento - Status Pendente')
                    ->html($this->criarTemplateEmail($dadosEmail));
            });
            
        } catch (\Exception $e) {
            // Log do erro, mas não quebra o fluxo
            \Log::error('Erro ao enviar email de confirmação: ' . $e->getMessage());
        }
    }

    /**
     * ✅ NOVO: Criar template HTML para o email de confirmação
     */
    private function criarTemplateEmail($dados)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Confirmação de Agendamento</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            
            <div style='background-color: #f8f9fa; padding: 30px; border-radius: 10px; border-left: 5px solid #28a745;'>
                <h1 style='color: #28a745; margin-bottom: 20px;'>✅ Agendamento Realizado com Sucesso!</h1>
                
                <p style='font-size: 16px; margin-bottom: 25px;'>
                    Olá <strong>{$dados['nomeCliente']}</strong>,
                </p>
                
                <p style='font-size: 14px; margin-bottom: 25px;'>
                    Seu agendamento foi realizado com sucesso e está com status <strong style='color: #ffc107;'>PENDENTE</strong>. 
                    Em breve nossa equipe irá confirmar seu agendamento através do painel administrativo.
                </p>
                
                <div style='background-color: white; padding: 20px; border-radius: 8px; margin: 25px 0;'>
                    <h3 style='color: #495057; margin-bottom: 15px; border-bottom: 2px solid #dee2e6; padding-bottom: 10px;'>
                        📋 Detalhes do seu agendamento:
                    </h3>
                    
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold; color: #6c757d;'>🔹 Agendamento #:</td>
                            <td style='padding: 8px 0;'>{$dados['agendamentoId']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold; color: #6c757d;'>🏥 Serviço:</td>
                            <td style='padding: 8px 0;'>{$dados['nomeServico']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold; color: #6c757d;'>💰 Valor:</td>
                            <td style='padding: 8px 0;'>{$dados['precoServico']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold; color: #6c757d;'>⏱️ Duração:</td>
                            <td style='padding: 8px 0;'>{$dados['duracaoServico']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold; color: #6c757d;'>📅 Data:</td>
                            <td style='padding: 8px 0;'>{$dados['dataAgendamento']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold; color: #6c757d;'>🕐 Horário:</td>
                            <td style='padding: 8px 0;'>{$dados['horarioAgendamento']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold; color: #6c757d;'>📝 Observações:</td>
                            <td style='padding: 8px 0;'>{$dados['observacoes']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold; color: #6c757d;'>📊 Status:</td>
                            <td style='padding: 8px 0;'><span style='background-color: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 4px; font-size: 12px;'>{$dados['status']}</span></td>
                        </tr>
                    </table>
                </div>
                
                <div style='background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 25px 0;'>
                    <h4 style='margin: 0 0 10px 0;'>ℹ️ Próximos passos:</h4>
                    <ul style='margin: 0; padding-left: 20px;'>
                        <li>Aguarde a confirmação da nossa equipe</li>
                        <li>Você receberá um novo email quando o status for atualizado</li>
                        <li>Em caso de dúvidas, entre em contato conosco</li>
                    </ul>
                </div>
                
                <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; color: #6c757d; font-size: 12px;'>
                    <p style='margin: 0;'>
                        Este é um email automático, não é necessário responder.<br>
                        Agendamento realizado em " . now()->format('d/m/Y H:i') . "
                    </p>
                </div>
            </div>
            
        </body>
        </html>";
    }

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
            
            // ✅ ENVIAR EMAIL DE CONFIRMAÇÃO
            $this->enviarEmailConfirmacao([
                'nome' => $this->nome,
                'email' => $this->email
            ], [
                'agendamento_id' => $this->agendamentoId
            ]);
            
            $this->etapaAtual = 3;
            $this->mensagemSucesso = 'Agendamento realizado com sucesso! Sua conta foi criada e você já está logado no sistema. Um email de confirmação foi enviado para ' . $this->email;
            
        } catch (\Exception $e) {
            $this->mensagemErro = 'Erro ao processar agendamento: ' . $e->getMessage();
        }
        
        $this->carregando = false;
    }

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
                    // ✅ COMPATIBILIDADE COM AMBOS OS CAMPOS
                    $duracao = $servico->duracao_minutos ?? $servico->duracao ?? 30;
                    
                    return [
                        'id' => $servico->id,
                        'nome' => $servico->nome,
                        'descricao' => $servico->descricao ?? '',
                        'preco' => $servico->preco ?? 0,
                        'duracao' => $duracao,
                        'preco_formatado' => 'R$ ' . number_format($servico->preco ?? 0, 2, ',', '.'),
                        'duracao_formatada' => $duracao . ' min',
                        'display_completo' => $servico->nome . ' - R$ ' . number_format($servico->preco ?? 0, 2, ',', '.') . ' (' . $duracao . ' min)'
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