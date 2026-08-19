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
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id')->constrained()->onDelete('cascade');

            // ✅ Days as boolean
            $table->boolean('monday')->default(false);
            $table->time('monday_start')->nullable();
            $table->time('monday_end')->nullable();

            $table->boolean('tuesday')->default(false);
            $table->time('tuesday_start')->nullable();
            $table->time('tuesday_end')->nullable();

            $table->boolean('wednesday')->default(false);
            $table->time('wednesday_start')->nullable();
            $table->time('wednesday_end')->nullable();

            $table->boolean('thursday')->default(false);
            $table->time('thursday_start')->nullable();
            $table->time('thursday_end')->nullable();

            $table->boolean('friday')->default(false);
            $table->time('friday_start')->nullable();
            $table->time('friday_end')->nullable();

            $table->boolean('saturday')->default(false);
            $table->time('saturday_start')->nullable();
            $table->time('saturday_end')->nullable();

            $table->boolean('sunday')->default(false);
            $table->time('sunday_start')->nullable();
            $table->time('sunday_end')->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};
