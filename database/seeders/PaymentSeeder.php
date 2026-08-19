<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\ServiceUser;
use App\Models\ServiceProvider;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = ['bank', 'paypal', 'stripe', 'jazzcash', 'easypaisa'];

        // 🔹 Random payments for Service Users
        $serviceUsers = ServiceUser::all();
        foreach ($serviceUsers as $user) {
            Payment::create([
                'service_user_id'    => $user->id,
                'service_provider_id'=> null,
                'payment_method'     => fake()->randomElement($methods),
                'account_holder_name'=> $user->user->fullname ?? 'Unknown User',
                'account_name'       => fake()->company(),
                'account_title'      => 'Personal Account',
                'account_number'     => fake()->numerify('##########'),
                'sort_no'            => fake()->numerify('###-###'),
            ]);
        }

        // 🔹 Random payments for Service Providers
        $serviceProviders = ServiceProvider::all();
        foreach ($serviceProviders as $provider) {
            Payment::create([
                'service_user_id'    => null,
                'service_provider_id'=> $provider->id,
                'payment_method'     => fake()->randomElement($methods),
                'account_holder_name'=> $provider->user->fullname ?? 'Unknown Provider',
                'account_name'       => $provider->business_name ?? fake()->company(),
                'account_title'      => 'Business Account',
                'account_number'     => fake()->numerify('##########'),
                'sort_no'            => fake()->numerify('###-###'),
            ]);
        }
    }
}
