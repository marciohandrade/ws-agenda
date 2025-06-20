<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Clínica Vida Saudável')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @livewireStyles
    @yield('head')
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    <!-- ✅ SÓ O MENU FICA AQUI -->
    <header class="fixed top-0 w-full bg-white shadow z-50">
        <nav class="bg-white shadow-md fixed w-full top-0 left-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="text-xl font-bold text-blue-800">Clínica Vida</a>
                </div>
                <div class="-mr-2 flex items-center sm:hidden">
                    <!-- Botão do menu mobile -->
                    <button type="button" id="mobile-menu-button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-800 hover:text-white hover:bg-blue-600 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path class="inline" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    </button>
                </div>
                <!-- Menu Desktop -->
                <div class="hidden sm:flex sm:items-center space-x-6">
                    <a href="/#inicio" class="menu-link px-3 py-2 rounded transition" data-section="inicio">Início</a>
                    <a href="/#sobre" class="menu-link px-3 py-2 rounded transition" data-section="sobre">Sobre</a>
                    <a href="/#especialidades" class="menu-link px-3 py-2 rounded transition" data-section="especialidades">Especialidades</a>
                    <a href="/#equipe" class="menu-link px-3 py-2 rounded transition" data-section="equipe">Equipe</a>
                    
                    <!-- Botão Agendar - sempre destacado -->
                    <a href="/agendar" class="menu-link px-4 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition font-medium {{ request()->is('agendar*') ? 'bg-blue-800' : '' }}">
                        <i class="fas fa-calendar-plus mr-2"></i>Agendar Online
                    </a>
                    
                    <a href="/#contato" class="menu-link px-3 py-2 rounded transition" data-section="contato">Contato</a>
                </div>
                </div>
            </div>

            <!-- Menu Mobile -->
            <div id="mobile-menu" class="sm:hidden hidden px-4 pt-2 pb-4 space-y-1 bg-white shadow">
            <a href="/#inicio" class="menu-link block px-3 py-2 rounded transition" data-section="inicio">Início</a>
            <a href="/#sobre" class="menu-link block px-3 py-2 rounded transition" data-section="sobre">Sobre</a>
            <a href="/#especialidades" class="menu-link block px-3 py-2 rounded transition" data-section="especialidades">Especialidades</a>
            <a href="/#equipe" class="menu-link block px-3 py-2 rounded transition" data-section="equipe">Equipe</a>
            
            <!-- Botão Agendar Mobile -->
            <a href="/agendar" class="menu-link block px-4 py-3 bg-blue-600 text-white rounded-lg transition font-medium text-center {{ request()->is('agendar*') ? 'bg-blue-800' : '' }}">
                <i class="fas fa-calendar-plus mr-2"></i>Agendar Online
            </a>
            
            <a href="/#contato" class="menu-link block px-3 py-2 rounded transition" data-section="contato">Contato</a>
            </div>
        </nav>
    </header>

    <!-- ❗ AQUI VAI O CONTEÚDO DAS PÁGINAS -->
    <main class="pt-16">
        @yield('content')
    </main>

    <!-- ✅ SÓ O FOOTER FICA AQUI -->
    <footer class="bg-blue-800 text-white py-6 text-center">
        <div class="mb-2">
            <a href="#" class="mx-2 text-white hover:text-gray-200"><i class="fab fa-whatsapp fa-lg"></i></a>
            <a href="#" class="mx-2 text-white hover:text-gray-200"><i class="fab fa-instagram fa-lg"></i></a>
            <a href="#" class="mx-2 text-white hover:text-gray-200"><i class="fab fa-facebook fa-lg"></i></a>
        </div>
        <p class="text-sm">&copy; 2025 Clínica Vida Saudável. Todos os direitos reservados.</p>
    </footer>
    
    <!-- ✅ BOTÃO WHATSAPP FICA AQUI -->
    <a href="https://wa.me/5511999999999?text=Olá!%20Gostaria%20de%20agendar%20uma%20consulta%20com%20a%20Clínica%20Vida%20Saudável." 
    class="fixed bottom-4 right-4 flex items-center bg-green-500 text-white px-4 py-2 rounded-full shadow-lg hover:bg-green-600 transition z-50"
    target="_blank"
    aria-label="WhatsApp">
        <i class="fab fa-whatsapp fa-lg mr-2"></i>
        <span class="font-medium">Fale conosco</span>
    </a>

    @livewireScripts
    
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        // Lógica do menu mobile
        const menuButton = document.getElementById("mobile-menu-button");
        const mobileMenu = document.getElementById("mobile-menu");

        if (menuButton && mobileMenu) {
        menuButton.addEventListener("click", () => {
            mobileMenu.classList.toggle("hidden");
        });
        }

        // ✅ MENU ATIVO - VERSÃO FINAL CORRIGIDA
        const sections = document.querySelectorAll("section[id]");
        const navLinks = document.querySelectorAll(".menu-link");

        function onScroll() {
            const scrollPos = window.scrollY + 100;

            // Só funciona se existirem seções na página (página home)
            if (sections.length > 0) {
                sections.forEach(section => {
                    const sectionId = section.getAttribute("id");
                    
                    // Busca link correspondente usando data-section
                    const correspondingLink = Array.from(navLinks).find(link => {
                        return link.getAttribute("data-section") === sectionId;
                    });

                    if (
                        scrollPos >= section.offsetTop &&
                        scrollPos < section.offsetTop + section.offsetHeight
                    ) {
                        // Remove destaque de todos os links com data-section
                        navLinks.forEach(link => {
                            if (link.hasAttribute("data-section")) {
                                link.classList.remove("bg-blue-100", "text-blue-600", "font-bold");
                            }
                        });
                        
                        // Adiciona destaque no link correspondente
                        if (correspondingLink) {
                            correspondingLink.classList.add("bg-blue-100", "text-blue-600", "font-bold");
                        }
                    }
                });
            }
        }

        // ✅ CLICK NOS LINKS - SCROLL SUAVE SEM RECARREGAR
        navLinks.forEach(link => {
            link.addEventListener("click", function (e) {
                const href = this.getAttribute("href");
                
                // Se for link de seção (/#section)
                if (href.startsWith("/#")) {
                    e.preventDefault(); // ✅ IMPEDE RECARREGAMENTO
                    
                    const targetId = href.replace('/#', '');
                    
                    // Se estiver em outra página, vai para home primeiro
                    if (window.location.pathname !== "/") {
                        window.location.href = href;
                        return;
                    }
                    
                    // Se já estiver na home, faz scroll suave
                    const target = document.getElementById(targetId);
                    if (target) {
                        // Remove destaque de todos os links de seção
                        navLinks.forEach(l => {
                            if (l.hasAttribute("data-section")) {
                                l.classList.remove("bg-blue-100", "text-blue-600", "font-bold");
                            }
                        });
                        
                        // Adiciona destaque no link clicado
                        this.classList.add("bg-blue-100", "text-blue-600", "font-bold");
                        
                        // Scroll suave
                        target.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
        });

        // ✅ DESTACAR MENU BASEADO NA URL E HASH
        function highlightCurrentPage() {
            const currentPath = window.location.pathname;
            const currentHash = window.location.hash;
            
            // Se estiver na página de agendamento
            if (currentPath === "/agendar") {
                navLinks.forEach(link => {
                    const linkPath = link.getAttribute("href");
                    if (linkPath === "/agendar") {
                        link.classList.add("bg-blue-800", "text-white"); // Já tem esse estilo no botão
                    }
                });
                return;
            }
            
            // Se estiver na home
            if (currentPath === "/") {
                // Se tem hash na URL (#sobre, #equipe, etc)
                if (currentHash) {
                    const sectionId = currentHash.replace('#', '');
                    const correspondingLink = Array.from(navLinks).find(link => {
                        return link.getAttribute("data-section") === sectionId;
                    });
                    
                    if (correspondingLink) {
                        correspondingLink.classList.add("bg-blue-100", "text-blue-600", "font-bold");
                    }
                } else {
                    // Se não tem hash, destaca "Início"
                    const inicioLink = Array.from(navLinks).find(link => {
                        return link.getAttribute("data-section") === "inicio";
                    });
                    
                    if (inicioLink) {
                        inicioLink.classList.add("bg-blue-100", "text-blue-600", "font-bold");
                    }
                }
            }
        }

        // Executa ao carregar a página
        highlightCurrentPage();
        
        // Adiciona listeners apenas se estiver na home
        if (sections.length > 0) {
            window.addEventListener("scroll", onScroll);
            // Executa scroll check inicial
            setTimeout(onScroll, 100);
        }
    });
    </script>
    
    @yield('scripts')
</body>
</html>