<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermissions extends Component
{
    public string $newRoleName = '';
    public array $selectedPermissions = [];
    public ?int $editingRoleId = null;
    public bool $showCreateModal = false;

    public function createRole()
    {
        $this->validate([
            'newRoleName' => 'required|string|min:2|unique:roles,name',
            'selectedPermissions' => 'required|array|min:1',
        ]);

        $role = Role::create(['name' => $this->newRoleName]);
        $role->syncPermissions($this->selectedPermissions);

        $this->reset(['newRoleName', 'selectedPermissions', 'showCreateModal']);
        session()->flash('success', 'Role created successfully.');
    }

    public function editRole(int $roleId)
    {
        $this->editingRoleId = $roleId;
        $role = Role::findOrFail($roleId);
        $this->newRoleName = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        $this->showCreateModal = true;
    }

    public function updateRole()
    {
        $this->validate([
            'newRoleName' => 'required|string|min:2',
            'selectedPermissions' => 'required|array|min:1',
        ]);

        $role = Role::findOrFail($this->editingRoleId);
        $role->update(['name' => $this->newRoleName]);
        $role->syncPermissions($this->selectedPermissions);

        $this->reset(['newRoleName', 'selectedPermissions', 'editingRoleId', 'showCreateModal']);
        session()->flash('success', 'Role updated successfully.');
    }

    public function deleteRole(int $roleId)
    {
        $role = Role::findOrFail($roleId);
        if ($role->users()->count() > 0) {
            session()->flash('error', 'Cannot delete role with assigned users.');
            return;
        }
        $role->delete();
        session()->flash('success', 'Role deleted.');
    }

    public function render()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        return view('livewire.admin.roles-permissions', compact('roles', 'permissions'));
    }
}
