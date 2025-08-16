<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->string('payment_intent_id');
        $table->decimal('amount', 10, 2);
        $table->decimal('refunded_amount', 10, 2)->default(0); // Track refunded amount
        $table->string('status'); // 'paid', 'refunded', 'partially_refunded'
        $table->string('customer_email')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
