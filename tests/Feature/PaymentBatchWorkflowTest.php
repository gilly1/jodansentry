<?php

namespace Tests\Feature;

use App\Enums\PaymentBatchStatus;
use App\Models\PaymentBatch;
use App\Models\PaymentItem;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentBatchWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    protected function createMaker(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('Maker');

        return $user;
    }

    protected function createApprover(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('Approver');

        return $user;
    }

    protected function createAdmin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('Admin');

        return $user;
    }

    protected function createBatch(User $uploader, PaymentBatchStatus $status = PaymentBatchStatus::UPLOADED): PaymentBatch
    {
        return PaymentBatch::create([
            'batch_id' => 'SAL-' . now()->format('Ymd') . '-000001',
            'uploaded_by' => $uploader->id,
            'status' => $status,
            'source_file_path' => 'payment-batches/test.xlsx',
            'source_file_name' => 'test.xlsx',
            'file_checksum' => hash('sha256', 'test'),
            'record_count' => 5,
            'valid_record_count' => 4,
            'invalid_record_count' => 1,
            'total_amount' => 50000,
        ]);
    }

    public function test_maker_can_access_upload_page(): void
    {
        $maker = $this->createMaker();

        $this->actingAs($maker)
            ->get(route('payments.upload'))
            ->assertOk();
    }

    public function test_maker_can_view_own_batches(): void
    {
        $maker = $this->createMaker();
        $batch = $this->createBatch($maker);

        $this->actingAs($maker)
            ->get(route('payments.batches'))
            ->assertOk()
            ->assertSee($batch->batch_id);
    }

    public function test_approver_can_access_pending_approvals(): void
    {
        $approver = $this->createApprover();

        $this->actingAs($approver)
            ->get(route('payments.approvals'))
            ->assertOk();
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'is_active' => false,
            'password' => bcrypt('password'),
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_batch_status_transitions(): void
    {
        $batch = new PaymentBatch();

        $batch->status = PaymentBatchStatus::APPROVED;
        $this->assertTrue($batch->isProcessable());

        $batch->status = PaymentBatchStatus::SCHEDULED;
        $this->assertTrue($batch->isProcessable());

        $batch->status = PaymentBatchStatus::PROCESSING;
        $this->assertFalse($batch->isProcessable());

        $batch->status = PaymentBatchStatus::REJECTED;
        $this->assertFalse($batch->isProcessable());
    }

    public function test_approved_batch_is_cancellable(): void
    {
        $batch = new PaymentBatch();

        $batch->status = PaymentBatchStatus::APPROVED;
        $this->assertTrue($batch->isCancellable());

        $batch->status = PaymentBatchStatus::SCHEDULED;
        $this->assertTrue($batch->isCancellable());

        $batch->status = PaymentBatchStatus::PROCESSING;
        $this->assertFalse($batch->isCancellable());
    }
}
