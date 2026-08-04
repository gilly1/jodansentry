<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('successful_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_batch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('transaction_receipt')->nullable()->unique();
            $table->string('phone_number');
            $table->decimal('amount', 15, 2);
            $table->string('mpesa_result_description')->nullable();
            $table->timestamp('paid_at');
            $table->timestamps();

            $table->index('phone_number');
            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('successful_transactions');
    }
};
