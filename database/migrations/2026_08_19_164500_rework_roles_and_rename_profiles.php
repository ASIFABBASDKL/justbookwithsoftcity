<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_buyer')->default(true)->after('password');
            $table->boolean('is_seller')->default(false)->after('is_buyer');
            $table->string('username')->nullable()->unique()->after('fullname');
            $table->string('avatar')->nullable()->after('device_token');
            $table->string('country')->nullable()->after('avatar');
            $table->string('timezone')->default('UTC')->after('country');
            $table->timestamp('last_seen_at')->nullable()->after('timezone');
        });

        if (Schema::hasColumn('users', 'role')) {
            foreach (DB::table('users')->get() as $user) {
                DB::table('users')->where('id', $user->id)->update([
                    'is_buyer' => true,
                    'is_seller' => ($user->role ?? '') === 'provider',
                ]);
            }

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }

        Schema::disableForeignKeyConstraints();
        $this->dropProfileForeignKeys();

        if (Schema::hasTable('service_providers') && ! Schema::hasTable('seller_profiles')) {
            Schema::rename('service_providers', 'seller_profiles');
        }

        if (Schema::hasTable('service_users') && ! Schema::hasTable('buyer_profiles')) {
            Schema::rename('service_users', 'buyer_profiles');
        }

        $this->restoreProfileForeignKeys();
        Schema::enableForeignKeyConstraints();

        Schema::table('seller_profiles', function (Blueprint $table) {
            $table->string('headline')->nullable();
            $table->text('bio')->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->json('languages')->nullable();
            $table->string('level')->default('new');
            $table->unsignedInteger('response_time_mins')->nullable();
            $table->decimal('completion_rate', 5, 2)->nullable();
            $table->decimal('on_time_rate', 5, 2)->nullable();
            $table->decimal('total_earnings', 12, 2)->default(0);
        });

        Schema::table('buyer_profiles', function (Blueprint $table) {
            $table->string('company')->nullable();
            $table->decimal('total_spent', 12, 2)->default(0);
            $table->unsignedInteger('jobs_posted')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('seller_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'headline',
                'bio',
                'hourly_rate',
                'languages',
                'level',
                'response_time_mins',
                'completion_rate',
                'on_time_rate',
                'total_earnings',
            ]);
        });

        Schema::table('buyer_profiles', function (Blueprint $table) {
            $table->dropColumn(['company', 'total_spent', 'jobs_posted']);
        });

        $this->dropProfileForeignKeys();

        if (Schema::hasTable('seller_profiles') && ! Schema::hasTable('service_providers')) {
            Schema::rename('seller_profiles', 'service_providers');
        }

        if (Schema::hasTable('buyer_profiles') && ! Schema::hasTable('service_users')) {
            Schema::rename('buyer_profiles', 'service_users');
        }

        Schema::table('wallets', function (Blueprint $table) {
            $table->foreign('service_provider_id')->references('id')->on('service_providers')->cascadeOnDelete();
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('service_user_id')->references('id')->on('service_users')->cascadeOnDelete();
            $table->foreign('service_provider_id')->references('id')->on('service_providers')->cascadeOnDelete();
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreign('service_user_id')->references('id')->on('service_users')->cascadeOnDelete();
            $table->foreign('service_provider_id')->references('id')->on('service_providers')->cascadeOnDelete();
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('service_user_id')->references('id')->on('service_users')->nullOnDelete();
            $table->foreign('service_provider_id')->references('id')->on('service_providers')->nullOnDelete();
        });
        Schema::table('services_and_pricing', function (Blueprint $table) {
            $table->foreign('service_provider_id')->references('id')->on('service_providers')->cascadeOnDelete();
        });
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->foreign('service_provider_id')->references('id')->on('service_providers')->cascadeOnDelete();
        });
        Schema::table('service_user_and_provider_chats', function (Blueprint $table) {
            $table->foreign('service_user_id')->references('id')->on('service_users')->nullOnDelete();
            $table->foreign('service_provider_id')->references('id')->on('service_providers')->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['provider', 'user'])->default('user');
        });

        DB::table('users')->where('is_seller', true)->update(['role' => 'provider']);
        DB::table('users')->where('is_seller', false)->update(['role' => 'user']);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_buyer',
                'is_seller',
                'username',
                'avatar',
                'country',
                'timezone',
                'last_seen_at',
            ]);
        });
    }

    private function dropProfileForeignKeys(): void
    {
        $this->dropFk('wallets', ['service_provider_id']);
        $this->dropFk('bookings', ['service_user_id']);
        $this->dropFk('bookings', ['service_provider_id']);
        $this->dropFk('reviews', ['service_user_id']);
        $this->dropFk('reviews', ['service_provider_id']);
        $this->dropFk('payments', ['service_user_id']);
        $this->dropFk('payments', ['service_provider_id']);
        $this->dropFk('services_and_pricing', ['service_provider_id']);
        $this->dropFk('payment_transactions', ['service_provider_id']);
        $this->dropFk('service_user_and_provider_chats', ['service_user_id']);
        $this->dropFk('service_user_and_provider_chats', ['service_provider_id']);
    }

    private function restoreProfileForeignKeys(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->foreign('service_provider_id')->references('id')->on('seller_profiles')->cascadeOnDelete();
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('service_user_id')->references('id')->on('buyer_profiles')->cascadeOnDelete();
            $table->foreign('service_provider_id')->references('id')->on('seller_profiles')->cascadeOnDelete();
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreign('service_user_id')->references('id')->on('buyer_profiles')->cascadeOnDelete();
            $table->foreign('service_provider_id')->references('id')->on('seller_profiles')->cascadeOnDelete();
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('service_user_id')->references('id')->on('buyer_profiles')->nullOnDelete();
            $table->foreign('service_provider_id')->references('id')->on('seller_profiles')->nullOnDelete();
        });
        Schema::table('services_and_pricing', function (Blueprint $table) {
            $table->foreign('service_provider_id')->references('id')->on('seller_profiles')->cascadeOnDelete();
        });
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->foreign('service_provider_id')->references('id')->on('seller_profiles')->cascadeOnDelete();
        });
        Schema::table('service_user_and_provider_chats', function (Blueprint $table) {
            $table->foreign('service_user_id')->references('id')->on('buyer_profiles')->nullOnDelete();
            $table->foreign('service_provider_id')->references('id')->on('seller_profiles')->nullOnDelete();
        });
    }

    private function dropFk(string $table, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns) {
            try {
                $blueprint->dropForeign($columns);
            } catch (\Throwable) {
                // SQLite / already dropped
            }
        });
    }
};
