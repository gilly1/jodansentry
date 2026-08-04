<?php

namespace App\Models;

use App\Enums\PaymentBatchStatus;
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
        'original_filename',
        'stored_filepath',
        'file_checksum',
        'total_records',
        'valid_records',
        'invalid_records',
        'successful_records',
        'failed_records',
        'total_amount',
        'mpesa_account',
        'rejection_reason',
        'self_approved',
        'audit_summary',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'scheduled_at',
        'processing_started_at',
        'processing_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentBatchStatus::class,
            'total_amount' => 'decimal:2',
            'self_approved' => 'boolean',
            'audit_summary' => 'array',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'processing_started_at' => 'datetime',
            'processing_completed_at' => 'datetime',
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
        return $this->items()->where('status', '!=', 'invalid');
    }

    public function invalidItems(): HasMany
    {
        return $this->items()->where('status', 'invalid');
    }

    public function refreshTotals(): void
    {
        $this->update([
            'total_records' => $this->items()->count(),
            'valid_records' => $this->validItems()->count(),
            'invalid_records' => $this->invalidItems()->count(),
            'successful_records' => $this->items()->where('status', 'successful')->count(),
            'failed_records' => $this->items()->where('status', 'failed')->count(),
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
            PaymentBatchStatus::UPLOADED,
            PaymentBatchStatus::PENDING_APPROVAL,
            PaymentBatchStatus::APPROVED,
            PaymentBatchStatus::SCHEDULED,
        ]);
    }
}
