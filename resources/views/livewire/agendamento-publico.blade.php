<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-header text-center">
                    <h4 class="mb-0">Agendamento Online</h4>
                    @if(!$agendamentoCriado)
                        <div class="progress mt-3" style="height: 6px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ ($etapa / 2) * 100 }}%"></div>
                        </div>
                        <small class="text-muted">Etapa {{ $etapa }} de 2</small>
                    @endif
                </div>

                <div class="card-body">
                    @if($agendamentoCriado)
                        <!-- Confirmação do Agendamento -->
                        <div class="text-center">
                            <div class="mb-4">
                                <i class="fas fa-check-circle fa-4x text-success"></i>
                            </div>
                            <h5 class="text-success mb-3">Agendamento Realizado com Sucesso!</h5>
                            <div class="alert alert-info">
                                <h6 class="alert-heading">Detalhes do seu agendamento:</h6>
                                <hr>
                                <p class="mb-1"><strong>Serviço:</strong> {{ $servicos->find($servico_id)->nome ?? '' }}</p>
                                <p class="mb-1"><strong>Data:</strong> {{ Carbon\Carbon::parse($data_agendamento)->format('d/m/Y') }}</p>
                                <p class="mb-1"><strong>Horário:</strong> {{ $horario_agendamento }}</p>
                                <p class="mb-0"><strong>Status:</strong> <span class="badge bg-warning">Pendente para aprovação</span></p>
                            </div>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Importante:</strong> Seu agendamento está pendente para aprovação. 
                                Entraremos em contato em breve para confirmar.
                            </div>
                            <button class="btn btn-primary" wire:click="novoAgendamento">
                                <i class="fas fa-plus"></i> Fazer Novo Agendamento
                            </button>
                        </div>

                    @elseif($etapa == 1)
                        <!-- Etapa 1: Dados Pessoais -->
                        <h5 class="mb-4">Seus Dados Pessoais</h5>
                        
                        <form wire:submit.prevent="proximaEtapa">
                            <div class="row">
                                <div class="col-12">
                                    <label class="form-label">Nome Completo *</label>
                                    <input type="text" class="form-control @error('nome') is-invalid @enderror" 
                                           wire:model="nome" placeholder="Digite seu nome completo">
                                    @error('nome') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="form-label">E-mail *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           wire:model="email" placeholder="seu@email.com">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Telefone *</label>
                                    <input type="text" class="form-control @error('telefone') is-invalid @enderror" 
                                           wire:model="telefone" placeholder="(11) 99999-9999">
                                    @error('telefone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="form-label">Data de Nascimento</label>
                                    <input type="date" class="form-control @error('data_nascimento') is-invalid @enderror" 
                                           wire:model="data_nascimento">
                                    @error('data_nascimento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Gênero</label>
                                    <select class="form-select @error('genero') is-invalid @enderror" wire:model="genero">
                                        <option value="">Selecione...</option>
                                        <option value="masculino">Masculino</option>
                                        <option value="feminino">Feminino</option>
                                        <option value="outro">Outro</option>
                                    </select>
                                    @error('genero') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="form-label">CPF</label>
                                    <input type="text" class="form-control @error('cpf') is-invalid @enderror" 
                                           wire:model="cpf" placeholder="000.000.000-00">
                                    @error('cpf') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">CEP</label>
                                    <input type="text" class="form-control @error('cep') is-invalid @enderror" 
                                           wire:model="cep" placeholder="00000-000">
                                    @error('cep') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-8">
                                    <label class="form-label">Endereço</label>
                                    <input type="text" class="form-control @error('endereco') is-invalid @enderror" 
                                           wire:model="endereco" placeholder="Rua, Avenida...">
                                    @error('endereco') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Número</label>
                                    <input type="text" class="form-control @error('numero') is-invalid @enderror" 
                                           wire:model="numero" placeholder="123">
                                    @error('numero') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="form-label">Complemento</label>
                                <input type="text" class="form-control @error('complemento') is-invalid @enderror" 
                                       wire:model="complemento" placeholder="Apartamento, sala, etc...">
                                @error('complemento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary">
                                    Continuar <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </form>

                    @elseif($etapa == 2)
                        <!-- Etapa 2: Dados do Agendamento -->
                        <h5 class="mb-4">Agendar Consulta</h5>
                        
                        <form wire:submit.prevent="finalizarAgendamento">
                            <div class="mb-3">
                                <label class="form-label">Serviço *</label>
                                <select class="form-select @error('servico_id') is-invalid @enderror" wire:model="servico_id">
                                    <option value="">Selecione o serviço</option>
                                    @foreach($servicos as $servico)
                                        <option value="{{ $servico->id }}">
                                            {{ $servico->nome }} 
                                            ({{ $servico->duracao_formatada }})
                                            @if($servico->preco)
                                                - {{ $servico->preco_formatado }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('servico_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Data *</label>
                                    <input type="date" class="form-control @error('data_agendamento') is-invalid @enderror" 
                                           wire:model="data_agendamento" min="{{ date('Y-m-d') }}">
                                    @error('data_agendamento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Horário *</label>
                                    <select class="form-select @error('horario_agendamento') is-invalid @enderror" 
                                            wire:model="horario_agendamento">
                                        <option value="">Selecione o horário</option>
                                        @foreach($horariosDisponiveis as $horario)
                                            <option value="{{ $horario }}">{{ $horario }}</option>
                                        @endforeach
                                    </select>
                                    @error('horario_agendamento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    
                                    @if($data_agendamento && empty($horariosDisponiveis))
                                        <small class="text-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            @if(\Carbon\Carbon::parse($data_agendamento)->isWeekend())
                                                Não atendemos aos finais de semana. Selecione um dia útil.
                                            @else
                                                Nenhum horário disponível para esta data.
                                            @endif
                                        </small>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="form-label">Observações</label>
                                <textarea class="form-control @error('observacoes') is-invalid @enderror" 
                                          wire:model="observacoes" rows="3" 
                                          placeholder="Alguma observação sobre sua consulta..."></textarea>
                                @error('observacoes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle"></i>
                                <strong>Importante:</strong> Seu agendamento ficará pendente para aprovação. 
                                Entraremos em contato para confirmar.
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-success" 
                                        @if(empty($horariosDisponiveis) && $data_agendamento) disabled @endif>
                                    <i class="fas fa-calendar-check"></i> Confirmar Agendamento
                                </button>
                                <button type="button" class="btn btn-outline-secondary" wire:click="etapaAnterior">
                                    <i class="fas fa-arrow-left"></i> Voltar
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Informações de Contato -->
            <div class="card mt-4">
                <div class="card-body text-center">
                    <h6>Precisa de ajuda?</h6>
                    <p class="mb-2">
                        <i class="fas fa-phone text-primary"></i> 
                        <strong>(11) 99999-9999</strong>
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-envelope text-primary"></i> 
                        <strong>contato@clinica.com</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>