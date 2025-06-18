<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Agendamento Online' }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Livewire Styles -->
    @livewireStyles
    
    <!-- Custom Styles -->
    <style>
        /* Melhorias visuais customizadas */
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        /* Loading state */
        .loading {
            pointer-events: none;
            opacity: 0.7;
        }
        
        /* Animações suaves */
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <!-- Header Simples -->
    <header class="bg-white shadow-sm border-b">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <i class="fas fa-clinic-medical text-blue-600 text-2xl mr-3"></i>
                    <h1 class="text-xl font-bold text-gray-800">Clínica Médica</h1>
                </div>
                <div class="text-sm text-gray-600">
                    <i class="fas fa-phone mr-1"></i>
                    (11) 99999-9999
                </div>
            </div>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="min-h-screen">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-3 gap-8">
                
                <!-- Informações de Contato -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contato</h3>
                    <div class="space-y-2">
                        <p class="flex items-center">
                            <i class="fas fa-phone mr-3 text-blue-400"></i>
                            (11) 99999-9999
                        </p>
                        <p class="flex items-center">
                            <i class="fas fa-envelope mr-3 text-blue-400"></i>
                            contato@clinica.com
                        </p>
                        <p class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-3 text-blue-400"></i>
                            Rua da Clínica, 123 - São Paulo/SP
                        </p>
                    </div>
                </div>

                <!-- Horário de Funcionamento -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Horário de Funcionamento</h3>
                    <div class="space-y-2 text-sm">
                        <p><strong>Segunda a Sexta:</strong> 08:00 às 18:00</p>
                        <p><strong>Sábado:</strong> 08:00 às 12:00</p>
                        <p><strong>Domingo:</strong> Fechado</p>
                    </div>
                </div>

                <!-- Informações Adicionais -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Informações</h3>
                    <div class="space-y-2 text-sm">
                        <p>✓ Agendamento online 24h</p>
                        <p>✓ Confirmação por WhatsApp</p>
                        <p>✓ Profissionais qualificados</p>
                        <p>✓ Ambiente seguro e higienizado</p>
                    </div>
                </div>
            </div>
            
            <!-- Copyright -->
            <div class="border-t border-gray-700 mt-8 pt-4 text-center text-sm text-gray-400">
                <p>&copy; {{ date('Y') }} Clínica Médica. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Livewire Scripts -->
    @livewireScripts
    
    <!-- Máscaras de Input -->
    <script>
        // Máscaras usando Alpine.js
        document.addEventListener('alpine:init', () => {
            Alpine.directive('mask', (el, { expression }, { evaluate }) => {
                let mask = evaluate(expression);
                
                el.addEventListener('input', (e) => {
                    let value = e.target.value.replace(/\D/g, '');
                    let formattedValue = '';
                    
                    if (mask === '(99) 99999-9999') {
                        // Telefone
                        if (value.length >= 11) {
                            formattedValue = `(${value.substr(0,2)}) ${value.substr(2,5)}-${value.substr(7,4)}`;
                        } else if (value.length >= 7) {
                            formattedValue = `(${value.substr(0,2)}) ${value.substr(2,4)}-${value.substr(6)}`;
                        } else if (value.length >= 3) {
                            formattedValue = `(${value.substr(0,2)}) ${value.substr(2)}`;
                        } else if (value.length >= 1) {
                            formattedValue = `(${value}`;
                        }
                    } else if (mask === '999.999.999-99') {
                        // CPF
                        if (value.length >= 9) {
                            formattedValue = `${value.substr(0,3)}.${value.substr(3,3)}.${value.substr(6,3)}-${value.substr(9,2)}`;
                        } else if (value.length >= 6) {
                            formattedValue = `${value.substr(0,3)}.${value.substr(3,3)}.${value.substr(6)}`;
                        } else if (value.length >= 3) {
                            formattedValue = `${value.substr(0,3)}.${value.substr(3)}`;
                        } else {
                            formattedValue = value;
                        }
                    } else if (mask === '99999-999') {
                        // CEP
                        if (value.length >= 5) {
                            formattedValue = `${value.substr(0,5)}-${value.substr(5,3)}`;
                        } else {
                            formattedValue = value;
                        }
                    }
                    
                    e.target.value = formattedValue;
                });
            });
        });
    </script>

    <!-- Notificações Toast (opcional) -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>
    
    <script>
        // Sistema simples de notificações
        window.showToast = function(message, type = 'info') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-yellow-500',
                info: 'bg-blue-500'
            };
            
            toast.className = `${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full opacity-0`;
            toast.textContent = message;
            
            container.appendChild(toast);
            
            // Animar entrada
            setTimeout(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
            }, 100);
            
            // Remover após 5 segundos
            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => {
                    container.removeChild(toast);
                }, 300);
            }, 5000);
        };
    </script>
</body>
</html>