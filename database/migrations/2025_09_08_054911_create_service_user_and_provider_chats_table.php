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
        Schema::create('service_user_and_provider_chats', function (Blueprint $table) {
            $table->id();

            $table->text('message'); // Message content
            $table->string('image_path')->nullable();       // for image files
            $table->string('voice_path')->nullable();       // for voice/audio
            $table->string('attachment_path')->nullable();  // for docs, pdfs, etc.
            $table->string('sender_type'); // 'user' OR 'provider'

            // Sender
            $table->unsignedBigInteger('service_user_id')->nullable();     // nullable if provider sends
            $table->unsignedBigInteger('service_provider_id')->nullable(); // nullable if user sends

            // Foreign keys
            $table->foreign('service_user_id')
                ->references('id')
                ->on('service_users')
                ->onDelete('set null');

            $table->foreign('service_provider_id')
                ->references('id')
                ->on('service_providers')
                ->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_user_and_provider_chats');
    }
};
