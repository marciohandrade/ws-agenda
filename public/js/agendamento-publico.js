/**
 * AGENDAMENTO PÚBLICO - JavaScript Modular Mobile-First
 * Versão otimizada para performance e UX
 */

class AgendamentoPublico {
    constructor() {
        this.etapaAtual = 1;
        this.dadosAgendamento = {};
        this.mesAtual = new Date();
        this.diasFuncionamento = [];
        this.cache = new Map();
        
        this.init();
    }

    /**
     * Inicialização da aplicação
     */
    init() {
        this.bindEvents();
        this.carregarDadosIniciais();
        this.configurarMascaras();
        this.configurarValidacaoRealTime();
    }

    /**
     * Bind de todos os eventos
     */
    bindEvents() {
        // Navegação entre etapas
        document.getElementById('btnProximoServico')?.addEventListener('click', () => this.proximaEtapa());
        document.getElementById('btnProximoData')?.addEventListener('click', () => this.proximaEtapa());
        document.getElementById('btnProximoHorario')?.addEventListener('click', () => this.proximaEtapa());
        
        document.getElementById('btnVoltarData')?.addEventListener('click', () => this.etapaAnterior());
        document.getElementById('btnVoltarHorario')?.addEventListener('click', () => this.etapaAnterior());
        document.getElementById('btnVoltarDados')?.addEventListener('click', () => this.etapaAnterior());

        // Navegação do calendário
        document.getElementById('mesAnterior')?.addEventListener('click', () => this.mesAnterior());
        document.getElementById('mesProximo')?.addEventListener('click', () => this.mesProximo());

        // Submit do formulário
        document.getElementById('agendamentoForm')?.addEventListener('submit', (e) => this.salvarAgendamento(e));

        // Validação em tempo real
        ['nome', 'email', 'telefone'].forEach(campo => {
            document.getElementById(campo)?.addEventListener('input', () => this.validarCampo(campo));
            document.getElementById(campo)?.addEventListener('blur', () => this.validarCampo(campo));
        });
    }

    /**
     * Carrega dados iniciais necessários
     */
    async carregarDadosIniciais() {
        try {
            await Promise.all([
                this.carregarServicos(),
                this.carregarDiasFuncionamento()
            ]);
        } catch (error) {
            this.mostrarErro('Erro ao carregar dados iniciais. Recarregue a página.');
            console.error('Erro dados iniciais:', error);
        }
    }

    /**
     * Carrega lista de serviços
     */
    async carregarServicos() {
        const cacheKey = 'servicos';
        if (this.cache.has(cacheKey)) {
            this.renderizarServicos(this.cache.get(cacheKey));
            return;
        }

        try {
            const response = await fetch('/api/agendamento/servicos');
            const data = await response.json();

            if (data.success) {
                this.cache.set(cacheKey, data.servicos);
                this.renderizarServicos(data.servicos);
            } else {
                throw new Error('Falha ao carregar serviços');
            }
        } catch (error) {
            console.error('Erro ao carregar serviços:', error);
            this.mostrarErroServicos();
        }
    }

    /**
     * Renderiza os serviços na interface
     */
    renderizarServicos(servicos) {
        const container = document.getElementById('servicosGrid');
        
        if (!servicos || servicos.length === 0) {
            container.innerHTML = `
                <div class="sem-servicos">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Nenhum serviço disponível no momento</p>
                </div>
            `;
            return;
        }

        container.innerHTML = servicos.map(servico => `
            <div class="servico-card" data-servico-id="${servico.id}" onclick="agendamento.selecionarServico(${servico.id})">
                <div class="servico-nome">${servico.nome}</div>
                <div class="servico-detalhes">
                    <span class="servico-duracao">${servico.duracao}min</span>
                    <span class="servico-preco">R$ ${servico.preco}</span>
                </div>
                ${servico.descricao ? `<div class="servico-descricao">${servico.descricao}</div>` : ''}
            </div>
        `).join('');
    }

