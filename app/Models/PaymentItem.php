<?php

namespace App\Models;

use App\Enums\PaymentItemStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentItem extends Model
{
    protected $fillable = [
        'payment_batch_id',
        'row_number',
        'employee_name',
        'employee_code',
        'phone_number_raw',
        'normalized_phone',
        'amount',
        'narration',
        'status',
        'validation_errors',
        'mpesa_originator_conversation_id',
        'mpesa_conversation_id',
        'mpesa_transaction_receipt',
        'mpesa_result_code',
        'mpesa_result_description',
        'mpesa_response_code',
        'mpesa_response_description',
        'request_payload',
        'response_payload',
        'callback_payload',
        'timeout_payload',
        'attempts',
        'last_attempted_at',
        'processed_at',
        'failed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentItemStatus::class,
            'amount' => 'decimal:2',
            'validation_errors' => 'array',
            'request_payload' => 'array',
            'response_payload' => 'array',
            'callback_payload' => 'array',
            'timeout_payload' => 'array',
            'last_attempted_at' => 'datetime',
            'processed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PaymentBatch::class, 'payment_batch_id');
    }

    public function isPayable(): bool
    {
        if ($this->mpesa_transaction_receipt) {
            return false;
        }

        if ($this->status === PaymentItemStatus::SUCCESSFUL) {
            return false;
        }

        if ($this->status === PaymentItemStatus::PROCESSING) {
            return false;
        }

        return $this->status->isPayable();
    }

    public function markProcessing(): void
    {
        $this->update(['status' => PaymentItemStatus::PROCESSING]);
    }

    public function markQueued(): void
    {
        $this->update(['status' => PaymentItemStatus::QUEUED]);
    }
}
