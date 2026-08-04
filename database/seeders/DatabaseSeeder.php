<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'upload payments',
            'view payments',
            'submit payments',
            'approve payments',
            'execute payments',
            'schedule payments',
            'cancel payments',
            'view audit logs',
            'query balance',
            'query transaction status',
            'manage users',
            'manage roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create default roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions($permissions);

        $maker = Role::firstOrCreate(['name' => 'maker']);
        $maker->syncPermissions([
            'upload payments',
            'view payments',
            'submit payments',
            'query transaction status',
        ]);

        $approver = Role::firstOrCreate(['name' => 'approver']);
        $approver->syncPermissions([
            'view payments',
            'approve payments',
            'execute payments',
            'schedule payments',
            'cancel payments',
            'query balance',
            'query transaction status',
        ]);

        $auditor = Role::firstOrCreate(['name' => 'auditor']);
        $auditor->syncPermissions([
            'view payments',
            'view audit logs',
            'query transaction status',
        ]);

        // Create admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@JodanPay.test'],
            [
                'name' => 'System Admin',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $adminUser->assignRole('admin');
    }
}

