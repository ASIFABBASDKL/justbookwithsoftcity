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
        Schema::create('service_providers', function (Blueprint $table) {
            $table->id();

            // 🔹 Relation with users table
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->string('business_name')->nullable();        // Business Name
            $table->string('category')->nullable();             // Category
            $table->string('service_type')->nullable();         // Type of Service
            $table->string('id_verification')->nullable();      // ID Verification
            $table->string('country')->nullable();              // Country
            $table->string('driving_license')->nullable();      // Driving License
            $table->string('passport')->nullable();             // Passport
            $table->string('gmc_dbs_number')->nullable();       // GMC/DBS Number
            $table->string('portfolio')->nullable();            // Portfolio
            $table->string('experience_years')->nullable();     // Experience Years
            $table->string('image')->nullable();                // Image
            $table->string('description')->nullable();          // Description


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_providers');
    }
};
