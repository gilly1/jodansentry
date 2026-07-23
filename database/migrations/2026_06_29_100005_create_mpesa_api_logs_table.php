<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpesa_api_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_item_id')->nullable()->constrained('payment_items')->nullOnDelete();
            $table->foreignId('payment_batch_id')->nullable()->constrained('payment_batches')->nullOnDelete();
            $table->enum('direction', ['request', 'response', 'callback', 'timeout']);
            $table->string('endpoint')->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->json('payload')->nullable();
            $table->json('masked_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpesa_api_logs');
    }
};
