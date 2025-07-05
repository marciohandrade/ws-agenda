<div class="p-6">
    <h1 class="text-2xl font-bold">Teste - Novo Agendamento v6 (Carregamento de Horários)</h1>
    <p>Usuário: {{ Auth::user()->name }}</p>
    <p>Mês/Ano: {{ $this->nomesMeses[$mesAtual] }} {{ $anoAtual }}</p>
    
    @if($mensagemErro)
        <div class="bg-red-100 p-3 rounded text-red-700 mt-4">
            {{ $mensagemErro }}
        </div>
    @endif
    
    <!-- TESTE: Select de serviços -->
    <div class="mt-4 border p-4 rounded">
        <h3 class="font-bold mb-2">🧪 TESTE: Seleção de Serviço + Carregamento de Horários</h3>
        <select wire:model.live="servico_id" class="border p-2 rounded w-full">
            <option value="">Selecione um serviço...</option>
            @foreach($servicos as $servico)
                <option value="{{ $servico['id'] }}">{{ $servico['display_completo'] }}</option>
            @endforeach
        </select>
        
        @if($servico_id)
            <div class="mt-2 bg-blue-50 p-2 rounded">
                <p><strong>Serviço selecionado:</strong> {{ $servico_id }}</p>
            </div>
        @endif
    </div>
    
    <!-- TESTE: Grid do calendário simplificado -->
    <div class="mt-6 border p-4 rounded">
        <h3 class="font-bold mb-4">🧪 TESTE: Seleção de Data (com carregamento de horários)</h3>
        
        <div class="flex justify-between items-center mb-4">
            <button wire:click="mesAnterior" class="bg-blue-500 text-white px-3 py-1 rounded">
                ← Anterior
            </button>
            <h4 class="font-semibold">{{ $this->nomesMeses[$mesAtual] }} {{ $anoAtual }}</h4>
            <button wire:click="mesProximo" class="bg-blue-500 text-white px-3 py-1 rounded">
                Próximo →
            </button>
        </div>
        
        <!-- Grid simplificado só com dias disponíveis -->
        <div class="grid grid-cols-7 gap-1 text-center text-sm border">
            @foreach($this->dadosCalendario as $dia)
                @if(!$dia['isOutroMes'] && !$dia['isPassado'] && $dia['isDisponivel'])
                    <button wire:click="selecionarData('{{ $dia['data'] }}')"
                            class="p-2 border {{ $dia['isSelecionado'] ? 'bg-blue-600 text-white' : 'bg-white hover:bg-blue-100' }}">
                        {{ $dia['dia'] }}
                        @if($dia['isHoje']) 🔵 @endif
                    </button>
                @else
                    <div class="p-2 text-gray-400">{{ $dia['dia'] }}</div>
                @endif
            @endforeach
        </div>
        
        @if($dataSelecionada)
            <div class="mt-4 bg-green-50 p-3 rounded">
                <p><strong>⚠️ TESTE CRÍTICO: Data selecionada:</strong> {{ $dataSelecionada }}</p>
                <p><strong>Isso vai tentar carregar horários!</strong></p>
                
                @if($servico_id)
                    <div class="mt-2 bg-yellow-50 p-2 rounded text-sm">
                        <p><strong>🚨 Atenção:</strong> Serviço + Data selecionados = vai carregar horários do banco!</p>
                        <p>Se a página quebrar aqui, encontramos o problema!</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
    
    <!-- TESTE: Log de Debug -->
    <div class="mt-4 border p-4 rounded bg-gray-50">
        <h3 class="font-bold mb-2">📊 Debug Info:</h3>
        <p><strong>Serviço ID:</strong> {{ $servico_id ?: 'Nenhum' }}</p>
        <p><strong>Data selecionada:</strong> {{ $dataSelecionada ?: 'Nenhuma' }}</p>
        <p><strong>Data agendamento:</strong> {{ $dataAgendamento ?: 'Nenhuma' }}</p>
        <p><strong>Total dias calendário:</strong> {{ count($this->dadosCalendario) }}</p>
        <p><strong>Dias funcionamento:</strong> {{ implode(',', $diasFuncionamento) }}</p>
    </div>
</div>