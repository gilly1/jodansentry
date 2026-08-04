<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    protected $fillable = [
        'phone_number',
        'mpesa_name',
        'total_transactions',
        'total_amount',
        'last_paid_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'total_transactions' => 'integer',
            'last_paid_at' => 'datetime',
        ];
    }

    public function successfulTransactions(): HasMany
    {
        return $this->hasMany(SuccessfulTransaction::class);
    }

    public function refreshTotals(): void
    {
        $this->update([
            'total_transactions' => $this->successfulTransactions()->count(),
            'total_amount' => $this->successfulTransactions()->sum('amount'),
            'last_paid_at' => $this->successfulTransactions()->max('paid_at'),
        ]);
    }
}
