<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Demo logins for manual testing — admin / seller / buyer.
 * Password sab ka: password
 */
class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $admin = $this->makeUser('Demo Admin', 'admin@justbook.test', '+920000000001');
        $admin->assignRole('admin');

        $seller = $this->makeUser('Demo Seller', 'seller@justbook.test', '+920000000002');
        $seller->becomeSeller();

        $buyer = $this->makeUser('Demo Buyer', 'buyer@justbook.test', '+920000000003');
        $buyer->ensureBuyerProfile();

        $this->command?->info('Demo users ready — password: password');
    }

    private function makeUser(string $fullname, string $email, string $phone): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'fullname' => $fullname,
                'phone_number' => $phone,
                'password' => 'password',
                'is_buyer' => true,
                'is_seller' => false,
                'timezone' => 'UTC',
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
            ]
        );

        if (! $user->username) {
            $user->username = Str::slug($fullname).$user->id;
            $user->save();
        }

        $user->ensureBuyerProfile();

        return $user->fresh();
    }
}
