@extends('layouts.clinica')

@section('title', 'Página não encontrada - Clínica Vida Saudável')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-green-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-lg w-full text-center">
        
        {{-- ✅ ILUSTRAÇÃO 404 --}}
        <div class="mb-8">
            <div class="mx-auto h-32 w-32 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full flex items-center justify-center mb-6">
                <i class="fas fa-search text-4xl text-blue-600"></i>
            </div>
            
            <div class="text-6xl font-bold text-gray-800 mb-2">404</div>
            <h1 class="text-2xl font-bold text-gray-800 mb-4">
                {{ $title ?? 'Página não encontrada' }}
            </h1>
            <p class="text-gray-600 text-lg mb-8">
                {{ $message ?? 'A página que você está procurando não existe ou foi movida.' }}
            </p>
        </div>

        {{-- ✅ AÇÕES RÁPIDAS --}}
        <div class="space-y-4 mb-8">
            <a href="{{ $back_url ?? '/' }}" 
               class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                <i class="fas fa-home mr-2"></i>
                {{ $back_text ?? 'Voltar ao Início' }}
            </a>
            
            @auth
                {{-- ✅ Links contextuais para usuários logados --}}
                @php $user = auth()->user(); @endphp
                
                @if($user->canAccessAdmin())
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="{{ route('agendamentos.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition-colors text-sm">
                            <i class="fas fa-calendar-check mr-2"></i>
                            Painel Administrativo
                        </a>
                        <a href="{{ route('agendar') }}" 
                           class="inline-flex items-center px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors text-sm">
                            <i class="fas fa-plus mr-2"></i>
                            Novo Agendamento
                        </a>
                    </div>
                @else
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="{{ route('usuario.meus-agendamentos') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm">
                            <i class="fas fa-calendar-check mr-2"></i>
                            Meus Agendamentos
                        </a>
                        <a href="{{ route('agendar') }}" 
                           class="inline-flex items-center px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors text-sm">
                            <i class="fas fa-plus mr-2"></i>
                            Novo Agendamento
                        </a>
                    </div>
                @endif
            @else
                {{-- ✅ Links para usuários não logados --}}
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('agendar') }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors text-sm">
                        <i class="fas fa-calendar-plus mr-2"></i>
                        Agendar Online
                    </a>
                    <a href="{{ route('login') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Fazer Login
                    </a>
                </div>
            @endauth
        </div>

        {{-- ✅ BUSCA RÁPIDA --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-search mr-2 text-blue-600"></i>
                O que você estava procurando?
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                <a href="/#sobre" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors">
                    <i class="fas fa-info-circle mr-3 text-blue-500"></i>
                    Sobre a Clínica
                </a>
                <a href="/#especialidades" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors">
                    <i class="fas fa-stethoscope mr-3 text-green-500"></i>
                    Especialidades
                </a>
                <a href="/#equipe" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors">
                    <i class="fas fa-user-md mr-3 text-purple-500"></i>
                    Nossa Equipe
                </a>
                <a href="/#contato" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors">
                    <i class="fas fa-phone mr-3 text-orange-500"></i>
                    Contato
                </a>
            </div>
        </div>

        {{-- ✅ INFORMAÇÕES DE CONTATO --}}
        <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
            <h3 class="text-lg font-semibold text-blue-800 mb-4">
                <i class="fas fa-headset mr-2"></i>
                Precisa de ajuda?
            </h3>
            
            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-center text-blue-700">
                    <i class="fas fa-phone mr-2"></i>
                    <span>(11) 3000-0000</span>
                </div>
                
                <div class="flex items-center justify-center text-blue-700">
                    <i class="fas fa-envelope mr-2"></i>
                    <a href="mailto:contato@clinicavida.com.br" class="hover:text-blue-900 transition-colors">
                        contato@clinicavida.com.br
                    </a>
                </div>
                
                <div class="flex justify-center space-x-4 pt-2">
                    <a href="https://wa.me/5511999999999" target="_blank" 
                       class="inline-flex items-center px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-xs">
                        <i class="fab fa-whatsapp mr-1"></i>
                        WhatsApp
                    </a>
                    <a href="#" target="_blank" 
                       class="inline-flex items-center px-3 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 transition-colors text-xs">
                        <i class="fab fa-instagram mr-1"></i>
                        Instagram
                    </a>
                </div>
            </div>
        </div>

        {{-- ✅ MENSAGEM DE DESENVOLVIMENTO (apenas em ambiente local) --}}
        @if(app()->environment('local', 'development'))
            <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-left">
                <h4 class="text-yellow-800 font-medium mb-2">
                    <i class="fas fa-code mr-2"></i>
                    Informações de Desenvolvimento
                </h4>
                <div class="text-xs text-yellow-700 space-y-1">
                    <p><strong>URL Solicitada:</strong> {{ request()->fullUrl() }}</p>
                    <p><strong>Método:</strong> {{ request()->method() }}</p>
                    <p><strong>IP:</strong> {{ request()->ip() }}</p>
                    <p><strong>User Agent:</strong> {{ Str::limit(request()->userAgent(), 50) }}</p>
                    @auth
                        <p><strong>Usuário:</strong> {{ auth()->user()->name }} ({{ auth()->user()->tipo_usuario }})</p>
                    @else
                        <p><strong>Usuário:</strong> Não logado</p>
                    @endauth
                </div>
            </div>
        @endif
    </div>
</div>

{{-- ✅ JAVASCRIPT PARA FUNCIONALIDADES AVANÇADAS --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ✅ EFEITO DE ZOOM NO ÍCONE 404
    const icon = document.querySelector('.fa-search');
    if (icon) {
        setInterval(() => {
            icon.style.transform = 'scale(1.1)';
            setTimeout(() => {
                icon.style.transform = 'scale(1)';
            }, 1000);
        }, 3000);
    }

    // ✅ TRACK DE PÁGINAS 404 (Analytics)
    if (typeof gtag !== 'undefined') {
        gtag('event', 'page_not_found', {
            'page_location': window.location.href,
            'page_title': '404 - Página não encontrada'
        });
    }

    // ✅ LOG PARA DESENVOLVIMENTO
    @if(app()->environment('local', 'development'))
        console.group('🔍 Página 404 - Debug Info');
        console.log('URL:', window.location.href);
        console.log('Referrer:', document.referrer || 'Direto');
        console.log('Timestamp:', new Date().toISOString());
        @auth
            console.log('Usuário:', '{{ auth()->user()->name }}');
            console.log('Tipo:', '{{ auth()->user()->tipo_usuario }}');
        @else
            console.log('Usuário:', 'Não logado');
        @endauth
        console.groupEnd();
    @endif

    // ✅ SUGESTÃO INTELIGENTE BASEADA NA URL
    const currentPath = window.location.pathname.toLowerCase();
    const suggestions = {
        'agenda': '/agendar',
        'agendamento': '/agendar',
        'consulta': '/agendar',
        'login': '/login',
        'perfil': '/perfil',
        'admin': '/dashboard',
        'painel': '/dashboard'
    };

    for (const [keyword, suggestion] of Object.entries(suggestions)) {
        if (currentPath.includes(keyword)) {
            const suggestionDiv = document.createElement('div');
            suggestionDiv.className = 'mt-4 p-3 bg-green-50 border border-green-200 rounded-lg';
            suggestionDiv.innerHTML = `
                <p class="text-green-800 text-sm">
                    <i class="fas fa-lightbulb mr-2"></i>
                    Você queria acessar: 
                    <a href="${suggestion}" class="font-medium hover:underline">${suggestion}</a>?
                </p>
            `;
            
            // Inserir antes do último elemento
            const lastElement = document.querySelector('.max-w-lg').lastElementChild;
            lastElement.parentNode.insertBefore(suggestionDiv, lastElement);
            break;
        }
    }

    // ✅ HISTÓRICO DE NAVEGAÇÃO (se disponível)
    if (history.length > 1 && document.referrer) {
        const backButton = document.createElement('button');
        backButton.className = 'mt-3 text-sm text-gray-600 hover:text-gray-800 transition-colors';
        backButton.innerHTML = '<i class="fas fa-arrow-left mr-1"></i>Voltar à página anterior';
        backButton.onclick = () => history.back();
        
        const actionsDiv = document.querySelector('.space-y-4');
        actionsDiv.appendChild(backButton);
    }
});
</script>
@endsection