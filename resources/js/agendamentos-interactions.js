// resources/js/agendamentos-interactions.js

/**
 * Sistema de Interações para Agendamentos
 * Funcionalidades:
 * - Scroll horizontal inteligente
 * - Atalhos de teclado
 * - Gestos touch para mobile
 * - Auto-refresh em tempo real
 * - Notificações push
 */

class AgendamentosInteractions {
    constructor() {
        this.config = {
            autoRefresh: true,
            refreshInterval: 30000, // 30 segundos
            enableKeyboardShortcuts: true,
            enableTouchGestures: true,
            enableScrollIndicators: true
        };
        
        this.refreshTimer = null;
        this.touchStartX = 0;
        this.touchStartY = 0;
        
        this.init();
    }
    
    init() {
        this.setupScrollInteractions();
        this.setupKeyboardShortcuts();
        this.setupTouchGestures();
        this.setupAutoRefresh();
        this.setupScrollIndicators();
        this.setupFilterPersistence();
        this.setupRealTimeUpdates();
        
        console.log('🚀 Agendamentos Interactions inicializado');
    }
    
    // ====== SCROLL HORIZONTAL INTELIGENTE ======
    setupScrollInteractions() {
        const scrollContainers = document.querySelectorAll('.status-scroll');
        
        scrollContainers.forEach(container => {
            // Scroll com mouse wheel
            container.addEventListener('wheel', (e) => {
                if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) return;
                
                e.preventDefault();
                container.scrollLeft += e.deltaY > 0 ? 60 : -60;
            });
            
            // Auto-scroll para item ativo
            this.scrollToActiveStatus(container);
            
            // Indica scroll disponível
            this.updateScrollIndicators(container);
            container.addEventListener('scroll', () => {
                this.updateScrollIndicators(container);
            });
        });
    }
    
    scrollToActiveStatus(container) {
        const activeButton = container.querySelector('.status-button-active');
        if (activeButton) {
            const containerRect = container.getBoundingClientRect();
            const buttonRect = activeButton.getBoundingClientRect();
            
            if (buttonRect.left < containerRect.left || buttonRect.right > containerRect.right) {
                activeButton.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                    inline: 'center'
                });
            }
        }
    }
    
    updateScrollIndicators(container) {
        const wrapper = container.closest('.status-scroll-container');
        if (!wrapper) return;
        
        const canScrollLeft = container.scrollLeft > 0;
        const canScrollRight = container.scrollLeft < (container.scrollWidth - container.clientWidth);
        
        wrapper.classList.toggle('can-scroll-left', canScrollLeft);
        wrapper.classList.toggle('can-scroll-right', canScrollRight);
    }
    
    // ====== ATALHOS DE TECLADO ======
    setupKeyboardShortcuts() {
        if (!this.config.enableKeyboardShortcuts) return;
        
        document.addEventListener('keydown', (e) => {
            // Só funciona se não estiver em input/textarea
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;
            
            const shortcuts = {
                'KeyF': () => this.focusSearch(), // F - Focar busca
                'KeyR': () => this.refreshData(), // R - Refresh
                'KeyC': () => this.clearFilters(), // C - Clear filters
                'KeyT': () => this.toggleTableView(), // T - Toggle view
                'Digit1': () => this.selectStatus('pendente'), // 1-4 para status principais
                'Digit2': () => this.selectStatus('confirmado'),
                'Digit3': () => this.selectStatus('concluido'),
                'Digit4': () => this.selectStatus('cancelado'),
                'Escape': () => this.closeAllModals() // ESC - Fechar modais
            };
            
            if (e.ctrlKey || e.metaKey) {
                const shortcut = shortcuts[e.code];
                if (shortcut) {
                    e.preventDefault();
                    shortcut();
                }
            }
        });
        
        // Dica visual de atalhos (opcional)
        this.showKeyboardHints();
    }
    
    focusSearch() {
        const searchInput = document.querySelector('input[wire\\:model*="buscaUnificada"]');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    }
    
    refreshData() {
        if (window.Livewire) {
            window.Livewire.find(this.getLivewireComponentId())?.refresh();
            this.showToast('Dados atualizados!', 'info', 2000);
        }
    }
    
    clearFilters() {
        if (window.Livewire) {
            window.Livewire.find(this.getLivewireComponentId())?.call('limparFiltros');
        }
    }
    
    toggleTableView() {
        const currentView = document.querySelector('[wire\\:click*="alterarView"]')?.textContent.includes('table') ? 'cards' : 'table';
        if (window.Livewire) {
            window.Livewire.find(this.getLivewireComponentId())?.call('alterarView', currentView);
        }
    }
    
    selectStatus(status) {
        if (window.Livewire) {
            window.Livewire.find(this.getLivewireComponentId())?.call('setStatus', status);
        }
    }
    
    closeAllModals() {
        // Fecha dropdowns abertos
        document.querySelectorAll('[x-data*="open"]').forEach(el => {
            if (el.__x && el.__x.$data.open) {
                el.__x.$data.open = false;
            }
        });
    }
    
    getLivewireComponentId() {
        const component = document.querySelector('[wire\\:id]');
        return component?.getAttribute('wire:id');
    }
    
    // ====== GESTOS TOUCH PARA MOBILE ======
    setupTouchGestures() {
        if (!this.config.enableTouchGestures) return;
        
        const statusContainer = document.querySelector('.status-scroll');
        if (!statusContainer) return;
        
        statusContainer.addEventListener('touchstart', (e) => {
            this.touchStartX = e.touches[0].clientX;
            this.touchStartY = e.touches[0].clientY;
        });
        
        statusContainer.addEventListener('touchend', (e) => {
            const touchEndX = e.changedTouches[0].clientX;
            const touchEndY = e.changedTouches[0].clientY;
            
            const deltaX = this.touchStartX - touchEndX;
            const deltaY = this.touchStartY - touchEndY;
            
            // Swipe horizontal significativo
            if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 50) {
                // Implementar navegação por swipe se necessário
                // Por exemplo: próxima página de filtros
            }
            
            // Swipe vertical para refresh (pull-to-refresh)
            if (deltaY < -100 && Math.abs(deltaX) < 50) {
                this.refreshData();
            }
        });
    }
    
    // ====== AUTO-REFRESH ======
    setupAutoRefresh() {
        if (!this.config.autoRefresh) return;
        
        // Para o timer existente
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
        }
        
        // Inicia novo timer
        this.refreshTimer = setInterval(() => {
            // Só atualiza se a página estiver visível
            if (!document.hidden) {
                this.refreshData();
            }
        }, this.config.refreshInterval);
        
        // Para o timer quando a página não está visível
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                if (this.refreshTimer) {
                    clearInterval(this.refreshTimer);
                    this.refreshTimer = null;
                }
            } else {
                this.setupAutoRefresh();
            }
        });
    }
    
    // ====== INDICADORES DE SCROLL ======
    setupScrollIndicators() {
        if (!this.config.enableScrollIndicators) return;
        
        const style = document.createElement('style');
        style.textContent = `
            .status-scroll-container.can-scroll-left::before {
                opacity: 1;
            }
            .status-scroll-container.can-scroll-right::after {
                opacity: 1;
            }
            .status-scroll-container::before,
            .status-scroll-container::after {
                opacity: 0;
                transition: opacity 0.3s ease;
            }
        `;
        document.head.appendChild(style);
    }
    
    // ====== PERSISTÊNCIA DE FILTROS ======
    setupFilterPersistence() {
        // Salva estado dos filtros no localStorage
        const saveFilters = () => {
            const filters = {
                showFiltros: document.querySelector('[wire\\:model*="showFiltros"]')?.checked,
                showStatusSecundarios: document.querySelector('[wire\\:model*="showStatusSecundarios"]')?.checked,
                viewMode: document.querySelector('.bg-white.text-blue-600') ? 'cards' : 'table',
                timestamp: Date.now()
            };
            
            localStorage.setItem('agendamentos_filters', JSON.stringify(filters));
        };
        
        // Restaura filtros salvos
        const restoreFilters = () => {
            try {
                const saved = localStorage.getItem('agendamentos_filters');
                if (saved) {
                    const filters = JSON.parse(saved);
                    // Só restaura se foi salvo nas últimas 24h
                    if (Date.now() - filters.timestamp < 86400000) {
                        // Implementar restauração se necessário
                    }
                }
            } catch (e) {
                console.warn('Erro ao restaurar filtros:', e);
            }
        };
        
        // Eventos para salvar estado
        document.addEventListener('change', saveFilters);
        document.addEventListener('click', saveFilters);
        
        // Restaura na inicialização
        restoreFilters();
    }
    
    // ====== ATUALIZAÇÕES EM TEMPO REAL ======
    setupRealTimeUpdates() {
        // Escuta eventos Livewire para animações suaves
        document.addEventListener('livewire:updated', () => {
            // Re-aplica interações após atualizações
            setTimeout(() => {
                this.setupScrollInteractions();
                this.animateNewItems();
            }, 100);
        });
        
        // Animação para novos itens
        document.addEventListener('livewire:load', () => {
            this.animateInitialLoad();
        });
    }
    
    animateNewItems() {
        const newItems = document.querySelectorAll('.agendamento-card:not(.animated)');
        newItems.forEach((item, index) => {
            item.classList.add('animated');
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                item.style.transition = 'all 0.3s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateY(0)';
            }, index * 50);
        });
    }
    
    animateInitialLoad() {
        const items = document.querySelectorAll('.agendamento-card, .status-button');
        items.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                item.style.transition = 'all 0.4s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateY(0)';
            }, index * 30);
        });
    }
    
    // ====== DICAS DE ATALHOS ======
    showKeyboardHints() {
        const hints = document.createElement('div');
        hints.className = 'fixed bottom-4 left-4 bg-gray-900 text-white text-xs p-2 rounded shadow-lg opacity-0 transition-opacity z-50';
        hints.innerHTML = `
            <div class="font-semibold mb-1">Atalhos:</div>
            <div>Ctrl+F: Buscar | Ctrl+R: Atualizar | Ctrl+C: Limpar</div>
            <div>Ctrl+T: Alternar visualização | 1-4: Status</div>
        `;
        document.body.appendChild(hints);
        
        // Mostra dicas por alguns segundos na primeira visita
        if (!localStorage.getItem('keyboard_hints_shown')) {
            setTimeout(() => {
                hints.style.opacity = '1';
                setTimeout(() => {
                    hints.style.opacity = '0';
                    setTimeout(() => hints.remove(), 300);
                }, 5000);
            }, 2000);
            
            localStorage.setItem('keyboard_hints_shown', 'true');
        } else {
            hints.remove();
        }
    }
    
    // ====== UTILITÁRIOS ======
    showToast(message, type = 'info', duration = 3000) {
        if (window.showToast) {
            window.showToast(message, type, duration);
        }
    }
    
    // Cleanup quando necessário
    destroy() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
        }
        
        // Remove event listeners se necessário
        document.removeEventListener('keydown', this.keydownHandler);
        document.removeEventListener('visibilitychange', this.visibilityHandler);
    }
}

// Inicialização automática quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.agendamentosInteractions = new AgendamentosInteractions();
});

// Para uso global
window.AgendamentosInteractions = AgendamentosInteractions;