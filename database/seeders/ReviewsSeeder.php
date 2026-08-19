<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\Review;
use Faker\Factory as Faker;

class ReviewsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $bookings = Booking::with(['serviceArea', 'serviceUser'])->get();

        if ($bookings->isEmpty()) {
            $this->command->warn('⚠️ No bookings found. Run BookingsSeeder first.');
            return;
        }

        foreach ($bookings as $booking) {
            $providerId = optional($booking->serviceArea)->service_provider_id;
            $userId = $booking->service_user_id;

            if ($providerId && $userId) {
                Review::firstOrCreate(
                    [
                        'booking_id'          => $booking->id,
                        'service_user_id'     => $userId,
                        'service_provider_id' => $providerId,
                    ],
                    [
                        'rating'        => $faker->numberBetween(1, 5),
                        'satisfaction'  => $faker->numberBetween(1, 5),
                        'response_rate' => $faker->numberBetween(1, 5),
                        'job_success'   => $faker->numberBetween(1, 5),
                        'reliability'   => $faker->numberBetween(1, 5),
                        'comment'       => $faker->sentence(12),
                        'is_visible'    => $faker->boolean(90), // 90% visible
                    ]
                );
            }
        }
    }
}