    /**
     * Seleciona um serviço
     */
    selecionarServico(servicoId) {
        // Remove seleção anterior
        document.querySelectorAll('.servico-card').forEach(card => {
            card.classList.remove('selected');
        });

        // Adiciona nova seleção
        const card = document.querySelector(`[data-servico-id="${servicoId}"]`);
        card.classList.add('selected');

        // Salva dados
        this.dadosAgendamento.servico_id = servicoId;
        document.getElementById('servico_id').value = servicoId;

        // Habilita próximo botão
        document.getElementById('btnProximoServico').disabled = false;

        // Feedback tátil em mobile
        if (navigator.vibrate) {
            navigator.vibrate(50);
        }
    }

    /**
     * Carrega dias de funcionamento
     */
    async carregarDiasFuncionamento() {
        try {
            const response = await fetch('/api/agendamento/dias-funcionamento');
            const data = await response.json();

            if (data.success) {
                this.diasFuncionamento = data.dias;
            } else {
                // Fallback para seg-sex
                this.diasFuncionamento = [1, 2, 3, 4, 5];
            }
        } catch (error) {
            console.error('Erro ao carregar dias funcionamento:', error);
            this.diasFuncionamento = [1, 2, 3, 4, 5];
        }
    }

    /**
     * Navega para próxima etapa
     */
    proximaEtapa() {
        if (this.etapaAtual < 4) {
            this.atualizarProgressBar(this.etapaAtual + 1);
            this.mostrarEtapa(this.etapaAtual + 1);
            this.etapaAtual++;

            // Ações específicas por etapa
            if (this.etapaAtual === 2) {
                this.inicializarCalendario();
            } else if (this.etapaAtual === 4) {
                this.mostrarResumo();
            }
        }
    }

    /**
     * Volta para etapa anterior
     */
    etapaAnterior() {
        if (this.etapaAtual > 1) {
            this.atualizarProgressBar(this.etapaAtual - 1);
            this.mostrarEtapa(this.etapaAtual - 1);
            this.etapaAtual--;
        }
    }

    /**
     * Atualiza a barra de progresso
     */
    atualizarProgressBar(etapa) {
        document.querySelectorAll('.progress-step').forEach((step, index) => {
            const stepNumber = index + 1;
            step.classList.remove('active', 'completed');
            
            if (stepNumber === etapa) {
                step.classList.add('active');
            } else if (stepNumber < etapa) {
                step.classList.add('completed');
            }
        });
    }

