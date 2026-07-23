<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'roles.manage',

            'payment-batches.view',
            'payment-batches.view-all',
            'payment-batches.upload',
            'payment-batches.validate',
            'payment-batches.create',
            'payment-batches.submit',
            'payment-batches.approve',
            'payment-batches.reject',
            'payment-batches.schedule',
            'payment-batches.process',
            'payment-batches.retry-failed',
            'payment-batches.export',

            'mpesa-settings.view',
            'mpesa-settings.update',
            'dashboard.view',
            'reports.view',
            'audit-logs.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Admin
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->givePermissionTo(Permission::all());

        // Maker
        $maker = Role::firstOrCreate(['name' => 'Maker']);
        $maker->givePermissionTo([
            'payment-batches.view',
            'payment-batches.upload',
            'payment-batches.validate',
            'payment-batches.create',
            'payment-batches.submit',
            'payment-batches.schedule',
            'dashboard.view',
        ]);

        // Approver
        $approver = Role::firstOrCreate(['name' => 'Approver']);
        $approver->givePermissionTo([
            'payment-batches.view',
            'payment-batches.view-all',
            'payment-batches.approve',
            'payment-batches.reject',
            'reports.view',
            'dashboard.view',
            'audit-logs.view',
        ]);

        // Auditor
        $auditor = Role::firstOrCreate(['name' => 'Auditor']);
        $auditor->givePermissionTo([
            'payment-batches.view-all',
            'reports.view',
            'dashboard.view',
            'audit-logs.view',
            'payment-batches.export',
        ]);
    }
}
