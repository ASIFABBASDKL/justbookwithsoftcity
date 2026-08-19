<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\ServiceProvider;
use App\Models\ServiceUser;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'fullname'     => 'Provider One',
                'email'        => 'provider1@example.com',
                'phone_number' => '03001234567',
                'password'     => Hash::make('password'),
                'role'         => 'provider',
            ],
            [
                'fullname'     => 'Provider Two',
                'email'        => 'provider2@example.com',
                'phone_number' => '03001234568',
                'password'     => Hash::make('password'),
                'role'         => 'provider',
            ],
            [
                'fullname'     => 'User One',
                'email'        => 'user1@example.com',
                'phone_number' => '03007654321',
                'password'     => Hash::make('password'),
                'role'         => 'user',
            ],
            [
                'fullname'     => 'User Two',
                'email'        => 'user2@example.com',
                'phone_number' => '03007654322',
                'password'     => Hash::make('password'),
                'role'         => 'user',
            ],
            [
                'fullname'     => 'User Three',
                'email'        => 'user3@example.com',
                'phone_number' => '03007654323',
                'password'     => Hash::make('password'),
                'role'         => 'user',
            ],
        ];

        foreach ($users as $u) {
            // 🔹 Add a random device_token for each user
            $u['device_token'] = Str::random(32);

            $user = User::firstOrCreate(
                ['email' => $u['email']], // unique check
                $u
            );

            if ($user->role === 'provider') {
                ServiceProvider::firstOrCreate([
                    'user_id' => $user->id,
                ]);
            } else {
                ServiceUser::firstOrCreate([
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}
