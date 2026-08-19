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
        Schema::create('service_users', function (Blueprint $table) {
            $table->id();

            // 🔹 Relation with users table
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->string('img')->nullable();                   // Profile Image
            $table->string('gender')->nullable();                // Gender
            $table->string('preferred_language')->nullable();    // Preferred Language
            $table->string('location')->nullable();              // Location
            $table->boolean('enable_ai_voice_assistant')->default(false); // AI Voice Assistant
            $table->boolean('notifications')->default(true);     // Notifications
            $table->boolean('recommendations')->default(true);   // Recommendations

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_users');
    }
};
