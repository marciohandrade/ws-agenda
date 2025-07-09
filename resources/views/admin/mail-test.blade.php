<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Teste de Email - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Teste de Configuração de Email</h1>
            
            <!-- Configurações Atuais -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">📧 Configurações SMTP Atuais</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Host SMTP:</label>
                        <p class="text-gray-800">{{ $config['host'] ?? 'Não configurado' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Porta:</label>
                        <p class="text-gray-800">{{ $config['port'] ?? 'Não configurado' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Usuário:</label>
                        <p class="text-gray-800">{{ $config['username'] ?? 'Não configurado' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Criptografia:</label>
                        <p class="text-gray-800">{{ strtoupper($config['encryption'] ?? 'Não configurado') }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600">Email Remetente:</label>
                        <p class="text-gray-800">{{ $config['from_address'] ?? 'Não configurado' }}</p>
                    </div>
                </div>
            </div>

            <!-- Teste de Conexão -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">🔌 Teste de Conexão SMTP</h2>
                <button id="test-connection" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Testar Conexão
                </button>
                <div id="connection-result" class="mt-4"></div>
            </div>

            <!-- Envio de Email de Teste -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">📤 Enviar Email de Teste</h2>
                <form id="email-test-form" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="test-name" class="block text-sm font-medium text-gray-700">Nome:</label>
                            <input type="text" id="test-name" name="name" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2">
                        </div>
                        <div>
                            <label for="test-email" class="block text-sm font-medium text-gray-700">Email:</label>
                            <input type="email" id="test-email" name="email" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2">
                        </div>
                    </div>
                    <div class="flex space-x-4">
                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Enviar Email de Recuperação de Senha
                        </button>
                        <button type="button" id="send-appointment" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                            Enviar Email de Agendamento
                        </button>
                    </div>
                </form>
                <div id="email-result" class="mt-4"></div>
            </div>

            <!-- Logs de Email -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">📋 Logs de Email</h2>
                <button id="view-logs" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mb-4">
                    Visualizar Logs
                </button>
                <div id="logs-container" class="bg-gray-50 p-4 rounded max-h-96 overflow-y-auto hidden">
                    <pre id="logs-content" class="text-sm text-gray-700"></pre>
                </div>
            </div>

            <!-- Voltar ao Painel -->
            <div class="mt-8 text-center">
                <a href="{{ route('agendamento.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded">
                    ← Voltar ao Painel
                </a>
            </div>
        </div>
    </div>

    <script>
        // Configurar CSRF token para requisições AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Teste de conexão
        $('#test-connection').click(function() {
            const button = $(this);
            const result = $('#connection-result');
            
            button.prop('disabled', true).text('Testando...');
            result.html('<div class="text-blue-600">Testando conexão SMTP...</div>');
            
            $.post('/admin/test-email/connection')
                .done(function(data) {
                    if (data.success) {
                        result.html('<div class="text-green-600 font-semibold">✅ ' + data.message + '</div>');
                    } else {
                        result.html('<div class="text-red-600 font-semibold">❌ ' + data.message + '</div>');
                    }
                })
                .fail(function() {
                    result.html('<div class="text-red-600 font-semibold">❌ Erro na requisição</div>');
                })
                .always(function() {
                    button.prop('disabled', false).text('Testar Conexão');
                });
        });

        // Envio de email de teste
        $('#email-test-form').submit(function(e) {
            e.preventDefault();
            
            const form = $(this);
            const result = $('#email-result');
            const submitBtn = form.find('button[type="submit"]');
            
            submitBtn.prop('disabled', true).text('Enviando...');
            result.html('<div class="text-blue-600">Enviando email de teste...</div>');
            
            $.post('/admin/test-email/send', form.serialize())
                .done(function(data) {
                    if (data.success) {
                        result.html('<div class="text-green-600 font-semibold">✅ ' + data.message + '</div>');
                    } else {
                        result.html('<div class="text-red-600 font-semibold">❌ ' + data.message + '</div>');
                    }
                })
                .fail(function() {
                    result.html('<div class="text-red-600 font-semibold">❌ Erro na requisição</div>');
                })
                .always(function() {
                    submitBtn.prop('disabled', false).text('Enviar Email de Recuperação de Senha');
                });
        });

        // Envio de email de agendamento
        $('#send-appointment').click(function() {
            const name = $('#test-name').val();
            const email = $('#test-email').val();
            
            if (!name || !email) {
                alert('Preencha nome e email primeiro');
                return;
            }
            
            const button = $(this);
            const result = $('#email-result');
            
            button.prop('disabled', true).text('Enviando...');
            result.html('<div class="text-blue-600">Enviando email de agendamento...</div>');
            
            $.post('/admin/test-email/send-appointment', {name: name, email: email})
                .done(function(data) {
                    if (data.success) {
                        result.html('<div class="text-green-600 font-semibold">✅ ' + data.message + '</div>');
                    } else {
                        result.html('<div class="text-red-600 font-semibold">❌ ' + data.message + '</div>');
                    }
                })
                .fail(function() {
                    result.html('<div class="text-red-600 font-semibold">❌ Erro na requisição</div>');
                })
                .always(function() {
                    button.prop('disabled', false).text('Enviar Email de Agendamento');
                });
        });

        // Visualizar logs
        $('#view-logs').click(function() {
            const button = $(this);
            const container = $('#logs-container');
            const content = $('#logs-content');
            
            button.prop('disabled', true).text('Carregando...');
            
            $.post('/admin/test-email/logs')
                .done(function(data) {
                    if (data.success) {
                        content.text(data.logs.join('\n'));
                        container.removeClass('hidden');
                    } else {
                        alert('Erro ao carregar logs: ' + data.message);
                    }
                })
                .fail(function() {
                    alert('Erro na requisição de logs');
                })
                .always(function() {
                    button.prop('disabled', false).text('Visualizar Logs');
                });
        });
    </script>
</body>
</html>