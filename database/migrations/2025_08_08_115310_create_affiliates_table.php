<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('email')->unique();
            $table->enum('status', ['pending', 'active', 'inactive', 'rejected'])->default('pending');
            $table->decimal('rate', 8, 2)->nullable(); // Default commission rate
            $table->enum('rate_type', ['percentage', 'flat'])->nullable();
            $table->foreignId('tiered_rate_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('signup_bonus', 10, 2)->default(0);
            $table->string('payment_email')->nullable();
            $table->decimal('store_credit_balance', 10, 2)->default(0);
            $table->foreignId('parent_id')->nullable()->constrained('affiliates')->onDelete('set null'); // For MLM
            $table->string('slug')->unique()->nullable();
            $table->string('website_url')->nullable();
            $table->integer('cookie_duration')->nullable(); 
            $table->timestamps();
            
            $table->index(['status']);
            $table->index(['parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliates');
    }
};
