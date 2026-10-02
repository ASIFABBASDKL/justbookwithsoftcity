<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'fullname' => 'Provider One',
                'email' => 'provider1@example.com',
                'phone_number' => '03001234567',
                'password' => 'password',
                'is_seller' => true,
            ],
            [
                'fullname' => 'Provider Two',
                'email' => 'provider2@example.com',
                'phone_number' => '03001234568',
                'password' => 'password',
                'is_seller' => true,
            ],
            [
                'fullname' => 'User One',
                'email' => 'user1@example.com',
                'phone_number' => '03007654321',
                'password' => 'password',
                'is_seller' => false,
            ],
            [
                'fullname' => 'User Two',
                'email' => 'user2@example.com',
                'phone_number' => '03007654322',
                'password' => 'password',
                'is_seller' => false,
            ],
            [
                'fullname' => 'User Three',
                'email' => 'user3@example.com',
                'phone_number' => '03007654323',
                'password' => 'password',
                'is_seller' => false,
            ],
        ];

        foreach ($users as $u) {
            $u['device_token'] = Str::random(32);
            $u['is_buyer'] = true;
            $u['timezone'] = 'UTC';

            $user = User::firstOrCreate(
                ['email' => $u['email']],
                $u
            );

            $user->ensureBuyerProfile();

            if ($user->is_seller) {
                $user->becomeSeller();
            }

            if (! $user->username) {
                $user->username = Str::slug($user->fullname).$user->id;
                $user->save();
            }
        }
    }
}
