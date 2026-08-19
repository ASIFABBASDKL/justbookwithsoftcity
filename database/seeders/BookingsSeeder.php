<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceUser;
use App\Models\ServiceProvider;
use App\Models\ServiceAndPricing;
use App\Models\Booking;
use Faker\Factory as Faker;

class BookingsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $serviceUsers   = ServiceUser::all();
        $providers      = ServiceProvider::all();
        $servicesPricing = ServiceAndPricing::all();

        // agar required tables empty hain
        if ($serviceUsers->isEmpty() || $providers->isEmpty() || $servicesPricing->isEmpty()) {
            $this->command->warn('⚠️ ServiceUsers, ServiceProviders, or ServicesAndPricing not found. Run their seeders first.');
            return;
        }

        foreach ($serviceUsers as $user) {
            // Har user ke liye 1–3 bookings
            for ($i = 0; $i < rand(1, 3); $i++) {
                $provider = $providers->random();
                $service  = $servicesPricing->where('service_provider_id', $provider->id)->random();

                $price          = $faker->numberBetween(1000, 5000);
                $discount       = $faker->boolean(40) ? $faker->numberBetween(100, 500) : 0;
                $tax            = round($price * 0.05, 2);
                $serviceCharges = 200;
                $emergency      = $faker->boolean(20) ? 500 : 0;
                $subtotal       = $price - $discount;
                $total          = $subtotal + $tax + $serviceCharges + $emergency;

                Booking::create([
                    'service_user_id'        => $user->id,
                    'service_provider_id'    => $provider->id,
                    'services_and_pricing_id'=> $service->id,

                    'booking_date'     => $faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
                    'booking_time'     => $faker->time('H:i'),
                    'address'          => $faker->address,
                    'frequency'        => $faker->randomElement(['one-time', 'weekly', 'monthly']),
                    'describe'         => $faker->sentence(10),
                    'location_img'     => $faker->imageUrl(400, 300, 'city', true, 'location'),
                    'special_request'  => $faker->sentence(8),

                    'price'            => $price,
                    'subtotal'         => $subtotal,
                    'discount'         => $discount,
                    'tax'              => $tax,
                    'service_charges'  => $serviceCharges,
                    'emergency_booking'=> $emergency,
                    'total_amount'     => $total,

                    'payment_method'   => $faker->randomElement(['cash', 'bank', 'paypal', 'stripe']),
                    'payment_status'   => $faker->randomElement(['unpaid', 'paid', 'refunded']),
                    'status'           => $faker->randomElement(['pending', 'confirmed', 'completed', 'cancelled']),
                ]);
            }
        }
    }
}
