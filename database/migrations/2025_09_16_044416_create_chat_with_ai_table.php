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
        Schema::create('chat_with_ai', function (Blueprint $table) {
            $table->id();

            // Har user ka ek hi record hoga
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->onDelete('cascade');

            // Multiple values (arrays) JSON ke form mein store hongi
            $table->json('voice_path')->nullable();        // ["voice1.mp3","voice2.mp3"]
            $table->json('transcribed_text')->nullable();  // ["hello","hi there"]
            $table->json('message')->nullable();           // ["hello","bye"]
            $table->json('response_text')->nullable();     // ["How can I assist you?","Goodbye!"]

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_with_ai');
    }
};
