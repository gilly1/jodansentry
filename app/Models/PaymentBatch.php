<?php

namespace App\Models;

use App\Enums\PaymentBatchStatus;
use App\Enums\PaymentItemStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentBatch extends Model
{
    protected $fillable = [
        'batch_id',
        'uploaded_by',
        'approved_by',
        'rejected_by',
        'status',
        'source_file_path',
        'source_file_name',
        'file_checksum',
        'record_count',
        'valid_record_count',
        'invalid_record_count',
        'total_amount',
        'scheduled_at',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'processing_started_at',
        'processing_completed_at',
        'rejection_reason',
        'self_approved',
        'mpesa_account',
        'audit_summary',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentBatchStatus::class,
            'total_amount' => 'decimal:2',
            'scheduled_at' => 'datetime',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'processing_started_at' => 'datetime',
            'processing_completed_at' => 'datetime',
            'self_approved' => 'boolean',
            'audit_summary' => 'array',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PaymentItem::class);
    }

    public function validItems(): HasMany
    {
        return $this->items()->where('status', '!=', PaymentItemStatus::INVALID);
    }

    public function invalidItems(): HasMany
    {
        return $this->items()->where('status', PaymentItemStatus::INVALID);
    }

    public function refreshTotals(): void
    {
        $this->update([
            'record_count' => $this->items()->count(),
            'valid_record_count' => $this->validItems()->count(),
            'invalid_record_count' => $this->invalidItems()->count(),
            'total_amount' => $this->validItems()->sum('amount'),
        ]);
    }

    public function isProcessable(): bool
    {
        return in_array($this->status, [
            PaymentBatchStatus::APPROVED,
            PaymentBatchStatus::SCHEDULED,
        ]);
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [
            PaymentBatchStatus::APPROVED,
            PaymentBatchStatus::SCHEDULED,
        ]);
    }
}
