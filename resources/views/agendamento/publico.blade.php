@extends('layouts.clinica')

@section('title', 'Agendamento Online - Clínica Vida Saudável')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/agendamento-publico.css') }}">
@endpush

@section('content')
<div class="agendamento-container">
    <!-- Header Fixo -->
    <div class="header-agendamento">
        <div class="container">
            <h1>
                <i class="fas fa-calendar-plus"></i>
                Agendar Consulta
            </h1>
            <p>Rápido, fácil e seguro</p>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="progress-container">
        <div class="progress-bar">
            <div class="progress-step active" data-step="1">
                <span class="step-number">1</span>
                <span class="step-label">Serviço</span>
            </div>
            <div class="progress-step" data-step="2">
                <span class="step-number">2</span>
                <span class="step-label">Data</span>
            </div>
            <div class="progress-step" data-step="3">
                <span class="step-number">3</span>
                <span class="step-label">Horário</span>
            </div>
            <div class="progress-step" data-step="4">
                <span class="step-number">4</span>
                <span class="step-label">Dados</span>
            </div>
        </div>
    </div>

    <!-- Formulário por Etapas -->
    <div class="form-container">
        <form id="agendamentoForm" class="agendamento-form">
            @csrf
            
            <!-- ETAPA 1: SERVIÇO -->
            <div class="form-step active" data-step="1">
                <div class="step-header">
                    <h2>Escolha o Serviço</h2>
                    <p>Selecione o tipo de atendimento desejado</p>
                </div>
                
                <div class="input-group">
                    <div class="servicos-grid" id="servicosGrid">
                        <!-- Carregado via JavaScript -->
                        <div class="loading-spinner">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>Carregando serviços...</span>
                        </div>
                    </div>
                    <input type="hidden" id="servico_id" name="servico_id" required>
                    <div class="error-message" id="erro-servico"></div>
                </div>

                <div class="step-actions">
                    <button type="button" class="btn btn-primary btn-block" id="btnProximoServico" disabled>
                        Próximo
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- ETAPA 2: DATA -->
            <div class="form-step" data-step="2">
                <div class="step-header">
                    <h2>Escolha a Data</h2>
                    <p>Selecione um dia disponível</p>
                </div>

                <div class="input-group">
                    <!-- Mini Calendário Mobile-Friendly -->
                    <div class="calendario-container">
                        <div class="calendario-header">
                            <button type="button" class="btn-nav" id="mesAnterior">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <h3 id="mesAno">Carregando...</h3>
                            <button type="button" class="btn-nav" id="mesProximo">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        
                        <div class="calendario-dias-semana">
                            <span>Dom</span><span>Seg</span><span>Ter</span><span>Qua</span><span>Qui</span><span>Sex</span><span>Sáb</span>
                        </div>
                        
                        <div class="calendario-grid" id="calendarioGrid">
                            <!-- Dias carregados via JavaScript -->
                        </div>
                    </div>
                    
                    <input type="hidden" id="data_agendamento" name="data_agendamento" required>
                    <div class="error-message" id="erro-data"></div>
                </div>

                <div class="step-actions">
                    <button type="button" class="btn btn-secondary" id="btnVoltarData">
                        <i class="fas fa-arrow-left"></i>
                        Voltar
                    </button>
                    <button type="button" class="btn btn-primary" id="btnProximoData" disabled>
                        Próximo
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- ETAPA 3: HORÁRIO -->
            <div class="form-step" data-step="3">
                <div class="step-header">
                    <h2>Escolha o Horário</h2>
                    <p>Horários disponíveis para <span id="dataEscolhida"></span></p>
                </div>

                <div class="input-group">
                    <div class="horarios-container">
                        <div class="horarios-grid" id="horariosGrid">
                            <!-- Horários carregados via JavaScript -->
                        </div>
                        
                        <div class="sem-horarios" id="semHorarios" style="display: none;">
                            <i class="fas fa-calendar-times"></i>
                            <h3>Sem horários disponíveis</h3>
                            <p>Escolha outro dia no calendário</p>
                        </div>
                    </div>
                    
                    <input type="hidden" id="horario_agendamento" name="horario_agendamento" required>
                    <div class="error-message" id="erro-horario"></div>
                </div>

                <div class="step-actions">
                    <button type="button" class="btn btn-secondary" id="btnVoltarHorario">
                        <i class="fas fa-arrow-left"></i>
                        Voltar
                    </button>
                    <button type="button" class="btn btn-primary" id="btnProximoHorario" disabled>
                        Próximo
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- ETAPA 4: DADOS PESSOAIS -->
            <div class="form-step" data-step="4">
                <div class="step-header">
                    <h2>Seus Dados</h2>
                    <p>Precisamos destas informações para confirmar seu agendamento</p>
                </div>

                <!-- Resumo do Agendamento -->
                <div class="resumo-agendamento" id="resumoAgendamento" style="display: none;">
                    <h3>Resumo do seu agendamento:</h3>
                    <div class="resumo-item">
                        <strong>Serviço:</strong> <span id="resumoServico"></span>
                    </div>
                    <div class="resumo-item">
                        <strong>Data:</strong> <span id="resumoData"></span>
                    </div>
                    <div class="resumo-item">
                        <strong>Horário:</strong> <span id="resumoHorario"></span>
                    </div>
                </div>

                <!-- Formulário de Dados -->
                <div class="input-group">
                    <div class="form-row">
                        <label for="nome">Nome Completo *</label>
                        <input type="text" id="nome" name="nome" required
                               placeholder="Seu nome completo"
                               autocomplete="name">
                        <div class="error-message" id="erro-nome"></div>
                    </div>

                    <div class="form-row">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required
                               placeholder="seu@email.com"
                               autocomplete="email">
                        <div class="error-message" id="erro-email"></div>
                    </div>

                    <div class="form-row">
                        <label for="telefone">Telefone *</label>
                        <input type="tel" id="telefone" name="telefone" required
                               placeholder="(11) 99999-9999"
                               autocomplete="tel">
                        <div class="error-message" id="erro-telefone"></div>
                    </div>

                    <div class="form-row">
                        <label for="observacoes">Observações (opcional)</label>
                        <textarea id="observacoes" name="observacoes" rows="3"
                                  placeholder="Alguma informação adicional..."></textarea>
                        <div class="error-message" id="erro-observacoes"></div>
                    </div>
                </div>

                <div class="step-actions">
                    <button type="button" class="btn btn-secondary" id="btnVoltarDados">
                        <i class="fas fa-arrow-left"></i>
                        Voltar
                    </button>
                    <button type="submit" class="btn btn-success btn-large" id="btnConfirmar" disabled>
                        <i class="fas fa-check"></i>
                        Confirmar Agendamento
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tela de Sucesso -->
    <div class="sucesso-container" id="sucessoContainer" style="display: none;">
        <div class="sucesso-content">
            <div class="sucesso-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2>Agendamento Confirmado!</h2>
            <p id="sucessoMensagem">Seu agendamento foi realizado com sucesso!</p>
            
            <div class="sucesso-detalhes" id="sucessoDetalhes">
                <!-- Detalhes do agendamento -->
            </div>

            <div class="sucesso-acoes">
                <button type="button" class="btn btn-primary btn-block" onclick="window.location.reload()">
                    <i class="fas fa-plus"></i>
                    Fazer Novo Agendamento
                </button>
                
                <div class="cta-conta">
                    <h3>Quer acompanhar seus agendamentos?</h3>
                    <p>Crie sua conta e tenha acesso a histórico, reagendamentos e muito mais!</p>
                    <a href="/cadastro" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus"></i>
                        Criar Minha Conta
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay" style="display: none;">
        <div class="loading-content">
            <div class="loading-spinner-large">
                <i class="fas fa-spinner fa-spin"></i>
            </div>
            <h3>Processando...</h3>
            <p id="loadingMessage">Aguarde um momento</p>
        </div>
    </div>
</div>

<!-- Benefícios do Agendamento -->
<div class="beneficios-section">
    <div class="container">
        <h3>Por que agendar online?</h3>
        <div class="beneficios-grid">
            <div class="beneficio-item">
                <i class="fas fa-clock"></i>
                <h4>Economia de Tempo</h4>
                <p>Agende em minutos, sem precisar ligar</p>
            </div>
            <div class="beneficio-item">
                <i class="fas fa-calendar-check"></i>
                <h4>Disponibilidade Real</h4>
                <p>Veja horários disponíveis em tempo real</p>
            </div>
            <div class="beneficio-item">
                <i class="fas fa-mobile-alt"></i>
                <h4>Confirmação Imediata</h4>
                <p>Receba confirmação por email e SMS</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/agendamento-publico.js') }}"></script>
@endpush