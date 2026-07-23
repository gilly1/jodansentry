<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_auditor_cannot_upload(): void
    {
        $auditor = User::factory()->create(['is_active' => true]);
        $auditor->assignRole('Auditor');

        $this->actingAs($auditor)
            ->get(route('payments.upload'))
            ->assertOk(); // Can visit page but cannot submit

        $this->assertFalse($auditor->can('payment-batches.upload'));
    }

    public function test_maker_cannot_approve_without_approver_role(): void
    {
        $maker = User::factory()->create(['is_active' => true]);
        $maker->assignRole('Maker');

        $this->assertFalse($maker->can('payment-batches.approve'));
    }

    public function test_admin_has_all_permissions(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('Admin');

        $this->assertTrue($admin->can('payment-batches.upload'));
        $this->assertTrue($admin->can('payment-batches.approve'));
        $this->assertTrue($admin->can('payment-batches.reject'));
        $this->assertTrue($admin->can('users.create'));
        $this->assertTrue($admin->can('mpesa-settings.update'));
        $this->assertTrue($admin->can('audit-logs.view'));
    }

    public function test_approver_can_view_all_batches(): void
    {
        $approver = User::factory()->create(['is_active' => true]);
        $approver->assignRole('Approver');

        $this->assertTrue($approver->can('payment-batches.view-all'));
    }

    public function test_auditor_cannot_create_payments(): void
    {
        $auditor = User::factory()->create(['is_active' => true]);
        $auditor->assignRole('Auditor');

        $this->assertFalse($auditor->can('payment-batches.upload'));
        $this->assertFalse($auditor->can('payment-batches.create'));
        $this->assertFalse($auditor->can('payment-batches.approve'));
        $this->assertFalse($auditor->can('payment-batches.reject'));
        $this->assertFalse($auditor->can('payment-batches.schedule'));
        $this->assertFalse($auditor->can('payment-batches.process'));
    }
}
