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
        Schema::create('enable_locations', function (Blueprint $table) {
            $table->id();

            // 🔹 Relation with service_providers
            $table->foreignId('service_provider_id')
                  ->constrained('service_providers')
                  ->onDelete('cascade');

            // 🔹 Location fields
            $table->boolean('is_location_enabled')->default(false); // ✅ new field
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
        Schema::dropIfExists('enable_locations');
    }
};
