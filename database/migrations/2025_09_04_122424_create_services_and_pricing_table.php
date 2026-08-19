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
        Schema::create('services_and_pricing', function (Blueprint $table) {
            $table->id();
            // 🔹 Relation with service_providers
            $table->foreignId('service_provider_id')
                ->constrained('service_providers')
                ->onDelete('cascade');

            $table->string('category')->nullable();         // Category
            $table->string('subcategory')->nullable();         // SubCategory
            $table->string('service_name')->nullable();     // Service Name
            $table->text('description')->nullable();        // Description
            $table->string('img')->nullable();              // Image
            $table->string('service_price')->nullable();    // Service Price
            $table->string('service_duration')->nullable(); // Service Duration

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services_and_pricing');
    }
};
