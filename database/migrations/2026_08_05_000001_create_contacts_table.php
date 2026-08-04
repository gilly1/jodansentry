<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->unique();
            $table->string('mpesa_name');
            $table->unsignedInteger('total_transactions')->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->timestamp('last_paid_at')->nullable();
            $table->timestamps();

            $table->index('mpesa_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
