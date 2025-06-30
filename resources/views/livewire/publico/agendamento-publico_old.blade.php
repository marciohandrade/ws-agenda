<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        
        <!-- Card Principal -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            
            <!-- Header com Progress Bar -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6">
                <h2 class="text-2xl font-bold text-center mb-4">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Agendamento Online
                </h2>
                
                <!-- Progress Bar (apenas se não finalizado) -->
                @if(!$agendamentoCriado)
                    <div class="w-full bg-blue-500 rounded-full h-2 mb-2">
                        <div class="bg-white h-2 rounded-full transition-all duration-300" 
                             style="width: {{ ($etapa / 2) * 100 }}%"></div>
                    </div>
                    <div class="text-center text-blue-100 text-sm">
                        Etapa {{ $etapa }} de 2
                    </div>
                @endif
            </div>

            <div class="p-6">
                
                <!-- Mensagens de Erro/Sucesso -->
                @if (session()->has('erro'))
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('erro') }}
                    </div>
                @endif

                <!-- Confirmação do Agendamento -->
                @if($agendamentoCriado)
                    <div class="text-center">
                        <div class="mb-6">
                            <i class="fas fa-check-circle text-6xl text-green-500"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-green-600 mb-4">
                            Agendamento Realizado com Sucesso!
                        </h3>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <h4 class="font-semibold text-blue-800 mb-3">Detalhes do seu agendamento:</h4>
                            <div class="space-y-2 text-left">
                                <p><strong>Serviço:</strong> {{ $agendamentoSalvo['servico_nome'] ?? '' }}</p>
                                <p><strong>Data:</strong> {{ $agendamentoSalvo['data_formatada'] ?? '' }}</p>
                                <p><strong>Horário:</strong> {{ $agendamentoSalvo['horario'] ?? '' }}</p>
                                <p><strong>Status:</strong> 
                                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-sm">
                                        {{ $agendamentoSalvo['status'] ?? '' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                            <strong>Importante:</strong> Seu agendamento está pendente para aprovação. 
                            Entraremos em contato em breve para confirmar.
                        </div>
                        
                        <button wire:click="novoAgendamento" 
                                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i> Fazer Novo Agendamento
                        </button>
                    </div>

                <!-- Etapa 1: Dados Pessoais -->
                @elseif($etapa === 1)
                    <div>
                        <h3 class="text-xl font-semibold mb-6 text-gray-800">
                            <i class="fas fa-user mr-2 text-blue-600"></i>
                            Seus Dados Pessoais
                        </h3>
                        
                        <form wire:submit.prevent="proximaEtapa" class="w-full space-y-6">
                            
                            <!-- Nome (linha única) -->
                            <div class="flex flex-col">
                                <label class="text-sm font-medium text-gray-700 mb-2">Nome Completo *</label>
                                <input type="text" wire:model="nome" 
                                       class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('nome') border-red-500 @enderror"
                                       placeholder="Digite seu nome completo">
                                @error('nome') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Email e Telefone -->
                            <div class="flex flex-wrap gap-4">
                                <div class="flex-1 min-w-[220px]">
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">E-mail *</label>
                                    <input type="email" wire:model="email" 
                                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror"
                                           placeholder="seu@email.com">
                                    @error('email') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex-1 min-w-[180px]">
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">Telefone *</label>
                                    <input type="text" wire:model="telefone" x-mask="(99) 99999-9999" maxlength="15"
                                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('telefone') border-red-500 @enderror"
                                           placeholder="(11) 99999-9999">
                                    @error('telefone') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Data de Nascimento e Gênero -->
                            <div class="flex flex-wrap gap-4">
                                <div class="flex-1 min-w-[180px]">
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">Data de Nascimento</label>
                                    <input type="date" wire:model="data_nascimento"
                                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('data_nascimento') border-red-500 @enderror"
                                           max="{{ date('Y-m-d') }}" min="1900-01-01">
                                    @error('data_nascimento') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex-1 min-w-[180px]">
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">Gênero</label>
                                    <select wire:model="genero" 
                                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('genero') border-red-500 @enderror">
                                        <option value="">Selecione</option>
                                        <option value="Masculino">Masculino</option>
                                        <option value="Feminino">Feminino</option>
                                        <option value="Não-binário">Não-binário</option>
                                        <option value="Prefere não informar">Prefere não informar</option>
                                    </select>
                                    @error('genero') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- CPF e CEP -->
                            <div class="flex flex-wrap gap-4">
                                <div class="flex-1 min-w-[180px]">
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">CPF</label>
                                    <input type="text" wire:model="cpf" x-mask="999.999.999-99"
                                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('cpf') border-red-500 @enderror"
                                           placeholder="000.000.000-00">
                                    @error('cpf') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex-1 min-w-[180px]">
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">CEP</label>
                                    <input type="text" wire:model="cep" x-mask="99999-999"
                                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('cep') border-red-500 @enderror"
                                           placeholder="00000-000">
                                    @error('cep') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Endereço -->
                            <div class="flex flex-col">
                                <label class="text-sm font-medium text-gray-700 mb-2">Endereço</label>
                                <input type="text" wire:model="endereco" maxlength="80"
                                       class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('endereco') border-red-500 @enderror"
                                       placeholder="Rua, Avenida...">
                                @error('endereco') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Número e Complemento -->
                            <div class="flex flex-wrap gap-4">
                                <div class="flex-1 min-w-[120px]">
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">Número</label>
                                    <input type="text" wire:model="numero" maxlength="10"
                                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('numero') border-red-500 @enderror"
                                           placeholder="123">
                                    @error('numero') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex-1 min-w-[180px]">
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">Complemento</label>
                                    <input type="text" wire:model="complemento" maxlength="30"
                                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('complemento') border-red-500 @enderror"
                                           placeholder="Apto, Bloco, Fundos...">
                                    @error('complemento') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Botão -->
                            <div class="flex justify-center mt-8">
                                <button type="submit" 
                                        class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                                    Continuar <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                <!-- Etapa 2: Dados do Agendamento -->
                @elseif($etapa === 2)
                    <div>
                        <h3 class="text-xl font-semibold mb-6 text-gray-800">
                            <i class="fas fa-calendar-check mr-2 text-blue-600"></i>
                            Agendar Consulta
                        </h3>
                        
                        <form wire:submit.prevent="finalizarAgendamento" class="w-full space-y-6">
                            
                            <!-- Serviço -->
                            <div class="flex flex-col">
                                <label class="text-sm font-medium text-gray-700 mb-2">Serviço *</label>
                                <select wire:model="servico_id"
                                        class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('servico_id') border-red-500 @enderror">
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
                                @error('servico_id') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Data e Horário -->
                            <div class="flex flex-wrap gap-4">
                                <div class="flex-1 min-w-[180px]">
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">Data *</label>
                                    <input type="date" wire:model="data_agendamento"
                                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('data_agendamento') border-red-500 @enderror"
                                           min="{{ date('Y-m-d') }}">
                                    @error('data_agendamento') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex-1 min-w-[180px]">
                                    <label class="text-sm font-medium text-gray-700 mb-2 block">Horário *</label>
                                    <select wire:model="horario_agendamento" 
                                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('horario_agendamento') border-red-500 @enderror">
                                        <option value="">Selecione o horário</option>
                                        @foreach($horariosDisponiveis as $horario)
                                            <option value="{{ $horario }}">{{ $horario }}</option>
                                        @endforeach
                                    </select>
                                    @error('horario_agendamento') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                                    
                                    @if($data_agendamento && empty($horariosDisponiveis))
                                        <div class="text-yellow-600 text-sm mt-1">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            @if($this->isWeekend($data_agendamento))
                                                Não atendemos aos finais de semana. Selecione um dia útil.
                                            @else
                                                Nenhum horário disponível para esta data.
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Observações -->
                            <div class="flex flex-col">
                                <label class="text-sm font-medium text-gray-700 mb-2">Observações</label>
                                <textarea wire:model="observacoes" rows="3"
                                          class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none @error('observacoes') border-red-500 @enderror"
                                          placeholder="Alguma observação sobre sua consulta..."></textarea>
                                @error('observacoes') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Aviso -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                <strong>Importante:</strong> Seu agendamento ficará pendente para aprovação. 
                                Entraremos em contato para confirmar.
                            </div>

                            <!-- Botões -->
                            <div class="flex flex-col sm:flex-row gap-4 justify-center mt-8">
                                <button type="button" wire:click="etapaAnterior" 
                                        class="order-2 sm:order-1 bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition-colors">
                                    <i class="fas fa-arrow-left mr-2"></i> Voltar
                                </button>
                                <button type="submit" 
                                        @if($data_agendamento && empty($horariosDisponiveis)) disabled @endif
                                        class="order-1 sm:order-2 px-6 py-3 rounded-lg transition-colors
                                               {{ ($data_agendamento && empty($horariosDisponiveis)) ? 
                                                  'bg-gray-400 text-gray-600 cursor-not-allowed' : 
                                                  'bg-green-600 hover:bg-green-700 text-white' }}">
                                    <i class="fas fa-calendar-check mr-2"></i> Confirmar Agendamento
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

            </div>
        </div>

        <!-- Informações de Contato -->
        <div class="bg-white rounded-lg shadow-lg mt-6 p-6 text-center">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">Precisa de ajuda?</h4>
            <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                <div class="flex items-center">
                    <i class="fas fa-phone text-blue-600 mr-2"></i>
                    <strong class="text-gray-700">(11) 99999-9999</strong>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-envelope text-blue-600 mr-2"></i>
                    <strong class="text-gray-700">contato@clinica.com</strong>
                </div>
            </div>
        </div>

    </div>
</div>