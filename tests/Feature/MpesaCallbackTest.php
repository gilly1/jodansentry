<?php

namespace Tests\Feature;

use App\Actions\Mpesa\HandleB2CResultCallback;
use App\Enums\PaymentItemStatus;
use App\Models\PaymentBatch;
use App\Models\PaymentItem;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MpesaCallbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    protected function createPaymentItem(): PaymentItem
    {
        $user = User::factory()->create();
        $batch = PaymentBatch::create([
            'batch_id' => 'SAL-20260629-000001',
            'uploaded_by' => $user->id,
            'status' => 'processing',
            'source_file_path' => 'test.xlsx',
            'source_file_name' => 'test.xlsx',
            'record_count' => 1,
            'valid_record_count' => 1,
            'total_amount' => 1000,
        ]);

        return PaymentItem::create([
            'payment_batch_id' => $batch->id,
            'row_number' => 2,
            'employee_name' => 'John Mwangi',
            'normalized_phone' => '254712345678',
            'amount' => 1000,
            'status' => PaymentItemStatus::PROCESSING,
            'mpesa_originator_conversation_id' => 'AG_20260629_0001',
            'mpesa_conversation_id' => 'AG_20260629_0002',
        ]);
    }

    public function test_successful_callback_updates_item(): void
    {
        $item = $this->createPaymentItem();

        $payload = [
            'Result' => [
                'ResultType' => 0,
                'ResultCode' => 0,
                'ResultDesc' => 'The service request is processed successfully.',
                'OriginatorConversationID' => 'AG_20260629_0001',
                'ConversationID' => 'AG_20260629_0002',
                'TransactionID' => 'SHJ0000000',
                'ResultParameters' => [
                    'ResultParameter' => [
                        ['Key' => 'TransactionReceipt', 'Value' => 'SHJ7890456'],
                        ['Key' => 'TransactionAmount', 'Value' => 1000],
                        ['Key' => 'ReceiverPartyPublicName', 'Value' => '254712345678 - JOHN MWANGI'],
                    ],
                ],
            ],
        ];

        app(HandleB2CResultCallback::class)->handle($payload);

        $item->refresh();

        $this->assertEquals(PaymentItemStatus::SUCCESSFUL, $item->status);
        $this->assertEquals('SHJ7890456', $item->mpesa_transaction_receipt);
        $this->assertEquals(0, $item->mpesa_result_code);
        $this->assertNotNull($item->processed_at);
    }

    public function test_failed_callback_updates_item(): void
    {
        $item = $this->createPaymentItem();

        $payload = [
            'Result' => [
                'ResultCode' => 2001,
                'ResultDesc' => 'The initiator information is invalid.',
                'OriginatorConversationID' => 'AG_20260629_0001',
                'ConversationID' => 'AG_20260629_0002',
            ],
        ];

        app(HandleB2CResultCallback::class)->handle($payload);

        $item->refresh();

        $this->assertEquals(PaymentItemStatus::FAILED, $item->status);
        $this->assertNotNull($item->failed_at);
    }

    public function test_callback_endpoint_responds_correctly(): void
    {
        $response = $this->postJson(route('mpesa.b2c.result'), [
            'Result' => [
                'ResultCode' => 0,
                'OriginatorConversationID' => 'UNKNOWN_ID',
            ],
        ]);

        $response->assertOk()
            ->assertJson(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    public function test_timeout_callback_endpoint_responds(): void
    {
        $response = $this->postJson(route('mpesa.b2c.timeout'), [
            'Result' => [
                'ResultCode' => 100000001,
                'OriginatorConversationID' => 'UNKNOWN_ID',
            ],
        ]);

        $response->assertOk()
            ->assertJson(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    public function test_idempotent_callback_does_not_overwrite_success(): void
    {
        $item = $this->createPaymentItem();
        $item->update([
            'status' => PaymentItemStatus::SUCCESSFUL,
            'mpesa_transaction_receipt' => 'SHJ7890456',
        ]);

        $payload = [
            'Result' => [
                'ResultCode' => 0,
                'OriginatorConversationID' => 'AG_20260629_0001',
                'ConversationID' => 'AG_20260629_0002',
                'ResultParameters' => [
                    'ResultParameter' => [
                        ['Key' => 'TransactionReceipt', 'Value' => 'SHJ9999999'],
                    ],
                ],
            ],
        ];

        app(HandleB2CResultCallback::class)->handle($payload);

        $item->refresh();

        // Should keep original receipt, not overwrite
        $this->assertEquals('SHJ7890456', $item->mpesa_transaction_receipt);
    }
}
