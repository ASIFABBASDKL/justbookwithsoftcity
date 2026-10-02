<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call(RoleSeeder::class);
        $this->call(MarketplaceSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(ServiceProviderSeeder::class);
        $this->call(ServiceUserSeeder::class);
        $this->call(ServicesAndPricingSeeder::class);
        $this->call(PaymentSeeder::class);
        $this->call(BookingsSeeder::class);
        $this->call(ServiceUserAndProviderChatSeeder::class);
    }
}
