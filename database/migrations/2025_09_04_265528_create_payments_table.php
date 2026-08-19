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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // 🔹 Relation with service_users (optional)
            $table->foreignId('service_user_id')
                  ->nullable()
                  ->constrained('service_users')
                  ->onDelete('cascade');

            // 🔹 Relation with service_providers (optional)
            $table->foreignId('service_provider_id')
                  ->nullable()
                  ->constrained('service_providers')
                  ->onDelete('cascade');

            $table->string('payment_method');              // e.g. bank, paypal, stripe
            $table->string('account_holder_name')->nullable(); // ✅ Account Holder Name
            $table->string('account_name')->nullable();
            $table->string('account_title')->nullable();
            $table->string('account_number')->nullable();
            $table->string('sort_no')->nullable();         // ✅ Sort Number

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
