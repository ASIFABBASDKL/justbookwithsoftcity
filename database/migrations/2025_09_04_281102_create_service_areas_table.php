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
        Schema::create('service_areas', function (Blueprint $table) {
            $table->id();

            // 🔹 Relation with service_providers
            $table->foreignId('service_provider_id')
                  ->constrained('service_providers')
                  ->onDelete('cascade');

            // 🔹 Address fields
            $table->string('city')->nullable();
            $table->string('building')->nullable();
            $table->string('apartment')->nullable();
            $table->string('floor')->nullable();
            $table->string('street')->nullable();

            // 🔹 Location fields
            $table->boolean('live_location')->default(false);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_areas');
    }
};
