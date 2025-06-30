<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulário Responsivo</title>    
</head>
<body class="bg-gray-100 p-4">
    <div class="max-w-6xl mx-auto p-4 sm:p-6 bg-white rounded shadow">
        <h2 class="text-xl sm:text-2xl font-bold mb-4">Painel Administrativo / Cadastro de Agendamentos</h2>

        <form class="w-full space-y-6 sm:space-y-8">
        
            <!-- Linha 1: Cliente e Serviço - Responsive -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
                    <select class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecione um cliente</option>
                        <option value="1">João Silva - (11) 99999-9999</option>
                        <option value="2">Maria Santos - (11) 88888-8888</option>
                        <option value="3">Pedro Oliveira - (11) 77777-7777</option>
                    </select>
                    <span class="text-red-500 text-xs mt-1 hidden">Este campo é obrigatório</span>
                </div>

                <div class="w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Serviço *</label>
                    <select class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecione um serviço</option>
                        <option value="1">Consulta Médica (30 min)</option>
                        <option value="2">Exame de Rotina (45 min)</option>
                        <option value="3">Procedimento Cirúrgico (60 min)</option>
                    </select>
                    <span class="text-red-500 text-xs mt-1 hidden">Este campo é obrigatório</span>
                </div>
            </div>

            <!-- Linha 2: Data, Horário e Status - Responsive Stack -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data do Agendamento *</label>
                    <input type="date" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           min="2025-06-25">
                    <span class="text-red-500 text-xs mt-1 hidden">Este campo é obrigatório</span>
                </div>

                <div class="w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Horário *</label>
                    <input type="time" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <span class="text-red-500 text-xs mt-1 hidden">Este campo é obrigatório</span>
                </div>

                <div class="w-full sm:col-span-2 lg:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                    <select class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="pendente">Pendente</option>
                        <option value="confirmado">Confirmado</option>
                        <option value="concluido">Concluído</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                    <span class="text-red-500 text-xs mt-1 hidden">Este campo é obrigatório</span>
                </div>
            </div>

            <!-- Linha 3: Observações - Full Width -->
            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                <textarea class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                          rows="3" 
                          placeholder="Observações sobre o agendamento..."></textarea>
                <span class="text-red-500 text-xs mt-1 hidden">Erro nas observações</span>
            </div>

            <!-- ✅ Largura fixa para garantir tamanho maior -->
          <div class="flex justify-center items-center gap-3 pt-4">
            @if ($editando)
                <button type="button" wire:click="resetarFormulario"
                        class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition-colors text-sm font-medium w-36">
                    Cancelar
                </button>
            @endif

            <button type="submit" 
                    class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-900 transition-colors text-sm font-medium w-36">
                {{ $editando ? 'Atualizar' : 'Cadastrar' }}
            </button>
        </div>

        </form>

        <!-- Filtros de Pesquisa - Melhorados -->
        <div class="mt-8 sm:mt-10 mb-6 space-y-6">
            <h3 class="text-lg font-semibold">Filtros de Pesquisa</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pesquisar Cliente</label>
                    <input type="text" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           placeholder="Nome do cliente...">
                </div>
                
                <div class="w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data</label>
                    <input type="date" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos os status</option>
                        <option value="pendente">Pendente</option>
                        <option value="confirmado">Confirmado</option>
                        <option value="concluido">Concluído</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
                
                <div class="w-full flex justify-center items-end">
                    <button class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition-colors text-sm font-medium w-36">
                        Limpar
                    </button>
                </div>                
            </div>
        </div>

        <!-- Preview da Lista (Responsiva) -->
        <h3 class="text-xl font-bold mb-4">Lista de Agendamentos</h3>
        <p class="text-sm text-gray-500 mb-4 lg:hidden">Deslize para o lado →</p>

        <div class="overflow-x-auto w-full">
            <table class="w-full min-w-[900px] text-sm table-auto border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-4 text-left font-medium text-gray-700">Cliente</th>
                        <th class="p-4 text-left font-medium text-gray-700">Serviço</th>
                        <th class="p-4 text-left font-medium text-gray-700">Data & Hora</th>
                        <th class="p-4 text-left font-medium text-gray-700">Status</th>
                        <th class="p-4 text-left font-medium text-gray-700">Observações</th>
                        <th class="p-4 text-left font-medium text-gray-700">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-t border-gray-200 hover:bg-gray-50">
                        <td class="p-4">
                            <div class="font-medium text-gray-900">João Silva</div>
                            <div class="text-xs text-gray-500">(11) 99999-9999</div>
                            <span class="inline-block bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded mt-1">
                                Auto-cadastro
                            </span>
                        </td>
                        <td class="p-4">
                            <div class="font-medium text-gray-900">Consulta Médica</div>
                            <div class="text-xs text-gray-500">30 minutos</div>
                        </td>
                        <td class="p-4">
                            <div class="font-medium text-gray-900">25/06/2025 14:30</div>
                        </td>
                        <td class="p-4">
                            <span class="inline-block bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">
                                Pendente
                            </span>
                        </td>
                        <td class="p-4">
                            <div class="text-gray-700">Primeira consulta do paciente</div>
                        </td>
                        <td class="p-4">
                            <div class="flex flex-wrap gap-1">
                                <button class="text-green-600 hover:underline text-xs px-1 py-1">
                                    Confirmar
                                </button>
                                <button class="text-blue-600 hover:underline text-xs px-1 py-1">
                                    Concluir
                                </button>
                                <button class="text-orange-600 hover:underline text-xs px-1 py-1">
                                    Cancelar
                                </button>
                                <button class="text-blue-600 hover:underline text-xs px-1 py-1">
                                    Editar
                                </button>
                                <button class="text-red-600 hover:underline text-xs px-1 py-1">
                                    Excluir
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>