<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class EditUser extends Component
{
    public User $user;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $selectedRoles = [];

    public function mount(User $user)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
    }

    public function update()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->user->id,
            'selectedRoles' => 'required|array|min:1',
        ];

        if ($this->password) {
            $rules['password'] = 'min:8|confirmed';
        }

        $this->validate($rules);

        $this->user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        if ($this->password) {
            $this->user->update(['password' => bcrypt($this->password)]);
        }

        $this->user->syncRoles($this->selectedRoles);

        session()->flash('success', 'User updated successfully.');
        return redirect()->route('admin.users');
    }

    public function render()
    {
        $roles = Role::all();
        return view('livewire.admin.edit-user', compact('roles'));
    }
}