    /**
     * Mostra uma etapa específica
     */
    mostrarEtapa(numero) {
        document.querySelectorAll('.form-step').forEach(step => {
            step.classList.remove('active');
        });
        
        const etapaAlvo = document.querySelector(`[data-step="${numero}"]`);
        if (etapaAlvo) {
            etapaAlvo.classList.add('active');
            
            // Scroll suave para o topo
            etapaAlvo.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
            });
        }
    }

    /**
     * Inicializa o calendário
     */
    inicializarCalendario() {
        const hoje = new Date();
        if (this.mesAtual < hoje) {
            this.mesAtual = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
        }
        this.renderizarCalendario();
    }

    /**
     * Navega para mês anterior
     */
    mesAnterior() {
        const hoje = new Date();
        const novoMes = new Date(this.mesAtual.getFullYear(), this.mesAtual.getMonth() - 1, 1);
        
        // Não permite voltar para o passado
        if (novoMes >= new Date(hoje.getFullYear(), hoje.getMonth(), 1)) {
            this.mesAtual = novoMes;
            this.renderizarCalendario();
        }
    }

    /**
     * Navega para próximo mês
     */
    mesProximo() {
        const limite = new Date();
        limite.setMonth(limite.getMonth() + 2); // Máximo 2 meses à frente
        
        const novoMes = new Date(this.mesAtual.getFullYear(), this.mesAtual.getMonth() + 1, 1);
        
        if (novoMes <= limite) {
            this.mesAtual = novoMes;
            this.renderizarCalendario();
        }
    }

    /**
     * Renderiza o calendário
     */
    async renderizarCalendario() {
        const mesAno = document.getElementById('mesAno');
        const container = document.getElementById('calendarioGrid');

        // Atualizar título
        const meses = [
            'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
            'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
        ];
        mesAno.textContent = `${meses[this.mesAtual.getMonth()]} ${this.mesAtual.getFullYear()}`;

        // Mostrar loading
        container.innerHTML = '<div class="loading-spinner" style="grid-column: 1 / -1;"><i class="fas fa-spinner fa-spin"></i> Carregando...</div>';

        try {
            // Gerar estrutura do calendário
            const primeiroDia = new Date(this.mesAtual.getFullYear(), this.mesAtual.getMonth(), 1);
            const ultimoDia = new Date(this.mesAtual.getFullYear(), this.mesAtual.getMonth() + 1, 0);
            const hoje = new Date();

            let html = '';

            // Dias do mês anterior para completar a semana
            const diasAntes = primeiroDia.getDay();
            for (let i = diasAntes - 1; i >= 0; i--) {
                const dia = new Date(primeiroDia);
                dia.setDate(dia.getDate() - (i + 1));
                html += this.criarDiaCalendario(dia, true, false);
            }

            // Dias do mês atual
            for (let dia = 1; dia <= ultimoDia.getDate(); dia++) {
                const data = new Date(this.mesAtual.getFullYear(), this.mesAtual.getMonth(), dia);
                const disponivel = await this.isDiaDisponivel(data, hoje);
                html += this.criarDiaCalendario(data, false, disponivel);
            }

            container.innerHTML = html;

        } catch (error) {
            console.error('Erro ao renderizar calendário:', error);
            container.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; color: #f44336;">Erro ao carregar calendário</div>';
        }
    }

    /**
     * Cria um dia do calendário
     */
    criarDiaCalendario(data, outroMes, disponivel) {
        const classes = ['dia-calendario'];
        let onclick = '';

        if (outroMes) {
            classes.push('dia-outro-mes', 'dia-indisponivel');
        } else if (disponivel) {
            classes.push('dia-disponivel');
            onclick = `onclick="agendamento.selecionarData('${data.toISOString().split('T')[0]}')"`;
        } else {
            classes.push('dia-indisponivel');
        }

        // Marcar se é o dia selecionado
        if (this.dadosAgendamento.data_agendamento === data.toISOString().split('T')[0]) {
            classes.push('dia-selecionado');
        }

        return `<div class="${classes.join(' ')}" ${onclick}>${data.getDate()}</div>`;
    }

    /**
     * Verifica se um dia está disponível
     */
    async isDiaDisponivel(data, hoje) {
        // Não pode ser no passado
        if (data < hoje.setHours(0, 0, 0, 0)) return false;

        // Verificar se está nos dias de funcionamento
        const diaSemana = data.getDay();
        if (!this.diasFuncionamento.includes(diaSemana)) return false;

        // Verificar bloqueios específicos via API
        try {
            const dataStr = data.toISOString().split('T')[0];
            const cacheKey = `dia_${dataStr}`;
            
            if (this.cache.has(cacheKey)) {
                return this.cache.get(cacheKey);
            }

            const response = await fetch(`/api/agendamento/dia-disponivel/${dataStr}`);
            const result = await response.json();
            
            this.cache.set(cacheKey, result.disponivel);
            return result.disponivel;

        } catch (error) {
            console.error('Erro ao verificar disponibilidade:', error);
            return false;
        }
    }

    /**
     * Seleciona uma data
     */
    async selecionarData(dataStr) {
        // Remove seleção anterior
        document.querySelectorAll('.dia-selecionado').forEach(dia => {
            dia.classList.remove('dia-selecionado');
            dia.classList.add('dia-disponivel');
        });

        // Adiciona nova seleção
        const elemento = event.target;
        elemento.classList.remove('dia-disponivel');
        elemento.classList.add('dia-selecionado');

        // Salva dados
        this.dadosAgendamento.data_agendamento = dataStr;
        document.getElementById('data_agendamento').value = dataStr;

        // Habilita próximo botão
        document.getElementById('btnProximoData').disabled = false;

        // Feedback tátil
        if (navigator.vibrate) {
            navigator.vibrate(50);
        }

        // Carrega horários automaticamente
        await this.carregarHorarios(dataStr);
    }

    /**
     * Carrega horários disponíveis para uma data
     */
    async carregarHorarios(data) {
        const container = document.getElementById('horariosGrid');
        const semHorarios = document.getElementById('semHorarios');
        
        // Mostrar loading
        container.innerHTML = '<div class="loading-spinner" style="grid-column: 1 / -1;"><i class="fas fa-spinner fa-spin"></i> Carregando horários...</div>';
        semHorarios.style.display = 'none';

        try {
            const cacheKey = `horarios_${data}`;
            let result;

            if (this.cache.has(cacheKey)) {
                result = this.cache.get(cacheKey);
            } else {
                const response = await fetch(`/api/agendamento/horarios/${data}`);
                result = await response.json();
                
                // Cache por 3 minutos
                setTimeout(() => this.cache.delete(cacheKey), 180000);
                this.cache.set(cacheKey, result);
            }

            container.innerHTML = '';

            if (!result.success || result.horarios.length === 0) {
                semHorarios.style.display = 'block';
                return;
            }

            semHorarios.style.display = 'none';
            this.renderizarHorarios(result.horarios);

        } catch (error) {
            console.error('Erro ao carregar horários:', error);
            container.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; color: #f44336;">Erro ao carregar horários</div>';
        }
    }

    /**
     * Renderiza os horários na interface
     */
    renderizarHorarios(horarios) {
        const container = document.getElementById('horariosGrid');
        
        container.innerHTML = horarios.map(horario => {
            const classes = ['horario-card'];
            let onclick = '';

            if (horario.disponivel) {
                classes.push('disponivel');
                onclick = `onclick="agendamento.selecionarHorario('${horario.value}')"`;
            } else {
                classes.push('ocupado');
            }

            // Marcar se é o horário selecionado
            if (this.dadosAgendamento.horario_agendamento === horario.value) {
                classes.push('selecionado');
            }

            return `
                <div class="${classes.join(' ')}" ${onclick} 
                     ${!horario.disponivel ? 'title="Horário ocupado"' : ''}>
                    ${horario.display}
                </div>
            `;
        }).join('');
    }

    /**
     * Seleciona um horário
     */
    selecionarHorario(horario) {
        // Remove seleção anterior
        document.querySelectorAll('.horario-card.selecionado').forEach(card => {
            card.classList.remove('selecionado');
            card.classList.add('disponivel');
        });

        // Adiciona nova seleção
        const elemento = event.target;
        elemento.classList.remove('disponivel');
        elemento.classList.add('selecionado');

        // Salva dados
        this.dadosAgendamento.horario_agendamento = horario;
        document.getElementById('horario_agendamento').value = horario;

        // Habilita próximo botão
        document.getElementById('btnProximoHorario').disabled = false;

        // Feedback tátil
        if (navigator.vibrate) {
            navigator.vibrate(50);
        }
    }

    /**
     * Mostra o resumo do agendamento
     */
    mostrarResumo() {
        const resumo = document.getElementById('resumoAgendamento');
        const servicoSelecionado = document.querySelector('.servico-card.selected');
        
        if (servicoSelecionado && this.dadosAgendamento.data_agendamento && this.dadosAgendamento.horario_agendamento) {
            // Formatar data
            const data = new Date(this.dadosAgendamento.data_agendamento);
            const dataFormatada = data.toLocaleDateString('pt-BR');

            // Preencher resumo
            document.getElementById('resumoServico').textContent = servicoSelecionado.querySelector('.servico-nome').textContent;
            document.getElementById('resumoData').textContent = dataFormatada;
            document.getElementById('resumoHorario').textContent = this.dadosAgendamento.horario_agendamento;

            // Atualizar data escolhida no header da etapa 3
            document.getElementById('dataEscolhida').textContent = dataFormatada;

            resumo.style.display = 'block';
        }

        this.validarFormularioCompleto();
    }

    /**
     * Configura máscaras nos campos
     */
    configurarMascaras() {
        const telefoneInput = document.getElementById('telefone');
        
        if (telefoneInput) {
            telefoneInput.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\D/g, '');
                
                if (value.length <= 11) {
                    if (value.length <= 10) {
                        value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
                    } else {
                        value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                    }
                }
                
                e.target.value = value;
            });
        }
    }

    /**
     * Configura validação em tempo real
     */
    configurarValidacaoRealTime() {
        ['nome', 'email', 'telefone'].forEach(campo => {
            const input = document.getElementById(campo);
            if (input) {
                input.addEventListener('input', () => {
                    this.limparErro(campo);
                    this.validarFormularioCompleto();
                });
            }
        });
    }

    /**
     * Valida um campo específico
     */
    validarCampo(campo) {
        const input = document.getElementById(campo);
        const valor = input.value.trim();
        let valido = true;
        let mensagem = '';

        switch (campo) {
            case 'nome':
                if (!valor) {
                    mensagem = 'Nome é obrigatório';
                    valido = false;
                } else if (valor.length < 2) {
                    mensagem = 'Nome deve ter pelo menos 2 caracteres';
                    valido = false;
                } else if (!/^[a-zA-ZÀ-ÿ\s]+$/.test(valor)) {
                    mensagem = 'Nome deve conter apenas letras';
                    valido = false;
                }
                break;

            case 'email':
                if (!valor) {
                    mensagem = 'Email é obrigatório';
                    valido = false;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor)) {
                    mensagem = 'Email deve ter um formato válido';
                    valido = false;
                }
                break;

            case 'telefone':
                if (!valor) {
                    mensagem = 'Telefone é obrigatório';
                    valido = false;
                } else if (valor.replace(/\D/g, '').length < 10) {
                    mensagem = 'Telefone deve ter pelo menos 10 dígitos';
                    valido = false;
                }
                break;
        }

        if (!valido) {
            this.mostrarErroCampo(campo, mensagem);
        } else {
            this.limparErro(campo);
        }

        return valido;
    }

    /**
     * Mostra erro em um campo específico
     */
    mostrarErroCampo(campo, mensagem) {
        const input = document.getElementById(campo);
        const errorDiv = document.getElementById(`erro-${campo}`);
        
        if (input && errorDiv) {
            input.style.borderColor = '#f44336';
            errorDiv.textContent = mensagem;
            errorDiv.classList.add('show');
        }
    }

    /**
     * Limpa erro de um campo
     */
    limparErro(campo) {
        const input = document.getElementById(campo);
        const errorDiv = document.getElementById(`erro-${campo}`);
        
        if (input && errorDiv) {
            input.style.borderColor = '#e0e0e0';
            errorDiv.classList.remove('show');
        }
    }

    /**
     * Valida se o formulário está completo
     */
    validarFormularioCompleto() {
        const nome = document.getElementById('nome')?.value.trim();
        const email = document.getElementById('email')?.value.trim();
        const telefone = document.getElementById('telefone')?.value.trim();
        
        const completo = nome && email && telefone && 
                        this.dadosAgendamento.servico_id &&
                        this.dadosAgendamento.data_agendamento &&
                        this.dadosAgendamento.horario_agendamento;

        const btnConfirmar = document.getElementById('btnConfirmar');
        if (btnConfirmar) {
            btnConfirmar.disabled = !completo;
        }
    }

    /**
     * Salva o agendamento
     */
    async salvarAgendamento(event) {
        event.preventDefault();

        // Validar todos os campos
        const camposValidos = ['nome', 'email', 'telefone'].every(campo => this.validarCampo(campo));
        
        if (!camposValidos) {
            this.mostrarErro('Por favor, corrija os campos destacados');
            return;
        }

        // Mostrar loading
        this.mostrarLoading('Confirmando seu agendamento...');

        try {
            // Preparar dados
            const formData = new FormData(document.getElementById('agendamentoForm'));
            const dados = Object.fromEntries(formData.entries());

            // Fazer requisição
            const response = await fetch('/api/agendamento/salvar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': dados._token
                },
                body: JSON.stringify(dados)
            });

            const result = await response.json();

            if (result.success) {
                this.mostrarSucesso(result.message, result.agendamento);
                
                // Limpar cache para atualizar disponibilidade
                this.cache.clear();
            } else {
                this.mostrarErro(result.message || 'Erro ao salvar agendamento');
                
                // Se horário foi ocupado, recarregar horários
                if (result.codigo === 'HORARIO_OCUPADO') {
                    await this.carregarHorarios(this.dadosAgendamento.data_agendamento);
                }
            }

        } catch (error) {
            console.error('Erro ao salvar agendamento:', error);
            this.mostrarErro('Erro de conexão. Tente novamente.');
        } finally {
            this.esconderLoading();
        }
    }

    /**
     * Mostra loading overlay
     */
    mostrarLoading(mensagem = 'Carregando...') {
        const overlay = document.getElementById('loadingOverlay');
        const message = document.getElementById('loadingMessage');
        
        if (overlay && message) {
            message.textContent = mensagem;
            overlay.style.display = 'flex';
        }
    }

    /**
     * Esconde loading overlay
     */
    esconderLoading() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }

    /**
     * Mostra tela de sucesso
     */
    mostrarSucesso(mensagem, agendamento) {
        const container = document.getElementById('sucessoContainer');
        const mensagemEl = document.getElementById('sucessoMensagem');
        const detalhesEl = document.getElementById('sucessoDetalhes');

        if (container && mensagemEl) {
            mensagemEl.textContent = mensagem;
            
            if (agendamento && detalhesEl) {
                detalhesEl.innerHTML = `
                    <div><strong>Serviço:</strong> ${agendamento.servico}</div>
                    <div><strong>Data:</strong> ${agendamento.data}</div>
                    <div><strong>Horário:</strong> ${agendamento.horario}</div>
                    <div><strong>Cliente:</strong> ${agendamento.cliente}</div>
                `;
            }

            container.style.display = 'flex';
        }
    }

    /**
     * Mostra mensagem de erro
     */
    mostrarErro(mensagem) {
        // Toast notification simples
        const toast = document.createElement('div');
        toast.className = 'error-toast';
        toast.innerHTML = `
            <i class="fas fa-exclamation-triangle"></i>
            <span>${mensagem}</span>
        `;
        
        // Adicionar CSS inline para o toast
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #f44336;
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
            animation: slideDown 0.3s ease-out;
        `;

        document.body.appendChild(toast);

        // Remover após 5 segundos
        setTimeout(() => {
            toast.style.animation = 'slideUp 0.3s ease-out';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 5000);
    }

    /**
     * Mostra erro nos serviços
     */
    mostrarErroServicos() {
        const container = document.getElementById('servicosGrid');
        container.innerHTML = `
            <div class="erro-servicos">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Erro ao carregar serviços</h3>
                <p>Tente recarregar a página</p>
                <button onclick="window.location.reload()" class="btn btn-primary">
                    <i class="fas fa-refresh"></i> Recarregar
                </button>
            </div>
        `;
    }
}

// Inicializar aplicação quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.agendamento = new AgendamentoPublico();
});

// Adicionar CSS para animações do toast
const toastStyles = document.createElement('style');
toastStyles.textContent = `
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateX(-50%) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    }
    
    @keyframes slideUp {
        from {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
        to {
            opacity: 0;
            transform: translateX(-50%) translateY(-20px);
        }
    }
    
    .erro-servicos {
        text-align: center;
        padding: 40px 20px;
        color: #666;
        grid-column: 1 / -1;
    }
    
    .erro-servicos i {
        font-size: 3rem;
        color: #f44336;
        margin-bottom: 16px;
    }
    
    .erro-servicos h3 {
        margin: 0 0 8px 0;
        color: #333;
    }
    
    .erro-servicos p {
        margin: 0 0 20px 0;
        font-size: 0.9rem;
    }
`;
document.head.appendChild(toastStyles);