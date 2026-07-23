<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id')->unique();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->foreignId('rejected_by')->nullable()->constrained('users');
            $table->string('status')->default('uploaded')->index();
            $table->string('source_file_path');
            $table->string('source_file_name');
            $table->string('file_checksum')->nullable();
            $table->unsignedInteger('record_count')->default(0);
            $table->unsignedInteger('valid_record_count')->default(0);
            $table->unsignedInteger('invalid_record_count')->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('processing_completed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->boolean('self_approved')->default(false);
            $table->string('mpesa_account')->nullable();
            $table->json('audit_summary')->nullable();
            $table->timestamps();

            $table->index('uploaded_by');
            $table->index('approved_by');
            $table->index('scheduled_at');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_batches');
    }
};
