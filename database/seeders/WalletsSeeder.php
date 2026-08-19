<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\Wallet;
use Faker\Factory as Faker;

class WalletsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $bookings = Booking::with('serviceArea')->get();

        if ($bookings->isEmpty()) {
            $this->command->warn('⚠️ No bookings found. Run BookingsSeeder first.');
            return;
        }

        foreach ($bookings as $booking) {
            // Ensure booking has provider via serviceArea
            $providerId = optional($booking->serviceArea)->service_provider_id;

            if ($providerId) {
                $earned = $booking->total_amount ?? $faker->numberBetween(1000, 5000);
                $withdraw = $faker->boolean(50) ? $faker->numberBetween(0, $earned) : 0;

                Wallet::firstOrCreate(
                    [
                        'service_provider_id' => $providerId,
                        'booking_id'          => $booking->id,
                    ],
                    [
                        'earned'   => $earned,
                        'withdraw' => $withdraw,
                    ]
                );
            }
        }
    }
}
