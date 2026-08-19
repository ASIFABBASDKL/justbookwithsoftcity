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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            // 🔹 Relations
            $table->foreignId('service_user_id')
                ->nullable()
                ->constrained('service_users')
                ->onDelete('cascade'); // Customer (service user)

            $table->foreignId('service_provider_id')
                ->nullable()
                ->constrained('service_providers')
                ->onDelete('cascade'); // Provider

            $table->foreignId('services_and_pricing_id')
                ->nullable()
                ->constrained('services_and_pricing')
                ->onDelete('cascade'); // Service & Pricing

          
            // 🔹 Booking details
            $table->date('booking_date');
            $table->time('booking_time');
            $table->string('address')->nullable();
            $table->enum('frequency', ['one-time', 'weekly', 'monthly'])->default('one-time');
            $table->text('describe')->nullable();
            $table->string('location_img')->nullable();
            $table->text('special_request')->nullable();

            // 🔹 Pricing breakdown
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->decimal('discount', 10, 2)->nullable();
            $table->decimal('tax', 10, 2)->nullable();
            $table->decimal('service_charges', 10, 2)->nullable();
            $table->decimal('emergency_booking', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();

            // 🔹 Payment
            $table->string('payment_method')->nullable(); // e.g. cash, bank, paypal, stripe
            $table->string('payment_status')->default('unpaid'); // unpaid, paid, refunded

            // 🔹 Status
            $table->string('status')->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
