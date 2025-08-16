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
        Schema::create('affiliate_tier_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->onDelete('cascade');
            $table->integer('tier_level'); 
            $table->decimal('rate', 8, 2);
            $table->enum('rate_type', ['percentage', 'flat']);
            $table->integer('threshold');
            $table->timestamps();
            
            $table->unique(['affiliate_id', 'tier_level']);
            $table->index(['affiliate_id', 'tier_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_tier_rates');
    }
};
