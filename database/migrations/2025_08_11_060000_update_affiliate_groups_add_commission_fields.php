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
        Schema::table('affiliate_groups', function (Blueprint $table) {
            $table->enum('rate_type', ['flat', 'percentage'])->after('name')->nullable();
            $table->decimal('rate', 8, 2)->after('rate_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_groups', function (Blueprint $table) {
            $table->dropColumn(['rate_type', 'rate']);
        });
    }
};