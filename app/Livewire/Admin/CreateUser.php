<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class CreateUser extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $selectedRoles = [];

    public function create()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'selectedRoles' => 'required|array|min:1',
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password),
        ]);

        $user->syncRoles($this->selectedRoles);

        session()->flash('success', 'User created successfully.');
        return redirect()->route('admin.users');
    }

    public function render()
    {
        $roles = Role::all();
        return view('livewire.admin.create-user', compact('roles'));
    }
}
