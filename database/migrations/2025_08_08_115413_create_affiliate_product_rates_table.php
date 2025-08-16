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
        Schema::create('affiliate_product_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->onDelete('cascade');
           $table->foreignId('product_variant_id')
                  ->constrained('lunar_product_variants')
                  ->after('affiliate_id') 
                  ->nullable();
            $table->decimal('rate', 8, 2);
            $table->enum('rate_type', ['percentage', 'flat']);
            $table->timestamps();
            $table->index(['product_variant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_product_rates');
    }
};
