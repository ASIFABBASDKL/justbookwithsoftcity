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
        Schema::create('two_step_verifications', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // 🔹 Contact info
            $table->string('email');          // OTP send hone ke liye email
            $table->string('phone_number');   // OTP send hone ke liye phone

            // 🔹 Device info
            $table->string('device_id');      // current device ID
            $table->string('device_name');    // current device name
            $table->string('old_device_id')->nullable();   // old device ID optional
            $table->string('old_device_name')->nullable(); // old device name optional

            // 🔹 OTPs
            $table->string('email_otp')->nullable();      
            $table->string('phone_otp')->nullable();      

            // 🔹 Expiration
            $table->timestamp('email_expires_at')->nullable();
            $table->timestamp('phone_expires_at')->nullable();

            // 🔹 Verification status / boolean
            $table->boolean('email_verified')->default(false); // email OTP verified?
            $table->boolean('phone_verified')->default(false); // phone OTP verified?

            // 🔹 Optional: overall status (true if both verified)
            $table->boolean('status')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('two_step_verifications');
    }
};
