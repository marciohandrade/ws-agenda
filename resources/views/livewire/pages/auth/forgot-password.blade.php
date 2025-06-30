<?php
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Services\CustomPasswordResetService;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // Usar nosso serviço personalizado com PHPMailer
        $resetService = new CustomPasswordResetService();
        $result = $resetService->sendPasswordResetEmail($this->email);

        if ($result['success']) {
            $this->reset('email');
            session()->flash('status', __('passwords.sent'));
        } else {
            // Se for usuário não encontrado, usar a chave de tradução
            if (str_contains($result['message'], 'não encontrado')) {
                $this->addError('email', __('passwords.user'));
            } else {
                $this->addError('email', $result['message']);
            }
        }
    }
}; ?>

<div>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Esqueceu sua senha? Sem problemas. Basta nos informar seu endereço de e-mail e enviaremos um link para redefinição de senha que permitirá que você escolha uma nova.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Enviar Link de Redefinição') }}
            </x-primary-button>
        </div>
    </form>
</div>