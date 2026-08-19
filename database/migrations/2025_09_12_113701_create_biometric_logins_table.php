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
        Schema::create('biometric_logins', function (Blueprint $table) {
            $table->id();

            // User link
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Device info (nullable for deactivation)
              $table->string('device_id')->nullable();       
              $table->string('device_name')->nullable();     

             // Biometric login token (nullable for deactivation)
             $table->string('biometric_token')->nullable(); 

             // Status
             $table->boolean('is_active')->default(true); 
             $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_logins');
    }
};
