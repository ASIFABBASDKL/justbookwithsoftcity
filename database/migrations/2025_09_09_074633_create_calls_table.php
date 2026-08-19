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
        Schema::create('calls', function (Blueprint $table) {
            $table->id();

            // 🔹 Relations
            $table->foreignId('service_provider_id')
                  ->constrained('service_providers')
                  ->cascadeOnDelete();

            $table->foreignId('service_user_id')
                  ->constrained('service_users')
                  ->cascadeOnDelete();

            // 🔹 Call info
            $table->string('duration')->nullable();   // e.g. "120s" or "2 min"
            $table->enum('type', ['audio', 'video'])->default('audio');
            $table->timestamp('call_time')->nullable(); // call started/ended

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
