<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_batch_id')->constrained('payment_batches')->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->string('employee_name')->nullable();
            $table->string('employee_code')->nullable();
            $table->string('phone_number_raw')->nullable();
            $table->string('normalized_phone')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('narration')->nullable();
            $table->string('status')->default('validated')->index();
            $table->json('validation_errors')->nullable();
            $table->string('mpesa_originator_conversation_id')->nullable()->index();
            $table->string('mpesa_conversation_id')->nullable()->index();
            $table->string('mpesa_transaction_receipt')->nullable()->index();
            $table->string('mpesa_result_code')->nullable();
            $table->text('mpesa_result_description')->nullable();
            $table->string('mpesa_response_code')->nullable();
            $table->text('mpesa_response_description')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->json('callback_payload')->nullable();
            $table->json('timeout_payload')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('last_attempted_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index('payment_batch_id');
            $table->index('normalized_phone');
            $table->index('employee_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_items');
    }
};
