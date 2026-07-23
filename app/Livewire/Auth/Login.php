<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected array $rules = [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ];

    public function login(): void
    {
        $this->validate();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->addError('email', 'These credentials do not match our records.');

            return;
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            $this->addError('email', 'Your account has been deactivated.');

            return;
        }

        $user->update(['last_login_at' => now()]);

        session()->regenerate();

        $this->redirect(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('components.layouts.guest');
    }
}
