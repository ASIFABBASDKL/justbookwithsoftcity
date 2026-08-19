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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            // 🔹 Relations
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade'); 
            $table->foreignId('service_user_id')->constrained('service_users')->onDelete('cascade'); 
            $table->foreignId('service_provider_id')->constrained('service_providers')->onDelete('cascade'); 

            // 🔹 Review details
            $table->tinyInteger('rating')->default(0); // Overall rating 1 to 5
            $table->tinyInteger('satisfaction')->default(0);   // satisfaction level
            $table->tinyInteger('response_rate')->default(0);  // how fast provider responded
            $table->tinyInteger('job_success')->default(0);    // task/job success rate
            $table->tinyInteger('reliability')->default(0);    // trust/reliability

            $table->text('comment')->nullable();       // User feedback
            $table->boolean('is_visible')->default(true); // For hiding abusive reviews

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
