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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();

            // 🔹 Kis provider ka wallet hai
            $table->foreignId('service_provider_id')
                ->constrained('service_providers')
                ->cascadeOnDelete();

            // 🔹 Kis wallet se link hai
            $table->foreignId('wallet_id')
                ->constrained('wallets')
                ->cascadeOnDelete();

            // 🔹 Kis booking se relate hai
            $table->foreignId('booking_id')->nullable()
                ->constrained('bookings')
                ->cascadeOnDelete();

            // 🔹 Payment Details
            $table->string('transaction_id')->unique();
            $table->enum('payment_method', [
                'credit_card',
                'debit_card',
                'paypal',
                'stripe',
                'cash',
                'bank_transfer'
            ]);
            $table->decimal('amount', 10, 2);

            // 🔹 Account Number (withdraw ke liye)
            $table->string('account_number')->nullable();

            // 🔹 Payment Status → incoming (add to wallet) ya withdraw (wallet se nikalna)
            $table->enum('status', ['incoming', 'withdraw'])->default('incoming');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
