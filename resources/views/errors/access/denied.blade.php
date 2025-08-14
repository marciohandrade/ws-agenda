<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚫 Acesso Negado - {{ config('app.name', 'Sistema') }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Animação suave */
        .bounce-in {
            animation: bounceIn 0.8s ease-out;
        }
        
        @keyframes bounceIn {
            0% { 
                transform: scale(0.3) translateY(-50px); 
                opacity: 0; 
            }
            50% { 
                transform: scale(1.05) translateY(0); 
            }
            70% { 
                transform: scale(0.95); 
            }
            100% { 
                transform: scale(1); 
                opacity: 1; 
            }
        }

        /* Gradiente de fundo */
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        /* Garantir altura total da tela */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body class="gradient-bg">

    <!-- Container principal centralizado -->
    <div class="min-h-screen flex items-center justify-center p-4">
        
        <!-- Card principal -->
        <div class="bg-white rounded-2xl shadow-2xl bounce-in max-w-md w-full overflow-hidden">
            
            <!-- Header com ícone -->
            <div class="bg-red-500 p-8 text-center">
                <div class="w-20 h-20 mx-auto mb-4 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-shield-alt text-3xl text-white"></i>
                </div>
                <h1 class="text-2xl font-bold text-white">Acesso Negado</h1>
                <p class="text-red-100 mt-2">Área restrita</p>
            </div>

            <!-- Conteúdo principal -->
            <div class="p-8">
                
                <!-- Saudação -->
                <div class="text-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">
                        Olá, {{ $userName }}!
                    </h2>
                    <p class="text-gray-600">
                        Você não tem permissão para acessar esta área do sistema.
                    </p>
                </div>

                <!-- Área tentada -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-red-500 mt-1 mr-3 flex-shrink-0"></i>
                        <div class="flex-grow">
                            <h3 class="font-medium text-red-800 mb-1">Tentativa de acesso:</h3>
                            <p class="text-red-700 text-sm">
                                <code class="bg-red-100 px-2 py-1 rounded text-xs">{{ $attemptedPath }}</code>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Áreas permitidas -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-8">
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink-0"></i>
                        <div class="flex-grow">
                            <h3 class="font-medium text-green-800 mb-1">Suas áreas permitidas:</h3>
                            <p class="text-green-700 text-sm">{{ $allowedAreas }}</p>
                        </div>
                    </div>
                </div>

                <!-- Botões de ação -->
                <div class="space-y-3">
                    
                    <!-- Botão principal -->
                    <a href="{{ $redirectUrl }}" 
                       class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-blue-700 transition-all duration-200 flex items-center justify-center group">
                        <i class="fas fa-home mr-2 group-hover:scale-110 transition-transform"></i>
                        Ir para minha área
                    </a>

                    <!-- Botão secundário -->
                    <button onclick="history.back()" 
                            class="w-full bg-gray-500 text-white py-3 px-6 rounded-lg font-medium hover:bg-gray-600 transition-all duration-200 flex items-center justify-center group">
                        <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                        Voltar
                    </button>

                    <!-- Link para logout -->
                    <div class="text-center pt-4 border-t border-gray-200">
                        <a href="/logout" class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                            <i class="fas fa-sign-out-alt mr-1"></i>
                            Fazer logout
                        </a>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-8 py-4 text-center border-t">
                <p class="text-xs text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Se você acredita que isto é um erro, entre em contato com o administrador.
                </p>
            </div>
        </div>
    </div>

    <!-- Auto-redirect após 30 segundos (opcional) -->
    <script>
        // Contador visual (opcional)
        let countdown = 30;
        
        // Atualizar título com contador
        function updateTitle() {
            document.title = `🚫 Acesso Negado (${countdown}s) - {{ config('app.name', 'Sistema') }}`;
        }
        
        // Iniciar contador em 25 segundos (dar tempo para ler)
        setTimeout(function() {
            const countdownInterval = setInterval(function() {
                countdown--;
                updateTitle();
                
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    
                    // Mostrar aviso
                    const notice = document.createElement('div');
                    notice.className = 'fixed bottom-4 right-4 bg-blue-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center';
                    notice.innerHTML = '<i class="fas fa-info-circle mr-2"></i>Redirecionando automaticamente...';
                    document.body.appendChild(notice);
                    
                    // Redirecionar
                    setTimeout(function() {
                        window.location.href = '{{ $redirectUrl }}';
                    }, 2000);
                }
            }, 1000);
        }, 25000); // Começa o countdown aos 25 segundos
    </script>

</body>
</html>