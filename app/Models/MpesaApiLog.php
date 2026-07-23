<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpesaApiLog extends Model
{
    protected $fillable = [
        'payment_item_id',
        'payment_batch_id',
        'direction',
        'endpoint',
        'http_status',
        'payload',
        'masked_payload',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'masked_payload' => 'array',
        ];
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
