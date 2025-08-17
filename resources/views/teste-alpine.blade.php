<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Teste Alpine.js - {{ config('app.name') }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js (teste se está funcionando) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Se o teste falhar, descomente a linha abaixo -->
    <!-- <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> -->
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    
    <div class="max-w-2xl mx-auto p-8">
        
        <!-- TESTE PRINCIPAL -->
        <div x-data="{ 
            teste: false, 
            contador: 0,
            messages: [],
            addMessage(msg) { 
                this.messages.push(msg);
                setTimeout(() => this.messages.shift(), 3000);
            }
        }" class="bg-white rounded-lg shadow-lg p-6">
            
            <!-- Cabeçalho -->
            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">🧪 Teste Alpine.js</h1>
                <p class="text-gray-600 mt-2">Vamos verificar se o Alpine.js está funcionando corretamente</p>
            </div>
            
            <!-- STATUS ALPINE -->
            <div class="mb-6 p-4 rounded-lg" :class="Alpine ? 'bg-green-100 border border-green-300' : 'bg-red-100 border border-red-300'">
                <div class="flex items-center gap-3">
                    <div class="text-2xl" x-text="Alpine ? '✅' : '❌'"></div>
                    <div>
                        <h3 class="font-bold" x-text="Alpine ? 'Alpine.js Detectado!' : 'Alpine.js NÃO Detectado!'"></h3>
                        <p class="text-sm" x-text="Alpine ? 'Versão: ' + (Alpine.version || 'Desconhecida') : 'Alpine não está carregado'"></p>
                    </div>
                </div>
            </div>
            
            <!-- TESTES INTERATIVOS -->
            <div class="space-y-4">
                
                <!-- Teste 1: Show/Hide -->
                <div class="p-4 bg-blue-50 rounded-lg">
                    <h4 class="font-semibold text-blue-800 mb-2">Teste 1: Mostrar/Ocultar</h4>
                    <button 
                        @click="teste = !teste; addMessage('Teste 1: ' + (teste ? 'Mostrado' : 'Ocultado'))"
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors"
                    >
                        <span x-text="teste ? 'Ocultar' : 'Mostrar'"></span> Conteúdo
                    </button>
                    
                    <div x-show="teste" x-transition class="mt-3 p-3 bg-green-100 text-green-800 rounded">
                        🎉 Parabéns! O x-show e x-transition estão funcionando!
                    </div>
                </div>
                
                <!-- Teste 2: Contador -->
                <div class="p-4 bg-purple-50 rounded-lg">
                    <h4 class="font-semibold text-purple-800 mb-2">Teste 2: Reatividade</h4>
                    <div class="flex items-center gap-3">
                        <button 
                            @click="contador--; addMessage('Contador: ' + contador)"
                            class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600"
                        >
                            -
                        </button>
                        <span class="text-2xl font-bold px-4 py-2 bg-white rounded border" x-text="contador"></span>
                        <button 
                            @click="contador++; addMessage('Contador: ' + contador)"
                            class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600"
                        >
                            +
                        </button>
                    </div>
                </div>
                
                <!-- Teste 3: Input -->
                <div class="p-4 bg-yellow-50 rounded-lg">
                    <h4 class="font-semibold text-yellow-800 mb-2">Teste 3: Two-way Binding</h4>
                    <input 
                        x-model="teste_input"
                        type="text" 
                        placeholder="Digite algo aqui..."
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                    <p class="mt-2 text-sm text-gray-600">
                        Você digitou: <strong x-text="teste_input || 'nada ainda'"></strong>
                    </p>
                </div>
                
                <!-- Teste 4: Loop -->
                <div class="p-4 bg-indigo-50 rounded-lg">
                    <h4 class="font-semibold text-indigo-800 mb-2">Teste 4: Loops</h4>
                    <div class="grid grid-cols-2 gap-2">
                        <template x-for="i in 6" :key="i">
                            <div 
                                class="p-2 bg-white rounded border text-center cursor-pointer hover:bg-indigo-100 transition-colors"
                                x-text="'Item ' + i"
                                @click="addMessage('Clicou no item ' + i)"
                            ></div>
                        </template>
                    </div>
                </div>
            </div>
            
            <!-- MENSAGENS DE FEEDBACK -->
            <div class="mt-6">
                <h4 class="font-semibold text-gray-800 mb-2">📢 Log de Testes:</h4>
                <div class="h-24 overflow-y-auto bg-gray-50 rounded p-3 text-sm">
                    <template x-for="(message, index) in messages" :key="index">
                        <div x-text="'🔸 ' + message" class="text-gray-700"></div>
                    </template>
                    <div x-show="messages.length === 0" class="text-gray-500 italic">
                        Nenhum teste executado ainda...
                    </div>
                </div>
            </div>
            
            <!-- INFORMAÇÕES TÉCNICAS -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="font-semibold text-gray-800 mb-2">🔧 Informações Técnicas:</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <strong>Alpine Carregado:</strong> 
                        <span x-text="!!window.Alpine ? 'Sim' : 'Não'"></span>
                    </div>
                    <div>
                        <strong>Versão Alpine:</strong> 
                        <span x-text="window.Alpine?.version || 'N/A'"></span>
                    </div>
                    <div>
                        <strong>Elementos com x-data:</strong> 
                        <span x-text="document.querySelectorAll('[x-data]').length"></span>
                    </div>
                    <div>
                        <strong>Browser:</strong> 
                        <span x-text="navigator.userAgent.includes('Chrome') ? 'Chrome' : navigator.userAgent.includes('Firefox') ? 'Firefox' : 'Outro'"></span>
                    </div>
                </div>
            </div>
            
            <!-- BOTÕES DE AÇÃO -->
            <div class="mt-6 flex gap-3 justify-center">
                <button 
                    @click="location.href = '/painel/agendamentos'"
                    class="px-6 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors"
                >
                    ← Voltar aos Agendamentos
                </button>
                
                <button 
                    @click="location.reload()"
                    class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors"
                >
                    🔄 Recarregar Teste
                </button>
            </div>
        </div>
        
        <!-- INSTRUÇÕES -->
        <div class="mt-6 p-4 bg-blue-100 rounded-lg text-blue-800 text-sm">
            <h4 class="font-semibold mb-2">📋 Como Interpretar os Resultados:</h4>
            <ul class="space-y-1">
                <li><strong>✅ Verde:</strong> Alpine funcionando - mantenha o CDN comentado</li>
                <li><strong>❌ Vermelho:</strong> Alpine com problema - descomente o CDN no layout</li>
                <li><strong>Testes interativos:</strong> Se funcionarem, tudo está OK</li>
                <li><strong>Se nada funcionar:</strong> Problema de carregamento do Alpine</li>
            </ul>
        </div>
    </div>
    
    <!-- CONSOLE DEBUG -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.group('🧪 Debug Alpine.js');
            console.log('✅ DOM Carregado');
            console.log('Alpine global:', !!window.Alpine);
            console.log('Alpine version:', window.Alpine?.version || 'N/A');
            console.log('Elementos x-data:', document.querySelectorAll('[x-data]').length);
            console.log('User Agent:', navigator.userAgent);
            console.groupEnd();
            
            // Teste adicional após 1 segundo
            setTimeout(() => {
                console.group('🔄 Teste Tardio Alpine.js');
                console.log('Alpine após delay:', !!window.Alpine);
                console.log('Elementos x-data ativos:', document.querySelectorAll('[x-data]').length);
                console.groupEnd();
            }, 1000);
        });
    </script>
</body>
</html>