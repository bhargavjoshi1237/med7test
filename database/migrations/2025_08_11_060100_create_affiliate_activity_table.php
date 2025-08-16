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
        Schema::create('affiliate_activity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->constrained('lunar_product_variants')->onDelete('cascade');
            $table->foreignId('buyer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('product_price', 10, 2);
            $table->decimal('commission_rate', 8, 2);
            $table->enum('commission_type', ['flat', 'percentage']);
            $table->decimal('commission_amount', 10, 2);
            $table->string('order_reference')->nullable();
            $table->timestamp('activity_date');
            $table->timestamps();
            
            $table->index(['affiliate_id', 'activity_date']);
            $table->index(['activity_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_activity');
    }
};