{{-- resources/views/components/toast-notifications.blade.php - VERSÃO SIMPLIFICADA --}}
<div 
    x-data="{ 
        toasts: [],
        showToast(message, type = 'success') {
            const id = Date.now();
            this.toasts.push({ id, message, type, visible: true });
            setTimeout(() => this.removeToast(id), 5000);
        },
        removeToast(id) {
            const index = this.toasts.findIndex(t => t.id === id);
            if (index > -1) {
                this.toasts[index].visible = false;
                setTimeout(() => this.toasts.splice(index, 1), 300);
            }
        }
    }" 
    class="fixed top-4 right-4 z-50 space-y-2"
    @toast-sucesso.window="showToast($event.detail, 'success')"
    @toast-erro.window="showToast($event.detail, 'error')"
    @toast-info.window="showToast($event.detail, 'info')"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div 
            x-show="toast.visible"
            x-transition:enter="transform transition ease-out duration-300"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transform transition ease-in duration-200"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="translate-x-full opacity-0"
            class="max-w-sm w-full rounded-lg shadow-lg overflow-hidden"
            :class="{
                'bg-green-500 text-white': toast.type === 'success',
                'bg-red-500 text-white': toast.type === 'error', 
                'bg-blue-500 text-white': toast.type === 'info'
            }"
        >
            <div class="p-4 flex items-center justify-between">
                <div class="flex items-center">
                    <span x-show="toast.type === 'success'" class="mr-2">✅</span>
                    <span x-show="toast.type === 'error'" class="mr-2">❌</span>
                    <span x-show="toast.type === 'info'" class="mr-2">ℹ️</span>
                    <span x-text="toast.message" class="text-sm font-medium"></span>
                </div>
                <button @click="removeToast(toast.id)" class="ml-3 text-white hover:text-gray-200">
                    <span class="sr-only">Fechar</span>
                    ✕
                </button>
            </div>
        </div>
    </template>
</div>

<script>
// Função global para facilitar o uso
window.showToast = function(message, type = 'success') {
    window.dispatchEvent(new CustomEvent(`toast-${type}`, { detail: message }));
};
</script>