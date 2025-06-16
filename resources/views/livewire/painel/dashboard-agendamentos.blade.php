<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Dashboard - Agendamentos</h3>
        <div class="text-muted">
            <i class="fas fa-calendar"></i> {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <!-- Mensagens -->
    @if (session()->has('sucesso'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('sucesso') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Cards de Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-title">Agendamentos Hoje</h6>
                            <h3 class="mb-0">{{ $agendamentosHoje->count() }}</h3>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-title">Agendamentos Amanhã</h6>
                            <h3 class="mb-0">{{ $agendamentosAmanha->count() }}</h3>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-calendar-plus fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-title">Pendentes</h6>
                            <h3 class="mb-0">{{ $agendamentosPendentes->count() }}</h3>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-clock fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-title">Total do Mês</h6>
                            <h3 class="mb-0">{{ $totalAgendamentosMes }}</h3>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-chart-line fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Agendamentos de Hoje -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Agendamentos de Hoje</h5>
                    <span class="badge bg-primary">{{ $agendamentosHoje->count() }}</span>
                </div>
                <div class="card-body">
                    @if($agendamentosHoje->count() > 0)
                        @foreach($agendamentosHoje as $agendamento)
                            <div class="d-flex align-items-center p-2 border-bottom">
                                <div class="flex-grow-1">
                                    <div class="fw-bold">{{ $agendamento->cliente->nome }}</div>
                                    <small class="text-muted">
                                        {{ $agendamento->servico->nome }} - 
                                        {{ Carbon\Carbon::parse($agendamento->horario_agendamento)->format('H:i') }}
                                    </small>
                                </div>
                                <div>
                                    <span class="badge bg-{{ $agendamento->cor_status }}">
                                        {{ $agendamento->status_formatado }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-calendar-times fa-2x mb-2"></i>
                            <p class="mb-0">Nenhum agendamento para hoje</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Agendamentos Pendentes -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pendentes para Aprovação</h5>
                    <span class="badge bg-warning">{{ $agendamentosPendentes->count() }}</span>
                </div>
                <div class="card-body">
                    @if($agendamentosPendentes->count() > 0)
                        @foreach($agendamentosPendentes as $agendamento)
                            <div class="d-flex align-items-center p-2 border-bottom">
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
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <p class="mb-0">Nenhum agendamento pendente</p>
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
                <div class="card-header">
                    <h5 class="mb-0">Próximos Agendamentos (7 dias)</h5>
                </div>
                <div class="card-body">
                    @if($proximosAgendamentos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Serviço</th>
                                        <th>Data & Hora</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($proximosAgendamentos as $agendamento)
                                        <tr>
                                            <td>
                                                <strong>{{ $agendamento->cliente->nome }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $agendamento->cliente->telefone }}</small>
                                            </td>
                                            <td>{{ $agendamento->servico->nome }}</td>
                                            <td>{{ $agendamento->data_hora_formatada }}</td>
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
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                            <p class="mb-0">Nenhum agendamento nos próximos 7 dias</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Estatísticas Adicionais -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                    <h5>{{ $this->estatisticas['total_clientes'] }}</h5>
                    <p class="text-muted mb-0">Total de Clientes</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h5>{{ $this->estatisticas['agendamentos_concluidos_mes'] }}</h5>
                    <p class="text-muted mb-0">Concluídos este Mês</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-concierge-bell fa-2x text-info mb-2"></i>
                    <h5>{{ $this->estatisticas['total_servicos_ativos'] }}</h5>
                    <p class="text-muted mb-0">Serviços Ativos</p>
                </div>
            </div>
        </div>
    </div>
</div>