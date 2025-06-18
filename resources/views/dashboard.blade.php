<div>
    <!-- Header com Filtros -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">
            <i class="fas fa-tachometer-alt text-primary me-2"></i>
            Dashboard - Agendamentos
        </h3>
        <p>Filtro atual: {{ $filtro_periodo ?? 'não definido' }}</p>         
        <div class="d-flex align-items-center gap-3">
            <!-- Filtro Rápido -->
            <div class="btn-group" role="group">
                <button wire:click="$set('filtro_periodo', 'hoje')" 
                        class="btn btn-sm {{ $filtro_periodo == 'hoje' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Hoje
                </button>
                <button wire:click="$set('filtro_periodo', 'semana')" 
                        class="btn btn-sm {{ $filtro_periodo == 'semana' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Semana
                </button>
                <button wire:click="$set('filtro_periodo', 'mes')" 
                        class="btn btn-sm {{ $filtro_periodo == 'mes' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Mês
                </button>
            </div>
            
            <!-- Botão Atualizar -->
            <button wire:click="atualizarDados" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-sync-alt"></i>
            </button>
            
            <!-- Data/Hora Atual -->
            <div class="text-muted small">
                <i class="fas fa-calendar"></i> {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    <!-- Mensagens -->
    @if (session()->has('sucesso'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('sucesso') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Cards de Estatísticas Principais -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-title mb-1">Agendamentos Hoje</h6>
                            <h2 class="mb-0 fw-bold">{{ $agendamentosHoje->count() }}</h2>
                            <small class="opacity-75">
                                {{ $agendamentosHoje->where('status', 'confirmado')->count() }} confirmados
                            </small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-calendar-day fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-title mb-1">Agendamentos Amanhã</h6>
                            <h2 class="mb-0 fw-bold">{{ $agendamentosAmanha->count() }}</h2>
                            <small class="opacity-75">
                                Próximo: {{ $agendamentosAmanha->first()?->horario_agendamento ? Carbon\Carbon::parse($agendamentosAmanha->first()->horario_agendamento)->format('H:i') : '--' }}
                            </small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-calendar-plus fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-title mb-1">Pendentes</h6>
                            <h2 class="mb-0 fw-bold">{{ $agendamentosPendentes->count() }}</h2>
                            <small class="opacity-75">
                                Precisam aprovação
                            </small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-clock fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-title mb-1">Total do Mês</h6>
                            <h2 class="mb-0 fw-bold">{{ $totalAgendamentosMes }}</h2>
                            <small class="opacity-75">
                                @if(isset($estatisticas['crescimento_mensal']))
                                    @if($estatisticas['crescimento_mensal'] > 0)
                                        <i class="fas fa-arrow-up"></i> +{{ $estatisticas['crescimento_mensal'] }}%
                                    @elseif($estatisticas['crescimento_mensal'] < 0)
                                        <i class="fas fa-arrow-down"></i> {{ $estatisticas['crescimento_mensal'] }}%
                                    @else
                                        <i class="fas fa-minus"></i> 0%
                                    @endif
                                @endif
                            </small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-chart-line fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards de Receita (se serviços têm preço) -->
    @if(isset($estatisticas['receita_estimada_mes']) && $estatisticas['receita_estimada_mes'] > 0)
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-dollar-sign fa-2x text-success mb-2"></i>
                        <h4 class="text-success">R$ {{ number_format($estatisticas['receita_estimada_mes'], 2, ',', '.') }}</h4>
                        <p class="text-muted mb-0">Receita Estimada (Mês)</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-money-check-alt fa-2x text-primary mb-2"></i>
                        <h4 class="text-primary">R$ {{ number_format($estatisticas['receita_confirmada_mes'], 2, ',', '.') }}</h4>
                        <p class="text-muted mb-0">Receita Confirmada (Concluídos)</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Próximo Agendamento em Destaque -->
    @if(isset($estatisticas['proximo_agendamento']) && $estatisticas['proximo_agendamento'])
        <div class="alert alert-info mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-bell fa-2x me-3"></i>
                <div class="flex-grow-1">
                    <h5 class="alert-heading mb-1">🔔 Próximo Agendamento</h5>
                    <strong>{{ $estatisticas['proximo_agendamento']->cliente->nome }}</strong> - 
                    {{ $estatisticas['proximo_agendamento']->servico->nome }}<br>
                    <small>
                        {{ $estatisticas['proximo_agendamento']->data_agendamento->format('d/m/Y') }} às 
                        {{ Carbon\Carbon::parse($estatisticas['proximo_agendamento']->horario_agendamento)->format('H:i') }}
                    </small>
                </div>
                <div>
                    @if($estatisticas['proximo_agendamento']->status === 'pendente')
                        <button class="btn btn-success btn-sm me-2" 
                                wire:click="confirmarAgendamento({{ $estatisticas['proximo_agendamento']->id }})">
                            <i class="fas fa-check"></i> Confirmar
                        </button>
                    @endif
                    <span class="badge bg-{{ $estatisticas['proximo_agendamento']->cor_status }}">
                        {{ $estatisticas['proximo_agendamento']->status_formatado }}
                    </span>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <!-- Agendamentos de Hoje -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-day text-primary me-2"></i>
                        Agendamentos de Hoje
                    </h5>
                    <span class="badge bg-primary">{{ $agendamentosHoje->count() }}</span>
                </div>
                <div class="card-body">
                    @if($agendamentosHoje->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($agendamentosHoje as $agendamento)
                                <div class="list-group-item d-flex align-items-center p-3 border-0 border-bottom">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">{{ $agendamento->cliente->nome }}</div>
                                        <small class="text-muted">
                                            {{ $agendamento->servico->nome }} - 
                                            {{ Carbon\Carbon::parse($agendamento->horario_agendamento)->format('H:i') }}
                                        </small>
                                        @if($agendamento->cliente->telefone)
                                            <br><small class="text-muted">
                                                <i class="fas fa-phone me-1"></i>{{ $agendamento->cliente->telefone }}
                                            </small>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $agendamento->cor_status }} mb-1 d-block">
                                            {{ $agendamento->status_formatado }}
                                        </span>
                                        @if($agendamento->status === 'confirmado')
                                            <button class="btn btn-outline-success btn-sm" 
                                                    wire:click="concluirAgendamento({{ $agendamento->id }})"
                                                    title="Marcar como concluído">
                                                <i class="fas fa-check-double"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-calendar-times fa-3x mb-3 opacity-50"></i>
                            <p class="mb-0">Nenhum agendamento para hoje</p>
                            <small>Aproveite para organizar outras atividades!</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Agendamentos Pendentes -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-clock text-warning me-2"></i>
                        Pendentes para Aprovação
                    </h5>
                    <span class="badge bg-warning">{{ $agendamentosPendentes->count() }}</span>
                </div>
                <div class="card-body">
                    @if($agendamentosPendentes->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($agendamentosPendentes as $agendamento)
                                <div class="list-group-item d-flex align-items-center p-3 border-0 border-bottom">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">{{ $agendamento->cliente->nome }}</div>
                                        <small class="text-muted">
                                            {{ $agendamento->servico->nome }}<br>
                                            {{ $agendamento->data_hora_formatada }}
                                        </small>
                                    </div>
                                    <div class="btn-group-vertical btn-group-sm">
                                        <button class="btn btn-outline-success btn-sm" 
                                                wire:click="confirmarAgendamento({{ $agendamento->id }})"
                                                title="Confirmar">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" 
                                                wire:click="cancelarAgendamento({{ $agendamento->id }})"
                                                onclick="return confirm('Cancelar este agendamento?')"
                                                title="Cancelar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-check-circle fa-3x mb-3 text-success opacity-50"></i>
                            <p class="mb-0">Nenhum agendamento pendente</p>
                            <small>Tudo em dia! 🎉</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Estatísticas Detalhadas -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <h4>{{ $estatisticas['total_clientes'] ?? 0 }}</h4>
                    <p class="text-muted mb-1">Total de Clientes</p>
                    @if(isset($estatisticas['clientes_novos_mes']))
                        <small class="text-success">
                            <i class="fas fa-plus"></i> {{ $estatisticas['clientes_novos_mes'] }} novos este mês
                        </small>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h4>{{ $estatisticas['agendamentos_concluidos_mes'] ?? 0 }}</h4>
                    <p class="text-muted mb-1">Concluídos este Mês</p>
                    @if(isset($estatisticas['taxa_conversao']))
                        <small class="text-info">
                            <i class="fas fa-percentage"></i> {{ $estatisticas['taxa_conversao'] }}% taxa de conversão
                        </small>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-concierge-bell fa-3x text-info mb-3"></i>
                    <h4>{{ $estatisticas['total_servicos_ativos'] ?? 0 }}</h4>
                    <p class="text-muted mb-0">Serviços Ativos</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-chart-bar fa-3x text-warning mb-3"></i>
                    <h4>{{ isset($estatisticas['agendamentos_por_status']['confirmado']) ? $estatisticas['agendamentos_por_status']['confirmado'] : 0 }}</h4>
                    <p class="text-muted mb-0">Confirmados (Mês)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Horários Populares e Serviços Mais Procurados -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-clock text-info me-2"></i>
                        Horários Mais Procurados
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($estatisticas['horarios_populares']) && $estatisticas['horarios_populares']->count() > 0)
                        @foreach($estatisticas['horarios_populares'] as $horario)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>{{ $horario->horario }}</span>
                                <div>
                                    <span class="badge bg-primary">{{ $horario->total }} agendamentos</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-clock fa-2x mb-2 opacity-50"></i>
                            <p class="mb-0">Dados insuficientes</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-star text-warning me-2"></i>
                        Serviços Mais Procurados
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($estatisticas['servicos_populares']) && $estatisticas['servicos_populares']->count() > 0)
                        @foreach($estatisticas['servicos_populares'] as $servico)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>{{ $servico->nome }}</span>
                                <div>
                                    <span class="badge bg-success">{{ $servico->total }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-concierge-bell fa-2x mb-2 opacity-50"></i>
                            <p class="mb-0">Dados insuficientes</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Próximos Agendamentos -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                        Próximos Agendamentos (7 dias)
                    </h5>
                    <div>
                        <button wire:click="exportarRelatorio" class="btn btn-outline-secondary btn-sm me-2">
                            <i class="fas fa-file-pdf"></i> Relatório
                        </button>
                        <span class="badge bg-info">{{ $proximosAgendamentos->count() }}</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($proximosAgendamentos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="25%">Cliente</th>
                                        <th width="20%">Serviço</th>
                                        <th width="20%">Data & Hora</th>
                                        <th width="15%">Status</th>
                                        <th width="20%">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($proximosAgendamentos as $agendamento)
                                        <tr class="{{ $agendamento->data_agendamento->isToday() ? 'table-warning' : '' }}">
                                            <td>
                                                <strong>{{ $agendamento->cliente->nome }}</strong>
                                                @if($agendamento->cliente->telefone)
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-phone me-1"></i>{{ $agendamento->cliente->telefone }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $agendamento->servico->nome }}
                                                @if($agendamento->servico->duracao_formatada)
                                                    <br><small class="text-muted">{{ $agendamento->servico->duracao_formatada }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $agendamento->data_agendamento->format('d/m/Y') }}
                                                <br>
                                                <strong>{{ Carbon\Carbon::parse($agendamento->horario_agendamento)->format('H:i') }}</strong>
                                                @if($agendamento->data_agendamento->isToday())
                                                    <br><small class="text-warning fw-bold">HOJE</small>
                                                @elseif($agendamento->data_agendamento->isTomorrow())
                                                    <br><small class="text-info">Amanhã</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $agendamento->cor_status }}">
                                                    {{ $agendamento->status_formatado }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    @if($agendamento->status === 'pendente')
                                                        <button class="btn btn-outline-success" 
                                                                wire:click="confirmarAgendamento({{ $agendamento->id }})"
                                                                title="Confirmar">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    @if($agendamento->status === 'confirmado')
                                                        <button class="btn btn-outline-primary" 
                                                                wire:click="concluirAgendamento({{ $agendamento->id }})"
                                                                title="Concluir">
                                                            <i class="fas fa-check-double"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    @if($agendamento->podeSerCancelado())
                                                        <button class="btn btn-outline-danger" 
                                                                wire:click="cancelarAgendamento({{ $agendamento->id }})"
                                                                onclick="return confirm('Cancelar agendamento?')"
                                                                title="Cancelar">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-calendar-alt fa-3x mb-3 opacity-50"></i>
                            <h5>Nenhum agendamento nos próximos 7 dias</h5>
                            <p class="mb-0">Período tranquilo pela frente!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Resumo de Status -->
    @if(isset($estatisticas['agendamentos_por_status']) && count($estatisticas['agendamentos_por_status']) > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-pie text-info me-2"></i>
                            Resumo por Status (Mês Atual)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            @php
                                $statusColors = [
                                    'pendente' => 'warning',
                                    'confirmado' => 'info', 
                                    'concluido' => 'success',
                                    'cancelado' => 'danger'
                                ];
                                $statusLabels = [
                                    'pendente' => 'Pendentes',
                                    'confirmado' => 'Confirmados',
                                    'concluido' => 'Concluídos',
                                    'cancelado' => 'Cancelados'
                                ];
                            @endphp
                            
                            @foreach($estatisticas['agendamentos_por_status'] as $status => $total)
                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        <h3 class="text-{{ $statusColors[$status] ?? 'secondary' }}">{{ $total }}</h3>
                                        <p class="mb-0">{{ $statusLabels[$status] ?? ucfirst($status) }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Loading Indicator -->
    <div wire:loading class="position-fixed top-50 start-50 translate-middle">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
    </div>
</div>