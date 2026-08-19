<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();

            // 🔹 Relation → sirf provider ke liye
            $table->foreignId('service_provider_id')
                ->constrained('service_providers')
                ->onDelete('cascade'); 

            // 🔹 Wallet info
            $table->decimal('total_amount', 12, 2)->default(0.00);             // total earned
            $table->decimal('total_available_amount', 12, 2)->default(0.00);   // available balance
            $table->decimal('total_withdrawal_amount', 12, 2)->default(0.00);  // already withdrawn

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
