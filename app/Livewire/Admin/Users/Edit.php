<?php

namespace App\Livewire\Admin\Users;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Edit extends Component
{
    public User $user;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $selectedRoles = [];

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
    }

    public function update(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'selectedRoles' => 'required|array|min:1',
        ]);

        $oldValues = [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'roles' => $this->user->roles->pluck('name')->toArray(),
        ];

        $this->user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        if ($this->password) {
            $this->user->update(['password' => Hash::make($this->password)]);
        }

        $this->user->syncRoles($this->selectedRoles);

        AuditLog::record('user_updated', $this->user, auth()->user(), $oldValues, [
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->selectedRoles,
        ]);

        session()->flash('success', "User {$this->user->name} updated successfully.");

        $this->redirect(route('admin.users.index'));
    }

    public function render()
    {
        return view('livewire.admin.users.edit', [
            'roles' => Role::all(),
        ])->layout('components.layouts.app');
    }
}
