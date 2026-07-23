<?php

namespace App\Livewire\Admin\Users;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Create extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $selectedRoles = [];

    protected array $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
        'selectedRoles' => 'required|array|min:1',
    ];

    public function create(): void
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        $user->syncRoles($this->selectedRoles);

        AuditLog::record('user_created', $user, auth()->user());

        session()->flash('success', "User {$user->name} created successfully.");

        $this->redirect(route('admin.users.index'));
    }

    public function render()
    {
        return view('livewire.admin.users.create', [
            'roles' => Role::all(),
        ])->layout('components.layouts.app');
    }
}
