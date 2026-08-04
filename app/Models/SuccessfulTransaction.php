<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuccessfulTransaction extends Model
{
    protected $fillable = [
        'contact_id',
        'payment_item_id',
        'payment_batch_id',
        'transaction_receipt',
        'phone_number',
        'amount',
        'mpesa_result_description',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function paymentItem(): BelongsTo
    {
        return $this->belongsTo(PaymentItem::class);
    }

    public function paymentBatch(): BelongsTo
    {
        return $this->belongsTo(PaymentBatch::class);
    }
}
